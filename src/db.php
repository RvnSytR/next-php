<?php

define("MYSQL_HOST", "localhost");
define("MYSQL_USER", "root");
define("MYSQL_PASSWORD", "");
define("MYSQL_DATABASE", "starter");

try {
    $conn = new mysqli(MYSQL_HOST, MYSQL_USER, MYSQL_PASSWORD, MYSQL_DATABASE);
} catch (mysqli_sql_exception $e) {
    responseError($e);
}

function executeStmt(mysqli_stmt $stmt)
{
    $stmt->execute();
    return $stmt->get_result();
}

$db = [
    "user" => [
        "insert" => function (array $data) use ($conn) {
            $stmt = $conn->prepare(
                "INSERT INTO user (id, email, password, name, image, role) VALUES (?, ?,?, ?, ?, ?)"
            );
            $stmt->bind_param(
                "ssssss",
                $data["id"],
                $data["email"],
                $data["password"],
                $data["name"],
                $data["image"],
                $data["role"]
            );
            return $stmt->execute();
        },

        "select" => function () use ($conn) {
            $stmt = $conn->prepare(
                "SELECT id, email, name, image, role, updated_at, created_at FROM user ORDER BY created_at DESC"
            );
            return executeStmt($stmt);
        },

        "select-by-id" => function (string $id) use ($conn) {
            $stmt = $conn->prepare("SELECT * FROM user WHERE id=?");
            $stmt->bind_param("s", $id);
            return executeStmt($stmt);
        },

        "select-by-email" => function (string $email) use ($conn) {
            $stmt = $conn->prepare("SELECT * FROM user WHERE email=?");
            $stmt->bind_param("s", $email);
            return executeStmt($stmt);
        },

        "select-name&image-by-id" => function (string $id) use ($conn) {
            $stmt = $conn->prepare("SELECT name, image FROM user WHERE id=?");
            $stmt->bind_param("s", $id);
            return executeStmt($stmt);
        },

        "select-password-by-id" => function (string $id) use ($conn) {
            $stmt = $conn->prepare("SELECT password FROM user WHERE id=?");
            $stmt->bind_param("s", $id);
            return executeStmt($stmt);
        },

        "update-name&image-by-id" => function (array $data) use ($conn) {
            $stmt = $conn->prepare("UPDATE user SET name=?, image=? WHERE id=?");
            $stmt->bind_param("sss", $data["name"], $data["image"], $data["id"]);
            return executeStmt($stmt);
        },

        "update-password-by-id" => function (string $id, string $password) use ($conn) {
            $stmt = $conn->prepare("UPDATE user SET password=? WHERE id=?");
            $stmt->bind_param("ss", $password, $id);
            return executeStmt($stmt);
        },

        // "update-role-by-id" => function (array $data) use ($conn) {
        //     $stmt = $conn->prepare("UPDATE user SET name=?, role=? WHERE id=?");
        //     $stmt->bind_param("sss", $data["name"], $data["role"], $data["id"]);
        //     return executeStmt($stmt);
        // },

        "remove" => function (string $id) use ($conn) {
            $stmt = $conn->prepare("DELETE FROM user WHERE id=?");
            $stmt->bind_param("s", $id);
            return executeStmt($stmt);
        },
    ]
];
