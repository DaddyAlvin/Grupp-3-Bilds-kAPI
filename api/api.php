<?php
// Tvingar PHP att använda strikt typkontroll i den här filen.
declare(strict_types=1);

// Används när Wikimedia inte levererar någon information om bildens ägare.
const OWNER_UNAVAILABLE = 'Ägare ej tillgänglig';

// Wikimedia rekommenderar en identifierande User-Agent så att anrop kan
// kopplas till rätt applikation om Wikimedia behöver kontakta utvecklaren.
const WIKIMEDIA_USER_AGENT = 'BildsokAPI/1.0 (contact: alvinsandgren)';

// Varje API-svar ska innehålla högst fem färdiga bildobjekt.
const PHOTOS_PER_PAGE = 5;

// API:t fungerar som en server-side proxy mot Wikimedia Commons.
// JavaScript får alltid JSON i stället för HTML eller färdig bildvisning.
header('Content-Type: application/json; charset=utf-8');

// Tillåter att frontend-applikationen ligger på en annan domän än API:t.
header('Access-Control-Allow-Origin: *');

// API:t tar endast emot läsande GET-anrop. OPTIONS behövs för CORS-kontrollen.
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
	// Webbläsaren skickar ibland en förfrågan före det riktiga GET-anropet.
	// Denna kontroll räcker för att bekräfta CORS utan ett onödigt Wikimedia-anrop.
	http_response_code(204);
	exit;
}

// Alla andra HTTP-metoder än GET ska stoppas innan någon sökning görs.
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
	respond(['error' => 'Metoden stöds inte.'], 405);
}

// Läs vilken sida frontend vill ha och se till att sidnumret aldrig blir lägre än 1.
$page = max(getIntParameter('page', 1), 1);

// Söktexten hämtas från URL:ens text-parameter och tomma blanksteg runt den tas bort.
$text = trim((string)($_GET['text'] ?? ''));

// Continuation-token används av Wikimedia för att fortsätta där föregående sida slutade.
$continuationToken = trim((string)($_GET['continuation'] ?? ''));

// Sex sidor med fem bilder vardera begränsar totalt antal hämtade bilder till 30.
if ($page > 6) {
	respond(['error' => 'Maximalt 30 bilder kan hämtas.'], 400);
}

// Avvisa sökningar som saknar text eller är orimligt långa innan extern data hämtas.
if ($text === '' || stringLength($text) > 200) {
	respond(['error' => 'Ange en sökterm på 1-200 tecken.'], 400);
}

// Bygg Wikimedia Commons API:s sökparametrar.
// Generatorn söker bland sidor i namespace 6, vilket är Commons namespace för filer.
$queryParameters = [
	'action' => 'query',
	'generator' => 'search',
	'gsrsearch' => $text,
	'gsrnamespace' => 6,
	// Be Wikimedia om fem träffar åt gången.
	'gsrlimit' => PHOTOS_PER_PAGE,
	// Hämta både bildinformation och koordinater så mapPhoto kan skapa vårt objekt.
	'prop' => 'imageinfo|coordinates',
	'iiprop' => 'url',
	'format' => 'json',
	'origin' => '*',
	// Ber Wikimedia vänta om deras servrar har hög belastning.
	'maxlag' => 5,
];

// En token finns bara när frontend hämtar en senare sida.
// Den innehåller Wikimedia-parametrar som gör att sökningen fortsätter korrekt.
$continuation = decodeContinuation($continuationToken);
if ($continuation !== null) {
	$queryParameters = array_merge($queryParameters, $continuation);
}

// Gör den associativa PHP-arrayen till en korrekt URL-querystring.
$query = http_build_query($queryParameters);

// Här samlas endast giltiga, omformade bildobjekt som ska skickas till JavaScript.
$photos = [];

