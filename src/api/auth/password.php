<?php

action("POST", function ($db) {
    $data = checkFields($_POST, [
        "password" => ["type" => "string"],
        "newPassword" => ["type" => "password"],
        "confirmPassword" => ["type" => "string"],
    ]);

    if ($data["newPassword"] !== $data["confirmPassword"]) {
        throw new Error("Kata sandi tidak cocok - silakan periksa kembali.", 401);
    }

    $id = $_SESSION["id"];
    $currentPassword = $db["user"]["select-password-by-id"]($id)->fetch_assoc()["password"];

    if (!password_verify($data["password"], $currentPassword)) {
        throw  new Error("Kata sandi salah.", 401);
    }

    if (password_verify($data["newPassword"], $currentPassword)) {
        throw new Error("Tidak ada perubahan yang dilakukan pada kata sandi.", 409);
    }

    $db["user"]["update-password-by-id"]($id, password_hash(trim($data["newPassword"]), PASSWORD_BCRYPT));
    responseSuccess(["message" => "Kata sandi berhasil diperbarui."]);
});
