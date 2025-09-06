<?php

function route(string|array $methods, string $route, string $filePath, bool $isAuthenticated = false)
{
    if (!checkMethod($methods)) {
        return;
    }

    global $mainDir, $requestURL, $isAPI;

    if ($requestURL === "/sign-in" && isset($_SESSION["id"])) {
        header("Location: /dashboard");
        exit();
    }

    if ($route === "/404") {
        if ($isAPI) {
            responseError(new Error("Sumber daya yang diminta tidak ditemukan.", 404));
        } else {
            include_once $mainDir . "/404.html";
        }

        exit();
    }



    $checkIsAuthenticated = function (array $roles = []) use ($isAPI, $mainDir) {
        if (!isset($_SESSION["id"])) {
            if ($isAPI) {
                responseError(new Error("Permintaan tidak terautentikasi!", 401));
            } else {
                header("Location: /sign-in");
                exit();
            }
        }

        if (
            !empty($roles) &&
            (!isset($_SESSION["role"]) || !in_array($_SESSION["role"], $roles))
        ) {
            if ($isAPI) {
                responseError(new Error("Permintaan ini tidak diperbolehkan!", 403));
            } else {
                include_once $mainDir . "/404.html";
                exit();
            }
        }
    };

    $routeParts = array_slice(explode("/", $route), 1);
    $requestURLParts = array_slice(explode("/", $requestURL), 1);

    if (empty($routeParts[0]) && empty($requestURLParts)) {
        if ($isAuthenticated) $checkIsAuthenticated();
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

    if ($isAuthenticated) $checkIsAuthenticated();
    include_once $mainDir . $filePath;
    exit();
}

function pageRoute(string $route, bool $isAuthenticated = false)
{
    $filePath = $route === "/" ? "/index" : $route;
    route("GET", $route, "$filePath.html", $isAuthenticated);
}
