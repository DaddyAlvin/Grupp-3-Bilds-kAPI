<!DOCTYPE html>
<html lang="sv">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Testa bild-API</title>
	<link rel="stylesheet" href="css/style.css">
</head>
<body>
	<h1>Testa bild-API:t</h1>

	<form id="search-form">
		<label for="text">Sökord</label>
		<input id="text" name="text" value="Stockholm" maxlength="200" required>

		<label for="total-images">Totalt antal bilder</label>
		<input id="total-images" name="total-images" type="number" min="5" max="30" step="5" value="30">

		<button type="submit">Anropa API</button>
	</form>
	<p id="status">Fyll i en sökterm och klicka på "Anropa API".</p>
	<div id="gallery"></div>

	<h2>Rått JSON-svar</h2>
	<pre id="raw-response">Inget API-svar ännu.</pre>

	<script>
		const form = document.getElementById('search-form');
		const status = document.getElementById('status');
		const gallery = document.getElementById('gallery');
		const rawResponse = document.getElementById('raw-response');
		const batchSize = 5;

		async function loadImages() {
			gallery.replaceChildren();
			rawResponse.textContent = 'Hämtar svar...';
			const searchText = document.getElementById('text').value.trim();
			const requestedTotal = Number(document.getElementById('total-images').value);
			let continuation = null;
			let loadedImages = 0;
			let page = 1;
			let lastResponse = null;

			while (loadedImages < requestedTotal) {
				status.textContent = `Hämtar bilder ${loadedImages + 1}-${Math.min(loadedImages + batchSize, requestedTotal)}...`;
				const params = new URLSearchParams({
					text: searchText,
					per_page: String(batchSize),
					page: String(page),
				});
				if (continuation) {
					params.set('continuation', continuation);
				}

				const response = await fetch(`api.php?${params}`);
				const data = await response.json();
				lastResponse = data;

				if (!response.ok) {
					rawResponse.textContent = JSON.stringify(data, null, 2);
					throw new Error(data.error || `HTTP ${response.status}`);
				}

				continuation = data.next_continuation ?? null;

				data.photos.forEach((photo) => {
					const figure = document.createElement('figure');
					const image = document.createElement('img');
					const caption = document.createElement('figcaption');

					image.src = photo.image_url;
					image.alt = photo.title || 'Bild från Wikimedia Commons';
					caption.textContent = photo.title || 'Bild utan titel';

					figure.append(image, caption);
					gallery.appendChild(figure);
				});
				loadedImages += data.photos.length;
				page += 1;

				if (data.photos.length === 0 || !continuation) {
					break;
				}
			}

			rawResponse.textContent = JSON.stringify(lastResponse, null, 2);
			status.textContent = `Klar - ${loadedImages} bilder laddades i batchar om ${batchSize}`;
		}

		form.addEventListener('submit', async (event) => {
			event.preventDefault();
			try {
				await loadImages();
			} catch (error) {
				status.textContent = 'Kunde inte läsa API-svaret.';
				rawResponse.textContent = error.message;
			}
		});
	</script>
</body>
</html>
