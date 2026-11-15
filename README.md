# Wallet Ledger API

A RESTful Wallet Ledger API built with **Laravel 12, PHP 8.2, MySQL 8 and Docker**.

The application provides wallet credit, debit and balance inquiry operations through secure REST API endpoints. It maintains transaction history in an immutable ledger and stores the current wallet balance separately for faster balance retrieval.

---

## 1. Project Overview

The Wallet Ledger API supports the following operations:

- Credit money into a user's wallet.
- Debit money from a user's wallet.
- Retrieve the current wallet balance.
- Authenticate API requests using a Bearer Token.
- Store every successful transaction in the ledger.
- Maintain a separate wallet balance for fast reads.
- Use database transactions to maintain data consistency.

## 2. Technology Stack

| Technology | Purpose |
|---|---|
| PHP 8.2 | Backend programming language |
| Laravel 12 | REST API framework |
| MySQL 8 | Relational database |
| Docker | Application containerization |
| Docker Compose | Container management |
| Laravel Migrations | Database schema management |
| Bearer Token | API authentication |

## 3. Project Structure

Important project directories and files:

```text
walletLedger/
│
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   └── Middleware/
│   │
│   └── Models/
│
├── bootstrap/
│   └── app.php
│
├── database/
│   ├── migrations/
│   └── seeders/
│
├── routes/
│   └── api.php
│
├── .env
├── .env.example
├── Dockerfile
├── docker-compose.yml
├── .dockerignore
└── README.md
```

## 4. Database Design

The application uses MySQL with two main wallet tables.

### 4.1 ledger

The `ledger` table stores individual credit and debit transactions.

| Column | Data Type | Description |
|---|---|---|
| id | BIGINT UNSIGNED | Primary key, auto increment |
| user_id | VARCHAR(64) | Wallet owner identifier |
| type | VARCHAR(10) | Transaction type: credit or debit |
| amount | BIGINT | Transaction amount |
| created_at | TIMESTAMP | Transaction creation time |

**Indexes:**

- PRIMARY KEY (`id`)
- Composite INDEX (`user_id`, `id`)

Index name:

```sql
idx_ledger_user_id_id
```

The composite index helps retrieve transactions belonging to a particular user efficiently.

### 4.2 wallet_accounts

The `wallet_accounts` table stores the current balance of each user.

| Column | Data Type | Description |
|---|---|---|
| id | BIGINT UNSIGNED | Primary key, auto increment |
| user_id | VARCHAR(64) | Unique wallet owner identifier |
| balance | BIGINT | Current wallet balance |
| created_at | TIMESTAMP | Record creation time |
| updated_at | TIMESTAMP | Last update time |

**Indexes:**

- PRIMARY KEY (`id`)
- UNIQUE INDEX (`user_id`)

The unique index ensures that each user has only one wallet account.

### 4.3 Database Design Approach

The application uses two separate tables for different responsibilities:

- `ledger`: Maintains transaction history.
- `wallet_accounts`: Maintains the latest wallet balance.

The ledger is intended to be immutable. Existing transaction records should not be modified or deleted during normal wallet operations.

The wallet balance is maintained separately to avoid calculating the balance by scanning the entire ledger whenever a balance request is received.

## 5. Database Configuration

The project uses XAMPP MySQL for local database management.

Start MySQL from the XAMPP Control Panel.

Create a database named:

```sql
CREATE DATABASE wallet_ledger;
```

Configure the project's `.env` file:

```env
APP_NAME=WalletLedger
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8080

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=wallet_ledger
DB_USERNAME=root
DB_PASSWORD=

API_TOKEN=recruitment-test-token
```

Use your actual database credentials if they are different.

**Note:** Never commit your real `.env` file or production credentials to version control.

## 6. Laravel Database Migrations

Laravel migrations define and manage the database structure.

The project includes migrations for:

- `wallet_accounts`
- `ledger`

Create migrations:

```bash
php artisan make:migration create_wallet_accounts_table

php artisan make:migration create_ledger_table
```

Run migrations locally:

```bash
php artisan migrate
```

Check migration status:

```bash
php artisan migrate:status
```

The current migration files include table-existence checks to avoid recreating tables that already exist in the database.

