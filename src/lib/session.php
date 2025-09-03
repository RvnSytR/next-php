<?php

function setSession(array $data)
{
    $_SESSION["id"] = $data["id"];
    $_SESSION["name"] = $data["name"];
    $_SESSION["image"] = $data["image"];
    $_SESSION["email"] = $data["email"];
    $_SESSION["role"] = $data["role"];
}

function destroySession()
{
    session_unset();
    session_destroy();
}
