<?php

// Sign Up
action("POST", function ($db) {
    $data = checkFields($_POST, [
        "name" => ["type" => "string"],
        "email" => ["type" => "email"],
        "password" => ["type" => "password"],
        "confirmPassword" => ["type" => "string"],
    ]);

    if ($data["password"] !== $data["confirmPassword"]) {
        throw new Error("Kata sandi tidak cocok - silakan periksa kembali.", 400);
    }

    if (!empty($db["user"]["select-by-email"]($data["email"])->fetch_assoc())) {
        throw new Error("Email ini sudah terdaftar.", 409);
    }

    $data["id"] = uuidv4();
    $data["role"] = "user";
    $data["password"] = password_hash(trim($data["password"]), PASSWORD_BCRYPT);
    $data["image"] = null;

    $db["user"]["insert"]($data);
    responseSuccess(["message" => "Akun {$data["name"]} berhasil dibuat."]);
});
