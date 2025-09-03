<?php

$mainDir = $_SERVER["DOCUMENT_ROOT"];
$libDir = $mainDir . "/src/lib";
$uploadDir = $mainDir . "/upload";

$requestURL = filter_var($_SERVER["REQUEST_URI"], FILTER_SANITIZE_URL);
$requestURL = rtrim($requestURL, "/");
$requestURL = strtok($requestURL, "?");
$isAPI = str_starts_with($requestURL, "/api");

// ! Enable this for extract `/out` next static export
require_once $libDir . "/next-static.php";

require_once $libDir . "/meta.php";
require_once $libDir . "/response.php";

require_once $libDir . "/checker.php";
require_once $libDir . "/router.php";

if ($isAPI) {
    header("Content-Type: application/json");
    require_once $libDir . "/action.php";
    require_once $libDir . "/session.php";
}

$directories = ["avatar" => $uploadDir . "/avatar"];
checkDirectories();

// * PAGE ROUTES
// pageRoute("/");

// * API ROUTES
route(["GET", "POST"], "/api", "/src/api/example.php");

// * NOT FOUND
route("ANY", "/404", "NOT FOUND");
