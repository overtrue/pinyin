<?php 
$pinyin_data = file_get_contents("./src/data/dict.php");
$pinyin_data = json_decode($pinyin_data, true);
$content = file_get_contents("./hotWord.data");
$arr = explode(" ", $content);
$clicp_data = "{\n";

foreach ($pinyin_data as $key => $value) {
	if (!in_array($key, $arr) && mb_strlen($key, "utf-8") == 1) {
		$clicp_data .= "\t\"$key\": \"$value\",\n";
	}
}

$clicp_data .= "}";

file_put_contents( __DIR__ ."/src/data/nothot.php", $clicp_data);

 ?>