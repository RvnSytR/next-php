<?php

action("GET", function ($db) use ($params) {
    $id = $params["id"] ?? null;
    $data = isset($id)
        ? $db["user"]["selectById"]($id)->fetch_assoc()
        : $db["user"]["select"]()->fetch_all(MYSQLI_ASSOC);
    responseSuccess(["data" => $data]);
});

$uploadCtx = [];
action(
    "POST",
    function ($db) use (&$uploadCtx) {
        $data = checkFields($_POST, [
            "email" => ["type" => "email"],
            "password" => ["type" => "password"],
            "name" => ["type" => "string"],
            "image" => ["type" => "file", "optional" => true, "max" => 1],
            "role" => ["type" => "string"],
        ]);

        if (!empty($db["user"]["selectByEmail"]($data["email"])->fetch_assoc())) {
            throw new Error("Email ini sudah terdaftar.", 409);
        }

        $data["id"] = uuidv4();
        $data["role"] = strtolower($data["role"]);
        $data["password"] = password_hash(trim($data["password"]), PASSWORD_BCRYPT);

        $uploadCtx = uploadFiles($data["image"] ?? [], "avatar", ["withDate" => true]);
        $data["image"] = $uploadCtx[0] ?? null;

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
    $res = $db["user"]["selectNameImageById"]($data["id"])->fetch_assoc();

    if (!$res) throw new Error("Akun tidak ditemukan.", 404);
    if (isset($res["image"])) removeFiles([$res["image"]]);

    isset($res["image"]) && removeFiles([$res["image"]]);
    $db["user"]["remove"]($data["id"]);

    responseSuccess(["message" => "Akun {$res["name"]} berhasil dihapus."]);
});
