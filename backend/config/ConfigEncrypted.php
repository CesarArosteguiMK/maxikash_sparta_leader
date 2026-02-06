<?php

/**
 * Carga de API keys desde BD cifradas (Opción B).
 * Cifrado: AES-256-CBC con clave maestra (derivada por SHA-256).
 * La clave maestra debe estar en variable de entorno CONFIG_MASTER_KEY o en config.php.
 */

if (!function_exists('config_encrypted_decrypt')) {
    /**
     * Descifra un valor guardado (base64: IV 16 bytes + ciphertext).
     *
     * @param string $masterKey Clave maestra
     * @param string $encryptedBase64 Valor cifrado en base64 (IV + ciphertext)
     * @return string Valor en claro o vacío si falla
     */
    function config_encrypted_decrypt($masterKey, $encryptedBase64)
    {
        if ($masterKey === '' || $encryptedBase64 === '') {
            return '';
        }
        $raw = base64_decode($encryptedBase64, true);
        if ($raw === false || strlen($raw) < 17) {
            return '';
        }
        $iv = substr($raw, 0, 16);
        $ciphertext = substr($raw, 16);
        $key = hash('sha256', $masterKey, true);
        $decrypted = @openssl_decrypt($ciphertext, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
        return $decrypted !== false ? $decrypted : '';
    }
}

if (!function_exists('config_encrypted_encrypt')) {
    /**
     * Cifra un valor para guardar en BD (base64: IV 16 bytes + ciphertext).
     *
     * @param string $masterKey Clave maestra
     * @param string $plainValue Valor en claro
     * @return string Valor cifrado en base64 o vacío si falla
     */
    function config_encrypted_encrypt($masterKey, $plainValue)
    {
        if ($masterKey === '' || $plainValue === '') {
            return '';
        }
        $key = hash('sha256', $masterKey, true);
        $iv = random_bytes(16);
        $ciphertext = @openssl_encrypt($plainValue, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
        if ($ciphertext === false) {
            return '';
        }
        return base64_encode($iv . $ciphertext);
    }
}

if (!function_exists('config_encrypted_load_from_db')) {
    /**
     * Carga todas las claves desde config_api y las descifra.
     *
     * @param string $masterKey Clave maestra
     * @return array [ clave => valor_descifrado, ... ]
     */
    function config_encrypted_load_from_db($masterKey)
    {
        if ($masterKey === '') {
            return [];
        }
        try {
            $db = new \Core\Database();
            $rows = $db->queryAll('SELECT clave, valor_cifrado FROM config_api');
        } catch (\Throwable $e) {
            return [];
        }
        $out = [];
        foreach ($rows as $row) {
            $clave = $row['clave'] ?? '';
            $cifrado = $row['valor_cifrado'] ?? '';
            if ($clave !== '') {
                $out[$clave] = config_encrypted_decrypt($masterKey, $cifrado);
            }
        }
        return $out;
    }
}
