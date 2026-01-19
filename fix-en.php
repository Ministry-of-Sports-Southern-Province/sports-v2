<?php
$en = json_decode(file_get_contents('assets/lang/en-backup.json'), true);
$en['stats.total_clubs'] = 'Total Clubs';
$en['stats.galle'] = 'Galle District';
$en['stats.matara'] = 'Matara District';
$en['stats.hambantota'] = 'Hambantota District';
file_put_contents('assets/lang/en.json', json_encode($en, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
echo 'English fixed: ' . count($en) . ' keys' . PHP_EOL;
