# 🚀 Laravel Docker Development Environment

This repository contains a professional Dockerized Laravel environment with PHP 8.4, MySQL 8.0 (Dual-DB setup), Nginx, and Bun.

---

## 🛠 1. Startup Guide (Daily Use)
*Use these commands for your daily development routine.*

### Start the environment
```bash
docker-compose up -d
```

### Install dependencies (if updated)
```bash
docker compose exec app composer install
docker compose exec app bun install
```

### Run database migrations
```bash
docker compose exec app php artisan migrate
```

### Stop the environment
```bash
docker-compose down
```
> **Note:** Use `docker-compose down -v` to completely wipe the database and volumes.

---

## 🏗 2. Project Initialization (New Setup)
*Step-by-step guide to deploying the project for the first time.*

### Step 1: Initial Build
Ensure `docker-compose.yml`, `Dockerfile`, and `nginx.conf` are in the root.
```bash
docker-compose up -d --build
```

### Step 2: Install Laravel Framework
Using a temporary folder to avoid "directory not empty" errors:
```bash
docker compose exec app bash

# Run these inside the container:
composer create-project laravel/laravel temp_install
mv temp_install/* .
mv temp_install/.* .
rm -rf temp_install

# Set mandatory Linux permissions:
chmod -R 777 storage bootstrap/cache
```

### Step 3: Environment Setup (.env)
1. Configure your `.env` file to use Docker service names:
    - `DB_HOST=mysql`
    - `DB_DATABASE=fb_mysql`
    - `DB_USERNAME=fb_mysql`
    - `DB_PASSWORD=fb_mysql`
2. Generate the application key:
   ```bash
   php artisan key:generate
   ```
3. Create `.env.testing` for TDD and set `DB_HOST=mysql_test`.

### Step 4: Install Quality & Dev Tools
```bash
# Static analysis and IDE support
composer require larastan/larastan barryvdh/laravel-ide-helper --dev

# Generate helper files for Cursor/PhpStorm
php artisan ide-helper:generate
php artisan ide-helper:models -N
```

### Step 5: Frontend Assets (Bun)
```bash
bun install
bun run dev
```

---

## 📋 Useful Commands (Cheat Sheet)

| Task                     | Command                            |
|:-------------------------|:-----------------------------------|
| **Shell Access**         | `docker compose exec app bash`     |
| **Run Tests (TDD)**      | `php artisan test`                 |
| **Fix Code Style**       | `vendor/bin/pint`                  |
| **Static Analysis**      | `vendor/bin/phpstan analyse`       |
| **Fresh Database**       | `php artisan migrate:fresh --seed` |
| **run rector/pint/stan** | `composer lint`                    |
