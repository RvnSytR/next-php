<?php

function route(string|array $methods, string $route, string $filePath, string|array $roles = [])
{
    if (!checkMethod($methods)) return;
    global $rootDir, $requestURL, $isAPI;

    if ($requestURL === "/sign-in" && isset($_SESSION["id"])) {
        header("Location: /dashboard");
        exit();
    }

    if ($route === "/404") {
        if ($isAPI) responseError(new Error("Sumber daya yang diminta tidak ditemukan.", 404));
        else include_once $rootDir . "/404.html";
        exit();
    }

    $requireAuth = (is_string($roles) && strtolower($roles) === "all") || (is_array($roles) && !empty($roles));
    $checkIsAuthenticated = function () use ($roles, $isAPI, $rootDir) {
        if (!isset($_SESSION["id"])) {
            if ($isAPI) responseError(new Error("Permintaan tidak terautentikasi!", 401));
            else {
                header("Location: /sign-in");
                exit();
            }
        }

        if (is_array($roles) && !empty($roles)) {
            if (!isset($_SESSION["role"]) || !in_array($_SESSION["role"], $roles)) {
                if ($isAPI) responseError(new Error("Permintaan ini tidak diperbolehkan!", 403));
                else {
                    include_once $rootDir . "/404.html";
                    exit();
                }
            }
        }
    };

    $routeParts = array_slice(explode("/", $route), 1);
    $requestURLParts = array_slice(explode("/", $requestURL), 1);

    if (empty($routeParts[0]) && empty($requestURLParts)) {
        if ($requireAuth) $checkIsAuthenticated();
        include_once $rootDir . $filePath;
        exit();
    }

    if (count($routeParts) != count($requestURLParts)) return;

    $params = [];
    foreach ($routeParts as $i => $part) {
        if (isset($part[0]) && $part[0] === ":") {
            $params[substr($part, 1)] = $requestURLParts[$i];
        } elseif ($part !== $requestURLParts[$i]) {
            return;
        }
    }

    if ($requireAuth) $checkIsAuthenticated();
    include_once $rootDir . $filePath;
    exit();
}

function pageRoute(string $route, string|array $roles = [])
{
    $filePath = $route === "/" ? "/index" : $route;
    route("GET", $route, "$filePath.html", $roles);
}

function apiRoute(string|array $methods, string $route, string $apiFilePath, string|array $roles = [])
{
    route($methods, $route, "/src/api$apiFilePath", $roles);
}
