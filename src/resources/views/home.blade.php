@extends('layouts.app')

@section('title', 'Home')

@section('content')
<div class="container-fluid">
    <!-- Filter Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label for="bankFilter" class="form-label">Bank</label>
                            <select class="form-select" id="bankFilter">
                                <option value="">Select Bank</option>
                                @foreach($banks as $bank)
                                    <option value="{{ $bank }}">{{ $bank }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="yearFilter" class="form-label">Year</label>
                            <select class="form-select" id="yearFilter">
                                <option value="">Select Year</option>
                                @foreach($years as $year)
                                    <option value="{{ $year }}">{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="monthFilter" class="form-label">Month</label>
                            <select class="form-select" id="monthFilter">
                                <option value="">Select Month</option>
                                <option value="1">January</option>
                                <option value="2">February</option>
                                <option value="3">March</option>
                                <option value="4">April</option>
                                <option value="5">May</option>
                                <option value="6">June</option>
                                <option value="7">July</option>
                                <option value="8">August</option>
                                <option value="9">September</option>
                                <option value="10">October</option>
                                <option value="11">November</option>
                                <option value="12">December</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="button" class="btn btn-primary w-100" id="loadTransactions">
                                <i class="bi bi-search"></i> Load Transactions
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Spinner -->
    <div class="row d-none" id="loadingSpinner">
        <div class="col-12 text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    </div>

    <!-- No Data Message -->
    <div class="row d-none" id="noDataMessage">
        <div class="col-12">
            <div class="alert alert-info text-center">
                <i class="bi bi-info-circle"></i> No Data Available
            </div>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="row d-none" id="transactionsSection">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Transactions</h5>
                        <button type="button" class="btn btn-success" id="saveChanges" style="display: none;">
                            <i class="bi bi-save"></i> Save Changes
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Date</th>
                                    <th>Description</th>
                                    <th>Category</th>
                                    <th>Notes</th>
                                    <th class="text-end">Withdraw</th>
                                    <th class="text-end">Deposit</th>
                                    <th class="text-end">Balance</th>
                                </tr>
                            </thead>
                            <tbody id="transactionsTableBody">
                                <!-- Transactions will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const loadBtn = document.getElementById('loadTransactions');
    const bankFilter = document.getElementById('bankFilter');
    const yearFilter = document.getElementById('yearFilter');
    const monthFilter = document.getElementById('monthFilter');
    const loadingSpinner = document.getElementById('loadingSpinner');
    const noDataMessage = document.getElementById('noDataMessage');
    const transactionsSection = document.getElementById('transactionsSection');
    const transactionsTableBody = document.getElementById('transactionsTableBody');
    const saveChangesBtn = document.getElementById('saveChanges');

    const categories = @json($categories);
    
    // Track pending changes
    let pendingChanges = {};

    // Load transactions
    loadBtn.addEventListener('click', async () => {
        const bank = bankFilter.value;
        const year = yearFilter.value;
        const month = monthFilter.value;

        if (!bank || !year || !month) {
            alert('Please select bank, year, and month');
            return;
        }

        // Show loading spinner
        loadingSpinner.classList.remove('d-none');
        noDataMessage.classList.add('d-none');
        transactionsSection.classList.add('d-none');

        try {
            const response = await fetch('{{ route('transactions.get') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ bank, year, month })
            });

            const data = await response.json();
            loadingSpinner.classList.add('d-none');

            if (data.success && data.transactions.length > 0) {
                displayTransactions(data.transactions);
            } else {
                noDataMessage.classList.remove('d-none');
            }
        } catch (error) {
            loadingSpinner.classList.add('d-none');
            alert('Error loading transactions: ' + error.message);
        }
    });

    // Display transactions in table
    function displayTransactions(transactions) {
        transactionsTableBody.innerHTML = '';
        pendingChanges = {}; // Reset pending changes
        saveChangesBtn.style.display = 'none'; // Hide save button
        
        transactions.forEach(transaction => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${formatDate(transaction.date)}</td>
                <td title="${transaction.description}">${truncateText(transaction.description, 50)}</td>
                <td>
                    <select class="form-select form-select-sm category-select" data-transaction-id="${transaction.id}">
                        <option value="">None</option>
                        ${categories.map(cat => `
                            <option value="${cat.id}" ${transaction.category_id == cat.id ? 'selected' : ''}>
                                ${cat.name}
                            </option>
                        `).join('')}
                    </select>
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm notes-input" 
                           data-transaction-id="${transaction.id}" 
                           value="${transaction.notes || ''}" 
                           placeholder="Add notes...">
                </td>
                <td class="text-end">${formatCurrency(transaction.withdraw)}</td>
                <td class="text-end">${formatCurrency(transaction.deposit)}</td>
                <td class="text-end">${formatCurrency(transaction.balance)}</td>
            `;
            transactionsTableBody.appendChild(row);
        });

        transactionsSection.classList.remove('d-none');

        // Add event listeners for category changes
        document.querySelectorAll('.category-select').forEach(select => {
            select.addEventListener('change', (e) => {
                const transactionId = e.target.dataset.transactionId;
                if (!pendingChanges[transactionId]) {
                    pendingChanges[transactionId] = {};
                }
                pendingChanges[transactionId].category_id = e.target.value || null;
                saveChangesBtn.style.display = 'block'; // Show save button
            });
        });

        // Add event listeners for notes changes
        document.querySelectorAll('.notes-input').forEach(input => {
            input.addEventListener('input', (e) => {
                const transactionId = e.target.dataset.transactionId;
                if (!pendingChanges[transactionId]) {
                    pendingChanges[transactionId] = {};
                }
                pendingChanges[transactionId].notes = e.target.value || null;
                saveChangesBtn.style.display = 'block'; // Show save button
            });
        });
    }

    // Save all pending changes
    saveChangesBtn.addEventListener('click', async () => {
        if (Object.keys(pendingChanges).length === 0) {
            return;
        }

        saveChangesBtn.disabled = true;
        saveChangesBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Saving...';

        let allSuccess = true;
        for (const [transactionId, data] of Object.entries(pendingChanges)) {
            const success = await updateTransaction(transactionId, data);
            if (!success) {
                allSuccess = false;
            }
        }

        if (allSuccess) {
            pendingChanges = {};
            saveChangesBtn.style.display = 'none';
            
            // Show success feedback
            const alert = document.createElement('div');
            alert.className = 'alert alert-success alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3';
            alert.style.zIndex = '9999';
            alert.innerHTML = `
                Changes saved successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(alert);
            setTimeout(() => alert.remove(), 3000);
        } else {
            alert('Some changes failed to save. Please try again.');
        }

        saveChangesBtn.disabled = false;
        saveChangesBtn.innerHTML = '<i class="bi bi-save"></i> Save Changes';
    });

    // Update transaction
    async function updateTransaction(transactionId, data) {
        try {
            const response = await fetch(`/transactions/${transactionId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-HTTP-Method-Override': 'PATCH'
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();
            return result.success;
        } catch (error) {
            console.error('Error updating transaction:', error);
            return false;
        }
    }

    // Helper functions
    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
    }

    function formatCurrency(value) {
        if (!value) return '-';
        return '$' + parseFloat(value).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    }

    function truncateText(text, maxLength) {
        if (text.length <= maxLength) return text;
        return text.substring(0, maxLength) + '...';
    }
</script>
@endpush
