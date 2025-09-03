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
