# Installation & Usage Guide

Complete guide for installing and using CI4 API Base Controller in your CodeIgniter 4 projects.

## 📋 Table of Contents

- [Requirements](#requirements)
- [Installation](#installation)
- [Quick Start](#quick-start)
- [Step-by-Step Tutorial](#step-by-step-tutorial)
- [Configuration](#configuration)
- [Advanced Usage](#advanced-usage)
- [Troubleshooting](#troubleshooting)

---

## Requirements

Before installing, ensure your system meets these requirements:

- **PHP**: 8.0 or higher
- **CodeIgniter**: 4.0 or higher
- **Composer**: Latest stable version
- **Extensions**:
  - `intl`
  - `json`
  - `mbstring`

### Verifying Requirements

```bash
# Check PHP version
php -v

# Check required extensions
php -m | grep -E 'intl|json|mbstring'

# Check Composer version
composer --version
```

---

## Installation

### Step 1: Install via Composer

In your CodeIgniter 4 project root, run:

```bash
composer require dcardenasl/ci4-api-base
```

### Step 2: Verify Installation

Check that the package is in your `vendor` directory:

```bash
ls -la vendor/dcardenasl/ci4-api-base
```

You should see the package structure with `src`, `examples`, etc.

---

## Quick Start

### 1. Create Your First API Controller

Create `app/Controllers/Api/ProductController.php`:

```php
<?php

namespace App\Controllers\Api;

use dcardenasl\CI4ApiBase\Controllers\ApiController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Services\ProductService;

class ProductController extends ApiController
{
    private ProductService $service;

    public function __construct()
    {
        $this->service = new ProductService();
    }

    protected function getService(): object
    {
        return $this->service;
    }

    protected function getSuccessStatus(string $method): int
    {
        return match ($method) {
            'store' => ResponseInterface::HTTP_CREATED,
            'destroy' => ResponseInterface::HTTP_NO_CONTENT,
            default => ResponseInterface::HTTP_OK,
        };
    }

    public function index(): ResponseInterface
    {
        return $this->handleRequest('index');
    }

    public function show(int $id): ResponseInterface
    {
        return $this->handleRequest('show', ['id' => $id]);
    }

    public function create(): ResponseInterface
    {
        return $this->handleRequest('store');
    }

    public function update(int $id): ResponseInterface
    {
        return $this->handleRequest('update', ['id' => $id]);
    }

    public function delete(int $id): ResponseInterface
    {
        return $this->handleRequest('destroy', ['id' => $id]);
    }
}
```

### 2. Create Your Service

Create `app/Services/ProductService.php`:

```php
<?php

namespace App\Services;

use App\Models\ProductModel;

class ProductService
{
    private ProductModel $model;

    public function __construct()
    {
        $this->model = new ProductModel();
    }

    public function index(array $data): array
    {
        $products = $this->model->findAll();
        return ['data' => $products];
    }

    public function show(array $data): array
    {
        $product = $this->model->find($data['id']);

        if (!$product) {
            throw new \InvalidArgumentException('Product not found');
        }

        return ['data' => $product];
    }

    public function store(array $data): array
    {
        $id = $this->model->insert($data);
        $product = $this->model->find($id);

        return [
            'message' => 'Product created successfully',
            'data' => $product
        ];
    }

    public function update(array $data): array
    {
        $this->model->update($data['id'], $data);
        $product = $this->model->find($data['id']);

        return [
            'message' => 'Product updated successfully',
            'data' => $product
        ];
    }

    public function destroy(array $data): array
    {
        $this->model->delete($data['id']);
        return ['message' => 'Product deleted successfully'];
    }
}
```

### 3. Configure Routes

Add to `app/Config/Routes.php`:

```php
$routes->group('api', ['namespace' => 'App\Controllers\Api'], function($routes) {
    $routes->get('products', 'ProductController::index');
    $routes->get('products/(:num)', 'ProductController::show/$1');
    $routes->post('products', 'ProductController::create');
    $routes->put('products/(:num)', 'ProductController::update/$1');
    $routes->delete('products/(:num)', 'ProductController::delete/$1');
});
```

### 4. Test Your API

```bash
# List all products
curl http://localhost:8080/api/products

# Get specific product
curl http://localhost:8080/api/products/1

# Create product
curl -X POST http://localhost:8080/api/products \
  -H "Content-Type: application/json" \
  -d '{"name":"Laptop","price":999.99,"stock":10}'

# Update product
curl -X PUT http://localhost:8080/api/products/1 \
  -H "Content-Type: application/json" \
  -d '{"price":899.99}'

# Delete product
curl -X DELETE http://localhost:8080/api/products/1
```

---

## Step-by-Step Tutorial

### Complete CRUD Implementation

#### Step 1: Database Migration

Create `app/Database/Migrations/2025-01-18-000001_CreateProductsTable.php`:

```php
<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProductsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'price' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'stock' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('products');
    }

    public function down()
    {
        $this->forge->dropTable('products');
    }
}
```

Run migration:

```bash
php spark migrate
```

#### Step 2: Create Model

Create `app/Models/ProductModel.php`:

```php
<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductModel extends Model
{
    protected $table = 'products';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'name',
        'description',
        'price',
        'stock',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = false;
    protected $validationRules = [];
    protected $validationMessages = [];
    protected $skipValidation = false;
}
```

#### Step 3: Add Validation (Optional but Recommended)

Create `app/Validations/ProductValidation.php`:

```php
<?php

namespace App\Validations;

use CodeIgniter\Validation\Validation;
use Config\Services;

class ProductValidation
{
    private Validation $validation;
    private array $errors = [];

    public function __construct()
    {
        $this->validation = Services::validation();
    }

    public function validateCreate(array $data): bool
    {
        $rules = [
            'name' => 'required|min_length[3]|max_length[255]',
            'price' => 'required|decimal|greater_than[0]',
            'stock' => 'required|integer|greater_than_equal_to[0]',
        ];

        $this->validation->setRules($rules);

        if (!$this->validation->run($data)) {
            $this->errors = $this->validation->getErrors();
            return false;
        }

        return true;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
```

Update your service to use validation:

```php
public function store(array $data): array
{
    $validation = new \App\Validations\ProductValidation();

    if (!$validation->validateCreate($data)) {
        return ['errors' => $validation->getErrors()];
    }

    // ... rest of the code
}
```

---

## Configuration

### CORS Configuration

To enable CORS for your API, add to `app/Config/Filters.php`:

```php
public $globals = [
    'before' => [
        'cors',
    ],
];

public $filters = [
    'cors' => \App\Filters\Cors::class,
];
```

Create `app/Filters/Cors.php`:

```php
<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class Cors implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');

        if ($request->getMethod() === 'options') {
            die();
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
}
```

---

## Advanced Usage

### File Upload Example

```php
public function uploadImage(): ResponseInterface
{
    $fileData = $this->getFileInput('image');
    return $this->handleRequest('uploadImage', $fileData);
}
```

### Custom Response Helpers

```php
// Return 404
if (!$product) {
    return $this->respondNotFound('Product not found');
}

// Return validation errors
if (!$validation->validate()) {
    return $this->respondValidationError($validation->getErrors());
}

// Return 401
if (!$authenticated) {
    return $this->respondUnauthorized('Invalid credentials');
}
```

### Pagination

```php
public function index(array $data): array
{
    $page = $data['page'] ?? 1;
    $limit = $data['limit'] ?? 10;

    $builder = $this->model->builder();
    $total = $builder->countAllResults(false);
    $products = $builder->get($limit, ($page - 1) * $limit)->getResultArray();

    return [
        'data' => $products,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'pages' => ceil($total / $limit)
        ]
    ];
}
```

---

## Troubleshooting

### Issue: Class not found

**Solution**: Ensure autoload is configured and run:

```bash
composer dump-autoload
```

### Issue: Routes not working

**Solution**: Check that mod_rewrite is enabled and `.htaccess` is present.

### Issue: JSON not being parsed

**Solution**: Ensure request has `Content-Type: application/json` header.

### Issue: File upload fails

**Solution**: Check `php.ini` settings:

```ini
upload_max_filesize = 10M
post_max_size = 10M
```

---

## Next Steps

- Check the `/examples` directory for complete working examples
- Read [CONTRIBUTING.md](CONTRIBUTING.md) if you want to contribute
- Join discussions at [GitHub Discussions](https://github.com/dcardenasl/ci4-api-base/discussions)

---

## Support

Need help?

- 📖 [Documentation](https://github.com/dcardenasl/ci4-api-base/wiki)
- 💬 [Discussions](https://github.com/dcardenasl/ci4-api-base/discussions)
- 🐛 [Issues](https://github.com/dcardenasl/ci4-api-base/issues)
