<?php
/*
 * Production values should come from web-server environment variables.
 * A local config is optional and stays outside source control.
 */
$localConfigFile = __DIR__ . '/config.local.php';
$localConfig = is_file($localConfigFile) ? require $localConfigFile : [];
if (!is_array($localConfig)) {
    $localConfig = [];
}

function storeConfigValue($key, $default = '') {
    global $localConfig;
    $environmentValue = getenv($key);
    if ($environmentValue !== false && $environmentValue !== '') {
        return $environmentValue;
    }
    return $localConfig[$key] ?? $default;
}

$appEnvironment = storeConfigValue('APP_ENV', 'local');
error_reporting(E_ALL);
ini_set('display_errors', $appEnvironment === 'production' ? '0' : '1');
ini_set('log_errors', '1');

define('DB_SERVER', storeConfigValue('DB_HOST', 'localhost'));
define('DB_PORT', storeConfigValue('DB_PORT', '3306'));
define('DB_USERNAME', storeConfigValue('DB_USERNAME', 'root'));
define('DB_PASSWORD', storeConfigValue('DB_PASSWORD', ''));
define('DB_NAME', storeConfigValue('DB_NAME', 'ygywxhgiig_servers'));

$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_secure', storeConfigValue('COOKIE_SECURE', $isHttps ? '1' : '0'));
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);
session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (!isset($_SESSION['created_at'])) {
    $_SESSION['created_at'] = time();
} elseif (isset($_SESSION['user_id']) && time() - $_SESSION['created_at'] > 7200) {
    session_regenerate_id(true);
    $_SESSION['created_at'] = time();
}

header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
if ($isHttps) {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}

