<?php

namespace Services;

use Core\Database;

/**
 * Apariencia personal de Leonidas.
 *
 * La preferencia siempre pertenece al actor autenticado. Este servicio no
 * permite elegir otra persona ni alterar el permiso de acceso al agente.
 */
final class LeonidasAppearanceService
{
    public const TEMA_PREDETERMINADO = 'corporativo';

    private Database $db;

    public function __construct(?Database $db = null)
    {
        $this->db = $db ?? new Database();
        $this->asegurarEsquema();
    }

    public static function temas(): array
    {
        return [
            'corporativo' => [
                'id' => 'corporativo',
                'nombre' => 'Corporativo',
                'descripcion' => 'Azul y verde Maxikash con metal plata.',
                'color_principal' => '#0048B7',
                'color_secundario' => '#D2D854',
                'color_metal' => '#D7E0EA',
                'casco_visible' => true,
                'pechera_visible' => true,
            ],
            'clasico' => [
                'id' => 'clasico',
                'nombre' => 'Espartano clásico',
                'descripcion' => 'Carmesí, cuero y bronce.',
                'color_principal' => '#A91E2C',
                'color_secundario' => '#6F4328',
                'color_metal' => '#D8B37B',
                'casco_visible' => true,
                'pechera_visible' => true,
            ],
            'nocturno' => [
                'id' => 'nocturno',
                'nombre' => 'Azul nocturno',
                'descripcion' => 'Azul profundo con acentos eléctricos.',
                'color_principal' => '#111827',
                'color_secundario' => '#4E68D8',
                'color_metal' => '#AEBBC8',
                'casco_visible' => true,
                'pechera_visible' => true,
            ],
            'dorado' => [
                'id' => 'dorado',
                'nombre' => 'Guardia dorada',
                'descripcion' => 'Tonos tierra con metal ceremonial.',
                'color_principal' => '#70430D',
                'color_secundario' => '#EAB308',
                'color_metal' => '#F5D57A',
                'casco_visible' => true,
                'pechera_visible' => true,
            ],
            'bosque' => [
                'id' => 'bosque',
                'nombre' => 'Legión bosque',
                'descripcion' => 'Verde oscuro, cuero y bronce.',
                'color_principal' => '#14532D',
                'color_secundario' => '#8A5A24',
                'color_metal' => '#C0A46B',
                'casco_visible' => true,
                'pechera_visible' => true,
            ],
        ];
    }

    public static function predeterminada(): array
    {
        return self::temas()[self::TEMA_PREDETERMINADO];
    }

    public function obtener(int $personaId): array
    {
        self::validarPersona($personaId);
        $row = $this->db->queryOne(
            'SELECT tema, color_principal, color_secundario, color_metal,
                    casco_visible, pechera_visible, actualizado_en
             FROM leonidas_apariencia_usuario
             WHERE persona_id = :persona_id
             LIMIT 1',
            ['persona_id' => $personaId]
        );

        $apariencia = $row
            ? self::normalizarSolicitud($row)
            : self::predeterminada();
        $apariencia['personalizada'] = (bool) $row;
        $apariencia['actualizado_en'] = $row['actualizado_en'] ?? null;

        return [
            'apariencia' => $apariencia,
            'temas' => array_values(self::temas()),
            'predeterminada' => self::TEMA_PREDETERMINADO,
        ];
    }

    public function guardar(int $personaId, array $payload): array
    {
        self::validarPersona($personaId);
        $apariencia = self::normalizarSolicitud($payload);

        $this->db->CRUD(
            'INSERT INTO leonidas_apariencia_usuario
                (persona_id, tema, color_principal, color_secundario, color_metal,
                 casco_visible, pechera_visible, creado_en, actualizado_en)
             VALUES
                (:persona_id, :tema, :principal, :secundario, :metal,
                 :casco_visible, :pechera_visible, NOW(), NOW())
             ON DUPLICATE KEY UPDATE
                tema = VALUES(tema),
                color_principal = VALUES(color_principal),
                color_secundario = VALUES(color_secundario),
                color_metal = VALUES(color_metal),
                casco_visible = VALUES(casco_visible),
                pechera_visible = VALUES(pechera_visible),
                actualizado_en = NOW()',
            [
                'persona_id' => $personaId,
                'tema' => $apariencia['id'],
                'principal' => $apariencia['color_principal'],
                'secundario' => $apariencia['color_secundario'],
                'metal' => $apariencia['color_metal'],
                'casco_visible' => $apariencia['casco_visible'] ? 1 : 0,
                'pechera_visible' => $apariencia['pechera_visible'] ? 1 : 0,
            ]
        );

        $apariencia['personalizada'] = true;
        $apariencia['actualizado_en'] = date('Y-m-d H:i:s');
        return [
            'mensaje' => 'El vestuario de Leonidas se guardó para tu usuario.',
            'apariencia' => $apariencia,
        ];
    }

