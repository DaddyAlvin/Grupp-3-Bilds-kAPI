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

    <main class="about-page">
        <div class="about-box">
            <h1>Välkommen till världens bästa bildsökningssida!</h1>
            <p>
                Vi är dedikerade till att ge dig den bästa upplevelsen när det gäller att söka och upptäcka bilder online.
                Vårt mål är att göra det enkelt och roligt att hitta precis det du letar efter.
            </p>
            <p>
                Tack för att du besöker vår sida. Vi hoppas att du hittar allt du behöver och att din upplevelse blir både inspirerande och givande.
            </p>
        </div>
        
        <div class="about-box">
            <h2>Vårt team</h2>
            <p>
                Vi är tre studenter som brinner för programmering och bildsökning. Tillsammans arbetar vi för att skapa den bästa möjliga upplevelsen för våra användare.
            </p>
            <ul>
                <li>Edwin - Frontend-utveckling</li>
                <li>Alvin - Backend-utveckling</li>
                <li>Alexsander - Style och design</li>
            </ul>
        </div>
    </main>
    <footer>
        <a href="omOss.php">Om oss</a>
        <a href="kontakt.php">Kontakt</a>
    </footer>
</body>
</html>