// Ett Wikimedia-svar kan innehålla bilder utan URL eller koordinater.
// Loopen hämtar nästa Wikimedia-sida tills vi har fem giltiga bilder eller är slut.
do {
	// Hämta rå JSON från Wikimedia via vår felhanterande hjälpfunktion.
	$body = fetchWikimediaData($query);

	// Omvandla JSON-texten till PHP-arrayer för att kunna läsa dess fält.
	$data = json_decode($body, true);

	// Ett lyckat HTTP-anrop är inte tillräckligt; svaret måste också ha query-data.
	if (!is_array($data) || !isset($data['query']) || !is_array($data['query'])) {
		respond(['error' => 'Wikimedia Commons returnerade ett ogiltigt svar.'], 502);
	}

	// Wikimedia använder en pages-array. Varje sida motsvarar normalt en bildfil.
	foreach ($data['query']['pages'] ?? [] as $photo) {
		// mapPhoto returnerar null om bilden saknar obligatoriska eller giltiga uppgifter.
		$mappedPhoto = mapPhoto($photo);
		if ($mappedPhoto !== null) {
			$photos[] = $mappedPhoto;
		}

		// Sluta direkt när batchen är full så att vi inte skickar fler än fem objekt.
		if (count($photos) >= PHOTOS_PER_PAGE) {
			break;
		}
	}

	// Ett continue-fält betyder att Wikimedia har fler träffar att lämna ut.
	$continuation = $data['continue'] ?? null;
	if (count($photos) < PHOTOS_PER_PAGE && is_array($continuation)) {
		// Fortsätt med samma grundsökning och lägg till Wikimedia-token.
		$query = http_build_query(array_merge($queryParameters, $continuation));
	}
} while (count($photos) < PHOTOS_PER_PAGE && is_array($continuation));

// Spara tokenen för nästa sida om Wikimedia har fler resultat.
$nextContinuation = is_array($data['continue'] ?? null)
	? $data['continue']
	: null;

// Skicka ett JSON-objekt till JavaScript.
// photos innehåller bildobjekten, medan övriga fält beskriver pagineringen.
respond([
	'page' => $page,
	'pages' => $nextContinuation === null ? $page : $page + 1,
	'total' => count($photos),
	'photos' => $photos,
	'next_continuation' => $nextContinuation === null ? null : encodeContinuation($nextContinuation),
]);

function respond(array $payload, int $status = 200): never
{
	// Sätt HTTP-statusen så frontend kan skilja lyckade svar från fel.
	http_response_code($status);

	// Omvandla PHP-arrayen till JSON som kan läsas direkt av JavaScript.
	// UNESCAPED_UNICODE bevarar svenska tecken och UNESCAPED_SLASHES ger rena URL:er.
	echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

	// Ett svar ska avsluta skriptet så att inget extra innehåll råkar läggas till.
	exit;
}

function parseCoordinate(mixed $value, float $min, float $max): ?float
{
	// Koordinaten måste först vara ett numeriskt värde innan den kan konverteras.
	if (!is_numeric($value)) {
		return null;
	}

	// Gör värdet till float eftersom latitud och longitud är decimaltal.
	$value = (float)$value;

	// Returnera bara koordinater inom jordens giltiga intervall.
	return $value >= $min && $value <= $max ? $value : null;
}

function getIntParameter(string $name, int $default): int
{
	// Läs en heltalsparameter säkert från GET-parametrarna.
	$value = filter_input(INPUT_GET, $name, FILTER_VALIDATE_INT);

	// Använd standardvärdet om parametern saknas eller inte är ett heltal.
	return $value === false || $value === null ? $default : $value;
}

function stringLength(string $value): int
{
	// mb_strlen räknar svenska och andra Unicode-tecken korrekt som tecken.
	// Fallbacken gör ändå funktionen användbar om mbstring-tillägget saknas.
	return function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);
}

