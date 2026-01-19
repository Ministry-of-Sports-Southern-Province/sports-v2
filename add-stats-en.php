<?php
// Read the current en.json
$json = json_decode(file_get_contents(__DIR__ . '/assets/lang/en.json'), true);

// Add stats keys
$json['stats.total_clubs'] = 'Total Clubs';
$json['stats.galle'] = 'Galle District';
$json['stats.matara'] = 'Matara District';
$json['stats.hambantota'] = 'Hambantota District';

// Save
file_put_contents(__DIR__ . '/assets/lang/en.json', json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo "English translation updated: " . count($json) . " keys\n";
