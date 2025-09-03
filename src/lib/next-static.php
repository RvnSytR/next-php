<?php

/**
 * Deploy Next.js static export from `/out` to the root of this project or `/`
 */

function deployNextStatic()
{
    $mainDir = $_SERVER["DOCUMENT_ROOT"];
    $outDir  = $mainDir . "/out";

    // ! Disable this to remove all next static files
    if (!is_dir($outDir)) {
        return;
    }

    // Files/folders to never delete
    $forbidden = [
        ".git",
        "src",
        "upload",
        ".gitignore",
        ".htaccess",
        "index.php",
    ];

    // Helper: delete recursively but skip forbidden
    $deleteRecursive = function ($path) use ($forbidden, $mainDir, &$deleteRecursive) {
        $relPath = str_replace($mainDir . "/", "", $path);

        if (in_array($relPath, $forbidden)) {
            return; // Skip forbidden files/folders
        }

        if (is_dir($path)) {
            $items = array_diff(scandir($path), [".", ".."]);
            foreach ($items as $item) {
                $deleteRecursive($path . "/" . $item);
            }
            @rmdir($path);
        } else {
            @unlink($path);
        }
    };

    // Step 1: Clean /main (except forbidden + out folder)
    $items = array_diff(scandir($mainDir), [".", ".."]);
    foreach ($items as $item) {
        if ($item === "out") continue; // skip out until later
        $deleteRecursive($mainDir . "/" . $item);
    }

    // Step 2: Move /out content to /main
    if (is_dir($outDir)) {
        $moveRecursive = function ($src, $dst) use (&$moveRecursive) {
            if (is_dir($src)) {
                if (!is_dir($dst)) {
                    mkdir($dst, 0755, true);
                }
                $items = array_diff(scandir($src), [".", ".."]);
                foreach ($items as $item) {
                    $moveRecursive("$src/$item", "$dst/$item");
                }
                @rmdir($src);
            } else {
                rename($src, $dst);
            }
        };
        $moveRecursive($outDir, $mainDir);
    }
}

deployNextStatic();
