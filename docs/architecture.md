# Bankroll Architecture Documentation

## System Overview

Bankroll is a Laravel 12.x web application designed for importing, classifying, and exporting bank transactions. The application follows Laravel's MVC architecture with a focus on data integrity and user experience.

## Technology Stack

### Backend
- **Framework**: Laravel 12.x
- **Language**: PHP 8.2+
- **Database**: SQLite
- **Packages**:
  - `maatwebsite/excel`: Excel/CSV import and export
  - `barryvdh/laravel-dompdf`: PDF generation

### Frontend
- **Template Engine**: Blade (Laravel's native)
- **CSS Framework**: Bootstrap 5.3
- **Icons**: Bootstrap Icons
- **JavaScript**: Vanilla JS (no frameworks)
- **AJAX**: Fetch API

### Development Tools
- **Package Manager**: Composer (PHP)
- **Testing**: PHPUnit
- **Code Style**: Laravel Pint

## Application Architecture

### Directory Structure

```
src/
├── app/
│   ├── Console/
│   │   └── Commands/
│   │       ├── UserAddCommand.php
│   │       ├── UserListCommand.php
│   │       └── UserRemoveCommand.php
│   ├── Exports/
│   │   └── TransactionsExport.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/
│   │   │   │   └── LoginController.php
│   │   │   ├── CategoryController.php
│   │   │   ├── ExportController.php
│   │   │   ├── HomeController.php
│   │   │   └── ImportController.php
│   │   └── Middleware/
│   └── Models/
│       ├── Category.php
│       ├── Transaction.php
│       └── User.php
├── database/
│   ├── migrations/
│   └── seeders/
├── resources/
│   └── views/
│       ├── auth/
│       ├── exports/
│       └── layouts/
└── routes/
    └── web.php
```

## Core Components

### 1. Authentication System

**Controller**: `App\Http\Controllers\Auth\LoginController`

**Features**:
- Dual authentication (username or email)
- Session-based authentication
- Remember me functionality
- CSRF protection

**Flow**:
1. User submits credentials
2. System attempts authentication with username (name field)
3. If failed, attempts with email field
4. On success, regenerates session and redirects to home

### 2. Transaction Management

**Model**: `App\Models\Transaction`

**Protected Fields** (Read-only after import):
- `date`
- `description`
- `withdraw`
- `deposit`
- `balance`

**Editable Fields**:
- `category_id`
- `notes`

**Key Principle**: Original bank transaction data is immutable. Only classification fields can be modified.

### 3. Import System

**Controller**: `App\Http\Controllers\ImportController`

**Process Flow**:
```
File Upload → Validation → Processing → Database Transaction → Response
```

**Validation Steps**:
1. File format check (XLS, XLSX, CSV)
2. File size check (max 5MB)
3. Column header validation (exact match)
4. Row-level data validation:
   - Date format parsing
   - Numeric validation for amounts
   - Required field checks

**Features**:
- Support for multiple date formats
- Excel date number conversion
- Currency symbol removal
- Database transaction wrapping (atomic operations)
- Detailed error reporting

### 4. Export System

**Controller**: `App\Http\Controllers\ExportController`

**Export Class**: `App\Exports\TransactionsExport`

**Supported Formats**:
- **Excel**: Styled with borders, bold headers, auto-width
- **CSV**: UTF-8 encoded, comma-delimited
- **PDF**: Landscape orientation with page numbers

**Process**:
1. Receive filter parameters (bank, year, month)
2. Query filtered transactions with category relationship
3. Format data according to export type
4. Generate file and trigger download
5. Set appropriate content-type headers

### 5. Category Management

**Controller**: `App\Http\Controllers\CategoryController`

**Model**: `App\Models\Category`

**Category Types**:
- **System Categories**: `is_custom = false` (read-only)
- **Custom Categories**: `is_custom = true` (user-created)

**Business Rules**:
1. System categories cannot be deleted
2. Custom categories can be added by users
3. Category names must be unique (case-insensitive)
4. Categories in use cannot be deleted
5. Maximum category name length: 50 characters

## Database Schema

### Users Table
```sql
- id: bigint (primary key)
- name: string (username)
- email: string (unique)
- password: string (hashed)
- remember_token: string
- timestamps
```

### Categories Table
```sql
- id: bigint (primary key)
- name: string (unique)
- is_custom: boolean (system vs custom)
- timestamps
- soft deletes
```

### Transactions Table
```sql
- id: bigint (primary key)
- bank_name: string (indexed)
- date: date (indexed)
- description: text
- withdraw: decimal(10,2)
- deposit: decimal(10,2)
- balance: decimal(10,2)
- category_id: foreign key (nullable)
- notes: text (nullable)
- year: integer (indexed)
- month: integer (indexed)
- timestamps

Indexes:
- (bank_name, year, month) composite index
```

## Security Measures

### Data Integrity
1. **Fillable Attributes**: Only classification fields are mass-assignable
2. **Guarded Attributes**: Bank data fields are protected from mass assignment
3. **Database Transactions**: Import operations use atomic transactions
4. **CSRF Protection**: All forms include CSRF tokens

### Authentication
1. **Password Hashing**: Bcrypt hashing for all passwords
2. **Session Management**: Proper session regeneration on login
3. **Route Protection**: All routes except login require authentication
4. **Remember Token**: Secure implementation of "remember me"

### File Uploads
1. **MIME Type Validation**: Server-side file type checking
2. **Size Limits**: 5MB maximum file size
3. **Path Traversal Prevention**: Temporary file handling
4. **Validation**: Strict column and data validation

## User Interface Design

### Layout Structure
```
┌─────────────────────────────────┐
│ Header (Nav + Theme + Logout)   │
├─────────────────────────────────┤
│                                 │
│         Main Content            │
│   (Filters + Transactions)      │
│                                 │
├─────────────────────────────────┤
│ Footer (App Info)               │
└─────────────────────────────────┘
```

### Theme System
**Storage**: localStorage
**Options**: light, dark, auto
**Persistence**: Across sessions

**Implementation**:
1. Check localStorage for saved theme
2. Default to dark mode if none saved
3. Apply theme to `data-bs-theme` attribute
4. Listen for system theme changes in auto mode

### Modal Dialogs
1. **Import Modal**: File upload and bank selection
2. **Categories Modal**: Category management interface

Both use Bootstrap 5.3 modal component with AJAX operations.

## API Endpoints

### Authentication
- `GET /login` - Show login form
- `POST /login` - Process login
- `POST /logout` - Logout user

### Transactions
- `GET /` - Home page (transaction viewer)
- `POST /transactions/get` - Load filtered transactions
- `PATCH /transactions/{id}` - Update classification

### Import
- `POST /transactions/import` - Import transactions from file

### Export
- `GET /transactions/export/excel` - Export as Excel
- `GET /transactions/export/csv` - Export as CSV
- `GET /transactions/export/pdf` - Export as PDF

### Categories
- `GET /categories` - List all categories
- `POST /categories` - Create custom category
- `DELETE /categories/{id}` - Delete custom category
- `GET /categories/all` - Get all for dropdown

## Data Flow

### Transaction Classification Flow
```
1. User loads transactions (bank + year + month)
2. Frontend fetches transactions via AJAX
3. Transactions displayed with editable fields
4. User modifies category/notes
5. Changes tracked in pendingChanges object
6. Save button appears
7. User clicks save
8. All changes sent to backend in batch
9. Backend validates and updates
10. Success message displayed
11. Save button hidden
```

### Import Flow
```
1. User selects file and enters bank name
2. File uploaded via FormData
3. Backend reads file with PhpSpreadsheet
4. Column headers validated
5. Each row validated
6. Database transaction started
7. Valid rows inserted
8. Transaction committed
9. Success/error response sent
10. Frontend shows result
11. Optional: Refresh transaction list
```

## Performance Considerations

### Database Optimization
1. **Indexes**: Composite index on (bank_name, year, month)
2. **Eager Loading**: Categories loaded with transactions
3. **Pagination**: Not currently implemented (consider for large datasets)

### File Processing
1. **Memory Management**: Streaming for large files
2. **Batch Operations**: Bulk inserts for import
3. **Validation**: Early exit on errors

### Frontend
1. **Minimal Dependencies**: No heavy JavaScript frameworks
2. **Lazy Loading**: Categories loaded only when modal opens
3. **Batch Updates**: Multiple transaction updates in single request

## Error Handling

### Import Errors
- **File Errors**: MIME type, size, format
- **Column Errors**: Missing or incorrect headers
- **Data Errors**: Row-level validation failures
- **Database Errors**: Transaction rollback on failure

### Export Errors
- **Parameter Validation**: Missing or invalid filters
- **Data Errors**: No transactions found
- **Generation Errors**: PDF/Excel creation failures

### Category Errors
- **Uniqueness**: Duplicate category names
- **Usage**: Deletion of categories in use
- **Permission**: System category modification attempts

## Extensibility Points

### Adding New Export Formats
1. Create export method in ExportController
2. Add route in web.php
3. Add dropdown option in home.blade.php
4. Implement export logic

### Adding New Transaction Fields
1. Add migration for new column
2. Update Transaction model fillable/guarded
3. Modify import validation
4. Update export formatting
5. Adjust UI to display field

### Custom Categories Features
- Add color coding
- Add icons
- Add subcategories
- Add category groups

## Deployment Considerations

### Environment Variables
```env
APP_NAME=Bankroll
APP_ENV=production
APP_DEBUG=false
DB_CONNECTION=sqlite
DB_DATABASE=/path/to/database.sqlite
```

### Database Setup
```bash
php artisan migrate --seed
```

### Default Categories
Seeded via CategorySeeder:
- EXPENSE:ELECTRIC BILL
- EXPENSE:ENTERTAINMENT
- EXPENSE:FUEL
- EXPENSE:HEALTHCARE
- EXPENSE:TRAVEL
- INCOME:SALES

### User Management
Console commands:
```bash
php artisan user:add username password [--email=email]
php artisan user:remove username
php artisan user:list
```

## Testing Strategy

### Unit Tests
- Model validation rules
- Import validation logic
- Export formatting
- Category uniqueness

### Feature Tests
- Authentication flow
- Import process
- Export generation
- Category CRUD operations

### Browser Tests
- UI interactions
- Modal dialogs
- Theme switching
- Form submissions

## Monitoring and Logging

### Laravel Log Channels
- Import errors logged with context
- Authentication failures logged
- Database errors captured

### Error Reporting
- User-friendly error messages
- Detailed error logs for debugging
- Row-level error tracking for imports

## Future Enhancements

### Potential Features
1. **Dashboard**: Summary statistics and charts
2. **Search**: Full-text search on descriptions
3. **Filters**: Advanced filtering options
4. **Bulk Operations**: Bulk category assignment
5. **Reports**: Custom report generation
6. **API**: RESTful API for integrations
7. **Multi-user**: Role-based access control
8. **Attachments**: Receipt/document uploads

### Performance Improvements
1. **Pagination**: For large transaction lists
2. **Caching**: Redis for frequently accessed data
3. **Queue Jobs**: Background processing for large imports
4. **Database**: Consider PostgreSQL for larger deployments

---

**Document Version:** 1.0.0  
**Last Updated:** 2025-11-17  
**Maintained By:** Development Team
