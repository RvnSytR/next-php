<?php

function uploadFiles(array $files, string $dirKey, array $options = []): array
{
    global $directories;

    if (!in_array($dirKey, array_keys($directories))) {
        throw new Error("Direktori dengan key '$dirKey' tidak ditemukan.", 500);
    }

    if (empty($files)) {
        return [];
    }

    // Options
    $prefix =
        isset($options["prefix"]) && checkType($options["prefix"], "string")
        ? $options["prefix"]
        : "";
    $suffix =
        isset($options["suffix"]) && checkType($options["suffix"], "string")
        ? $options["suffix"]
        : "";
    $date =
        isset($options["date"]) && checkType($options["date"], "boolean")
        ? $options["date"]
        : false;

    $savedPaths = [];

    foreach ($files as $file) {
        $originalName = pathinfo($file["name"], PATHINFO_FILENAME);
        $extension = pathinfo($file["name"], PATHINFO_EXTENSION);

        $dt = new DateTime();
        $timestamp = $date ? "_" . $dt->format("YmdHisv") : "";

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
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
}

function action(
    string|array $methods,
    callable $callback,
    array $response = []
) {
    if (!checkMethod($methods)) {
        return;
    }

    try {
        call_user_func_array($callback, []);
    } catch (Throwable $th) {
        $onError = $response["onError"] ?? null;
        if ($onError && is_callable($onError)) {
            call_user_func_array($onError, [$th]);
        }
        responseError($th);
    }
}
