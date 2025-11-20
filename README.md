
<h1 align="center">Bankroll</h1>

<p align="center">
  <img src="https://img.shields.io/github/languages/top/devnodesin/bankroll?logo=php&logoColor=white" alt="Top language"/>
  <img src="https://img.shields.io/github/license/devnodesin/bankroll?logo=github&logoColor=white" alt="License"/>
  <img src="https://img.shields.io/github/issues/devnodesin/bankroll?logo=github&logoColor=white" alt="Issues"/>
  <img src="https://img.shields.io/github/stars/devnodesin/bankroll?logo=github&logoColor=white" alt="Stars"/>
  <img src="https://img.shields.io/github/forks/devnodesin/bankroll?logo=github&logoColor=white" alt="Forks"/>
  <a href="https://github.com/pre-commit/pre-commit"><img src="https://img.shields.io/badge/pre--commit-enabled-brightgreen?logo=pre-commit&logoColor=white" alt="pre-commit"/></a>
</p>

<p align="center">
Built with ❤️ by **[Devnodes.in](https://devnodes.in)**
</p>


<div align="center">

**Bankroll** - Bank Transaction Management Software

A clean, minimal Laravel 12.x web application for importing, classifying, and exporting bank transactions with strict data integrity principles.

</div>



## ✨ Key Features

- **Secure Authentication** - Multi-user login with protected routes
- **Smart Import** - Upload bank statements (XLS, XLSX, CSV up to 5MB) with automatic column detection
- **Data Integrity** - Original transaction data is read-only and never modified
- **Easy Classification** - Assign categories and notes to transactions
- **Flexible Filtering** - View transactions by bank, year, and month
- **Multiple Export Formats** - Download as Excel, CSV, or PDF
- **Modern UI** - Responsive design with 4 theme modes (Sepia, Light, Dark, Auto)
- **Built for Performance** - Laravel 12.x, PHP 8.2+, and SQLite
- 📁 Importing Transactions: Excel (.xlsx, .xls), CSV (.csv), (Max: 5MB)
- 📁 Required Columns: `Date, Description, Withdraw, Deposit, Balance`
- Smart Column Mapping: The application automatically detects common column name variations
- User can "Preview & Map Columns" feature to manually map them.
- 📅 Supported Date Formats: `YYYY-MM-DD` (2024-03-15), `DD/MM/YYYY` (15/03/2024), `MM/DD/YYYY` (03/15/2024), Excel date numbers
- 🎨 Theme System: Four built-in themes—Sepia (default), Light, Dark, and Auto (system)—for comfortable viewing and reduced eye strain.

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
