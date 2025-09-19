# Next.JS with PHP Starter Template

Starter template based on [`next-starter`](https://github.com/RvnSytR/next-starter). This template is designed to speed up the process of setting up a **Next.js frontend with a PHP backend**.

The main goal of this template is to build projects with Next.js as the frontend stack, while still allowing the final app to be hosted on affordable and widely available hosting platforms (e.g cPanel, hPanel, DirectAdmin, etc.).

Since this project is **upstreamed** from `next-starter`, most of the features remain the same. The key difference is that instead of using Next.js’ built-in server and API routes, this setup relies on **PHP as the backend**.

## Good to know

The frontend is generated using [Next.js static export](https://nextjs.org/docs/app/guides/static-exports). This means:

- **Limitations:** Next.js server-side features and other [unsupported features](https://nextjs.org/docs/app/guides/static-exports#unsupported-features) are not available.
- **Replacements:** In this template, features such as routing, dynamic routes, authentication, database connections, etc. are handled by **PHP in the final build**.
- **Data fetching:** All data fetching happens on the **client-side**.

## Getting Started

### Installation

Create a new repository using this template or clone the repository directly:

```sh
git clone https://github.com/RvnSytR/next-php
cd next-php
```

Install the frontend dependencies:

```sh
cd next && bun install
```

### Database Setup

Unlike `next-starter` (which uses Postgres), this template uses MySQL. The database schema is provided in [/php/schema.sql](/php/schema.sql).

You can import it in one of two ways:

#### Option 1: phpMyAdmin (recommended)

1. Open phpMyAdmin in your local server environment (Laragon, XAMPP, etc.).
2. Create a new database (e.g. `starter`).
3. Go to the **Import** tab and upload `schema.sql`.

#### Option 2: MySQL CLI

If you prefer the terminal:

```sh
mysql -u your_username -p your_database < php/schema.sql
```

### Development Server

#### Backend (PHP)

I recommend using local server environment such as [Laragon](https://laragon.org/), [XAMPP](https://www.apachefriends.org/), or similar.

Using **Laragon** as an example:

1. Open **Preferences**.
2. Set **Document Root** to the **contents** of `/php`:

   ```sh
   .../next-php/php
   ```

3. Restart Apache.

Your PHP backend will now be available at [http://localhost](http://localhost).

#### Frontend (Next.js)

Run the Next.js development server:

```sh
bun run dev
# or explicitly:
cd next && bun run dev
```

### Building

To generate the production build, run:

```sh
bun run build
```

This script will:

1. Build the static Next.js frontend.
2. Bundle it together with the PHP backend inside the /build directory.

#### Note

This project’s build script (`build.ts`) uses Bun’s built-in [`$`](https://bun.sh/docs/runtime/shell) API for running shell commands.

Because of that, you **must have Bun installed** for the script to work.

If you prefer another package manager, you’ll need to:

- Replace `bun` with your package manager in commands.
- Rewrite `build.ts` to use Node.js equivalents (e.g. `child_process.exec`) instead of Bun’s `$`.

### Production

The final app is output to the [/build](/build) directory.

This directory contains everything you need for deployment (Next.js Frontend + PHP backend).

#### Previewing locally

If you want to preview the production build with Laragon:

1. Open **Preferences**.
2. Set **Document Root** to the **contents** of `/build`:

   ```sh
   .../next-php/build
   ```

3. Restart Apache.
4. Visit [http://localhost](http://localhost).

#### Deployment to cPanel (or similar)

1. Open your hosting file manager (usually the root directory is `/www` or `/public_html`).
2. Upload the **contents** of `/build` directly into that root directory. It should be:
   - `/build/index.php` → `/www/index.php`
   - `/build/src/...` → `/www/src/...`
3. The app should be available immediately at your domain.
