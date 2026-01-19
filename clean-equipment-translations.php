<?php
// Remove equipment.* keys from all language files

$languages = ['en', 'si', 'ta'];

foreach ($languages as $lang) {
    $file = "assets/lang/{$lang}.json";

    if (!file_exists($file)) {
        echo "File not found: {$file}\n";
        continue;
    }

    // Read JSON
    $json = file_get_contents($file);
    $data = json_decode($json, true);

    if (!$data) {
        echo "Failed to parse: {$file}\n";
        continue;
    }

    // Remove all equipment.* keys
    $removed = 0;
    foreach (array_keys($data) as $key) {
        if (strpos($key, 'equipment.') === 0) {
            unset($data[$key]);
            $removed++;
        }
    }

    // Save back
    file_put_contents($file, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

    echo "Cleaned {$file}: Removed {$removed} equipment entries\n";
}

echo "\nDone! All equipment data removed from translation files.\n";
echo "Equipment will now load from database only.\n";
