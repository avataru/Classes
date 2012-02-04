<?php
/**
 * Character manipulation class
 *
 * Functions in this class can be used to transliterate characters with
 * diacritics to their roman equivalents (romanization) or to convert characters
 * to their HTML entities.
 *
 * LICENSE: CC BY-NC-SA 3.0
 * http://creativecommons.org/licenses/by-nc-sa/3.0/
 *
 *
 * @version 1.0
 * @author Mihai Zaharie <mihai@zaharie.ro>
 * @license http://creativecommons.org/licenses/by-nc-sa/3.0/   CC BY-NC-SA 3.0
 *
 *
 *
 * Note: not all the characters in the transliteration map have entities in the
 * entities map. More work is required for this.
 */

class Characters
{
    /**
     * Map of characters and their roman equivalents. Longer characters need to
     * be placed before shorter ones.
     *
     * Based on: http://en.wikipedia.org/wiki/Diacritic#Types
     *
     *
     * Accents
     * acute / apex: Áá Ǽǽ Ćć Éé Ǵǵ Íí Ḱḱ Ĺĺ Ḿḿ Ńń Óó Ǿǿ Ṕṕ Ŕŕ Śś Úú Ẃẃ Ýý Źź
     * grave: Àà Èè Ìì Ǹǹ Òò Ùù Ẁẁ Ỳỳ
     * circumflex: Ââ Ĉĉ Êê Ĝĝ Ĥĥ Îî Ĵĵ Ôô Ŝŝ Ûû Ŵŵ Ŷŷ Ẑẑ
     * caron / inverse circumflex: Ǎǎ Čč Ďď Ěě Ǧǧ Ȟȟ Ǐǐ J̌ǰ Ǩǩ Ľľ Ňň Ǒǒ Řř Šš Ťť Ǔǔ Žž Ǯǯ
     * double acute: Őő Űű
     * double grave: Ȁȁ Ȅȅ Ȉȉ Ȍȍ Ȑȑ Ȕȕ
     *
     * Dots
     * dot above: Ȧȧ Ḃḃ Ċċ Ḋḋ Ėė Ḟḟ Ġġ Ḣḣ İ Ṁṁ Ṅṅ Ȯȯ Ṗṗ Ṙṙ Ṡṡẛ Ṫṫ Ẇẇ Ẋẋ Ẏẏ Żż
     * dot below: Ạạ Ḅḅ Ḍḍ Ẹẹ Ḥḥ Ịị Ḳḳ Ḷḷ Ṃṃ Ṇṇ Ọọ Ṛṛ Ṣṣ Ṭṭ Ụụ Ṿṿ Ẉẉ Ỵỵ Ẓẓ
     * interpunct
     * tittle
     * trema / diaeresis / umlaut: Ää Ëë Ḧḧ Ïï N̈n̈ Öö T̈ẗ Üü Ẅẅ Ẍẍ Ÿÿ
     * colon
     *
     * Ring
     * ring: Åå Ůů W̊ẘ Y̊ẙ
     *
     * Vertical line
     * vertical line below:
     *
     * Macron / horizontal line
     * macron: Āā Ēē Ḡḡ Īī Ōō Ūū Ȳȳ Ǣǣ
     * macron below: Ḇḇ Ḏḏ ẖ Ḵḵ Ḻḻ Ṉṉ Ṟṟ Ṯṯ
     *
     * Overlays
     * bar
     * slash
     * stroke: Ⱥⱥ Ƀƀ Ȼȼ Đđ Ɇɇ Ǥǥ Ħħ Ɨɨ Ɉɉ Ꝁꝁ Łł Øø Ᵽᵽ Ɍɍ Ŧŧ Ʉʉ Ɏɏ Ƶƶ Ꝥꝥ
     *
     * Curves
     * breve: Ăă Ĕĕ Ğğ Ĭĭ Ŏŏ Ŭŭ
     * sicilicus
     * tilde: Ãã Ẽẽ Ĩĩ Ññ Õõ P̃p̃ Ũũ Ṽṽ Ỹỹ
     * titlo
     *
     * Curls above
     * apostrophe
     * hook: Ɓɓ Ƈƈ Ɗɗ Ƒƒ Ɠɠ ɦ Ƙƙ Ɱɱ Ƥƥ ʠ Ƭƭ Ʋʋ Ⱳⱳ Ƴƴ
     * horn: Ơơ Ưư
     *
     * Curls below
     * comma: D̦d̦ Șș Țț
     * cedilla: Çç Ḑḑ Ȩȩ Ģģ Ḩḩ Ķķ Ļļ Ņņ Ŗŗ Şş Ţţ
     * ogonek: Ąą Ą̈ą̈ Ęę Įį Ǫǫ Ǫ̈ǫ̈ Ųų
     *
     * Double marks
     * double breve
     * ligature tie
     * double circumflex
     * double macton
     * double tilde
     *
     * @var array
     */
    private static $transliterationMap = array(
        "\xC4\x84\xCC\x88" => 'A', "\xC4\x85\xCC\x88" => 'a',

        "\xC7\xAA\xCC\x88" => 'O', "\xC7\xAB\xCC\x88" => 'o',

        "\xE1\xBA\xA0" => 'A', "\xE1\xBA\xA1" => 'a', "\xE2\xB1\xA5" => 'a',

        "\xE1\xB8\x82" => 'B', "\xE1\xB8\x83" => 'b', "\xE1\xB8\x84" => 'B',
        "\xE1\xB8\x85" => 'b', "\xE1\xB8\x86" => 'B', "\xE1\xB8\x87" => 'b',

        "\xE1\xB8\x8A" => 'D', "\xE1\xB8\x8B" => 'd', "\xE1\xB8\x8C" => 'D',
        "\xE1\xB8\x8D" => 'd', "\xE1\xB8\x8E" => 'D', "\xE1\xB8\x8F" => 'd',
        "\xE1\xB8\x90" => 'D', "\xE1\xB8\x91" => 'd', "\x44\xCC\xA6" => 'D',
        "\x64\xCC\xA6" => 'd',

        "\xE1\xBA\xB8" => 'E', "\xE1\xBA\xB9" => 'e', "\xE1\xBA\xB8" => 'E',
        "\xE1\xBA\xB9" => 'e', "\xE1\xBA\xBC" => 'E', "\xE1\xBA\xBD" => 'e',

        "\xE1\xB8\x9E" => 'F', "\xE1\xB8\x9F" => 'f',

        "\xE1\xB8\xA0" => 'G', "\xE1\xB8\xA1" => 'g',

        "\xE1\xB8\xA2" => 'H', "\xE1\xB8\xA3" => 'h', "\xE1\xB8\xA4" => 'H',
        "\xE1\xB8\xA5" => 'h', "\xE1\xB8\xA6" => 'H', "\xE1\xB8\xA7" => 'h',
        "\xE1\xBA\x96" => 'h', "\xE1\xB8\xA8" => 'H', "\xE1\xB8\xA9" => 'h',

        "\xE1\xBB\x8A" => 'I', "\xE1\xBB\x8B" => 'i',

        "\x4A\xCC\x8C" => 'J',

        "\xE1\xB8\xB0" => 'K', "\xE1\xB8\xB1" => 'k', "\xE1\xB8\xB2" => 'K',
        "\xE1\xB8\xB3" => 'k', "\xE1\xB8\xB4" => 'K', "\xE1\xB8\xB5" => 'k',
        "\xEA\x9D\x80" => 'K', "\xEA\x9D\x81" => 'k',

        "\xE1\xB8\xB6" => 'L', "\xE1\xB8\xB7" => 'l', "\xE1\xB8\xBA" => 'L',
        "\xE1\xB8\xBB" => 'l',

        "\xE1\xB8\xBE" => 'M', "\xE1\xB8\xBF" => 'm', "\xE1\xB9\x80" => 'M',
        "\xE1\xB9\x81" => 'm', "\xE1\xB9\x82" => 'M', "\xE1\xB9\x83" => 'm',
        "\xE2\xB1\xAE" => 'M',

        "\xE1\xB9\x84" => 'N', "\xE1\xB9\x85" => 'n', "\xE1\xB9\x86" => 'N',
        "\xE1\xB9\x87" => 'n', "\x4E\xCC\x88" => 'N', "\x6E\xCC\x88" => 'n',
        "\xE1\xB9\x88" => 'N', "\xE1\xB9\x89" => 'n',

        "\xE1\xBB\x8C" => 'O', "\xE1\xBB\x8D" => 'o',

        "\xE1\xB9\x94" => 'P', "\xE1\xB9\x95" => 'p', "\xE1\xB9\x96" => 'P',
        "\xE1\xB9\x97" => 'p', "\xE2\xB1\xA3" => 'P', "\xE1\xB5\xBD" => 'p',
        "\x50\xCC\x83" => 'P', "\x70\xCC\x83" => 'p',

        "\xE1\xB9\x98" => 'R', "\xE1\xB9\x99" => 'r', "\xE1\xB9\x9A" => 'R',
        "\xE1\xB9\x9B" => 'r', "\xE1\xB9\x9E" => 'R', "\xE1\xB9\x9F" => 'r',

        "\xE1\xB9\xA0" => 'S', "\xE1\xB9\xA1" => 's', "\xE1\xBA\x9B" => 's',
        "\xE1\xB9\xA2" => 'S', "\xE1\xB9\xA3" => 's',

        "\xE1\xB9\xAA" => 'T', "\xE1\xB9\xAB" => 't', "\xE1\xB9\xAC" => 'T',
        "\xE1\xB9\xAD" => 't', "\x54\xCC\x88" => 'T', "\xE1\xBA\x97" => 't',
        "\xE1\xB9\xAE" => 'T', "\xE1\xB9\xAF" => 't',

        "\xEA\x9D\xA4" => 'Th', "\xEA\x9D\xA5" => 'th',

        "\xE1\xBB\xA4" => 'U', "\xE1\xBB\xA5" => 'u',

        "\xE1\xB9\xBE" => 'V', "\xE1\xB9\xBF" => 'v', "\xE1\xB9\xBC" => 'V',
        "\xE1\xB9\xBD" => 'v',

        "\xE1\xBA\x82" => 'W', "\xE1\xBA\x83" => 'w', "\xE1\xBA\x80" => 'W',
        "\xE1\xBA\x81" => 'w', "\xE1\xBA\x86" => 'W', "\xE1\xBA\x87" => 'w',
        "\xE1\xBA\x88" => 'W', "\xE1\xBA\x89" => 'w', "\xE1\xBA\x84" => 'W',
        "\xE1\xBA\x85" => 'w', "\x57\xCC\x8A" => 'W', "\xE1\xBA\x98" => 'w',
        "\xE2\xB1\xB2" => 'W', "\xE2\xB1\xB3" => 'w',

        "\xE1\xBA\x8A" => 'X', "\xE1\xBA\x8B" => 'x', "\xE1\xBA\x8C" => 'X',
        "\xE1\xBA\x8D" => 'x',

        "\xE1\xBB\xB2" => 'Y', "\xE1\xBB\xB3" => 'y', "\xE1\xBA\x8E" => 'Y',
        "\xE1\xBA\x8F" => 'y', "\xE1\xBB\xB4" => 'Y', "\xE1\xBB\xB5" => 'y',
        "\x59\xCC\x8A" => 'Y', "\xE1\xBA\x99" => 'y', "\xE1\xBB\xB8" => 'Y',
        "\xE1\xBB\xB9" => 'y',

        "\xE1\xBA\x90" => 'Z', "\xE1\xBA\x91" => 'z', "\xE1\xBA\x92" => 'Z',
        "\xE1\xBA\x93" => 'z',

        "\xC3\x81" => 'A', "\xC3\xA1" => 'a', "\xC3\x80" => 'A', "\xC3\xA0" => 'a',
        "\xC3\x82" => 'A', "\xC3\xA2" => 'a', "\xC7\x8D" => 'A', "\xC7\x8E" => 'a',
        "\xC8\x80" => 'A', "\xC8\x81" => 'a', "\xC8\xA6" => 'A', "\xC8\xA7" => 'a',
        "\xC3\x84" => 'A', "\xC3\xA4" => 'a', "\xC3\x85" => 'A', "\xC3\xA5" => 'a',
        "\xC4\x80" => 'A', "\xC4\x81" => 'a', "\xC8\xBA" => 'A', "\xC4\x82" => 'A',
        "\xC4\x83" => 'a', "\xC3\x83" => 'A', "\xC3\xA3" => 'a', "\xC4\x84" => 'A',
        "\xC4\x85" => 'a',

        "\xC7\xBC" => 'Ae', "\xC7\xBD" => 'ae', "\xC7\xA2" => 'Ae', "\xC7\xA3" => 'ae',

        "\xC9\x83" => 'B', "\xC6\x80" => 'b', "\xC6\x81" => 'B', "\xC9\x93" => 'b',

        "\xC4\x86" => 'C', "\xC4\x87" => 'c', "\xC4\x88" => 'C', "\xC4\x89" => 'c',
        "\xC4\x8C" => 'C', "\xC4\x8D" => 'c', "\xC4\x8A" => 'C', "\xC4\x8B" => 'c',
        "\xC8\xBB" => 'C', "\xC8\xBC" => 'c', "\xC6\x87" => 'C', "\xC6\x88" => 'c',
        "\xC3\x87" => 'C', "\xC3\xA7" => 'c',

        "\xC4\x8E" => 'D', "\xC4\x8F" => 'd', "\xC4\x90" => 'D', "\xC4\x91" => 'd',
        "\xC6\x8A" => 'D', "\xC9\x97" => 'd', "\xC3\x90" => 'D', "\xC3\xB0" => 'd',

        "\xC3\x89" => 'E', "\xC3\xA9" => 'e', "\xC3\x88" => 'E', "\xC3\xA8" => 'e',
        "\xC3\x8A" => 'E', "\xC3\xAA" => 'e', "\xC4\x9A" => 'E', "\xC4\x9B" => 'e',
        "\xC8\x84" => 'E', "\xC8\x85" => 'e', "\xC4\x96" => 'E', "\xC4\x97" => 'e',
        "\xC3\x8B" => 'E', "\xC3\xAB" => 'e', "\xC4\x92" => 'E', "\xC4\x93" => 'e',
        "\xC9\x86" => 'E', "\xC9\x87" => 'e', "\xC4\x94" => 'E', "\xC4\x95" => 'e',
        "\xC8\xA8" => 'E', "\xC8\xA9" => 'e', "\xC4\x98" => 'E', "\xC4\x99" => 'e',

        "\xC6\x91" => 'F', "\xC6\x92" => 'f',

        "\xC7\xB4" => 'G', "\xC7\xB5" => 'g', "\xC4\x9C" => 'G', "\xC4\x9D" => 'g',
        "\xC7\xA6" => 'G', "\xC7\xA7" => 'g', "\xC4\xA0" => 'G', "\xC4\xA1" => 'g',
        "\xC7\xA4" => 'G', "\xC7\xA5" => 'g', "\xC4\x9E" => 'G', "\xC4\x9F" => 'g',
        "\xC6\x93" => 'G', "\xC9\xA0" => 'g', "\xC4\xA2" => 'G', "\xC4\xA3" => 'g',

        "\xC4\xA4" => 'H', "\xC4\xA5" => 'h', "\xC8\x9E" => 'H', "\xC8\x9F" => 'h',
        "\xC4\xA6" => 'H', "\xC4\xA7" => 'h', "\xC9\xA6" => 'h',

        "\xC3\x8D" => 'I', "\xC3\xAD" => 'i', "\xC3\x8C" => 'I', "\xC3\xAC" => 'i',
        "\xC3\x8E" => 'I', "\xC3\xAE" => 'i', "\xC7\x8F" => 'I', "\xC7\x90" => 'i',
        "\xC8\x88" => 'I', "\xC8\x89" => 'i', "\xC4\xB0" => 'I', "\xC3\x8F" => 'I',
        "\xC3\xAF" => 'i', "\xC4\xAA" => 'I', "\xC4\xAB" => 'i', "\xC6\x97" => 'I',
        "\xC9\xA8" => 'i', "\xC4\xAC" => 'I', "\xC4\xAD" => 'i', "\xC4\xA8" => 'I',
        "\xC4\xA9" => 'i', "\xC4\xAE" => 'I', "\xC4\xAF" => 'i',

        "\xC4\xB4" => 'J', "\xC4\xB5" => 'j', "\xC7\xB0" => 'j', "\xC9\x88" => 'J',
        "\xC9\x89" => 'j',

        "\xC7\xA8" => 'K', "\xC7\xA9" => 'k', "\xC6\x98" => 'K', "\xC6\x99" => 'k',
        "\xC4\xB6" => 'K', "\xC4\xB7" => 'k',

        "\xC4\xB9" => 'L', "\xC4\xBA" => 'l', "\xC4\xBD" => 'L', "\xC4\xBE" => 'l',
        "\xC5\x81" => 'L', "\xC5\x82" => 'l', "\xC4\xBB" => 'L', "\xC4\xBC" => 'l',

        "\xC9\xB1" => 'm',

        "\xC5\x83" => 'N', "\xC5\x84" => 'n', "\xC7\xB8" => 'N', "\xC7\xB9" => 'n',
        "\xC5\x87" => 'N', "\xC5\x88" => 'n', "\xC3\x91" => 'N', "\xC3\xB1" => 'n',
        "\xC5\x85" => 'N', "\xC5\x86" => 'n',

        "\xC3\x93" => 'O', "\xC3\xB3" => 'o', "\xC7\xBE" => 'O', "\xC7\xBF" => 'o',
        "\xC3\x92" => 'O', "\xC3\xB2" => 'o', "\xC3\x94" => 'O', "\xC3\xB4" => 'o',
        "\xC7\x91" => 'O', "\xC7\x92" => 'o', "\xC5\x90" => 'O', "\xC5\x91" => 'o',
        "\xC8\x8C" => 'O', "\xC8\x8D" => 'o', "\xC8\xAE" => 'O', "\xC8\xAF" => 'o',
        "\xC3\x96" => 'O', "\xC3\xB6" => 'o', "\xC5\x8C" => 'O', "\xC5\x8D" => 'o',
        "\xC3\x98" => 'O', "\xC3\xB8" => 'o', "\xC5\x8E" => 'O', "\xC5\x8F" => 'o',
        "\xC3\x95" => 'O', "\xC3\xB5" => 'o', "\xC6\xA0" => 'O', "\xC6\xA1" => 'o',
        "\xC7\xAA" => 'O', "\xC7\xAB" => 'o',

        "\xC6\xA4" => 'P', "\xC6\xA5" => 'p',

        "\xCA\xA0" => 'q',

        "\xC5\x94" => 'R', "\xC5\x95" => 'r', "\xC5\x98" => 'R', "\xC5\x99" => 'r',
        "\xC8\x90" => 'R', "\xC8\x91" => 'r', "\xC9\x8C" => 'R', "\xC9\x8D" => 'r',
        "\xC5\x96" => 'R', "\xC5\x97" => 'r',

        "\xC5\x9A" => 'S', "\xC5\x9B" => 's', "\xC5\x9C" => 'S', "\xC5\x9D" => 's',
        "\xC5\x9E" => 'S', "\xC5\x9F" => 's', "\xC8\x98" => 'S', "\xC8\x99" => 's',
        "\xC5\xA0" => 'S', "\xC5\xA1" => 's',

        "\xC5\xA4" => 'T', "\xC5\xA5" => 't', "\xC5\xA6" => 'T', "\xC5\xA7" => 't',
        "\xC6\xAC" => 'T', "\xC6\xAD" => 't', "\xC5\xA2" => 'T', "\xC5\xA3" => 't',
        "\xC8\x9A" => 'T', "\xC8\x9B" => 't',

        "\xC3\x9A" => 'U', "\xC3\xBA" => 'u', "\xC3\x99" => 'U', "\xC3\xB9" => 'u',
        "\xC3\x9B" => 'U', "\xC3\xBB" => 'u', "\xC7\x93" => 'U', "\xC7\x94" => 'u',
        "\xC5\xB0" => 'U', "\xC5\xB1" => 'u', "\xC8\x94" => 'U', "\xC8\x95" => 'u',
        "\xC3\x9C" => 'U', "\xC3\xBC" => 'u', "\xC5\xAE" => 'U', "\xC5\xAF" => 'u',
        "\xC5\xAA" => 'U', "\xC5\xAB" => 'u',

        "\xC9\x84" => 'U', "\xCA\x89" => 'u', "\xC5\xAC" => 'U', "\xC5\xAD" => 'u',
        "\xC5\xA8" => 'U', "\xC5\xA9" => 'u', "\xC6\xB2" => 'U', "\xCA\x8B" => 'u',
        "\xC6\xAF" => 'U', "\xC6\xB0" => 'u', "\xC5\xB2" => 'U', "\xC5\xB3" => 'u',

        "\xC5\xB4" => 'W', "\xC5\xB5" => 'w',

        "\xC3\x9D" => 'Y', "\xC3\xBD" => 'y', "\xC5\xB6" => 'Y', "\xC5\xB7" => 'y',
        "\xC5\xB8" => 'Y', "\xC3\xBF" => 'y', "\xC8\xB2" => 'Y', "\xC8\xB3" => 'y',
        "\xC9\x8E" => 'Y', "\xC9\x8F" => 'y', "\xC6\xB3" => 'Y', "\xC6\xB4" => 'y',

        "\xC5\xB9" => 'Z', "\xC5\xBA" => 'z', "\xC5\xBD" => 'Z', "\xC5\xBE" => 'z',
        "\xC7\xAE" => 'Z', "\xC7\xAF" => 'z', "\xC5\xBB" => 'Z', "\xC5\xBC" => 'z',
        "\xC6\xB5" => 'Z', "\xC6\xB6" => 'z'
    );

