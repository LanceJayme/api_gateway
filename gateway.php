<?php

// Connect to MySQL database
$host = 'localhost';
$dbname = 'api_gateway';  // change if your DB name is different
$username = 'root';       // default in XAMPP
$password = '';           // default is empty in XAMPP

$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
    exit;
}

header('Content-Type: application/json');

// 1. Get requested API path (e.g., 'users' or 'products')
$requestPath = $_GET['request_path'] ?? '';

// 2. Retrieve API key from custom header
$headers = getallheaders();
$apiKey = $headers['X-API-Key'] ?? null;

// 3. Define valid API keys
if (!$apiKey) {
    http_response_code(401);
    echo json_encode(["error" => "Missing API Key"]);
    logRequest($apiKey, $requestPath, 401);
    exit;
}

// Check if API key exists in the database
$stmt = $conn->prepare("SELECT user_name FROM api_keys WHERE api_key = ?");
$stmt->bind_param("s", $apiKey);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(401);
    echo json_encode(["error" => "Invalid API Key"]);
    logRequest($apiKey, $requestPath, 401);
    exit;
}

$user = $result->fetch_assoc();

// 5. Rate limiting setup
$limit = 10;
$window = 60;
$now = time();

// Check for existing rate limit entry
$stmt = $conn->prepare("SELECT last_request_ts, request_count FROM rate_limits WHERE api_key = ?");
$stmt->bind_param("s", $apiKey);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();
    $elapsed = $now - $row['last_request_ts'];

    if ($elapsed < $window) {
        if ($row['request_count'] >= $limit) {
            http_response_code(429);
            echo json_encode(["error" => "Rate limit exceeded"]);
            logRequest($apiKey, $requestPath, 429);
            exit;
        } else {
            // Increment request count
            $newCount = $row['request_count'] + 1;
            $stmt = $conn->prepare("UPDATE rate_limits SET request_count = ?, last_request_ts = ? WHERE api_key = ?");
            $stmt->bind_param("iis", $newCount, $now, $apiKey);
            $stmt->execute();
        }
    } else {
        // Reset window
        $stmt = $conn->prepare("UPDATE rate_limits SET request_count = 1, last_request_ts = ? WHERE api_key = ?");
        $stmt->bind_param("is", $now, $apiKey);
        $stmt->execute();
    }
} else {
    // First time this key is being used
    $stmt = $conn->prepare("INSERT INTO rate_limits (api_key, last_request_ts, request_count) VALUES (?, ?, 1)");
    $stmt->bind_param("si", $apiKey, $now);
    $stmt->execute();
}

$GLOBALS['FORWARDED_BY'] = 'MyPHPGateway';

// 7. Route to the appropriate service
switch ($requestPath) {
    case 'users':
        include 'services/service_users.php';
        logRequest($apiKey, $requestPath, 200);
        break;
    case 'products':
        include 'services/service_products.php';
        logRequest($apiKey, $requestPath, 200);
        break;
    case 'dashboard':
        include 'services/service_dashboard.php';
        logRequest($apiKey, $requestPath, 200);
        break;
    default:
        http_response_code(404);
        echo json_encode(["error" => "Unknown endpoint"]);
        logRequest($apiKey, $requestPath, 404);
        break;
}


// 8. Request Logging
function logRequest($key, $path, $status) {
    $log = "[" . date("Y-m-d H:i:s") . "] - IP: " . $_SERVER['REMOTE_ADDR'] .
           " - API Key: " . ($key ?: 'None') .
           " - Path: " . $path .
           " - Status: " . $status . "\n";
    file_put_contents(__DIR__ . '/logs/gateway.log', $log, FILE_APPEND);
}
