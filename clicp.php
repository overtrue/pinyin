<?php 
function getHash($str){
	$str = mb_substr($str, 0, 1, "UTF-8");
	$str = str_replace('%', '',  urlencode($str) );
	return $str;
}


$pinyin_data = file_get_contents("./src/data/dict.php");
$pinyin_data = json_decode($pinyin_data, true);

$clicp_data = array();
$base_path = __DIR__ . '/src/clicp_data/';
foreach ($pinyin_data as $key => $value) {
	$path = getHash( $key );
	if (!isset($clicp_data[$path])) {
		$clicp_data[$path] = "{\n";
	}	
	$clicp_data[$path] .= "\t\"$key\": \"$value\",\n";

}
foreach ($clicp_data as $key => $value) {
	$value = preg_replace('/,\n$/', "\n}", $value);
	file_put_contents( $base_path . $key . '.data', $value, FILE_APPEND);
}