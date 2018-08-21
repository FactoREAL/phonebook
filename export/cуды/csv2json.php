<?php
$settings = array(
	'group' => 'Мировые судьи',
	'region' => '31',
	'dctname' => 'MS',
	'dctgid' => '5',
	'dctnumber' => '143',
	'dctindex' => '14',
	'dcttid' => '1'
);

$res = array();
$sudy = array();
$sud = array();

$f = fopen('суды.csv', 'r');
while (($line = fgetcsv($f, 0, ',')) !== false) {
	$kod = trim(str_replace('+7', '', $line[4]));
	$sud[$line[0]] = array(
		'name' => trim($line[1]),
		'adres' => $line[2].', '.$line[3],
		'kod' => $kod,
		'email' => trim($line[5])
	);
}
fclose($f);

$f = fopen('справочник.csv', 'r');
$i = 0;
while (($line = fgetcsv($f, 0, ',')) !== false) {
	$sud_id = $line[0];
	$group = $line[1];
	$work_post = $line[2];
	$fio = $line[3];
	$number = $line[4];

	$tmp = explode(' ', $fio);
	$tmp = array_filter($tmp, function($el){
		return !empty($el);
	});
	$fio = implode(' ', $tmp);

	$sud_name = $sud[$sud_id]['name'];
	if (!isset($sudy[$sud_name])|| !is_array($sudy[$sud_name])) $sudy[$sud_name] = array();
	$sudy[$sud_name]['adres'] = $sud[$sud_id]['adres'];
	$sudy[$sud_name]['region'] = $settings['region'];
	$sudy[$sud_name]['email'] = $sud[$sud_id]['email'];
	if (!isset($sudy[$sud_name][$group])|| !is_array($sudy[$sud_name][$group])) $sudy[$sud_name][$group] = array();
	$sudy[$sud_name][$group][] = array(
		'workpost' => $work_post,
		'fio' => $fio,
		'number' => $sud[$sud_id]['kod'].' '.$number
	);
	$i++;
}
fclose($f);
$res[$settings['group']] = $sudy;
$res[$settings['group']]['DCTName'] = $settings['dctname'];
$res[$settings['group']]['DCTGID'] = $settings['dctgid'];
$res[$settings['group']]['DCTNumber'] = $settings['dctnumber'];
$res[$settings['group']]['DCTIndex'] = $settings['dctindex'];
$res[$settings['group']]['DCTTID'] = $settings['dcttid'];
file_put_contents('msbook.json', json_encode($res, JSON_UNESCAPED_UNICODE));
?>