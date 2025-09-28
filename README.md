# Mini-wallet readme

1. [Introduction](#introduction)
2. [Installation](#installation)
3. [Installation with Laravel Sail](#installation-with-laravel-sail)
4. [Usage](#usage)

## Introduction

This is an implementation for the Mini wallet technical assignment.
It is built with **Laravel 12 + Inertia 2 + Vue.js 3 + Tailwind 4** stack. Real time events are handled with **Laravel Echo** and **Pusher**.   
Laravel Pint is used for Csfixer. (see rules in `pint.json`)  
Rector is used for code refactoring.   
Phpstan is used for static analysis. (see `phpstan.neon` file for rules)
Pest is used for testing.

## Installation 

### 1. Clone the repository.
### 2. Set you environment variables in a `.env` file based on the `.env.example` file
```bash
    cp .env.example .envnpm
```
   **You need to set up your database and a Pusher account( PUSHER_APP_ID, PUSHER_APP_KEY, PUSHER_APP_SECRET) for the real time events in the `.env` file.**

### 3. To install dependencies and build the project, run the following commands:
```bash
    composer install
    npm install
    npm run dev # for development build
    npm run prod # for production build
```

### 4. Set your app key with the following command:
```bash
    php artisan key:generate
```
### 5. Set up your database and run the migrations:
```bash
    php artisan migrate
```

### 6. In order to process the events for the real-time updates you need to run start the default queue worker:
```bash
  php artisan queue:work
```
## Installation with Laravel Sail

1. Clone the repository as usual.

2. Copy the environment file:
    ```bash
    cp .env.example .env
    ```

3. Update your `.env`:
    - Change your .env values to:
```
      APP_URL=http://localhost:8080
      
      DB_CONNECTION=mysql
      DB_HOST=mysql
      DB_PORT=3306
      DB_DATABASE=mini_wallet
      DB_USERNAME=sail
      DB_PASSWORD=password
```
    - Set up your Pusher account as needed.

4. Start Sail and install dependencies:
```bash
    composer install
    ./vendor/bin/sail sail build --no-cache
    ./vendor/bin/sail up -d
    ./vendor/bin/sail composer install
    ./vendor/bin/sail npm install
    ./vendor/bin/sail npm run dev # or npm run prod
```

5. Generate app key:
```bash
    ./vendor/bin/sail artisan key:generate
```

6. Run database migrations:
```bash
    ./vendor/bin/sail artisan migrate
```

7. (Recommended) Start the queue worker for real-time updates:
```bash
    ./vendor/bin/sail artisan queue:work
```

8. (Optional) Run other development commands:
```bash
    ./vendor/bin/sail npm run eslint
    ./vendor/bin/sail composer lint
    ./vendor/bin/sail composer rector
    ./vendor/bin/sail composer test:types
    ./vendor/bin/sail composer test
```

**Note:** All `php artisan`, `npm`, or `composer` commands should be prefixed with `./vendor/bin/sail` when using Sail.


## Usage
### 0. To start off with the application it is recommended to register a new user.
You can do that by visiting the `/register` route.

### 1. To generate some test data(dummy users), you can run the following command:
```bash
  php artisan db:seed #or ./vendor/bin/sail artisan db:seed
```
**Note: every generated user has the same password: `password`**

### 2. To add balance to a user, you can use the following command `php artisan user:deposit {user_id} {amount}`:
```bash
  php artisan user:deposit 1 100 #or ./vendor/bin/sail artisan user:deposit 1 100
```


## For devs
**Optional:** You can also run following commands:
```bash
npm run eslint # for linting the frontend
composer lint # for phpcsfixer
composer rector # for refactoring the code
composer test:types # for static analysis
```

To run the test suite:
```bash
composer test # for running the tests
```
