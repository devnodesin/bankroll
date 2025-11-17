# Changelog

All notable changes to the Bankroll project will be documented in this file.

## [1.0.0] - 2025-11-17

### Added - GH-001: Laravel Project Setup and Configuration

- Created new Laravel 12.38.1 project in `src/` directory
- Configured application name to "Bankroll"
- Set application version to "1.0.0" in `config/app.php`
- Configured SQLite database connection
- Verified all default Laravel features are working
- Database migrations initialized successfully

### Added - GH-002: Database Schema and Models

- Created `categories` table migration with fields: id, name, is_custom, timestamps
- Created `transactions` table migration with fields: id, bank_name, date, description, withdraw, deposit, balance, reference_number, category_id, notes, year, month, timestamps
- Added proper indexes for performance (bank_name, date, year, month)
- Created Category model with relationship to transactions
- Created Transaction model with protected original bank data fields
- Only category_id and notes fields are fillable (editable)
- All original bank data fields are guarded (read-only)
- Created CategorySeeder with predefined categories: INCOME:SALES, EXPENSE:FUEL, EXPENSE:ELECTRIC BILL, EXPENSE:TRAVEL, EXPENSE:HEALTHCARE, EXPENSE:ENTERTAINMENT
- Successfully migrated database and seeded categories
