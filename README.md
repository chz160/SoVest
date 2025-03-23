# SoVest - Stock Prediction and Social Investment Platform

## Introduction

SoVest is a Laravel-based web application for stock predictions that allows users to create and share stock predictions while building reputation based on accuracy. The platform serves as a social investment community where users can track their prediction performance and follow top analysts.

## Project Overview

SoVest enables users to:
- Create and share stock predictions (bullish or bearish) with target prices and reasoning
- Vote on predictions from other users
- Build reputation through accurate predictions
- Search for stocks, predictions, and users
- Track prediction accuracy
- View trending predictions and top users

The platform combines social features with investment analysis to create a community-driven stock prediction ecosystem.

## Technology Stack

- **PHP**: 8.2+
- **Framework**: Laravel 12.0+
- **Database**: MySQL/MariaDB (with SQLite support for development)
- **Frontend**: Bootstrap CSS framework
- **API Integration**: Alpha Vantage API for stock data
- **Authentication**: Laravel's built-in authentication system
- **Testing**: Laravel Dusk for browser testing
- **Dependencies**: Managed through Composer

## Installation

### Prerequisites

- PHP 8.2 or higher
- Composer
- MySQL/MariaDB (or SQLite for development)
- Node.js and npm (for frontend assets)
- Git

### Step-by-Step Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/NelsonTH56/SoVest.git
   cd SoVest
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```
3. **Environment Configuration**
   ```bash
   cp .env.example .env
   ```
   
   Edit the `.env` file with your database credentials and Alpha Vantage API key:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=sovest
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   
   ALPHA_VANTAGE_API_KEY=your_api_key
   ```

4. **Generate application key**
   ```bash
   php artisan key:generate
   ```

5. **Run database migrations**
   ```bash
   php artisan migrate
   ```

7. **Seed the database** (optional)
   ```bash
   php artisan db:seed
   ```

8. **Set up storage link** (if needed)
   ```bash
   php artisan storage:link
   ```

9. **Run the application**
   ```bash
   php artisan serve
   ```
   
   The application will now be running at `http://localhost:8000`

## Project Structure

The SoVest application follows the standard Laravel directory structure:

- **`app/`**: Core application code
  - `Console/`: Artisan commands
  - `Http/Controllers/`: Controller classes
  - `Models/`: Eloquent model classes
  - `Services/`: Service classes for business logic
  - `Tasks/`: Background tasks and scheduled jobs
  - `Providers/`: Service providers
- **`bootstrap/`**: Application bootstrap files
- **`config/`**: Configuration files
- **`database/`**: Database migrations and seeders
- **`public/`**: Publicly accessible files
  - `css/`: CSS files including Bootstrap
  - `js/`: JavaScript files
  - `images/`: Image assets
- **`resources/`**: View templates and raw assets
  - `views/`: Blade templates
- **`routes/`**: Route definitions
  - `web.php`: Web routes
  - `console.php`: Console routes
- **`tests/`**: Test files
  - `Browser/`: Dusk browser tests
  - `Feature/`: Feature tests
  - `Unit/`: Unit tests

## Features

### User Authentication
- User registration and login
- Password reset functionality
- User profile management

### Stock Predictions
- Create predictions with target prices
- Set prediction type (bullish/bearish)
- Specify prediction end date
- Add reasoning for predictions
- Edit or delete your predictions

### Social Features
- View trending predictions
- Upvote or downvote predictions
- User reputation system based on prediction accuracy
- View leaderboard of top analysts

### Search Functionality
- Search for stocks, predictions, and users
- Filter search results by type
- Sort results by relevance, date, or other criteria
- Save searches for future reference
- View search history

### Stock Data Management
- Automatic stock price updates
- Historical stock data visualization
- Integration with Alpha Vantage API

## Basic Usage

### Creating a Prediction
1. Log in to your account
2. Navigate to "Create Prediction" page
3. Select a stock by symbol
4. Choose prediction type (bullish/bearish)
5. Set target price and end date
6. Provide reasoning for your prediction
7. Submit your prediction

### Searching
1. Use the search bar at the top of the page
2. Enter a stock symbol, company name, or username
3. Filter results by type (stocks, predictions, users)
4. Sort results as needed
5. Save interesting searches for future reference

### Viewing Your Predictions
1. Navigate to "My Predictions" page
2. View all your active and past predictions
3. Check prediction accuracy and performance
4. Edit or delete existing predictions

### Exploring Trending Content
1. Visit the "Trending" page
2. See popular predictions ranked by votes and accuracy
3. Discover top-performing analysts

## Testing

The application uses Laravel Dusk for browser testing. To run the tests:

1. **Set up a testing database** in `.env.testing`
2. **Install Chrome/Chromedriver** (required for Dusk)
3. **Run the tests**
   ```bash
   php artisan dusk
   ```

For unit and feature tests, run:
```bash
php artisan test
```

## License

This project is licensed under the MIT License - see the LICENSE file for details.