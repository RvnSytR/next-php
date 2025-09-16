<?php

// Get User by Id
action("GET", function ($req, $db) use ($params) {
    $res = $db["user"]["select-by-id"]($params["id"])->fetch_assoc();
    if (!$res) throw new Error("Akun tidak ditemukan.", 404);
    responseSuccess(["data" => $res]);
});

// Update User by Id
action("POST", function ($req, $db) use ($params) {
    $data = checkFields($req, ["role" => ["type" => "string"]]);

    $res = $db["user"]["select-name&role-by-id"]($params["id"])->fetch_assoc();
    if (!$res) throw new Error("Akun tidak ditemukan.", 404);

    $data["id"] = $params["id"];
    $data["role"] = strtolower($data["role"]);
    $db["user"]["update-role-by-id"]($data);

    $newRole = ucfirst($data["role"]);
    responseSuccess(["message" => "Role {$res["name"]} berhasil diperbarui menjadi $newRole."]);
});

// Delete User by Id
action("DELETE", function ($req, $db) use ($params) {
    $data = checkFields($params, ["id" => ["type" => "string"]]);

    if ($_SESSION["id"] === $data["id"])
        throw new Error("Anda tidak dapat menghapus akun yang sedang digunakan.", 400);
    if ($_SESSION["role"] !== "admin")
        throw new Error("Akses ditolak - Anda bukan admin.", 403);

    $res = $db["user"]["select-name&image-by-id"]($data["id"])->fetch_assoc();

    if (!$res) throw new Error("Akun tidak ditemukan.", 404);
    if (isset($res["image"])) removeFiles([strAddRootPath($res["image"])]);

    $db["user"]["remove"]($data["id"]);

    responseSuccess(["message" => "Akun atas nama {$res["name"]} berhasil dihapus."]);
});
