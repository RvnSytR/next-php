import { $ } from "bun";
import { existsSync, rmSync, cpSync, mkdirSync } from "fs";
import { join } from "path";

const root = process.cwd();
const nextDir = join(root, "next");
const outDir = join(nextDir, "out");
const phpDir = join(root, "php");
const mainDir = join(root, "main");

const unnecessaryMainDir = [
  "src/bruno",
  // "src/upload"
];

console.log("🚀 Starting deployment script...");

if (existsSync(mainDir)) {
  console.log("🧹 Cleaning old /main...");
  rmSync(mainDir, { recursive: true, force: true });
}

console.log("📂 Copying /php to /main...");
cpSync(phpDir, mainDir, { recursive: true });

unnecessaryMainDir.forEach((folder) => {
  const path = join(mainDir, folder);
  if (existsSync(path)) {
    console.log(`🗑 Removing ${path}...`);
    rmSync(path, { recursive: true, force: true });
  }
});

// async function main() {
//   // Step 2: check /next/out exists
//   if (!existsSync(outDir)) {
//     console.error(
//       "❌ Error: /next/out not found. Run `bun run build` first in /next."
//     );
//     process.exit(1);
//   }

//   // Step 3: copy /next/out → /www/out
//   console.log("📂 Copying /next/out → /www/out...");
//   const wwwOutDir = join(mainDir, "out");
//   cpSync(outDir, wwwOutDir, { recursive: true });

//   // Step 5: run PHP deployNextStatic
//   console.log("⚡ Running PHP deploy script...");
//   const result = await $`php -r "require '${join(
//     mainDir,
//     "deployNext.php"
//   )}';"`.quiet();

//   if (result.exitCode === 0) {
//     console.log("✅ Deployment done! Check /www for final build.");
//   } else {
//     console.error("❌ PHP deploy script failed.");
//     process.exit(result.exitCode);
//   }
// }
