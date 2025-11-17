# Installation Guide

Follow these steps to set up the Bankroll Laravel application:

```bash
cd src

# Install Dependencies

composer install
npm install
npm run build
```

## Configure Environment File


```bash
# Copy the example environment file and customize it.
cp .env.example .env

# Generate Application Key
php artisan key:generate

# Run Database Migrations
php artisan migrate

# Run Seeders (Optional)
php artisan db:seed

# Start Development Server

php artisan serve
```

Visit: `http://127.0.0.1:8000`

**Add/Remove user**

```bash
$ php artisan user:add mohamed thalib
User 'mohamed' created successfully with ID: 1
$ php artisan user:add thalib mohamed
User 'thalib' created successfully with ID: 2
$ php artisan user:list
+----+---------+------------------------+---------------------+
| ID | Name    | Email                  | Created At          |
+----+---------+------------------------+---------------------+
| 1  | mohamed | mohamed@bankroll.local | 2025-11-17 14:01:23 |
| 2  | thalib  | thalib@bankroll.local  | 2025-11-17 14:02:03 |
+----+---------+------------------------+---------------------+
$ php artisan user:remove thalib
User 'thalib' (thalib@bankroll.local) deleted successfully.
$ php artisan user:list
+----+---------+------------------------+---------------------+
| ID | Name    | Email                  | Created At          |
+----+---------+------------------------+---------------------+
| 1  | mohamed | mohamed@bankroll.local | 2025-11-17 14:01:23 |
+----+---------+------------------------+---------------------+
```