**Important:** Existing tables are not automatically altered by these checks. If an existing table has a different structure, a separate schema-alteration migration is required.

## 7. Docker Setup

Docker is used to run the Laravel application inside a container.

The current setup uses:

- Laravel application inside Docker.
- XAMPP MySQL running on the Windows host.
- Docker-to-host database connection through `host.docker.internal`.

### 7.1 Prerequisites

Install the following:

- Docker Desktop
- Docker Compose
- XAMPP
- PHP 8.2 or compatible PHP runtime for local development
- Composer

### 7.2 Docker Database Connection

The application container uses the following database settings:

```yaml
DB_CONNECTION: mysql
DB_HOST: host.docker.internal
DB_PORT: 3306
DB_DATABASE: wallet_ledger
DB_USERNAME: root
DB_PASSWORD: ""
```

`host.docker.internal` allows the Docker container to communicate with services running on the Windows host.

### 7.3 Build the Docker Image

Open PowerShell in the project directory:

```powershell
cd E:\xampp\htdocs\walletLedger
```

Build the application:

```powershell
docker compose build app
```

### 7.4 Start the Application

```powershell
docker compose up -d
```

Or rebuild and start:

```powershell
docker compose up -d --build
```

The application is accessible at:

```text
http://localhost:8080
```

### 7.5 Useful Docker Commands

Check running containers:

```powershell
docker compose ps
```

View application logs:

```powershell
docker compose logs --tail=100 app
```

Enter the application container:

```powershell
docker compose exec app sh
```

Run Laravel migrations:

```powershell
docker compose exec app php artisan migrate --force
```

Check migration status:

```powershell
docker compose exec app php artisan migrate:status
```

Stop containers:

```powershell
docker compose down
```

Restart containers:

```powershell
docker compose restart
```

## 8. API Authentication

All API endpoints require Bearer Token authentication.

The token is configured through the environment variable:

```env
API_TOKEN=recruitment-test-token
```

Include the token in the request header:

```http
Authorization: Bearer recruitment-test-token
```

### Authentication Rules

- Requests without a token must return HTTP 401.
- Requests with an incorrect token must return HTTP 401.
- Valid tokens allow the request to continue.
- Token comparison should use constant-time comparison.
- The token must never be hardcoded in application source code.
- The token must not be exposed in logs or API responses.

## 9. API Endpoints

Base URL:

```text
http://localhost:8080/api
```

### 9.1 Credit Wallet

**Endpoint**

```http
POST /api/credit
```

**Headers**

```http
Authorization: Bearer recruitment-test-token
Content-Type: application/json
Accept: application/json
```

**Request Body**

```json
{
    "user_id": "u123",
    "amount": 10000
}
```

**Description**

Adds the specified amount to the user's wallet.

A successful credit operation should:

1. Validate the request.
2. Create or locate the user's wallet account.
3. Add the amount to the wallet balance.
4. Insert a credit transaction into the ledger.
5. Commit both database changes atomically.

### 9.2 Debit Wallet

**Endpoint**

```http
POST /api/debit
```

**Headers**

```http
Authorization: Bearer recruitment-test-token
Content-Type: application/json
Accept: application/json
```

**Request Body**

```json
{
    "user_id": "u123",
    "amount": 2000
}
```

**Description**

Deducts the specified amount from the user's wallet.

A successful debit operation should:

1. Validate the request.
2. Lock or safely update the user's wallet account.
3. Verify that sufficient balance is available.
4. Deduct the requested amount.
5. Insert a debit transaction into the ledger.
6. Commit both changes atomically.

A debit must never result in a negative wallet balance.

### 9.3 Get Wallet Balance

**Endpoint**

```http
GET /api/balance/{user_id}
```

**Example**

```http
GET /api/balance/u123
```

**Headers**

```http
Authorization: Bearer recruitment-test-token
Accept: application/json
```

**Description**

Returns the current balance of the specified user.

The balance should be retrieved from `wallet_accounts` rather than calculating the sum of all ledger entries on every request.

## 10. Testing the API

The following examples use Windows PowerShell with `curl.exe`.

### 10.1 Test Authentication Without Token

```powershell
curl.exe -i http://localhost:8080/api/balance/u123
```

Expected result:

```text
HTTP 401 Unauthorized
```

