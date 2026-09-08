<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bildsökapi</title>
    <link rel="stylesheet" href="css/style.css">
    <script type="module" src="js/script.js"></script>
</head>
<body>
    <h1>Bildsökapi</h1>

    <label for="tagInput">Sökord</label>
    <input id="tagInput" type="search" placeholder="Sök efter bilder">

    <button id="loadImageDog" type="button">Visa hundbild</button>
    <button id="loadImageCat" type="button">Visa kattbild</button>

    <div id="gallery"></div>
</body>
</html>