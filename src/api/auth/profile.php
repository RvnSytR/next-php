<?php

action("GET", function ($db) {
    if (!isset($_SESSION["id"])) new Error("Permintaan tidak terautentikasi!", 401);
    $res = $db["user"]["select-by-id"]($_SESSION["id"])->fetch_assoc();
    $_SESSION = $res;
    responseSuccess(["data" => $_SESSION]);
});

$uploadCtx = [];
action(
    "POST",
    function ($db) use (&$uploadCtx) {
        $data = checkFields($_POST, [
            "name" => ["type" => "string", "optional" => true],
            "image" => ["type" => "file", "optional" => true, "max" => 1],
        ]);

        if (empty($data)) throw new Error("Tidak ada data untuk diperbarui.", 400);

        $data["id"] = $_SESSION["id"];
        $data["name"] = $data["name"] ?? $_SESSION["name"];

        if (isset($data["image"])) {
            if (isset($_SESSION["image"])) removeFiles([strAddRootPath($_SESSION["image"])]);
            $uploadCtx = uploadFiles($data["image"], "avatar", ["withDate" => true]);
            $data["image"] = strSliceRootPath($uploadCtx[0]);
        } else {
            $data["image"] = $_SESSION["image"];
        }

        $db["user"]["update-name&image-by-id"]($data);
        $_SESSION["name"] = $data["name"];
        $_SESSION["image"] = $data["image"];

        responseSuccess(["message" => "Profil berhasil diperbarui."]);
    },
    [
        "onError" => function () use (&$uploadCtx) {
            removeFiles($uploadCtx);
        },
    ]
);

action("DELETE", function ($db) {
    if (isset($_SESSION["image"])) removeFiles([strAddRootPath($_SESSION["image"])]);
    else responseError(new Error("Tidak ada foto profil yang diunggah.", 400));

    $data["id"] = $_SESSION["id"];
    $data["name"] = $_SESSION["name"];
    $data["image"] = null;

    $db["user"]["update-name&image-by-id"]($data);
    $_SESSION["image"] = null;

    responseSuccess(["message" => "Foto profil berhasil dihapus."]);
});
