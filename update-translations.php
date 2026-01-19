<?php
// Update language files with new translation keys

$translations = [
    'en' => [
        'form.select_district_first' => '⚠️ Please select district first',
        'form.select_district_first_helper' => 'Please select district first'
    ],
    'si' => [
        'form.select_district_first' => '⚠️ කරුණාකර මුලින්ම දිස්ත්‍රික්කය තෝරන්න',
        'form.select_district_first_helper' => 'කරුණාකර මුලින්ම දිස්ත්‍රික්කය තෝරන්න'
    ],
    'ta' => [
        'form.select_district_first' => '⚠️ தயவுசெய்து முதலில் மாவட்டத்தைத் தேர்ந்தெடுக்கவும்',
        'form.select_district_first_helper' => 'தயவுசெய்து முதலில் மாவட்டத்தைத் தேர்ந்தெடுக்கவும்'
    ]
];

foreach ($translations as $lang => $newKeys) {
    $file = "assets/lang/{$lang}.json";

    if (file_exists($file)) {
        // Read existing translations
        $existing = json_decode(file_get_contents($file), true);

        // Add new keys
        foreach ($newKeys as $key => $value) {
            // Use dot notation to add nested keys
            $keys = explode('.', $key);
            $current = &$existing;

            foreach ($keys as $i => $k) {
                if ($i === count($keys) - 1) {
                    $current[$k] = $value;
                } else {
                    if (!isset($current[$k])) {
                        $current[$k] = [];
                    }
                    $current = &$current[$k];
                }
            }
        }

        // Write back to file
        file_put_contents($file, json_encode($existing, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        echo "Updated: {$file}\n";
    } else {
        echo "File not found: {$file}\n";
    }
}

echo "\nDone!\n";
