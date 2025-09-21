<?php

action("GET", fn() => responseSuccess(["data" => $config]));

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
