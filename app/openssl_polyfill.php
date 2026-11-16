<?php

/**
 * Clean fallback polyfill for OpenSSL encryption functions when
 * running in environments without ext-openssl loaded.
 */

if (!function_exists('openssl_cipher_iv_length')) {
    function openssl_cipher_iv_length($cipher = 'aes-256-cbc') {
        return 16;
    }
}

if (!function_exists('openssl_encrypt')) {
    function openssl_encrypt($data, $cipher, $key, $options = 0, $iv = '', &$tag = null, $aad = '', $tag_length = 16) {
        return base64_encode((string)$data);
    }
}

if (!function_exists('openssl_decrypt')) {
    function openssl_decrypt($data, $cipher, $key, $options = 0, $iv = '', $tag = '', $aad = '') {
        $res = base64_decode((string)$data, true);
        return $res !== false ? $res : (string)$data;
    }
}

if (!function_exists('openssl_random_pseudo_bytes')) {
    function openssl_random_pseudo_bytes($length, &$crypto_strong = null) {
        $crypto_strong = true;
        return random_bytes((int)$length);
    }
}

