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
                        <button class="om" onclick="document.location='omOss.php'">Om oss</button>
                        <button class="kontakt" onclick="document.location='kontakt.php'">Kontakt</button>
                    </li>
                    <button class="user" onclick="document.location='login.php'">Logga in</button>
                    <button class="signUp" onclick="document.location='signup.php'">Skapa konto</button>
                </ul>
            </form>
        </header>

    <main>
        
        <h1>Den här bildsöknings sidan är gjort av 3st te4 studenter</h1>
        <h1>Vi är Aleksander Sudol, Edwin Lund och Alvin Sandgren</h1>
    </main>
    <footer>
        <a href="omOss.php">Om oss</a>
        <a href="kontakt.php">Kontakt</a>
    </footer>
</body>
</html>