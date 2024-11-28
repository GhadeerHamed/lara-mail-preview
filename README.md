# lara-mail-preview

# Laravel Mail Preview

A Laravel package that allows you to preview and customize mailables directly from the browser. It scans your Laravel project for mail classes, displays them in a dropdown, and allows you to input constructor parameters. When the fields are filled, the package renders the email template as HTML for preview.

## Features

- Scans the `App/Mail` directory for available mail classes.
- Displays mail classes in a dropdown when visiting the `/mail-preview` route.
- Displays constructor parameters for each selected mail class.
- Automatically detects if a constructor parameter is a **Model** or **Enum**:
    - If it's a **Model** (e.g., `App\Models\User`), a dropdown to select an existing record is generated.
    - If it's an **Enum**, a dropdown with all available enum options is shown.
- Renders the mail's Blade file as HTML once all required parameters are filled and submitted.

## Installation

### Step 1: Install via Composer

Run the following command to install the package via Composer:

```bash
composer require ghadeer/lara-mail-preview