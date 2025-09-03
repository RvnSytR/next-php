<?php

function response(int $code, string $message, array $props = [])
{
    $httpCode = checkHttpStatusCode($code) ? $code : 500;
    $data = $props["data"] ?? null;
    $res = ["code" => $httpCode, "message" => $message, "data" => $data];

    http_response_code($httpCode);
    echo json_encode(array_merge($res, $props));

    exit();
}

function responseSuccess(array $props = [])
{
    response($props["code"] ?? 200, $props["message"] ?? "success", $props);
}

function responseError(Throwable $th, array $props = [])
{
    global $messages;
    response(
        !empty($th->getCode()) ? $th->getCode() : 500,
        $th->getMessage() ?? $messages["error"],
        $props
    );
}
