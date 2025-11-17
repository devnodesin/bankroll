<nav class="navbar navbar-expand-lg bg-body-tertiary border-bottom">
    <div class="container-fluid">
        <span class="navbar-brand mb-0 h1">{{ config('app.name') }}</span>
        
        <div class="d-flex align-items-center gap-2">
            @auth
                <!-- Import Button -->
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#importModal">
                    <i class="bi bi-upload"></i> Import
                </button>
                
                <!-- Manage Banks Button -->
                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#banksModal">
                    <i class="bi bi-bank"></i> Banks
                </button>
                
                <!-- Manage Categories Button -->
                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#categoriesModal">
                    <i class="bi bi-tags"></i> Categories
                </button>
            @endauth
            
            <!-- Theme Switcher Button -->
            <button class="btn btn-outline-secondary" id="theme-toggle" type="button" title="Toggle theme">
                <i class="bi bi-sun-fill" id="theme-icon"></i>
            </button>
            
            @auth
                <!-- Logout Button -->
                <form method="POST" action="{{ route('logout') }}" class="mb-0">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </button>
                </form>
            @endauth
        </div>
    </div>
</nav>
