export class bild {
    constructor(containerId, tag) {
        this.container = document.getElementById(containerId);
        this.tag = tag;
        this.modal = document.getElementById("imageModal");
        this.modalImage = document.getElementById("modalImage");
        this.modalTitle = document.getElementById("modalTitle");
        this.modalLatitude = document.getElementById("modalLatitude");
        this.modalLongitude = document.getElementById("modalLongitude");
        this.map = null;
        this.marker = null;

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
    }
    
    renderImages(data) {
        this.container.innerHTML = "";

        const photos = data.photos ?? [];

        if (photos.length === 0) {
            this.container.textContent = `Inga bilder hittades för "${this.tag}".`;
            return;
        }

        photos.forEach((photo) => {
            const image = document.createElement("img");

            image.src = photo.image_url;
            image.alt = photo.title || `Bild med sökordet ${this.tag}`;
            image.setAttribute("role", "button");
            image.setAttribute("aria-label", `Visa ${image.alt} i större format`);
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
        this.container.innerHTML = "";
        this.container.setAttribute("aria-busy", "true");

        for (let index = 0; index < 30; index += 1) {
            const placeholder = document.createElement("div");
            placeholder.className = "image-placeholder";
            placeholder.setAttribute("aria-hidden", "true");
            this.container.appendChild(placeholder);
        }
    }

    openModal(photo) {
        this.modalImage.src = photo.image_url;
        this.modalImage.alt = photo.title || `Bild med sökordet ${this.tag}`;
        this.modalTitle.textContent = photo.title || "Bilddetaljer";
        this.modalLatitude.textContent = photo.latitude;
        this.modalLongitude.textContent = photo.longitude;
        this.modal.showModal();

        if (!this.map) {
            this.map = L.map("map");
            L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
                attribution: "&copy"
            }).addTo(this.map);
        }

        if (this.marker) {
            this.map.removeLayer(this.marker);
        }
        this.marker = L.marker([photo.latitude, photo.longitude]).addTo(this.map);

        this.map.setView([photo.latitude, photo.longitude], 13);
        this.map.invalidateSize();
    }

    //easy peasy
    async load() {
        const params = new URLSearchParams({
            text: this.tag,
            page: "1"
        });

        this.renderLoading();

        try {
            const response = await fetch(`api/api.php?${params}`);
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.error || `API-anropet misslyckades: ${response.status}`);
            }

            this.renderImages(data);
        } catch (error) {
            this.container.textContent = "Kunde inte hämta bilderna.";
            console.error("Kunde inte hämta bilderna:", error);
        }

    }

}