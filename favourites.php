<?php
// Load the database connection and the current user session.
require_once 'db.php';
session_start();

// Favorites are available only to authenticated users.
if (empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$userId = (int)$_SESSION['user_id'];
$username = $_SESSION['username'] ?? '';

// Fetch the current user's favorites, newest first.
$stmt = mysqli_prepare($con, 'SELECT wikimedia_page_id, image_url, created_at FROM likes WHERE user_id = ? ORDER BY created_at DESC');
$favorites = [];

if ($stmt) {
    mysqli_stmt_bind_param($stmt, 'i', $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $favorites[] = $row;
    }
    mysqli_stmt_close($stmt);
}
?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mina favoriter</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header class="top-bar">
        <h1>Mina Favoritbilder</h1>
        <div class="user-session-container">
            <span class="user-welcome">Inloggad som: <strong><?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?></strong></span>
            <a href="index.php" class="nav-button">Tillbaka till sök</a>
            <a href="logout.php" class="nav-button logout">Logga ut</a>
        </div>
    </header>

    <main class="favorites-container">
        <?php if (empty($favorites)): ?>
            <p>Du har inga sparade favoritbilder ännu.</p>
        <?php else: ?>
            <div id="gallery">
                <?php foreach ($favorites as $fav): ?>
                    <div class="favorite-card">
                        <?php if (!empty($fav['image_url'])): ?>
                            <img src="<?= htmlspecialchars($fav['image_url'], ENT_QUOTES, 'UTF-8') ?>" alt="Favoritbild">
                        <?php else: ?>
                            <div class="no-image">Bild-URL saknas</div>
                        <?php endif; ?>
                        <div class="fav-info">
                            <small>ID: <?= htmlspecialchars($fav['wikimedia_page_id'], ENT_QUOTES, 'UTF-8') ?></small>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>