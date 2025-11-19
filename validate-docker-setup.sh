#!/bin/bash
# Validation script for Docker setup

echo "Validating Docker setup for Bankroll..."
echo ""

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

errors=0
warnings=0

# Function to print colored messages
print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
    ((warnings++))
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
    ((errors++))
}

# Check if Dockerfile exists
echo "Checking files..."
if [ -f "Dockerfile" ]; then
    print_success "Dockerfile exists"
else
    print_error "Dockerfile not found"
fi

if [ -f "docker-compose.yml" ]; then
    print_success "docker-compose.yml exists"
else
    print_error "docker-compose.yml not found"
fi

if [ -f ".dockerignore" ]; then
    print_success ".dockerignore exists"
else
    print_warning ".dockerignore not found (optional but recommended)"
fi

# Check if src directory exists
echo ""
echo "Checking source files..."
if [ -d "src" ]; then
    print_success "src directory exists"
    
    # Check for critical Laravel files
    if [ -f "src/composer.json" ]; then
        print_success "composer.json exists"
    else
        print_error "src/composer.json not found"
    fi
    
    if [ -f "src/package.json" ]; then
        print_success "package.json exists"
    else
        print_error "src/package.json not found"
    fi
    
    if [ -f "src/artisan" ]; then
        print_success "artisan file exists"
    else
        print_error "src/artisan not found"
    fi
    
    if [ -d "src/public" ]; then
        print_success "public directory exists"
    else
        print_error "src/public directory not found"
    fi
else
    print_error "src directory not found"
fi

# Validate docker-compose.yml syntax
echo ""
echo "Validating docker-compose.yml..."
if command -v docker &> /dev/null; then
    if docker compose config > /dev/null 2>&1; then
        print_success "docker-compose.yml syntax is valid"
    else
        print_error "docker-compose.yml syntax is invalid"
    fi
    
    # Check for APP_KEY placeholder
    if grep -q "YOUR_APP_KEY_HERE" docker-compose.yml; then
        print_warning "APP_KEY not configured (remember to generate one)"
    else
        print_success "APP_KEY appears to be configured"
    fi
else
    print_warning "docker not installed, skipping validation"
fi

# Check Dockerfile basic syntax
echo ""
echo "Checking Dockerfile..."
if [ -s "Dockerfile" ]; then
    if grep -q "FROM.*frankenphp" Dockerfile && grep -q "COPY src/" Dockerfile; then
        print_success "Dockerfile has expected content"
    else
        print_warning "Dockerfile content may be incorrect"
    fi
else
    print_error "Dockerfile is empty"
fi

# Check documentation
echo ""
echo "Checking documentation..."
if [ -f "DEPLOYMENT.md" ]; then
    print_success "DEPLOYMENT.md exists"
else
    print_warning "DEPLOYMENT.md not found"
fi

if [ -f "DOCKER-QUICKSTART.md" ]; then
    print_success "DOCKER-QUICKSTART.md exists"
else
    print_warning "DOCKER-QUICKSTART.md not found"
fi

if [ -f "deploy.sh" ]; then
    print_success "deploy.sh exists"
    if [ -x "deploy.sh" ]; then
        print_success "deploy.sh is executable"
    else
        print_warning "deploy.sh is not executable (run: chmod +x deploy.sh)"
    fi
else
    print_warning "deploy.sh not found"
fi

# Summary
echo ""
echo "================================"
echo "Validation Summary"
echo "================================"
if [ $errors -eq 0 ] && [ $warnings -eq 0 ]; then
    echo -e "${GREEN}✓ All checks passed!${NC}"
    echo ""
    echo "You can now build and deploy:"
    echo "  ./deploy.sh"
    echo ""
    echo "Or manually:"
    echo "  docker-compose build"
    echo "  docker-compose up -d"
    exit 0
elif [ $errors -eq 0 ]; then
    echo -e "${YELLOW}⚠ Validation completed with $warnings warning(s)${NC}"
    echo ""
    echo "You can proceed with deployment, but review the warnings above."
    exit 0
else
    echo -e "${RED}✗ Validation failed with $errors error(s) and $warnings warning(s)${NC}"
    echo ""
    echo "Please fix the errors above before proceeding."
    exit 1
fi