    /**
     * Map of characters and their roman equivalents. Longer characters need to
     * be placed before shorter ones.
     *
     * @var array
     */
    private static $symbolsTransliterationMap = array(
        // Punctuation
        "\xC2\xAB" => '<<', "\xC2\xBB" => '>>',
        "\xE2\x9F\xA8" => '<', "\xE2\x9F\xA9" => '>', "\xE2\x80\x92" => '-',
        "\xE2\x80\x93" => '-', "\xE2\x80\x94" => '-', "\xE2\x80\x95" => '-',
        "\xE2\x80\xA6" => '...', "\xE2\x80\x90" => '-', "\xE2\x80\x98" => "'",
        "\xE2\x80\x99" => "'", "\xE2\x80\x9A" => "'", "\xE2\x80\x9C" => '"',
        "\xE2\x80\x9D" => '"', "\xE2\x80\x9E" => '"', "\xE2\x81\x84" => '/',

        // Word dividers
        "\xC2\xB7" => ' ',
        "\xE2\x80\x82" => ' ', "\xE2\x80\x83" => ' ', "\xE2\x80\x89" => ' ',
        "\xE2\x90\xA0" => ' ', "\xE2\x90\xA2" => ' ', "\xE2\x90\xA3" => '_',

        // General typography
        "\xC2\xA9" => '(c)', "\xC2\xAE" => '(r)', "\xC2\xA1" => '!',
        "\xC2\xBF" => '?', "\xC2\xA6" => '|',
        "\xE2\x80\xA2" => '*', "\xE3\x80\x83" => '"', "\xE2\x84\x96" => 'No.',
        "\xE2\x80\xB2" => "'", "\xE2\x80\xB3" => '"', "\xE2\x80\xB4" => '"\'',
        "\xE2\x84\xA0" => 'sm', "\xE2\x84\x97" => '(p)', "\xE2\x84\xA2" => 'tm',

        // Currency
        "\xC2\xA2" => 'c', "\xC2\xA3" => 'GBP', "\xC2\xA5" => 'JPY',
        "\xC6\x92" => 'f', "\x24" => 'USD',
        "\xE2\x82\xB3" => 'ARA', "\xE0\xB8\xBF" => 'THB', "\xE2\x82\xB5" => 'GHS',
        "\xE2\x82\xA1" => 'CRC', "\xE2\x82\xA2" => 'BRZ', "\xE2\x82\xA0" => 'ECU',
        "\xE2\x82\xAB" => 'VND', "\xE0\xA7\xB3" => 'BDT', "\xE2\x82\xAF" => 'GRD',
        "\xE2\x82\xAC" => 'EUR', "\xE2\x82\xA3" => 'FRF', "\xE2\x82\xB2" => 'PYG',
        "\xE2\x82\xB4" => 'UAH', "\xE2\x82\xAD" => 'LAK', "\xE2\x84\xB3" => 'GGK',
        "\xE2\x82\xA5" => 'mil', "\xE2\x82\xA6" => 'NGN', "\xE2\x82\xA7" => 'ESP',
        "\xE2\x82\xB1" => 'PHP', "\xE2\x82\xB0" => 'Pf', "\xE2\x82\xB9" => 'INR',
        "\xE2\x82\xA8" => 'Rs', "\xE2\x82\xAA" => 'ILS', "\xE2\x82\xB8" => 'ZKT',
        "\xE2\x82\xAE" => 'MNT', "\xE2\x82\xA9" => 'KRW', "\xE1\x9F\x9B" => 'KHR',

        // Uncommon typography
        "\xE2\x80\xBD" => '!?', "\xE2\x81\x80" => '_',

        // Mathematic symbols
        "\xC2\xB9" => '1', "\xC2\xB2" => '2', "\xC2\xB3" => '3',
        "\xC2\xBC" => '1/4', "\xC2\xBD" => '1/2', "\xC2\xBE" => '3/4',
        "\xC2\xB1" => '+-', "\xC2\xB5" => 'u', "\xC3\x97" => '*',
        "\xE2\x84\x91" => 'I', "\xE2\x84\x98" => 'P', "\xE2\x84\x9C" => 'R',

        // Ligatures
        "\xC3\x86" => 'Ae', "\xC3\xA6" => 'ae', "\xC5\x92" => 'Oe',
        "\xC5\x93" => 'oe', "\xC3\x9F" => 'ss', "\xC3\x9E" => 'Th',
        "\xC3\xBE" => 'th',
        "\xE1\xBA\x9E" => 'Ss',

        // Greek letters
        "\xCE\x91" => 'A', "\xCE\x92" => 'B', "\xCE\x93" => 'G', "\xCE\x94" => 'D',
        "\xCE\x95" => 'E', "\xCE\x96" => 'Z', "\xCE\x97" => 'H', "\xCE\x98" => 'Th',
        "\xCE\x99" => 'I', "\xCE\x9A" => 'K', "\xCE\x9B" => 'L', "\xCE\x9C" => 'M',
        "\xCE\x9D" => 'N', "\xCE\x9E" => 'X', "\xCE\x9F" => 'O', "\xCE\xA0" => 'P',
        "\xCE\xA1" => 'R', "\xCE\xA3" => 'S', "\xCE\xA4" => 'T', "\xCE\xA5" => 'Y',
        "\xCE\xA6" => 'Ph', "\xCE\xA7" => 'Ch', "\xCE\xA8" => 'Ps', "\xCE\xA9" => 'O',
        "\xCE\xB1" => 'a', "\xCE\xB2" => 'b', "\xCE\xB3" => 'g', "\xCE\xB4" => 'd',
        "\xCE\xB5" => 'e', "\xCE\xB6" => 'z', "\xCE\xB7" => 'h', "\xCE\xB8" => 'th',
        "\xCE\xB9" => 'i', "\xCE\xBA" => 'k', "\xCE\xBB" => 'l', "\xCE\xBC" => 'm',
        "\xCE\xBD" => 'n', "\xCE\xBE" => 'x', "\xCE\xBF" => 'o', "\xCF\x80" => 'p',
        "\xCF\x81" => 'r', "\xCF\x82" => 's', "\xCF\x83" => 's', "\xCF\x84" => 't',
        "\xCF\x85" => 'u', "\xCF\x86" => 'ph', "\xCF\x87" => 'ch', "\xCF\x88" => 'ps',
        "\xCF\x89" => 'o', "\xCF\x91" => 'th', "\xCF\x92" => 'y', "\xCF\x96" => 'p'
    );

