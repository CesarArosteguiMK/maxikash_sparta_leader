<?php

namespace Models;

use Core\Database;

final class FadRrhh
{
    public const STATUS_DRAFT = 'DRAFT';
    public const STATUS_PENDING = 'PENDING';
    public const STATUS_SIGNED = 'SIGNED';
    public const STATUS_CANCELLED = 'CANCELLED';
    public const STATUS_EXPIRED = 'EXPIRED';
    public const STATUS_REJECTED = 'REJECTED';
    public const STATUS_ERROR = 'ERROR';

    private static function now(): string
    {
        return (new \DateTimeImmutable('now', new \DateTimeZone('America/Mexico_City')))
            ->format('Y-m-d H:i:s');
    }

    private static function ensureSchema(): void
    {
        static $ready = false;
        if ($ready) {
            return;
        }

        $db = new Database();
        $db->CRUD(
            "CREATE TABLE IF NOT EXISTS candidato_fad_rrhh_solicitud (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                id_candidato INT NOT NULL,
                referencia VARCHAR(120) NOT NULL,
                template_code VARCHAR(80) NULL,
                company_code VARCHAR(40) NULL,
                document_id VARCHAR(120) NULL,
                signer_id VARCHAR(120) NULL,
                worker_signer_id VARCHAR(120) NULL,
                legal_signer_id VARCHAR(120) NULL,
                requisition_id VARCHAR(120) NULL,
                signing_url VARCHAR(700) NULL,
                legal_signing_url VARCHAR(700) NULL,
                estatus VARCHAR(30) NOT NULL DEFAULT 'DRAFT',
                pdf_firmado_ruta VARCHAR(700) NULL,
                pdf_firmado_sha256 CHAR(64) NULL,
                fad_archivo_ruta VARCHAR(700) NULL,
                ultimo_error VARCHAR(1000) NULL,
                intentos_sync INT UNSIGNED NOT NULL DEFAULT 0,
                creado_por INT NULL,
                actualizado_por INT NULL,
                enviado_en DATETIME NULL,
                firmado_en DATETIME NULL,
                ultima_sync_en DATETIME NULL,
                creado_en DATETIME NOT NULL,
                actualizado_en DATETIME NOT NULL,
                UNIQUE KEY uq_fad_rrhh_candidato (id_candidato),
                UNIQUE KEY uq_fad_rrhh_referencia (referencia),
                UNIQUE KEY uq_fad_rrhh_requisition (requisition_id),
                INDEX idx_fad_rrhh_estatus (estatus),
                INDEX idx_fad_rrhh_actualizado (actualizado_en)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
        self::ensureColumns($db);
        $ready = true;
    }

    public static function asegurarEsquema(): void
    {
        self::ensureSchema();
    }

    private static function ensureColumns(Database $db): void
    {
        $columns = [
            'template_code' => 'VARCHAR(80) NULL AFTER referencia',
            'company_code' => 'VARCHAR(40) NULL AFTER template_code',
            'worker_signer_id' => 'VARCHAR(120) NULL AFTER signer_id',
            'legal_signer_id' => 'VARCHAR(120) NULL AFTER worker_signer_id',
            'legal_signing_url' => 'VARCHAR(700) NULL AFTER signing_url',
        ];
        foreach ($columns as $name => $definition) {
            $exists = $db->queryOne(
                "SELECT COUNT(*) AS total
                 FROM information_schema.columns
                 WHERE table_schema = DATABASE()
                   AND table_name = 'candidato_fad_rrhh_solicitud'
                   AND column_name = :column_name",
                ['column_name' => $name]
            );
            if ((int) ($exists['total'] ?? 0) === 0) {
                $db->CRUD("ALTER TABLE candidato_fad_rrhh_solicitud ADD COLUMN {$name} {$definition}");
            }
        }
    }

    public static function preparar(int $idCandidato, ?int $idUsuario = null): array
    {
        if ($idCandidato <= 0) {
            throw new \InvalidArgumentException('ID de candidato inválido.');
        }

        self::ensureSchema();
        $db = new Database();
        $now = self::now();
        $reference = sprintf('SPARTA-RRHH-CAND-%d', $idCandidato);
        $db->CRUD(
            "INSERT INTO candidato_fad_rrhh_solicitud
                (id_candidato, referencia, estatus, creado_por, actualizado_por, creado_en, actualizado_en)
             VALUES
                (:id_candidato, :referencia, :estatus, :creado_por, :actualizado_por, :creado_en, :actualizado_en)
             ON DUPLICATE KEY UPDATE
                actualizado_por = VALUES(actualizado_por),
                actualizado_en = VALUES(actualizado_en)",
            [
                'id_candidato' => $idCandidato,
                'referencia' => $reference,
                'estatus' => self::STATUS_DRAFT,
                'creado_por' => $idUsuario,
                'actualizado_por' => $idUsuario,
                'creado_en' => $now,
                'actualizado_en' => $now,
            ]
        );

        return self::obtenerPorCandidato($idCandidato) ?? [];
    }

    public static function obtenerPorCandidato(int $idCandidato): ?array
    {
        if ($idCandidato <= 0) {
            return null;
        }
        self::ensureSchema();
        $db = new Database();
        return $db->queryOne(
            'SELECT * FROM candidato_fad_rrhh_solicitud WHERE id_candidato = :id LIMIT 1',
            ['id' => $idCandidato]
        );
    }

    public static function vincularSolicitud(
        int $idCandidato,
        string $requisitionId,
        ?string $documentId,
        ?string $signerId,
        ?string $signingUrl,
        ?int $idUsuario = null
    ): array {
        $requisitionId = trim($requisitionId);
        if ($idCandidato <= 0 || $requisitionId === '') {
            throw new \InvalidArgumentException('Candidato y requisition_id son obligatorios.');
        }

        self::preparar($idCandidato, $idUsuario);
        $db = new Database();
        $now = self::now();
        $db->CRUD(
            "UPDATE candidato_fad_rrhh_solicitud
             SET requisition_id = :requisition_id,
                 document_id = :document_id,
                 signer_id = :signer_id,
                 signing_url = :signing_url,
                 estatus = :estatus,
                 enviado_en = COALESCE(enviado_en, :enviado_en),
                 actualizado_por = :actualizado_por,
                 actualizado_en = :actualizado_en,
                 ultimo_error = NULL
             WHERE id_candidato = :id_candidato",
            [
                'requisition_id' => substr($requisitionId, 0, 120),
                'document_id' => self::nullable($documentId, 120),
                'signer_id' => self::nullable($signerId, 120),
                'signing_url' => self::nullable($signingUrl, 700),
                'estatus' => self::STATUS_PENDING,
                'enviado_en' => $now,
                'actualizado_por' => $idUsuario,
                'actualizado_en' => $now,
                'id_candidato' => $idCandidato,
            ]
        );
        return self::obtenerPorCandidato($idCandidato) ?? [];
    }

    public static function guardarProgreso(
        int $idCandidato,
        ?string $documentId,
        ?string $signerId,
        ?int $idUsuario = null
    ): array {
        self::preparar($idCandidato, $idUsuario);
        $db = new Database();
        $db->CRUD(
            "UPDATE candidato_fad_rrhh_solicitud
             SET document_id = COALESCE(:document_id, document_id),
                 signer_id = COALESCE(:signer_id, signer_id),
                 actualizado_por = :actualizado_por,
                 actualizado_en = :actualizado_en,
                 ultimo_error = NULL
             WHERE id_candidato = :id_candidato",
            [
                'document_id' => self::nullable($documentId, 120),
                'signer_id' => self::nullable($signerId, 120),
                'actualizado_por' => $idUsuario,
                'actualizado_en' => self::now(),
                'id_candidato' => $idCandidato,
            ]
        );
        return self::obtenerPorCandidato($idCandidato) ?? [];
    }

    public static function guardarContextoFirma(
        int $idCandidato,
        string $templateCode,
        string $companyCode,
        ?string $workerSignerId,
        ?string $legalSignerId,
        ?string $workerSigningUrl = null,
        ?string $legalSigningUrl = null,
        ?int $idUsuario = null
    ): array {
        self::preparar($idCandidato, $idUsuario);
        $db = new Database();
        $db->CRUD(
            "UPDATE candidato_fad_rrhh_solicitud
             SET template_code = :template_code,
                 company_code = :company_code,
                 signer_id = COALESCE(:worker_signer_id, signer_id),
                 worker_signer_id = COALESCE(:worker_signer_id, worker_signer_id),
                 legal_signer_id = COALESCE(:legal_signer_id, legal_signer_id),
                 signing_url = COALESCE(:worker_signing_url, signing_url),
                 legal_signing_url = COALESCE(:legal_signing_url, legal_signing_url),
                 actualizado_por = :actualizado_por,
                 actualizado_en = :actualizado_en,
                 ultimo_error = NULL
             WHERE id_candidato = :id_candidato",
            [
                'template_code' => substr(strtoupper(trim($templateCode)), 0, 80),
                'company_code' => substr(strtoupper(trim($companyCode)), 0, 40),
                'worker_signer_id' => self::nullable($workerSignerId, 120),
                'legal_signer_id' => self::nullable($legalSignerId, 120),
                'worker_signing_url' => self::nullable($workerSigningUrl, 700),
                'legal_signing_url' => self::nullable($legalSigningUrl, 700),
                'actualizado_por' => $idUsuario,
                'actualizado_en' => self::now(),
                'id_candidato' => $idCandidato,
            ]
        );
        return self::obtenerPorCandidato($idCandidato) ?? [];
    }

    public static function actualizarEstado(
        int $idCandidato,
        string $status,
        ?string $signedPdfPath = null,
        ?string $signedPdfSha256 = null,
        ?string $fadPath = null,
        ?string $error = null,
        ?int $idUsuario = null
    ): array {
        $allowed = [
            self::STATUS_DRAFT,
            self::STATUS_PENDING,
            self::STATUS_SIGNED,
            self::STATUS_CANCELLED,
            self::STATUS_EXPIRED,
            self::STATUS_REJECTED,
            self::STATUS_ERROR,
        ];
        $status = strtoupper(trim($status));
        if (!in_array($status, $allowed, true)) {
            throw new \InvalidArgumentException('Estado FAD no reconocido.');
        }

        self::preparar($idCandidato, $idUsuario);
        $db = new Database();
        $now = self::now();
        $db->CRUD(
            "UPDATE candidato_fad_rrhh_solicitud
             SET estatus = :estatus,
                 pdf_firmado_ruta = COALESCE(:pdf_ruta, pdf_firmado_ruta),
                 pdf_firmado_sha256 = COALESCE(:pdf_hash, pdf_firmado_sha256),
                 fad_archivo_ruta = COALESCE(:fad_ruta, fad_archivo_ruta),
                 ultimo_error = :ultimo_error,
                 intentos_sync = intentos_sync + 1,
                 ultima_sync_en = :ultima_sync_en,
                 firmado_en = CASE WHEN :estatus_firmado = 'SIGNED' THEN COALESCE(firmado_en, :firmado_en) ELSE firmado_en END,
                 actualizado_por = :actualizado_por,
                 actualizado_en = :actualizado_en
             WHERE id_candidato = :id_candidato",
            [
                'estatus' => $status,
                'pdf_ruta' => self::nullable($signedPdfPath, 700),
                'pdf_hash' => self::validHash($signedPdfSha256),
                'fad_ruta' => self::nullable($fadPath, 700),
                'ultimo_error' => self::nullable($error, 1000),
                'ultima_sync_en' => $now,
                'estatus_firmado' => $status,
                'firmado_en' => $now,
                'actualizado_por' => $idUsuario,
                'actualizado_en' => $now,
                'id_candidato' => $idCandidato,
            ]
        );
        return self::obtenerPorCandidato($idCandidato) ?? [];
    }

    private static function nullable(?string $value, int $max): ?string
    {
        $value = trim((string) $value);
        return $value === '' ? null : substr($value, 0, $max);
    }

    private static function validHash(?string $hash): ?string
    {
        $hash = strtolower(trim((string) $hash));
        return preg_match('/^[a-f0-9]{64}$/', $hash) ? $hash : null;
    }
}
