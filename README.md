# CI4 API Base Controller

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![PHP Version](https://img.shields.io/badge/PHP-8.0%2B-blue.svg)](https://www.php.net)
[![CodeIgniter](https://img.shields.io/badge/CodeIgniter-4.0%2B-red.svg)](https://codeigniter.com)

A lightweight, standardized base controller for building RESTful APIs in CodeIgniter 4.

## Why?

Stop writing the same request handling code in every controller. This base controller automatically:

- ✅ Merges GET, POST, JSON, and file data into one array
- ✅ Returns consistent JSON responses with proper HTTP status codes
- ✅ Handles exceptions gracefully
- ✅ Works with your existing service layer pattern

## Installation

```bash
composer require your-vendor/ci4-api-base
```

## Quick Start

### 1. Create Your Controller

```php
<?php

namespace App\Controllers\Api;

use YourVendor\CI4ApiBase\Controllers\ApiController;
use CodeIgniter\HTTP\ResponseInterface;

class ProductController extends ApiController
{
    protected function getService(): object
    {
        return new \App\Services\ProductService();
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

```php
<?php

namespace App\Services;

class ProductService
{
    public function index(array $data): array
    {
        // Your logic here
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
        // Validation errors? Return them
        if ($errors) {
            return ['errors' => $errors];
        }

        // All good? Return data
        return ['data' => $newProduct];
    }
}
```

### 3. Configure Routes

```php
$routes->group('api', function($routes) {
    $routes->resource('products', ['controller' => 'Api\ProductController']);
});
```

Done! Your API is ready.

## Features

### Automatic Request Data Collection

No more `$this->request->getGet()`, `getPost()`, `getJSON()`... The controller collects everything:

```php
// Handles all of these automatically:
GET  /api/products?search=laptop        // Query params
POST /api/products (JSON body)          // JSON
POST /api/products (form-data)          // Form data
PUT  /api/products/1 (raw input)        // Raw input
```

### Service Layer Integration

Your service receives all data in one clean array:

```php
public function update(array $data): array
{
    // $data contains: route params, query params, JSON body, form data
    // All merged and ready to use
}
```

### Error Handling

Return errors from your service, get proper HTTP responses:

```php
// Service returns errors
return ['errors' => ['name' => 'Required']];

// Controller automatically responds with 400 Bad Request
{
  "errors": {
    "name": "Required"
  }
}
```

Throw exceptions, get proper status codes:

```php
throw new InvalidArgumentException('Not found');
// Returns 400 Bad Request

throw new RuntimeException('Server error');
// Returns 500 Internal Server Error
```

### Helper Methods

```php
return $this->respondCreated(['data' => $product]);        // 201
return $this->respondNoContent();                          // 204
return $this->respondNotFound('Product not found');        // 404
return $this->respondUnauthorized();                       // 401
return $this->respondValidationError($errors);             // 422
```

### File Uploads

```php
public function upload(): ResponseInterface
{
    $fileData = $this->getFileInput('image');
    return $this->handleRequest('upload', $fileData);
}
```

## How It Works

The base controller follows a simple pattern:

1. **Collect** all request data (GET, POST, JSON, files, route params)
2. **Call** your service method with the collected data
3. **Determine** HTTP status based on the result
4. **Respond** with JSON

You implement two abstract methods:

- `getService()`: Return your service instance
- `getSuccessStatus()`: Define success status codes per method

Everything else is automatic.

## Requirements

- PHP 8.0+
- CodeIgniter 4.0+

## Contributing

Contributions welcome! Please see [CONTRIBUTING.md](CONTRIBUTING.md).

## License

MIT License - see [LICENSE](LICENSE) file.

## Support

- Issues: [GitHub Issues](https://github.com/your-vendor/ci4-api-base/issues)
- Discussions: [GitHub Discussions](https://github.com/your-vendor/ci4-api-base/discussions)

---

**Made with ❤️ for the CodeIgniter community**
