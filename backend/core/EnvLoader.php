<?php

namespace Core;

class EnvLoader
{
    private static $loaded = false;

    public static function load(): void
    {
        if (self::$loaded) {
            return;
        }
        self::$loaded = true;

        $configuredPath = getenv('SPARTA_ENV_FILE') ?: '';
        $defaultPath = 'C:\\xampp\\secure\\sparta_ledger.env';
        $paths = array_values(array_unique(array_filter([$configuredPath, $defaultPath])));

        $path = '';
        foreach ($paths as $candidatePath) {
            if (is_file($candidatePath) && is_readable($candidatePath)) {
                $path = $candidatePath;
                break;
            }
        }

        if ($path === '') {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            $currentValue = getenv($key);
            if ($key === '' || ($currentValue !== false && trim((string) $currentValue) !== '')) {
                continue;
            }

            if (strlen($value) >= 2) {
                $first = $value[0];
                $last = $value[strlen($value) - 1];
                if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                    $value = substr($value, 1, -1);
                }
            }

            $value = str_replace(['\\"', '\\\\'], ['"', '\\'], $value);
            putenv($key . '=' . $value);
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}
