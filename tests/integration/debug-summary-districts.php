<?php
// Debug helper: print summary.byDistrict and aggregation from clubs-list for inspection
$base = 'http://localhost/sports-v2';
function req($url){ $c = curl_init($url); curl_setopt_array($c,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>20]); $b = curl_exec($c); $code = curl_getinfo($c, CURLINFO_HTTP_CODE); $err = curl_error($c); curl_close($c); return [$code,$b,$err]; }
list($c,$b) = req($base.'/api/summary.php'); echo "SUMMARY (truncated):\n"; $s = json_decode($b,true); if(!$s) { echo "summary JSON parse error\n"; echo $b; exit(1);} foreach(array_slice($s['data']['byDistrict'],0,20) as $r) { printf("%s => %s\n", $r['district'], $r['count']); }

list($c,$b) = req($base.'/api/clubs-list.php?limit=1&page=1'); $one = json_decode($b,true); $total = $one['pagination']['total'] ?? 0; echo "\nclubs-list total clubs: $total\n";
list($c,$b) = req($base.'/api/clubs-list.php?limit='.$total.'&page=1'); $all = json_decode($b,true);
$agg = [];
foreach($all['data'] as $club){ $d = $club['district_name'] ?? ''; $agg[$d] = ($agg[$d] ?? 0) + 1; }

echo "\nTop aggregated districts from clubs-list:\n";
arsort($agg); $i=0; foreach($agg as $k=>$v){ printf("%s => %d\n", $k,$v); if(++$i>20) break; }

// print mismatches (show both values)
echo "\nMISMATCHES (summary vs clubs-list aggregation):\n";
$summaryMap = []; foreach($s['data']['byDistrict'] as $r) $summaryMap[$r['district']] = (int)$r['count'];
foreach($summaryMap as $district=>$count){ $expected = $agg[$district] ?? 0; if($expected !== $count) printf("%s: summary=%d clubs-list=%d\n", $district, $count, $expected); }
