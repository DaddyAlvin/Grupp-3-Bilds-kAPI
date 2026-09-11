import { bild } from "./bild.js";

const gallery = document.getElementById("gallery");
const modalGallery = new bild("gallery", "favoriter", "../api/");
const darkModeButton = document.getElementById("darkmode");

function setDarkMode(enabled) {
    document.body.classList.toggle("dark-mode", enabled);
    if (darkModeButton) {
        darkModeButton.textContent = enabled ? "☀️" : "🌙";
        darkModeButton.setAttribute("aria-label", enabled ? "Byt till ljust läge" : "Byt till mörkt läge");
        darkModeButton.setAttribute("aria-pressed", String(enabled));
    }
    localStorage.setItem("darkMode", String(enabled));
}

if (darkModeButton) {
    darkModeButton.addEventListener("click", () => {
        setDarkMode(!document.body.classList.contains("dark-mode"));
    });
}

setDarkMode(localStorage.getItem("darkMode") === "true");

for (const image of gallery.querySelectorAll("img[data-page-id]")) {
    const openFavorite = async () => {
        image.setAttribute("aria-busy", "true");

        const photo = {
            id: image.dataset.pageId,
            image_url: image.src,
            title: "Favoritbild",
            latitude: Number(image.dataset.latitude),
            longitude: Number(image.dataset.longitude)
        };

        await modalGallery.openModal(photo);
        image.removeAttribute("aria-busy");
    };

    image.addEventListener("click", openFavorite);
    image.addEventListener("keydown", (event) => {
        if (event.key === "Enter" || event.key === " ") {
            event.preventDefault();
            openFavorite();
        }
    });
}
