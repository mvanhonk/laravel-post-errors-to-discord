# Discord Logger for Laravel

This package provides a custom logger for Laravel that sends error logs to a Discord channel.

## Requirements

- Laravel version `11.x` or higher.

## Installation

1. Copy `DiscordLogger.php` to your `app/Logging/` directory.
2. Add the configuration from `logging_config.php` to your `config/logging.php` file.
3. Add the environment variables from `.env.example` to your `.env` file and fill in the values.
4. Install Guzzle if you haven't already: `composer require guzzlehttp/guzzle`

## Usage

Update your exception handler (`app/Exceptions/Handler.php` or `bootstrap/app.php` for Laravel 11+) to use the Discord logger:

```php
use Illuminate\Support\Facades\Log;

// ... other code ...

$this->reportable(function (\Throwable $e) {
    Log::channel('discord')->error($e->getMessage(), ['exception' => $e]);
});
```
within 
```php
->withExceptions(function (Exceptions $exceptions) {
        //place here
    }
```

Now your Laravel application will log errors to Discord.

## Usage
Test before using. 
Repository: [github respository](https://github.com/mvanhonk/laravel-post-errors-to-discord)