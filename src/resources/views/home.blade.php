@extends('layouts.app')

@push('styles')
<style>
    .searchable-dropdown .dropdown-menu {
        padding: 0.5rem;
    }
    
    .searchable-dropdown .category-search {
        margin-bottom: 0.5rem;
    }
    
    .searchable-dropdown .category-search:focus {
        border-color: var(--bs-primary);
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
    
    .searchable-dropdown .category-options {
        max-height: 200px;
        overflow-y: auto;
    }
    
    .searchable-dropdown .category-option {
        cursor: pointer;
        padding: 0.5rem 0.75rem;
        border: none;
        background: transparent;
        text-align: left;
        width: 100%;
        transition: background-color 0.15s ease-in-out;
    }
    
    .searchable-dropdown .category-option:hover {
        background-color: var(--bs-secondary-bg);
    }
    
    .searchable-dropdown .category-option.active {
        background-color: var(--bs-primary);
        color: white;
    }
    
    .searchable-dropdown .category-display {
        font-size: 0.875rem;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
</style>
@endpush

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
                            <select class="form-select" id="monthFilter" disabled>
                                <option value="">Select Bank & Year first</option>
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
                        <div class="btn-group">
                            <button type="button" class="btn btn-success" id="saveChanges" style="display: none;">
                                <i class="bi bi-save"></i> Save Changes
                            </button>
                            <button type="button" class="btn btn-info dropdown-toggle ms-2" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="display: none;">
                                <i class="bi bi-download"></i> Export
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="exportDropdown">
                                <li><a class="dropdown-item" href="#" id="exportExcel"><i class="bi bi-file-earmark-excel"></i> Export as Excel</a></li>
                                <li><a class="dropdown-item" href="#" id="exportCsv"><i class="bi bi-file-earmark-text"></i> Export as CSV</a></li>
                                <li><a class="dropdown-item" href="#" id="exportPdf"><i class="bi bi-file-earmark-pdf"></i> Export as PDF</a></li>
                            </ul>
                        </div>
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

    <!-- Categories Management Modal -->
    <div class="modal fade" id="categoriesModal" tabindex="-1" aria-labelledby="categoriesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="categoriesModalLabel">Manage Categories</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Add Category Form -->
                    <form id="addCategoryForm" class="mb-4">
                        @csrf
                        <div class="input-group">
                            <input type="text" class="form-control" id="categoryName" name="name" placeholder="Enter new category name" required maxlength="50">
                            <button type="submit" class="btn btn-primary" id="addCategoryBtn">
                                <i class="bi bi-plus-circle"></i> Add Category
                            </button>
                        </div>
                        <div id="categoryErrors" class="alert alert-danger mt-2 d-none"></div>
                        <div id="categorySuccess" class="alert alert-success mt-2 d-none"></div>
                    </form>

                    <!-- System Categories -->
                    <div class="mb-4">
                        <h6 class="text-muted">System Categories</h6>
                        <div class="list-group" id="systemCategoriesList">
                            <div class="text-center py-3">
                                <span class="spinner-border spinner-border-sm"></span> Loading...
                            </div>
                        </div>
                    </div>

                    <!-- Custom Categories -->
                    <div>
                        <h6 class="text-muted">Custom Categories</h6>
                        <div class="list-group" id="customCategoriesList">
                            <div class="text-center py-3 text-muted">No custom categories yet</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Banks Management Modal -->
    <div class="modal fade" id="banksModal" tabindex="-1" aria-labelledby="banksModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="banksModalLabel">Manage Banks</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Add Bank Form -->
                    <form id="addBankForm" class="mb-4">
                        @csrf
                        <div class="input-group">
                            <input type="text" class="form-control" id="bankName" name="name" placeholder="Enter bank name" required maxlength="100">
                            <button type="submit" class="btn btn-primary" id="addBankBtn">
                                <i class="bi bi-plus-circle"></i> Add Bank
                            </button>
                        </div>
                        <div id="bankErrors" class="alert alert-danger mt-2 d-none"></div>
                        <div id="bankSuccess" class="alert alert-success mt-2 d-none"></div>
                    </form>

                    <!-- Banks List -->
                    <div>
                        <h6 class="text-muted">Banks</h6>
                        <div class="list-group" id="banksList">
                            <div class="text-center py-3">
                                <span class="spinner-border spinner-border-sm"></span> Loading...
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Import Modal -->
    <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel">Import Transactions</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="importForm" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="importBankName" class="form-label">Bank Name</label>
                            <select class="form-select" id="importBankName" name="bank_name" required>
                                <option value="">Select Bank</option>
                                @foreach($banks as $bank)
                                    <option value="{{ $bank }}">{{ $bank }}</option>
                                @endforeach
                            </select>
                            <div class="form-text">If bank is not listed, add it using the Bank Management button first.</div>
                        </div>
                        <div class="mb-3">
                            <label for="importFile" class="form-label">Select File</label>
                            <input type="file" class="form-control" id="importFile" name="file" accept=".xlsx,.xls,.csv" required>
                            <div class="form-text">Accepted formats: XLS, XLSX, CSV (Max 5MB)</div>
                            <div class="form-text mt-2">
                                <strong>Required columns:</strong> Date, Description, Withdraw, Deposit, Balance
                            </div>
                            <div class="form-text mt-1">
                                <strong>Date format:</strong> DD/MM/YYYY (e.g., 15/03/2024)
                            </div>
                        </div>
                        <div id="importErrors" class="alert alert-danger d-none"></div>
                        <div id="importSuccess" class="alert alert-success d-none"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="importButton">
                            <i class="bi bi-upload"></i> Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const currencySymbol = @json($currencySymbol);
    
    // Categories modal elements
    const categoriesModal = document.getElementById('categoriesModal');
    const addCategoryForm = document.getElementById('addCategoryForm');
    const categoryName = document.getElementById('categoryName');
    const addCategoryBtn = document.getElementById('addCategoryBtn');
    const categoryErrors = document.getElementById('categoryErrors');
    const categorySuccess = document.getElementById('categorySuccess');
    const systemCategoriesList = document.getElementById('systemCategoriesList');
    const customCategoriesList = document.getElementById('customCategoriesList');
    const loadBtn = document.getElementById('loadTransactions');
    const bankFilter = document.getElementById('bankFilter');
    const yearFilter = document.getElementById('yearFilter');
    const monthFilter = document.getElementById('monthFilter');
    const loadingSpinner = document.getElementById('loadingSpinner');
    const noDataMessage = document.getElementById('noDataMessage');
    const transactionsSection = document.getElementById('transactionsSection');
    const transactionsTableBody = document.getElementById('transactionsTableBody');
    const saveChangesBtn = document.getElementById('saveChanges');
    const exportDropdown = document.getElementById('exportDropdown');
    const exportExcel = document.getElementById('exportExcel');
    const exportCsv = document.getElementById('exportCsv');
    const exportPdf = document.getElementById('exportPdf');

    let categories = @json($categories);
    
    // Track pending changes
    let pendingChanges = {};
    let currentFilters = { bank: '', year: '', month: '' };

    // Load categories when modal is opened
    categoriesModal.addEventListener('show.bs.modal', loadCategories);

    async function loadCategories() {
        try {
            const response = await fetch('{{ route('categories.index') }}', {
                headers: { 'X-CSRF-TOKEN': csrfToken }
            });
            const data = await response.json();

            if (data.success) {
                displayCategories(data.system, data.custom);
            }
        } catch (error) {
            systemCategoriesList.innerHTML = '<div class="text-center py-3 text-danger">Failed to load categories</div>';
        }
    }

    function displayCategories(system, custom) {
        // Display system categories
        if (system.length > 0) {
            systemCategoriesList.innerHTML = system.map(cat => `
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <i class="bi bi-shield-check text-primary me-2"></i>
                        <strong>${cat.name}</strong>
                    </div>
                    <span class="badge bg-primary">System</span>
                </div>
            `).join('');
        } else {
            systemCategoriesList.innerHTML = '<div class="text-center py-3 text-muted">No system categories</div>';
        }

        // Display custom categories
        if (custom.length > 0) {
            customCategoriesList.innerHTML = custom.map(cat => `
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <i class="bi bi-tag text-success me-2"></i>
                        ${cat.name}
                    </div>
                    <button class="btn btn-sm btn-outline-danger" onclick="deleteCategory(${cat.id}, '${cat.name}')">
                        <i class="bi bi-trash"></i> Delete
                    </button>
                </div>
            `).join('');
        } else {
            customCategoriesList.innerHTML = '<div class="text-center py-3 text-muted">No custom categories yet</div>';
        }
    }

    // Add category form submission
    addCategoryForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        categoryErrors.classList.add('d-none');
        categorySuccess.classList.add('d-none');
        addCategoryBtn.disabled = true;

        try {
            const response = await fetch('{{ route('categories.store') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ name: categoryName.value })
            });

            const data = await response.json();

            if (data.success) {
                categorySuccess.textContent = data.message;
                categorySuccess.classList.remove('d-none');
                categoryName.value = '';
                
                // Refresh categories
                await loadCategories();
                await refreshCategoriesDropdown();
            } else {
                categoryErrors.textContent = data.message || 'Failed to add category';
                categoryErrors.classList.remove('d-none');
            }
        } catch (error) {
            categoryErrors.textContent = 'Error: ' + error.message;
            categoryErrors.classList.remove('d-none');
        } finally {
            addCategoryBtn.disabled = false;
        }
    });

    // Delete category function
    window.deleteCategory = async function(id, name) {
        if (!confirm(`Are you sure you want to delete the category "${name}"?`)) {
            return;
        }

        try {
            const response = await fetch(`/categories/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                }
            });

            const data = await response.json();

            if (data.success) {
                categorySuccess.textContent = data.message;
                categorySuccess.classList.remove('d-none');
                
                // Refresh categories
                await loadCategories();
                await refreshCategoriesDropdown();

                setTimeout(() => categorySuccess.classList.add('d-none'), 3000);
            } else {
                alert(data.message);
            }
        } catch (error) {
            alert('Error deleting category: ' + error.message);
        }
    };

    // Refresh categories dropdown in transaction table
    async function refreshCategoriesDropdown() {
        try {
            const response = await fetch('{{ route('categories.all') }}', {
                headers: { 'X-CSRF-TOKEN': csrfToken }
            });
            const data = await response.json();

            if (data.success) {
                categories = data.categories;
            }
        } catch (error) {
            console.error('Failed to refresh categories:', error);
        }
    }

    // Banks management
    const banksModal = document.getElementById('banksModal');
    const addBankForm = document.getElementById('addBankForm');
    const bankNameInput = document.getElementById('bankName');
    const addBankBtn = document.getElementById('addBankBtn');
    const bankErrors = document.getElementById('bankErrors');
    const bankSuccess = document.getElementById('bankSuccess');
    const banksList = document.getElementById('banksList');

    // Load banks when modal is opened
    banksModal.addEventListener('show.bs.modal', loadBanks);

    async function loadBanks() {
        try {
            const response = await fetch('{{ route('banks.index') }}', {
                headers: { 'X-CSRF-TOKEN': csrfToken }
            });
            const data = await response.json();

            if (data.success) {
                displayBanks(data.banks);
            }
        } catch (error) {
            console.error('Failed to load banks:', error);
            banksList.innerHTML = '<div class="text-center text-danger">Failed to load banks</div>';
        }
    }

    function displayBanks(banks) {
        if (banks.length === 0) {
            banksList.innerHTML = '<div class="text-center py-3 text-muted">No banks added yet</div>';
            return;
        }

        banksList.innerHTML = banks.map(bank => `
            <div class="list-group-item d-flex justify-content-between align-items-center">
                <span>${bank.name}</span>
                <button class="btn btn-sm btn-outline-danger" onclick="deleteBank(${bank.id}, '${bank.name}')">
                    <i class="bi bi-trash"></i> Delete
                </button>
            </div>
        `).join('');
    }

    // Add bank
    addBankForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        bankErrors.classList.add('d-none');
        bankSuccess.classList.add('d-none');
        addBankBtn.disabled = true;

        try {
            const response = await fetch('{{ route('banks.store') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ name: bankNameInput.value })
            });

            const data = await response.json();

            if (data.success) {
                bankSuccess.textContent = data.message;
                bankSuccess.classList.remove('d-none');
                bankNameInput.value = '';
                await loadBanks();
                await refreshBankDropdown();

                setTimeout(() => bankSuccess.classList.add('d-none'), 3000);
            } else {
                bankErrors.textContent = data.message;
                bankErrors.classList.remove('d-none');
            }
        } catch (error) {
            bankErrors.textContent = 'Error adding bank: ' + error.message;
            bankErrors.classList.remove('d-none');
        } finally {
            addBankBtn.disabled = false;
        }
    });

    // Delete bank (global function for onclick)
    window.deleteBank = async (id, name) => {
        if (!confirm(`Are you sure you want to delete "${name}"?`)) {
            return;
        }

        try {
            const response = await fetch(`/banks/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                }
            });

            const data = await response.json();

            if (data.success) {
                bankSuccess.textContent = data.message;
                bankSuccess.classList.remove('d-none');
                await loadBanks();
                await refreshBankDropdown();

                setTimeout(() => bankSuccess.classList.add('d-none'), 3000);
            } else {
                alert(data.message);
            }
        } catch (error) {
            alert('Error deleting bank: ' + error.message);
        }
    };

    // Refresh bank dropdown in filter
    async function refreshBankDropdown() {
        try {
            const response = await fetch('{{ route('banks.index') }}', {
                headers: { 'X-CSRF-TOKEN': csrfToken }
            });
            const data = await response.json();

            if (data.success) {
                // Update main filter dropdown
                const currentSelection = bankFilter.value;
                bankFilter.innerHTML = '<option value="">Select Bank</option>';
                data.banks.forEach(bank => {
                    const option = document.createElement('option');
                    option.value = bank.name;
                    option.textContent = bank.name;
                    if (bank.name === currentSelection) {
                        option.selected = true;
                    }
                    bankFilter.appendChild(option);
                });

                // Update import modal dropdown
                const importBankName = document.getElementById('importBankName');
                const currentImportSelection = importBankName.value;
                importBankName.innerHTML = '<option value="">Select Bank</option>';
                data.banks.forEach(bank => {
                    const option = document.createElement('option');
                    option.value = bank.name;
                    option.textContent = bank.name;
                    if (bank.name === currentImportSelection) {
                        option.selected = true;
                    }
                    importBankName.appendChild(option);
                });
            }
        } catch (error) {
            console.error('Failed to refresh bank dropdown:', error);
        }
    }

    // Refresh year and month dropdowns after import
    async function refreshYearMonthDropdowns() {
        try {
            // Update the month dropdown if bank and year are selected
            await updateMonthDropdown();
            
            const alert = document.createElement('div');
            alert.className = 'alert alert-info alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3';
            alert.style.zIndex = '9999';
            alert.innerHTML = `
                Import successful! Select bank, year, and month to view transactions.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(alert);
            setTimeout(() => alert.remove(), 3000);
        } catch (error) {
            console.error('Failed to refresh year/month dropdowns:', error);
        }
    }

    // Month names for display
    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 
                        'July', 'August', 'September', 'October', 'November', 'December'];

    // Update month dropdown when bank or year changes
    async function updateMonthDropdown() {
        const bank = bankFilter.value;
        const year = yearFilter.value;

        if (!bank || !year) {
            // Reset to all months if bank or year not selected
            monthFilter.innerHTML = `
                <option value="">Select Month</option>
                ${monthNames.map((name, idx) => `<option value="${idx + 1}">${name}</option>`).join('')}
            `;
            monthFilter.disabled = !bank || !year;
            return;
        }

        try {
            const response = await fetch('{{ route('transactions.available-months') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ bank, year })
            });

            const data = await response.json();

            if (data.success) {
                const currentSelection = monthFilter.value;
                monthFilter.innerHTML = '<option value="">Select Month</option>';
                
                if (data.months.length === 0) {
                    monthFilter.innerHTML += '<option disabled>No data available</option>';
                    monthFilter.disabled = true;
                } else {
                    data.months.forEach(month => {
                        const option = document.createElement('option');
                        option.value = month;
                        option.textContent = monthNames[month - 1];
                        if (month == currentSelection) {
                            option.selected = true;
                        }
                        monthFilter.appendChild(option);
                    });
                    monthFilter.disabled = false;
                }
            }
        } catch (error) {
            console.error('Failed to load available months:', error);
        }
    }

    // Listen for bank and year changes
    bankFilter.addEventListener('change', updateMonthDropdown);
    yearFilter.addEventListener('change', updateMonthDropdown);

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
                currentFilters = { bank, year, month };
                displayTransactions(data.transactions);
                exportDropdown.style.display = 'block';
            } else {
                exportDropdown.style.display = 'none';
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
            const selectedCategory = categories.find(cat => cat.id == transaction.category_id);
            const selectedText = selectedCategory ? selectedCategory.name : 'None';
            
            row.innerHTML = `
                <td>${formatDate(transaction.date)}</td>
                <td title="${transaction.description}">${truncateText(transaction.description, 50)}</td>
                <td>
                    <div class="dropdown searchable-dropdown" data-transaction-id="${transaction.id}">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle w-100 text-start category-display" 
                                type="button" 
                                data-bs-toggle="dropdown" 
                                data-bs-auto-close="outside"
                                aria-expanded="false"
                                data-selected-value="${transaction.category_id || ''}">
                            ${selectedText}
                        </button>
                        <div class="dropdown-menu p-2" style="min-width: 250px;">
                            <input type="text" 
                                   class="form-control form-control-sm mb-2 category-search" 
                                   placeholder="Search categories..." 
                                   autocomplete="off">
                            <div class="category-options" style="max-height: 200px; overflow-y: auto;">
                                <button class="dropdown-item category-option" data-value="">None</button>
                                ${categories.map(cat => `
                                    <button class="dropdown-item category-option ${transaction.category_id == cat.id ? 'active' : ''}" 
                                            data-value="${cat.id}">
                                        ${cat.name}
                                    </button>
                                `).join('')}
                            </div>
                        </div>
                    </div>
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

        // Initialize searchable dropdowns
        initializeSearchableDropdowns();

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

    // Initialize searchable dropdowns functionality
    function initializeSearchableDropdowns() {
        document.querySelectorAll('.searchable-dropdown').forEach(dropdown => {
            const transactionId = dropdown.dataset.transactionId;
            const displayBtn = dropdown.querySelector('.category-display');
            const searchInput = dropdown.querySelector('.category-search');
            const optionsContainer = dropdown.querySelector('.category-options');
            const options = dropdown.querySelectorAll('.category-option');

            // Handle search input
            searchInput.addEventListener('input', (e) => {
                const searchTerm = e.target.value.toLowerCase();
                options.forEach(option => {
                    const text = option.textContent.toLowerCase();
                    if (text.includes(searchTerm)) {
                        option.style.display = 'block';
                    } else {
                        option.style.display = 'none';
                    }
                });
            });

            // Clear search when dropdown opens
            dropdown.addEventListener('show.bs.dropdown', () => {
                searchInput.value = '';
                options.forEach(option => option.style.display = 'block');
                searchInput.focus();
            });

            // Handle option selection
            options.forEach(option => {
                option.addEventListener('click', (e) => {
                    e.preventDefault();
                    const value = option.dataset.value;
                    const text = option.textContent;

                    // Update display
                    displayBtn.textContent = text;
                    displayBtn.dataset.selectedValue = value;

                    // Remove active class from all options
                    options.forEach(opt => opt.classList.remove('active'));
                    // Add active class to selected option
                    option.classList.add('active');

                    // Track change
                    if (!pendingChanges[transactionId]) {
                        pendingChanges[transactionId] = {};
                    }
                    pendingChanges[transactionId].category_id = value || null;
                    saveChangesBtn.style.display = 'block';

                    // Close dropdown
                    try {
                        const bsDropdown = bootstrap.Dropdown.getInstance(displayBtn);
                        if (bsDropdown) {
                            bsDropdown.hide();
                        }
                    } catch (e) {
                        // Fallback: manually close the dropdown
                        dropdown.querySelector('.dropdown-menu').classList.remove('show');
                        displayBtn.classList.remove('show');
                        displayBtn.setAttribute('aria-expanded', 'false');
                    }
                });
            });

            // Prevent dropdown from closing when clicking search input
            searchInput.addEventListener('click', (e) => {
                e.stopPropagation();
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
            setTimeout(() => {
                alert.remove();
                // Refresh the transactions list to show saved changes
                if (bankFilter.value && yearFilter.value && monthFilter.value) {
                    loadBtn.click();
                }
            }, 1500);
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
        return currencySymbol + parseFloat(value).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    }

    function truncateText(text, maxLength) {
        if (text.length <= maxLength) return text;
        return text.substring(0, maxLength) + '...';
    }

    // Import form handling
    const importForm = document.getElementById('importForm');
    const importButton = document.getElementById('importButton');
    const importErrors = document.getElementById('importErrors');
    const importSuccess = document.getElementById('importSuccess');
    const importModal = document.getElementById('importModal');

    importForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        importErrors.classList.add('d-none');
        importSuccess.classList.add('d-none');
        importButton.disabled = true;
        importButton.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Importing...';

        const formData = new FormData(importForm);

        try {
            const response = await fetch('{{ route('transactions.import') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                importSuccess.textContent = data.message;
                importSuccess.classList.remove('d-none');
                importForm.reset();
                
                setTimeout(async () => {
                    bootstrap.Modal.getInstance(importModal).hide();
                    importSuccess.classList.add('d-none');
                    
                    // Refresh year and month dropdowns
                    await refreshYearMonthDropdowns();
                    
                    // Reload transactions if filters are set
                    if (bankFilter.value && yearFilter.value && monthFilter.value) {
                        loadBtn.click();
                    }
                }, 2000);
            } else {
                let errorMessage = data.message;
                if (data.errors && Array.isArray(data.errors)) {
                    errorMessage += '<ul class="mb-0 mt-2">';
                    data.errors.forEach(err => {
                        errorMessage += `<li>${err}</li>`;
                    });
                    errorMessage += '</ul>';
                }
                if (data.required && data.found) {
                    errorMessage += '<div class="mt-2"><strong>Required:</strong> ' + data.required.join(', ') + '</div>';
                    errorMessage += '<div><strong>Found:</strong> ' + data.found.join(', ') + '</div>';
                }
                importErrors.innerHTML = errorMessage;
                importErrors.classList.remove('d-none');
            }
        } catch (error) {
            importErrors.textContent = 'Import failed: ' + error.message;
            importErrors.classList.remove('d-none');
        } finally {
            importButton.disabled = false;
            importButton.innerHTML = '<i class="bi bi-upload"></i> Import';
        }
    });

    // Reset modal on close
    importModal.addEventListener('hidden.bs.modal', () => {
        importForm.reset();
        importErrors.classList.add('d-none');
        importSuccess.classList.add('d-none');
    });

    // Export handlers
    exportExcel.addEventListener('click', (e) => {
        e.preventDefault();
        const url = new URL('{{ route('transactions.export.excel') }}', window.location.origin);
        url.searchParams.append('bank', currentFilters.bank);
        url.searchParams.append('year', currentFilters.year);
        url.searchParams.append('month', currentFilters.month);
        window.location.href = url.toString();
    });

    exportCsv.addEventListener('click', (e) => {
        e.preventDefault();
        const url = new URL('{{ route('transactions.export.csv') }}', window.location.origin);
        url.searchParams.append('bank', currentFilters.bank);
        url.searchParams.append('year', currentFilters.year);
        url.searchParams.append('month', currentFilters.month);
        window.location.href = url.toString();
    });

    exportPdf.addEventListener('click', (e) => {
        e.preventDefault();
        const url = new URL('{{ route('transactions.export.pdf') }}', window.location.origin);
        url.searchParams.append('bank', currentFilters.bank);
        url.searchParams.append('year', currentFilters.year);
        url.searchParams.append('month', currentFilters.month);
        window.location.href = url.toString();
    });
</script>
@endpush
