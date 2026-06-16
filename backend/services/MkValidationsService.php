<?php

namespace Services;

use RuntimeException;

class MkValidationsService
{
    private string $region;
    private string $table;
    private ?array $credentials;

    public function __construct()
    {
        $cfg = $this->loadConfig();
        $this->region = trim((string)($cfg['region'] ?? getenv('AWS_DEFAULT_REGION') ?: 'us-east-1'));
        $this->table = trim((string)($cfg['table'] ?? 'mk-validations'));

        $key = trim((string)($cfg['aws_access_key_id'] ?? getenv('AWS_ACCESS_KEY_ID') ?: ''));
        $secret = trim((string)($cfg['aws_secret_access_key'] ?? getenv('AWS_SECRET_ACCESS_KEY') ?: ''));
        $token = trim((string)($cfg['aws_session_token'] ?? getenv('AWS_SESSION_TOKEN') ?: ''));

        $this->credentials = ($key !== '' && $secret !== '')
            ? array_filter([
                'key' => $key,
                'secret' => $secret,
                'token' => $token !== '' ? $token : null,
            ])
            : null;
    }

    public function getByOferta(int $idOferta, ?string $validationType = null): array
    {
        if ($idOferta <= 0) {
            return $this->response(false, 'id_oferta debe ser un número mayor a cero.', []);
        }

        try {
            $client = $this->client();
            $marshaler = $this->marshaler();

            $params = [
                'TableName' => $this->table,
                'KeyConditionExpression' => '#id = :id',
                'ExpressionAttributeNames' => [
                    '#id' => 'id_oferta',
                ],
                'ExpressionAttributeValues' => [
                    ':id' => ['N' => (string)$idOferta],
                ],
            ];

            $validationType = trim((string)$validationType);
            if ($validationType !== '') {
                $params['KeyConditionExpression'] .= ' AND #type = :type';
                $params['ExpressionAttributeNames']['#type'] = 'validation_type';
                $params['ExpressionAttributeValues'][':type'] = ['S' => $validationType];
            }

            $items = [];
            do {
                $result = $client->query($params);
                foreach ($result['Items'] ?? [] as $item) {
                    $items[] = $marshaler->unmarshalItem($item);
                }
                if (isset($result['LastEvaluatedKey'])) {
                    $params['ExclusiveStartKey'] = $result['LastEvaluatedKey'];
                } else {
                    unset($params['ExclusiveStartKey']);
                }
            } while (isset($result['LastEvaluatedKey']));

            return $this->response(true, 'Validaciones consultadas.', $items);
        } catch (\Throwable $e) {
            return $this->response(false, 'No se pudo consultar DynamoDB.', [], $e->getMessage());
        }
    }

    public function scanPreview(int $limit = 25): array
    {
        $limit = max(1, min(100, $limit));

        try {
            $client = $this->client();
            $marshaler = $this->marshaler();
            $result = $client->scan([
                'TableName' => $this->table,
                'Limit' => $limit,
            ]);

            $items = [];
            foreach ($result['Items'] ?? [] as $item) {
                $items[] = $marshaler->unmarshalItem($item);
            }

            return $this->response(true, 'Muestra consultada.', $items);
        } catch (\Throwable $e) {
            return $this->response(false, 'No se pudo consultar DynamoDB.', [], $e->getMessage());
        }
    }

    public function getCoordenadasFirma(int $idOferta): array
    {
        $result = $this->getByOferta($idOferta, 'coordinates_validation');
        if (empty($result['success'])) {
            return $result;
        }

        $validation = $result['datos'][0] ?? null;
        if (!is_array($validation)) {
            return $this->response(false, 'No se encontro validacion de coordenadas para esta oferta.', []);
        }

        $payload = $this->decodeS3Response($validation['s3_response'] ?? null);
        if ($payload === null) {
            return $this->response(false, 'La validacion de coordenadas no tiene una respuesta JSON valida.', []);
        }

        $coordinates = is_array($payload['coordinates'] ?? null) ? $payload['coordinates'] : [];
        $firma = $this->coordinatePair($coordinates['fad'] ?? null);
        if ($firma === null) {
            return $this->response(false, 'No se encontraron coordenadas de firma en la validacion.', []);
        }

        return $this->response(true, 'Coordenadas de firma consultadas.', [[
            'id_oferta' => $idOferta,
            'validation_type' => 'coordinates_validation',
            'is_valid' => (bool)($validation['is_valid'] ?? $payload['status'] ?? false),
            'fecha_validacion' => $payload['timestamp'] ?? $validation['last_updated'] ?? null,
            'firma' => $firma,
            'domicilio' => $this->coordinatePair($coordinates['domicilio'] ?? null),
            'agencia' => $this->coordinatePair($coordinates['agencia'] ?? null),
            'rutas' => is_array($payload['routes'] ?? null) ? $payload['routes'] : [],
        ]]);
    }

    private function client()
    {
        if (!class_exists(\Aws\DynamoDb\DynamoDbClient::class)) {
            throw new RuntimeException('Falta instalar aws/aws-sdk-php en Composer para consultar DynamoDB.');
        }

        $options = [
            'region' => $this->region,
            'version' => 'latest',
        ];
        if ($this->credentials !== null) {
            $options['credentials'] = $this->credentials;
        }

        return new \Aws\DynamoDb\DynamoDbClient($options);
    }

    private function marshaler()
    {
        if (!class_exists(\Aws\DynamoDb\Marshaler::class)) {
            throw new RuntimeException('Falta instalar aws/aws-sdk-php en Composer para convertir datos de DynamoDB.');
        }

        return new \Aws\DynamoDb\Marshaler();
    }

    private function loadConfig(): array
    {
        $path = defined('RAIZ')
            ? RAIZ . '/config/config.ini'
            : dirname(__DIR__) . '/config/config.ini';
        if (!is_file($path)) {
            return [];
        }

        $ini = @parse_ini_file($path, true);
        return is_array($ini['dynamodb'] ?? null) ? $ini['dynamodb'] : [];
    }

    private function decodeS3Response($value): ?array
    {
        if (is_array($value)) {
            return $value;
        }
        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        $decoded = json_decode($value, true);
        return is_array($decoded) ? $decoded : null;
    }

    private function coordinatePair($value): ?array
    {
        if (!is_array($value) || count($value) < 2) {
            return null;
        }

        return [
            'lat' => (float)$value[0],
            'lng' => (float)$value[1],
        ];
    }

    private function response(bool $success, string $message, array $data, ?string $error = null): array
    {
        $response = [
            'success' => $success,
            'mensaje' => $message,
            'datos' => $data,
            'fuente' => [
                'driver' => 'dynamodb',
                'region' => $this->region,
                'table' => $this->table,
                'live' => true,
            ],
        ];
        if ($error !== null) {
            $response['error'] = $error;
        }

        return $response;
    }
}
