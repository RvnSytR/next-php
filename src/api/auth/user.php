<?php

action("GET", function ($db) use ($params) {
    $id = $params["id"] ?? null;
    $data = isset($id)
        ? $db["user"]["select-by-id"]($id)->fetch_assoc()
        : $db["user"]["select"]()->fetch_all(MYSQLI_ASSOC);
    responseSuccess(["data" => $data]);
});

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

action("DELETE", function ($db) use ($params) {
    $data = checkFields($params, ["id" => ["type" => "string"]]);
    $res = $db["user"]["select-name&image-by-id"]($data["id"])->fetch_assoc();

    if ($_SESSION) {
        if ($_SESSION["id"] === $data["id"])
            throw new Error("Anda tidak dapat menghapus akun yang sedang digunakan.", 400);
        if ($_SESSION["role"] !== "admin")
            throw new Error("Akses ditolak - Anda bukan admin.", 403);
    } else {
        throw new Error("Permintaan tidak terautentikasi!", 401);
    }

    if (!$res) throw new Error("Akun tidak ditemukan.", 404);
    if (isset($res["image"])) removeFiles([strAddRootPath($res["image"])]);

    $db["user"]["remove"]($data["id"]);
    responseSuccess(["message" => "Akun {$res["name"]} berhasil dihapus."]);
});
