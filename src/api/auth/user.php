<?php

action("GET", fn($db) => responseSuccess(["data" => $db["user"]["select"]()->fetch_all(MYSQLI_ASSOC)]));

$uploadCtx = [];
action(
    "POST",
    function ($db) use (&$uploadCtx) {
        $data = checkFields($_POST, [
            "name" => ["type" => "string"],
            "email" => ["type" => "email"],
            "password" => ["type" => "password"],
            "confirmPassword" => ["type" => "string"],
            "image" => ["type" => "file", "optional" => true, "max" => 1],
            "role" => ["type" => "string"],
        ]);

        if ($data["password"] !== $data["confirmPassword"]) {
            throw new Error("Kata sandi tidak cocok - silakan periksa kembali.", 400);
        }

        if (!empty($db["user"]["select-by-email"]($data["email"])->fetch_assoc())) {
            throw new Error("Email ini sudah terdaftar.", 409);
        }

        $data["id"] = uuidv4();
        $data["role"] = strtolower($data["role"]);
        $data["password"] = password_hash(trim($data["password"]), PASSWORD_BCRYPT);

        $uploadCtx = uploadFiles($data["image"] ?? [], "avatar", ["withDate" => true]);
        $data["image"] = !empty($uploadCtx[0]) ? strSliceRootPath($uploadCtx[0]) : null;

        $db["user"]["insert"]($data);
        responseSuccess(["message" => "Akun {$data["name"]} berhasil dibuat."]);
    },
    [
        "onError" => function () use (&$uploadCtx) {
            removeFiles($uploadCtx);
        },
    ]
);
