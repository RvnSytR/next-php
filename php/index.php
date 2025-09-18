<?php

session_start();

$docRoot = $_SERVER["DOCUMENT_ROOT"];
$src = $docRoot . "/src";
$uploadDir = $docRoot . "/upload";
$configDir = $src . "/configs";

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
    pageRoute("/dashboard/profile", "all");
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

    route(["GET", "POST"], "/api", "/api/example.php");
    route(["GET", "POST"], "/api/configs/:key", "/api/json.php", ["admin"]);

    route("POST", "/api/auth/login", "/api/auth/basic.php");
    route("DELETE", "/api/auth/logout", "/api/auth/basic.php");
    route("POST", "/api/auth/register", "/api/auth/register.php");

    route(["GET", "POST"], "/api/me", "/api/auth/me.php", "all");
    route("DELETE", "/api/me/avatar", "/api/auth/me-action.php", "all");
    route("POST", "/api/me/password", "/api/auth/me-action.php", "all");

    route(
        ["GET", "POST", "DELETE"],
        "/api/users",
        "/api/auth/users.php",
        "all",
    );
    route(
        ["GET", "POST", "DELETE"],
        "/api/users/:id",
        "/api/auth/users-id.php",
        "all",
    );
}

// * NOT FOUND
route("ANY", "/404", "NOT FOUND");
