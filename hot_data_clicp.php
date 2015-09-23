<?php 
$pinyin_data = file_get_contents("./src/data/dict.php");
$pinyin_data = json_decode($pinyin_data, true);
$content = file_get_contents("./hotWord.data");
$arr = explode(" ", $content);
$clicp_data = "{\n";

foreach ($arr as $key => $value) {
	if (isset($pinyin_data[$value])) {
		$clicp_data .= "\t\"$value\": \"$pinyin_data[$value]\",\n";
	}
}

$clicp_data .= "}";

file_put_contents( __DIR__ ."/src/data/hot.php", $clicp_data);

 ?>