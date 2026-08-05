<?php

namespace Services;

use Models\Candidatos;
use Models\FadRrhh;

final class FadRrhhService
{
    private ?FadRrhhPortalClient $client;

    public function __construct(?FadRrhhPortalClient $client = null)
    {
        $this->client = $client;
    }

    public function configuracion(): array
    {
        $enabled = $this->envBool('FAD_RRHH_ENABLED');
        $enforce = $this->envBool('FAD_RRHH_ENFORCE_SIGNED');
        $connectionRequired = [
            'username' => trim((string) getenv('FAD_RRHH_USERNAME')),
            'password' => trim((string) getenv('FAD_RRHH_PASSWORD')),
        ];
        $flowRequired = [
            'country_id' => $this->positiveId('FAD_RRHH_COUNTRY_ID'),
            'requisition_type_id' => $this->positiveId('FAD_RRHH_REQUISITION_TYPE_ID'),
            'sign_time_id' => $this->positiveId('FAD_RRHH_SIGN_TIME_ID'),
            'signature_box' => $this->boxConfigValid('FAD_RRHH_SIGNATURE_BOX'),
            'certificate_box' => $this->boxConfigValid('FAD_RRHH_CERTIFICATE_BOX'),
        ];
        $connectionReady = $enabled
            && count(array_filter($connectionRequired, static fn($value) => $value === '')) === 0;
        $flowReady = $connectionReady
            && count(array_filter($flowRequired, static fn($value) => $value === null)) === 0;

        return [
            'enabled' => $enabled,
            'enforce_signed' => $enforce,
            'auth_mode' => 'portal_bootstrap',
            'api_ready' => $connectionReady,
            'flow_ready' => $flowReady,
            'flow_missing' => array_keys(array_filter($flowRequired, static fn($value) => $value === null)),
            'mode' => $enforce ? 'fad_required' : ($enabled ? 'fad_observation' : 'preparation'),
            'portal_url' => 'https://clientes.firmaautografa.com/home',
        ];
    }

    public function preparar(int $idCandidato, ?int $idUsuario = null): array
    {
        $candidateResult = Candidatos::getById($idCandidato);
        if (empty($candidateResult['success']) || empty($candidateResult['datos'])) {
            throw new \RuntimeException('Candidato no encontrado.');
        }
        $candidate = $candidateResult['datos'];
        $blockers = [];
        if (!filter_var(trim((string) ($candidate['email'] ?? '')), FILTER_VALIDATE_EMAIL)) {
            $blockers[] = 'El candidato no tiene un correo válido.';
        }
        if (strlen(preg_replace('/\D+/', '', (string) ($candidate['telefono'] ?? ''))) < 10) {
            $blockers[] = 'El candidato no tiene un teléfono válido.';
        }

        $record = FadRrhh::preparar($idCandidato, $idUsuario);
        return $this->estadoDesdeRegistro($record, $blockers);
    }

    public function estado(int $idCandidato): array
    {
        return $this->estadoDesdeRegistro(FadRrhh::obtenerPorCandidato($idCandidato));
    }

    public function vincular(
        int $idCandidato,
        string $requisitionId,
        ?string $documentId,
        ?string $signerId,
        ?string $signingUrl,
        ?int $idUsuario = null
    ): array {
        $signingUrl = trim((string) $signingUrl);
        if ($signingUrl !== '') {
            $urlParts = parse_url($signingUrl);
            $host = strtolower((string) ($urlParts['host'] ?? ''));
            if (!filter_var($signingUrl, FILTER_VALIDATE_URL)
                || strtolower((string) ($urlParts['scheme'] ?? '')) !== 'https'
                || ($host !== 'firmaautografa.com' && !str_ends_with($host, '.firmaautografa.com'))) {
                throw new \InvalidArgumentException('La URL de firma debe pertenecer al dominio seguro de FAD.');
            }
        }
        $record = FadRrhh::vincularSolicitud(
            $idCandidato,
            $requisitionId,
            $documentId,
            $signerId,
            $signingUrl !== '' ? $signingUrl : null,
            $idUsuario
        );
        return $this->estadoDesdeRegistro($record);
    }

    public function evaluarPasoGestion(int $idCandidato): array
    {
        $config = $this->configuracion();
        if (empty($config['enforce_signed'])) {
            return self::evaluarEvidencia($config, null);
        }
        return self::evaluarEvidencia($config, FadRrhh::obtenerPorCandidato($idCandidato));
    }

