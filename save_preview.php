<?php
session_start();
header('Content-Type: application/json');

// Only allow logged-in users and POST requests
if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false]);
    exit;
}
file_put_contents('preview_debug.log', "Hit endpoint\n", FILE_APPEND);


$data = json_decode(file_get_contents('php://input'), true);
$boardId = intval($data['boardId']);
$previewBase64 = $data['previewImage'];

if (!$boardId || !$previewBase64) {
    echo json_encode(['success' => false]);
    exit;
}

// Decode base64 string
$imgData = base64_decode($previewBase64);
if ($imgData === false) {
    echo json_encode(['success' => false, 'error' => 'Base64 decode failed']);
    exit;
}

// Directory for previews
$dir = __DIR__ . '/previews/';
if (!is_dir($dir)) {
    mkdir($dir, 0777, true);
}

// Save preview for this board
$filename = $dir . 'board_' . $boardId . '_preview.png';
$result = file_put_contents($filename, $imgData);

echo json_encode(['success' => $result !== false]);

