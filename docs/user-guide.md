# Bankroll User Guide

## Table of Contents
- [Getting Started](#getting-started)
- [Logging In](#logging-in)
- [Viewing Transactions](#viewing-transactions)
- [Importing Transactions](#importing-transactions)
- [Classifying Transactions](#classifying-transactions)
- [Managing Categories](#managing-categories)
- [Exporting Data](#exporting-data)
- [Theme Switching](#theme-switching)
- [Troubleshooting](#troubleshooting)

## Getting Started

Bankroll is a simple and efficient tool for managing your bank transactions. With Bankroll, you can:
- Import transactions from XLS, XLSX, or CSV files
- Classify transactions with categories
- Add custom categories for your needs
- Export your data in Excel, CSV, or PDF format
- Switch between light and dark themes

## Logging In

1. Navigate to the Bankroll login page
2. Enter your **username or email**
3. Enter your **password**
4. Optionally, check "Remember me" to stay logged in
5. Click the **Login** button

**Note:** You can log in using either your username or email address.

## Viewing Transactions

### Loading Transactions

1. After logging in, you'll see the transaction filters at the top
2. Select a **Bank** from the dropdown
3. Select a **Year** from the dropdown
4. Select a **Month** from the dropdown
5. Click the **Load Transactions** button

The transactions will be displayed in a table with the following columns:
- **Date**: Transaction date
- **Description**: Transaction description
- **Category**: Assigned category (editable)
- **Notes**: Your notes (editable)
- **Withdraw**: Money withdrawn
- **Deposit**: Money deposited
- **Balance**: Account balance after transaction

## Importing Transactions

### Preparing Your File

Your import file must have these exact column headers (case-insensitive):
- **Date**
- **Description**
- **Withdraw**
- **Deposit**
- **Balance**

**Supported formats:**
- Microsoft Excel (.xlsx, .xls)
- CSV (.csv)
- Maximum file size: 5MB

### Import Steps

1. Click the **Import** button next to the Load button
2. Enter the **Bank Name** in the modal
3. Click **Select File** and choose your file
4. Click the **Import** button

**What happens during import:**
- The file is validated for correct column headers
- Each row is checked for valid data
- Transactions are added to the database
- If any errors occur, you'll see detailed error messages

**Success:** You'll see a message showing how many transactions were imported.

## Classifying Transactions

### Manual Classification

1. Load transactions for a specific bank, year, and month
2. For each transaction, select a category from the **Category** dropdown
3. Optionally, add notes in the **Notes** field
4. The **Save Changes** button will appear when you make changes
5. Click **Save Changes** to save all your updates at once

**Note:** Changes are not saved automatically. You must click the Save Changes button.

### Categories

Transactions can be classified using:
- **System Categories**: Pre-defined categories (cannot be deleted)
  - EXPENSE:ELECTRIC BILL
  - EXPENSE:ENTERTAINMENT
  - EXPENSE:FUEL
  - EXPENSE:HEALTHCARE
  - EXPENSE:TRAVEL
  - INCOME:SALES

- **Custom Categories**: Your own categories (can be added/deleted)

## Managing Categories

### Opening Category Management

1. Click the **Categories** button in the top navigation bar
2. The Category Management modal will open

### Adding a Custom Category

1. Enter a category name in the input field (max 50 characters)
2. Click **Add Category**
3. The category will appear in the Custom Categories section
4. It will also be available in the transaction category dropdowns

**Validation:**
- Category names must be unique (case-insensitive)
- Maximum length: 50 characters
- Cannot be empty

### Deleting a Custom Category

1. Find the category in the Custom Categories section
2. Click the **Delete** button next to the category
3. Confirm the deletion in the popup

**Note:** You cannot delete a category that is being used by transactions. You must first remove the category assignment from all transactions.

### System Categories

System categories are shown with a blue "System" badge and cannot be deleted. They are designed for common business expenses and income.

## Exporting Data

### Export Options

Once you've loaded transactions, an **Export** dropdown button will appear. You can export in three formats:
- **Excel (.xlsx)**: Formatted spreadsheet with styling
- **CSV (.csv)**: Plain text format, compatible with any spreadsheet software
- **PDF (.pdf)**: Professional document format for reports

### Export Steps

1. Load transactions using the filters
2. Click the **Export** dropdown button
3. Choose your desired format:
   - **Export as Excel**
   - **Export as CSV**
   - **Export as PDF**
4. The file will download automatically

**Export Details:**
- Only currently filtered transactions are exported
- Filename format: `transactions_{bank}_{year}_{month}.{ext}`
- Includes all columns: Date, Description, Category, Notes, Withdraw, Deposit, Balance
- Currency values are properly formatted

## Theme Switching

Bankroll supports three theme modes:

### Theme Options
- **Light Mode**: Bright background, dark text
- **Dark Mode**: Dark background, light text
- **Auto Mode**: Follows your system theme preference

### Switching Themes

1. Click the **theme toggle button** (sun/moon icon) in the top right
2. The theme cycles through: Light → Dark → Auto → Light
3. Your preference is saved automatically

The current theme is indicated by the icon:
- **Sun icon**: Light mode
- **Moon icon**: Dark mode
- **Half-circle icon**: Auto mode

**Note:** The theme setting persists across sessions.

## Troubleshooting

### Import Issues

**Problem:** "Missing required columns" error

**Solution:** 
- Check that your file has these exact column headers: Date, Description, Withdraw, Deposit, Balance
- Column names are case-insensitive but must match exactly
- Remove any extra columns or rename them

**Problem:** "Validation errors found" message

**Solution:**
- Check the date format in your file (common formats are supported)
- Ensure Withdraw, Deposit, and Balance columns contain valid numbers
- At least one of Withdraw or Deposit must have a value for each transaction

**Problem:** File upload fails

**Solution:**
- Check file size (maximum 5MB)
- Ensure file format is .xlsx, .xls, or .csv
- Try saving your Excel file in a different format

### Category Management

**Problem:** Can't delete a category

**Solution:**
- Check if the category is assigned to any transactions
- Go to transactions and change the category to something else
- Then try deleting again

**Problem:** "Category already exists" error

**Solution:**
- Category names must be unique (case-insensitive)
- Check if a similar category already exists
- Choose a different name

### Login Issues

**Problem:** Invalid credentials

**Solution:**
- Verify your username or email is correct
- Check your password (it's case-sensitive)
- Contact your administrator if you've forgotten your credentials

### Display Issues

**Problem:** UI elements look broken in dark mode

**Solution:**
- Clear your browser cache
- Refresh the page
- Ensure you're using a modern browser

**Problem:** Transactions not loading

**Solution:**
- Ensure all three filters (Bank, Year, Month) are selected
- Check your internet connection
- Refresh the page and try again

## Tips for Best Experience

1. **Regular Imports**: Import your bank statements regularly to keep data up-to-date
2. **Consistent Categories**: Use the same categories for similar transactions
3. **Add Notes**: Use the notes field to add context to important transactions
4. **Export Regularly**: Export your classified transactions for record-keeping
5. **Custom Categories**: Create custom categories that match your business needs
6. **Save Your Work**: Remember to click the Save Changes button after classifying transactions

## Keyboard Shortcuts

While not explicitly defined, you can use standard browser shortcuts:
- **Ctrl/Cmd + F**: Search on page
- **Tab**: Navigate between form fields
- **Enter**: Submit forms
- **Esc**: Close modals

## Getting Help

If you encounter issues not covered in this guide:
1. Check the error message displayed on screen
2. Ensure your browser is up to date
3. Contact your system administrator
4. Report issues through your organization's support channel

---

**Version:** 1.0.0  
**Built with ❤️ by Devnodes.in**
