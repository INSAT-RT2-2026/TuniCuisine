<?php

/**
 * Enables PHP extensions required by TuniCuisine (file uploads, Symfony).
 * Run once after installing PHP via winget: php bin/enable-php-extensions.php
 */

$iniPath = php_ini_loaded_file();
if ($iniPath === false || !is_file($iniPath)) {
    fwrite(STDERR, "Could not find php.ini. Run this with the project's PHP 8.4 (php.bat).\n");
    exit(1);
}

$required = ['fileinfo', 'mbstring'];
$contents = file_get_contents($iniPath);
if ($contents === false) {
    fwrite(STDERR, "Could not read: {$iniPath}\n");
    exit(1);
}

$changed = false;
foreach ($required as $ext) {
    $disabled = ';extension='.$ext;
    $enabled = 'extension='.$ext;
    if (str_contains($contents, $enabled."\n") || str_contains($contents, $enabled."\r")) {
        continue;
    }
    if (!str_contains($contents, $disabled)) {
        fwrite(STDERR, "Could not find ;extension={$ext} in php.ini — enable it manually.\n");
        continue;
    }
    $contents = str_replace($disabled, $enabled, $contents);
    $changed = true;
    echo "Enabled extension={$ext}\n";
}

if (!$changed) {
    echo "Required extensions are already enabled in:\n  {$iniPath}\n";
    exit(0);
}

if (file_put_contents($iniPath, $contents) === false) {
    fwrite(STDERR, "Could not write php.ini. Try running as administrator or edit:\n  {$iniPath}\n");
    exit(1);
}

echo "Updated php.ini:\n  {$iniPath}\n";
echo "Restart run-server.bat if it is already running.\n";
