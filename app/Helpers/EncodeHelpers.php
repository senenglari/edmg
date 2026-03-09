<?php
function encodedData($var) {
	$data     = [
                    "key" => $var,
                ];

    $enc        = encryptData(
                    $data,
                    ENCODE_ID,
                    ENCODE_SECRET
                );

   	return base64_encode($enc);
}

function decodedDataOld($var) {
	$dec = decryptData(
                base64_decode($var),
                ENCODE_ID,
                ENCODE_SECRET
            );

   	return $dec["key"];
}

function decodedData($var)
{
    $dec = decryptData(
        base64_decode($var),
        ENCODE_ID,
        ENCODE_SECRET
    );

    if (!is_array($dec) || !isset($dec['key'])) {
        return null;
    }

    return $dec['key'];
}

function encryptData(array $json_data, $cid, $secret) {
	return doubleEncrypt(strrev(time()) . '.' . json_encode($json_data), $cid, $secret);
}

function decryptData($hased_string, $cid, $secret) {
	$parsed_string = doubleDecrypt($hased_string, $cid, $secret);
	list($timestamp, $data) = array_pad(explode('.', $parsed_string, 2), 2, null);
	if (tsDiff(strrev($timestamp)) === true) {
		return json_decode($data, true);
	}
	return null;
}

function tsDiff_old($ts) {
	return abs($ts - time()) <= 48000000000;
}

function tsDiff($ts)
{
    if (!is_numeric($ts)) {
        return false;
    }

    $ts = (int) $ts;

    return abs($ts - time()) <= 48000000000;
}

function doubleEncrypt($string, $cid, $secret) {
	$result = '';
	$result = enc($string, $cid);
	$result = enc($result, $secret);
	return strtr(rtrim(base64_encode($result), '='), '+/', '-_');
}

function enc($string, $key) {
	$result = '';
	$strls = strlen($string);
	$strlk = strlen($key);
	for($i = 0; $i < $strls; $i++) {
		$char = substr($string, $i, 1);
		$keychar = substr($key, ($i % $strlk) - 1, 1);
		$char = chr((ord($char) + ord($keychar)) % 128);
		$result .= $char;
	}
	return $result;
}

function doubleDecrypt($string, $cid, $secret) {
	$result = base64_decode(strtr(str_pad($string, ceil(strlen($string) / 4) * 4, '=', STR_PAD_RIGHT), '-_', '+/'));
	$result = dec($result, $cid);
	$result = dec($result, $secret);
	return $result;
}

function dec($string, $key) {
	$result = '';
	$strls = strlen($string);
	$strlk = strlen($key);
	for($i = 0; $i < $strls; $i++) {
		$char = substr($string, $i, 1);
		$keychar = substr($key, ($i % $strlk) - 1, 1);
		$char = chr(((ord($char) - ord($keychar)) + 256) % 128);
		$result .= $char;
	}
	return $result;
}