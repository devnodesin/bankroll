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
    
    .searchable-dropdown .category-option.keyboard-highlighted {
        background-color: var(--bs-primary);
        color: white;
        outline: 2px solid var(--bs-primary);
        outline-offset: -2px;
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
                            <button type="button" class="btn btn-primary" id="applyRules" style="display: none;">
                                <i class="bi bi-funnel"></i> Apply Rules
                            </button>
                            <button type="button" class="btn btn-success ms-2" id="saveChanges" style="display: none;">
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
                                    <th>Category & Notes</th>
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
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel">Import Transactions</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="importForm" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <!-- Step 1: File Selection -->
                        <div id="fileSelectionStep">
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
                                    <strong>Supported formats:</strong><br>
                                    • Standard: Date, Description, Withdraw, Deposit, Balance<br>
                                    • Credit/Debit: Date, Description, Amount, Type (CR/DR), Balance
                                </div>
                            </div>
                        </div>

                        <!-- Step 2: Column Mapping -->
                        <div id="columnMappingStep" class="d-none">
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> Review the sample data below and select the date format used in your file
                            </div>
                            
                            <!-- Preview Table -->
                            <div class="mb-3">
                                <h6>File Preview</h6>
                                <div class="table-responsive" style="max-height: 200px; overflow-y: auto;">
                                    <table class="table table-sm table-bordered" id="previewTable">
                                        <thead class="table-light">
                                            <tr id="previewHeaders"></tr>
                                        </thead>
                                        <tbody id="previewBody"></tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Parser Type Selection -->
                            <div class="mb-3">
                                <label for="parserType" class="form-label">File Format <span class="text-danger">*</span></label>
                                <select class="form-select" id="parserType" name="parser_type" required>
                                    <option value="">Select Format</option>
                                </select>
                                <div class="form-text" id="parserDescription"></div>
                                <div class="alert alert-success mt-2" style="font-size: 0.875rem;">
                                    <i class="bi bi-info-circle"></i> <strong>Note:</strong> No database changes required. All formats map to the same transaction structure internally.
                                </div>
                            </div>

                            <!-- Date Format Selection -->
                            <div class="mb-3">
                                <label for="dateFormat" class="form-label">Date Format <span class="text-danger">*</span></label>
                                <select class="form-select" id="dateFormat" name="date_format" required>
                                    <option value="">Select Date Format</option>
                                    <option value="d/m/Y">DD/MM/YYYY (e.g., 15/03/2024) - European/Indian</option>
                                    <option value="d/m/y">DD/MM/YY (e.g., 15/03/24) - Short Year</option>
                                    <option value="m/d/Y">MM/DD/YYYY (e.g., 03/15/2024) - US Format</option>
                                    <option value="Y-m-d">YYYY-MM-DD (e.g., 2024-03-15) - ISO 8601</option>
                                </select>
                                <div class="form-text">Look at the date values in the preview above and select the matching format.</div>
                            </div>

                            <!-- Column Mappings -->
                            <div class="mb-3">
                                <h6>Column Mappings</h6>
                                <div class="row g-2" id="columnMappingsContainer">
                                    <!-- Will be populated dynamically based on parser type -->
                                </div>
                                <div class="form-text mt-2">
                                    <span class="text-danger">*</span> Required fields
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="backToFileSelection">
                                    <i class="bi bi-arrow-left"></i> Change File
                                </button>
                            </div>
                        </div>

                        <div id="importErrors" class="alert alert-danger d-none mt-3"></div>
                        <div id="importSuccess" class="alert alert-success d-none mt-3"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="previewButton">
                            <i class="bi bi-eye"></i> Preview & Map Columns
                        </button>
                        <button type="submit" class="btn btn-primary d-none" id="importButton">
                            <i class="bi bi-upload"></i> Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Apply Rules Modal -->
    <div class="modal fade" id="applyRulesModal" tabindex="-1" aria-labelledby="applyRulesModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="applyRulesModalLabel">Apply Rules to Transactions</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> Rules will be applied to all transactions in the current view (selected bank, year, and month).
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">How should rules handle existing categories?</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="applyMode" id="fillBlankOnly" value="false" checked>
                            <label class="form-check-label" for="fillBlankOnly">
                                <strong>Fill blank only</strong> - Only apply rules to transactions without a category
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="applyMode" id="overwriteAll" value="true">
                            <label class="form-check-label" for="overwriteAll">
                                <strong>Overwrite all</strong> - Replace existing categories with rule matches
                            </label>
                        </div>
                    </div>
                    
                    <div id="applyRulesErrors" class="alert alert-danger d-none"></div>
                    <div id="applyRulesSuccess" class="alert alert-success d-none"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmApplyRules">
                        <i class="bi bi-check-circle"></i> Apply Rules
                    </button>
                </div>
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

    // Reusable function to refresh filter dropdowns
    async function refreshFilters() {
        try {
            // Refresh bank dropdown
            await refreshBankDropdown();
            // Refresh categories cache
            await refreshCategoriesDropdown();
            // Update month dropdown if bank and year are selected
            if (bankFilter.value && yearFilter.value) {
                await updateMonthDropdown();
            }
        } catch (error) {
            console.error('Failed to refresh filters:', error);
        }
    }

    // Reusable function to refresh transactions table
    async function refreshTransactions() {
        if (bankFilter.value && yearFilter.value && monthFilter.value) {
            loadBtn.click();
        }
    }

    // Reload transactions and dropdowns when modals are closed
    categoriesModal.addEventListener('hidden.bs.modal', async () => {
        await refreshFilters();
        await refreshTransactions();
    });

    banksModal.addEventListener('hidden.bs.modal', async () => {
        await refreshFilters();
        await refreshTransactions();
    });

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
                if (applyRulesBtn) {
                    applyRulesBtn.style.display = 'block';
                }
            } else {
                exportDropdown.style.display = 'none';
                if (applyRulesBtn) {
                    applyRulesBtn.style.display = 'none';
                }
                noDataMessage.classList.remove('d-none');
            }
        } catch (error) {
            loadingSpinner.classList.add('d-none');
            alert('Error loading transactions: ' + error.message);
        }
    });

    // Get category background color class based on prefix
    function getCategoryColorClass(categoryName) {
        if (!categoryName || categoryName === 'None') {
            return '';
        }
        
        if (categoryName.startsWith('EXPENSE:')) {
            return 'text-bg-danger';
        } else if (categoryName.startsWith('INCOME:')) {
            return 'text-bg-success';
        } else if (categoryName.startsWith('TRANSFER:')) {
            return 'text-bg-primary';
        }
        
        return '';
    }

    // Display transactions in table
    function displayTransactions(transactions) {
        transactionsTableBody.innerHTML = '';
        pendingChanges = {}; // Reset pending changes
        saveChangesBtn.style.display = 'none'; // Hide save button
        
        transactions.forEach(transaction => {
            const row = document.createElement('tr');
            const selectedCategory = categories.find(cat => cat.id == transaction.category_id);
            const selectedText = selectedCategory ? selectedCategory.name : 'None';
            const colorClass = getCategoryColorClass(selectedText);
            
            row.innerHTML = `
                <td>${formatDate(transaction.date)}</td>
                <td>${transaction.description}</td>
                <td>
                    <div class="dropdown searchable-dropdown" data-transaction-id="${transaction.id}">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle w-100 text-start category-display ${colorClass}" 
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
                    <input type="text" class="form-control form-control-sm notes-input mt-2" 
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
            
            let highlightedIndex = -1;

            // Handle search input
            searchInput.addEventListener('input', (e) => {
                const searchTerm = e.target.value.toLowerCase();
                highlightedIndex = -1; // Reset highlighted index when searching
                options.forEach(option => {
                    const text = option.textContent.toLowerCase();
                    if (text.includes(searchTerm)) {
                        option.style.display = 'block';
                        option.classList.remove('keyboard-highlighted');
                    } else {
                        option.style.display = 'none';
                        option.classList.remove('keyboard-highlighted');
                    }
                });
            });

            // Clear search when dropdown opens
            dropdown.addEventListener('show.bs.dropdown', () => {
                searchInput.value = '';
                highlightedIndex = -1;
                options.forEach(option => {
                    option.style.display = 'block';
                    option.classList.remove('keyboard-highlighted');
                });
                searchInput.focus();
            });

            // Keyboard navigation for search input
            searchInput.addEventListener('keydown', (e) => {
                const visibleOptions = Array.from(options).filter(opt => opt.style.display !== 'none');
                
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    highlightedIndex = Math.min(highlightedIndex + 1, visibleOptions.length - 1);
                    updateHighlight(visibleOptions);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    highlightedIndex = Math.max(highlightedIndex - 1, -1);
                    updateHighlight(visibleOptions);
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    if (highlightedIndex >= 0 && highlightedIndex < visibleOptions.length) {
                        visibleOptions[highlightedIndex].click();
                    }
                } else if (e.key === 'Escape') {
                    e.preventDefault();
                    // Close dropdown
                    try {
                        const bsDropdown = bootstrap.Dropdown.getInstance(displayBtn);
                        if (bsDropdown) {
                            bsDropdown.hide();
                        }
                    } catch (err) {
                        dropdown.querySelector('.dropdown-menu').classList.remove('show');
                        displayBtn.classList.remove('show');
                        displayBtn.setAttribute('aria-expanded', 'false');
                    }
                }
            });

            // Function to update visual highlight
            function updateHighlight(visibleOptions) {
                // Remove all keyboard highlights
                options.forEach(opt => opt.classList.remove('keyboard-highlighted'));
                
                // Add highlight to current option
                if (highlightedIndex >= 0 && highlightedIndex < visibleOptions.length) {
                    visibleOptions[highlightedIndex].classList.add('keyboard-highlighted');
                    // Scroll into view if needed
                    visibleOptions[highlightedIndex].scrollIntoView({ block: 'nearest', behavior: 'smooth' });
                }
            }

            // Handle option selection
            options.forEach(option => {
                option.addEventListener('click', (e) => {
                    e.preventDefault();
                    const value = option.dataset.value;
                    const text = option.textContent;

                    // Update display
                    displayBtn.textContent = text;
                    displayBtn.dataset.selectedValue = value;
                    
                    // Remove existing color classes
                    displayBtn.classList.remove('bg-danger-subtle', 'bg-success-subtle', 'bg-primary-subtle', 'text-dark');
                    
                    // Add appropriate color class based on category
                    const colorClass = getCategoryColorClass(text);
                    if (colorClass) {
                        const classes = colorClass.split(' ');
                        displayBtn.classList.add(...classes);
                    }

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
            setTimeout(async () => {
                alert.remove();
                // Refresh the transactions list to show saved changes
                await refreshTransactions();
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
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();
        return `${day}/${month}/${year}`;
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
    const previewButton = document.getElementById('previewButton');
    const importErrors = document.getElementById('importErrors');
    const importSuccess = document.getElementById('importSuccess');
    const importModal = document.getElementById('importModal');
    const fileSelectionStep = document.getElementById('fileSelectionStep');
    const columnMappingStep = document.getElementById('columnMappingStep');
    const backToFileSelection = document.getElementById('backToFileSelection');
    
    let previewData = null;
    let columnMappings = {};

    // Preview button handler
    previewButton.addEventListener('click', async () => {
        const bankName = document.getElementById('importBankName').value;
        const fileInput = document.getElementById('importFile');
        
        if (!bankName) {
            alert('Please select a bank');
            return;
        }
        
        if (!fileInput.files[0]) {
            alert('Please select a file');
            return;
        }
        
        importErrors.classList.add('d-none');
        previewButton.disabled = true;
        previewButton.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Loading Preview...';
        
        const formData = new FormData();
        formData.append('file', fileInput.files[0]);
        
        try {
            const response = await fetch('{{ route('transactions.preview') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                previewData = data;
                showColumnMapping(data);
            } else {
                importErrors.innerHTML = data.message;
                importErrors.classList.remove('d-none');
            }
        } catch (error) {
            importErrors.textContent = 'Preview failed: ' + error.message;
            importErrors.classList.remove('d-none');
        } finally {
            previewButton.disabled = false;
            previewButton.innerHTML = '<i class="bi bi-eye"></i> Preview & Map Columns';
        }
    });

    // Show column mapping step
    function showColumnMapping(data) {
        // Hide file selection, show mapping
        fileSelectionStep.classList.add('d-none');
        columnMappingStep.classList.remove('d-none');
        previewButton.classList.add('d-none');
        importButton.classList.remove('d-none');
        
        // Populate parser type dropdown
        const parserTypeSelect = document.getElementById('parserType');
        const parserDescription = document.getElementById('parserDescription');
        parserTypeSelect.innerHTML = '<option value="">Select Format</option>';
        
        if (data.available_parsers) {
            data.available_parsers.forEach(parser => {
                const option = document.createElement('option');
                option.value = parser.id;
                option.textContent = parser.name;
                option.dataset.description = parser.description;
                parserTypeSelect.appendChild(option);
            });
        }
        
        // Set detected parser as selected
        if (data.parser_type) {
            parserTypeSelect.value = data.parser_type;
            const selectedOption = parserTypeSelect.options[parserTypeSelect.selectedIndex];
            parserDescription.textContent = selectedOption.dataset.description || '';
        }
        
        // Listen for parser type changes
        parserTypeSelect.addEventListener('change', () => {
            const selectedOption = parserTypeSelect.options[parserTypeSelect.selectedIndex];
            parserDescription.textContent = selectedOption.dataset.description || '';
            updateColumnMappings(data, parserTypeSelect.value);
        });
        
        // Populate preview table
        const previewHeaders = document.getElementById('previewHeaders');
        const previewBody = document.getElementById('previewBody');
        
        previewHeaders.innerHTML = '';
        data.headers.forEach(header => {
            const th = document.createElement('th');
            th.textContent = header;
            previewHeaders.appendChild(th);
        });
        
        previewBody.innerHTML = '';
        data.preview.forEach(row => {
            const tr = document.createElement('tr');
            row.forEach(cell => {
                const td = document.createElement('td');
                td.textContent = cell || '-';
                tr.appendChild(td);
            });
            previewBody.appendChild(tr);
        });
        
        // Initialize column mappings for the detected parser
        updateColumnMappings(data, data.parser_type);
    }
    
    // Update column mapping fields based on parser type
    function updateColumnMappings(data, parserType) {
        const container = document.getElementById('columnMappingsContainer');
        container.innerHTML = '';
        
        // Get field configurations from the backend data
        let fields = [];
        if (data.available_parsers) {
            const selectedParser = data.available_parsers.find(p => p.id === parserType);
            if (selectedParser && selectedParser.fields) {
                fields = selectedParser.fields;
            }
        }
        
        // Fallback to empty array if no configuration found
        if (fields.length === 0) {
            console.warn('No field configuration found for parser:', parserType);
            return;
        }
        
        fields.forEach(field => {
            const div = document.createElement('div');
            div.className = field.col;
            
            const label = document.createElement('label');
            label.className = 'form-label';
            label.textContent = field.label;
            if (field.required) {
                label.innerHTML += ' <span class="text-danger">*</span>';
            }
            
            const select = document.createElement('select');
            select.className = 'form-select form-select-sm';
            select.id = `map${field.key.charAt(0).toUpperCase() + field.key.slice(1)}`;
            select.name = `map_${field.key}`;
            if (field.required) {
                select.required = true;
            }
            
            // Add default option
            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.textContent = '-- Select Column --';
            select.appendChild(defaultOption);
            
            // Add column options
            data.headers.forEach((header, index) => {
                const option = document.createElement('option');
                option.value = index;
                option.textContent = header;
                select.appendChild(option);
            });
            
            // Set auto-detected mapping
            if (data.mappings[field.key] !== null && data.mappings[field.key] !== undefined) {
                select.value = data.mappings[field.key];
            }
            
            div.appendChild(label);
            div.appendChild(select);
            container.appendChild(div);
        });
    }

    // Back to file selection
    backToFileSelection.addEventListener('click', () => {
        columnMappingStep.classList.add('d-none');
        fileSelectionStep.classList.remove('d-none');
        importButton.classList.add('d-none');
        previewButton.classList.remove('d-none');
        importErrors.classList.add('d-none');
        document.getElementById('dateFormat').value = ''; // Reset date format selection
    });

    // Import form submission
    importForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        importErrors.classList.add('d-none');
        importSuccess.classList.add('d-none');
        importButton.disabled = true;
        importButton.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Importing...';

        // Validate parser type is selected
        const parserType = document.getElementById('parserType').value;
        if (!parserType) {
            importErrors.textContent = 'Please select a file format';
            importErrors.classList.remove('d-none');
            importButton.disabled = false;
            importButton.innerHTML = '<i class="bi bi-upload"></i> Import';
            return;
        }
        
        // Validate date format is selected
        const dateFormat = document.getElementById('dateFormat').value;
        if (!dateFormat) {
            importErrors.textContent = 'Please select a date format';
            importErrors.classList.remove('d-none');
            importButton.disabled = false;
            importButton.innerHTML = '<i class="bi bi-upload"></i> Import';
            return;
        }

        // Get column mappings dynamically from all map fields
        columnMappings = {};
        const mappingContainer = document.getElementById('columnMappingsContainer');
        const mappingSelects = mappingContainer.querySelectorAll('select');
        
        mappingSelects.forEach(select => {
            // Extract field name from select id (e.g., mapDate -> date)
            const fieldName = select.id.replace('map', '').toLowerCase();
            const value = select.value;
            columnMappings[fieldName] = value !== '' ? parseInt(value) : null;
        });
        
        // Get required fields from the selected parser's configuration
        let requiredFields = [];
        if (previewData && previewData.available_parsers) {
            const selectedParser = previewData.available_parsers.find(p => p.id === parserType);
            if (selectedParser && selectedParser.required_fields) {
                requiredFields = selectedParser.required_fields;
            }
        }
        
        // Validate that all required fields are mapped
        const missingFields = [];
        requiredFields.forEach(field => {
            if (columnMappings[field] === null || columnMappings[field] === undefined) {
                missingFields.push(field);
            }
        });
        
        if (missingFields.length > 0) {
            importErrors.textContent = 'Please map all required fields (marked with *): ' + missingFields.join(', ');
            importErrors.classList.remove('d-none');
            importButton.disabled = false;
            importButton.innerHTML = '<i class="bi bi-upload"></i> Import';
            return;
        }

        const formData = new FormData(importForm);
        formData.append('column_mappings', JSON.stringify(columnMappings));

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
                
                setTimeout(() => {
                    const modal = bootstrap.Modal.getInstance(importModal);
                    if (modal) {
                        // Add a one-time event listener to reload the page after the modal is hidden
                        importModal.addEventListener('hidden.bs.modal', () => {
                            location.reload();
                        }, { once: true });
                        modal.hide();
                    } else {
                        // Fallback if modal instance isn't found
                        location.reload();
                    }
                }, 1500); // Wait 1.5 seconds to allow user to read success message
            } else {
                let errorMessage = data.message;
                if (data.errors && Array.isArray(data.errors)) {
                    errorMessage += '<ul class="mb-0 mt-2">';
                    data.errors.forEach(err => {
                        errorMessage += `<li>${err}</li>`;
                    });
                    errorMessage += '</ul>';
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
        fileSelectionStep.classList.remove('d-none');
        columnMappingStep.classList.add('d-none');
        previewButton.classList.remove('d-none');
        importButton.classList.add('d-none');
        previewData = null;
        columnMappings = {};
        document.getElementById('dateFormat').value = '';
        document.getElementById('parserType').value = '';
        document.getElementById('columnMappingsContainer').innerHTML = '';
    });

    // Apply Rules functionality
    const applyRulesBtn = document.getElementById('applyRules');
    const applyRulesModal = new bootstrap.Modal(document.getElementById('applyRulesModal'));
    const confirmApplyRulesBtn = document.getElementById('confirmApplyRules');
    const applyRulesErrors = document.getElementById('applyRulesErrors');
    const applyRulesSuccess = document.getElementById('applyRulesSuccess');
    
    if (applyRulesBtn) {
        applyRulesBtn.addEventListener('click', () => {
            // Clear previous messages
            applyRulesErrors.classList.add('d-none');
            applyRulesSuccess.classList.add('d-none');
            
            // Show modal
            applyRulesModal.show();
        });
    }
    
    if (confirmApplyRulesBtn) {
        confirmApplyRulesBtn.addEventListener('click', async () => {
            const overwrite = document.querySelector('input[name="applyMode"]:checked').value === 'true';
            
            // Clear previous messages
            applyRulesErrors.classList.add('d-none');
            applyRulesSuccess.classList.add('d-none');
            
            // Disable button and show loading
            confirmApplyRulesBtn.disabled = true;
            confirmApplyRulesBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Applying...';
            
            try {
                const response = await fetch('{{ route('rules.apply') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        bank: currentFilters.bank,
                        year: currentFilters.year,
                        month: currentFilters.month,
                        overwrite: overwrite
                    })
                });
                
                const result = await response.json();
                
                if (response.ok && result.success) {
                    applyRulesSuccess.textContent = result.message;
                    applyRulesSuccess.classList.remove('d-none');
                    
                    // Reload transactions and filters to show updated categories
                    setTimeout(async () => {
                        applyRulesModal.hide();
                        await refreshFilters();
                        await refreshTransactions();
                    }, 2000);
                } else {
                    applyRulesErrors.textContent = result.message || 'Failed to apply rules';
                    applyRulesErrors.classList.remove('d-none');
                }
            } catch (error) {
                applyRulesErrors.textContent = 'An error occurred. Please try again.';
                applyRulesErrors.classList.remove('d-none');
                console.error('Error:', error);
            } finally {
                confirmApplyRulesBtn.disabled = false;
                confirmApplyRulesBtn.innerHTML = '<i class="bi bi-check-circle"></i> Apply Rules';
            }
        });
    }

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