    /**
     * Map of characters entities and the actual characters.
     *
     * Based on: http://en.wikipedia.org/wiki/Punctuation and
     * http://en.wikipedia.org/wiki/List_of_XML_and_HTML_character_entity_references
     *
     * @var array
     */
    private static $entitiesMap = array(
        // Romanian characters
        "\xC4\x82" => '&#258;',  "\xC4\x83" => '&#259;',  "\xC3\x82" => '&Acirc;',
        "\xC3\xA2" => '&acirc;', "\xC3\x8E" => '&Icirc;', "\xC3\xAE" => '&icirc;',
        "\xC8\x98" => '&#536;',  "\xC8\x99" => '&#537;',  "\xC5\x9E" => '&#350;',
        "\xC5\x9F" => '&#351;',  "\xC8\x9A" => '&#538;',  "\xC8\x9B" => '&#539;',
        "\xC5\xA2" => '&#354;',  "\xC5\xA3" => '&#355;',

        // Punctuation
        "\x27" => '&apos;', // apostrophe
        "\x22" => '&quot;', // double quotation mark
        "\xC2\xAB" => '&laquo;', "\xC2\xBB" => '&raquo;', // guillemets
        "\xE2\x9F\xA8" => '&#10216;', "\xE2\x9F\xA9" => '&#10217;', // brakets
        "\xE2\x80\x92" => '&#8210;', "\xE2\x80\x93" => '&ndash;', "\xE2\x80\x94" => '&mdash;', "\xE2\x80\x95" => '&#8213;', // dash
        "\xE2\x80\xA6" => '&hellip;', // ellipsis
        "\xE2\x80\x90" => '&#8208;', // hyphen
        "\xE2\x80\x98" => '&lsquo;', "\xE2\x80\x99" => '&rsquo;', "\xE2\x80\x9A" => '&sbquo;', // single quotation marks
        "\xE2\x80\x9C" => '&ldquo;', "\xE2\x80\x9D" => '&rdquo;', "\xE2\x80\x9E" => '&bdquo;', // double quotation marks
        "\xE2\x81\x84" => '&frasl;', // solidus

        // Word dividers
        "\xC2\xB7" => '&middot;', // interpunct
        "\xE2\x80\x82" => '&ensp;', "\xE2\x80\x83" => '&emsp;', "\xE2\x80\x89" => '&thinsp;', "\xE2\x90\xA0" => '&#9248;', "\xE2\x90\xA2" => '&#9250;', "\xE2\x90\xA3" => '&#9251;', // space

        // General typography
        // "\x26" => '&amp;', // ampersand
        "\xC2\xA9" => '&copy;', // copyright symbol
        "\xC2\xB0" => '&deg;', // degree
        "\xC2\xA1" => '&iexcl;', // inverted exclamation mark
        "\xC2\xBF" => '&iquest;', // inverted question mark
        "\xC3\xB7" => '&divide;', // obelus
        "\xC2\xBA" => '&ordm;', "\xC2\xAA" => '&ordf;', // ordinal indicator
        "\xC2\xB6" => '&para;', // pilcrow
        "\xC2\xAE" => '&reg;', // registered trademark
        "\xC2\xA7" => '&sect;', // section sign
        "\xC2\xA6" => '&brvbar;', // vertical broken bar
        "\xE2\x80\xA2" => '&bull;', // bullet
        "\xE2\x80\xA0" => '&dagger;', "\xE2\x80\xA1" => '&Dagger;', // dagger
        "\xE3\x80\x83" => '&#12291;', // ditto mark
        "\xE2\x84\x96" => '&#8470;', // numero sign
        "\xE2\x80\xB0" => '&permil;', "\xE2\x80\xB1" => '&#8241;', // percent
        "\xE2\x80\xB2" => '&prime;', "\xE2\x80\xB3" => '&Prime;', "\xE2\x80\xB4" => '&#8244;', // prime
        "\xE2\x84\xA0" => '&#8480;', // service mark
        "\xE2\x84\x97" => '&#8471;', // sound recording copyright
        "\xE2\x84\xA2" => '&trade;', // trademark

        // Currency
        "\xC2\xA4" => '&curren;', // currency (generic)
        "\xC2\xA2" => '&cent;', "\xC2\xA3" => '&pound;', "\xC2\xA5" => '&yen;',
        "\xC6\x92" => '&fnof;',
        "\xE2\x82\xB3" => '&#8371;', "\xE0\xB8\xBF" => '&#3647;', "\xE2\x82\xB5" => '&#8373;',
        "\xE2\x82\xA1" => '&#8353;', "\xE2\x82\xA2" => '&#8354;', "\xE2\x82\xA0" => '&#8352;',
        "\xE2\x82\xAB" => '&#8363;', "\xE0\xA7\xB3" => '&#2547;', "\xE2\x82\xAF" => '&#8367;',
        "\xE2\x82\xAC" => '&euro;',  "\xE2\x82\xA3" => '&#8355;', "\xE2\x82\xB2" => '&#8370;',
        "\xE2\x82\xB4" => '&#8372;', "\xE2\x82\xAD" => '&#8365;', "\xE2\x84\xB3" => '&#8499;',
        "\xE2\x82\xA5" => '&#8357;', "\xE2\x82\xA6" => '&#8358;', "\xE2\x82\xA7" => '&#8359;',
        "\xE2\x82\xB1" => '&#8369;', "\xE2\x82\xB0" => '&#8368;', "\xE2\x82\xB9" => '&#8377;',
        "\xE2\x82\xA8" => '&#8360;', "\xE2\x82\xAA" => '&#8362;', "\xE2\x82\xB8" => '&#8376;',
        "\xE2\x82\xAE" => '&#8366;', "\xE2\x82\xA9" => '&#8361;', "\xE1\x9F\x9B" => '&#6107;',

        // Uncommon typography
        "\xE2\x81\x82" => '&#8258;', // asterism
        "\xE2\x8A\xA4" => '&#8868;', // tee
        "\xE2\x98\x9E" => '&#9758;', // index/fist
        "\xE2\x80\xBD" => '&#8253;', // interrobang
        "\xE2\xB8\xAE" => '&#11822;', // irony & sarcasm punctuation
        "\xE2\x80\xBB" => '&#8251;', // reference mark
        "\xE2\x81\x80" => '&#8256;', // tie

        // Mathematic symbols
        "\x3C" => '&lt;', "\x3E" => '&gt;', // less-than, greater-than
        "\xC2\xB9" => '&sup1;', // superscript one
        "\xC2\xB2" => '&sup2;', // superscript two
        "\xC2\xB3" => '&sup3;', // superscript three
        "\xC2\xBC" => '&frac14;', // vulgar fraction one quarter
        "\xC2\xBD" => '&frac12;', // vulgar fraction one half
        "\xC2\xBE" => '&frac34;', // vulgar fraction three quarters
        "\xC2\xAC" => '&not;', // not sign
        "\xC2\xB1" => '&plusmn;', // plus-minus sign
        "\xC2\xB5" => '&micro;', // micro sign
        "\xC3\x97" => '&times;', // multiplication sign
        "\xE2\x84\x91" => '&image;', // black-letter capital I (= imaginary part)
        "\xE2\x84\x98" => '&weierp;', // script capital P (= power set = Weierstrass p)
        "\xE2\x84\x9C" => '&real;', // black-letter capital R (= real part symbol)
        "\xE2\x84\xB5" => '&alefsym;', // alef symbol (= first transfinite cardinal)
        "\xE2\x86\x90" => '&larr;', // leftwards arrow
        "\xE2\x86\x91" => '&uarr;', // upwards arrow
        "\xE2\x86\x92" => '&rarr;', // rightwards arrow
        "\xE2\x86\x93" => '&darr;', // downwards arrow
        "\xE2\x86\x94" => '&harr;', // left right arrow
        "\xE2\x86\xB5" => '&crarr;', // downwards arrow with corner leftwards (= carriage return)
        "\xE2\x87\x90" => '&lArr;', // leftwards double arrow
        "\xE2\x87\x91" => '&uArr;', // upwards double arrow
        "\xE2\x87\x92" => '&rArr;', // rightwards double arrow
        "\xE2\x87\x93" => '&dArr;', // downwards double arrow
        "\xE2\x87\x94" => '&hArr;', // left right double arrow
        "\xE2\x88\x80" => '&forall;', // for all
        "\xE2\x88\x82" => '&part;', // partial differential
        "\xE2\x88\x83" => '&exist;', // there exists
        "\xE2\x88\x85" => '&empty;', // empty set (= null set = diameter)
        "\xE2\x88\x87" => '&nabla;', // nabla (= backward difference)
        "\xE2\x88\x88" => '&isin;', // element of
        "\xE2\x88\x89" => '&notin;', // not an element of
        "\xE2\x88\x8B" => '&ni;', // contains as member
        "\xE2\x88\x8F" => '&prod;', // n-ary product (= product sign)
        "\xE2\x88\x91" => '&sum;', // n-ary summation
        "\xE2\x88\x92" => '&minus;', // minus sign
        "\xE2\x88\x97" => '&lowast;', // asterisk operator
        "\xE2\x88\x9A" => '&radic;', // square root (= radical sign)
        "\xE2\x88\x9D" => '&prop;', // proportional to
        "\xE2\x88\x9E" => '&infin;', // infinity
        "\xE2\x88\xA0" => '&ang;', // angle
        "\xE2\x88\xA7" => '&and;', // logical and (= wedge)
        "\xE2\x88\xA8" => '&or;', // logical or (= vee)
        "\xE2\x88\xA9" => '&cap;', // intersection (= cap)
        "\xE2\x88\xAA" => '&cup;', // union (= cup)
        "\xE2\x88\xAB" => '&int;', // integral
        "\xE2\x88\xB4" => '&there4;', // therefore sign
        "\xE2\x88\xB5" => '&#8757;', // because sign
        "\xE2\x88\xBC" => '&sim;', // tilde operator (= varies with = similar to)
        "\xE2\x89\x85" => '&cong;', // congruent to
        "\xE2\x89\x88" => '&asymp;', // almost equal to (= asymptotic to)
        "\xE2\x89\xA0" => '&ne;', // not equal to
        "\xE2\x89\xA1" => '&equiv;', // identical to; sometimes used for 'equivalent to'
        "\xE2\x89\xA4" => '&le;', // less-than or equal to
        "\xE2\x89\xA5" => '&ge;', // greater-than or equal to
        "\xE2\x8A\x82" => '&sub;', // subset of
        "\xE2\x8A\x83" => '&sup;', // superset of
        "\xE2\x8A\x84" => '&nsub;', // not a subset of
        "\xE2\x8A\x86" => '&sube;', // subset of or equal to
        "\xE2\x8A\x87" => '&supe;', // superset of or equal to
        "\xE2\x8A\x95" => '&oplus;', // circled plus (= direct sum)
        "\xE2\x8A\x97" => '&otimes;', // circled times (= vector product)
        "\xE2\x8A\xA5" => '&perp;', // up tack (= orthogonal to = perpendicular)
        "\xE2\x8B\x85" => '&sdot;', // dot operator
        "\xE2\x8C\x88" => '&lceil;', // left ceiling (= APL upstile)
        "\xE2\x8C\x89" => '&rceil;', // right ceiling
        "\xE2\x8C\x8A" => '&lfloor;', // left floor (= APL downstile)
        "\xE2\x8C\x8B" => '&rfloor;', // right floor
        "\xE3\x80\x88" => '&lang;', // left-pointing angle bracket (= bra)
        "\xE3\x80\x89" => '&rang;', // right-pointing angle bracket (= ket)
        "\xE2\x97\x8A" => '&loz;', // lozenge

        // Accents
        "\xC2\xA8" => '&uml;', // diaeresis
        "\xC2\xAF" => '&macr;', // macron
        "\xC2\xB4" => '&acute;', // acute accent
        "\xC2\xB8" => '&cedil;', // cedilla
        "\xCB\x86" => '&circ;', // circumflex accent
        "\xE2\x80\xBE" => '&oline;', // overline

        // Accented letters
        "\xC3\x80" => '&Agrave;', "\xC3\x81" => '&Aacute;', "\xC3\x83" => '&Atilde;',
        "\xC3\x84" => '&Auml;', "\xC3\x85" => '&Aring;', "\xC3\x86" => '&AElig;',
        "\xC3\x87" => '&Ccedil;', "\xC3\x88" => '&Egrave;', "\xC3\x89" => '&Eacute;',
        "\xC3\x8A" => '&Ecirc;', "\xC3\x8B" => '&Euml;', "\xC3\x8C" => '&Igrave;',
        "\xC3\x8D" => '&Iacute;', "\xC3\x8F" => '&Iuml;', "\xC3\x90" => '&ETH;',
        "\xC3\x91" => '&Ntilde;', "\xC3\x92" => '&Ograve;', "\xC3\x93" => '&Oacute;',
        "\xC3\x94" => '&Ocirc;', "\xC3\x95" => '&Otilde;', "\xC3\x96" => '&Ouml;',
        "\xC3\x98" => '&Oslash;', "\xC3\x99" => '&Ugrave;', "\xC3\x9A" => '&Uacute;',
        "\xC3\x9B" => '&Ucirc;', "\xC3\x9C" => '&Uuml;', "\xC3\x9D" => '&Yacute;',
        "\xC3\x9E" => '&THORN;', "\xC3\x9F" => '&szlig;', "\xC3\xA0" => '&agrave;',
        "\xC3\xA1" => '&aacute;', "\xC3\xA3" => '&atilde;', "\xC3\xA4" => '&auml;',
        "\xC3\xA5" => '&aring;', "\xC3\xA6" => '&aelig;', "\xC3\xA7" => '&ccedil;',
        "\xC3\xA8" => '&egrave;', "\xC3\xA9" => '&eacute;', "\xC3\xAA" => '&ecirc;',
        "\xC3\xAB" => '&euml;', "\xC3\xAC" => '&igrave;', "\xC3\xAD" => '&iacute;',
        "\xC3\xAF" => '&iuml;', "\xC3\xB0" => '&eth;', "\xC3\xB1" => '&ntilde;',
        "\xC3\xB2" => '&ograve;', "\xC3\xB3" => '&oacute;', "\xC3\xB4" => '&ocirc;',
        "\xC3\xB5" => '&otilde;', "\xC3\xB6" => '&ouml;', "\xC3\xB8" => '&oslash;',
        "\xC3\xB9" => '&ugrave;', "\xC3\xBA" => '&uacute;', "\xC3\xBB" => '&ucirc;',
        "\xC3\xBC" => '&uuml;', "\xC3\xBD" => '&yacute;', "\xC3\xBE" => '&thorn;',
        "\xC3\xBF" => '&yuml;', "\xC5\x92" => '&OElig;', "\xC5\x93" => '&oelig;',
        "\xC5\xA0" => '&Scaron;', "\xC5\xA1" => '&scaron;', "\xC5\xB8" => '&Yuml;',
        "\xE1\xBA\x9E" => '&#7838;',

        // Greek letters
        "\xCE\x91" => '&Alpha;', "\xCE\x92" => '&Beta;', "\xCE\x93" => '&Gamma;',
        "\xCE\x94" => '&Delta;', "\xCE\x95" => '&Epsilon;', "\xCE\x96" => '&Zeta;',
        "\xCE\x97" => '&Eta;', "\xCE\x98" => '&Theta;', "\xCE\x99" => '&Iota;',
        "\xCE\x9A" => '&Kappa;', "\xCE\x9B" => '&Lambda;', "\xCE\x9C" => '&Mu;',
        "\xCE\x9D" => '&Nu;', "\xCE\x9E" => '&Xi;', "\xCE\x9F" => '&Omicron;',
        "\xCE\xA0" => '&Pi;', "\xCE\xA1" => '&Rho;', "\xCE\xA3" => '&Sigma;',
        "\xCE\xA4" => '&Tau;', "\xCE\xA5" => '&Upsilon;', "\xCE\xA6" => '&Phi;',
        "\xCE\xA7" => '&Chi;', "\xCE\xA8" => '&Psi;', "\xCE\xA9" => '&Omega;',
        "\xCE\xB1" => '&alpha;', "\xCE\xB2" => '&beta;', "\xCE\xB3" => '&gamma;',
        "\xCE\xB4" => '&delta;', "\xCE\xB5" => '&epsilon;', "\xCE\xB6" => '&zeta;',
        "\xCE\xB7" => '&eta;', "\xCE\xB8" => '&theta;', "\xCE\xB9" => '&iota;',
        "\xCE\xBA" => '&kappa;', "\xCE\xBB" => '&lambda;', "\xCE\xBC" => '&mu;',
        "\xCE\xBD" => '&nu;', "\xCE\xBE" => '&xi;', "\xCE\xBF" => '&omicron;',
        "\xCF\x80" => '&pi;', "\xCF\x81" => '&rho;', "\xCF\x82" => '&sigmaf;',
        "\xCF\x83" => '&sigma;', "\xCF\x84" => '&tau;', "\xCF\x85" => '&upsilon;',
        "\xCF\x86" => '&phi;', "\xCF\x87" => '&chi;', "\xCF\x88" => '&psi;',
        "\xCF\x89" => '&omega;', "\xCF\x91" => '&thetasym;', "\xCF\x92" => '&upsih;',
        "\xCF\x96" => '&piv;',

        // Other
        "\xE2\x99\xA0" => '&spades;', // black spade suit
        "\xE2\x99\xA3" => '&clubs;', // black club suit (= shamrock)
        "\xE2\x99\xA5" => '&hearts;', // black heart suit (= valentine)
        "\xE2\x99\xA6" => '&diams;', // black diamond suit
    );

    /**
     * Transforms the characters according to the map.
     *
     * @param array $map
     * @param string $text
     *
     * @return string
     */
    private static function mapTransform($text, $map)
    {
        // return strtr($text, $map);
        return str_replace(array_keys($map), array_values($map), $text);
    }

    /**
     * Replaces characters with diacritics with their roman equivalents.
     *
     * @param string $text Input text
     * @param bool $withSymbols "Transliterate" symbols too
     * @param bool $removeNonASCII Remove non ASCII characters
     *
     * @return string
     */
    public static function diacritics($text, $withSymbols = false, $removeNonASCII = false)
    {
        $transformed = $text;

        if ($withSymbols)
        {
            $transformed = self::mapTransform($transformed, self::$symbolsTransliterationMap);
        }

        $transformed = self::mapTransform($transformed, self::$transliterationMap);

        if ($removeNonASCII)
        {
            $transformed = preg_replace('/[^(\x20-\x7F)\x0A]*/','', $transformed);
        }

        return $transformed;
    }

    /**
     * Replaces characters with their HTML entities.
     *
     * @param string $text Input text
     *
     * @return string
     */
    public static function entities($text)
    {
        return self::mapTransform($text, self::$entitiesMap);
    }
}