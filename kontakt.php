<?php
session_start();
$isLoggedIn = !empty($_SESSION['user_id']);
$username = $_SESSION['username'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>omOss</title>
    <link rel="icon" type="image/x-icon" href="/images/logo.jpg">
    <link rel="stylesheet" href="css/style.css">
    <script type="module" src="js/script.js"></script>
</head>
<body>
    <header>
            <form id="findUserSearch">
                <ul>
                    <li class= "logo"><a href="/"><img src="images/logo.jpg" alt="Logo"></a></li>            
                <button type="button" id="darkmode">☀️</button>
                    <li class="searchItem">
                        <div class="searchBox">
                            <button type="submit" class="searchButton" aria-label="Search">
                                <img src="images/search.png" alt="" class="searchIcon">
                            </button>
                            <input id="tagInput" type="search" placeholder="Sök efter bilder">
                        </div>
                    </li>
                
                    <li class="navLinks">
                        <a class="om" href="omOss.php">Om oss</a>
                        <a class="kontakt" href="kontakt.php">Kontakt</a>
                    <?php if ($isLoggedIn): ?>
                    <div class="dropdown">
                <button class="dropbtn"><?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?></button>
                <div class="dropdown-content">
                    <a href="db/favourites.php">
                        <span>Mina favoriter</span>
                        <img class="heart" src="images/favourite.png" alt="Favorit">
                    </a>
                    <a href="login/logout.php">
                        <span>Logga ut</span>
                        <img class="logOut" src="images/exit.png" alt="Logga ut">
                    </a>
                </div>
            </div>
                    
                <?php else: ?>
                    <li><a href="login/login.php" class="user">Logga in</a></li>
                    <li><a href="login/register.php" class="signUp">Skapa konto</a></li>
                <?php endif; ?>
            </ul>
        </form>
        </header>

    <main class="contact-page">
        <h1>Kontakta oss</h1>

        <p class="contact-intro">Har du frågor eller vill du komma i kontakt med oss? Vi finns här för dig.</p>

        <div class="contact-grid">
            <div class="contact-card">
                <h2>Edwin</h2>
                <p><strong>E-post:</strong> edwin.lund@elev.ga.ntig.se</p>
                <p><strong>Telefon:</strong> 0705477528</p>
            </div>

            <div class="contact-card">
                <h2>Alvin</h2>
                <p><strong>E-post:</strong> alvin_sandgren@outlook.com</p>
                <p><strong>Telefon:</strong> 070-29 55 107</p>
            </div>

            <div class="contact-card">
                <h2>Alexsander</h2>
                <p><strong>E-post:</strong> alexsander.sudol@elev.ga.ntig.se</p>
                <p><strong>Telefon:</strong> 0700932705</p>
            </div>
        </div>
    </main>
    <footer>
        <a href="omOss.php">Om oss</a>
        <a href="kontakt.php">Kontakt</a>
    </footer>
</body>
</html>