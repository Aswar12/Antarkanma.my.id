# 🧪 AntarkanMa Testing Guide

> **Created:** 27 Februari 2026  
> **Status:** Testing Foundation  
> **Target Coverage:** 80%

---

## 📋 Daftar Isi

1. [Setup Testing Environment](#setup-testing-environment)
2. [Running Tests](#running-tests)
3. [Test Structure](#test-structure)
4. [Available Tests](#available-tests)
5. [Writing New Tests](#writing-new-tests)
6. [CI/CD Integration](#cicd-integration)

---

## 🛠️ Setup Testing Environment

### 1. Install Dependencies

```bash
cd /path/to/antarkanma

# Pastikan PHPUnit terinstall
composer install --dev

# Verify PHPUnit
./vendor/bin/phpunit --version
```

### 2. Setup Test Database

```bash
# Buat database testing
mysql -u root -p

CREATE DATABASE antarkanma_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

### 3. Configure Environment

```bash
# Copy .env untuk testing
cp .env .env.testing

# Edit .env.testing
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=antarkanma_test
DB_USERNAME=root
DB_PASSWORD=your_password

# Generate app key
php artisan key:generate --env=.env.testing
```

### 4. Run Migrations on Test Database

```bash
php artisan migrate --env=.env.testing
```

### 5. Seed Test Data (Optional)

```bash
php artisan db:seed --env=.env.testing
```

---

## 🏃 Running Tests

### Run All Tests

```bash
# Basic
./vendor/bin/phpunit

# With coverage
./vendor/bin/phpunit --coverage-html=coverage

# Specific test file
./vendor/bin/phpunit tests/Feature/AuthTest.php

# Specific test method
./vendor/bin/phpunit --filter testUserCanLogin
```

### Run Test Groups

```bash
# Auth tests only
./vendor/bin/phpunit --testsuite=Feature --filter Auth

# Order tests only
./vendor/bin/phpunit --testsuite=Feature --filter Order

# API tests only
./vendor/bin/phpunit --testsuite=Feature --filter Api
```

### Run with Options

```bash
# Stop on first failure
./vendor/bin/phpunit --stop-on-failure

# Verbose output
./vendor/bin/phpunit -v

# Testdox format (readable)
./vendor/bin/phpunit --testdox
```

---

## 📁 Test Structure

```
tests/
├── TestCase.php              # Base test case
├── CreatesApplication.php    # Application bootstrap
├── Feature/
│   ├── AuthTest.php          # Authentication tests
│   ├── UserTest.php          # User profile tests
│   ├── OrderTest.php         # Order flow tests
│   ├── MerchantTest.php      # Merchant tests
│   ├── ProductTest.php       # Product tests
│   ├── CourierTest.php       # Courier tests
│   └── TransactionTest.php   # Transaction tests
└── Unit/
    ├── Models/
    │   ├── UserTest.php
    │   ├── OrderTest.php
    │   └── MerchantTest.php
    └── Services/
        ├── OngkirServiceTest.php
        └── FcmServiceTest.php
```

---

## ✅ Available Tests

### Authentication Tests (AuthTest.php)

```php
✅ testUserCanRegister
✅ testUserCannotRegisterWithInvalidEmail
✅ testUserCannotRegisterWithWeakPassword
✅ testUserCanLogin
✅ testUserCannotLoginWithWrongCredentials
✅ testUserCanLogout
✅ testUserCanRefreshToken
✅ testUnauthenticatedUserCannotAccessProtectedEndpoints
```

### User Profile Tests (UserTest.php)

```php
✅ testUserCanGetProfile
✅ testUserCanUpdateProfile
✅ testUserCanUpdateProfilePhoto
✅ testUserCanToggleActiveStatus
✅ testUserCanDeleteAccount
```

### Order Flow Tests (OrderTest.php)

```php
✅ testCustomerCanCreateOrder
✅ testCustomerCanViewOrderHistory
✅ testCustomerCanViewOrderDetail
✅ testMerchantCanApproveOrder
✅ testMerchantCanRejectOrder
✅ testMerchantCanMarkOrderAsReady
✅ testCourierCanAcceptOrder
✅ testCourierCanPickupOrder
✅ testCourierCanCompleteOrder
✅ testOrderStatusTransitions
```

### Courier Flow Tests (CourierTest.php)

```php
✅ testCourierCanSeeAvailableOrders
✅ testCourierCanAcceptTransaction
✅ testCourierCanArriveAtMerchant
✅ testCourierCanPickupOrderFromMerchant
✅ testCourierCanArriveAtCustomer
✅ testCourierCanCompleteDelivery
✅ testCourierCannotAcceptAlreadyAcceptedOrder
```

---

## ✍️ Writing New Tests

### Basic Test Structure

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_example()
    {
        // Arrange
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123')
        ]);

        // Act
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        // Assert
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => ['token', 'user']
                 ]);
    }
}
```

### Testing Authenticated Endpoints

```php
/** @test */
public function test_authenticated_user_can_access_profile()
{
    // Arrange
    $user = User::factory()->create();
    
    // Act
    $response = $this->actingAs($user, 'sanctum')
                     ->getJson('/api/user/profile');
    
    // Assert
    $response->assertStatus(200)
             ->assertJson([
                 'success' => true,
                 'data' => [
                     'email' => $user->email
                 ]
             ]);
}
```

### Testing API Responses

```php
/** @test */
public function test_order_creation()
{
    // Arrange
    $user = User::factory()->create();
    $merchant = Merchant::factory()->create();
    $product = Product::factory()->create(['merchant_id' => $merchant->id]);
    
    $payload = [
        'merchant_id' => $merchant->id,
        'items' => [
            [
                'product_id' => $product->id,
                'quantity' => 2,
            ]
        ],
        'delivery_address' => 'Jl. Test No. 123',
    ];
    
    // Act
    $response = $this->actingAs($user, 'sanctum')
                     ->postJson('/api/orders', $payload);
    
    // Assert
    $response->assertStatus(201)
             ->assertJsonStructure([
                 'success',
                 'data' => [
                     'order_id',
                     'transaction_id',
                     'total_amount'
                 ]
             ]);
}
```

---

## 🔄 CI/CD Integration

### GitHub Actions Workflow

```yaml
# .github/workflows/tests.yml
name: Tests

on:
  push:
    branches: [ main, develop ]
  pull_request:
    branches: [ main, develop ]

jobs:
  test:
    runs-on: ubuntu-latest
    
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: secret
          MYSQL_DATABASE: antarkanma_test
        ports:
          - 3306:3306
        options: --health-cmd="mysqladmin ping" --health-interval=10s --health-timeout=5s --health-retries=3
    
    steps:
    - uses: actions/checkout@v2
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.2'
        extensions: mysql, pdo_mysql
    
    - name: Install Dependencies
      run: composer install --prefer-dist --no-progress
    
    - name: Setup Environment
      run: |
        cp .env.example .env
        php artisan key:generate
        echo "DB_DATABASE=antarkanma_test" >> .env
        echo "DB_USERNAME=root" >> .env
        echo "DB_PASSWORD=secret" >> .env
    
    - name: Run Migrations
      run: php artisan migrate --force
    
    - name: Run Tests
      run: ./vendor/bin/phpunit --coverage-clover=coverage.xml
    
    - name: Upload Coverage
      uses: codecov/codecov-action@v2
      with:
        file: ./coverage.xml
```

---

## 📊 Coverage Targets

| Component | Current | Target | Priority |
|-----------|---------|--------|----------|
| Auth Endpoints | 0% | 80% | 🔴 Critical |
| User Endpoints | 0% | 70% | 🟡 High |
| Order Endpoints | 0% | 90% | 🔴 Critical |
| Courier Endpoints | 0% | 85% | 🔴 Critical |
| Merchant Endpoints | 0% | 70% | 🟡 High |
| Product Endpoints | 0% | 60% | 🟢 Medium |
| Models | 0% | 50% | 🟢 Medium |
| Services | 0% | 60% | 🟡 High |

---

## 🐛 Common Issues & Solutions

### Issue: Database Connection Failed

```bash
Error: SQLSTATE[HY000] [2002] Connection refused
```

**Solution:**
```bash
# Pastikan test database sudah dibuat
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS antarkanma_test"

# Check .env.testing configuration
cat .env.testing | grep DB_
```

### Issue: Sanctum Authentication Not Working

```bash
Expected: 200
Actual: 401 Unauthorized
```

**Solution:**
```php
// Use actingAs with Sanctum guard
$this->actingAs($user, 'sanctum')
     ->getJson('/api/protected-endpoint');
```

### Issue: Factory Not Found

```bash
Error: Class 'Database\Factories\UserFactory' not found
```

**Solution:**
```bash
# Generate factory
php artisan make:factory UserFactory --model=User

# Or use model create directly
$user = User::create([...]);
```

---

## 📚 Resources

- [Laravel Testing Documentation](https://laravel.com/docs/testing)
- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [Laravel Sanctum Testing](https://laravel.com/docs/sanctum#testing)
- [API Testing Best Practices](https://martinfowler.com/articles/api-testing.html)

---

## 🎯 Next Steps

1. ✅ Setup testing environment
2. ✅ Run existing tests
3. ⬜ Write missing tests
4. ⬜ Integrate with CI/CD
5. ⬜ Reach 80% coverage target

---

*Last Updated: 27 Februari 2026*
