<?php
// Enable strict typing so PHP performs stricter type handling throughout this
// endpoint. The shared database bootstrap also creates the mysqli connection
// used by all queries below, keeping connection configuration in one place.
declare(strict_types=1);

session_start();
if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? null) !== 'user') {
    http_response_code(401);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Här har du inte att göra.']);
    exit;
}

require_once __DIR__ . '/../db/db.php';

mysqli_report(MYSQLI_REPORT_OFF);

// This endpoint is consumed by JavaScript clients, so every successful response
// and every error response is returned as UTF-8 encoded JSON. Setting the
// header before output prevents clients from interpreting the response as HTML.
header('Content-Type: application/json; charset=utf-8');

// Favorites are private user data. Reject the request before performing any
// database operation when the session does not contain a valid authenticated
// user identifier, ensuring one account can never read or modify another
// account's saved images.
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Du måste vara inloggad för att hantera favoriter.']);
    exit;
}

// Read the request body once because php://input is a stream-like request
// source. Decoding it into an associative array lets POST clients send JSON,
// while the query-string fallbacks below preserve support for URL parameters.
$userId = (int)$_SESSION['user_id'];
$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true) ?? [];

// A GET request, or an explicit "check" action, only reports the current
// favorite state. A POST request changes that state by removing an existing
// favorite or creating one when no matching record exists.
$action = $_GET['action'] ?? $input['action'] ?? 'toggle';
$imageId = trim((string)($_GET['image_id'] ?? $input['image_id'] ?? ''));
$imageUrl = trim((string)($input['image_url'] ?? ''));
$latitude = (float)($input['latitude'] ?? 0);
$longitude = (float)($input['longitude'] ?? 0);

// The Wikimedia page identifier is the stable key used to associate a saved
// image with its owner. Without it, neither a lookup nor a toggle can be
// performed safely, so the request is rejected as invalid input.
if ($imageId === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Bild-ID saknas.']);
    exit;
}

// Check only the authenticated user's record for this image. The prepared
// statement prevents user-supplied values from being interpreted as SQL, and
// LIMIT 1 avoids unnecessary work because the response only needs a boolean.
if ($_SERVER['REQUEST_METHOD'] === 'GET' || $action === 'check') {
    $stmt = mysqli_prepare($con, 'SELECT id FROM likes WHERE user_id = ? AND wikimedia_page_id = ? LIMIT 1');
    if (!$stmt) {
        error_log('Favorite lookup prepare failed: ' . mysqli_error($con));
        http_response_code(500);
        echo json_encode(['error' => 'Favoriten kunde inte hämtas just nu.']);
        exit;
    }
    mysqli_stmt_bind_param($stmt, 'is', $userId, $imageId);
    if (!mysqli_stmt_execute($stmt)) {
        error_log('Favorite lookup failed: ' . mysqli_stmt_error($stmt));
        http_response_code(500);
        echo json_encode(['error' => 'Favoriten kunde inte hämtas just nu.']);
        exit;
    }
    $result = mysqli_stmt_get_result($stmt);
    $isLiked = mysqli_num_rows($result) > 0;
    mysqli_stmt_close($stmt);

    echo json_encode(['liked' => $isLiked]);
    exit;
}

// Toggle the favorite belonging to the authenticated user. The record is
// looked up first so the endpoint can choose between deleting the existing
// favorite and inserting a new one while keeping the response state explicit.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = mysqli_prepare($con, 'SELECT id FROM likes WHERE user_id = ? AND wikimedia_page_id = ? LIMIT 1');
    if (!$stmt) {
        error_log('Favorite fetch prepare failed: ' . mysqli_error($con));
        http_response_code(500);
        echo json_encode(['error' => 'Favoriten kunde inte sparas just nu.']);
        exit;
    }
    mysqli_stmt_bind_param($stmt, 'is', $userId, $imageId);
    if (!mysqli_stmt_execute($stmt)) {
        error_log('Favorite fetch failed: ' . mysqli_stmt_error($stmt));
        http_response_code(500);
        echo json_encode(['error' => 'Favoriten kunde inte sparas just nu.']);
        exit;
    }
    $result = mysqli_stmt_get_result($stmt);
    $existing = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    // An existing row means the image is already saved, so remove only that
    // row. Deleting by its primary key keeps the operation scoped to the
    // exact record found for the current user and image identifier.
    if ($existing) {
        $del = mysqli_prepare($con, 'DELETE FROM likes WHERE id = ?');
        if (!$del) {
            error_log('Favorite delete prepare failed: ' . mysqli_error($con));
            http_response_code(500);
            echo json_encode(['error' => 'Favoriten kunde inte tas bort just nu.']);
            exit;
        }
        mysqli_stmt_bind_param($del, 'i', $existing['id']);
        if (!mysqli_stmt_execute($del)) {
            error_log('Favorite delete failed: ' . mysqli_stmt_error($del));
            http_response_code(500);
            echo json_encode(['error' => 'Favoriten kunde inte tas bort just nu.']);
            exit;
        }
        mysqli_stmt_close($del);

        echo json_encode(['liked' => false, 'message' => 'Borttagen från favoriter']);
    } else {
        // No favorite exists yet, so store the image metadata together with
        // the authenticated owner. The URL and coordinates are retained to
        // allow the client to display the saved image and its location later.
        $ins = mysqli_prepare($con, 'INSERT INTO likes (user_id, wikimedia_page_id, image_url, latitude, longitude) VALUES (?, ?, ?, ?, ?)');
        if (!$ins) {
            error_log('Favorite insert prepare failed: ' . mysqli_error($con));
            http_response_code(500);
            echo json_encode(['error' => 'Favoriten kunde inte sparas just nu.']);
            exit;
        }
        mysqli_stmt_bind_param($ins, 'issdd', $userId, $imageId, $imageUrl, $latitude, $longitude);
        if (!mysqli_stmt_execute($ins)) {
            error_log('Favorite insert failed: ' . mysqli_stmt_error($ins));
            http_response_code(500);
            echo json_encode(['error' => 'Favoriten kunde inte sparas just nu.']);
            exit;
        }
        mysqli_stmt_close($ins);

        echo json_encode(['liked' => true, 'message' => 'Sparad i favoriter']);
    }
    exit;
}