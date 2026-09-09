export class bild {
    constructor(containerId, tag) {
        this.container = document.getElementById(containerId);
        this.tag = tag;
    }
    
    renderImages(data) {
        this.container.innerHTML = "";

        const photos = data.photos ?? [];

        if (photos.length === 0) {
            this.container.textContent = `Inga bilder hittades för "${this.tag}".`;
            return;
        }

        photos.forEach((photo) => {
            console.log("Latitud:", photo.latitude);
            console.log("Longitud:", photo.longitude);
            const image = document.createElement("img");

            image.src = photo.image_url;
            image.alt = photo.title || `Bild med sökordet ${this.tag}`;

            this.container.appendChild(image);
        });
    }

    //easy peasy
    async load() {
        const params = new URLSearchParams({
            text: this.tag,
            page: "1"
        });

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