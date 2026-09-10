<?php
// Read the current authentication state from the session.
session_start();
$isLoggedIn = !empty($_SESSION['user_id']);
$username = $_SESSION['username'] ?? '';
?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bildsöksapi site</title>
    <link rel="stylesheet" href="css/style.css">
    <script type="module" src="js/script.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
     integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
     crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
     integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
     crossorigin=""></script>
</head>
<body data-logged-in="<?= $isLoggedIn ? 'true' : 'false' ?>">
    <header class="top-bar">
        <h1>Bildsöksapi site</h1>
    </header>

    <main>
        <div class="user-session-container">
            <?php if ($isLoggedIn): ?>
                <div class="user-logged-in">
                    <span>Inloggad som: <strong><?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?></strong></span>
                    <a href="favourites.php" class="nav-button">Mina favoriter</a>
                    <a href="logout.php" class="nav-button logout">Logga ut</a>
                </div>
            <?php else: ?>
                <div class="auth-notice">
                    <p>Du måste ha ett konto för att kunna spara favoritbilder.</p>
                    <a href="login.php" class="nav-button">Logga in</a>
                    <a href="register.php" class="nav-button highlight">Skapa konto</a>
                </div>
            <?php endif; ?>
        </div>

        <form id="findUserSearch">
            <label for="tagInput">Sök efter bilder</label>
            <input id="tagInput" type="search" placeholder="Till exempel cats" required>
            <button type="submit">Sök</button>
        </form>

        <div id="gallery"></div>
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