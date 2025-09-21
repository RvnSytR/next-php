<?php

// Get User by Id
action("GET", function ($req, $db) use ($params) {
    $res = $db["user"]["select-by-id"]($params["id"])->fetch_assoc();
    if (!$res) {
        throw new Error("Akun tidak ditemukan.", 404);
    }
    responseSuccess(["data" => $res]);
});

// Update User by Id
action("POST", function ($req, $db) use ($params) {
    $id = $params["id"];
    $data = checkFields($req, ["role" => ["type" => "string", "max" => 20]]);

    if ($_SESSION["id"] === $id) {
        throw new Error(
            "Akses ditolak - Anda tidak dapat memperbarui akun yang sedang digunakan.",
            400,
        );
    }

    global $config;
    if (!in_array($data["role"], $config["roles"])) {
        throw new Error("Role tidak valid.", 400);
    }

    $res = $db["user"]["select-name&role-by-id"]($id)->fetch_assoc();
    if (!$res) {
        throw new Error("Akun tidak ditemukan.", 404);
    }

    $data["id"] = $id;
    $data["role"] = strtolower($data["role"]);
    $db["user"]["update-role-by-id"]($data);

    $newRole = ucfirst($data["role"]);
    responseSuccess([
        "message" => "Role {$res["name"]} berhasil diperbarui menjadi $newRole.",
    ]);
});

// Delete User by Id
action("DELETE", function ($req, $db) use ($params) {
    $data = checkFields($params, ["id" => ["type" => "string"]]);

    if ($_SESSION["id"] === $data["id"]) {
        throw new Error(
            "Anda tidak dapat menghapus akun yang sedang digunakan.",
            400,
        );
    }

    $res = $db["user"]["select-name&image-by-id"]($data["id"])->fetch_assoc();
    if (!$res) {
        throw new Error("Akun tidak ditemukan.", 404);
    }

    if (isset($res["image"])) {
        removeFiles([strAddRootPath($res["image"])]);
    }

    $db["user"]["remove"]($data["id"]);

    responseSuccess([
        "message" => "Akun atas nama {$res["name"]} berhasil dihapus.",
    ]);
});
