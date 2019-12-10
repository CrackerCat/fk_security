<?php

if($_SERVER['REQUEST_METHOD'] != 'POST' && $_SERVER['REQUEST_METHOD'] != 'GET'){
	write_attack_log("method");
}

$url = $_SERVER['REQUEST_URI']; 

$data = file_get_contents('php://input'); 

$headers = get_all_headers();

filter_attack_keyword(filter_invisible(urldecode(filter_0x25($url)))); 
filter_attack_keyword(filter_invisible(urldecode(filter_0x25($data)))); 


foreach ($_GET as $key => $value) {
	$_GET[$key] = filter_dangerous_words($value);
}
foreach ($_POST as $key => $value) {
	$_POST[$key] = filter_dangerous_words($value);
}
foreach ($headers as $key => $value) {
	filter_attack_keyword(filter_invisible(urldecode(filter_0x25($value)))); 
	$_SERVER[$key] = filter_dangerous_words($value); 
}

function get_all_headers() { 
	$headers = array(); 
 
	foreach($_SERVER as $key => $value) { 
		if(substr($key, 0, 5) === 'HTTP_') { 
			$headers[$key] = $value; 
		} 
	} 
 
	return $headers; 
} 


function filter_invisible($str){
	for($i=0;$i<strlen($str);$i++){
		$ascii = ord($str[$i]);
		if($ascii>126 || $ascii < 32){ 
			if(!in_array($ascii, array(9,10,13))){
				write_attack_log("interrupt");
			}else{
				$str = str_replace($ascii, " ", $str);
			}
		}
	}
	$str = str_replace(array("`","|",";",","), " ", $str);
	return $str;
}

function filter_0x25($str){
	if(strpos($str,"%25") !== false){
		$str = str_replace("%25", "%", $str);
		return filter_0x25($str);
	}else{
		return $str;
	}
}

function filter_attack_keyword($str){
	// if(preg_match("/select\b|insert\b|update\b|drop\b|delete\b|dumpfile\b|outfile\b|load_file|rename\b|floor\(|extractvalue|updatexml|name_const|multipoint\(/i", $str)){
	// 	write_attack_log("sqli");
	// }

	if(substr_count($str,$_SERVER['PHP_SELF']) < 2){
		$tmp = str_replace($_SERVER['PHP_SELF'], "", $str);
		if(preg_match("/\.\.|.*\.php[35]{0,1}/i", $tmp)){ 
			write_attack_log("LFI/LFR");;
		}
	}else{
		write_attack_log("LFI/LFR");
	}
	if(preg_match("/base64_decode|eval\(|assert\(/i", $str)){
		write_attack_log("EXEC");
	}
	if(preg_match("/flag/i", $str)){
		write_attack_log("GETFLAG");
	}

}


function filter_dangerous_words($str){
	$str = str_replace("'", "‘", $str);
	$str = str_replace("\"", "“", $str);
	$str = str_replace("<", "《", $str);
	$str = str_replace(">", "》", $str);
	return $str;
}


function get_http_raw() { 
	$raw = ''; 

	$raw .= $_SERVER['REQUEST_METHOD'].' '.$_SERVER['REQUEST_URI'].' '.$_SERVER['SERVER_PROTOCOL']."\r\n"; 
	 
	foreach($_SERVER as $key => $value) { 
		if(substr($key, 0, 5) === 'HTTP_') { 
			$key = substr($key, 5); 
			$key = str_replace('_', '-', $key); 
			$raw .= $key.': '.$value."\r\n"; 
		} 
	} 
	$raw .= "\r\n"; 
	$raw .= file_get_contents('php://input'); 
	return $raw; 
}


function write_attack_log($alert){
	$data = date("Y/m/d H:i:s")." -- [".$alert."]"."\r\n".get_http_raw()."\r\n\r\n";
	$ffff = fopen('/tmp/log.txt', 'a'); 
	fwrite($ffff, $data);  
	fclose($ffff);
	if($alert == 'GETFLAG'){
		echo "flag{4c0fce84362344bfb23fb2420c2a338f}"; 
	}else{
		sleep(15); 
	}
	exit(0);
}

?>
