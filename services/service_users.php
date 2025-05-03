<?php
$forwardedBy = $GLOBALS['FORWARDED_BY'] ?? 'Unknown';
echo json_encode([
    'source' => $forwardedBy,
    'users' => [
        ['id' => 1, 'name' => 'Alice'],
        ['id' => 2, 'name' => 'Bob']
    ]
]);
?>
