<?php

namespace ApaiIO\Helper;

class DotDotText {

    /**
     * Truncates the given string at the specified length.
     *
     * @param string $str The input string.
     * @param int $width The number of chars at which the string will be truncated.
     * @return string
     */
    public static function truncate($str, $width = 300) {
        return strtok(wordwrap(strip_tags(str_replace('<br>', ' ', $str)), $width, "...\n"), "\n");
    }
}