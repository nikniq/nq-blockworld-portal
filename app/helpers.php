<?php

if (! function_exists('humanSize')) {
    function humanSize(int $bytes): string
    {
        if ($bytes >= 1 << 20) {
            return number_format($bytes / (1 << 20), 1).' MB';
        }
        if ($bytes >= 1 << 10) {
            return number_format($bytes / (1 << 10)).' KB';
        }

        return $bytes.' B';
    }
}
