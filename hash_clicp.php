<?php 
function getPath($str){
	// $ord = ord($str{0});
	// $path = substr($ord, 0, 1);
	// return $path;
	return substr( sha1($str), 0 ,1);
}

$pinyin_data = file_get_contents("./src/data/dict.php");
$pinyin_data = json_decode($pinyin_data, true);

$clicp_data = array();

foreach ($pinyin_data as $key => $value) {
	if (mb_strlen($key, "utf-8") !== 1) {
		$path = getPath($key);
		if (empty($clicp_data[$path])) {
			$clicp_data[$path] = "{\n";
		}
		$clicp_data[$path] .= "\t\"$key\": \"$value\",\n";
	}
}

foreach ($clicp_data as $key => $value) {
	$clicp_data[$key] .= "}";
	file_put_contents( __DIR__ ."/src/data/$key.php", $clicp_data[$key]);
}




 ?>