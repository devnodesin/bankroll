# Blade Template Instructions for Bankroll

## Overview
Blade is Laravel's templating engine. Use it for all views in the Bankroll application with Bootstrap 5.3 for styling.

## Layout Structure

### Base Layout
Create a master layout that all pages extend.

**File: `resources/views/layouts/app.blade.php`**
```blade
<!DOCTYPE html>
<html lang="en" data-bs-theme="{{ session('theme', 'light') }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} - @yield('title', 'Dashboard')</title>
    
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    @stack('styles')
</head>
<body>
    @include('layouts.header')
    
    <main class="container-fluid py-4">
        @yield('content')
    </main>
    
    @include('layouts.footer')
    
    <!-- Bootstrap 5.3 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    @stack('scripts')
</body>
</html>
```

### Header Component
**File: `resources/views/layouts/header.blade.php`**
```blade
<nav class="navbar navbar-expand-lg bg-body-tertiary">
    <div class="container-fluid">
        <span class="navbar-brand mb-0 h1">{{ config('app.name') }}</span>
        
        <div class="d-flex">
            <!-- Theme Switcher Button -->
            <button class="btn btn-outline-secondary" id="theme-toggle" type="button">
                <i class="bi bi-sun-fill" id="theme-icon"></i>
            </button>
            
            @auth
                <form method="POST" action="{{ route('logout') }}" class="ms-2">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger">Logout</button>
                </form>
            @endauth
        </div>
    </div>
</nav>
```

### Footer Component
**File: `resources/views/layouts/footer.blade.php`**
```blade
<footer class="footer mt-auto py-3 bg-body-tertiary">
    <div class="container text-center">
        <span class="text-muted">
            {{ config('app.name') }} {{ config('app.version', '1.0.0') }} built with love by Devnodes.in
        </span>
    </div>
</footer>
```

## Page Templates

### Extending Layout
Every page should extend the base layout:

```blade
@extends('layouts.app')

@section('title', 'Page Title')

@section('content')
    <!-- Page content here -->
@endsection

@push('scripts')
    <script>
        // Page-specific JavaScript
    </script>
@endpush
```

### Login Page Example
```blade
@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title text-center mb-4">Login</h5>
                
                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control @error('username') is-invalid @enderror" 
                               id="username" name="username" value="{{ old('username') }}" required>
                        @error('username')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" 
                               id="password" name="password" required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100">Login</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
```

## Blade Directives Usage

### Authentication Directives
```blade
@auth
    <!-- Show only to authenticated users -->
@endauth

@guest
    <!-- Show only to guests -->
@endguest
```

### Conditional Rendering
```blade
@if($transactions->count() > 0)
    <!-- Show table -->
@else
    <div class="alert alert-info text-center">No Data Available</div>
@endif

@empty($transactions)
    <p>No transactions found.</p>
@endempty

@isset($variable)
    {{ $variable }}
@endisset
```

### Loops
```blade
@foreach($transactions as $transaction)
    <tr>
        <td>{{ $transaction->date->format('M d, Y') }}</td>
        <td>{{ $transaction->description }}</td>
        <td>{{ number_format($transaction->withdraw, 2) }}</td>
    </tr>
@endforeach

@forelse($categories as $category)
    <option value="{{ $category->id }}">{{ $category->name }}</option>
@empty
    <option disabled>No categories available</option>
@endforelse
```

### Error Handling
```blade
@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@error('field_name')
    <div class="invalid-feedback">{{ $message }}</div>
@enderror
```

### Session Messages
```blade
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
```

## Components

### Creating Reusable Components
Use Blade components for reusable UI elements.

**Create: `resources/views/components/transaction-row.blade.php`**
```blade
@props(['transaction'])

<tr>
    <td>{{ $transaction->date->format('M d, Y') }}</td>
    <td>{{ Str::limit($transaction->description, 50) }}</td>
    <td>
        <select class="form-select form-select-sm category-select" 
                data-transaction-id="{{ $transaction->id }}">
            <option value="">None</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}" 
                        @selected($transaction->category_id == $category->id)>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
    </td>
    <td class="text-end">${{ number_format($transaction->withdraw, 2) }}</td>
    <td class="text-end">${{ number_format($transaction->deposit, 2) }}</td>
    <td class="text-end">${{ number_format($transaction->balance, 2) }}</td>
</tr>
```

**Usage:**
```blade
@foreach($transactions as $transaction)
    <x-transaction-row :transaction="$transaction" />
@endforeach
```

