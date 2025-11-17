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

### Added - GH-003: Authentication System

- Created LoginController with login/logout functionality
- Implemented simple username/password login page with Bootstrap 5.3 styling
- Added authentication routes (login GET/POST, logout POST)
- Protected all routes except login with auth middleware
- Redirect authenticated users away from login page
- Created user management Artisan commands:
  - `user:add {username} {email} {password}` - Add new user
  - `user:remove {email}` - Remove user by email
  - `user:list` - List all users
- Created default admin user (admin@bankroll.local / password123)
- Session regeneration on login for security
- CSRF protection on all forms

### Added - GH-004: Base Layout with Bootstrap 5.3 and Theme Switcher

- Created base Blade layout (`layouts/app.blade.php`) with Bootstrap 5.3
- Implemented header component with:
  - Application name on the left
  - Theme toggle button with icon
  - Logout button for authenticated users
- Implemented footer component with app name, version, and credit
- Added theme switcher functionality with three modes:
  - Light mode (sun icon)
  - Dark mode (moon icon)
  - Auto mode (circle-half icon) - follows system preference
- Theme preference persists in localStorage
- Responsive design using Bootstrap grid system
- All Bootstrap 5.3 components styled correctly
- Theme automatically applies on page load
- System theme change detection when in auto mode
