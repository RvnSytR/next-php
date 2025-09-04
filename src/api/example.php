<?php

action("GET", fn() => responseSuccess(["message" => "Hello world!"]));

$uploadCtx = [];
action(
    "POST",
    function ($db) use ($params, &$uploadCtx) {
        $data = checkFields($_POST, [
            "text" => [
                "type" => "string",
                "min" => 2,
                "max" => 255,
                // "optional" => true
            ],
            "number" => [
                "type" => "number",
                "min" => 1,
                "max" => 100,
            ],
            "email" => ["type" => "email"],
            "password" => ["type" => "password"],
            "boolean" => ["type" => "boolean"],
            "date" => [
                "type" => "date",
                "min" => "2025-01-01 00:00:00",
                "max" => "2025-01-30 23:59:59",
            ],
            "time" => [
                "type" => "time",
                "min" => "08:00:00",
                "max" => "17:00:00",
            ],
            "array" => [
                "type" => "array",
                "min" => 1,
                "max" => 4,
            ],
            "file" => [
                "type" => "image",
                "optional" => true,
                // "min" => 1,
                // "max" => 5,
            ],
        ]);

        // $uploadCtx = uploadFiles($data["file"] ?? [], "avatar", [
        //     "date" => true,
        //     "prefix" => "prefix",
        //     "suffix" => "suffix"
        // ]);

        responseSuccess(["data" => $data]);
    },
    [
        // ? do something before responseError()
        "onError" => function () use (&$uploadCtx) {
            removeFiles($uploadCtx);
        },
    ]
);
