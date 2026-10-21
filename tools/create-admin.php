<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("This tool is available from the command line only.\n");
}

$password = $argv[1] ?? '';
if (!is_string($password) || strlen($password) < 12) {
    fwrite(STDERR, "Use a password with at least 12 characters.\n");
    exit(1);
}

$target = dirname(__DIR__) . '/config.local.php';
if (file_exists($target)) {
    fwrite(STDERR, "config.local.php already exists. It was not changed.\n");
    exit(1);
}

$content = "<?php\nreturn [\n"
    . "    'APP_ENV' => 'local',\n"
    . "    'STORE_ADMIN_PASSWORD_HASH' => " . var_export(password_hash($password, PASSWORD_DEFAULT), true) . ",\n"
    . "];\n";

if (file_put_contents($target, $content, LOCK_EX) === false) {
    fwrite(STDERR, "Could not create config.local.php.\n");
    exit(1);
}

echo "Admin configuration created. Open /admin.php to sign in.\n";
