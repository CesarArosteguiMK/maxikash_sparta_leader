<?php

namespace Services;

/**
 * Catálogo cerrado de contratos autorizables para Capital Humano.
 *
 * Las coordenadas son una propuesta basada en los machotes entregados. Cada
 * plantilla permanece bloqueada hasta que Capital Humano valide visualmente
 * sus áreas en FAD y active la variable de aprobación correspondiente.
 */
final class FadRrhhTemplateCatalog
{
    public const AMIGO_GENERAL_NUEVO = 'AMIGO_GENERAL_NUEVO';
    public const AMIGO_ACTUALIZACION = 'AMIGO_ACTUALIZACION';
    public const PENSIONAMAX_NUEVO = 'PENSIONAMAX_NUEVO';
    public const AMIGO_GESTOR_COBRANZA = 'AMIGO_GESTOR_COBRANZA';

    public function all(): array
    {
        return [
            self::AMIGO_GENERAL_NUEVO => $this->definition(
                self::AMIGO_GENERAL_NUEVO,
                'Contrato general — nuevo ingreso corporativo',
                'AMIGO_EFECTIVO',
                'Amigo Efectivo S.A.P.I. de C.V.',
                'GABRIELA LUCERO SANCHEZ',
                9,
                'FAD_RRHH_TEMPLATE_AMIGO_GENERAL_APPROVED'
            ),
            self::AMIGO_ACTUALIZACION => $this->definition(
                self::AMIGO_ACTUALIZACION,
                'Actualización y reconocimiento de antigüedad',
                'AMIGO_EFECTIVO',
                'Amigo Efectivo S.A.P.I. de C.V.',
                'GABRIELA LUCERO SANCHEZ',
                9,
                'FAD_RRHH_TEMPLATE_AMIGO_ACTUALIZACION_APPROVED'
            ),
            self::PENSIONAMAX_NUEVO => $this->definition(
                self::PENSIONAMAX_NUEVO,
                'Contrato Pensionamax — nuevo ingreso',
                'PENSIONAMAX',
                'Pensionamax S.A.P.I. de C.V.',
                'MARIA DEL CARMEN JARAMILLO CAMACHO',
                10,
                'FAD_RRHH_TEMPLATE_PENSIONAMAX_APPROVED'
            ),
            self::AMIGO_GESTOR_COBRANZA => $this->definition(
                self::AMIGO_GESTOR_COBRANZA,
                'Contrato gestor de cobranza',
                'AMIGO_EFECTIVO',
                'Amigo Efectivo S.A.P.I. de C.V.',
                'GABRIELA LUCERO SANCHEZ',
                8,
                'FAD_RRHH_TEMPLATE_GESTOR_COBRANZA_APPROVED'
            ),
        ];
    }

    public function get(string $code): array
    {
        $code = strtoupper(trim($code));
        $templates = $this->all();
        if ($code === '' || !isset($templates[$code])) {
            throw new \InvalidArgumentException('Selecciona un tipo de contrato FAD válido.');
        }
        return $templates[$code];
    }

    public function publicCatalog(): array
    {
        return array_values(array_map(static function (array $template): array {
            return [
                'code' => $template['code'],
                'label' => $template['label'],
                'company_code' => $template['company_code'],
                'company_name' => $template['company_name'],
                'legal_signer_name' => $template['legal_signer_name'],
                'expected_pages' => $template['expected_pages'],
                'approved' => $template['approved'],
            ];
        }, $this->all()));
    }

    private function definition(
        string $code,
        string $label,
        string $companyCode,
        string $companyName,
        string $legalSignerName,
        int $pages,
        string $approvalEnv
    ): array {
        return [
            'code' => $code,
            'label' => $label,
            'company_code' => $companyCode,
            'company_name' => $companyName,
            'legal_signer_name' => $legalSignerName,
            'expected_pages' => $pages,
            'approval_env' => $approvalEnv,
            'approved' => $this->envBool($approvalEnv),
            'worker_signatures' => $this->signatureBoxes($pages, false),
            'legal_signatures' => $this->signatureBoxes($pages, true),
            'certificate' => [
                'page' => $pages,
                'positionX1' => 0.12,
                'positionX2' => 0.82,
                'positionY1' => 0.83,
                'positionY2' => 0.93,
            ],
        ];
    }

    private function signatureBoxes(int $pages, bool $legal): array
    {
        $boxes = [];
        for ($page = 1; $page < $pages; $page++) {
            $boxes[] = [
                'page' => $page,
                'positionX1' => 0.855,
                'positionX2' => 0.985,
                'positionY1' => $legal ? 0.30 : 0.40,
                'positionY2' => $legal ? 0.37 : 0.47,
            ];
        }
        $boxes[] = [
            'page' => $pages,
            'positionX1' => $legal ? 0.18 : 0.55,
            'positionX2' => $legal ? 0.45 : 0.82,
            'positionY1' => 0.68,
            'positionY2' => 0.78,
        ];
        return $boxes;
    }

    private function envBool(string $name): bool
    {
        return in_array(strtolower(trim((string) getenv($name))), ['1', 'true', 'yes', 'on'], true);
    }
}
