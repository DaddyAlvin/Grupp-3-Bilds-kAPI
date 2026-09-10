<?php
// Enable strict typing and load the shared database connection.
declare(strict_types=1);
require_once __DIR__ . '/../db/db.php';
session_start();

// Every response from this endpoint is JSON.
header('Content-Type: application/json; charset=utf-8');

// Favorites belong to authenticated users and must never be shared between accounts.
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Du måste vara inloggad för att hantera favoriter.']);
    exit;
}

// Read the request body once so both query parameters and JSON input can be supported.
$userId = (int)$_SESSION['user_id'];
$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true) ?? [];

// GET requests check a favorite; POST requests toggle it.
$action = $_GET['action'] ?? $input['action'] ?? 'toggle';
$imageId = trim((string)($_GET['image_id'] ?? $input['image_id'] ?? ''));
$imageUrl = trim((string)($input['image_url'] ?? ''));

// An image identifier is required for both checking and changing a favorite.
if ($imageId === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Bild-ID saknas.']);
    exit;
}

// Return whether the current user has already saved this image.
if ($_SERVER['REQUEST_METHOD'] === 'GET' || $action === 'check') {
    $stmt = mysqli_prepare($con, 'SELECT id FROM likes WHERE user_id = ? AND wikimedia_page_id = ? LIMIT 1');
    mysqli_stmt_bind_param($stmt, 'is', $userId, $imageId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $isLiked = mysqli_num_rows($result) > 0;
    mysqli_stmt_close($stmt);

    echo json_encode(['liked' => $isLiked]);
    exit;
}

// Toggle the favorite for the current user.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = mysqli_prepare($con, 'SELECT id FROM likes WHERE user_id = ? AND wikimedia_page_id = ? LIMIT 1');
    mysqli_stmt_bind_param($stmt, 'is', $userId, $imageId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $existing = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if ($existing) {
        $del = mysqli_prepare($con, 'DELETE FROM likes WHERE id = ?');
        mysqli_stmt_bind_param($del, 'i', $existing['id']);
        mysqli_stmt_execute($del);
        mysqli_stmt_close($del);

        echo json_encode(['liked' => false, 'message' => 'Borttagen från favoriter']);
    } else {
        $ins = mysqli_prepare($con, 'INSERT INTO likes (user_id, wikimedia_page_id, image_url) VALUES (?, ?, ?)');
        mysqli_stmt_bind_param($ins, 'iss', $userId, $imageId, $imageUrl);
        mysqli_stmt_execute($ins);
        mysqli_stmt_close($ins);

        echo json_encode(['liked' => true, 'message' => 'Sparad i favoriter']);
    }
    exit;
}