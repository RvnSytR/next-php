<?php

action("GET", function () {
    global $configFile;
    $configData = json_decode(file_get_contents($configFile), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Error("Format konfigurasi (config.json) tidak valid.", 400);
    }

    responseSuccess(["data" => $configData]);
});

action("POST", function ($req) {
    global $configFile;
    $invalidMessage = "Format konfigurasi tidak valid.";

    if (is_string($req)) {
        json_decode($req);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Error($invalidMessage, 400);
        }
    } elseif (is_object($req) || empty($req)) {
        throw new Error($invalidMessage, 400);
    }

    if (
        file_put_contents(
            $configFile,
            json_encode($req, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
        )
    ) {
        responseSuccess(["message" => "Konfigurasi berhasil diperbarui."]);
    } else {
        throw new Error("Gagal memperbarui konfigurasi", 500);
    }
});
