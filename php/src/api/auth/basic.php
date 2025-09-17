<?php

// Sign In
action("POST", function ($req, $db) {
    $data = checkFields($req, [
        "email" => ["type" => "email"],
        "password" => ["type" => "string"],
    ]);

    $res = $db["user"]["select-by-email"]($data["email"])->fetch_assoc();
    if (!$res || !password_verify($data["password"], $res["password"])) {
        throw new Error("Email atau kata sandi salah.", 403);
    }

    $_SESSION = $res;
    responseSuccess(["message" => "Berhasil masuk - Selamat datang {$res["name"]}!"]);
});

// Sign Out
action("DELETE", function () {
    session_unset();
    session_destroy();
    responseSuccess(["message" => "Berhasil keluar - Sampai jumpa!"]);
});
