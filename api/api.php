<?php
// Enable strict typing for this file.
declare(strict_types=1);

// Fetch enough results to return up to 30 images with coordinates.
const TARGET_PHOTOS = 30;
$maxFetches = 20;
$fetchCount = 0;

// Used when Wikimedia does not provide image owner information.
const OWNER_UNAVAILABLE = 'Ägare ej tillgänglig';

// Wikimedia recommends an identifiable User-Agent so requests can be linked
// to the correct application if the developer needs to be contacted.
const WIKIMEDIA_USER_AGENT = 'BildsokAPI/1.0 (contact: alvinsandgren)';

// Request up to 50 images per Wikimedia page.
const PHOTOS_PER_PAGE = 50;

// The API works as a server-side proxy against Wikimedia Commons.
// JavaScript always receives JSON instead of HTML or a finished image view.
header('Content-Type: application/json; charset=utf-8');

// Allow the frontend application to run on a different domain than the API.
header('Access-Control-Allow-Origin: *');

// The API only accepts read-only GET requests. OPTIONS is needed for CORS control.
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
	// Browsers sometimes send a preflight request before the actual GET request.
	// This check is sufficient to confirm CORS without an unnecessary Wikimedia request.
	http_response_code(204);
	exit;
}

// Reject all methods other than GET before starting a search.
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
	respond(['error' => 'Metoden stöds inte.'], 405);
}

// Read the requested page and ensure it is never lower than 1.
$page = max(getIntParameter('page', 1), 1);

// The search text is retrieved from the URL's text parameter and any leading or trailing whitespace is removed.
$text = trim((string)($_GET['text'] ?? ''));

// The continuation token lets Wikimedia resume where the previous page ended.
$continuationToken = trim((string)($_GET['continuation'] ?? ''));

// Limit the search to six pages and a maximum of 30 returned images.
if ($page > 6) {
	respond(['error' => 'Maximalt 30 bilder kan hämtas.'], 400);
}

// Reject empty or unreasonably long searches before fetching external data.
if ($text === '' || stringLength($text) > 200) {
	respond(['error' => 'Ange en sökterm på 1-200 tecken.'], 400);
}

// Build the Wikimedia Commons API search parameters.
// The generator searches through pages in namespace 6, which is the Commons namespace for files.
$queryParameters = [
    'action' => 'query',
    'generator' => 'search',
	'gsrsearch' => $text, // Use the original search term.
    'gsrnamespace' => 6,
	'gsrlimit' => 50, // Wikimedia's maximum page size for coordinate searches.
    'prop' => 'imageinfo|coordinates',
    'iiprop' => 'url',
    'iiurlwidth' => 600,
    'format' => 'json',
    'origin' => '*',
];

// A token is present only when the frontend requests a later page.
// It contains the Wikimedia parameters needed to continue the search.
$continuation = decodeContinuation($continuationToken);
if ($continuation !== null) {
	$queryParameters = array_merge($queryParameters, $continuation);
}

// Convert the associative PHP array into a valid URL query string.
$query = http_build_query($queryParameters);

// Store only valid, transformed image objects for the JavaScript frontend.
$photos = [];

// Wikimedia may return images without a URL or coordinates.
// Fetch additional pages until 30 valid images are found or results run out.
do {
	$fetchCount++;
	// Fetch raw JSON from Wikimedia through the error-handling helper.
	$body = fetchWikimediaData($query);

	// Decode the JSON response into PHP arrays.
	$data = json_decode($body, true);

	// A successful HTTP request must also contain query data.
	if (!is_array($data) || !isset($data['query']) || !is_array($data['query'])) {
		respond(['error' => 'Wikimedia Commons returnerade ett ogiltigt svar.'], 502);
	}

	// Wikimedia uses a pages array. Each page normally represents an image file.
	foreach ($data['query']['pages'] ?? [] as $photo) {
		// mapPhoto returns null when required data is missing or invalid.
		$mappedPhoto = mapPhoto($photo);
		if ($mappedPhoto !== null) {
			$photos[] = $mappedPhoto;
		}

		// Stop immediately when we have reached 30 approved images.
		if (count($photos) >= TARGET_PHOTOS) {
			break;
		}
	}

	// A continue field means that Wikimedia has more results to provide.
	$continuation = $data['continue'] ?? null;
	if (count($photos) < TARGET_PHOTOS && is_array($continuation)) {
		// Continue with the same base search and add the Wikimedia token.
		$query = http_build_query(array_merge($queryParameters, $continuation));
	}

	// Pause briefly to reduce the risk of triggering Wikimedia's 429 rate limit.
    usleep(300000);
} while (count($photos) < TARGET_PHOTOS && is_array($continuation) && $fetchCount < $maxFetches);

// Save the token for the next page if Wikimedia has more results.
$nextContinuation = is_array($data['continue'] ?? null)
	? $data['continue']
	: null;

// Send a JSON object to JavaScript.
// photos contains the image objects, while the other fields describe pagination.
respond([
	'page' => $page,
	'pages' => $nextContinuation === null ? $page : $page + 1,
	'total' => count($photos),
	'photos' => $photos,
	'next_continuation' => $nextContinuation === null ? null : encodeContinuation($nextContinuation),
]);

function respond(array $payload, int $status = 200): never
{
	// Set the HTTP status so the frontend can distinguish successful responses from errors.
	http_response_code($status);

	// Convert the PHP array to JSON that JavaScript can read directly.
	// UNESCAPED_UNICODE preserves Swedish characters and UNESCAPED_SLASHES produces clean URLs.
	echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

	// A response must end the script so that no extra content is accidentally added.
	exit;
}

