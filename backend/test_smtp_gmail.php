<?php
/**
 * Prueba SMTP Gmail desde línea de comandos.
 * Uso: php backend/test_smtp_gmail.php
 * Lee .env desde la raíz del proyecto (un nivel arriba de backend/).
 * Si este script también da 535, el problema es la cuenta Gmail o Google Workspace, no el código.
 */

$root = dirname(__DIR__);
$envFile = $root . '/.env';
if (!is_file($envFile)) {
    echo "No se encontró .env en: $envFile\n";
    exit(1);
}

$env = [];
foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
    $line = trim($line);
    if ($line === '' || strpos($line, '#') === 0) continue;
    $eq = strpos($line, '=');
    if ($eq === false) continue;
    $key = trim(substr($line, 0, $eq));
    if (strpos($key, 'MAIL_') !== 0) continue;
    $value = trim(str_replace(["\r", "\n"], '', substr($line, $eq + 1)));
    if (preg_match('/^["\'](.+)["\']\s*$/s', $value, $m)) $value = trim($m[1]);
    $env[$key] = $value;
}

$host = $env['MAIL_SMTP_HOST'] ?? 'smtp.gmail.com';
$port = (int)($env['MAIL_SMTP_PORT'] ?? 587);
$secure = strtolower($env['MAIL_SMTP_SECURE'] ?? 'tls');
$user = trim($env['MAIL_SMTP_USER'] ?? '');
$passRaw = $env['MAIL_SMTP_PASS'] ?? '';
$pass = trim(preg_replace('/\s+/', '', $passRaw));
$pass = preg_replace('/[^\x20-\x7E]/', '', $pass);

if ($user === '' || $pass === '') {
    echo "Faltan MAIL_SMTP_USER o MAIL_SMTP_PASS en .env\n";
    exit(1);
}

if ($host === 'smtp.gmail.com' && ($port === 587 || $secure === 'tls')) {
    $port = 465;
    $secure = 'ssl';
}

echo "Conectando a $host:$port ($secure), usuario: $user, pass_len: " . strlen($pass) . "\n";

require $root . '/backend/libs/PHPMailer/vendor/autoload.php';

$mail = new \PHPMailer\PHPMailer\PHPMailer(true);
$mail->SMTPDebug = 2;
$mail->Debugoutput = function ($str) { echo $str; };
$mail->isSMTP();
$mail->Host = $host;
$mail->SMTPAuth = true;
$mail->AuthType = 'LOGIN';
$mail->Username = $user;
$mail->Password = $pass;
$mail->Port = $port;
$mail->SMTPSecure = $secure === 'ssl' ? \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS : \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
if ($host === 'smtp.gmail.com') {
    $mail->SMTPOptions = ['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]];
}
$mail->setFrom($user, 'Prueba');
$mail->addAddress($user, 'Prueba');
$mail->Subject = 'Prueba SMTP ' . date('Y-m-d H:i:s');
$mail->Body = 'Si recibes este correo, SMTP está bien configurado.';

try {
    $mail->send();
    echo "\nOK: Correo enviado.\n";
    exit(0);
} catch (Exception $e) {
    echo "\nError: " . $e->getMessage() . "\n";
    exit(1);
}
