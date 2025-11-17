# Bankroll - Bank Transaction Management

A clean, minimal Laravel 12.x web application for importing, classifying, and exporting bank transactions with strict data integrity principles.

## ✨ Features

### 🔐 Authentication
- Login with username or email
- Secure session-based authentication
- Remember me functionality
- Console commands for user management

### 📥 Import Transactions
- Support for **XLS, XLSX, CSV** formats (max 5MB)
- Strict column validation (Date, Description, Withdraw, Deposit, Balance)
- Multiple date format support
- Row-level error reporting
- Atomic database transactions

### 📊 Transaction Management
- View transactions by bank, year, and month
- **Read-only original data** - preserves data integrity
- Editable classification fields (Category, Notes)
- Manual save workflow with batch updates
- Real-time UI feedback

### 🏷️ Category Management
- **System Categories** (predefined, non-deletable)
  - EXPENSE:ELECTRIC BILL
  - EXPENSE:ENTERTAINMENT
  - EXPENSE:FUEL
  - EXPENSE:HEALTHCARE
  - EXPENSE:TRAVEL
  - INCOME:SALES
- **Custom Categories** (user-defined, deletable)
- Usage tracking prevents accidental deletion
- Case-insensitive uniqueness validation

### 📤 Export Transactions
- **Excel (.xlsx)** - Formatted with styling
- **CSV (.csv)** - Plain text format
- **PDF (.pdf)** - Professional reports
- Respects current filters
- Smart filename generation

### 🎨 User Interface
- **Bootstrap 5.3** - Clean, modern design
- **Theme Modes** - Light, Dark, Auto (system)
- **Responsive** - Works on all devices
- **Minimal** - No bloat, just essentials
- **High Performance** - Vanilla JavaScript, no heavy frameworks

## 🚀 Quick Start

### Requirements
- PHP 8.2+
- Composer
- SQLite (or other database)
- Laravel 12.x

### Installation

1. **Clone the repository**
```bash
git clone https://github.com/devnodesin/bankroll.git
cd bankroll/src
```

2. **Install dependencies**
```bash
composer install
```

3. **Configure environment**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Setup database**
```bash
touch database/database.sqlite
php artisan migrate --seed
```

5. **Create admin user**
```bash
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

## 📖 Documentation

- **[User Guide](docs/user-guide.md)** - End-user documentation
- **[Architecture](docs/architecture.md)** - Technical documentation
- **[Installation Guide](INSTALL.md)** - Detailed setup instructions

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

| Date       | Description              | Withdraw | Deposit  | Balance  |
|------------|--------------------------|----------|----------|----------|
| 2024-01-01 | Opening Balance          | -        | 5000.00  | 5000.00  |
| 2024-01-05 | Salary Deposit           | -        | 3000.00  | 8000.00  |
| 2024-01-10 | Grocery Shopping         | 150.50   | -        | 7849.50  |

**Supported date formats:** YYYY-MM-DD, DD/MM/YYYY, MM/DD/YYYY, Excel date numbers

## 🎯 Usage Flow

1. **Login** with username or email
2. **Import** transactions from your bank statement
3. **Load** transactions by selecting bank, year, month
4. **Classify** transactions by assigning categories and adding notes
5. **Save** your changes with the Save button
6. **Export** classified data in your preferred format

## 🛡️ Data Integrity

**Core Principle:** Original bank transaction data is immutable.

- ✅ **Editable:** Category assignments, Notes
- ❌ **Read-only:** Date, Description, Withdraw, Deposit, Balance

This ensures your imported bank data remains untouched and accurate.

## 🎨 Theme System

- **Light Mode** - Clean, bright interface
- **Dark Mode** - Easy on the eyes (default)
- **Auto Mode** - Follows system preference

Theme preference is saved in localStorage and persists across sessions.

## 📦 Technology Stack

- **Backend:** Laravel 12.x, PHP 8.2+
- **Database:** SQLite
- **Frontend:** Blade Templates, Bootstrap 5.3, Vanilla JS
- **Excel:** PhpSpreadsheet, Maatwebsite/Excel
- **PDF:** DomPDF

## 📜 License

This project is proprietary software developed by Devnodes.in.

## 👨‍💻 Development

Built with ❤️ by **Devnodes.in**

### Contributing
This is a private project. Contact the maintainers for contribution guidelines.

### Version
**Current Version:** 1.0.0

## 🔗 Links

- **Repository:** https://github.com/devnodesin/bankroll
- **Issues:** https://github.com/devnodesin/bankroll/issues
- **Changelog:** [CHANGELOG.md](src/CHANGELOG.md)

## 📸 Screenshots

### Login Page
Clean login interface with theme support

### Transaction Management
View and classify transactions with ease

### Category Management
Manage system and custom categories

### Export Options
Export in multiple formats (Excel, CSV, PDF)

---

**Bankroll** - Simple. Clean. Effective.

