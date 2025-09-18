<?php

action("GET", function () use ($params) {
    global $configDir;

    $fileName = "{$params["key"]}.json";
    $jsonFile = "$configDir/$fileName";

    if (!is_readable($jsonFile)) {
        throw new Error("$fileName tidak ditemukan.", 404);
    }

    $jsonData = json_decode(file_get_contents($jsonFile), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Error("Format $fileName invalid.", 400);
    }

    responseSuccess(["data" => $jsonData]);
});

action("POST", function ($req) use ($params) {
    global $configDir;

    $fileName = "{$params["key"]}.json";
    $jsonFile = "$configDir/$fileName";

    if (!is_readable($jsonFile)) {
        throw new Error("$fileName tidak ditemukan", 404);
    }

    if (
        file_put_contents(
            $jsonFile,
            json_encode($req, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
        )
    ) {
        responseSuccess(["message" => "$fileName berhasil diperbarui."]);
    } else {
        throw new Error("Gagal memperbarui $fileName", 500);
    }
});
