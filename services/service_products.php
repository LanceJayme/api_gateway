<?php
$forwardedBy = $GLOBALS['FORWARDED_BY'] ?? 'Unknown';
echo json_encode([
    'source' => $forwardedBy,
    'products' => [
        ["sku" => "A123", "productName" => "Widget"],
        ["sku" => "B456", "productName" => "Gadget"]
    ]
]);
?>