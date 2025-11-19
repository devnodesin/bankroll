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

### Option 1: Docker Deployment (Recommended for Production) 🐳

The easiest way to deploy Bankroll is using Docker with FrankenPHP:

```bash
# Clone the repository
git clone https://github.com/devnodesin/bankroll.git
cd bankroll

# Run automated deployment
./deploy.sh
```

Access the application at `http://localhost:8000`

📖 **Documentation:**
- **Quick Start**: [DOCKER-QUICKSTART.md](DOCKER-QUICKSTART.md) - Get running in 5 minutes
- **Complete Guide**: [DEPLOYMENT.md](DEPLOYMENT.md) - Full deployment documentation

### Option 2: Local Development Setup

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

3. **Start development server**

```bash
php artisan serve
```

4. **Access the application**

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

### Quick Import (Exact Column Names)

For fastest import, use these exact column names:

| Date       | Description      | Withdraw | Deposit | Balance |
| ---------- | ---------------- | -------- | ------- | ------- |
| 2024-01-01 | Opening Balance  | -        | 5000.00 | 5000.00 |
| 2024-01-05 | Salary Deposit   | -        | 3000.00 | 8000.00 |
| 2024-01-10 | Grocery Shopping | 150.50   | -       | 7849.50 |

### Column Mapping (Flexible Import)

**New!** Import files with any column names using our column mapping feature:

1. Upload your file (CSV, XLS, or XLSX)
2. Click "Preview & Map Columns" to see your file structure
3. Map your columns to the required fields:
   - **Date** (Required) - Transaction date
   - **Description** (Required) - Transaction details
   - **Withdraw** (Optional) - Debit amount
   - **Deposit** (Optional) - Credit amount
   - **Balance** (Required) - Account balance after transaction
4. Review the preview and click "Import"

The system automatically detects common column variations like:
- Date: "Transaction Date", "Txn Date", "Posting Date"
- Description: "Particulars", "Details", "Narration"
- Withdraw: "Debit", "Dr", "Amount Debited"
- Deposit: "Credit", "Cr", "Amount Credited"
- Balance: "Closing Balance", "Available Balance"

**Supported date formats:** YYYY-MM-DD, DD/MM/YYYY, MM/DD/YYYY, Excel date numbers
