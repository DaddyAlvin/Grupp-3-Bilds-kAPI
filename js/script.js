import { bild } from "./bild.js";

const searchForm = document.getElementById("findUserSearch");
const tagInput = document.getElementById("tagInput");
const tags = JSON.parse(localStorage.getItem("tags") || "[]");
const frontPageTags = ["cats", "dogs", "nature", "technology", "architecture", "food", "travel", "history"];
const darkModeButton = document.getElementById("darkmode");

let gallery;

// Apply the selected theme and persist the preference for future visits.
function setDarkMode(enabled) {
    document.body.classList.toggle("dark-mode", enabled);
    darkModeButton.textContent = enabled ? "☀️" : "🌙";
    darkModeButton.setAttribute("aria-label", enabled ? "Byt till ljust läge" : "Byt till mörkt läge");
    darkModeButton.setAttribute("aria-pressed", String(enabled));
    localStorage.setItem("darkMode", String(enabled));
}

// Pick one tag at random for the initial gallery view.
function getRandomTag(tagList) {
    const randomIndex = Math.floor(Math.random() * tagList.length);
    return tagList[randomIndex];
}

// Update the search field and load images for the selected tag.
function loadGallery(tag) {
    tagInput.value = tag;
    gallery ??= new bild("gallery", tag);
    gallery.tag = tag;
    gallery.load();
}

searchForm.addEventListener("submit", (event) => {
    event.preventDefault();

    const searchTerm = tagInput.value.trim();
    // Ignore empty searches.
    if (!searchTerm) {
        return;
    }

    tags.push(searchTerm);
    // Remember searched tags in local storage.
    localStorage.setItem("tags", JSON.stringify(tags));
    loadGallery(searchTerm);

});

darkModeButton.addEventListener("click", () => {
    setDarkMode(!document.body.classList.contains("dark-mode"));
});

setDarkMode(localStorage.getItem("darkMode") === "true");
loadGallery(getRandomTag(frontPageTags));