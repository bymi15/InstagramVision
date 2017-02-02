<?php
require_once 'config.php';
require_once "/lib/random_compat/lib/random.php";

function generateTokenKey(){
    $data = random_bytes(16) . KEY_VAL;
    $key = hash('sha256', $data);
    return $key;
}

function encryptData($data, $key){
    $iv = substr(hash('sha256', random_bytes(16)), 0, 16);
    $ciphertext = openssl_encrypt($data, ENC_METHOD, $key, 0, $iv);
    return base64_encode($ciphertext . ':' . $iv);
}

function decryptData($data, $key){
    $str = base64_decode($data);
    $parts = explode(':', $str);
    $plaintext = openssl_decrypt($parts[0], ENC_METHOD, $key, 0, $parts[1]);
    return $plaintext;
}
?>
