<?php

session_start();

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
}

$directories = ["avatar" => $uploadDir . "/avatar"];
checkDirectories();

// * PAGE ROUTES
pageRoute("/");
pageRoute("/sign-in");
pageRoute("/dashboard", true);
pageRoute("/dashboard/users", true);

// * API ROUTES
apiRoute(["GET", "POST"], "/api", "/example.php");
apiRoute(["POST", "DELETE"], "/api/sign", "/auth/sign.php");

apiRoute(["GET", "POST", "DELETE"], "/api/profile", "/auth/profile.php", true);

apiRoute(["GET", "POST", "DELETE"], "/api/user", "/auth/user.php", true);
apiRoute(["POST"], "/api/user/password", "/auth/password.php", true);
apiRoute(["GET", "POST", "DELETE"], "/api/user/:id", "/auth/user-id.php", true);

// * NOT FOUND
route("ANY", "/404", "NOT FOUND");
