<?php

session_start();
$isProd = true;

$mainDir = $_SERVER["DOCUMENT_ROOT"];
$uploadDir = $mainDir . "/upload";
$src = $mainDir . "/src";

$requestURL = filter_var($_SERVER["REQUEST_URI"], FILTER_SANITIZE_URL);
$requestURL = rtrim($requestURL, "/");
$requestURL = strtok($requestURL, "?");
$isAPI = str_starts_with($requestURL, "/api");

// ! Enable this to extract `/out` next static export
require_once $src . "/next-static.php";

require_once $src . "/meta.php";
require_once $src . "/response.php";

require_once $src . "/checker.php";
require_once $src . "/router.php";

if ($isAPI) {
    header("Content-Type: application/json");
    header("Access-Control-Allow-Origin: http://localhost:3000"); // TODO : FrontEnd Origin
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");

    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        http_response_code(200);
        exit();
    }

    require_once $src . "/utils.php";
    require_once $src . "/db.php";
    require_once $src . "/session.php";
}

$directories = ["avatar" => $uploadDir . "/avatar"];
checkDirectories();

// * PAGE ROUTES
pageRoute("/");
pageRoute("/sign-in");
pageRoute("/dashboard", $isProd);
pageRoute("/dashboard/users", $isProd);

// * API ROUTES
route(["GET", "POST"], "/api", "/src/api/example.php");
route(["POST", "DELETE"], "/api/sign", "/src/api/auth/sign.php");

route(["GET", "POST"], "/api/user", "/src/api/auth/user.php", $isProd);
route(["GET", "POST", "DELETE"], "/api/profile", "/src/api/auth/profile.php", $isProd);
route(["POST"], "/api/user/password", "/src/api/auth/password.php", $isProd);
route(["GET", "DELETE"], "/api/user/:id", "/src/api/auth/user.php", $isProd);

// * NOT FOUND
route("ANY", "/404", "NOT FOUND");
