<?php
session_start();
header('Content-Type: application/json');

// Only allow logged-in users and POST requests
if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false]);
    exit;
}

// Include database connection
include 'db.php';

file_put_contents('preview_debug.log', "Hit endpoint\n", FILE_APPEND);

$data = json_decode(file_get_contents('php://input'), true);
$boardId = intval($data['boardId']);
$previewBase64 = $data['previewImage'];
$drawingData = $data['drawingData'] ?? null; // NEW: Get drawing strokes data

if (!$boardId || !$previewBase64) {
    echo json_encode(['success' => false, 'error' => 'Missing board ID or preview image']);
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

// Save preview image for this board
$filename = $dir . 'board_' . $boardId . '_preview.png';
$imageResult = file_put_contents($filename, $imgData);

// NEW: Save drawing data to database if provided
$dbSuccess = true;
if ($drawingData !== null) {
    $drawingDataJson = json_encode($drawingData);
    
    // Check if drawing_data column exists, if not, just save the preview
    $stmt = $conn->prepare("UPDATE drawings SET drawing_data = ? WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("si", $drawingDataJson, $boardId);
        $dbSuccess = $stmt->execute();
        $stmt->close();
        
        file_put_contents('preview_debug.log', "Saved " . count($drawingData) . " strokes to DB\n", FILE_APPEND);
    } else {
        file_put_contents('preview_debug.log', "Failed to prepare statement: " . $conn->error . "\n", FILE_APPEND);
    }
}

$conn->close();

// Return success if image was saved (DB save is optional enhancement)
echo json_encode([
    'success' => $imageResult !== false,
    'strokesSaved' => $drawingData ? count($drawingData) : 0
]);
?>
