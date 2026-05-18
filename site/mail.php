<?php
/**
 * Обработчик заявок для shared-хостинга Beget.
 *
 * Два режима:
 *   - MAIL_MODE=mail   — встроенная PHP mail() (по умолчанию).
 *                       Лимит Beget: 30 писем/мин.
 *   - MAIL_MODE=smtp   — отправка через smtp.beget.com:465 (SSL).
 *                       Надёжнее, требует логина почтового ящика на домене.
 *
 * Конфигурация — через mail.config.php (создаётся рядом, в репо не коммитим).
 * Пример mail.config.php:
 *
 *   <?php
 *   return [
 *     'mode'        => 'mail',                // 'mail' | 'smtp'
 *     'to'          => 'kisuke43@gmail.com',  // получатель заявок
 *     'from_email'  => 'noreply@kinodvk.ru',  // должен быть на вашем домене
 *     'from_name'   => 'Кинопремия — форма',
 *     'subject'     => 'Новая заявка — Кинопремия',
 *     // только для mode=smtp:
 *     'smtp_host'   => 'smtp.beget.com',
 *     'smtp_port'   => 465,
 *     'smtp_secure' => 'ssl',                 // 'ssl' (465) | 'tls' (587/2525)
 *     'smtp_user'   => 'noreply@kinodvk.ru',
 *     'smtp_pass'   => 'СЕКРЕТ',
 *   ];
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

// --- защита от флуда: один IP не чаще 1 раза в 10 сек, не более 5 в минуту ---
$ip      = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$ip      = trim(explode(',', $ip)[0]);
$tmpDir  = sys_get_temp_dir() . '/kinodvk_rl';
if (!is_dir($tmpDir)) @mkdir($tmpDir, 0700, true);
$rlFile  = $tmpDir . '/' . md5($ip) . '.log';
$now     = time();
$hits    = is_file($rlFile) ? array_map('intval', file($rlFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)) : [];
$hits    = array_values(array_filter($hits, fn($t) => $t > $now - 60));
if (count($hits) > 0 && ($now - end($hits)) < 10) {
    http_response_code(429);
    echo json_encode(['ok' => false, 'error' => 'Слишком часто, попробуйте ещё раз через несколько секунд']);
    exit;
}
if (count($hits) >= 5) {
    http_response_code(429);
    echo json_encode(['ok' => false, 'error' => 'Слишком много заявок с этого IP, подождите минуту']);
    exit;
}
$hits[] = $now;
@file_put_contents($rlFile, implode("\n", $hits));

// --- читаем JSON или form-urlencoded ---
$raw  = file_get_contents('php://input') ?: '';
$data = [];
if ($raw !== '' && stripos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false) {
    $decoded = json_decode($raw, true);
    if (is_array($decoded)) $data = $decoded;
} else {
    $data = $_POST;
}

$name    = trim((string)($data['name']    ?? ''));
$phone   = trim((string)($data['phone']   ?? ''));
$company = trim((string)($data['company'] ?? '')); // honeypot
$page    = trim((string)($data['page']    ?? '/'));

// honeypot: боты заполнят, людям он скрыт — отвечаем «ok», но письмо не шлём
if ($company !== '') {
    echo json_encode(['ok' => true]);
    exit;
}

$name  = mb_substr($name, 0, 80);
$phone = mb_substr($phone, 0, 32);

if (mb_strlen($name) < 2) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Имя слишком короткое']);
    exit;
}
if (strlen(preg_replace('/\D+/', '', $phone) ?? '') < 6) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Некорректный телефон']);
    exit;
}

// --- конфигурация ---
$config = is_file(__DIR__ . '/mail.config.php')
    ? require __DIR__ . '/mail.config.php'
    : [];
$cfg = array_merge([
    'mode'        => 'mail',
    'to'          => 'kisuke43@gmail.com',
    'from_email'  => 'noreply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost'),
    'from_name'   => 'Кинопремия — форма',
    'subject'     => 'Новая заявка — Кинопремия',
    'smtp_host'   => 'smtp.beget.com',
    'smtp_port'   => 465,
    'smtp_secure' => 'ssl',
    'smtp_user'   => '',
    'smtp_pass'   => '',
], is_array($config) ? $config : []);

// --- текст письма ---
$ts = (new DateTime('now', new DateTimeZone('Asia/Vladivostok')))->format('d.m.Y H:i');
$ua = mb_substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 200);

$plain  = "Новая заявка — Дальневосточная Кинопремия\n";
$plain .= "----------------------------------------\n";
$plain .= "Имя:       {$name}\n";
$plain .= "Телефон:   {$phone}\n";
$plain .= "Страница:  {$page}\n";
$plain .= "Время:     {$ts} (Влд)\n";
$plain .= "IP:        {$ip}\n";
$plain .= "UA:        {$ua}\n";

$esc  = fn($s) => htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$html = '<h2 style="font-family:Arial,sans-serif">🎬 Новая заявка — Кинопремия</h2>'
      . '<table cellpadding="6" style="font-family:Arial,sans-serif;font-size:14px;border-collapse:collapse">'
      . '<tr><td><b>Имя</b></td><td>' . $esc($name) . '</td></tr>'
      . '<tr><td><b>Телефон</b></td><td>' . $esc($phone) . '</td></tr>'
      . '<tr><td><b>Страница</b></td><td>' . $esc($page) . '</td></tr>'
      . '<tr><td><b>Время</b></td><td>' . $esc($ts) . ' (Влд)</td></tr>'
      . '<tr><td><b>IP</b></td><td>' . $esc($ip) . '</td></tr>'
      . '<tr><td><b>UA</b></td><td>' . $esc($ua) . '</td></tr>'
      . '</table>';

$ok      = false;
$errText = '';

try {
    if (strtolower($cfg['mode']) === 'smtp') {
        $ok = sendSmtp($cfg, $plain, $html);
    } else {
        $ok = sendMail($cfg, $plain);
    }
} catch (Throwable $e) {
    $errText = $e->getMessage();
    error_log('[kinodvk/mail] ' . $errText);
}

if (!$ok) {
    http_response_code(502);
    echo json_encode(['ok' => false, 'error' => 'Не удалось отправить заявку'.($errText ? ': '.$errText : '')]);
    exit;
}

echo json_encode(['ok' => true]);
exit;


/* ============================================================
 *  mail() — встроенная отправка
 * ============================================================ */
