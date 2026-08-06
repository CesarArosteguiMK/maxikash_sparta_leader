<?php

namespace Services;

use Models\Candidatos;

/** Consolida los datos contractuales sin modificar el expediente ni FAD. */
final class FadRrhhContractDataService
{
    private const REQUIRED = [
        'full_name', 'nationality', 'sex', 'age', 'marital_status', 'rfc', 'curp', 'nss',
        'address', 'email', 'phone', 'emergency_contacts', 'clabe', 'account_number', 'bank',
        'position', 'activities', 'salary', 'start_date', 'beneficiaries',
    ];

    public function forCandidate(int $idCandidato): array
    {
        $candidateResult = Candidatos::getById($idCandidato);
        if (empty($candidateResult['success']) || empty($candidateResult['datos'])) {
            throw new \RuntimeException('Candidato no encontrado.');
        }
        $documents = Candidatos::getDocumentosYVerificacion($idCandidato, false);
        return self::consolidate($candidateResult['datos'], $documents);
    }

    public static function consolidate(array $candidate, array $documentBundle, array $overrides = []): array
    {
        $sources = [];
        $documentSources = self::documentSources($documentBundle);
        $find = static function (array $aliases) use ($documentSources): array {
            foreach ($documentSources as $source) {
                $value = self::findRecursive($source['data'], $aliases);
                if (!self::isEmpty($value)) {
                    return [$value, $source['label']];
                }
            }
            return ['', 'pendiente'];
        };
        $candidateValue = static function (string $field, $value) use (&$sources) {
            if (!self::isEmpty($value)) {
                $sources[$field] = 'seleccion_personal';
                return $value;
            }
            return '';
        };

        $fullName = trim(implode(' ', array_filter([
            $candidate['nombres'] ?? '', $candidate['segundo_nombre'] ?? '',
            $candidate['apellidop'] ?? '', $candidate['apellidom'] ?? '',
        ], static fn($v) => trim((string) $v) !== '')));
        $street = trim(implode(' ', array_filter([$candidate['domicilio_calle_texto'] ?? '', $candidate['domicilio_num_exterior'] ?? ''])));
        $address = $street === '' ? '' : trim(implode(', ', array_filter([
            $street,
            !empty($candidate['domicilio_num_interior']) ? 'Int. ' . $candidate['domicilio_num_interior'] : '',
            $candidate['nombre_div_nivel3'] ?? '', $candidate['nombre_div_nivel2'] ?? '',
            $candidate['nombre_div_nivel1'] ?? '', $candidate['codigo_postal'] ?? '',
        ], static fn($v) => trim((string) $v) !== '')));

        $data = [
            'full_name' => $candidateValue('full_name', $fullName),
            'email' => $candidateValue('email', trim((string) ($candidate['email'] ?? ''))),
            'phone' => $candidateValue('phone', trim((string) ($candidate['telefono'] ?? ''))),
            'address' => $candidateValue('address', $address),
            'position' => $candidateValue('position', trim((string) ($candidate['nombre_puesto'] ?? ''))),
            'salary' => $candidateValue('salary', $candidate['sueldo_bruto'] ?? ''),
            'start_date' => $candidateValue('start_date', trim((string) ($candidate['fecha_ingreso_programada'] ?? ''))),
            'nationality' => '', 'birth_date' => '', 'sex' => '', 'age' => '',
            'marital_status' => '', 'rfc' => '', 'curp' => '', 'nss' => '',
            'emergency_contacts' => '', 'clabe' => '', 'account_number' => '', 'bank' => '',
            'activities' => [], 'beneficiaries' => [],
        ];
        $aliases = [
            'nationality' => ['nacionalidad'],
            'birth_date' => ['fecha_nacimiento', 'fecha_de_nacimiento'],
            'sex' => ['sexo', 'genero'],
            'age' => ['edad'],
            'marital_status' => ['estado_civil'],
            'rfc' => ['rfc'], 'curp' => ['curp'], 'nss' => ['nss', 'nss_extraido', 'nss_lectura_ia', 'nss_principal', 'numero_seguro_social'],
            'address' => ['domicilio', 'direccion'],
            'email' => ['correo_electronico', 'email', 'correo'],
            'phone' => ['telefono', 'celular'],
            'emergency_contacts' => ['contactos_emergencia', 'contacto_emergencia'],
            'clabe' => ['clabe', 'clabe_interbancaria'],
            'account_number' => ['numero_cuenta', 'cuenta'],
            'bank' => ['banco', 'banco_detectado'],
            'position' => ['puesto_solicitado', 'puesto'],
            'activities' => ['actividades', 'funciones', 'responsabilidades'],
            'beneficiaries' => ['beneficiarios'],
        ];
        foreach ($aliases as $field => $keys) {
            if (!self::isEmpty($data[$field] ?? null)) {
                continue;
            }
            [$value, $source] = $find($keys);
            if (!self::isEmpty($value)) {
                $data[$field] = $value;
                $sources[$field] = $source;
            }
        }

        $data['curp'] = strtoupper(trim((string) $data['curp']));
        $data['rfc'] = strtoupper(trim((string) $data['rfc']));
        $data['nss'] = preg_replace('/\D+/', '', (string) $data['nss']) ?? '';
        $data['clabe'] = preg_replace('/\D+/', '', (string) $data['clabe']) ?? '';
        $data['sex'] = self::normalizeSex($data['sex']);
        $data['activities'] = self::normalizeLines($data['activities']);
        $data['emergency_contacts'] = implode('; ', self::normalizeLines($data['emergency_contacts']));
        $data['beneficiaries'] = self::normalizeBeneficiaries($data['beneficiaries']);

        $curpDerived = self::deriveCurp((string) $data['curp']);
        foreach (['birth_date', 'sex', 'age'] as $field) {
            if (self::isEmpty($data[$field]) && !self::isEmpty($curpDerived[$field] ?? null)) {
                $data[$field] = $curpDerived[$field];
                $sources[$field] = 'derivado_curp';
            }
        }
        if (!self::isEmpty($data['birth_date']) && self::isEmpty($data['age'])) {
            try {
                $data['age'] = (int) (new \DateTimeImmutable((string) $data['birth_date']))->diff(new \DateTimeImmutable('today'))->y;
                $sources['age'] = 'derivado_fecha_nacimiento';
            } catch (\Throwable $e) {
            }
        }

        foreach ($overrides as $field => $value) {
            if (array_key_exists($field, $data)) {
                $data[$field] = $field === 'beneficiaries' ? self::normalizeBeneficiaries($value)
                    : ($field === 'activities' ? self::normalizeLines($value) : $value);
                $sources[$field] = 'captura_manual';
            }
        }
        $validation = self::validate($data);
        return [
            'data' => $data,
            'sources' => $sources,
            'missing' => $validation['missing'],
            'errors' => $validation['errors'],
            'ready' => !$validation['missing'] && !$validation['errors'],
        ];
    }

