<?php
foreach(['en', 'si', 'ta'] as $lang) {
    $json = json_decode(file_get_contents("assets/lang/{$lang}.json"), true);
    echo "{$lang}: " . count($json) . " keys - ";
    echo (isset($json['stats.total_clubs']) ? '✓ stats keys present' : '✗ missing stats keys') . "\n";
    
    // Show the actual stats values
    if (isset($json['stats.total_clubs'])) {
        echo "  stats.total_clubs: {$json['stats.total_clubs']}\n";
        echo "  stats.galle: {$json['stats.galle']}\n";
        echo "  stats.matara: {$json['stats.matara']}\n";
        echo "  stats.hambantota: {$json['stats.hambantota']}\n";
    }
    echo "\n";
}
