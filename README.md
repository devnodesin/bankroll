# Bankroll - Bank Transaction Management Software

A clean, minimal Laravel 12.x web application for importing, classifying, and exporting bank transactions with strict data integrity principles.

Built with ❤️ by **[Devnodes.in](https://devnodes.in)** | [MIT License](LICENSE.md)

**Repository:** https://github.com/devnodesin/bankroll  
**Issues:** https://github.com/devnodesin/bankroll/issues

## ✨ Key Features

- **Secure Authentication** - Multi-user login with protected routes
- **Smart Import** - Upload bank statements (XLS, XLSX, CSV up to 5MB) with automatic column detection
- **Data Integrity** - Original transaction data is read-only and never modified
- **Easy Classification** - Assign categories and notes to transactions
- **Flexible Filtering** - View transactions by bank, year, and month
- **Multiple Export Formats** - Download as Excel, CSV, or PDF
- **Modern UI** - Responsive design with 4 theme modes (Sepia, Light, Dark, Auto)
- **Built for Performance** - Laravel 12.x, PHP 8.2+, and SQLite

## Installation

### Option 1: Docker Deployment (Recommended) 🐳

```bash
git clone https://github.com/devnodesin/bankroll.git
cd bankroll
./deploy.sh
```

Access at `http://localhost:8000` | See [DOCKER-QUICKSTART.md](DOCKER-QUICKSTART.md) for details

### Option 2: Local Development

```bash
git clone https://github.com/devnodesin/bankroll.git
cd bankroll/src

# Install dependencies
composer install
cp .env.example .env
php artisan key:generate

# Setup database
touch database/database.sqlite
php artisan migrate
php artisan db:seed

# Create user
php artisan user:add admin password123

# Start server
php artisan serve
```

Access at `http://localhost:8000` with credentials: `admin` / `password123`

## 🔧 User Management

```bash
php artisan user:add username password [--email=user@example.com]
php artisan user:remove username
php artisan user:list
```

## 📁 Importing Transactions

### Supported File Formats
- Excel (.xlsx, .xls)
- CSV (.csv)
- Maximum file size: 5MB

### Required Columns
Your file must contain these data points:
- **Date** (Required) - Transaction date
- **Description** (Required) - Transaction details  
- **Withdraw** or **Deposit** (At least one required) - Transaction amounts
- **Balance** (Required) - Account balance after transaction

### Smart Column Mapping
The application automatically detects common column name variations:
- Date: "Transaction Date", "Txn Date", "Posting Date"
- Description: "Particulars", "Details", "Narration"
- Withdraw: "Debit", "Dr", "Amount Debited"
- Deposit: "Credit", "Cr", "Amount Credited"
- Balance: "Closing Balance", "Available Balance"

If your columns don't match, use the "Preview & Map Columns" feature to manually map them.

### Supported Date Formats
- YYYY-MM-DD (2024-03-15)
- DD/MM/YYYY (15/03/2024)
- MM/DD/YYYY (03/15/2024)
- Excel date numbers

## 🎨 Theme System

Four built-in themes for comfortable viewing:
- **Sepia (Default)** - Warm, low-glare theme that reduces eye strain
- **Light** - Classic bright theme with high contrast
- **Dark** - Modern dark theme for low-light environments
- **Auto** - Follows your system's light/dark preference

Click the theme toggle button in the navbar to switch themes. Your preference is saved automatically.

All themes meet WCAG AA accessibility standards.

