<?php

function toBytes($mb)
{
    return $mb * 1024 * 1024;
}

$otherFileMeta = [
    "image" => [
        "mimeTypes" => ["image/png", "image/jpeg", "image/svg+xml", "image/webp"],
        "extensions" => [".png", ".jpg", ".jpeg", ".svg", ".webp"],
        "size" => ["mb" => 2, "byte" => toBytes(2)],
    ],

    "document" => [
        "mimeTypes" => [
            "application/pdf",
            "application/msword",
            "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
            "application/vnd.ms-excel",
            "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
            "application/vnd.ms-powerpoint",
            "application/vnd.openxmlformats-officedocument.presentationml.presentation",
        ],
        "extensions" => [".pdf", ".doc", ".docx", ".xls", ".xlsx", ".ppt", ".pptx"],
        "size" => ["mb" => 2, "byte" => toBytes(2)],
    ],

    "archive" => [
        "mimeTypes" => [
            "application/zip",
            "application/x-rar-compressed",
            "application/x-7z-compressed",
            "application/x-tar",
        ],
        "extensions" => [".zip", ".rar", ".7z", ".tar"],
        "size" => ["mb" => 20, "byte" => toBytes(20)],
    ],

    "audio" => [
        "mimeTypes" => ["audio/mpeg", "audio/wav", "audio/ogg", "audio/flac"],
        "extensions" => [".mp3", ".wav", ".ogg", ".flac"],
        "size" => ["mb" => 10, "byte" => toBytes(10)],
    ],

    "video" => [
        "mimeTypes" => ["video/mp4", "video/x-msvideo", "video/x-matroska", "video/ogg", "video/webm"],
        "extensions" => [".mp4", ".avi", ".mkv", ".ogg", ".webm"],
        "size" => ["mb" => 50, "byte" => toBytes(50)],
    ],
];

$maxFileSize = max(array_map(function ($item) {
    return $item["size"]["mb"];
}, $otherFileMeta));

$fileMeta = array_merge([
    "file" => [
        "mimeTypes" => array_merge(...array_values(array_map(fn($item) => $item["mimeTypes"], $otherFileMeta))),
        "extensions" => array_merge(...array_values(array_map(fn($item) => $item["extensions"], $otherFileMeta))),
        "size" => ["mb" => $maxFileSize, "byte" => toBytes($maxFileSize)],
    ],
], $otherFileMeta);

$fieldTypes = array_merge(
    [
        "string",
        "number",
        "boolean",
        "date",
        "time",
        "array",
        "json",
        "email",
        "password",
    ],
    array_keys($fileMeta)
);
