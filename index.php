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
    <title>Bildsökning</title>
    <link rel="icon" type="image/x-icon" href="images/logo.jpg">
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
        <form id="findUserSearch">
            <ul>
                <li class="logo"><a href="/"><img src="images/logo.jpg" alt="Logo"></a></li>
                <li class="searchItem">
                    <div class="searchBox">
                        <button type="submit" class="searchButton" aria-label="Sök">
                            <img src="images/search.png" alt="" class="searchIcon">
                        </button>
                        <input id="tagInput" type="search" placeholder="Sök efter bilder" required>
                    </div>
                </li>
                <button type="button" id="darkmode">☀️</button>
                <li class="navLinks">
                    <a href="omOss.php" class="om">Om oss</a>
                    <a href="kontakt.php" class="kontakt">Kontakt</a>
                    <a href="nyheter.php" class="nyheter">Nyheter</a>
                </li>
                <?php if ($isLoggedIn): ?>
                    <li><span class="user">Inloggad som: <?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?></span></li>
                    <li><a href="db/favourites.php" class="signUp">Mina favoriter</a></li>
                    <li><a href="login/logout.php" class="user">Logga ut</a></li>
                <?php else: ?>
                    <li><a href="login/login.php" class="user">Logga in</a></li>
                    <li><a href="login/register.php" class="signUp">Skapa konto</a></li>
                <?php endif; ?>
            </ul>
        </form>
    </header>

    <main>
        <h1>Klicka på en bild för att se var någonstans på jorden som den tagits</h1>
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

    <footer>
        <a href="omOss.php">Om oss</a>
        <a href="kontakt.php">Kontakt</a>
    </footer>
</body>
</html>
