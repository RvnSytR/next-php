<?php

session_start();

$docRoot = $_SERVER["DOCUMENT_ROOT"];
$uploadDir = $docRoot . "/upload";
$src = $docRoot . "/src";

$requestURL = filter_var($_SERVER["REQUEST_URI"], FILTER_SANITIZE_URL);
$requestURL = rtrim($requestURL, "/");
$requestURL = strtok($requestURL, "?");
$isAPI = str_starts_with($requestURL, "/api");

require_once $src . "/meta.php";
require_once $src . "/response.php";

require_once $src . "/checker.php";
require_once $src . "/router.php";

require_once $src . "/init.php";

// * PAGE ROUTES
if (!$isAPI) {
    pageRoute("/");
    pageRoute("/sign-in");
    pageRoute("/dashboard", "all");
    pageRoute("/dashboard/users", ["admin"]);
}

// * API ROUTES
if ($isAPI) {
    header("Content-Type: application/json");
    header("Access-Control-Allow-Origin: http://localhost:3000"); // TODO : FrontEnd Origin
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");

    if ($_SERVER["REQUEST_METHOD"] == "OPTIONS") {
        http_response_code(200);
        exit();
    }

    require_once $src . "/utils.php";
    require_once $src . "/db.php";

    apiRoute(["GET", "POST"], "/api", "/example.php");

    apiRoute("POST", "/api/auth/login", "/auth/basic.php");
    apiRoute("DELETE", "/api/auth/logout", "/auth/basic.php");
    apiRoute("POST", "/api/auth/register", "/auth/register.php");

    apiRoute(["GET", "POST"], "/api/me", "/auth/me.php", "all");
    apiRoute("POST", "/api/me/password", "/auth/me-action.php", "all");
    apiRoute("DELETE", "/api/me/avatar", "/auth/me-action.php", "all");

    apiRoute(["GET", "POST", "DELETE"], "/api/users", "/auth/users.php", "all");
    apiRoute(
        ["GET", "POST", "DELETE"],
        "/api/users/:id",
        "/auth/users-id.php",
        "all",
    );

    apiRoute(["GET", "POST"], "/api/:json", "/json.php", ["admin"]);
}

// * NOT FOUND
route("ANY", "/404", "NOT FOUND");
