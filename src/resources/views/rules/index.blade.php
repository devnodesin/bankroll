@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2><i class="bi bi-funnel"></i> Transaction Rules</h2>
                <a href="{{ route('home') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Home
                </a>
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    <div id="alertContainer"></div>

    <!-- Create Rule Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-plus-circle"></i> Create New Rule</h5>
                </div>
                <div class="card-body">
                    <form id="createRuleForm">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="description_match" class="form-label">Description Match <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="description_match" name="description_match" 
                                       placeholder="e.g., AMAZON, ATM, SALARY" required>
                                <small class="text-muted">Transactions containing this text will match this rule</small>
                            </div>
                            <div class="col-md-3">
                                <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                                <select class="form-select" id="category_id" name="category_id" required>
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="transaction_type" class="form-label">Transaction Type <span class="text-danger">*</span></label>
                                <select class="form-select" id="transaction_type" name="transaction_type" required>
                                    <option value="both">Both (Withdraw & Deposit)</option>
                                    <option value="withdraw">Withdraw Only</option>
                                    <option value="deposit">Deposit Only</option>
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-plus"></i> Create Rule
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Rules List -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-list-ul"></i> Existing Rules</h5>
                </div>
                <div class="card-body">
                    @if($rules->isEmpty())
                        <div class="alert alert-info text-center">
                            <i class="bi bi-info-circle"></i> No rules created yet. Create your first rule above!
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover" id="rulesTable">
                                <thead class="table-secondary">
                                    <tr>
                                        <th>Description Match</th>
                                        <th>Category</th>
                                        <th>Transaction Type</th>
                                        <th>Created</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($rules as $rule)
                                        <tr data-rule-id="{{ $rule->id }}">
                                            <td>
                                                <span class="description-display">{{ $rule->description_match }}</span>
                                                <input type="text" class="form-control form-control-sm description-edit d-none" 
                                                       value="{{ $rule->description_match }}">
                                            </td>
                                            <td>
                                                <span class="category-display">{{ $rule->category->name }}</span>
                                                <select class="form-select form-select-sm category-edit d-none">
                                                    @foreach($categories as $category)
                                                        <option value="{{ $category->id }}" 
                                                                @selected($rule->category_id == $category->id)>
                                                            {{ $category->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <span class="type-display">
                                                    @if($rule->transaction_type === 'both')
                                                        <span class="badge bg-secondary">Both</span>
                                                    @elseif($rule->transaction_type === 'withdraw')
                                                        <span class="badge bg-danger">Withdraw</span>
                                                    @else
                                                        <span class="badge bg-success">Deposit</span>
                                                    @endif
                                                </span>
                                                <select class="form-select form-select-sm type-edit d-none">
                                                    <option value="both" @selected($rule->transaction_type === 'both')>Both</option>
                                                    <option value="withdraw" @selected($rule->transaction_type === 'withdraw')>Withdraw</option>
                                                    <option value="deposit" @selected($rule->transaction_type === 'deposit')>Deposit</option>
                                                </select>
                                            </td>
                                            <td>
                                                <small class="text-muted">{{ $rule->created_at->format('M d, Y') }}</small>
                                            </td>
                                            <td class="text-end">
                                                <div class="btn-group btn-group-sm view-mode">
                                                    <button type="button" class="btn btn-outline-primary btn-edit" title="Edit">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-danger btn-delete" title="Delete">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                                <div class="btn-group btn-group-sm edit-mode d-none">
                                                    <button type="button" class="btn btn-success btn-save" title="Save">
                                                        <i class="bi bi-check"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-secondary btn-cancel" title="Cancel">
                                                        <i class="bi bi-x"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Info Card -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-info">
                <div class="card-body">
                    <h6 class="card-title"><i class="bi bi-info-circle text-info"></i> How Rules Work</h6>
                    <ul class="mb-0">
                        <li>Rules automatically categorize transactions based on description matches</li>
                        <li>Description matching is case-insensitive and searches for the text anywhere in the transaction description</li>
                        <li>You can filter rules by transaction type (Withdraw, Deposit, or Both)</li>
                        <li>Apply rules from the main transaction view using the "Apply Rules" button</li>
                        <li>Choose whether to overwrite existing categories or only fill blank ones when applying rules</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // CSRF Token
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    
    // Show alert message
    function showAlert(message, type = 'success') {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        const container = document.getElementById('alertContainer');
        container.innerHTML = '';
        container.appendChild(alertDiv);
        
        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
        
        // Scroll to top to show alert
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
    
    // Create Rule Form Submit
    document.getElementById('createRuleForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        const data = Object.fromEntries(formData);
        
        const submitBtn = e.target.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Creating...';
        
        try {
            const response = await fetch('{{ route("rules.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            });
            
            const result = await response.json();
            
            if (response.ok) {
                showAlert(result.message, 'success');
                e.target.reset();
                
                // Reload page to show new rule
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                const errors = result.errors || {};
                const errorMessages = Object.values(errors).flat().join('<br>');
                showAlert(errorMessages || result.message || 'Failed to create rule', 'danger');
            }
        } catch (error) {
            showAlert('An error occurred. Please try again.', 'danger');
            console.error('Error:', error);
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    });
    
    // Edit Rule
    document.querySelectorAll('.btn-edit').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const row = e.target.closest('tr');
            row.querySelector('.description-display').classList.add('d-none');
            row.querySelector('.description-edit').classList.remove('d-none');
            row.querySelector('.category-display').classList.add('d-none');
            row.querySelector('.category-edit').classList.remove('d-none');
            row.querySelector('.type-display').classList.add('d-none');
            row.querySelector('.type-edit').classList.remove('d-none');
            row.querySelector('.view-mode').classList.add('d-none');
            row.querySelector('.edit-mode').classList.remove('d-none');
        });
    });
    
    // Cancel Edit
    document.querySelectorAll('.btn-cancel').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const row = e.target.closest('tr');
            row.querySelector('.description-display').classList.remove('d-none');
            row.querySelector('.description-edit').classList.add('d-none');
            row.querySelector('.category-display').classList.remove('d-none');
            row.querySelector('.category-edit').classList.add('d-none');
            row.querySelector('.type-display').classList.remove('d-none');
            row.querySelector('.type-edit').classList.add('d-none');
            row.querySelector('.view-mode').classList.remove('d-none');
            row.querySelector('.edit-mode').classList.add('d-none');
        });
    });
    
    // Save Rule
    document.querySelectorAll('.btn-save').forEach(btn => {
        btn.addEventListener('click', async (e) => {
            const row = e.target.closest('tr');
            const ruleId = row.dataset.ruleId;
            const description = row.querySelector('.description-edit').value;
            const categoryId = row.querySelector('.category-edit').value;
            const transactionType = row.querySelector('.type-edit').value;
            
            const saveBtn = e.target.closest('button');
            const originalContent = saveBtn.innerHTML;
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
            
            try {
                const response = await fetch(`/rules/${ruleId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        description_match: description,
                        category_id: categoryId,
                        transaction_type: transactionType
                    })
                });
                
                const result = await response.json();
                
                if (response.ok) {
                    showAlert(result.message, 'success');
                    
                    // Reload page to show updated rule
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    const errors = result.errors || {};
                    const errorMessages = Object.values(errors).flat().join('<br>');
                    showAlert(errorMessages || result.message || 'Failed to update rule', 'danger');
                }
            } catch (error) {
                showAlert('An error occurred. Please try again.', 'danger');
                console.error('Error:', error);
            } finally {
                saveBtn.disabled = false;
                saveBtn.innerHTML = originalContent;
            }
        });
    });
    
    // Delete Rule
    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', async (e) => {
            if (!confirm('Are you sure you want to delete this rule?')) {
                return;
            }
            
            const row = e.target.closest('tr');
            const ruleId = row.dataset.ruleId;
            
            const deleteBtn = e.target.closest('button');
            const originalContent = deleteBtn.innerHTML;
            deleteBtn.disabled = true;
            deleteBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
            
            try {
                const response = await fetch(`/rules/${ruleId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });
                
                const result = await response.json();
                
                if (response.ok) {
                    showAlert(result.message, 'success');
                    row.remove();
                    
                    // Check if table is empty
                    const tbody = document.querySelector('#rulesTable tbody');
                    if (tbody && tbody.children.length === 0) {
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    }
                } else {
                    showAlert(result.message || 'Failed to delete rule', 'danger');
                }
            } catch (error) {
                showAlert('An error occurred. Please try again.', 'danger');
                console.error('Error:', error);
            } finally {
                deleteBtn.disabled = false;
                deleteBtn.innerHTML = originalContent;
            }
        });
    });
</script>
@endpush