function parseCoordinate(mixed $value, float $min, float $max): ?float
{
	// The coordinate must first be a numeric value before it can be converted.
	if (!is_numeric($value)) {
		return null;
	}

	// Convert the value to a float because latitude and longitude are decimal values.
	$value = (float)$value;

	// Return only coordinates within Earth's valid ranges.
	return $value >= $min && $value <= $max ? $value : null;
}

function getIntParameter(string $name, int $default): int
{
	// Safely read an integer parameter from the GET parameters.
	$value = filter_input(INPUT_GET, $name, FILTER_VALIDATE_INT);

	// Use the default value if the parameter is missing or is not an integer.
	return $value === false || $value === null ? $default : $value;
}

function stringLength(string $value): int
{
	// mb_strlen correctly counts Swedish and other Unicode characters as characters.
	// The fallback still makes the function useful if the mbstring extension is missing.
	return function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);
}

function mapPhoto(mixed $photo): ?array
{
	// Ensure that the Wikimedia entry is actually an array before reading fields.
	if (!is_array($photo)) {
		return null;
	}

	// Get the image's original URL and reject values that are not valid URLs.
	// Prefer the thumbnail URL and fall back to the original URL if necessary.
	$imageUrl = filter_var((string)($photo['imageinfo'][0]['thumburl'] ?? $photo['imageinfo'][0]['url'] ?? ''), FILTER_VALIDATE_URL);
	if ($imageUrl === false) {
		return null;
	}

	// Coordinates and imageinfo are arrays because Wikimedia can have multiple values.
	// The first coordinate is used as the image's geographic position.
	$coordinates = $photo['coordinates'][0] ?? [];
	$latitude = parseCoordinate($coordinates['lat'] ?? null, -90, 90);
	$longitude = parseCoordinate($coordinates['lon'] ?? null, -180, 180);

	// Images without both a valid latitude and longitude cannot be used by the map/location logic.
	if ($latitude === null || $longitude === null) {
		return null;
	}

	// Create a simplified and stable object for the frontend.
	// The frontend does not need to know about the Wikimedia response or its internal structure.
	return [
		// pageid functions as an identifying value for the image at Wikimedia.
		'id' => (string)($photo['pageid'] ?? ''),
		// Remove any HTML from the title before sending it to JavaScript.
		'title' => trim(strip_tags((string)($photo['title'] ?? ''))),
		// The URL is used by the frontend when the image is loaded.
		'image_url' => $imageUrl,
		// The Wikimedia result does not always contain owner information in this request.
		'owner' => OWNER_UNAVAILABLE,
		// A ready-made text representation is convenient for simple display.
		'location' => $latitude . ', ' . $longitude,
		// Separate numeric values can be used by, for example, map logic.
		'latitude' => $latitude,
		'longitude' => $longitude,
	];
}

function fetchWikimediaData(string $query): string
{
	// Assemble the Wikimedia URL with the already URL-encoded query string.
	$ch = curl_init('https://commons.wikimedia.org/w/api.php?' . $query);

	// Configure cURL for a server-to-server request that returns text.
	curl_setopt_array($ch, [
		// Return the response as a variable instead of printing it directly.
		CURLOPT_RETURNTRANSFER => true,
		// Abort if Wikimedia does not respond within ten seconds.
		CURLOPT_TIMEOUT => 10,
		// The connection may take no more than five seconds to establish.
		CURLOPT_CONNECTTIMEOUT => 5,
		// Do not automatically follow redirects from an external service.
		CURLOPT_FOLLOWLOCATION => false,
		// Accept compressed responses to reduce the amount of transferred data.
		CURLOPT_ENCODING => '',
		// Indicate that we want JSON and identify the application to Wikimedia.
		CURLOPT_HTTPHEADER => [
			'Accept: application/json',
			'User-Agent: ' . WIKIMEDIA_USER_AGENT,
		],
	]);
	$body = curl_exec($ch);
	$status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
	$curlError = curl_error($ch);
	curl_close($ch);

	// Handle network errors, timeouts, and HTTP statuses that do not indicate a successful response.
	if ($body === false || $curlError !== '' || $status < 200 || $status >= 300) {
    respond([
        'error' => "cURL-fel: {$curlError} | HTTP status: {$status}",
        'raw_body' => $body
    ], 502);
}

	// Return the raw response so the main flow can parse and transform it.
	return $body;
}

function encodeContinuation(array $continuation): string
{
	// The Wikimedia token is an array. JSON makes it structured before encoding.
	// The Base64 URL format makes the token safe to send as a URL parameter.
	return rtrim(strtr(base64_encode((string)json_encode($continuation)), '+/', '-_'), '=');
}

function decodeContinuation(string $token): ?array
{
	// An empty token means that the search should start from the first result.
	if ($token === '') {
		return null;
	}

	// Base64 sometimes needs padding characters for the length to be divisible by four.
	$padding = strlen($token) % 4;

	// Restore URL-safe Base64, decode the token, and parse its JSON contents.
	$decoded = base64_decode(strtr($token . str_repeat('=', $padding === 0 ? 0 : 4 - $padding), '-_', '+/'), true);
	$continuation = is_string($decoded) ? json_decode($decoded, true) : null;

	// An invalid token is treated as if no continuation had been sent.
	return is_array($continuation) ? $continuation : null;
}