    public function restablecer(int $personaId): array
    {
        self::validarPersona($personaId);
        $this->db->CRUD(
            'DELETE FROM leonidas_apariencia_usuario WHERE persona_id = :persona_id',
            ['persona_id' => $personaId]
        );

        $apariencia = self::predeterminada();
        $apariencia['personalizada'] = false;
        $apariencia['actualizado_en'] = null;
        return [
            'mensaje' => 'Leonidas volvió al vestuario corporativo.',
            'apariencia' => $apariencia,
        ];
    }

    public static function normalizarSolicitud(array $payload): array
    {
        $tema = strtolower(trim((string) ($payload['tema'] ?? $payload['id'] ?? self::TEMA_PREDETERMINADO)));
        $temas = self::temas();
        if ($tema !== 'personalizado' && isset($temas[$tema])) {
            $apariencia = $temas[$tema];
            $apariencia['casco_visible'] = self::validarVisibilidad($payload['casco_visible'] ?? true, 'casco');
            $apariencia['pechera_visible'] = self::validarVisibilidad($payload['pechera_visible'] ?? true, 'pechera');
            return $apariencia;
        }
        if ($tema !== 'personalizado') {
            throw new \InvalidArgumentException('El tema de vestuario no está permitido.');
        }

        return [
            'id' => 'personalizado',
            'nombre' => 'Personalizado',
            'descripcion' => 'Paleta elegida por el usuario.',
            'color_principal' => self::validarColor($payload['color_principal'] ?? null, 'principal'),
            'color_secundario' => self::validarColor($payload['color_secundario'] ?? null, 'secundario'),
            'color_metal' => self::validarColor($payload['color_metal'] ?? null, 'metal'),
            'casco_visible' => self::validarVisibilidad($payload['casco_visible'] ?? true, 'casco'),
            'pechera_visible' => self::validarVisibilidad($payload['pechera_visible'] ?? true, 'pechera'),
        ];
    }

    private static function validarVisibilidad($valor, string $pieza): bool
    {
        if (is_bool($valor)) {
            return $valor;
        }
        if ($valor === 1 || $valor === 0 || $valor === '1' || $valor === '0') {
            return (bool) (int) $valor;
        }
        throw new \InvalidArgumentException('La visibilidad de ' . $pieza . ' debe ser verdadera o falsa.');
    }

    private static function validarColor($valor, string $campo): string
    {
        $color = strtoupper(trim((string) $valor));
        if (preg_match('/^#[0-9A-F]{6}$/', $color) !== 1) {
            throw new \InvalidArgumentException('El color ' . $campo . ' debe utilizar el formato hexadecimal #RRGGBB.');
        }
        return $color;
    }

    private static function validarPersona(int $personaId): void
    {
        if ($personaId <= 0) {
            throw new \InvalidArgumentException('No se pudo identificar al usuario de la apariencia.');
        }
    }

    private function asegurarEsquema(): void
    {
        $this->db->CRUD(
            'CREATE TABLE IF NOT EXISTS leonidas_apariencia_usuario (
                persona_id INT NOT NULL,
                tema VARCHAR(32) NOT NULL DEFAULT \'corporativo\',
                color_principal CHAR(7) NOT NULL,
                color_secundario CHAR(7) NOT NULL,
                color_metal CHAR(7) NOT NULL,
                casco_visible TINYINT(1) NOT NULL DEFAULT 1,
                pechera_visible TINYINT(1) NOT NULL DEFAULT 1,
                creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                actualizado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (persona_id),
                KEY idx_leonidas_apariencia_tema (tema)
             ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
        $this->asegurarColumna('casco_visible');
        $this->asegurarColumna('pechera_visible');
    }

    private function asegurarColumna(string $columna): void
    {
        $existente = $this->db->queryOne(
            'SHOW COLUMNS FROM leonidas_apariencia_usuario LIKE :columna',
            ['columna' => $columna]
        );
        if ($existente) {
            return;
        }
        $this->db->CRUD(
            'ALTER TABLE leonidas_apariencia_usuario
             ADD COLUMN `' . $columna . '` TINYINT(1) NOT NULL DEFAULT 1
             AFTER color_metal'
        );
    }
}
