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



```bash
# Add user
$ php artisan user:add username email@domain.com passwrod
User 'username' created successfully with ID: 1

# List User
$ php artisan user:list                                    
+----+---------+---------------------+---------------------+
| ID | Name    | Email               | Created At          |
+----+---------+---------------------+---------------------+      
| 1  | usernae | email@domain.com | 2025-11-17 12:36:11 |      
+----+---------+---------------------+---------------------+ 

# Remove user
php artisan user:remove email@domain.com             
User 'usernae' (email@domain.com) deleted successfully.
```