    public function probarConexion(): array
    {
        $config = $this->configuracion();
        if (empty($config['api_ready'])) {
            throw new \RuntimeException('Configura y habilita las credenciales del portal FAD RRHH.');
        }
        $token = $this->token();
        $user = $this->portalClient()->getCurrentUser($token);
        $types = $this->portalClient()->getRequisitionTypes($token);
        $lifeTimes = $this->portalClient()->getLifeTimes($token);
        $signers = $this->portalClient()->getSigners($token, (string) $user['userId']);
        return [
            'conexion' => 'OK',
            'user_id_disponible' => !empty($user['userId']),
            'tipos_solicitud' => $this->safeCatalog($types),
            'vigencias' => $this->safeCatalog($lifeTimes),
            'pais_mexico_sugerido' => $this->suggestCountry($signers),
            'escritura_realizada' => false,
        ];
    }

    public function enviarContrato(
        int $idCandidato,
        string $pdfPath,
        string $originalName,
        ?int $idUsuario = null
    ): array {
        $config = $this->configuracion();
        if (empty($config['flow_ready'])) {
            throw new \RuntimeException('La conexión o los catálogos/espacios de firma FAD están incompletos.');
        }
        $this->validatePdf($pdfPath);
        $candidateResult = Candidatos::getById($idCandidato);
        if (empty($candidateResult['success']) || empty($candidateResult['datos'])) {
            throw new \RuntimeException('Candidato no encontrado.');
        }
        $candidate = $candidateResult['datos'];
        $email = trim((string) ($candidate['email'] ?? ''));
        $phone = preg_replace('/\D+/', '', (string) ($candidate['telefono'] ?? ''));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($phone) < 10) {
            throw new \RuntimeException('El candidato necesita correo y teléfono válidos para FAD.');
        }
        $phone = substr($phone, -10);
        $record = FadRrhh::preparar($idCandidato, $idUsuario);
        if (!empty($record['requisition_id'])) {
            return $this->estadoDesdeRegistro($record);
        }

        $token = $this->token();
        $user = $this->portalClient()->getCurrentUser($token);
        $userId = (string) $user['userId'];
        $documentId = trim((string) ($record['document_id'] ?? ''));
        if ($documentId === '') {
            $upload = $this->portalClient()->uploadDocument(
                $token,
                $userId,
                $pdfPath,
                $this->safePdfName($originalName, $idCandidato)
            );
            $documentId = $this->findValue($upload, ['documentId', 'idDocument', 'id']);
            if ($documentId === '') {
                throw new \RuntimeException('FAD recibió el PDF pero no devolvió document_id.');
            }
            $record = FadRrhh::guardarProgreso($idCandidato, $documentId, null, $idUsuario);
        }

        $signerId = trim((string) ($record['signer_id'] ?? ''));
        if ($signerId === '') {
            $signer = $this->portalClient()->createSigner($token, $userId, [
                'name' => trim((string) ($candidate['nombres'] ?? '')),
                'lastName' => trim((string) ($candidate['apellidop'] ?? '')),
                'secondLastName' => trim((string) ($candidate['apellidom'] ?? '')),
                'email' => $email,
                'companyEmail' => $email,
                'phone' => $phone,
                'securityCode' => substr($phone, -4),
                'countryId' => $this->env('FAD_RRHH_COUNTRY_ID'),
                'countryCode' => $this->env('FAD_RRHH_COUNTRY_CODE', '+52'),
            ]);
            $signerId = $this->findValue($signer, ['signerId', 'idSigner', 'id']);
            if ($signerId === '') {
                throw new \RuntimeException('FAD recibió al firmante pero no devolvió signer_id.');
            }
            $record = FadRrhh::guardarProgreso($idCandidato, null, $signerId, $idUsuario);
        }

