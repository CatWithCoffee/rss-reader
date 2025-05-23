<?php

if (!function_exists('hex2rgb')) {
    function hex2rgb($hex, $alpha = 1) {
        $hex = str_replace('#', '', $hex);
        $length = strlen($hex);
        $rgb = [
            'r' => hexdec($length == 6 ? substr($hex, 0, 2) : ($length == 3 ? str_repeat(substr($hex, 0, 1), 2) : 0)),
            'g' => hexdec($length == 6 ? substr($hex, 2, 2) : ($length == 3 ? str_repeat(substr($hex, 1, 1), 2) : 0)),
            'b' => hexdec($length == 6 ? substr($hex, 4, 2) : ($length == 3 ? str_repeat(substr($hex, 2, 1), 2) : 0))
        ];
        return "{$rgb['r']}, {$rgb['g']}, {$rgb['b']}, $alpha";
    }
}