    public static function validate(array $data): array
    {
        $missing = [];
        foreach (self::REQUIRED as $field) {
            if (self::isEmpty($data[$field] ?? null)) {
                $missing[] = $field;
            }
        }
        $errors = [];
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Correo no válido.';
        if (!empty($data['phone']) && strlen(preg_replace('/\D+/', '', (string) $data['phone']) ?? '') < 10) $errors['phone'] = 'Teléfono incompleto.';
        if (!empty($data['curp']) && !preg_match('/^[A-Z]{4}\d{6}[HM][A-Z]{5}[A-Z0-9]\d$/', strtoupper((string) $data['curp']))) $errors['curp'] = 'CURP no válida.';
        if (!empty($data['rfc']) && !preg_match('/^[A-ZÑ&]{3,4}\d{6}[A-Z0-9]{3}$/u', strtoupper((string) $data['rfc']))) $errors['rfc'] = 'RFC no válido.';
        if (!empty($data['nss']) && strlen(preg_replace('/\D+/', '', (string) $data['nss']) ?? '') !== 11) $errors['nss'] = 'El NSS debe tener 11 dígitos.';
        if (!empty($data['clabe']) && strlen(preg_replace('/\D+/', '', (string) $data['clabe']) ?? '') !== 18) $errors['clabe'] = 'La CLABE debe tener 18 dígitos.';
        if (!empty($data['age']) && ((int) $data['age'] < 18 || (int) $data['age'] > 99)) $errors['age'] = 'La edad debe estar entre 18 y 99 años.';
        if (!empty($data['salary'])) {
            $salary = str_replace([',', '$', ' '], '', (string) $data['salary']);
            if (!is_numeric($salary) || (float) $salary <= 0) $errors['salary'] = 'El sueldo bruto no es válido.';
        }
        if (!empty($data['start_date'])) {
            $date = \DateTimeImmutable::createFromFormat('!Y-m-d', (string) $data['start_date']);
            if (!$date || $date->format('Y-m-d') !== (string) $data['start_date']) $errors['start_date'] = 'La fecha de ingreso no es válida.';
        }
        if (!empty($data['activities']) && count(self::normalizeLines($data['activities'])) < 3) $errors['activities'] = 'Captura al menos tres actividades.';
        $beneficiaries = self::normalizeBeneficiaries($data['beneficiaries'] ?? []);
        if ($beneficiaries) {
            foreach ($beneficiaries as $beneficiary) {
                if ($beneficiary['name'] === '' || $beneficiary['relationship'] === '' || (float) $beneficiary['percentage'] <= 0) {
                    $errors['beneficiaries'] = 'Completa nombre, parentesco y porcentaje de cada beneficiario.';
                    break;
                }
            }
            $total = array_sum(array_map(static fn($row) => (float) $row['percentage'], $beneficiaries));
            if (abs($total - 100) > 0.01) $errors['beneficiaries'] = 'Los porcentajes de beneficiarios deben sumar 100%.';
        }
        return ['missing' => $missing, 'errors' => $errors];
    }

