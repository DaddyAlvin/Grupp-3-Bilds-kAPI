<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="css/style.css">
    <script type="module" src="js/script.js"></script>
</head>
<body>
    <h1>Bildsöksapi site</h1>
    <form id="findUserSearch">
        <label for="tagInput">Sök efter bilder</label>
        <input id="tagInput" type="search" placeholder="Till exempel cats" required>
        <button type="submit">Sök</button>
    </form>
    <div id="gallery"></div>
    <button id="loadImageDog">Ladda ny Hund</button>
    <button id="loadImageCat">Ladda ny katt</button>
    
</body>
</html>