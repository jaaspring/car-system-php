<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Default Language
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'en';
}

// Handle Language Switch
if (isset($_GET['lang'])) {
    $lang = $_GET['lang'];
    if (in_array($lang, ['en', 'my'])) {
        $_SESSION['lang'] = $lang;
    }
}

// Load Language File
$langCode = $_SESSION['lang'];
$langFile = __DIR__ . "/lang/$langCode.php";

if (file_exists($langFile)) {
    $L = require $langFile;
} else {
    // Fallback
    $L = require __DIR__ . "/lang/en.php";
}
?>
