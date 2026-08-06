<?php

namespace Services;

/** Genera el contrato desde el machote autorizado y lo convierte a PDF. */
final class FadRrhhContractGenerator
{
    private const SUPPORTED = ['AMIGO_GENERAL_NUEVO', 'AMIGO_ACTUALIZACION', 'PENSIONAMAX_NUEVO'];

    public function generate(string $templateCode, array $data): array
    {
        $templateCode = strtoupper(trim($templateCode));
        if (!in_array($templateCode, self::SUPPORTED, true)) {
            throw new \RuntimeException('Este tipo de contrato todavía no cuenta con generación automática revisada.');
        }
        $validation = FadRrhhContractDataService::validate($data);
        if ($validation['missing'] || $validation['errors']) {
            throw new \InvalidArgumentException('Completa y corrige los datos contractuales antes de generar el contrato.');
        }
        if ($templateCode === 'AMIGO_ACTUALIZACION') {
            $this->validateUpdateData($data);
        }
        $requiredBeneficiaries = in_array($templateCode, ['AMIGO_GENERAL_NUEVO', 'AMIGO_ACTUALIZACION'], true) ? 2 : 1;
        if (count($data['beneficiaries'] ?? []) !== $requiredBeneficiaries) {
            throw new \InvalidArgumentException(sprintf('Este contrato requiere exactamente %d beneficiario(s).', $requiredBeneficiaries));
        }

        $root = dirname(__DIR__, 2);
        $python = $root . DIRECTORY_SEPARATOR . 'backend' . DIRECTORY_SEPARATOR . 'API' . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'PythonPortable' . DIRECTORY_SEPARATOR . 'python.exe';
        $script = $root . DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR . 'generar_contrato_fad_rrhh.py';
        $export = $root . DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR . 'Export-DocxToPdf.ps1';
        $powershell = getenv('WINDIR') . DIRECTORY_SEPARATOR . 'System32' . DIRECTORY_SEPARATOR . 'WindowsPowerShell' . DIRECTORY_SEPARATOR . 'v1.0' . DIRECTORY_SEPARATOR . 'powershell.exe';
        foreach ([$python, $script, $export, $powershell] as $file) {
            if (!is_file($file)) throw new \RuntimeException('El servidor no cuenta con todos los componentes para generar el contrato.');
        }

        $work = rtrim(sys_get_temp_dir(), '\\/') . DIRECTORY_SEPARATOR . 'sparta_fad_' . bin2hex(random_bytes(10));
        if (!mkdir($work, 0700, true) && !is_dir($work)) throw new \RuntimeException('No fue posible crear el área temporal del contrato.');
        $json = $work . DIRECTORY_SEPARATOR . 'datos.json';
        $docx = $work . DIRECTORY_SEPARATOR . 'contrato.docx';
        $pdf = $work . DIRECTORY_SEPARATOR . 'contrato.pdf';
        $payload = $this->generatorPayload($templateCode, $data);
        if (file_put_contents($json, json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX) === false) {
            $this->removeTree($work);
            throw new \RuntimeException('No fue posible preparar los datos del contrato.');
        }
        try {
            $this->run([$python, $script, '--template', $templateCode, '--data', $json, '--output', $docx], $root, 90);
            $this->convertToPdf($docx, $pdf, $work, $root, $powershell, $export);
            if (!is_file($pdf) || filesize($pdf) < 1000 || file_get_contents($pdf, false, null, 0, 4) !== '%PDF') {
                throw new \RuntimeException('La conversión no produjo un PDF válido.');
            }
            return ['pdf_path' => $pdf, 'work_dir' => $work, 'filename' => 'Contrato_' . $templateCode . '.pdf'];
        } catch (\Throwable $e) {
            $this->removeTree($work);
            throw $e;
        }
    }

    public function cleanup(array $generated): void
    {
        $work = (string) ($generated['work_dir'] ?? '');
        if ($work !== '' && str_starts_with(realpath($work) ?: '', realpath(sys_get_temp_dir()) ?: '__none__')) $this->removeTree($work);
    }