### 10.2 Test Authentication With Valid Token

```powershell
curl.exe -i `
  -H "Authorization: Bearer recruitment-test-token" `
  -H "Accept: application/json" `
  http://localhost:8080/api/balance/u123
```

### 10.3 Test Credit

```powershell
curl.exe -i -X POST `
  http://localhost:8080/api/credit `
  -H "Authorization: Bearer recruitment-test-token" `
  -H "Content-Type: application/json" `
  -d '{"user_id":"docker_test_001","amount":10000}'
```

### 10.4 Test Debit

```powershell
curl.exe -i -X POST `
  http://localhost:8080/api/debit `
  -H "Authorization: Bearer recruitment-test-token" `
  -H "Content-Type: application/json" `
  -d '{"user_id":"docker_test_001","amount":2000}'
```

### 10.5 Test Balance

```powershell
curl.exe -i `
  -H "Authorization: Bearer recruitment-test-token" `
  http://localhost:8080/api/balance/docker_test_001
```

After a successful credit of 10,000 and debit of 2,000, the expected balance is:

```text
8000
```

Actual response formats and HTTP status codes should be verified against the implemented controllers.

## 11. Data Consistency and Concurrency

Wallet operations must maintain consistency when multiple requests are processed simultaneously.

Important implementation requirements:

- Use database transactions for wallet updates.
- Protect wallet balance updates against concurrent requests.
- Prevent negative balances.
- Ensure ledger entries and wallet balances remain consistent.
- Use row-level locking or atomic conditional updates for debit operations.
- Handle database deadlocks and transaction conflicts safely.
- Keep transactions for different users independent where possible.

The ledger should be treated as the permanent transaction history, while `wallet_accounts.balance` represents the current balance.

## 12. Performance and Database Indexing

The database uses indexes to improve query performance.

Important indexes:

| Table | Index | Purpose |
|---|---|---|
| ledger | PRIMARY (`id`) | Unique transaction identifier |
| ledger | (`user_id`, `id`) | User-specific ledger queries |
| wallet_accounts | PRIMARY (`id`) | Unique wallet identifier |
| wallet_accounts | UNIQUE (`user_id`) | Fast wallet lookup and uniqueness |

The separate wallet balance table avoids full ledger aggregation for ordinary balance requests.

For large datasets, query execution plans should be checked using:

```sql
EXPLAIN
SELECT id, user_id, type, amount, created_at
FROM ledger
WHERE user_id = 'u123'
ORDER BY id DESC
LIMIT 20;
```

Performance should be evaluated with representative data volumes and concurrent requests.

## 13. Troubleshooting

### Database Connection Refused

Check that:

- XAMPP MySQL is running.
- MySQL is listening on port 3306.
- The database `wallet_ledger` exists.
- Docker uses `host.docker.internal` as its database host.
- MySQL allows connections from the Docker environment.

### Migration Errors

Check migration status:

```powershell
docker compose exec app php artisan migrate:status
```

Clear Laravel configuration cache:

```powershell
docker compose exec app php artisan config:clear
```

### Container Logs

```powershell
docker compose logs --tail=100 app
```

### Application Not Accessible

Check container status:

```powershell
docker compose ps
```

Confirm that port 8080 is available and mapped correctly.

## 14. Security Considerations

- API authentication is required for all wallet endpoints.
- Store secrets in environment variables.
- Do not commit `.env` files.
- Validate all incoming API data.
- Prevent unauthorized access to wallet records.
- Use database transactions for financial operations.
- Do not expose sensitive information in error responses.
- Protect transaction history against unauthorized modifications.

## 15. Current Implementation Notes

The current development environment has:

- Laravel application running through Docker.
- MySQL database running through XAMPP.
- `ledger` and `wallet_accounts` tables available.
- Laravel migrations recorded as executed.
- Composite ledger index verified.
- Unique wallet user index verified.
- Sample wallet balance checked against its ledger entries.

Large-scale seed data, automated concurrency testing, load-test measurements, and 10-million-record performance benchmarks should be documented with actual results after those tests have been implemented and executed.

---

## 16. Author

**Project:** Wallet Ledger API

**Framework:** Laravel 12

**Database:** MySQL 8

**Runtime:** Docker

