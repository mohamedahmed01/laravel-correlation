# Laravel Correlation ID Middleware

A package to manage correlation IDs for request tracing in Laravel applications.

A correlation ID is a unique identifier assigned to each request entering a distributed system. It helps developers trace requests, debug issues, and identify potential security threats. By attaching a correlation ID to each request, you can track its journey through various services and components, simplifying troubleshooting and monitoring.

---

## 📌 Installation

```sh
composer require mohamedahmed01/laravel-correlation
```

---

## ⚙️ Configuration

### Publish the Config File

```sh
php artisan vendor:publish --tag=correlation-config
```

### Config Options (`config/correlation.php`)

- **`header`**: Header name to use (default: `X-Correlation-ID`)
- **`alternate_headers`**: Additional headers to check for a correlation ID (e.g., `X-Request-ID`, `Trace-ID`)
- **`generator`**: Strategy for generating correlation IDs (`uuid`, `timestamp`, `hash`)
- **`storage`**: Store correlation IDs in `cache`, `session`, or `none`
- **`queue`**: Enable correlation ID propagation in queued jobs (default: `true`)
- **`propagate`**: Automatically include correlation ID in outgoing HTTP requests (default: `true`)
- **`auto_register_middleware`**: Automatically register middleware (default: `true`)

---

## 🚀 Usage

The correlation ID will be:

1. Extracted from incoming requests (from configured headers)
2. Generated if missing (based on configured strategy)
3. Stored in cache (if enabled)
4. Included in all responses
5. Available in logs
6. Passed through queued jobs
7. Propagated in HTTP requests
8. Accessible via helper functions and Blade directives

### Middleware Registration

If `auto_register_middleware` is disabled, manually register the middleware in `app/Http/Kernel.php`:

```php
protected $middleware = [
    \Mohamedahmed01\LaravelCorrelation\Http\Middleware\CorrelationMiddleware::class,
];
```

### Accessing the Correlation ID

#### 📌 In Controllers or Services

```php
$correlationId = correlation_id();
```

#### 📌 In Blade Views

```blade
@correlationId
```

#### 📌 In Jobs (Queued Work)

```php
public function handle()
{
    $correlationId = correlation_id();
    Log::info("Processing job", ['correlation_id' => $correlationId]);
}
```

#### 📌 In Logs

All logs during a request will automatically include the correlation ID:

```json
{
    "message": "User created",
    "context": {
        "correlation_id": "123e4567-e89b-12d3-a456-426614174000"
    }
}
```

### 🌍 HTTP Client Propagation

If `propagate` is enabled, correlation IDs will be automatically included in outgoing HTTP requests:

```php
$response = Http::withCorrelationId()->get('https://api.example.com/data');
```

### 🔧 Artisan Commands

List stored correlation IDs:

```sh
php artisan correlation:list
```

---

## ✅ Testing

Run the test suite to ensure functionality:

```sh
php artisan test
```

---

## 📜 License

MIT License