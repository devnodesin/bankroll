# Bank Transaction Classification App

Bankroll - A Laravel web application to import, classify, and export bank transactions while preserving original transaction data integrity.

## Features

**Import & Export**
- Import monthly transactions in XLS/CSV file formats
- Export monthly transactions in XLS, CSV, PDF
- Imported files must have the following columns: Date, Description, Withdraw, Deposit, Balance
- If columns do not match exactly, abort the import and show an error

**Data Model & Integrity**
- Fields: Date, Description, Amount, Reference Number
- Columns: Date, Description, Notes (classified field, selectable from drop-down), Withdraw, Deposit, Balance
- **No modification** of original bank data (amounts, dates, descriptions)
- All bank transaction fields remain read-only

**Classification & Categories**
- Predefined categories for small businesses: FUEL, ELECTRIC BILL, GROCERIES, DINING, TRAVEL, HEALTHCARE, ENTERTAINMENT
- Manual category assignment
- Add custom categories
- Update categories at any time

**User Interface & Experience**
- Uses Bootstrap 5.3 styles and components only for styling
- Supports Dark, Light, and System modes. The last selected mode is saved in local storage and automatically loaded on future visits if available.
- Clean, intuitive user interface
- Clear error messages for failed imports
- Uses Blade template engine for frontend

## Pages

- **Login Page:** One username and password, Login button
- **Home Page:** Main app page (single page)
    - Header with navigation; app name on the left, dark mode toggle button on the right
    - Footer: "{appname} {version} built with love by Devnodes.in" (get app name and version from `config/app.php`)
    - Main section row 1: Dropdowns for bank, year, month; loads table in row 2 based on selection
    - Row 2: If no data is available for the selected month, show "No Data" page; else, show table with data

