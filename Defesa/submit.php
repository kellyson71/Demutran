<?php
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

$file = 'responses.json';

if (!file_exists($file)) {
    file_put_contents($file, json_encode([]));
}

$jsonData = json_decode(file_get_contents($file), true);

$jsonData[] = $data;

file_put_contents($file, json_encode($jsonData, JSON_PRETTY_PRINT));

echo json_encode(['status' => 'success']);
?>
