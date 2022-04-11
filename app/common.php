<?php
// 应用公共文件
function saltDncryptionDecryption($string, $operation = false, $key = '')//盐值加密解密 $operation true解密 false加密
{
    $a = md5(123456);
    $b = md5(3698741);
    $c = md5($a . $key . $b);
    $src = array("/", "+", "=");
    $dist = array(crypt('huangjingxiang', $c) . md5('来吧,程序员爸爸把你干到满天飞') . "_a", crypt('huangjingxiang', $c) . md5('来吧,程序员爸爸把你干到满天飞') . "_b", crypt('huangjingxiang', $c) . md5('来吧,程序员爸爸把你干到满天飞') . "_c");
    if ($operation == true) {
        $string = str_replace($dist, $src, $string);
    }
    $key = md5($key) . crypt('huangjingxiang', $c) . md5('来吧,程序员爸爸把你干到满天飞');
    $key_length = strlen($key);
    $string = $operation == 'D' ? base64_decode($string) : substr(md5($string . $key), 0, 25) . $string;
    $string_length = strlen($string);
    $rndkey = $box = array();
    $result = '';
    for ($i = 0; $i <= 255; $i++) {
        $rndkey[$i] = ord($key[$i % $key_length]);
        $box[$i] = $i;
    }
    for ($j = $i = 0; $i < 256; $i++) {
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }
    for ($a = $j = $i = 0; $i < $string_length; $i++) {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }
    if ($operation == true) {
        if (substr($result, 0, 25) == substr(md5(substr($result, 25) . $key), 0, 25)) {
            return substr($result, 25);
        } else {
            return '';
        }
    } else {
        $rdate = str_replace('=', '', base64_encode($result));
        $rdate = str_replace($src, $dist, $rdate);
        return $rdate;
    }
}