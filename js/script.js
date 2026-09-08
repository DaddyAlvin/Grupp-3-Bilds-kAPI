import { bild } from "./bild.js";

const searchForm = document.getElementById("findUserSearch");
const tagInput = document.getElementById("tagInput");
// Typ random startsida
const tags = ["cats", "dogs", "cars", "nature", "space"];
const randomTag = tags[Math.floor(Math.random() * tags.length)];

const startGallery = new bild("gallery", randomTag);

searchForm.addEventListener("submit", (event) => {
    event.preventDefault();

    const searchTerm = tagInput.value.trim();
    const gallery = new bild("gallery", searchTerm);

    gallery.load();
});


startGallery.load();