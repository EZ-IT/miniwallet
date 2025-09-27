# Mini-wallet readme

1. [Introduction](#introduction)
2. [Installation](#installation)
3. [Usage](#usage)

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
    cp .env.example .env
    ```
   **You need to set up your database and a Pusher account for the real time events in the `.env` file.**

### 3. Set your app key with the following command:
    ```bash
    php artisan key:generate
    ```
### 4. Set up your database and run the migrations:
    ```bash
    php artisan migrate
    ```

### 5. To install dependencies and build the project, run the following commands:
    ```bash
    composer install
    npm install
    npm run dev # for development build
    npm run prod # for production build
    ```
## Usage
[TODO]
