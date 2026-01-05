# EcoGarden API

REST API for EcoGarden & co - Gardening advice and weather information.

## Requirements

- PHP 8.2+
- Composer
- SQLite

## Installation

```bash
# Install dependencies
composer install

# Generate JWT keys
php bin/console lexik:jwt:generate-keypair

# Create database schema
php bin/console doctrine:schema:create

# Load fixtures (test data)
php bin/console doctrine:fixtures:load --no-interaction
```

## Configuration

Copy `.env` to `.env.local` and configure:

```
# OpenWeatherMap API key (free at https://openweathermap.org/)
OPENWEATHERMAP_API_KEY=your_api_key
```

## Running the Server

```bash
symfony server:start
# or
php -S localhost:8000 -t public
```

## API Routes

### Public Routes

| Method | Route | Description |
|--------|-------|-------------|
| POST | `/api/user` | Create user account |
| POST | `/api/auth` | Authentication (get JWT) |

### Authenticated Routes (ROLE_USER)

| Method | Route | Description |
|--------|-------|-------------|
| GET | `/api/advice` | Current month advices |
| GET | `/api/advice/{month}` | Specific month advices (1-12) |
| GET | `/api/weather` | User's city weather |
| GET | `/api/weather/{city}` | Specific city weather |

### Admin Routes (ROLE_ADMIN)

| Method | Route | Description |
|--------|-------|-------------|
| POST | `/api/advice` | Create advice |
| PUT | `/api/advice/{id}` | Update advice |
| DELETE | `/api/advice/{id}` | Delete advice |
| PUT | `/api/user/{id}` | Update user |
| DELETE | `/api/user/{id}` | Delete user |

## Usage Examples

### Create a user

```bash
curl -X POST http://localhost:8000/api/user \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"secret123","cityName":"Paris"}'
```

### Authenticate

```bash
curl -X POST http://localhost:8000/api/auth \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"secret123"}'
```

### Get advices

```bash
curl http://localhost:8000/api/advice \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```

### Get weather

```bash
curl http://localhost:8000/api/weather/Paris \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```

## Test Accounts (fixtures)

| Email | Password | Role |
|-------|----------|------|
| admin@ecogarden.fr | admin123 | ROLE_ADMIN |
| user@ecogarden.fr | user123 | ROLE_USER |
| gardener@ecogarden.fr | garden123 | ROLE_USER |

## Loading Fixtures

```bash
# Load all fixtures
php bin/console doctrine:fixtures:load --no-interaction

# Load only users
php bin/console doctrine:fixtures:load --group=users --no-interaction

# Load only advices
php bin/console doctrine:fixtures:load --group=advices --no-interaction
```

## HTTP Status Codes

| Code | Description |
|------|-------------|
| 200/201 | Success |
| 400 | Bad request |
| 401 | Not authenticated |
| 403 | Access denied |
| 404 | Resource not found |
| 500 | Server error |