    private static function documentSources(array $bundle): array
    {
        $sources = [];
        if (is_array($bundle['verificacion'] ?? null)) $sources[] = ['label' => 'validacion_expediente', 'data' => $bundle['verificacion']];
        foreach (($bundle['documentos'] ?? []) as $doc) {
            $label = self::sourceLabel((string) ($doc['tipo_documento'] ?? 'documento'));
            foreach (['verificacion_calidad', 'verificacion_fiscal'] as $key) {
                if (is_array($doc[$key] ?? null)) $sources[] = ['label' => $label, 'data' => $doc[$key]];
            }
        }
        return $sources;
    }

    private static function sourceLabel(string $type): string
    {
        $type = strtolower(trim($type));
        if (str_contains($type, 'solicitud')) return 'solicitud_interna';
        if (str_contains($type, 'seguro') || $type === 'nss') return 'documento_nss';
        if (str_contains($type, 'fiscal')) return 'constancia_fiscal';
        if (str_contains($type, 'cuenta')) return 'estado_cuenta';
        if (str_contains($type, 'nacimiento')) return 'acta_nacimiento';
        return $type !== '' ? $type : 'documento';
    }

    private static function findRecursive($value, array $aliases)
    {
        if (!is_array($value)) return '';
        $normalized = array_map(static fn($v) => strtolower((string) $v), $aliases);
        foreach ($value as $key => $child) {
            if (in_array(strtolower((string) $key), $normalized, true) && !self::isEmpty($child)) {
                if (!is_array($child)) return $child;
                $nested = self::findRecursive($child, $aliases);
                if (!self::isEmpty($nested)) return $nested;
                foreach (['valor', 'value', 'numero', 'texto', 'extraido'] as $valueKey) {
                    if (!self::isEmpty($child[$valueKey] ?? null) && !is_array($child[$valueKey])) return $child[$valueKey];
                }
                if (array_is_list($child)) return $child;
            }
        }
        foreach ($value as $child) {
            if (is_array($child)) {
                $found = self::findRecursive($child, $aliases);
                if (!self::isEmpty($found)) return $found;
            }
        }
        return '';
    }

    private static function normalizeLines($value): array
    {
        if (is_string($value)) $value = preg_split('/[\r\n;]+/', $value) ?: [];
        if (!is_array($value)) return [];
        $lines = [];
        foreach ($value as $item) {
            if (is_array($item)) $item = $item['nombre'] ?? $item['descripcion'] ?? implode(' ', array_filter(array_map('strval', $item)));
            $item = trim((string) $item);
            if ($item !== '') $lines[] = $item;
        }
        return array_values(array_unique($lines));
    }

    private static function normalizeBeneficiaries($value): array
    {
        if (!is_array($value)) return [];
        $rows = [];
        foreach ($value as $item) {
            if (!is_array($item)) continue;
            $name = trim((string) ($item['name'] ?? $item['nombre'] ?? $item['nombre_completo'] ?? $item['nombre_beneficiario'] ?? $item['beneficiario'] ?? ''));
            $relationship = trim((string) ($item['relationship'] ?? $item['parentesco'] ?? $item['relacion'] ?? ''));
            $percentage = (float) ($item['percentage'] ?? $item['porcentaje'] ?? $item['porcentaje_asignado'] ?? 0);
            if ($name !== '' || $relationship !== '' || $percentage > 0) $rows[] = compact('name', 'relationship', 'percentage');
        }
        return $rows;
    }

    private static function deriveCurp(string $curp): array
    {
        $curp = strtoupper(trim($curp));
        if (!preg_match('/^[A-Z]{4}(\d{2})(\d{2})(\d{2})([HM])/', $curp, $m)) return [];
        $yy = (int) $m[1];
        $current = (int) date('y');
        $year = $yy <= $current ? 2000 + $yy : 1900 + $yy;
        $date = sprintf('%04d-%02d-%02d', $year, (int) $m[2], (int) $m[3]);
        if (!checkdate((int) $m[2], (int) $m[3], $year)) return [];
        $age = (new \DateTimeImmutable($date))->diff(new \DateTimeImmutable('today'))->y;
        return ['birth_date' => $date, 'sex' => $m[4] === 'H' ? 'MASCULINO' : 'FEMENINO', 'age' => $age];
    }

    private static function normalizeSex($value): string
    {
        $value = strtoupper(trim((string) $value));
        if (in_array($value, ['H', 'HOMBRE', 'MASCULINO', 'M'], true)) return 'MASCULINO';
        if (in_array($value, ['M', 'MUJER', 'FEMENINO', 'F'], true)) return 'FEMENINO';
        return $value;
    }

    private static function isEmpty($value): bool
    {
        return $value === null || $value === '' || (is_array($value) && !$value);
    }
}
