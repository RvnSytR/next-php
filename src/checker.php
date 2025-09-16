<?php

function checkHttpStatusCode($code)
{
    return is_int($code) && $code >= 100 && $code <= 599;
}

function checkMethod(string|array $methods): bool
{
    $methods = is_array($methods)
        ? array_map("strtoupper", $methods)
        : [strtoupper($methods)];

    if (
        !in_array("ANY", $methods, true) &&
        !in_array($_SERVER["REQUEST_METHOD"], $methods, true)
    ) return false;

    return true;
}

function checkType($value, string $type): bool
{
    switch ($type) {
        case "string":
        case "password":
            return is_string($value);
        case "number":
            return is_numeric($value);
        case "boolean":
            return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) !== null;
        case "date":
        case "time":
            return strtotime($value) !== false;
        case "array":
            return is_array($value);
        case "json":
            json_decode($value);
            return json_last_error() === JSON_ERROR_NONE;
        case "email":
            return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
        default:
            return false;
    }
}

function checkFields(array $fields, array $rules): array
{
    global $fileMeta, $fieldTypes;

    $files = $_FILES;

    $normalizedFiles = [];
    $validated = [];
    $missingFields = [];

    // Normalize file uploads
    foreach ($files as $key => $fileEntry) {
        if (is_array($fileEntry["name"])) {
            for ($i = 0; $i < count($fileEntry["name"]); $i++) {
                if ($fileEntry["error"][$i] === UPLOAD_ERR_OK) {
                    $normalizedFiles[$key][] = [
                        "name" => $fileEntry["name"][$i],
                        "type" => $fileEntry["type"][$i],
                        "tmp_name" => $fileEntry["tmp_name"][$i],
                        "error" => $fileEntry["error"][$i],
                        "size" => $fileEntry["size"][$i],
                    ];
                }
            }
        } else {
            if ($fileEntry["error"] === UPLOAD_ERR_OK) {
                $normalizedFiles[$key][] = [
                    "name" => $fileEntry["name"],
                    "type" => $fileEntry["type"],
                    "tmp_name" => $fileEntry["tmp_name"],
                    "error" => $fileEntry["error"],
                    "size" => $fileEntry["size"],
                ];
            }
        }
    }

    // Validation
    foreach ($rules as $key => $rule) {
        $type = $rule["type"];
        $optional = $rule["optional"] ?? false;

        if (!in_array($type, $fieldTypes)) {
            throw new Error("Tipe '$type' pada field '$key', tidak valid. Tipe yang didukung: " . join(", ", $fieldTypes), 400);
        }

        $hasField = array_key_exists($key, $fields) && $fields[$key] !== "";
        $hasFile = isset($normalizedFiles[$key]);

        if (!$hasField && !$hasFile) {
            if (!$optional) $missingFields[] = $key;
            continue;
        }

        $value = $hasFile ? $normalizedFiles[$key] : $fields[$key];

        if ($hasFile) {
            $meta = $fileMeta[$type];
            $minFile = $rule["min"] ?? null;
            $maxFile = $rule["max"] ?? null;

            if (is_numeric($minFile) && count($value) < $minFile) {
                throw new Error("Jumlah file pada field '$key' tidak boleh kurang dari $minFile file.", 400);
            }

            if (is_numeric($maxFile) && count($value) > $maxFile) {
                throw new Error("Jumlah file pada field '$key' tidak boleh melebihi $maxFile file.", 400);
            }

            foreach ($value as $file) {
                if ($type !== "file" && !in_array($file["type"], $meta["mimeTypes"])) {
                    throw new Error("File '{$file["name"]}' pada field '$key' memiliki format tidak valid (MIME: {$file["type"]}). Format yang didukung: " . join(", ", $meta["extensions"]), 400);
                }
                if ($file["size"] > $meta["size"]["byte"]) {
                    throw new Error("Ukuran file '{$file["name"]}' pada field '$key' tidak boleh melebihi {$meta["size"]["mb"]} MB.");
                }
            }
        } else {
            if (!checkType($value, $type)) throw new Error("Field '$key' harus berupa $type yang valid.");

            switch ($type) {
                case "string":
                    $min = $rule["min"] ?? null;
                    $max = $rule["max"] ?? null;
                    if ($min !== null && strlen($value) < $min) {
                        throw new Error("$key harus terdiri dari minimal $min karakter.");
                    }
                    if ($max !== null && strlen($value) > $max) {
                        throw new Error("$key tidak boleh melebihi $max karakter.");
                    }
                    break;

                case "password":
                    $min = $rule["min"] ?? 8;
                    $max = $rule["max"] ?? 255;
                    if (strlen($value) < $min) {
                        throw new Error("$key harus terdiri dari minimal $min karakter.");
                    }
                    if (strlen($value) > $max) {
                        throw new Error("$key tidak boleh melebihi $max karakter.");
                    }
                    if (!preg_match('/[A-Z]/', $value)) {
                        throw new Error("$key harus mengandung huruf kapital (A-Z).");
                    }
                    if (!preg_match('/[a-z]/', $value)) {
                        throw new Error("$key harus mengandung huruf kecil (a-z).");
                    }
                    if (!preg_match('/[0-9]/', $value)) {
                        throw new Error("$key harus mengandung angka (0-9).");
                    }
                    if (!preg_match('/[^A-Za-z0-9]/', $value)) {
                        throw new Error("$key harus mengandung karakter khusus.");
                    }
                    break;

                case "number":
                    $min = $rule["min"] ?? null;
                    $max = $rule["max"] ?? null;
                    $value = floatval($value);
                    if ($min !== null && $value < $min) {
                        throw new Error("$key tidak boleh kurang dari $min.");
                    }
                    if ($max !== null && $value > $max) {
                        throw new Error("$key tidak boleh melebihi $max.");
                    }
                    break;

                case "boolean":
                    $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                    break;

                case "date":
                case "time":
                    $min = $rule["min"] ?? null;
                    $max = $rule["max"] ?? null;
                    $ts = strtotime($value);
                    if ($min !== null && $ts < strtotime($min)) {
                        throw new Error("$key tidak boleh sebelum $min.");
                    }
                    if ($max !== null && $ts > strtotime($max)) {
                        throw new Error("$key tidak boleh setelah $max.");
                    }
                    break;

                case "array":
                    $min = $rule["min"] ?? null;
                    $max = $rule["max"] ?? null;
                    if ($min !== null && count($value) < $min) {
                        throw new Error("$key harus memiliki minimal $min item.");
                    }
                    if ($max !== null && count($value) > $max) {
                        throw new Error("$key tidak boleh memiliki melebihi $max item.");
                    }
                    break;
            }
        }

        $validated[$key] = $value;
    }

    if (!empty($missingFields)) throw new Error("Data yang diperlukan tidak lengkap: " . join(", ", $missingFields), 400);
    return $validated;
}
