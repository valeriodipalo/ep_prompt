# Required Package Installation

Run these commands to install authentication and payment packages:

```bash
# Laravel Cashier for Stripe integration
composer require laravel/cashier

# Supabase PHP client (alternative to direct HTTP calls)
composer require supabase/supabase-php

# JWT handling for Supabase tokens
composer require firebase/php-jwt

# HTTP client improvements
composer require guzzlehttp/guzzle
```

## Environment Variables to Add

Add these to your `.env` file:

```env
# Supabase Configuration
SUPABASE_URL=your_supabase_url
SUPABASE_ANON_KEY=your_anon_key
SUPABASE_SERVICE_KEY=your_service_role_key

# Stripe Configuration  
STRIPE_KEY=pk_test_your_public_key
STRIPE_SECRET=sk_test_your_secret_key
STRIPE_WEBHOOK_SECRET=whsec_your_webhook_secret

# App Configuration
APP_URL=http://localhost:8001
FRONTEND_URL=http://localhost:8080

# Token Configuration
FREE_TOKENS_PER_USER=10
PREMIUM_TOKEN_COST=1
REGULAR_TOKEN_COST=1
```

## Publish Cashier Configuration

```bash
php artisan vendor:publish --tag="cashier-config"
php artisan vendor:publish --tag="cashier-migrations"
```
