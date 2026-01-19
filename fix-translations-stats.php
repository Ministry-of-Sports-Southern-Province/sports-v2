<?php
/**
 * Fix Translation Files - Add Statistics Keys with Proper UTF-8 Encoding
 */

// Read existing translation files
$enFile = 'assets/lang/en.json';
$siFile = 'assets/lang/si.json';
$taFile = 'assets/lang/ta.json';

// English translations
$enJson = json_decode(file_get_contents($enFile), true);
$enJson['stats.total_clubs'] = 'Total Clubs';
$enJson['stats.galle'] = 'Galle District';
$enJson['stats.matara'] = 'Matara District';
$enJson['stats.hambantota'] = 'Hambantota District';
file_put_contents($enFile, json_encode($enJson, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

// Sinhala translations
$siJson = json_decode(file_get_contents($siFile), true);
$siJson['stats.total_clubs'] = 'මුළු සමාජ ගණන';
$siJson['stats.galle'] = 'ගාල්ල දිස්ත්‍රික්කය';
$siJson['stats.matara'] = 'මාතර දිස්ත්‍රික්කය';
$siJson['stats.hambantota'] = 'හම්බන්තොට දිස්ත්‍රික්කය';
file_put_contents($siFile, json_encode($siJson, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

// Tamil translations
$taJson = json_decode(file_get_contents($taFile), true);
$taJson['stats.total_clubs'] = 'மொத்த சங்கங்கள்';
$taJson['stats.galle'] = 'காலி மாவட்டம்';
$taJson['stats.matara'] = 'மாத்தறை மாவட்டம்';
$taJson['stats.hambantota'] = 'அம்பாந்தோட்டை மாவட்டம்';
file_put_contents($taFile, json_encode($taJson, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

echo "Translation files fixed successfully!\n";
echo "English file: " . filesize($enFile) . " bytes\n";
echo "Sinhala file: " . filesize($siFile) . " bytes\n";
echo "Tamil file: " . filesize($taFile) . " bytes\n";
