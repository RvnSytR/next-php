<?php

// Get all User
action("GET", fn($db) => responseSuccess(["data" => $db["user"]["select"]()->fetch_all(MYSQLI_ASSOC)]));

// Create new User
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

// Remove many User
action("DELETE", function ($db) {
    $req = (array) json_decode(file_get_contents("php://input"));

    $data = checkFields($req, ["ids" => ["type" => "array"]]);
    $dataLength = count($data["ids"]);

    if ($_SESSION["role"] !== "admin")
        throw new Error("Akses ditolak - Anda bukan admin.", 403);

    $i = 1;
    foreach ($data["ids"] as $item) {
        if ($_SESSION["id"] === $item) throw new Error("Data ke {$i}: Anda tidak dapat menghapus akun yang sedang digunakan.", 400);
        $i++;
    }

    $successLength = 0;
    foreach ($data["ids"] as $item) {
        $res = $db["user"]["select-name&image-by-id"]($item)->fetch_assoc();

        if (!$res) continue;

        if (isset($res["image"])) removeFiles([strAddRootPath($res["image"])]);
        $db["user"]["remove"]($item);

        $successLength++;
    }

    responseSuccess(["message" => "{$successLength} dari {$dataLength} akun pengguna berhasil dihapus."]);
});
