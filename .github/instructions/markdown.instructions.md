---
applyTo: '**/*.md'
---

# Markdown Documentation Instructions for Bankroll

## Overview
All documentation for the Bankroll project should be written in Markdown format. This ensures consistency, readability, and easy version control.

## File Organization

### Documentation Structure
```
bankroll/
├── README.md                    # Main project overview
├── docs/
│   ├── user-guide.md           # End-user documentation
│   ├── architecture.md         # Technical architecture
│   └── deployment.md           # Deployment instructions
└── prd/
    ├── gh-001.md               # Task 1
    ├── gh-002.md               # Task 2
    └── ...                     # More tasks
```

## Markdown Formatting Standards

### Headings
Use ATX-style headings with proper hierarchy:

```markdown
# H1 - Main Title (One per document)
## H2 - Major Sections
### H3 - Subsections
#### H4 - Sub-subsections
```

**Rules:**
- Only one H1 per document (the main title)
- Don't skip heading levels (don't jump from H2 to H4)
- Add blank lines before and after headings
- Use sentence case for headings

### Emphasis
```markdown
**Bold text** for strong emphasis
*Italic text* for mild emphasis
`code` for inline code or technical terms
~~Strikethrough~~ for deprecated content
```

### Lists

**Unordered Lists:**
```markdown
- First item
- Second item
  - Nested item
  - Another nested item
- Third item
```

**Ordered Lists:**
```markdown
1. First step
2. Second step
3. Third step
```

**Task Lists:**
```markdown
- [ ] Incomplete task
- [x] Completed task
```

### Code Blocks

**Inline Code:**
```markdown
Use `composer install` to install dependencies.
```

**Code Blocks with Syntax Highlighting:**
````markdown
```php
public function index()
{
    return view('home');
}
```

```bash
php artisan migrate
```

```javascript
const theme = localStorage.getItem('theme');
```
````

### Links
```markdown
[Link text](https://example.com)
[Relative link](../docs/guide.md)
[Link with title](https://example.com "Title text")
```

### Images
```markdown
![Alt text](path/to/image.png)
![Alt text](url "Optional title")
```

### Tables
```markdown
| Column 1 | Column 2 | Column 3 |
|----------|----------|----------|
| Data 1   | Data 2   | Data 3   |
| Data 4   | Data 5   | Data 6   |

<!-- Alignment -->
| Left | Center | Right |
|:-----|:------:|------:|
| L1   | C1     | R1    |
```

### Blockquotes
```markdown
> This is a blockquote.
> It can span multiple lines.

> **Note:** Important information here.
```

### Horizontal Rules
```markdown
---
or
***
or
___
```

## Document Templates

### README.md Template
```markdown
# Project Name

Brief description of the project.

## Features

- Feature 1
- Feature 2
- Feature 3

## Installation

1. Clone the repository
2. Install dependencies
3. Configure environment

## Usage

How to use the application.

## Requirements

- Requirement 1
- Requirement 2

## License

License information
```

### Task Document Template (PRD)
```markdown
# Task GH-XXX: Task Title

## Objective
Clear, concise objective statement.

## Requirements

### 1. Requirement Category
- Specific requirement
- Another requirement

### 2. Another Category
- More requirements

## Acceptance Criteria
- [ ] Criterion 1
- [ ] Criterion 2
- [ ] Criterion 3

## Technical Notes
- Technical consideration 1
- Technical consideration 2
```

### User Guide Template
```markdown
# User Guide - [Application Name]

## Table of Contents
- [Getting Started](#getting-started)
- [Feature 1](#feature-1)
- [Feature 2](#feature-2)

## Getting Started

Introduction for new users.

### Login
1. Navigate to login page
2. Enter credentials
3. Click login button

## Feature 1

Detailed explanation with screenshots.

## Troubleshooting

Common issues and solutions.
```

## Writing Style Guidelines

### General Principles
1. **Be Clear and Concise**: Get to the point quickly
2. **Use Active Voice**: "Click the button" not "The button should be clicked"
3. **Be Specific**: Provide exact names, paths, and commands
4. **Use Examples**: Show, don't just tell
5. **Stay Organized**: Use headings and lists appropriately

