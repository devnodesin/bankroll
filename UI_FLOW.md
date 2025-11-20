# Rules Feature - UI Flow Documentation

## Navigation Flow

```
Home Page (/)
    ↓
    [Rules Button in Header] → Rules Management Page (/rules)
                                    ↓
                            [Create/Edit/Delete Rules]
                                    ↓
                            [Back to Home] → Home Page (/)
                                                ↓
                                    [Load Transactions for Bank/Year/Month]
                                                ↓
                                        [Apply Rules Button]
                                                ↓
                                        [Apply Rules Modal]
                                                ↓
                                    [Choose: Fill Blank or Overwrite]
                                                ↓
                                        [Confirm Apply Rules]
                                                ↓
                                    [Transactions Auto-Reload with Categories]
```

## Screen Components

### 1. Navigation Header (All Pages)
```
┌────────────────────────────────────────────────────────────────────┐
│ Bankroll                                                           │
│  [Import] [Banks] [Categories] [Rules] [Theme] [Logout]           │
└────────────────────────────────────────────────────────────────────┘
                                      ↑
                                   NEW BUTTON
```

### 2. Rules Management Page (/rules)

#### Top Section
```
┌────────────────────────────────────────────────────────────────────┐
│ ⚙ Transaction Rules                           [← Back to Home]     │
└────────────────────────────────────────────────────────────────────┘
```

#### Create Rule Card
```
┌────────────────────────────────────────────────────────────────────┐
│ ➕ Create New Rule                                                 │
├────────────────────────────────────────────────────────────────────┤
│ Description Match *  │ Category *        │ Transaction Type * │    │
│ [e.g., AMAZON]       │ [Select Category] │ [Both/Withdraw/   ]│    │
│                      │                   │  Deposit          │    │
│                      │                   │                   │ [Create] │
└────────────────────────────────────────────────────────────────────┘
```

#### Existing Rules Table
```
┌────────────────────────────────────────────────────────────────────┐
│ 📋 Existing Rules                                                  │
├─────────────┬─────────────┬──────────────┬──────────┬─────────────┤
│ Description │ Category    │ Type         │ Created  │ Actions     │
├─────────────┼─────────────┼──────────────┼──────────┼─────────────┤
│ AMAZON      │ Shopping    │ [Withdraw]   │ Nov 20   │ [✏️] [🗑️]   │
│ SALARY      │ Income      │ [Deposit]    │ Nov 19   │ [✏️] [🗑️]   │
│ ATM         │ Cash        │ [Withdraw]   │ Nov 18   │ [✏️] [🗑️]   │
└─────────────┴─────────────┴──────────────┴──────────┴─────────────┘
```

#### Edit Mode (When Pencil Clicked)
```
┌────────────────────────────────────────────────────────────────────┐
│ [AMAZON____] │ [Shopping ▼] │ [Withdraw ▼] │ Nov 20   │ [✓] [✗]   │
└────────────────────────────────────────────────────────────────────┘
```

#### Info Section
```
┌────────────────────────────────────────────────────────────────────┐
│ ℹ️ How Rules Work                                                  │
│ • Rules automatically categorize transactions based on description │
│ • Description matching is case-insensitive                         │
│ • Apply rules from main transaction view                           │
│ • Choose to overwrite existing categories or fill blank ones only  │
└────────────────────────────────────────────────────────────────────┘
```

### 3. Home Page with Transactions (/home)

#### Filter Section
```
┌────────────────────────────────────────────────────────────────────┐
│ Bank ▼        │ Year ▼        │ Month ▼       │ [🔍 Load         ]│
│ [Chase Bank ] │ [2024       ] │ [November   ] │  Transactions    │
└────────────────────────────────────────────────────────────────────┘
```

#### Transactions Table with New Button
```
┌────────────────────────────────────────────────────────────────────┐
│ Transactions          [🔧 Apply Rules] [💾 Save] [📥 Export ▼]    │
│                               ↑                                     │
│                           NEW BUTTON                                │
├────────┬─────────────┬───────────────┬──────────┬──────────┬───────┤
│ Date   │ Description │ Category      │ Withdraw │ Deposit  │ Bal   │
├────────┼─────────────┼───────────────┼──────────┼──────────┼───────┤
│ Nov 20 │ AMAZON.COM  │ [Shopping ▼]  │ $50.00   │    -     │ $950  │
│ Nov 19 │ SALARY      │ [Income ▼]    │    -     │ $1000.00 │ $1000 │
│ Nov 18 │ ATM CASH    │ [Cash ▼]      │ $20.00   │    -     │ $980  │
└────────┴─────────────┴───────────────┴──────────┴──────────┴───────┘
```

