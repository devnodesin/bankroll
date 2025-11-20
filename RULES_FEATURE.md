# Rules Feature Documentation

## Overview

The Rules feature allows users to create automated classification rules for bank transactions. Rules match transactions based on description patterns and automatically assign categories, making transaction management more efficient.

## Features

### 1. Rule Management

#### Creating Rules
- Navigate to the Rules page using the "Rules" button in the navigation bar
- Fill in the rule creation form:
  - **Description Match**: Text to search for in transaction descriptions (case-insensitive, substring match)
  - **Category**: The category to assign to matching transactions
  - **Transaction Type**: Filter by Withdraw, Deposit, or Both

#### Editing Rules
- Click the edit (pencil) icon on any rule
- Modify the fields as needed
- Click the check icon to save or X to cancel

#### Deleting Rules
- Click the delete (trash) icon on any rule
- Confirm the deletion in the popup dialog

### 2. Applying Rules

#### From Transaction View
1. Load transactions by selecting Bank, Year, and Month
2. Click the "Apply Rules" button (appears when transactions are loaded)
3. Choose application mode:
   - **Fill blank only**: Only categorize transactions without an existing category
   - **Overwrite all**: Replace all categories with rule matches (use with caution)
4. Click "Apply Rules" to process

#### Rule Matching Logic
- Description matching is case-insensitive and searches for the text anywhere in the transaction description
- Transaction type filtering:
  - **Withdraw**: Only matches transactions with a withdrawal amount
  - **Deposit**: Only matches transactions with a deposit amount
  - **Both**: Matches all transactions (regardless of type)
- Rules are applied in the order they exist in the database
- Later rules can override earlier rules if "Overwrite all" is selected

## Data Integrity

The Rules feature maintains the integrity of your original bank transaction data:
- **Protected Fields**: Date, Description, Withdraw, Deposit, Balance, Reference Number, Year, Month
- **Modifiable Fields**: Only Category and Notes can be updated by rules
- Original bank data is never modified by the Rules feature

## Example Use Cases

### Example 1: Categorizing Online Purchases
```
Description Match: "AMAZON"
Category: Shopping
Transaction Type: Withdraw
```
This rule will categorize all withdrawals containing "AMAZON" as Shopping.

### Example 2: Salary Deposits
```
Description Match: "SALARY"
Category: Income
Transaction Type: Deposit
```
This rule will categorize all deposits containing "SALARY" as Income.

### Example 3: ATM Withdrawals
```
Description Match: "ATM"
Category: Cash Withdrawal
Transaction Type: Withdraw
```
This rule will categorize all ATM withdrawals.

### Example 4: Utility Bills
```
Description Match: "ELECTRIC"
Category: Utilities
Transaction Type: Both
```
This rule will categorize both deposits and withdrawals containing "ELECTRIC" as Utilities.

## Best Practices

1. **Be Specific**: Use distinctive text patterns that uniquely identify transaction types
2. **Start Simple**: Begin with a few obvious rules and expand as needed
3. **Use Fill Blank Only**: For the first application, use "Fill blank only" to avoid accidentally overwriting manual categorizations
4. **Test Incrementally**: Apply rules to one month at a time initially to verify they work as expected
5. **Review Results**: After applying rules, review a sample of categorized transactions to ensure accuracy
6. **Regular Maintenance**: Update rules as your transaction patterns change

## Technical Details

### Database Schema
```sql
CREATE TABLE rules (
    id BIGINT PRIMARY KEY,
    description_match VARCHAR(255) NOT NULL,
    category_id BIGINT NOT NULL,
    transaction_type ENUM('withdraw', 'deposit', 'both') DEFAULT 'both',
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);
```

### API Endpoints
- `GET /rules` - Display rules management page
- `POST /rules` - Create a new rule
- `PUT /rules/{rule}` - Update an existing rule
- `DELETE /rules/{rule}` - Delete a rule
- `POST /rules/apply` - Apply rules to transactions

### Validation Rules
#### Rule Creation/Update:
- `description_match`: Required, string, max 255 characters
- `category_id`: Required, must exist in categories table
- `transaction_type`: Required, must be one of: withdraw, deposit, both

#### Rule Application:
- `bank`: Required, string
- `year`: Required, integer, between 1900 and 2100
- `month`: Required, integer, between 1 and 12
- `overwrite`: Required, boolean

## Security Considerations

1. **Authentication Required**: All rule operations require user authentication
2. **CSRF Protection**: All forms include CSRF tokens
3. **Input Validation**: All inputs are validated on the server side
4. **SQL Injection Prevention**: Uses Laravel Eloquent ORM with parameter binding
5. **Data Integrity**: Original transaction data is protected by Laravel's fillable/guarded properties
6. **Transaction Atomicity**: Rule application uses database transactions to ensure consistency

## Troubleshooting

### Rules Not Matching Expected Transactions
- Verify the description match text appears in the transaction description
- Check the transaction type filter (withdraw/deposit/both)
- Ensure the category exists and hasn't been deleted
- Description matching is case-insensitive

### Rules Overwriting Manual Categories
- Use "Fill blank only" mode instead of "Overwrite all"
- Review your rules for overly broad description matches

### No Transactions Updated
- Verify you have created rules
- Check that transactions exist for the selected bank, year, and month
- Ensure transaction descriptions match at least one rule
- Verify the transaction type filter in your rules

## Future Enhancements

Potential improvements for the Rules feature:
- Rule priority/ordering system
- More advanced matching patterns (regex support)
- Rule testing/preview before applying
- Bulk rule import/export
- Rule application statistics and history
- Scheduled automatic rule application
- Multiple description patterns per rule
- Amount range filtering
- Date range filtering

## Support

For issues or questions:
1. Check this documentation
2. Review the application logs for error messages
3. Verify your data meets the validation requirements
4. Contact your system administrator
