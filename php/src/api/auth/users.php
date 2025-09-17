<?php

// Get all User
action("GET", fn($req, $db) => responseSuccess(["data" => $db["user"]["select"]()->fetch_all(MYSQLI_ASSOC)]));

// Create new User
action("POST", function ($req, $db) {
    $data = checkFields($req, [
        "name" => ["type" => "string"],
        "email" => ["type" => "email"],
        "password" => ["type" => "password"],
        "confirmPassword" => ["type" => "string"],
        "role" => ["type" => "string"],
    ]);

    if ($data["password"] !== $data["confirmPassword"]) {
        throw new Error("Kata sandi tidak cocok - silakan periksa kembali.", 400);
    }

    $user = $db["user"]["select-by-email"]($data["email"])->fetch_assoc();
    if (!empty($user)) throw new Error("Email ini sudah terdaftar.", 409);

    $data["id"] = uuidv4();
    $data["role"] = strtolower($data["role"]);
    $data["password"] = password_hash(trim($data["password"]), PASSWORD_BCRYPT);

    $db["user"]["insert"]($data);
    responseSuccess(["message" => "Akun {$data["name"]} berhasil dibuat."]);
});

// Remove many User
action("DELETE", function ($req, $db) {
    $data = checkFields($req, ["ids" => ["type" => "array"]]);
    $dataLength = count($data["ids"]);

    if ($_SESSION["role"] !== "admin") throw new Error("Akses ditolak - Anda bukan admin.", 403);

    $i = 1;
    foreach ($data["ids"] as $item) {
        if ($_SESSION["id"] === $item) {
            throw new Error("Data ke {$i}: Anda tidak dapat menghapus akun yang sedang digunakan.", 400);
        }
        $i++;
    }

    $successLength = 0;
    foreach ($data["ids"] as $item) {
        $user = $db["user"]["select-name&image-by-id"]($item)->fetch_assoc();
        if (empty($user)) continue;

        if (isset($user["image"])) removeFiles([strAddRootPath($user["image"])]);
        $db["user"]["remove"]($item);

        $successLength++;
    }

    responseSuccess(["message" => "{$successLength} dari {$dataLength} akun pengguna berhasil dihapus."]);
});
