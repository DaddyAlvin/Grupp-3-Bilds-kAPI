<?php
// Load the database connection and the current user session.
require_once __DIR__ . '/db.php';
session_start();

// Favorites are available only to authenticated users.
if (empty($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$userId = (int)$_SESSION['user_id'];
$username = $_SESSION['username'] ?? '';

// Fetch the current user's favorites, newest first.
$stmt = mysqli_prepare($con, 'SELECT wikimedia_page_id, image_url, latitude, longitude, created_at FROM likes WHERE user_id = ? ORDER BY created_at DESC');
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
    <link rel="icon" type="image/x-icon" href="../images/logo.jpg">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script type="module" src="../js/favourites.js"></script>
</head>
<body data-logged-in="true">
    <header class="top-bar">
        <ul>
            <li class="logo"><a href="../index.php"><img src="../images/logo.jpg" alt="Logo"></a></li>
            <li class="navLinks">
                <a href="../omOss.php" class="om">Om oss</a>
                <a href="../foretag.php" class="företag">Företag</a>
                <a href="../nyheter.php" class="nyheter">Nyheter</a>
            </li>
            <li><span class="user">Inloggad som: <?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?></span></li>
            <li><a href="favourites.php" class="signUp">Mina favoriter</a></li>
            <li><a href="../login/logout.php" class="user">Logga ut</a></li>
        </ul>
    </header>

    <main class="favorites-container">
        <h1>Mina favoritbilder</h1>
        <?php if (empty($favorites)): ?>
            <p>Du har inga sparade favoritbilder ännu.</p>
        <?php else: ?>
            <div id="gallery">
                <?php foreach ($favorites as $fav): ?>
                    <div class="favorite-card">
                        <?php if (!empty($fav['image_url'])): ?>
                            <img src="<?= htmlspecialchars($fav['image_url'], ENT_QUOTES, 'UTF-8') ?>"
                                data-page-id="<?= htmlspecialchars($fav['wikimedia_page_id'], ENT_QUOTES, 'UTF-8') ?>"
                                data-latitude="<?= htmlspecialchars((string)$fav['latitude'], ENT_QUOTES, 'UTF-8') ?>"
                                data-longitude="<?= htmlspecialchars((string)$fav['longitude'], ENT_QUOTES, 'UTF-8') ?>"
                                alt="Favoritbild" tabindex="0">
                        <?php else: ?>
                            <div class="no-image">Bild-URL saknas</div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <dialog id="imageModal" aria-labelledby="modalTitle">
        <div class="modal-content">
            <button class="modal-close" type="button" aria-label="Stäng">&times;</button>
            <div class="modal-image-wrap">
                <img id="modalImage" src="" alt="">
            </div>
            <div class="modal-details">
                <h2 id="modalTitle"></h2>
                <div class="like-container">
                    <button id="likeBtn" type="button" class="like-button">
                        <span id="likeIcon">🤍</span> <span id="likeText">Spara som favorit</span>
                    </button>
                    <span id="likeMsg" class="like-msg"></span>
                </div>
                <div class="coordinates" aria-label="Bildens koordinater">
                    <span>Latitud: <strong id="modalLatitude"></strong></span>
                    <span>Longitud: <strong id="modalLongitude"></strong></span>
                </div>
                <div class="map-placeholder" aria-label="Plats för karta">
                    <div id="map"></div>
                </div>
            </div>
        </div>
    </dialog>
</body>
</html>