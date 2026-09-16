import { bild } from "./bild.js";

const searchForm = document.getElementById("findUserSearch");
const tagInput = document.getElementById("tagInput");
const tags = JSON.parse(localStorage.getItem("tags") || "[]");
const frontPageTags = ["cats", "dogs", "nature", "technology", "architecture", "food", "travel", "history"];
const darkModeButton = document.getElementById("darkmode");
const logoImage = document.querySelector(".logo img");
const resultHeading = document.getElementById("resultHeading");
const downloadImage = document.querySelector(".download");

let gallery;

// Apply the selected theme and persist the preference for future visits.
function setDarkMode(enabled) {
    document.body.classList.toggle("dark-mode", enabled);
    
    if (logoImage) {
        // Behåll logotypens sökväg oavsett undermapp
        if (logoImage.src.includes("../")) {
            logoImage.src = enabled ? "../images/darkModeLogo.png" : "../images/logo.jpg";
        } else {
            logoImage.src = enabled ? "images/darkModeLogo.png" : "images/logo.jpg";
        }
        
        if (downloadImage) {
        downloadImage.src = enabled ? "../images/darkModeDownload.png" : "../images/download.png";
    }
    }
    
    if (darkModeButton) {
        darkModeButton.textContent = enabled ? "☀️" : "🌙";
        darkModeButton.setAttribute("aria-label", enabled ? "Byt till ljust läge" : "Byt till mörkt läge");
        darkModeButton.setAttribute("aria-pressed", String(enabled));
    }
    
    localStorage.setItem("darkMode", String(enabled));
}

// Pick one tag at random for the initial gallery view.
function getRandomTag(tagList) {
    const randomIndex = Math.floor(Math.random() * tagList.length);
    return tagList[randomIndex];
}

// Update the search field and load images for the selected tag.
function loadGallery(tag) {
    if (tagInput) tagInput.value = tag;
    if (resultHeading) resultHeading.textContent = `${tag}`;

    const galleryElement = document.getElementById("gallery");
    if (!galleryElement) return; // Avbryt om galleriet inte finns på sidan

    gallery ??= new bild("gallery", tag);
    gallery.tag = tag;
    gallery.load();
}

// Säker stängning av sökformuläret (körs bara om formuläret finns)
searchForm?.addEventListener("submit", (event) => {
    event.preventDefault();

    const searchTerm = tagInput?.value.trim();
    if (!searchTerm) {
        return;
    }

    tags.push(searchTerm);
    localStorage.setItem("tags", JSON.stringify(tags));
    loadGallery(searchTerm);
});

// Säker klicklyssnare för darkmode-knappen
darkModeButton?.addEventListener("click", () => {
    setDarkMode(!document.body.classList.contains("dark-mode"));
});

// Initiera darkmode
setDarkMode(localStorage.getItem("darkMode") === "true");

// Ladda bara galleriet om galleriet faktiskt finns på nuvarande sida
if (document.getElementById("gallery")) {
    loadGallery(getRandomTag(frontPageTags));
}