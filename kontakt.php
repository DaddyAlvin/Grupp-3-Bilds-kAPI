<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Om oss</title>
    <link rel="icon" type="image/x-icon" href="/images/logo.jpg">
    <link rel="stylesheet" href="css/style.css">
    <script type="module" src="js/script.js"></script>
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
                    <button type="button" id="darkmode">☀️</button>
                
                    <li class="navLinks">
                        <button class="om" onclick="document.location='omOss.php'">Om oss</button>
                        <button class="kontakt" onclick="document.location='kontakt.php'">Kontakt</button>
                    </li>
                    <button class="user" onclick="document.location='login.php'">Logga in</button>
                    <button class="signUp" onclick="document.location='signup.php'">Skapa konto</button>
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