## Bootstrap 5.3 Integration

### Form Elements
```blade
<!-- Text Input -->
<div class="mb-3">
    <label for="field" class="form-label">Label</label>
    <input type="text" class="form-control" id="field" name="field">
</div>

<!-- Select Dropdown -->
<div class="mb-3">
    <label for="select" class="form-label">Choose</label>
    <select class="form-select" id="select" name="select">
        <option selected disabled>Select...</option>
        <option value="1">Option 1</option>
    </select>
</div>

<!-- File Upload -->
<div class="mb-3">
    <label for="file" class="form-label">Upload File</label>
    <input type="file" class="form-control" id="file" name="file">
</div>
```

### Tables
```blade
<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th>Date</th>
                <th>Description</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $transaction)
                <tr>
                    <td>{{ $transaction->date }}</td>
                    <td>{{ $transaction->description }}</td>
                    <td>{{ $transaction->amount }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center">No data available</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
```

### Modals
```blade
<!-- Trigger Button -->
<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#importModal">
    Import Transactions
</button>

<!-- Modal -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Import Transactions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('transactions.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <!-- Form fields -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Import</button>
                </div>
            </form>
        </div>
    </div>
</div>
```

### Alerts
```blade
<div class="alert alert-success alert-dismissible fade show" role="alert">
    Success message here!
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
```

## JavaScript Integration

### Theme Switcher Script
```blade
@push('scripts')
<script>
    // Theme switcher
    const themeToggle = document.getElementById('theme-toggle');
    const html = document.documentElement;
    
    // Load saved theme or default to light
    const savedTheme = localStorage.getItem('theme') || 'light';
    html.setAttribute('data-bs-theme', savedTheme);
    
    themeToggle.addEventListener('click', () => {
        const currentTheme = html.getAttribute('data-bs-theme');
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';
        
        html.setAttribute('data-bs-theme', newTheme);
        localStorage.setItem('theme', newTheme);
    });
</script>
@endpush
```

### AJAX with CSRF Token
```blade
@push('scripts')
<script>
    // Set CSRF token for all AJAX requests
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    
    // Example AJAX call
    fetch('/api/endpoint', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ data: 'value' })
    })
    .then(response => response.json())
    .then(data => console.log(data))
    .catch(error => console.error('Error:', error));
</script>
@endpush
```

## Best Practices

### 1. Use Named Routes
```blade
<!-- GOOD -->
<a href="{{ route('transactions.index') }}">Transactions</a>
<form action="{{ route('transactions.store') }}" method="POST">

<!-- AVOID -->
<a href="/transactions">Transactions</a>
```

### 2. Escape Output (XSS Protection)
```blade
<!-- Escaped (safe) -->
{{ $variable }}

<!-- Unescaped (only for trusted HTML) -->
{!! $trustedHtml !!}
```

### 3. Use Old Input for Forms
```blade
<input type="text" name="name" value="{{ old('name', $default) }}">
```

### 4. CSRF Protection
```blade
<form method="POST">
    @csrf
    <!-- form fields -->
</form>

<!-- For PUT/PATCH/DELETE -->
<form method="POST">
    @csrf
    @method('PUT')
    <!-- form fields -->
</form>
```

### 5. Include Partials
```blade
@include('partials.alerts')
@include('partials.transaction-table', ['transactions' => $transactions])
```

## Bankroll-Specific Guidelines

### Read-Only Fields Display
```blade
<td class="bg-light">{{ $transaction->date->format('M d, Y') }}</td>
<td class="bg-light">{{ $transaction->description }}</td>
<td class="bg-light text-end">${{ number_format($transaction->withdraw, 2) }}</td>
```

### Editable Classification Field
```blade
<td>
    <select class="form-select form-select-sm" 
            data-transaction-id="{{ $transaction->id }}"
            onchange="updateCategory(this)">
        <option value="">Select Category</option>
        @foreach($categories as $category)
            <option value="{{ $category->id }}" 
                    @selected($transaction->category_id == $category->id)>
                {{ $category->name }}
            </option>
        @endforeach
    </select>
</td>
```

## Remember
1. Always use Bootstrap 5.3 classes for styling
2. Escape output with {{ }} unless you trust the HTML
3. Include @csrf on all forms
4. Use @error directives for validation feedback
5. Leverage Blade components for reusability
6. Keep logic in controllers, not views
7. Use named routes for maintainability
8. Ensure responsive design with Bootstrap grid
