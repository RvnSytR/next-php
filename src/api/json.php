<?php

action("GET", function () use ($params) {
    global $rootDir;

    $jsonFile = $rootDir . "/src/json/" . $params["json"];
    if (!file_exists($jsonFile)) throw new Error($params["json"] . " tidak ditemukan.", 404);

    $jsonData = file_get_contents($jsonFile);
    $data = json_decode($jsonData, true);
    responseSuccess(["data" => $data]);
});

action("POST", function ($req) use ($params) {
    global $rootDir;

    $jsonFile = $rootDir . "/src/json/" . $params["json"];
    if (!file_exists($jsonFile)) throw new Error($params["json"] . " tidak ditemukan", 404);

    $jsonData = json_encode($req, JSON_PRETTY_PRINT);
    if (file_put_contents($jsonFile, $jsonData)) {
        responseSuccess(["message" => $params["json"] . " berhasil diperbarui."]);
    } else {
        throw new Error("Gagal memperbarui " . $params["json"], 500);
    }
});
