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
    <title>Bildsköning</title>
    <link rel="icon" type="image/x-icon" href="/images/logo.jpg">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
            <form id="findUserSearch">
                <ul>
                    <li class= "logo"><a href="/"><img src="images/logo.jpg" alt="Logo"></a></li>            
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
                    </li>
                    <a class="user" href="login/login.php">Logga in</a>
                    <a class="signUp" href="login/register.php">Skapa konto</a>
                </ul>
            </form>
        </header>

    <main>
        
        <h1>Vällkommen till världens bästa bildsökningssida!</h1>
    </main>
    <footer>
        <a href="omOss.php">Om oss</a>
        <a href="kontakt.php">Kontakt</a>
    </footer>
</body>
</html>