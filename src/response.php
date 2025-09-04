<?php

function response(int $code, bool $success, string $message, array $props = [])
{
    $httpCode = checkHttpStatusCode($code) ? $code : 500;
    $data = $props["data"] ?? null;
    $res = ["code" => $httpCode, "success" => $success, "message" => $message, "data" => $data];

    http_response_code($httpCode);
    echo json_encode(array_merge($res, $props));
    exit();
}

function responseSuccess(array $props = [])
{
    response(
        $props["code"] ?? 200,
        true,
        $props["message"] ?? "Success",
        $props
    );
}

function responseError(Throwable $th, array $props = [])
{
    global $messages;
    response(
        !empty($th->getCode()) ? $th->getCode() : 500,
        false,
        $th->getMessage() ?? $messages["error"],
        $props
    );
}
