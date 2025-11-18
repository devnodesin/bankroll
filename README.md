# Bankroll - Bank Transaction Mapping Sowtware

Built with ❤️ by **[Devnodes.in](https://devnodes.in)**

[MIT License](LICENSE.md)

A clean, minimal Laravel 12.x web application for importing, classifying, and exporting bank transactions with strict data integrity principles.

- **Repository:** https://github.com/devnodesin/bankroll
- **Issues:** https://github.com/devnodesin/bankroll/issues

## ✨ Features

- Built on Laravel 12.x, PHP 8.2+, and SQLite for speed and reliability.
- Secure multi-user login
- Fast import of bank statements (**XLS, XLSX, CSV**, up to 5MB)
- Automatic column and date validation
- Instant error feedback for every row
- Powerful transaction filtering (bank, year, month)
- 100% data integrity: original data is always protected
- Effortless classification: assign categories and notes
- Flexible category management (system & custom)
- One-click export (**Excel, CSV, PDF**)
- Responsive, modern UI with theme modes (Light, Dark, Auto)

## Installation

1. **Clone the repository**

```bash
git clone https://github.com/devnodesin/bankroll.git
cd bankroll/src
```

2. **Install dependencies**

```bash
# Install dependencies
composer install

# Configure environment
cp .env.example .env
php artisan key:generate

# Setup database
touch database/database.sqlite
php artisan migrate

# Load sample data (Optional)
php artisan db:seed

## Add user
php artisan user:add admin password123
```

6. **Start development server**

```bash
php artisan serve
```

7. **Access the application**

```
http://localhost:8000
Login: admin / password123
```

## 🔧 Console Commands

### User Management

```bash
# Add user (email optional)
php artisan user:add username password [--email=email@example.com]

# Remove user by username
php artisan user:remove username

# List all users
php artisan user:list
```

## 📁 File Import Format

Your import files must have these exact columns:

| Date       | Description      | Withdraw | Deposit | Balance |
| ---------- | ---------------- | -------- | ------- | ------- |
| 2024-01-01 | Opening Balance  | -        | 5000.00 | 5000.00 |
| 2024-01-05 | Salary Deposit   | -        | 3000.00 | 8000.00 |
| 2024-01-10 | Grocery Shopping | 150.50   | -       | 7849.50 |

**Supported date formats:** YYYY-MM-DD, DD/MM/YYYY, MM/DD/YYYY, Excel date numbers
