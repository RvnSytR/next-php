<?php

action("POST", function ($db) {
    $data = checkFields($_POST, ["ids" => ["type" => "array"]]);
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
