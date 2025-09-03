<?php

function route(string|array $methods, string $route, string $filePath)
{
    if (!checkMethod($methods)) {
        return;
    }

    global $mainDir, $requestURL, $isAPI;

    if ($route == "/404") {
        if ($isAPI) {
            responseError(new Error("Sumber daya yang diminta tidak ditemukan.", 404));
        } else {
            include_once $mainDir . "/404.html";
        }

        exit();
    }

    $routeParts = array_slice(explode("/", $route), 1);
    $requestURLParts = array_slice(explode("/", $requestURL), 1);

    if (empty($routeParts[0]) && empty($requestURLParts)) {
        include_once $mainDir . $filePath;
        exit();
    }

    if (count($routeParts) != count($requestURLParts)) {
        return;
    }

    $params = [];
    foreach ($routeParts as $i => $part) {
        if (isset($part[0]) && $part[0] === ":") {
            $params[substr($part, 1)] = $requestURLParts[$i];
        } elseif ($part !== $requestURLParts[$i]) {
            return;
        }
    }

    include_once $mainDir . $filePath;
    exit();
}

function pageRoute(string $route)
{
    $filePath = $route === "/" ? "/index" : $route;
    route("GET", $route, "$filePath.html");
}
