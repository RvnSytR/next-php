<?php

function uuidv4()
{
    $data = random_bytes(16);
    $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
    $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
    return vsprintf("%s%s-%s-%s-%s-%s%s%s", str_split(bin2hex($data), 4));
}

function strSliceRootPath(string $str)
{
    $rootPath = $_SERVER["DOCUMENT_ROOT"];
    if (strpos($str, $rootPath) === 0) return substr($str, strlen($rootPath));
    return $str;
}

function strAddRootPath(string $str)
{
    return $_SERVER["DOCUMENT_ROOT"] . $str;
}

function base64url_encode($data)
{
    return rtrim(strtr(base64_encode($data), "+/", "-_"), "=");
}

function base64url_decode($data)
{
    return base64_decode(str_pad(strtr($data, "-_", "+/"), strlen($data) % 4, "=", STR_PAD_RIGHT));
}

function uploadFiles(array $files, string $dirKey, array $options = []): array
{
    global $directories;

    if (!in_array($dirKey, array_keys($directories))) {
        throw new Error("Direktori dengan key '$dirKey' tidak ditemukan.", 500);
    }

    if (empty($files)) return [];

    // Options
    $prefix = $options["prefix"] ?? "";
    $suffix = $options["suffix"] ?? "";
    $withDate =
        isset($options["withDate"]) && checkType($options["withDate"], "boolean")
        ? $options["withDate"]
        : false;

    $savedPaths = [];

    foreach ($files as $file) {
        $originalName = pathinfo($file["name"], PATHINFO_FILENAME);
        $extension = pathinfo($file["name"], PATHINFO_EXTENSION);

        $dt = new DateTime();
        $timestamp = $withDate ? "_" . $dt->format("YmdHisv") : "";

        $filename = $prefix . $originalName . $timestamp . $suffix;
        $filename .= $extension ? "." . $extension : "";

        $destination = $directories[$dirKey] . "/" . $filename;

        if (!move_uploaded_file($file["tmp_name"], $destination)) {
            throw new Error("Gagal mengunggah file '" . $file["name"] . "'.", 500);
        }

        $savedPaths[] = $destination;
    }

    return $savedPaths;
}

function removeFiles(array $filePaths)
{
    foreach ($filePaths as $filePath) {
        if (file_exists($filePath)) unlink($filePath);
    }
}

function action(
    string|array $methods,
    callable $callback,
    array $response = []
) {
    if (!checkMethod($methods)) return;
    global $db;

    try {
        $req = (array) json_decode(file_get_contents("php://input"));
        call_user_func_array($callback, [$req, $db]);
    } catch (Throwable $th) {
        $onError = $response["onError"] ?? null;
        if ($onError && is_callable($onError)) call_user_func_array($onError, [$th]);
        responseError($th);
    }
}
