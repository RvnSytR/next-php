import { $ } from "bun";
import { cpSync, existsSync, rmSync } from "fs";
import { join } from "path";

const root = process.cwd();
const nextDir = join(root, "next");
const outDir = join(nextDir, "out");
const phpDir = join(root, "php");
const buildDir = join(root, "build");

const unnecessaryBuildFolders = [
  "bruno",
  // "upload"
];

console.log("🚀  Starting next-php build...");

if (existsSync(buildDir)) {
  console.log("🧹  Cleaning old /build...");
  rmSync(buildDir, { recursive: true, force: true });
}

console.log("📤  Copying /php → /build...");
cpSync(phpDir, buildDir, { recursive: true });

if (!existsSync(join(buildDir, "index.php"))) {
  console.error("❌  index.php missing in /php. Aborting.");
  process.exit();
}

let removed = false;
unnecessaryBuildFolders.forEach((folder) => {
  const path = join(buildDir, folder);
  if (existsSync(path)) {
    console.log(`📂  Removing /${folder}...`);
    rmSync(path, { recursive: true, force: true });
    removed = true;
  }
});
if (!removed) console.log("📂  No unnecessary folders removed.");

if (existsSync(outDir)) {
  const answer = prompt("❔  Do you want to fresh build Next.js? (y/N): ");
  if (answer?.toLowerCase() === "y") {
    console.log("🚀  Running fresh Next.js build...\n");
    await $`cd next && bun run build`;
  } else {
    console.log("⏩  Skipping fresh build, using existing /next/out...");
  }
} else {
  console.log("🚀  /next/out not found. Building Next.js App...\n");
  await $`cd next && bun run build`;
}

console.log("📤  Copying /next/out → /build...");
cpSync(outDir, buildDir, { recursive: true });

console.log("✅  Build complete! Check /build for final app.");