    private function generatorPayload(string $templateCode, array $data): array
    {
        $salary = round((float) str_replace([',', '$', ' '], '', (string) $data['salary']), 2);
        if ($salary <= 0) throw new \InvalidArgumentException('El sueldo bruto debe ser mayor a cero.');
        $payload = [
            'full_name' => mb_strtoupper(trim((string) $data['full_name']), 'UTF-8'),
            'nationality' => mb_strtoupper(trim((string) $data['nationality']), 'UTF-8'),
            'sex' => mb_strtoupper(trim((string) $data['sex']), 'UTF-8'),
            'gender' => strtoupper((string) $data['sex']) === 'FEMENINO' ? 'MUJER' : 'HOMBRE',
            'age' => (int) $data['age'],
            'marital_status' => mb_strtoupper(trim((string) $data['marital_status']), 'UTF-8'),
            'rfc' => strtoupper(trim((string) $data['rfc'])),
            'curp' => strtoupper(trim((string) $data['curp'])),
            'nss' => preg_replace('/\D+/', '', (string) $data['nss']),
            'address' => trim((string) $data['address']),
            'emergency_contacts' => trim((string) $data['emergency_contacts']),
            'clabe' => preg_replace('/\D+/', '', (string) $data['clabe']),
            'account_number' => trim((string) $data['account_number']),
            'bank' => mb_strtoupper(trim((string) $data['bank']), 'UTF-8'),
            'position' => mb_strtoupper(trim((string) $data['position']), 'UTF-8'),
            'activities' => array_values(array_slice(array_filter(array_map('trim', $data['activities'])), 0, 8)),
            'salary' => $salary,
            'salary_words' => $this->numberWords((int) floor($salary)),
            'salary_cents' => (int) round(($salary - floor($salary)) * 100),
            'start_date_text' => $this->dateText((string) $data['start_date']),
            'signature_date_text' => $this->dateText(date('Y-m-d')),
            'beneficiaries' => array_values($data['beneficiaries']),
        ];
        if ($templateCode === 'AMIGO_ACTUALIZACION') {
            $originalSalary = round((float) str_replace([',', '$', ' '], '', (string) $data['original_salary']), 2);
            $payload['original_start_date_text'] = $this->dateText((string) $data['original_start_date']);
            $payload['original_position'] = mb_strtoupper(trim((string) $data['original_position']), 'UTF-8');
            $payload['original_salary'] = $originalSalary;
            $payload['original_salary_words'] = $this->numberWords((int) floor($originalSalary));
            $payload['original_salary_cents'] = (int) round(($originalSalary - floor($originalSalary)) * 100);
        }
        return $payload;
    }

    private function validateUpdateData(array $data): void
    {
        foreach (['original_start_date', 'original_position', 'original_salary'] as $field) {
            if (!isset($data[$field]) || trim((string) $data[$field]) === '') {
                throw new \InvalidArgumentException('Completa los datos originales de antigüedad, puesto y sueldo del colaborador.');
            }
        }
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', (string) $data['original_start_date']);
        if (!$date || $date->format('Y-m-d') !== (string) $data['original_start_date']) {
            throw new \InvalidArgumentException('La fecha original de ingreso no es válida.');
        }
        if ($date > new \DateTimeImmutable((string) $data['start_date'])) {
            throw new \InvalidArgumentException('La fecha original de ingreso no puede ser posterior a la fecha vigente.');
        }
        $salary = str_replace([',', '$', ' '], '', (string) $data['original_salary']);
        if (!is_numeric($salary) || (float) $salary <= 0) {
            throw new \InvalidArgumentException('El sueldo bruto original no es válido.');
        }
    }

