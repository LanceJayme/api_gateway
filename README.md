# PHP API Gateway Project

This project is a simple API Gateway implemented in PHP. It validates API keys, handles basic rate limiting, routes requests to backend services (`users`, `products`), and logs all requests. Bonus features include a database-backed rate limiter, request transformation, response aggregation, and a log analysis dashboard.

---

## 📦 Setup Instructions

1. Requirements:
   - XAMPP (Apache + MySQL)
   - PHP 7.x or higher
   - Postman or curl for testing

2. **Installation**:
   - Clone or download this repository into your XAMPP `htdocs` folder (e.g., `C:/xampp/htdocs/my_api_gateway`).
   - Start Apache and MySQL in XAMPP.

3. Database Setup:
   - Open phpMyAdmin (`http://localhost/phpmyadmin`)
   - Run the SQL below to create the required tables:

     ```sql
     CREATE DATABASE api_gateway;

     USE api_gateway;

     CREATE TABLE api_keys (
         api_key VARCHAR(255) PRIMARY KEY,
         user_name VARCHAR(100)
     );

     INSERT INTO api_keys (api_key, user_name) VALUES 
         ('key123', 'UserA'),
         ('key456', 'UserB');

     CREATE TABLE rate_limits (
         api_key VARCHAR(255) PRIMARY KEY,
         last_request_ts INT,
         request_count INT
     );
4. Project Structure:
APIGateway/
├── gateway.php
├── docs.html
├── analyze_log.php
├── logs/
│   └── gateway.log
├── services/
│   ├── service_users.php
│   ├── service_products.php
│   └── service_dashboard.php
└── ratelimit_data/


 Valid API Keys

| API Key  | User   |
|----------|--------|
| key123   | UserA  |
| key456   | UserB  |

Postman Collection Setup

🔹 1. GET /api/users
Method: GET
URL: http://localhost/my_api_gateway/api/users
Headers:
Key: X-API-Key
Value: key123

🔹 2. GET /api/products
Method: GET
URL: http://localhost/my_api_gateway/api/products
Headers:
Key: X-API-Key
Value: key123

🔹 3. GET /api/dashboard
Method: GET
URL: http://localhost/my_api_gateway/api/dashboard
Headers:
Key: X-API-Key
Value: key123

🔹 4. GET /api/users (No API Key – Simulate 401)
Method: GET
URL: http://localhost/my_api_gateway/api/users
Headers: none

Expected Response:
{ "error": "Invalid or missing API Key" }

🔹 5. Rate Limit Test
Send the same request (e.g., /api/products) more than 10 times in less than 60 seconds.
After the 11th+ request, 
expect:
{ "error": "Rate limit exceeded" }

Documentation
Visit http://localhost/my_api_gateway/docs.html to view the API documentation.

Log Analysis
Open http://localhost/my_api_gateway/analyze_log.php
View stats: total requests, requests per key, status code counts, and most accessed paths.

Challenges & Assumptions

- Used getallheaders() to retrieve custom headers.

- Switched from file-based to MySQL rate limiting for better scalability.

- Used include to simulate internal API calls for performance during response aggregation.

- Assumed backend services will check the optional X-Forwarded-By header for gateway identification.

Bonus Tasks Completed

✅ MySQL Database for Keys/Rate Limiting

Replaced arrays/files with SQL-based validation and rate tracking.

✅ Log Analysis Script

analyze_log.php summarizes the log file in a browser-friendly format.

✅ Response Aggregation

Added /api/dashboard to combine users and products data into one JSON response.

✅ Request Transformation

Each request to backend services includes the custom header: X-Forwarded-By: MyPHPGateway
