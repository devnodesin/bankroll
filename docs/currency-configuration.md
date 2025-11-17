# Currency Symbol Configuration

## Overview
Bankroll supports customizable currency symbols for displaying monetary amounts across all exports and views.

## Configuration

### Method 1: Environment Variable (Recommended)
Add the `CURRENCY_SYMBOL` variable to your `.env` file:

```env
CURRENCY_SYMBOL=$
```

### Method 2: Application Config
Alternatively, you can set the currency symbol directly in `config/app.php`:

```php
'currency_symbol' => '$',
```

## Supported Currency Symbols
You can use any currency symbol supported by your system encoding (UTF-8):

- `$` - US Dollar (default)
- `€` - Euro
- `£` - British Pound
- `¥` - Japanese Yen / Chinese Yuan
- `₹` - Indian Rupee
- `₽` - Russian Ruble
- `₺` - Turkish Lira
- `R$` - Brazilian Real
- `A$` - Australian Dollar
- `C$` - Canadian Dollar
- And many more...

## Examples

### US Dollar (Default)
```env
CURRENCY_SYMBOL=$
```
Output: `$1,234.56`

### Euro
```env
CURRENCY_SYMBOL=€
```
Output: `€1,234.56`

### Indian Rupee
```env
CURRENCY_SYMBOL=₹
```
Output: `₹1,234.56`

### Multi-character Symbols
```env
CURRENCY_SYMBOL=USD
```
Output: `USD1,234.56`

## Where Currency Symbol Appears

The configured currency symbol will be used in:

1. **Excel Exports** - All monetary columns (Withdraw, Deposit, Balance)
2. **CSV Exports** - All monetary columns (Withdraw, Deposit, Balance)
3. **PDF Exports** - All monetary columns (Withdraw, Deposit, Balance)
4. **Web Interface** - Transaction table display

## Implementation Notes

- The currency symbol is retrieved from the configuration on every request
- No application restart is required when changing the currency symbol via `.env`
- The symbol is prepended to the numeric value (e.g., `$100.00`)
- Number formatting (thousands separator, decimal places) remains consistent regardless of currency symbol
- If no currency symbol is configured, the default `$` is used

## Troubleshooting

### Currency symbol not showing correctly in PDF exports
- The application uses DejaVu Sans font for PDF generation, which supports Unicode characters including ₹, €, £, ¥, etc.
- DomPDF configuration has `convert_entities` set to `false` to preserve Unicode symbols
- Ensure your `.env` file is saved with UTF-8 encoding
- Clear application cache: `php artisan config:clear`

### Currency symbol not showing correctly in other views
- Ensure your system and application support UTF-8 encoding
- Check that your `.env` file is saved with UTF-8 encoding
- Clear application cache: `php artisan config:clear`

### Export files showing wrong symbol
- Verify the `CURRENCY_SYMBOL` value in your `.env` file
- Clear configuration cache: `php artisan config:clear`
- Check that the value doesn't contain extra spaces or quotes

## Technical Details

The currency symbol configuration is defined in `config/app.php`:

```php
'currency_symbol' => env('CURRENCY_SYMBOL', '$'),
```

This allows the value to be:
1. Set via environment variable (`.env` file) - Preferred
2. Fall back to the default value (`$`) if not configured

The implementation ensures consistent currency display across:
- Backend: `App\Exports\TransactionsExport`
- Views: `resources/views/exports/transactions-pdf.blade.php`
- Frontend: `resources/views/home.blade.php` (JavaScript formatting)

### PDF Export Unicode Support

PDF exports use the following configuration to support Unicode currency symbols:

1. **Font**: DejaVu Sans font family (supports Unicode characters)
2. **Encoding**: UTF-8 meta charset in the PDF template
3. **DomPDF Config**: `convert_entities` set to `false` to preserve Unicode characters

This ensures that symbols like ₹ (Indian Rupee), € (Euro), £ (Pound), ¥ (Yen/Yuan) render correctly in PDF exports.
