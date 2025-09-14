<?php

function setSession(array $data)
{
    $_SESSION["id"] = $data["id"];
    $_SESSION["name"] = $data["name"];
    $_SESSION["image"] = $data["image"];
    $_SESSION["email"] = $data["email"];
    $_SESSION["role"] = $data["role"];
    $_SESSION["updated_at"] = $data["updated_at"];
    $_SESSION["created_at"] = $data["created_at"];
}

function destroySession()
{
    session_unset();
    session_destroy();
}
