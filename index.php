<?php

session_start();

$mainDir = $_SERVER["DOCUMENT_ROOT"];
$uploadDir = $mainDir . "/upload";
$src = $mainDir . "/src";

$requestURL = filter_var($_SERVER["REQUEST_URI"], FILTER_SANITIZE_URL);
$requestURL = rtrim($requestURL, "/");
$requestURL = strtok($requestURL, "?");
$isAPI = str_starts_with($requestURL, "/api");

// ! Enable this for extract `/out` next static export
require_once $src . "/next-static.php";

require_once $src . "/meta.php";
require_once $src . "/response.php";

require_once $src . "/checker.php";
require_once $src . "/router.php";

if ($isAPI) {
    header("Content-Type: application/json");
    header("Access-Control-Allow-Origin: http://localhost:3000"); // TODO : FrontEnd Origin
    header("Access-Control-Allow-Headers: Content-Type");
    require_once $src . "/utils.php";
    require_once $src . "/db.php";
    require_once $src . "/session.php";
}

$directories = ["avatar" => $uploadDir . "/avatar"];
checkDirectories();

// * PAGE ROUTES
pageRoute("/", true);
pageRoute("/sign-in");

// * API ROUTES
$apiPath = "/src/api";
route(["GET", "POST"], "/api", "$apiPath/example.php");

route(["POST", "DELETE"], "/api/sign", "$apiPath/auth/sign.php");
route(["GET", "POST"], "/api/user", "$apiPath/auth/user.php", true);
route(["DELETE"], "/api/user/:id", "$apiPath/auth/user.php", true);

// * NOT FOUND
route("ANY", "/404", "NOT FOUND");
