<p align="center"><a href="https://codenteq.com" target="_blank"><img src="src/Resources/assets/images/stripe.svg" width="288"></a></p>

# Stripe Payment Gateway
[![License](https://poser.pugx.org/codenteq/stripe-payment-gateway/license)](https://github.com/codenteq/stripe-payment-gateway/blob/master/LICENSE)
[![Total Downloads](https://poser.pugx.org/codenteq/stripe-payment-gateway/d/total)](https://packagist.org/packages/codenteq/stripe-payment-gateway)

## 1. Introduction:

Install this package now to receive secure payments in your online store. Stripe offers an easy and secure payment gateway

## 2. Requirements:

* **PHP**: 8.1 or higher.
* **Bagisto**: v2.*
* **Composer**: 1.6.5 or higher.

## 3. Installation:

- Add this following line in composer.json
```
 "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/Whitestork-projects/Bagisto-Stripe-Payment-Gateway"
        }
    ],
```
- then add this line in require in composer.json
```
  "whitestork/bagisto-stripe-payment-gateway": "dev-master"
```

- Run these commands below to complete the setup
```
composer updated
composer dump-autoload
```

- Run these commands below to complete the setup
```
php artisan optimize
```

- Go to the Bagisto admin panel, find the Stripe payment gateway, enter your API key and start receiving payments.
```
http://localhost:8000/admin/configuration/sales/payment_methods
```

## Installation without composer:

- Unzip the respective extension zip and then merge "packages" and "storage" folders into project root directory.
- Goto config/app.php file and add following line under 'providers'

```
Webkul\Stripe\Providers\StripeServiceProvider::class,
```

- Goto composer.json file and add following line under 'psr-4'

```
"Webkul\\Stripe\\": "packages/Webkul/Stripe/src"
```

- Run these commands below to complete the setup

```
composer dump-autoload
```

```
php artisan optimize
```

- Go to the Bagisto admin panel, find the Stripe payment gateway, enter your API key and start receiving payments.
```
http://localhost:8000/admin/configuration/sales/payment_methods
```