### 4. Apply Rules Modal

```
┌─────────────────────────────────────────────────────────────────┐
│ Apply Rules to Transactions                               [×]   │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  ℹ️  Rules will be applied to all transactions in the current  │
│      view (selected bank, year, and month).                    │
│                                                                 │
│  How should rules handle existing categories?                  │
│                                                                 │
│  ○ Fill blank only - Only apply rules to transactions         │
│    without a category                                          │
│                                                                 │
│  ○ Overwrite all - Replace existing categories with rule      │
│    matches                                                     │
│                                                                 │
├─────────────────────────────────────────────────────────────────┤
│                                    [Cancel] [✓ Apply Rules]     │
└─────────────────────────────────────────────────────────────────┘
```

### 5. Success/Error Messages

#### Success Message
```
┌─────────────────────────────────────────────────────────────────┐
│ ✓ Rules applied successfully. 15 transaction(s) updated.  [×]  │
└─────────────────────────────────────────────────────────────────┘
```

#### Error Message
```
┌─────────────────────────────────────────────────────────────────┐
│ ✗ Failed to apply rules: No rules found.                  [×]  │
└─────────────────────────────────────────────────────────────────┘
```

#### Loading State
```
┌─────────────────────────────────────────────────────────────────┐
│ [⟳ Applying...] Apply Rules                                    │
└─────────────────────────────────────────────────────────────────┘
```

## User Interaction Flow

### Creating a Rule
1. User clicks "Rules" button in navigation
2. User fills in rule creation form:
   - Enters description text (e.g., "AMAZON")
   - Selects category from dropdown (e.g., "Shopping")
   - Selects transaction type (e.g., "Withdraw")
3. User clicks "Create Rule" button
4. Form data sent via AJAX to server
5. Success message shows: "Rule created successfully"
6. Page auto-reloads to show new rule in table

### Editing a Rule
1. User clicks pencil (✏️) icon on a rule row
2. Row switches to edit mode with input fields
3. User modifies the values
4. User clicks check (✓) icon to save or X icon to cancel
5. If save: AJAX request updates rule, success message shown, page reloads
6. If cancel: Row returns to view mode without changes

### Deleting a Rule
1. User clicks trash (🗑️) icon on a rule row
2. Browser confirmation dialog appears
3. If confirmed: AJAX request deletes rule, success message shown, row removed
4. If cancelled: No action taken

### Applying Rules
1. User loads transactions for a bank/year/month
2. "Apply Rules" button becomes visible
3. User clicks "Apply Rules" button
4. Modal appears with two options:
   - Fill blank only (default, safer)
   - Overwrite all (requires caution)
5. User selects desired option
6. User clicks "Apply Rules" button in modal
7. Button shows loading spinner
8. AJAX request processes rules
9. Success message shows number of transactions updated
10. Modal closes after 2 seconds
11. Transactions table auto-reloads to show updated categories

## Responsive Behavior

All components are responsive using Bootstrap 5.3:
- Forms stack vertically on mobile devices
- Tables become scrollable on small screens
- Buttons adjust size for touch interfaces
- Modals scale appropriately for screen size

## Accessibility Features

- Semantic HTML with proper heading hierarchy
- ARIA labels on buttons and interactive elements
- Keyboard navigation support
- Screen reader friendly labels
- Focus indicators on interactive elements
- Confirmation dialogs for destructive actions

## Animation & Feedback

- Smooth fade-in/fade-out for alerts
- Loading spinners during AJAX operations
- Button state changes (disabled during operations)
- Auto-dismiss success messages (5 seconds)
- Smooth scroll to top when showing alerts

## Color Coding

- **Primary (Blue)**: Primary actions (Apply Rules, Edit)
- **Success (Green)**: Success messages, Create button
- **Danger (Red)**: Delete actions, Error messages
- **Warning (Yellow)**: Caution messages
- **Info (Light Blue)**: Informational messages
- **Secondary (Gray)**: Cancel actions, disabled states

## Badge Colors

- **Both**: Gray badge
- **Withdraw**: Red badge
- **Deposit**: Green badge
- **System**: Blue badge (for categories)

This UI flow provides a clear, intuitive experience for managing and applying transaction classification rules.