    private function run(array $command, string $cwd, int $timeout): void
    {
        $pipes = [];
        $process = proc_open($command, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, $cwd, null, ['bypass_shell' => true]);
        if (!is_resource($process)) throw new \RuntimeException('No fue posible iniciar el generador contractual.');
        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);
        $stdout = $stderr = '';
        $started = time();
        do {
            $stdout .= stream_get_contents($pipes[1]);
            $stderr .= stream_get_contents($pipes[2]);
            $status = proc_get_status($process);
            if (!$status['running']) break;
            if ((time() - $started) > $timeout) {
                proc_terminate($process);
                throw new \RuntimeException('La generación del contrato excedió el tiempo permitido.');
            }
            usleep(100000);
        } while (true);
        $stdout .= stream_get_contents($pipes[1]);
        $stderr .= stream_get_contents($pipes[2]);
        fclose($pipes[1]); fclose($pipes[2]);
        $exit = proc_close($process);
        if ($exit !== 0) {
            $detail = trim($stderr !== '' ? $stderr : $stdout);
            throw new \RuntimeException('No se pudo generar el contrato' . ($detail !== '' ? ': ' . mb_substr($detail, 0, 700) : '.'));
        }
    }

    private function convertToPdf(
        string $docx,
        string $pdf,
        string $work,
        string $root,
        string $powershell,
        string $wordExporter
    ): void {
        $errors = [];
        try {
            $this->run([
                $powershell, '-NoProfile', '-NonInteractive', '-ExecutionPolicy', 'Bypass',
                '-File', $wordExporter, '-InputPath', $docx, '-OutputPath', $pdf,
            ], $root, 120);
            return;
        } catch (\Throwable $e) {
            $errors[] = 'Microsoft Word no está disponible para la cuenta que ejecuta Sparta.';
            @unlink($pdf);
        }

        $libreOffice = $this->libreOfficeExecutable();
        if ($libreOffice !== null) {
            $profile = $work . DIRECTORY_SEPARATOR . 'libreoffice_profile';
            if (!mkdir($profile, 0700, true) && !is_dir($profile)) {
                throw new \RuntimeException('No fue posible crear el perfil temporal de LibreOffice.');
            }
            $profileUri = 'file:///' . str_replace(
                ['\\', ' '],
                ['/', '%20'],
                ltrim($profile, '\\/')
            );
            try {
                $this->run([
                    $libreOffice,
                    '--headless',
                    '-env:UserInstallation=' . $profileUri,
                    '--convert-to', 'pdf',
                    '--outdir', $work,
                    $docx,
                ], $root, 120);
                if (is_file($pdf)) return;
                $errors[] = 'LibreOffice terminó sin producir el PDF esperado.';
            } catch (\Throwable $e) {
                $errors[] = 'LibreOffice no pudo convertir el contrato.';
            }
        } else {
            $errors[] = 'LibreOffice no está instalado ni configurado.';
        }

        throw new \RuntimeException(
            'No fue posible convertir el contrato DOCX a PDF. ' . implode(' ', $errors)
        );
    }

    private function libreOfficeExecutable(): ?string
    {
        $configured = trim((string) getenv('FAD_RRHH_LIBREOFFICE_BIN'));
        $root = dirname(__DIR__, 2);
        $candidates = array_filter([
            $configured,
            $root . '\\backend\\tools\\LibreOfficePortable\\App\\libreoffice\\program\\soffice.exe',
            $root . '\\backend\\tools\\LibreOfficePortable\\App\\LibreOffice\\program\\soffice.exe',
            'C:\\Program Files\\LibreOffice\\program\\soffice.exe',
            'C:\\Program Files (x86)\\LibreOffice\\program\\soffice.exe',
        ]);
        foreach ($candidates as $candidate) {
            if (is_file($candidate)) return $candidate;
        }
        return null;
    }

    private function dateText(string $date): string
    {
        try { $d = new \DateTimeImmutable($date); } catch (\Throwable $e) { throw new \InvalidArgumentException('La fecha de ingreso no es válida.'); }
        $months = [1 => 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
        return $d->format('j') . ' de ' . $months[(int) $d->format('n')] . ' de ' . $d->format('Y');
    }

    private function numberWords(int $number): string
    {
        if ($number === 0) return 'CERO';
        if ($number < 0 || $number > 999999999) throw new \InvalidArgumentException('El sueldo está fuera del rango permitido.');
        $parts = [];
        $millions = intdiv($number, 1000000);
        if ($millions) { $parts[] = $millions === 1 ? 'UN MILLON' : $this->underThousand($millions) . ' MILLONES'; $number %= 1000000; }
        $thousands = intdiv($number, 1000);
        if ($thousands) { $parts[] = $thousands === 1 ? 'MIL' : $this->underThousand($thousands) . ' MIL'; $number %= 1000; }
        if ($number) $parts[] = $this->underThousand($number);
        return implode(' ', $parts);
    }

    private function underThousand(int $n): string
    {
        $ones = ['', 'UNO', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE', 'DIEZ', 'ONCE', 'DOCE', 'TRECE', 'CATORCE', 'QUINCE', 'DIECISEIS', 'DIECISIETE', 'DIECIOCHO', 'DIECINUEVE', 'VEINTE', 'VEINTIUNO', 'VEINTIDOS', 'VEINTITRES', 'VEINTICUATRO', 'VEINTICINCO', 'VEINTISEIS', 'VEINTISIETE', 'VEINTIOCHO', 'VEINTINUEVE'];
        $hundreds = ['', 'CIENTO', 'DOSCIENTOS', 'TRESCIENTOS', 'CUATROCIENTOS', 'QUINIENTOS', 'SEISCIENTOS', 'SETECIENTOS', 'OCHOCIENTOS', 'NOVECIENTOS'];
        if ($n === 100) return 'CIEN';
        $out = [];
        if ($n >= 100) { $out[] = $hundreds[intdiv($n, 100)]; $n %= 100; }
        if ($n < 30) { if ($n) $out[] = $ones[$n]; }
        else { $tens = [3 => 'TREINTA', 4 => 'CUARENTA', 5 => 'CINCUENTA', 6 => 'SESENTA', 7 => 'SETENTA', 8 => 'OCHENTA', 9 => 'NOVENTA']; $out[] = $tens[intdiv($n, 10)] . (($n % 10) ? ' Y ' . $ones[$n % 10] : ''); }
        return implode(' ', $out);
    }

    private function removeTree(string $directory): void
    {
        if (!is_dir($directory)) return;
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($iterator as $item) $item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
        @rmdir($directory);
    }
}
