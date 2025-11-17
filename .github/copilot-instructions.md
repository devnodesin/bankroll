# Bankroll Project - GitHub Copilot Instructions

## Project Overview
Bankroll is a Laravel web application for importing, classifying, and exporting bank transactions while preserving original transaction data integrity.

## Technology Stack
- **Backend**: Laravel (latest stable version)
- **Frontend**: Blade templating engine
- **Styling**: Bootstrap 5.3 (only framework allowed)
- **Database**: SQLLite
- **Language**: PHP 8.1+

## Core Principles

### 1. Data Integrity
- **NEVER modify original bank transaction data** (dates, descriptions, amounts, balances)
- All bank fields are READ-ONLY after import
- Only classification fields (notes, category) are editable
- Original data must remain exactly as imported

### 2. Styling Guidelines
- **ONLY use Bootstrap 5.3** for all styling
- No custom CSS frameworks or UI libraries
- Use Bootstrap's built-in components and utilities
- Leverage Bootstrap 5.3's color mode system for theming
- Maintain responsive design using Bootstrap grid system

### 3. Code Quality
- Follow Laravel best practices and conventions
- Use Eloquent ORM for database operations
- Implement proper validation on all inputs
- Use CSRF protection on all forms
- Handle errors gracefully with clear messages
- Write clean, readable, and maintainable code

## Project Structure
```
bankroll/
├── src/                    # Laravel application code
├── prd/                    # Product requirement documents (tasks)
├── docs/                   # Documentation
├── .github/
│   ├── copilot-instructions.md          # This file
│   └── instructions/
│       ├── laravel.instructions.md      # Laravel-specific guidelines
│       ├── markdown.instructions.md     # Documentation guidelines
│       └── blade.instructions.md        # Blade template guidelines
```

## Key Features to Remember

### Import System
- Strict column validation: Date, Description, Withdraw, Deposit, Balance
- Abort import if columns don't match EXACTLY
- Support XLS, XLSX, and CSV formats
- Show clear error messages for validation failures

### Classification System
- Predefined categories for small businesses
- Support for custom user categories
- Only classification fields are editable
- Changes saved via AJAX

### Export System
- Three formats: Excel, CSV, PDF
- Export only filtered data (by bank/year/month)
- Maintain formatting and readability

### Theme System
- Three modes: Light, Dark, System (auto)
- Persist selection in localStorage
- Use Bootstrap 5.3's native color mode system

## Development Guidelines

### When Writing Code
1. Prioritize data integrity in transactions
2. Use Bootstrap 5.3 components exclusively
3. Implement proper validation (frontend and backend)
4. Follow RESTful conventions for routes
5. Use meaningful variable and function names
6. Add comments for complex logic only

### When Creating Views
1. Extend base layout (`layouts.app`)
2. Use Blade components for reusability
3. Apply Bootstrap 5.3 classes for styling
4. Ensure responsive design
5. Support theme switching
6. Follow accessibility best practices

### When Working with Database
1. Use migrations for schema changes
2. Protect original transaction fields from mass assignment
3. Use proper indexes for performance
4. Implement database transactions for data integrity
5. Use Eloquent relationships

### Security Considerations
1. Always validate user inputs
2. Use Laravel's authentication system
3. Implement CSRF protection
4. Sanitize file uploads
5. Use prepared statements (Eloquent handles this)
6. Hash passwords with bcrypt

## Common Patterns

### AJAX Operations
- Use for inline edits (classification changes)
- Return JSON responses
- Handle loading and error states
- Provide user feedback

### File Operations
- Use Laravel Excel for XLS/CSV
- Use DomPDF for PDF generation
- Validate file types and sizes
- Handle large files efficiently

### Validation
- Use Laravel's validation rules
- Return clear error messages
- Validate on both client and server side

## Reference Files
- Check `prd/` directory for detailed task requirements
- Refer to specific instruction files in `.github/instructions/`
- Follow Laravel documentation for framework features

## Contact
Built with ❤️ by Devnodes.in
