import { bild } from "./bild.js";

const loadImageButtonDog = document.getElementById("loadImageDog");
const loadImageButtonCat = document.getElementById("loadImageCat");
const searchForm = document.getElementById("findUserSearch");
const tagInput = document.getElementById("tagInput");

searchForm.addEventListener("submit", (event) => {
    event.preventDefault();

    const searchTerm = tagInput.value.trim();
    const gallery = new bild("gallery", searchTerm);

    gallery.load();
});

loadImageButtonDog.addEventListener("click", () => {
    const doggallery = new bild("gallery", "dogs");

    doggallery.load();

});

loadImageButtonCat.addEventListener("click", () => {
    const catgallery = new bild("gallery", "cats");

    catgallery.load();
    
});