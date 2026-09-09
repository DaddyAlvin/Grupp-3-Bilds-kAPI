import { bild } from "./bild.js";

const searchForm = document.getElementById("findUserSearch");
const tagInput = document.getElementById("tagInput");
const tags = JSON.parse(localStorage.getItem("tags") || "[]");
const frontPageTags = ["cats", "dogs", "nature", "technology", "architecture", "food", "travel", "history"];

function getRandomTag(tagList) {
    const randomIndex = Math.floor(Math.random() * tagList.length);
    return tagList[randomIndex];
}

function loadGallery(tag) {
    tagInput.value = tag;
    new bild("gallery", tag).load();
}

searchForm.addEventListener("submit", (event) => {
    event.preventDefault();

    const searchTerm = tagInput.value.trim();
    if (!searchTerm) {
        return;
    }

    tags.push(searchTerm);
    localStorage.setItem("tags", JSON.stringify(tags));
    loadGallery(searchTerm);

});


loadGallery(getRandomTag(frontPageTags));
