<?php
header('Content-Type: application/json; charset=utf-8');

$directory = __DIR__;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string) ($_POST['name'] ?? ''));

    if ($name === '' || $name === '.' || $name === '..' || strpbrk($name, '/\\') !== false || preg_match('/[\x00-\x1F\x7F]/', $name)) {
        http_response_code(400);
        echo json_encode(['error' => 'Ange ett giltigt mappnamn utan sökväg.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $path = $directory . DIRECTORY_SEPARATOR . $name;
    if (file_exists($path)) {
        http_response_code(409);
        echo json_encode(['error' => 'En fil eller mapp med det namnet finns redan.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if (!mkdir($path, 0775)) {
        http_response_code(500);
        echo json_encode(['error' => 'Mappen kunde inte skapas.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $safeName = htmlspecialchars($name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $index = <<<HTML
<!doctype html>
<html lang="sv">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{$safeName}</title>
</head>
<body>
    <main>
        <h1>Hej!</h1>
        <p>Välkommen till <strong>{$safeName}</strong>.</p>
        <p>Det här är projektets startsida.</p>
    </main>
</body>
</html>
HTML;

    if (file_put_contents($path . DIRECTORY_SEPARATOR . 'index.html', $index, LOCK_EX) === false) {
        rmdir($path);
        http_response_code(500);
        echo json_encode(['error' => 'Mappen skapades, men startsidan kunde inte skapas.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    echo json_encode(['name' => $name, 'href' => rawurlencode($name) . '/'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$folders = [];

foreach (scandir($directory) as $entry) {
    if ($entry === '.' || $entry === '..' || $entry === 'index.html' || $entry === 'folders.php') {
        continue;
    }

    $path = $directory . DIRECTORY_SEPARATOR . $entry;
    if (is_dir($path)) {
        $folders[] = [
            'name' => $entry,
            'href' => rawurlencode($entry) . '/'
        ];
    }
}

usort($folders, static fn(array $first, array $second): int => strcasecmp($first['name'], $second['name']));
echo json_encode($folders, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