function sendMail(array $cfg, string $plain): bool
{
    $subject = '=?UTF-8?B?' . base64_encode($cfg['subject']) . '?=';
    $fromHdr = '=?UTF-8?B?' . base64_encode($cfg['from_name']) . '?= <' . $cfg['from_email'] . '>';

    $headers  = "From: {$fromHdr}\r\n";
    $headers .= "Reply-To: {$cfg['from_email']}\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $headers .= "Content-Transfer-Encoding: 8bit\r\n";

    // -f нужен, чтобы Return-Path был адекватным; на Beget работает.
    return @mail($cfg['to'], $subject, $plain, $headers, '-f' . $cfg['from_email']);
}


/* ============================================================
 *  SMTP — рукописная реализация (без зависимостей).
 *  Поддерживает SSL (465) и STARTTLS (587/25/2525).
 * ============================================================ */
function sendSmtp(array $cfg, string $plain, string $html): bool
{
    $host   = $cfg['smtp_host'];
    $port   = (int)$cfg['smtp_port'];
    $secure = strtolower((string)$cfg['smtp_secure']); // ssl | tls | ''
    $user   = $cfg['smtp_user'];
    $pass   = $cfg['smtp_pass'];

    $remote = ($secure === 'ssl' ? 'ssl://' : '') . $host . ':' . $port;
    $errno = 0; $errstr = '';
    $fp = @stream_socket_client($remote, $errno, $errstr, 15, STREAM_CLIENT_CONNECT);
    if (!$fp) throw new RuntimeException("SMTP connect: {$errstr} ({$errno})");
    stream_set_timeout($fp, 15);

    $read = function() use ($fp) {
        $data = '';
        while (!feof($fp)) {
            $line = fgets($fp, 1024);
            if ($line === false) break;
            $data .= $line;
            if (isset($line[3]) && $line[3] === ' ') break;
        }
        return $data;
    };
    $write = function(string $cmd) use ($fp) {
        fwrite($fp, $cmd . "\r\n");
    };
    $expect = function(string $resp, string $codes) {
        $c = substr($resp, 0, 3);
        if (strpos($codes, $c) === false) {
            throw new RuntimeException('SMTP unexpected: ' . trim($resp));
        }
    };

    $expect($read(), '220');
    $write('EHLO ' . ($_SERVER['SERVER_NAME'] ?? 'localhost'));
    $ehlo = $read();
    $expect($ehlo, '250');

    if ($secure === 'tls') {
        $write('STARTTLS');
        $expect($read(), '220');
        if (!stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            throw new RuntimeException('SMTP STARTTLS failed');
        }
        $write('EHLO ' . ($_SERVER['SERVER_NAME'] ?? 'localhost'));
        $expect($read(), '250');
    }

    if ($user !== '') {
        $write('AUTH LOGIN');
        $expect($read(), '334');
        $write(base64_encode($user));
        $expect($read(), '334');
        $write(base64_encode($pass));
        $expect($read(), '235');
    }

    $write('MAIL FROM:<' . $cfg['from_email'] . '>');
    $expect($read(), '250');
    $write('RCPT TO:<' . $cfg['to'] . '>');
    $expect($read(), '250251');
    $write('DATA');
    $expect($read(), '354');

    $boundary = 'b_' . bin2hex(random_bytes(8));
    $fromHdr  = '=?UTF-8?B?' . base64_encode($cfg['from_name']) . '?= <' . $cfg['from_email'] . '>';
    $subj     = '=?UTF-8?B?' . base64_encode($cfg['subject']) . '?=';

    $msg  = "From: {$fromHdr}\r\n";
    $msg .= "To: {$cfg['to']}\r\n";
    $msg .= "Subject: {$subj}\r\n";
    $msg .= "Date: " . date('r') . "\r\n";
    $msg .= "Message-ID: <" . bin2hex(random_bytes(8)) . "@" . ($_SERVER['SERVER_NAME'] ?? 'localhost') . ">\r\n";
    $msg .= "MIME-Version: 1.0\r\n";
    $msg .= "Content-Type: multipart/alternative; boundary=\"{$boundary}\"\r\n";
    $msg .= "\r\n";
    $msg .= "--{$boundary}\r\n";
    $msg .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $msg .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
    $msg .= $plain . "\r\n";
    $msg .= "--{$boundary}\r\n";
    $msg .= "Content-Type: text/html; charset=UTF-8\r\n";
    $msg .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
    $msg .= $html . "\r\n";
    $msg .= "--{$boundary}--\r\n";
    // экранируем строки начинающиеся с точки
    $msg = preg_replace('/^\./m', '..', $msg);

    fwrite($fp, $msg . "\r\n.\r\n");
    $expect($read(), '250');
    $write('QUIT');
    @fclose($fp);
    return true;
}