try {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME, (int)DB_PORT);
    $conn->set_charset("utf8mb4");
} catch (Throwable $e) {
    http_response_code(500);
    if ($appEnvironment === 'production') {
        exit('Database connection error.');
    }
    exit('Database connection error: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}

define('SITE_NAME', 'ZaidanStore');
define('SITE_URL', storeConfigValue('SITE_URL', 'http://localhost/web-main'));
define('WHATSAPP_NUMBER', storeConfigValue('WHATSAPP_NUMBER', '6285771001904'));
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_TIMEOUT', 900);
define('MAX_FILE_SIZE', 5242880);
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/jpg']);
define('MAX_SERVERS_PER_USER', 2);

function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

function getCurrentUser() {
    global $conn;
    if (!isLoggedIn()) {
        return null;
    }
    $user_id = (int)$_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT id, username, email FROM users WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    $data = trim($data);
    $data = strip_tags($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function validateCSRF($token) {
    return isset($_SESSION['csrf_token']) && is_string($token) && hash_equals($_SESSION['csrf_token'], $token);
}

function regenerateCSRF() {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function checkRateLimit($identifier, $max_attempts, $time_window) {
    if (!isset($_SESSION['rate_limit'])) {
        $_SESSION['rate_limit'] = [];
    }
    
    $current_time = time();
    $key = md5($identifier);
    
    if (!isset($_SESSION['rate_limit'][$key])) {
        $_SESSION['rate_limit'][$key] = [
            'attempts' => 1,
            'first_attempt' => $current_time
        ];
        return true;
    }
    
    $data = $_SESSION['rate_limit'][$key];
    
    if ($current_time - $data['first_attempt'] > $time_window) {
        $_SESSION['rate_limit'][$key] = [
            'attempts' => 1,
            'first_attempt' => $current_time
        ];
        return true;
    }
    
    if ($data['attempts'] >= $max_attempts) {
        return false;
    }
    
    $_SESSION['rate_limit'][$key]['attempts']++;
    return true;
}

function validateUsername($username) {
    if (strlen($username) < 3 || strlen($username) > 20) {
        return false;
    }
    return preg_match('/^[a-zA-Z0-9_]+$/', $username);
}

function validatePassword($password) {
    if (strlen($password) < 8 || strlen($password) > 128) {
        return false;
    }
    if (!preg_match('/[A-Z]/', $password)) {
        return false;
    }
    if (!preg_match('/[a-z]/', $password)) {
        return false;
    }
    if (!preg_match('/[0-9]/', $password)) {
        return false;
    }
    return true;
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) && strlen($email) <= 100;
}

// Email Verification + SMTP (set these via environment variables in production)
// Windows (PowerShell) example:
//   setx SMTP_HOST "mail.spacemail.com"
//   setx SMTP_PORT "465"
//   setx SMTP_SECURE "ssl"   # or "tls" for STARTTLS on 587
//   setx SMTP_USERNAME "businessrelyz@gmail.com"
//   setx SMTP_PASSWORD "YOUR_PASSWORD_HERE"
//
// If env vars are not set, these fall back to the defaults below.
define('SMTP_HOST', 'mail.spacemail.com');
define('SMTP_PORT', 465);              // 465 for SSL, or 587 for TLS
define('SMTP_SECURE', 'ssl');          // 'ssl' for 465, 'tls' for 587
define('SMTP_USERNAME', 'relyz@hf2p-servers.com');
define('SMTP_PASSWORD', '$H466h6wh12');   // your SMTP password
define('EMAIL_FROM', SMTP_USERNAME);
define('EMAIL_FROM_NAME', SITE_NAME);
define('EMAIL_VERIFY_TTL_SECONDS', 86400); // 24 hours

function smtpSendMail($toEmail, $subject, $htmlBody, $textBody = '') {
    $host = SMTP_HOST;
    $port = SMTP_PORT;
    $secure = strtolower(SMTP_SECURE);

    if (SMTP_PASSWORD === '') {
        // Fail closed if SMTP password not configured
        return false;
    }

    $timeout = 15;
    $remote = ($secure === 'ssl') ? "ssl://{$host}:{$port}" : "{$host}:{$port}";
    $fp = @stream_socket_client($remote, $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT);
    if (!$fp) {
        return false;
    }
    stream_set_timeout($fp, $timeout);

    $read = function() use ($fp) {
        $data = '';
        while (!feof($fp)) {
            $line = fgets($fp, 515);
            if ($line === false) break;
            $data .= $line;
            // multiline responses end when 4th char is space
            if (preg_match('/^\d{3} /', $line)) break;
        }
        return $data;
    };

    $write = function($cmd) use ($fp, $read) {
        fwrite($fp, $cmd . "\r\n");
        return $read();
    };

    $expect = function($resp, $codes) {
        foreach ((array)$codes as $code) {
            if (strpos($resp, (string)$code) === 0) return true;
        }
        return false;
    };

    $resp = $read();
    if (!$expect($resp, 220)) { fclose($fp); return false; }

    $helloName = preg_replace('/[^a-zA-Z0-9\.\-]/', '', $_SERVER['SERVER_NAME'] ?? 'localhost');
    $resp = $write("EHLO {$helloName}");
    if (!$expect($resp, 250)) {
        $resp = $write("HELO {$helloName}");
        if (!$expect($resp, 250)) { fclose($fp); return false; }
    }

    // STARTTLS if requested
    if ($secure === 'tls') {
        $resp = $write("STARTTLS");
        if (!$expect($resp, 220)) { fclose($fp); return false; }
        if (!stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) { fclose($fp); return false; }
        $resp = $write("EHLO {$helloName}");
        if (!$expect($resp, 250)) { fclose($fp); return false; }
    }

    // AUTH LOGIN
    $resp = $write("AUTH LOGIN");
    if (!$expect($resp, 334)) { fclose($fp); return false; }
    $resp = $write(base64_encode(SMTP_USERNAME));
    if (!$expect($resp, 334)) { fclose($fp); return false; }
    $resp = $write(base64_encode(SMTP_PASSWORD));
    if (!$expect($resp, 235)) { fclose($fp); return false; }

    $from = EMAIL_FROM;
    $resp = $write("MAIL FROM:<{$from}>");
    if (!$expect($resp, 250)) { fclose($fp); return false; }
    $resp = $write("RCPT TO:<{$toEmail}>");
    if (!$expect($resp, [250, 251])) { fclose($fp); return false; }
    $resp = $write("DATA");
    if (!$expect($resp, 354)) { fclose($fp); return false; }

    $boundary = 'bnd_' . bin2hex(random_bytes(8));
    $fromName = EMAIL_FROM_NAME;
    $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';

    if ($textBody === '') {
        $textBody = strip_tags(preg_replace('/<br\s*\/?>/i', "\n", $htmlBody));
    }

    $headers = [];
    $headers[] = "From: " . str_replace(["\r", "\n"], '', $fromName) . " <{$from}>";
    $headers[] = "To: <{$toEmail}>";
    $headers[] = "Subject: {$encodedSubject}";
    $headers[] = "MIME-Version: 1.0";
    $headers[] = "Content-Type: multipart/alternative; boundary=\"{$boundary}\"";
    $headersStr = implode("\r\n", $headers);

    $body = '';
    $body .= "--{$boundary}\r\n";
    $body .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
    $body .= $textBody . "\r\n\r\n";
    $body .= "--{$boundary}\r\n";
    $body .= "Content-Type: text/html; charset=UTF-8\r\n";
    $body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
    $body .= $htmlBody . "\r\n\r\n";
    $body .= "--{$boundary}--\r\n";

    $data = $headersStr . "\r\n\r\n" . $body . "\r\n.";
    fwrite($fp, $data . "\r\n");
    $resp = $read();
    if (!$expect($resp, 250)) { fclose($fp); return false; }

    $write("QUIT");
    fclose($fp);
    return true;
}

function generateEmailVerificationToken() {
    return bin2hex(random_bytes(32));
}

function hashEmailVerificationToken($token) {
    return hash('sha256', $token);
}

function sendVerificationEmail($toEmail, $token) {
    $verifyUrl = rtrim(SITE_URL, '/') . '/verify-email.php?token=' . urlencode($token);

    $subject = 'Verify your email address';
    $html = '<p>Welcome!</p>'
        . '<p>Please verify your email by clicking the link below:</p>'
        . '<p><a href="' . htmlspecialchars($verifyUrl, ENT_QUOTES, 'UTF-8') . '">Verify Email</a></p>'
        . '<p>If you did not create this account, you can ignore this email.</p>';
    $text = "Welcome!\n\nVerify your email:\n{$verifyUrl}\n\nIf you did not create this account, ignore this email.";

    return smtpSendMail($toEmail, $subject, $html, $text);
}

function generatePasswordResetToken() {
    return bin2hex(random_bytes(32));
}

function hashPasswordResetToken($token) {
    return hash('sha256', $token);
}

function sendPasswordResetEmail($toEmail, $token) {
    $resetUrl = rtrim(SITE_URL, '/') . '/reset-password.php?token=' . urlencode($token);

    $subject = 'Reset your password';
    $html = '<p>You requested a password reset.</p>'
        . '<p>Click the link below to reset your password:</p>'
        . '<p><a href="' . htmlspecialchars($resetUrl, ENT_QUOTES, 'UTF-8') . '">Reset Password</a></p>'
        . '<p>This link will expire in 1 hour.</p>'
        . '<p>If you did not request this, you can safely ignore this email.</p>';
    $text = "You requested a password reset.\n\nReset your password:\n{$resetUrl}\n\nThis link expires in 1 hour.\n\nIf you did not request this, ignore this email.";

    return smtpSendMail($toEmail, $subject, $html, $text);
}

function isAllowedEmailDomain($email) {
    $pos = strrpos($email, '@');
    if ($pos === false) {
        return false;
    }

    $domain = strtolower(substr($email, $pos + 1));

    $allowed_domains = [
        'gmail.com',
        'outlook.com',
        'hotmail.com',
        'yahoo.com',
        'protonmail.com',
        'proton.me'
    ];

    return in_array($domain, $allowed_domains, true);
}

function isVerifiedUserId($userId) {
    global $conn;
    $userId = (int)$userId;
    $stmt = $conn->prepare("SELECT is_verified FROM users WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return (int)($row['is_verified'] ?? 0) === 1;
}

function validateIPAddress($ip) {
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        return true;
    }
    if (preg_match('/^[a-zA-Z0-9][a-zA-Z0-9-]{1,61}[a-zA-Z0-9]\.[a-zA-Z]{2,}$/', $ip)) {
        return true;
    }
    return false;
}

function validatePort($port) {
    return is_numeric($port) && $port >= 1 && $port <= 65535;
}

function secureRedirect($url) {
    $allowed_pages = ['index.php', 'dashboard.php', 'login.php', 'register.php', 'forgot-password.php', 'reset-password.php', 'settings.php'];
    $url = basename($url);
    if (in_array($url, $allowed_pages)) {
        header("Location: " . $url);
        exit();
    }
    header("Location: index.php");
    exit();
}
?>