        $signature = $this->box('FAD_RRHH_SIGNATURE_BOX', true);
        $certificate = $this->box('FAD_RRHH_CERTIFICATE_BOX', false);
        $reference = (string) ($record['referencia'] ?? ('SPARTA-RRHH-CAND-' . $idCandidato));
        $fullName = trim(implode(' ', array_filter([
            $candidate['nombres'] ?? '',
            $candidate['segundo_nombre'] ?? '',
            $candidate['apellidop'] ?? '',
            $candidate['apellidom'] ?? '',
        ])));
        $requisition = $this->portalClient()->createRequisition($token, $userId, [
            'name' => substr('Contrato laboral - ' . $fullName, 0, 255),
            'requisitionTypeId' => $this->env('FAD_RRHH_REQUISITION_TYPE_ID'),
            'reference' => $reference,
            'signTimeId' => $this->env('FAD_RRHH_SIGN_TIME_ID'),
            'acceptanceLegend' => $this->env('FAD_RRHH_ACCEPTANCE_LEGEND', 'Acepto firmar el presente contrato laboral.'),
            'documentId' => $documentId,
            'cardId' => null,
            'certificate' => $certificate,
            'localizationNotRequired' => $this->envBool('FAD_RRHH_LOCALIZATION_NOT_REQUIRED'),
            'acceptanceVideoNotRequired' => $this->envBool('FAD_RRHH_ACCEPTANCE_VIDEO_NOT_REQUIRED'),
            'isSingleDeviceSignAvailable' => false,
            'signOnWeb' => true,
            'signers' => [[
                'signerId' => $signerId,
                'order' => 1,
                'countryCode' => $this->env('FAD_RRHH_COUNTRY_CODE', '+52'),
                'phone' => $phone,
                'notification' => true,
                'sendSMS' => true,
                'signDevicePhone' => $this->env('FAD_RRHH_COUNTRY_CODE', '+52') . $phone,
                'status' => 'ACTIVE',
                'signatures' => [$signature],
            ]],
        ]);
        $requisitionId = $this->findValue($requisition, ['requisitionId', 'idRequisition', 'id']);
        if ($requisitionId === '') {
            throw new \RuntimeException('FAD creó la solicitud pero no devolvió requisition_id.');
        }
        $ticket = $this->findValue($requisition, ['firstTicket', 'signingUrl', 'url']);
        $signingUrl = filter_var($ticket, FILTER_VALIDATE_URL) ? $ticket : null;
        $record = FadRrhh::vincularSolicitud(
            $idCandidato,
            $requisitionId,
            $documentId,
            $signerId,
            $signingUrl,
            $idUsuario
        );
        return $this->estadoDesdeRegistro($record);
    }

    public function sincronizar(int $idCandidato, ?int $idUsuario = null): array
    {
        $record = FadRrhh::obtenerPorCandidato($idCandidato);
        $requisitionId = trim((string) ($record['requisition_id'] ?? ''));
        if ($requisitionId === '') {
            throw new \RuntimeException('El candidato todavía no tiene una solicitud FAD vinculada.');
        }
        $token = $this->token();
        $info = $this->portalClient()->getRequisitionInfo($token, $requisitionId);
        $status = $this->normalizeStatus($this->findValue($info, ['status', 'requisitionStatus', 'state']));
        $pdfPath = null;
        $pdfHash = null;
        if ($status === FadRrhh::STATUS_SIGNED) {
            $pdf = $this->portalClient()->downloadSignedPdf($token, $requisitionId);
            $pdfHash = hash('sha256', $pdf);
            if ($pdfHash !== (string) ($record['pdf_firmado_sha256'] ?? '')) {
                $name = 'contrato_firmado_fad_' . $idCandidato . '.pdf';
                $saved = Candidatos::guardarDocumento(
                    $idCandidato,
                    $name,
                    'fad-rrhh://' . $requisitionId . '/signed.pdf',
                    'Contrato firmado',
                    $pdf,
                    'application/pdf'
                );
                if (empty($saved['success'])) {
                    throw new \RuntimeException('No se pudo guardar el PDF firmado en el expediente.');
                }
            }
            $pdfPath = 'fad-rrhh://' . $requisitionId . '/signed.pdf';
        }
        $record = FadRrhh::actualizarEstado(
            $idCandidato,
            $status,
            $pdfPath,
            $pdfHash,
            null,
            null,
            $idUsuario
        );
        return $this->estadoDesdeRegistro($record);
    }

    public static function evaluarEvidencia(array $config, ?array $record): array
    {
        $enforce = !empty($config['enforce_signed']);
        if (!$enforce) {
            return [
                'permitido' => true,
                'motivo' => 'FAD RRHH está en preparación u observación; el flujo heredado continúa disponible.',
                'estatus' => strtoupper((string) ($record['estatus'] ?? 'NOT_STARTED')),
            ];
        }
        if (empty($config['enabled']) || empty($config['api_ready'])) {
            return [
                'permitido' => false,
                'motivo' => 'FAD RRHH está marcado como obligatorio, pero su configuración API está incompleta.',
                'estatus' => strtoupper((string) ($record['estatus'] ?? 'NOT_STARTED')),
            ];
        }
        if (!$record || strtoupper((string) ($record['estatus'] ?? '')) !== FadRrhh::STATUS_SIGNED) {
            return [
                'permitido' => false,
                'motivo' => 'El contrato todavía no aparece como firmado en FAD.',
                'estatus' => strtoupper((string) ($record['estatus'] ?? 'NOT_STARTED')),
            ];
        }
        $path = trim((string) ($record['pdf_firmado_ruta'] ?? ''));
        $hash = strtolower(trim((string) ($record['pdf_firmado_sha256'] ?? '')));
        if ($path === '' || !preg_match('/^[a-f0-9]{64}$/', $hash)) {
            return [
                'permitido' => false,
                'motivo' => 'FAD reporta la firma, pero falta resguardar y verificar el PDF final.',
                'estatus' => FadRrhh::STATUS_SIGNED,
            ];
        }
        return [
            'permitido' => true,
            'motivo' => 'Firma FAD y PDF final verificados.',
            'estatus' => FadRrhh::STATUS_SIGNED,
        ];
    }

    private function estadoDesdeRegistro(?array $record, array $blockers = []): array
    {
        $config = $this->configuracion();
        $gate = self::evaluarEvidencia($config, $record);
        return [
            'configuracion' => $config,
            'solicitud' => $record ? $this->publicRecord($record) : null,
            'permitido_pasar_gestion' => !empty($gate['permitido']),
            'motivo_bloqueo' => empty($gate['permitido']) ? $gate['motivo'] : null,
            'bloqueos_preparacion' => $blockers,
            'siguientes_pasos' => $this->nextSteps($record, $config),
        ];
    }

    private function publicRecord(array $record): array
    {
        $allowed = [
            'id', 'id_candidato', 'referencia', 'document_id', 'signer_id',
            'requisition_id', 'signing_url', 'estatus', 'pdf_firmado_sha256',
            'enviado_en', 'firmado_en', 'ultima_sync_en', 'creado_en', 'actualizado_en',
        ];
        return array_intersect_key($record, array_flip($allowed));
    }

    private function nextSteps(?array $record, array $config): array
    {
        if (!$record) {
            return ['Preparar el expediente local FAD del candidato.'];
        }
        if (($record['estatus'] ?? '') === FadRrhh::STATUS_DRAFT) {
            if (empty($config['api_ready'])) {
                return ['Configurar y validar la conexión de Capital Humano con FAD.'];
            }
            if (empty($config['flow_ready'])) {
                return ['Definir las posiciones de firma y certificado sobre la plantilla contractual.'];
            }
            return ['Cargar contrato, registrar firmante y crear la solicitud mediante la API.'];
        }
        if (($record['estatus'] ?? '') === FadRrhh::STATUS_PENDING) {
            return ['Esperar la firma y sincronizar el estado de la solicitud.'];
        }
        if (($record['estatus'] ?? '') === FadRrhh::STATUS_SIGNED) {
            return ['Verificar el PDF firmado y anexarlo al expediente de Capital Humano.'];
        }
        return ['Revisar el estado final en FAD antes de continuar.'];
    }

    private function envBool(string $name): bool
    {
        return in_array(strtolower(trim((string) getenv($name))), ['1', 'true', 'yes', 'on'], true);
    }

    private function positiveId(string $name): ?string
    {
        $value = trim((string) getenv($name));
        return preg_match('/^[1-9]\d*$/', $value) ? $value : null;
    }

    private function boxConfigValid(string $name): ?array
    {
        $box = json_decode(trim((string) getenv($name)), true);
        $required = ['page', 'positionX1', 'positionX2', 'positionY1', 'positionY2'];
        if (!is_array($box) || array_diff($required, array_keys($box))) {
            return null;
        }
        if (!is_numeric($box['page']) || (int) $box['page'] < 1) {
            return null;
        }
        foreach (array_slice($required, 1) as $key) {
            if (!is_numeric($box[$key]) || (float) $box[$key] < 0 || (float) $box[$key] > 1) {
                return null;
            }
        }
        if ((float) $box['positionX1'] >= (float) $box['positionX2']
            || (float) $box['positionY1'] >= (float) $box['positionY2']) {
            return null;
        }
        return $box;
    }

    private function portalClient(): FadRrhhPortalClient
    {
        if ($this->client === null) {
            $this->client = new FadRrhhPortalClient(
                null,
                $this->env('FAD_RRHH_API_BASE', 'https://api.firmaautografa.com'),
                $this->env('FAD_RRHH_PORTAL_BASE', 'https://clientes.firmaautografa.com')
            );
        }
        return $this->client;
    }

    private function token(): string
    {
        $auth = $this->portalClient()->authenticate(
            $this->env('FAD_RRHH_USERNAME'),
            $this->env('FAD_RRHH_PASSWORD')
        );
        return (string) ($auth['access_token'] ?? '');
    }

    private function env(string $name, string $default = ''): string
    {
        $value = trim((string) getenv($name));
        return $value !== '' ? $value : $default;
    }

    private function validatePdf(string $path): void
    {
        if (!is_file($path) || !is_readable($path)) {
            throw new \RuntimeException('No se recibió un contrato PDF válido.');
        }
        $size = (int) filesize($path);
        if ($size <= 0 || $size > 10 * 1024 * 1024) {
            throw new \RuntimeException('El contrato debe pesar entre 1 byte y 10 MB.');
        }
        $handle = fopen($path, 'rb');
        $magic = $handle ? fread($handle, 5) : '';
        if (is_resource($handle)) fclose($handle);
        if ($magic !== '%PDF-') {
            throw new \RuntimeException('El archivo recibido no es un PDF.');
        }
    }

    private function safePdfName(string $name, int $idCandidato): string
    {
        $base = preg_replace('/[^A-Za-z0-9._-]+/', '_', basename($name));
        if (!is_string($base) || $base === '' || !str_ends_with(strtolower($base), '.pdf')) {
            $base = 'contrato_' . $idCandidato . '.pdf';
        }
        return substr($base, 0, 180);
    }

    private function box(string $envName, bool $signature): array
    {
        $box = json_decode($this->env($envName), true);
        $required = ['page', 'positionX1', 'positionX2', 'positionY1', 'positionY2'];
        if (!is_array($box) || array_diff($required, array_keys($box))) {
            throw new \RuntimeException('La configuración ' . $envName . ' no es válida.');
        }
        foreach (array_slice($required, 1) as $key) {
            $value = (float) $box[$key];
            if ($value < 0 || $value > 1) {
                throw new \RuntimeException('Las coordenadas FAD deben estar entre 0 y 1.');
            }
            $box[$key] = number_format($value, 4, '.', '');
        }
        $box['page'] = (string) max(1, (int) $box['page']);
        if ($signature) {
            $box['signerType'] = 'Firmante';
            $box['centerX'] = number_format(((float) $box['positionX1'] + (float) $box['positionX2']) / 2, 4, '.', '');
            $box['centerY'] = number_format(((float) $box['positionY1'] + (float) $box['positionY2']) / 2, 4, '.', '');
            $box['optional'] = false;
        }
        return $box;
    }

    private function findValue(array $data, array $keys): string
    {
        foreach ($keys as $key) {
            if (isset($data[$key]) && is_scalar($data[$key])) {
                return trim((string) $data[$key]);
            }
        }
        foreach ($data as $value) {
            if (is_array($value)) {
                $found = $this->findValue($value, $keys);
                if ($found !== '') return $found;
            }
        }
        return '';
    }

    private function normalizeStatus(string $status): string
    {
        $status = strtoupper(trim($status));
        $map = [
            'COMPLETED' => FadRrhh::STATUS_SIGNED,
            'SIGNED' => FadRrhh::STATUS_SIGNED,
            'PENDING' => FadRrhh::STATUS_PENDING,
            'IN_PROCESS' => FadRrhh::STATUS_PENDING,
            'CANCELED' => FadRrhh::STATUS_CANCELLED,
            'CANCELLED' => FadRrhh::STATUS_CANCELLED,
            'EXPIRED' => FadRrhh::STATUS_EXPIRED,
            'REJECTED' => FadRrhh::STATUS_REJECTED,
            'ERROR' => FadRrhh::STATUS_ERROR,
        ];
        return $map[$status] ?? FadRrhh::STATUS_PENDING;
    }

    private function safeCatalog(array $items): array
    {
        $result = [];
        foreach ($items as $item) {
            if (!is_array($item)) continue;
            $id = $this->findValue($item, ['id', 'requisitionTypeId', 'signTimeId', 'lifeTimeId']);
            $name = $this->findValue($item, [
                'name', 'description', 'label', 'type', 'requisitionType', 'signTime',
            ]);
            if ($id !== '') {
                $result[] = ['id' => $id, 'nombre' => $name];
            }
        }
        return $result;
    }

    private function suggestCountry(array $signers): ?array
    {
        foreach ($signers as $signer) {
            if (!is_array($signer)) continue;
            $code = $this->findValue($signer, ['countryCode']);
            $id = $this->findValue($signer, ['countryId']);
            if ($id !== '' && in_array($code, ['+52', '+521', '52', '521'], true)) {
                return ['id' => $id, 'codigo' => '+52'];
            }
        }
        return null;
    }
}
