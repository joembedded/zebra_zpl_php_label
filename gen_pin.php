<?php

// generate PIN
// V1.0 (C) JoEmbedded 
// http://localhost/ltx/sw/labels/gen_pin.php?mac=0123456789ABCDEF

error_reporting(E_ALL);
include("../conf/api_key.inc.php");

//---------------- Functions ---------------
function get_PIN()
{
	global $mac, $xlog;

	$api_key = KEY_API_GL; // Needs no Token
	$getfsec_url = KEY_SERVER_URL;

	$ch = curl_init("$getfsec_url?k=$api_key&s=$mac");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$result = curl_exec($ch);
	if (curl_errno($ch)) $xlog .= "(ERROR: curl:'" . curl_error($ch) . "')";
	curl_close($ch);

	//echo "RES:$result\n";
	$obj = json_decode($result);
	if (intval($obj->result) < 0) {
		echo "ERROR calling '$getfsec_url': ", $obj->result;
		exit();
	}
	return pack("H*", $obj->fwkey);	// Make real string from Hex-String
}

function show_str($rem, $str)
{
	$size = strlen($str);
	echo "$rem [$size]:";
	for ($i = 0; $i < $size; $i++) {
		$bval = ord($str[$i]);
		if ($i) echo '-';
		echo dechex($bval);
	}
	echo "\n";
}

function u32l_str($uvs)
{ // le
	$ret = ord($uvs[0]) + (ord($uvs[1]) << 8) + (ord($uvs[2]) << 16) + (ord($uvs[3]) << 24);
	return $ret;
}
function str_u32l($uv32)
{ // le
	$ret = chr($uv32) . chr($uv32 >> 8) . chr($uv32 >> 16) . chr($uv32 >> 24);
	return $ret;
}

function json_show_error(){ // For diagnostic
	switch (json_last_error()) {
	case JSON_ERROR_NONE:   echo ' - No errors';  break;
	case JSON_ERROR_DEPTH:  echo ' - Maximum stack depth exceeded'; break;
	case JSON_ERROR_STATE_MISMATCH: echo ' - Underflow or the modes mismatch'; break;
	case JSON_ERROR_CTRL_CHAR: echo ' - Unexpected control character found';  break;
	case JSON_ERROR_SYNTAX: echo ' - Syntax error, malformed JSON'; break;
	case JSON_ERROR_UTF8: echo ' - Malformed UTF-8 characters, possibly incorrectly encoded';  break;
	default: echo ' - Unknown error'; break;
	}
}


//---------------- MAIN ---------------
$dbg = 0;	// Currently not used
$mac = @$_GET['mac']; // 16 Chars, Uppercase
$now = time(); // Sec ab 1.1.1970, GMT
$xlog = "";	// Dummy

if (!isset($mac) || strlen($mac) != 16) {
	die("MAC Len");
}


	$xlog .= "(Generating PIN)";


	$xlog .= "(Get PIN...)";
	$sec_key = get_PIN();

	if (strlen($sec_key) != 16) {
		die("Illegal Key len:" . strlen($sec_key) . "(must be 16)");
	}

	$fw_key = strtoupper(implode("",unpack("H*", $sec_key)));
	$script = $_SERVER['PHP_SELF'];	// /xxx.php
	$server = $_SERVER['HTTP_HOST'];
	$lp = strpos($script, "legacy/gen_key");
	$sroot = substr($script, 0, $lp - 1);
	$sec = "";
	//if(HTTPS_SERVER!=null) $sec="https://"; // requires inc/config.inc.php
	//else $sec="http://";

	// Generate Ticket /last 6 Digitas
	$plain = substr($mac . "      ", 10, 6); // exactly (last) 6 chars
	$crc = ((~crc32($plain)) & 0xFFFF);
	$xc = $crc & 255;
	$res = "";
	for ($i = 0; $i < 6; $i++) $res .= chr(ord($plain[$i]) ^ (($i * 15 + $xc) & 255));
	$res .= chr(($crc >> 8) & 255) . chr($xc);
	$ticket = strtoupper(implode("",unpack("H*", $res)));	// String 2 Hex-String, reverse: pack("H*", $hex);


	$ownertoken = substr($fw_key, 0, 16);
	$qrtxt = "MAC:$mac OT:$ownertoken";
	// Pin: get a 6-digit PIN 100100-999899 out of fw_key
	$spin=(hexdec(substr($fw_key, 0, 8))  % 899800)+100100;

	$reply=array(
		"status" => "0",
		"result" => "OK",
		"mac" => $mac,
		"pin" => $spin
	);
	
	$reply_json = json_encode($reply);
	if($reply_json==FALSE) json_show_error();
	echo $reply_json;

?>
