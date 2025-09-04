<?php

action("POST", function ($db) {
    $data = checkFields($_POST, [
        "email" => ["type" => "email"],
        "password" => ["type" => "string"],
    ]);

    $res = $db["user"]["selectByEmail"]($data["email"])->fetch_assoc();
    if (!$res || !password_verify($data["password"], $res["password"])) {
        throw new Error("Email atau kata sandi salah.", 403);
    }

    setSession($res);
    responseSuccess(["message" => "Berhasil masuk - Selamat datang {$res["name"]}!"]);
});

action("DELETE", function () {
    destroySession();
    responseSuccess(["message" => "Berhasil keluar - Sampai jumpa!"]);
});
