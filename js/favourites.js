import { bild } from "./bild.js";

const gallery = document.getElementById("gallery");
const modalGallery = new bild("gallery", "favoriter", "../api/");
const darkModeButton = document.getElementById("darkmode");
const logoImage = document.querySelector(".logo img");
const downloadImage = document.querySelector(".download");

// Apply the selected theme and keep the toggle button accessible.
function setDarkMode(enabled) {
    document.body.classList.toggle("dark-mode", enabled);
    if (logoImage) {
        logoImage.src = enabled ? "../images/darkModeLogo.png" : "../images/logo.jpg";
    }
        if (downloadImage) {
        downloadImage.src = enabled ? "../images/darkModeDownload.png" : "../images/download.png";
    }
    if (darkModeButton) {
        darkModeButton.textContent = enabled ? "☀️" : "🌙";
        darkModeButton.setAttribute("aria-label", enabled ? "Byt till ljust läge" : "Byt till mörkt läge");
        darkModeButton.setAttribute("aria-pressed", String(enabled));
    }
    localStorage.setItem("darkMode", String(enabled));
}

if (darkModeButton) {
    // Toggle dark mode when the user activates the theme button.
    darkModeButton.addEventListener("click", () => {
        setDarkMode(!document.body.classList.contains("dark-mode"));
    });
}

// Restore the user's saved theme preference when the page loads.
setDarkMode(localStorage.getItem("darkMode") === "true");

// Add keyboard and pointer interaction to each favourite image.
for (const image of gallery.querySelectorAll("img[data-page-id]")) {
    const openFavorite = async () => {
        // Indicate that the image is being opened while the modal loads.
        image.setAttribute("aria-busy", "true");

        // Build the photo object expected by the modal gallery.
        const photo = {
            id: image.dataset.pageId,
            image_url: image.src,
            title: "Favoritbild",
            latitude: Number(image.dataset.latitude),
            longitude: Number(image.dataset.longitude)
        };

        await modalGallery.openModal(photo);
        // Remove the loading state after the modal has opened.
        image.removeAttribute("aria-busy");
    };

    image.addEventListener("click", openFavorite);
    // Support opening favourites with Enter or Space for keyboard users.
    image.addEventListener("keydown", (event) => {
        if (event.key === "Enter" || event.key === " ") {
            event.preventDefault();
            openFavorite();
        }
    });
}