### Technical Documentation
- Use precise technical terms
- Provide code examples where relevant
- Include command-line examples with proper formatting
- Document assumptions and prerequisites
- Explain the "why" not just the "what"

### User Documentation
- Use plain language, avoid jargon
- Include step-by-step instructions
- Add screenshots or diagrams when helpful
- Anticipate common questions
- Provide troubleshooting section

## Bankroll-Specific Guidelines

### PRD Documents (prd/gh-XXX.md)
Each task document should include:

1. **Clear Objective**: One-sentence goal
2. **Detailed Requirements**: Organized by category
3. **Acceptance Criteria**: Checklist format
4. **Technical Notes**: Implementation hints

**Example:**
```markdown
# Task GH-001: Laravel Project Setup

## Objective
Set up a new Laravel project with basic configuration.

## Requirements

### 1. Installation
- Create new Laravel project in `src/` directory
- Configure environment variables

### 2. Configuration
- Set application name to "Bankroll"
- Configure database connection

## Acceptance Criteria
- [ ] Laravel installed successfully
- [ ] Application runs without errors
- [ ] Database connection works

## Technical Notes
- Use latest stable Laravel version
- Follow Laravel best practices
```

### User Guide Structure
```markdown
# Bankroll User Guide

## Introduction
Brief overview of Bankroll.

## Getting Started

### Logging In
1. Open the application
2. Enter username and password
3. Click "Login"

## Importing Transactions

### Preparing Your File
Your file must have these columns:
- Date
- Description
- Withdraw
- Deposit
- Balance

### Import Steps
1. Click "Import" button
2. Select bank from dropdown
3. Choose file
4. Click "Import"

## Classifying Transactions

### Adding Categories
How to add custom categories.

### Assigning Categories
How to classify transactions.

## Exporting Data

### Export Formats
- Excel (.xlsx)
- CSV (.csv)
- PDF (.pdf)

### Export Steps
1. Filter transactions
2. Click "Export"
3. Choose format

## Troubleshooting

### Import Failed
**Problem:** File rejected
**Solution:** Check column names match exactly

### Can't Delete Category
**Problem:** Delete button disabled
**Solution:** System categories cannot be deleted
```

## Markdown Best Practices

### DO:
- ✅ Use blank lines to separate elements
- ✅ Use consistent formatting throughout
- ✅ Include code blocks for commands and code
- ✅ Use task lists for checklists
- ✅ Add links to related documentation
- ✅ Keep lines reasonably short (80-100 chars when possible)
- ✅ Use relative links for internal documents
- ✅ Include table of contents for long documents

### DON'T:
- ❌ Mix different list marker styles in same list
- ❌ Use HTML when Markdown suffices
- ❌ Forget to add blank lines around code blocks
- ❌ Create overly deep heading hierarchies (> H4)
- ❌ Use unclear link text like "click here"
- ❌ Paste code without syntax highlighting
- ❌ Create tables that are too wide

## Tools and Validation

### Recommended Editors
- VS Code with Markdown extensions
- Any text editor with Markdown preview

### Linting
Use markdownlint or similar tools to check:
- Consistent heading styles
- Proper list formatting
- No trailing spaces
- Blank lines around code blocks

### Preview
Always preview your Markdown before committing to ensure:
- Formatting renders correctly
- Links work
- Code blocks display properly
- Tables are readable

## Common Patterns

### Admonitions
```markdown
> **Note:** Important information that needs attention.

> **Warning:** Critical information about potential issues.

> **Tip:** Helpful suggestion for better results.
```

### File Paths
```markdown
Use code formatting for file paths: `src/app/Models/Transaction.php`
```

### Commands
```markdown
Run this command:
```bash
php artisan migrate --seed
```
```

### Configuration
```markdown
In your `.env` file:
```env
APP_NAME=Bankroll
DB_CONNECTION=sqlite
```
```

## Version Control

### Commit Messages for Docs
```
docs: Add user guide for import feature
docs: Update installation instructions
docs: Fix typo in deployment guide
docs: Improve code examples in API docs
```

## Remember
1. Write for your audience (users vs developers)
2. Keep documentation up to date with code changes
3. Use examples liberally
4. Make it scannable with headings and lists
5. Test all commands and code examples
6. Link related documentation
7. Include troubleshooting sections
8. Be consistent in formatting and terminology
