<?php
$logFile = __DIR__ . '/logs/gateway.log';

if (!file_exists($logFile)) {
    die("<h2>Log file not found.</h2>");
}

$lines = file($logFile, FILE_IGNORE_NEW_LINES);
$totalRequests = count($lines);
$keyCounts = [];
$statusCounts = [];
$pathCounts = [];

foreach ($lines as $line) {
    if (preg_match('/API Key: (\S+).*Path: (\S+).*Status: (\d+)/', $line, $matches)) {
        $key = $matches[1];
        $path = $matches[2];
        $status = $matches[3];

        $keyCounts[$key] = ($keyCounts[$key] ?? 0) + 1;
        $statusCounts[$status] = ($statusCounts[$status] ?? 0) + 1;
        $pathCounts[$path] = ($pathCounts[$path] ?? 0) + 1;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>API Gateway Log Summary</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f4f4f4; color: #333; }
        h1 { color: #0066cc; }
        .section { background: #fff; padding: 15px 20px; margin-bottom: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        ul { list-style-type: none; padding-left: 0; }
        li { padding: 4px 0; }
    </style>
</head>
<body>

<h1>API Gateway Log Summary</h1>

<div class="section">
    <h2>Total Requests</h2>
    <p><?= $totalRequests ?></p>
</div>

<div class="section">
    <h2>Requests per API Key</h2>
    <ul>
        <?php foreach ($keyCounts as $key => $count): ?>
            <li><strong><?= htmlspecialchars($key) ?>:</strong> <?= $count ?></li>
        <?php endforeach; ?>
    </ul>
</div>

<div class="section">
    <h2>Status Codes</h2>
    <ul>
        <?php foreach ($statusCounts as $status => $count): ?>
            <li><strong><?= $status ?>:</strong> <?= $count ?></li>
        <?php endforeach; ?>
    </ul>
</div>

<div class="section">
    <h2>Most Accessed Paths</h2>
    <ul>
        <?php foreach ($pathCounts as $path => $count): ?>
            <li><strong><?= htmlspecialchars($path) ?>:</strong> <?= $count ?></li>
        <?php endforeach; ?>
    </ul>
</div>

</body>
</html>
