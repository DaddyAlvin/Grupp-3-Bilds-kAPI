export class bild {
    constructor(containerId, tag) {
        this.container = document.getElementById(containerId);
        this.tag = tag;
    }
    //easy super peasy
    renderRandomImage(data) {
        this.container.innerHTML = "";

        const pages = Object.values(data.query?.pages ?? {}).filter(
            (page) => page.imageinfo?.[0]?.thumburl
        );

        if (pages.length === 0) {
            this.container.textContent = `Inga bilder hittades för "${this.tag}".`;
            return;
        }

        const item = pages[Math.floor(Math.random() * pages.length)];
        const image = document.createElement("img");

        image.src = item.imageinfo[0].thumburl;
        image.alt = `Bild med taggen ${this.tag}`;

        this.container.appendChild(image);
    }

    //easy peasy
    load() {
        const params = new URLSearchParams({
            action: "query",
            format: "json",
            origin: "*",
            generator: "search",
            gsrnamespace: "6",
            gsrsearch: this.tag,
            prop: "imageinfo",
            iiprop: "url",
            iiurlwidth: "500"
        });

        fetch(`https://commons.wikimedia.org/w/api.php?${params}`)
            .then((response) => {
                if (!response.ok) {
                    throw new Error(`API-anropet misslyckades: ${response.status}`);
                }

                return response.json();
            })
            .then((data) => this.renderRandomImage(data))
            .catch((error) => {
                console.error("Kunde inte hämta bilden:", error);
            });
    }

}