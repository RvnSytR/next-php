<?php

action("GET", function () {
    $data = !empty($_SESSION) ? $_SESSION : null;
    $data["image"] = !empty($data["image"]) ? substr($data["image"], strlen($_SERVER["DOCUMENT_ROOT"])) : null;
    responseSuccess(["data" => $data]);
});

$uploadCtx = [];
action(
    "POST",
    function ($db) use (&$uploadCtx) {
        $data = checkFields($_POST, [
            "name" => ["type" => "string"],
            "image" => ["type" => "file", "optional" => true, "max" => 1],
        ]);

        $data["id"] = $_SESSION["id"];
        $data["email"] = $_SESSION["email"];
        $data["role"] = $_SESSION["role"];

        if (isset($data["image"])) {
            if (isset($_SESSION["image"])) removeFiles([$_SESSION["image"]]);
            $uploadCtx = uploadFiles($data["image"], "avatar", ["withDate" => true]);
            $data["image"] = $uploadCtx[0];
        } else {
            $data["image"] = $_SESSION["image"];
        }

        $db["user"]["updateNameImageById"]($data);
        setSession($data);
        responseSuccess(["message" => "Profil berhasil diperbarui."]);
    },
    [
        "onError" => function () use (&$uploadCtx) {
            removeFiles($uploadCtx);
        },
    ]
);

action("DELETE", function ($db) {
    if (isset($_SESSION["image"])) removeFiles([$_SESSION["image"]]);

    $data["id"] = $_SESSION["id"];
    $data["name"] = $_SESSION["name"];
    $data["image"] = null;
    $data["email"] = $_SESSION["email"];
    $data["role"] = $_SESSION["role"];

    $db["user"]["updateNameImageById"]($data);
    setSession($data);
    responseSuccess(["message" => "Foto profil berhasil dihapus."]);
});