function mapPhoto(mixed $photo): ?array
{
	// Säkerställ att Wikimedia-posten verkligen är en array innan fält läses.
	if (!is_array($photo)) {
		return null;
	}

	// Hämta bildens original-URL och avvisa värden som inte är giltiga URL:er.
	$imageUrl = filter_var((string)($photo['imageinfo'][0]['url'] ?? ''), FILTER_VALIDATE_URL);
	if ($imageUrl === false) {
		return null;
	}

	// Coordinates och imageinfo ligger i arrayer eftersom Wikimedia kan ha flera värden.
	// Den första koordinaten används som bildens geografiska position.
	$coordinates = $photo['coordinates'][0] ?? [];
	$latitude = parseCoordinate($coordinates['lat'] ?? null, -90, 90);
	$longitude = parseCoordinate($coordinates['lon'] ?? null, -180, 180);

	// Bilder utan både giltig latitud och longitud kan inte användas av kart-/platslogiken.
	if ($latitude === null || $longitude === null) {
		return null;
	}

	// Skapa ett förenklat och stabilt objekt för frontend.
	// Frontend behöver inte känna till Wikimedia-svaret eller dess interna struktur.
	return [
		// pageid fungerar som ett identifierande värde för bilden hos Wikimedia.
		'id' => (string)($photo['pageid'] ?? ''),
		// Ta bort eventuell HTML från titeln innan den skickas till JavaScript.
		'title' => trim(strip_tags((string)($photo['title'] ?? ''))),
		// URL:en används av frontend när bilden ska laddas.
		'image_url' => $imageUrl,
		// Wikimedia-resultatet innehåller inte alltid ägarinformation i denna fråga.
		'owner' => OWNER_UNAVAILABLE,
		// En färdig textrepresentation är praktisk för enkel visning.
		'location' => $latitude . ', ' . $longitude,
		// Separata numeriska värden kan användas av exempelvis kartlogik.
		'latitude' => $latitude,
		'longitude' => $longitude,
	];
}

function fetchWikimediaData(string $query): string
{
	// Sätt ihop Wikimedia-URL:en med den redan URL-kodade querystringen.
	$ch = curl_init('https://commons.wikimedia.org/w/api.php?' . $query);

	// Konfigurera cURL för ett server-till-server-anrop som returnerar text.
	curl_setopt_array($ch, [
		// Returnera svaret till variabeln i stället för att skriva ut det direkt.
		CURLOPT_RETURNTRANSFER => true,
		// Avsluta om Wikimedia inte svarar inom tio sekunder.
		CURLOPT_TIMEOUT => 10,
		// Anslutningen får högst ta fem sekunder att etablera.
		CURLOPT_CONNECTTIMEOUT => 5,
		// Följ inte omdirigeringar automatiskt från en extern tjänst.
		CURLOPT_FOLLOWLOCATION => false,
		// Acceptera komprimerade svar för att minska mängden överförd data.
		CURLOPT_ENCODING => '',
		// Tala om att vi vill ha JSON och identifiera applikationen för Wikimedia.
		CURLOPT_HTTPHEADER => [
			'Accept: application/json',
			'User-Agent: ' . WIKIMEDIA_USER_AGENT,
		],
	]);
	$body = curl_exec($ch);
	$status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
	$curlError = curl_error($ch);
	curl_close($ch);

	// Hantera nätverksfel, timeout och HTTP-statusar som inte betyder lyckat svar.
	if ($body === false || $curlError !== '' || $status < 200 || $status >= 300) {
		respond(['error' => 'Kunde inte hämta data från Wikimedia Commons.'], 502);
	}

	// Returnera råsvaret så huvudflödet kan tolka och omforma det.
	return $body;
}

function encodeContinuation(array $continuation): string
{
	// Wikimedia-tokenen är en array. JSON gör den strukturerad innan kodning.
	// Base64-url-formatet gör tokenen säker att skicka som URL-parameter.
	return rtrim(strtr(base64_encode((string)json_encode($continuation)), '+/', '-_'), '=');
}

function decodeContinuation(string $token): ?array
{
	// En tom token betyder att sökningen ska börja från första resultatet.
	if ($token === '') {
		return null;
	}

	// Base64 behöver ibland utfyllnadstecken för att längden ska bli delbar med fyra.
	$padding = strlen($token) % 4;

	// Återställ URL-safe Base64, avkoda tokenen och tolka dess JSON-innehåll.
	$decoded = base64_decode(strtr($token . str_repeat('=', $padding === 0 ? 0 : 4 - $padding), '-_', '+/'), true);
	$continuation = is_string($decoded) ? json_decode($decoded, true) : null;

	// Ogiltig token behandlas som om ingen continuation skickats.
	return is_array($continuation) ? $continuation : null;
}