<?php

// * Required Directories
$directories = [
    "avatar" => $uploadDir . "/avatar",
];

// * Default Config on config.json
$defaultConfig = [
    "disabledRoutes" => [],
];

// ? Directories Checker
if (!file_exists($uploadDir)) {
    mkdir($uploadDir);
}

foreach ($directories as $key => $folder) {
    if (!file_exists($folder)) {
        mkdir($folder);
    }
}

// ? Configuration Loader
$configFile = "$docRoot/config.json";
if (!file_exists($configFile)) {
    file_put_contents(
        $configFile,
        json_encode($defaultConfig, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
    );
}

$configJson = file_get_contents($configFile);
if (empty(trim($configJson))) {
    $config = [];
} else {
    $config = json_decode($configJson, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        header("Content-Type: application/json");
        responseError(
            new Error(
                "Gagal memuat konfigurasi. Silakan periksa format JSON pada config.json.",
                500,
            ),
        );
    }
}

if (!is_array($config)) {
    $config = [];
}

foreach ($defaultConfig as $key => $value) {
    if (!array_key_exists($key, $config)) {
        $config[$key] = $value;
    }
}

file_put_contents(
    $configFile,
    json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
);
