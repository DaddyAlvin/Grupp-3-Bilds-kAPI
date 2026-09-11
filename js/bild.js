export class bild {
    constructor(containerId, tag, apiBase = "api/") {
        // Store the shared page elements used by the gallery, modal, map, and favorite controls.
        this.container = document.getElementById(containerId);
        this.tag = tag;
        this.apiBase = apiBase;
        this.modal = document.getElementById("imageModal");
        this.modalImage = document.getElementById("modalImage");
        this.modalTitle = document.getElementById("modalTitle");
        this.modalLatitude = document.getElementById("modalLatitude");
        this.modalLongitude = document.getElementById("modalLongitude");
        this.map = null;
        this.marker = null;

        this.likeBtn = document.getElementById("likeBtn");
        this.likeIcon = document.getElementById("likeIcon");
        this.likeText = document.getElementById("likeText");
        this.likeMsg = document.getElementById("likeMsg");

        this.currentPhoto = null;
        this.map = null;
        this.marker = null;
        this.isLoggedIn = document.body.dataset.loggedIn === "true";

        // Bind modal events once because a new gallery instance is created for each search.
        if (!this.modal.dataset.eventsBound) {
            this.modal.querySelector(".modal-close").addEventListener("click", () => {
                this.modal.close();
            });

            this.modal.addEventListener("click", (event) => {
                if (event.target === this.modal) {
                    this.modal.close();
                }
            });

            this.modal.dataset.eventsBound = "true";
        }

        // Bind the favorite button once; toggleLike handles guest access.
        if (this.likeBtn && !this.likeBtn.dataset.bound) {
            this.likeBtn.addEventListener("click", () => this.toggleLike());
            this.likeBtn.dataset.bound = "true";
        }
    }
    
    renderImages(data) {
        // Replace the previous results with the images returned by the API.
        this.container.innerHTML = "";
        this.container.removeAttribute("aria-busy");
        const photos = data.photos ?? [];

        if (photos.length === 0) {
            this.container.textContent = `Inga bilder hittades för "${this.tag}".`;
            return;
        }

        photos.forEach((photo) => {
            const image = document.createElement("img");
            image.src = photo.image_url;
            image.alt = photo.title || `Bild med sökordet ${this.tag}`;
            image.tabIndex = 0;
            image.addEventListener("click", () => this.openModal(photo));
            image.addEventListener("keydown", (event) => {
                if (event.key === "Enter" || event.key === " ") {
                    event.preventDefault();
                    this.openModal(photo);
                }
            });

            this.container.appendChild(image);
        });
    }

    renderLoading() {
        // Show a stable set of placeholders while the API request is in progress.
        this.container.innerHTML = "";
        this.container.setAttribute("aria-busy", "true");

        for (let index = 0; index < 30; index += 1) {
            const placeholder = document.createElement("div");
            placeholder.className = "image-placeholder";
            placeholder.setAttribute("aria-hidden", "true");
            this.container.appendChild(placeholder);
        }
    }

    async openModal(photo) {
        // Populate the modal with the selected image and its location data.
        this.currentPhoto = photo;
        this.modalImage.src = photo.image_url;
        this.modalImage.alt = photo.title || `Bild med sökordet ${this.tag}`;
        this.modalTitle.textContent = photo.title || "Bilddetaljer";
        this.modalLatitude.textContent = photo.latitude;
        this.modalLongitude.textContent = photo.longitude;
        if (this.likeMsg) this.likeMsg.textContent = "";

        await this.checkLikeState(photo.id);

        this.modal.showModal();

        // Create the map once, then reuse it for every selected image.
        if (!this.map) {
            this.map = L.map("map");
            L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
            }).addTo(this.map);
        }

        if (this.marker) {
            this.map.removeLayer(this.marker);
        }
        this.marker = L.marker([photo.latitude, photo.longitude]).addTo(this.map);
        this.map.setView([photo.latitude, photo.longitude], 13);
        this.map.invalidateSize();
    }

    async checkLikeState(imageId) {
        // Guests cannot have saved favorites, so avoid an unnecessary API request.
        if (!this.isLoggedIn || !imageId) {
            this.setLikeUI(false);
            return;
        }

        try {
            const res = await fetch(`${this.apiBase}like.php?action=check&image_id=${encodeURIComponent(imageId)}`);
            if (res.ok) {
                const data = await res.json();
                this.setLikeUI(data.liked);
            }
        } catch (err) {
            console.error("Fel vid hämtning av favoritsvar:", err);
        }
    }

    async toggleLike() {
        // Require authentication before sending a favorite request.
        if (!this.isLoggedIn) {
            if (this.likeMsg) this.likeMsg.textContent = "Logga in för att spara favoriter.";
            return;
        }

        if (!this.currentPhoto?.id) return;

        // Toggle the selected image through the favorites endpoint.
        try {
            const res = await fetch(`${this.apiBase}like.php`, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    image_id: this.currentPhoto.id,
                    image_url: this.currentPhoto.image_url,
                    latitude: this.currentPhoto.latitude,
                    longitude: this.currentPhoto.longitude
                })
            });

            const responseText = await res.text();
            let data;
            try {
                data = JSON.parse(responseText);
            } catch {
                throw new Error("Favoriten kunde inte sparas just nu.");
            }
            if (res.ok) {
                this.setLikeUI(data.liked);
                if (this.likeMsg) this.likeMsg.textContent = data.message;
            } else {
                if (this.likeMsg) this.likeMsg.textContent = data.error || "Fel uppstod.";
            }
        } catch (err) {
            if (this.likeMsg) this.likeMsg.textContent = "Favoriten kunde inte sparas just nu.";
            console.error("Fel vid sparande av favorit:", err);
        }
    }

    setLikeUI(isLiked) {
        // Keep the button icon, label, and state class synchronized.
        if (!this.likeBtn) return;
        if (isLiked) {
            this.likeIcon.textContent = "❤️";
            this.likeText.textContent = "Sparad i favoriter";
            this.likeBtn.classList.add("active");
        } else {
            this.likeIcon.textContent = "🤍";
            this.likeText.textContent = "Spara som favorit";
            this.likeBtn.classList.remove("active");
        }
    }

    async load() {
        // Fetch one page of coordinate images for the current search term.
        const params = new URLSearchParams({
            text: this.tag,
            page: "1"
        });

        this.renderLoading();

        // Render the results or a user-facing error message.
        try {
            const response = await fetch(`${this.apiBase}api.php?${params}`);
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.error || `API-anropet misslyckades: ${response.status}`);
            }

            this.renderImages(data);
        } catch (error) {
            this.container.removeAttribute("aria-busy");
            this.container.textContent = "Kunde inte hämta bilderna.";
            console.error("Kunde inte hämta bilderna:", error);
        }
    }
}