<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="css/style.css">
    <script type="module" src="js/script.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
     integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
     crossorigin=""/>
     <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
     integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
     crossorigin=""></script>
</head>
<body>
    <h1>Bildsöksapi site</h1>
    <form id="findUserSearch">
        <label for="tagInput">Sök efter bilder</label>
        <input id="tagInput" type="search" placeholder="Till exempel cats" required>
        <button type="submit">Sök</button>
    </form>
    <div id="gallery"></div>

    <dialog id="imageModal" aria-labelledby="modalTitle">
        <div class="modal-content">
            <button class="modal-close" type="button" aria-label="Stäng">&times;</button>
            <div class="modal-image-wrap">
                <img id="modalImage" src="" alt="">
            </div>
            <div class="modal-details">
                <h2 id="modalTitle"></h2>
                <div class="coordinates" aria-label="Bildens koordinater">
                    <span>Latitud: <strong id="modalLatitude"></strong></span>
                    <span>Longitud: <strong id="modalLongitude"></strong></span>
                </div>
                <div class="map-placeholder" aria-label="Plats för karta">
                    <span>Karta kommer senare</span>
                    <div id="map"></div>
                </div>
            </div>
        </div>
    </dialog>

</body>
</html>
