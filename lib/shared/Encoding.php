<?php
/**************************************************************************
 *
 *   Copyright 2010 American Public Media Group
 *
 *   This file is part of AIR2.
 *
 *   AIR2 is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   AIR2 is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with AIR2.  If not, see <http://www.gnu.org/licenses/>.
 *
 *************************************************************************/

class Encoding {

    /**
     *
     *
     * @param unknown $u32
     * @return unknown
     */
    public static function uchr( $u32 ) {
        $utf8 = '';
        if ($u32 < 0x80) {
            $utf8 = chr($u32);
        }
        elseif ($u32 < 0x800) {
            $utf8 .= chr(($u32 >> 6) | 0xc0);
            $utf8 .= chr(($u32 & 0x3f) | 0x80);
        }
        elseif ($u32 < 0x10000) {
            $utf8 .= chr(($u32 >> 12) | 0xe0);
            $utf8 .= chr((($u32 >> 6) & 0x3f) | 0x80);
            $utf8 .= chr(($u32 & 0x3f) | 0x80);
        }
        elseif ($u32 < 0x110000) {
            $utf8 .= chr(($u32 >> 18) | 0xf0);
            $utf8 .= chr((($u32 >> 12) & 0x3f) | 0x80);
            $utf8 .= chr((($u32 >> 6) & 0x3f) | 0x80);
            $utf8 .= chr(($u32 & 0x3f) | 0x80);
        }
        return $utf8;
    }


    static $trans_map = array();
    static $trans_map_built = false;
    static $cp1252_map = array(
        "\xc2\x80" => "\xe2\x82\xac", /* EURO SIGN */
        "\xc2\x82" => "\xe2\x80\x9a", /* SINGLE LOW-9 QUOTATION MARK */
        "\xc2\x83" => "\xc6\x92",    /* LATIN SMALL LETTER F WITH HOOK */
        "\xc2\x84" => "\xe2\x80\x9e", /* DOUBLE LOW-9 QUOTATION MARK */
        "\xc2\x85" => "\xe2\x80\xa6", /* HORIZONTAL ELLIPSIS */
        "\xc2\x86" => "\xe2\x80\xa0", /* DAGGER */
        "\xc2\x87" => "\xe2\x80\xa1", /* DOUBLE DAGGER */
        "\xc2\x88" => "\xcb\x86",    /* MODIFIER LETTER CIRCUMFLEX ACCENT */
        "\xc2\x89" => "\xe2\x80\xb0", /* PER MILLE SIGN */
        "\xc2\x8a" => "\xc5\xa0",    /* LATIN CAPITAL LETTER S WITH CARON */
        "\xc2\x8b" => "\xe2\x80\xb9", /* SINGLE LEFT-POINTING ANGLE QUOTATION */
        "\xc2\x8c" => "\xc5\x92",    /* LATIN CAPITAL LIGATURE OE */
        "\xc2\x8e" => "\xc5\xbd",    /* LATIN CAPITAL LETTER Z WITH CARON */
        "\xc2\x91" => "\xe2\x80\x98", /* LEFT SINGLE QUOTATION MARK */
        "\xc2\x92" => "\xe2\x80\x99", /* RIGHT SINGLE QUOTATION MARK */
        "\xc2\x93" => "\xe2\x80\x9c", /* LEFT DOUBLE QUOTATION MARK */
        "\xc2\x94" => "\xe2\x80\x9d", /* RIGHT DOUBLE QUOTATION MARK */
        "\xc2\x95" => "\xe2\x80\xa2", /* BULLET */
        "\xc2\x96" => "\xe2\x80\x93", /* EN DASH */
        "\xc2\x97" => "\xe2\x80\x94", /* EM DASH */

        "\xc2\x98" => "\xcb\x9c",    /* SMALL TILDE */
        "\xc2\x99" => "\xe2\x84\xa2", /* TRADE MARK SIGN */
        "\xc2\x9a" => "\xc5\xa1",    /* LATIN SMALL LETTER S WITH CARON */
        "\xc2\x9b" => "\xe2\x80\xba", /* SINGLE RIGHT-POINTING ANGLE QUOTATION*/
        "\xc2\x9c" => "\xc5\x93",    /* LATIN SMALL LIGATURE OE */
        "\xc2\x9e" => "\xc5\xbe",    /* LATIN SMALL LETTER Z WITH CARON */
        "\xc2\x9f" => "\xc5\xb8"      /* LATIN CAPITAL LETTER Y WITH DIAERESIS*/
    );


    /**
     * Called by transliterate().
     * We must defer this till after compile time since
     * we use the uchr() method.
     */
    private static function _build_trans_map() {
        if (Encoding::$trans_map_built) {
            return;
        }
        //error_log("building trans_map");
        Encoding::$trans_map = array(
            Encoding::uchr(0x100) => "A",
            Encoding::uchr(0x101) => "a",
            Encoding::uchr(0x102) => "A",
            Encoding::uchr(0x103) => "a",
            Encoding::uchr(0x104) => "A",
            Encoding::uchr(0x105) => "a",
            Encoding::uchr(0x106) => "C",
            Encoding::uchr(0x107) => "c",
            Encoding::uchr(0x108) => "Ch",
            Encoding::uchr(0x109) => "ch",
            Encoding::uchr(0x10A) => "C",
            Encoding::uchr(0x10B) => "c",
            Encoding::uchr(0x10C) => "C",
            Encoding::uchr(0x10D) => "c",
            Encoding::uchr(0x10E) => "D",
            Encoding::uchr(0x10F) => "d",
            Encoding::uchr(0x110) => "D",
            Encoding::uchr(0x111) => "d",
            Encoding::uchr(0x112) => "E",
            Encoding::uchr(0x113) => "e",
            Encoding::uchr(0x114) => "E",
            Encoding::uchr(0x115) => "e",
            Encoding::uchr(0x116) => "E",
            Encoding::uchr(0x117) => "e",
            Encoding::uchr(0x118) => "E",
            Encoding::uchr(0x119) => "e",
            Encoding::uchr(0x11A) => "E",
            Encoding::uchr(0x11B) => "e",
            Encoding::uchr(0x11C) => "Gh",
            Encoding::uchr(0x11D) => "gh",
            Encoding::uchr(0x11E) => "G",
            Encoding::uchr(0x11F) => "g",
            Encoding::uchr(0x120) => "G",
            Encoding::uchr(0x121) => "g",
            Encoding::uchr(0x122) => "G",
            Encoding::uchr(0x123) => "g",
            Encoding::uchr(0x124) => "Hh",
            Encoding::uchr(0x125) => "hh",
            Encoding::uchr(0x126) => "H",
            Encoding::uchr(0x127) => "h",
            Encoding::uchr(0x128) => "I",
            Encoding::uchr(0x129) => "i",
            Encoding::uchr(0x12A) => "I",
            Encoding::uchr(0x12B) => "i",
            Encoding::uchr(0x12C) => "I",
            Encoding::uchr(0x12D) => "i",
            Encoding::uchr(0x12E) => "I",
            Encoding::uchr(0x12F) => "i",
            Encoding::uchr(0x130) => "I",
            Encoding::uchr(0x131) => "i",
            Encoding::uchr(0x132) => "IJ",
            Encoding::uchr(0x133) => "ij",
            Encoding::uchr(0x134) => "Jh",
            Encoding::uchr(0x135) => "jh",
            Encoding::uchr(0x136) => "K",
            Encoding::uchr(0x137) => "k",
            Encoding::uchr(0x138) => "k",
            Encoding::uchr(0x139) => "L",
            Encoding::uchr(0x13A) => "l",
            Encoding::uchr(0x13B) => "L",
            Encoding::uchr(0x13C) => "l",
            Encoding::uchr(0x13D) => "L",
            Encoding::uchr(0x13E) => "l",
            Encoding::uchr(0x13F) => "L".Encoding::uchr(0xB7),
            Encoding::uchr(0x140) => "l".Encoding::uchr(0xB7),
            Encoding::uchr(0x141) => "L",
            Encoding::uchr(0x142) => "l",
            Encoding::uchr(0x143) => "N",
            Encoding::uchr(0x144) => "n",
            Encoding::uchr(0x145) => "N",
            Encoding::uchr(0x146) => "n",
            Encoding::uchr(0x147) => "N",
            Encoding::uchr(0x148) => "n",
            Encoding::uchr(0x149) => "'n",
            Encoding::uchr(0x14A) => "NG",
            Encoding::uchr(0x14B) => "ng",
            Encoding::uchr(0x14C) => "O",
            Encoding::uchr(0x14D) => "o",
            Encoding::uchr(0x14E) => "O",
            Encoding::uchr(0x14F) => "o",
            Encoding::uchr(0x150) => "O",
            Encoding::uchr(0x151) => "o",
            Encoding::uchr(0x152) => "OE",
            Encoding::uchr(0x153) => "oe",
            Encoding::uchr(0x154) => "R",
            Encoding::uchr(0x155) => "r",
            Encoding::uchr(0x156) => "R",
            Encoding::uchr(0x157) => "r",
            Encoding::uchr(0x158) => "R",
            Encoding::uchr(0x159) => "r",
            Encoding::uchr(0x15A) => "S",
            Encoding::uchr(0x15B) => "s",
            Encoding::uchr(0x15C) => "Sh",
            Encoding::uchr(0x15D) => "sh",
            Encoding::uchr(0x15E) => "S",
            Encoding::uchr(0x15F) => "s",
            Encoding::uchr(0x160) => "S",
            Encoding::uchr(0x161) => "s",
            Encoding::uchr(0x162) => "T",
            Encoding::uchr(0x163) => "t",
            Encoding::uchr(0x164) => "T",
            Encoding::uchr(0x165) => "t",
            Encoding::uchr(0x166) => "T",
            Encoding::uchr(0x167) => "t",
            Encoding::uchr(0x168) => "U",
            Encoding::uchr(0x169) => "u",
            Encoding::uchr(0x16A) => "U",
            Encoding::uchr(0x16B) => "u",
            Encoding::uchr(0x16C) => "U",
            Encoding::uchr(0x16D) => "u",
            Encoding::uchr(0x16E) => "U",
            Encoding::uchr(0x16F) => "u",
            Encoding::uchr(0x170) => "U",
            Encoding::uchr(0x171) => "u",
            Encoding::uchr(0x172) => "U",
            Encoding::uchr(0x173) => "u",
            Encoding::uchr(0x174) => "W",
            Encoding::uchr(0x175) => "w",
            Encoding::uchr(0x176) => "Y",
            Encoding::uchr(0x177) => "y",
            Encoding::uchr(0x178) => "Y",
            Encoding::uchr(0x179) => "Z",
            Encoding::uchr(0x17A) => "z",
            Encoding::uchr(0x17B) => "Z",
            Encoding::uchr(0x17C) => "z",
            Encoding::uchr(0x17D) => "Z",
            Encoding::uchr(0x17E) => "z",
            Encoding::uchr(0x17F) => "s",
            Encoding::uchr(0x192) => "f",
            Encoding::uchr(0x218) => Encoding::uchr(0x15E),
            Encoding::uchr(0x219) => Encoding::uchr(0x15F),
            Encoding::uchr(0x21A) => Encoding::uchr(0x162),
            Encoding::uchr(0x21B) => Encoding::uchr(0x163),
            Encoding::uchr(0x2B9) => Encoding::uchr(0x2032),
            Encoding::uchr(0x2BB) => Encoding::uchr(0x2018),
            Encoding::uchr(0x2BC) => Encoding::uchr(0x2019),
            Encoding::uchr(0x2BD) => Encoding::uchr(0x201B),
            Encoding::uchr(0x2C6) => "^",
            Encoding::uchr(0x2C8) => "'",
            Encoding::uchr(0x2C9) => Encoding::uchr(0xAF),
            Encoding::uchr(0x2CC) => ",",
            Encoding::uchr(0x2D0) => ":",
            Encoding::uchr(0x2DA) => Encoding::uchr(0xB0),
            Encoding::uchr(0x2DC) => "~",
            Encoding::uchr(0x2DD) => "\"",
            Encoding::uchr(0x374) => "'",
            Encoding::uchr(0x375) => ",",
            Encoding::uchr(0x37E) => ";",
            Encoding::uchr(0x1E02) => "B",
            Encoding::uchr(0x1E03) => "b",
            Encoding::uchr(0x1E0A) => "D",
            Encoding::uchr(0x1E0B) => "d",
            Encoding::uchr(0x1E1E) => "F",
            Encoding::uchr(0x1E1F) => "f",
            Encoding::uchr(0x1E40) => "M",
            Encoding::uchr(0x1E41) => "m",
            Encoding::uchr(0x1E56) => "P",
            Encoding::uchr(0x1E57) => "p",
            Encoding::uchr(0x1E60) => "S",
            Encoding::uchr(0x1E61) => "s",
            Encoding::uchr(0x1E6A) => "T",
            Encoding::uchr(0x1E6B) => "t",
            Encoding::uchr(0x1E80) => "W",
            Encoding::uchr(0x1E81) => "w",
            Encoding::uchr(0x1E82) => "W",
            Encoding::uchr(0x1E83) => "w",
            Encoding::uchr(0x1E84) => "W",
            Encoding::uchr(0x1E85) => "w",
            Encoding::uchr(0x1EF2) => "Y",
            Encoding::uchr(0x1EF3) => "y",
            Encoding::uchr(0x2000) => " ",
            Encoding::uchr(0x2001) => "  ",
            Encoding::uchr(0x2002) => " ",
            Encoding::uchr(0x2003) => "  ",
            Encoding::uchr(0x2004) => " ",
            Encoding::uchr(0x2005) => " ",
            Encoding::uchr(0x2006) => " ",
            Encoding::uchr(0x2007) => " ",
            Encoding::uchr(0x2008) => " ",
            Encoding::uchr(0x2009) => " ",
            Encoding::uchr(0x200A) => "",
            Encoding::uchr(0x200B) => "",
            Encoding::uchr(0x200C) => "",
            Encoding::uchr(0x200D) => "",
            Encoding::uchr(0x200E) => "",
            Encoding::uchr(0x200F) => "",
            Encoding::uchr(0x2010) => "-",
            Encoding::uchr(0x2011) => "-",
            Encoding::uchr(0x2012) => "-",
            Encoding::uchr(0x2013) => "-",
            Encoding::uchr(0x2014) => "--",
            Encoding::uchr(0x2015) => "--",
            Encoding::uchr(0x2016) => "||",
            Encoding::uchr(0x2017) => "_",
            Encoding::uchr(0x2018) => "'",
            Encoding::uchr(0x2019) => "'",
            Encoding::uchr(0x201A) => "'",
            Encoding::uchr(0x201B) => "'",
            Encoding::uchr(0x201C) => "\"",
            Encoding::uchr(0x201D) => "\"",
            Encoding::uchr(0x201E) => "\"",
            Encoding::uchr(0x201F) => "\"",
            Encoding::uchr(0x2020) => "+",
            Encoding::uchr(0x2021) => "++",
            Encoding::uchr(0x2022) => "o",
            Encoding::uchr(0x2023) => ">",
            Encoding::uchr(0x2024) => ".",
            Encoding::uchr(0x2025) => "..",
            Encoding::uchr(0x2026) => "...",
            Encoding::uchr(0x2027) => "-",
            Encoding::uchr(0x202A) => "",
            Encoding::uchr(0x202B) => "",
            Encoding::uchr(0x202C) => "",
            Encoding::uchr(0x202D) => "",
            Encoding::uchr(0x202E) => "",
            Encoding::uchr(0x202F) => " ",
            Encoding::uchr(0x2030) => " 0/00",
            Encoding::uchr(0x2032) => "'",
            Encoding::uchr(0x2033) => "\"",
            Encoding::uchr(0x2034) => "'''",
            Encoding::uchr(0x2035) => "`",
            Encoding::uchr(0x2036) => "``",
            Encoding::uchr(0x2037) => "```",
            Encoding::uchr(0x2039) => "<",
            Encoding::uchr(0x203A) => ">",
            Encoding::uchr(0x203C) => "!!",
            Encoding::uchr(0x203E) => "-",
            Encoding::uchr(0x2043) => "-",
            Encoding::uchr(0x2044) => "/",
            Encoding::uchr(0x2048) => "?!",
            Encoding::uchr(0x2049) => "!?",
            Encoding::uchr(0x204A) => 7,
            Encoding::uchr(0x2070) => "^0",
            Encoding::uchr(0x2074) => "^4",
            Encoding::uchr(0x2075) => "^5",
            Encoding::uchr(0x2076) => "^6",
            Encoding::uchr(0x2077) => "^7",
            Encoding::uchr(0x2078) => "^8",
            Encoding::uchr(0x2079) => "^9",
            Encoding::uchr(0x207A) => "^+",
            Encoding::uchr(0x207B) => "^-",
            Encoding::uchr(0x207C) => "^=",
            Encoding::uchr(0x207D) => "^(",
            Encoding::uchr(0x207E) => "^)",
            Encoding::uchr(0x207F) => "^n",
            Encoding::uchr(0x2080) => "_0",
            Encoding::uchr(0x2081) => "_1",
            Encoding::uchr(0x2082) => "_2",
            Encoding::uchr(0x2083) => "_3",
            Encoding::uchr(0x2084) => "_4",
            Encoding::uchr(0x2085) => "_5",
            Encoding::uchr(0x2086) => "_6",
            Encoding::uchr(0x2087) => "_7",
            Encoding::uchr(0x2088) => "_8",
            Encoding::uchr(0x2089) => "_9",
            Encoding::uchr(0x208A) => "_+",
            Encoding::uchr(0x208B) => "_-",
            Encoding::uchr(0x208C) => "_=",
            Encoding::uchr(0x208D) => "_(",
            Encoding::uchr(0x208E) => "_)",
            Encoding::uchr(0x20AC) => "EUR",
            Encoding::uchr(0x2100) => "a/c",
            Encoding::uchr(0x2101) => "a/s",
            Encoding::uchr(0x2103) => Encoding::uchr(0xB0C),
            Encoding::uchr(0x2105) => "c/o",
            Encoding::uchr(0x2106) => "c/u",
            Encoding::uchr(0x2109) => Encoding::uchr(0xB0F),
            Encoding::uchr(0x2113) => "l",
            Encoding::uchr(0x2116) => "N".Encoding::uchr(0xBA),
            Encoding::uchr(0x2117) => "(P)",
            Encoding::uchr(0x2120) => "[SM]",
            Encoding::uchr(0x2121) => "TEL",
            Encoding::uchr(0x2122) => "[TM]",
            Encoding::uchr(0x2126) => Encoding::uchr(0x3A9),
            Encoding::uchr(0x212A) => "K",
            Encoding::uchr(0x212B) => Encoding::uchr(0xC5),
            Encoding::uchr(0x212E) => "e",
            Encoding::uchr(0x2153) => " 1/3",
            Encoding::uchr(0x2154) => " 2/3",
            Encoding::uchr(0x2155) => " 1/5",
            Encoding::uchr(0x2156) => " 2/5",
            Encoding::uchr(0x2157) => " 3/5",
            Encoding::uchr(0x2158) => " 4/5",
            Encoding::uchr(0x2159) => " 1/6",
            Encoding::uchr(0x215A) => " 5/6",
            Encoding::uchr(0x215B) => " 1/8",
            Encoding::uchr(0x215C) => " 3/8",
            Encoding::uchr(0x215D) => " 5/8",
            Encoding::uchr(0x215E) => " 7/8",
            Encoding::uchr(0x215F) => " 1/",
            Encoding::uchr(0x2160) => "I",
            Encoding::uchr(0x2161) => "II",
            Encoding::uchr(0x2162) => "III",
            Encoding::uchr(0x2163) => "IV",
            Encoding::uchr(0x2164) => "V",
            Encoding::uchr(0x2165) => "VI",
            Encoding::uchr(0x2166) => "VII",
            Encoding::uchr(0x2167) => "VIII",
            Encoding::uchr(0x2168) => "IX",
            Encoding::uchr(0x2169) => "X",
            Encoding::uchr(0x216A) => "XI",
            Encoding::uchr(0x216B) => "XII",
            Encoding::uchr(0x216C) => "L",
            Encoding::uchr(0x216D) => "C",
            Encoding::uchr(0x216E) => "D",
            Encoding::uchr(0x216F) => "M",
            Encoding::uchr(0x2170) => "i",
            Encoding::uchr(0x2171) => "ii",
            Encoding::uchr(0x2172) => "iii",
            Encoding::uchr(0x2173) => "iv",
            Encoding::uchr(0x2174) => "v",
            Encoding::uchr(0x2175) => "vi",
            Encoding::uchr(0x2176) => "vii",
            Encoding::uchr(0x2177) => "viii",
            Encoding::uchr(0x2178) => "ix",
            Encoding::uchr(0x2179) => "x",
            Encoding::uchr(0x217A) => "xi",
            Encoding::uchr(0x217B) => "xii",
            Encoding::uchr(0x217C) => "l",
            Encoding::uchr(0x217D) => "c",
            Encoding::uchr(0x217E) => "d",
            Encoding::uchr(0x217F) => "m",
            Encoding::uchr(0x2190) => "<-",
            Encoding::uchr(0x2191) => "^",
            Encoding::uchr(0x2192) => "->",
            Encoding::uchr(0x2193) => "v",
            Encoding::uchr(0x2194) => "<->",
            Encoding::uchr(0x21D0) => "<=",
            Encoding::uchr(0x21D2) => "=>",
            Encoding::uchr(0x21D4) => "<=>",
            Encoding::uchr(0x2212) => Encoding::uchr(0x2013),
            Encoding::uchr(0x2215) => "/",
            Encoding::uchr(0x2216) => "\\",
            Encoding::uchr(0x2217) => "*",
            Encoding::uchr(0x2218) => "o",
            Encoding::uchr(0x2219) => Encoding::uchr(0xB7),
            Encoding::uchr(0x221E) => "inf",
            Encoding::uchr(0x2223) => "|",
            Encoding::uchr(0x2225) => "||",
            Encoding::uchr(0x2236) => ":",
            Encoding::uchr(0x223C) => "~",
            Encoding::uchr(0x2260) => "/=",
            Encoding::uchr(0x2261) => "=",
            Encoding::uchr(0x2264) => "<=",
            Encoding::uchr(0x2265) => ">=",
            Encoding::uchr(0x226A) => "<<",
            Encoding::uchr(0x226B) => ">>",
            Encoding::uchr(0x2295) => "(+)",
            Encoding::uchr(0x2296) => "(-)",
            Encoding::uchr(0x2297) => "(x)",
            Encoding::uchr(0x2298) => "(/)",
            Encoding::uchr(0x22A2) => "|-",
            Encoding::uchr(0x22A3) => "-|",
            Encoding::uchr(0x22A6) => "|-",
            Encoding::uchr(0x22A7) => "|=",
            Encoding::uchr(0x22A8) => "|=",
            Encoding::uchr(0x22A9) => "||-",
            Encoding::uchr(0x22C5) => Encoding::uchr(0xB7),
            Encoding::uchr(0x22C6) => "*",
            Encoding::uchr(0x22D5) => "#",
            Encoding::uchr(0x22D8) => "<<<",
            Encoding::uchr(0x22D9) => ">>>",
            Encoding::uchr(0x22EF) => "...",
            Encoding::uchr(0x2329) => "<",
            Encoding::uchr(0x232A) => ">",
            Encoding::uchr(0x2400) => "NUL",
            Encoding::uchr(0x2401) => "SOH",
            Encoding::uchr(0x2402) => "STX",
            Encoding::uchr(0x2403) => "ETX",
            Encoding::uchr(0x2404) => "EOT",
            Encoding::uchr(0x2405) => "ENQ",
            Encoding::uchr(0x2406) => "ACK",
            Encoding::uchr(0x2407) => "BEL",
            Encoding::uchr(0x2408) => "BS",
            Encoding::uchr(0x2409) => "HT",
            Encoding::uchr(0x240A) => "LF",
            Encoding::uchr(0x240B) => "VT",
            Encoding::uchr(0x240C) => "FF",
            Encoding::uchr(0x240D) => "CR",
            Encoding::uchr(0x240E) => "SO",
            Encoding::uchr(0x240F) => "SI",
            Encoding::uchr(0x2410) => "DLE",
            Encoding::uchr(0x2411) => "DC1",
            Encoding::uchr(0x2412) => "DC2",
            Encoding::uchr(0x2413) => "DC3",
            Encoding::uchr(0x2414) => "DC4",
            Encoding::uchr(0x2415) => "NAK",
            Encoding::uchr(0x2416) => "SYN",
            Encoding::uchr(0x2417) => "ETB",
            Encoding::uchr(0x2418) => "CAN",
            Encoding::uchr(0x2419) => "EM",
            Encoding::uchr(0x241A) => "SUB",
            Encoding::uchr(0x241B) => "ESC",
            Encoding::uchr(0x241C) => "FS",
            Encoding::uchr(0x241D) => "GS",
            Encoding::uchr(0x241E) => "RS",
            Encoding::uchr(0x241F) => "US",
            Encoding::uchr(0x2420) => "SP",
            Encoding::uchr(0x2421) => "DEL",
            Encoding::uchr(0x2423) => "_",
            Encoding::uchr(0x2424) => "NL",
            Encoding::uchr(0x2425) => "///",
            Encoding::uchr(0x2426) => "?",
            Encoding::uchr(0x2460) => "(1)",
            Encoding::uchr(0x2461) => "(2)",
            Encoding::uchr(0x2462) => "(3)",
            Encoding::uchr(0x2463) => "(4)",
            Encoding::uchr(0x2464) => "(5)",
            Encoding::uchr(0x2465) => "(6)",
            Encoding::uchr(0x2466) => "(7)",
            Encoding::uchr(0x2467) => "(8)",
            Encoding::uchr(0x2468) => "(9)",
            Encoding::uchr(0x2469) => "(10)",
            Encoding::uchr(0x246A) => "(11)",
            Encoding::uchr(0x246B) => "(12)",
            Encoding::uchr(0x246C) => "(13)",
            Encoding::uchr(0x246D) => "(14)",
            Encoding::uchr(0x246E) => "(15)",
            Encoding::uchr(0x246F) => "(16)",
            Encoding::uchr(0x2470) => "(17)",
            Encoding::uchr(0x2471) => "(18)",
            Encoding::uchr(0x2472) => "(19)",
            Encoding::uchr(0x2473) => "(20)",
            Encoding::uchr(0x2474) => "(1)",
            Encoding::uchr(0x2475) => "(2)",
            Encoding::uchr(0x2476) => "(3)",
            Encoding::uchr(0x2477) => "(4)",
            Encoding::uchr(0x2478) => "(5)",
            Encoding::uchr(0x2479) => "(6)",
            Encoding::uchr(0x247A) => "(7)",
            Encoding::uchr(0x247B) => "(8)",
            Encoding::uchr(0x247C) => "(9)",
            Encoding::uchr(0x247D) => "(10)",
            Encoding::uchr(0x247E) => "(11)",
            Encoding::uchr(0x247F) => "(12)",
            Encoding::uchr(0x2480) => "(13)",
            Encoding::uchr(0x2481) => "(14)",
            Encoding::uchr(0x2482) => "(15)",
            Encoding::uchr(0x2483) => "(16)",
            Encoding::uchr(0x2484) => "(17)",
            Encoding::uchr(0x2485) => "(18)",
            Encoding::uchr(0x2486) => "(19)",
            Encoding::uchr(0x2487) => "(20)",
            Encoding::uchr(0x2488) => "1.",
            Encoding::uchr(0x2489) => "2.",
            Encoding::uchr(0x248A) => "3.",
            Encoding::uchr(0x248B) => "4.",
            Encoding::uchr(0x248C) => "5.",
            Encoding::uchr(0x248D) => "6.",
            Encoding::uchr(0x248E) => "7.",
            Encoding::uchr(0x248F) => "8.",
            Encoding::uchr(0x2490) => "9.",
            Encoding::uchr(0x2491) => "10.",
            Encoding::uchr(0x2492) => "11.",
            Encoding::uchr(0x2493) => "12.",
            Encoding::uchr(0x2494) => "13.",
            Encoding::uchr(0x2495) => "14.",
            Encoding::uchr(0x2496) => "15.",
            Encoding::uchr(0x2497) => "16.",
            Encoding::uchr(0x2498) => "17.",
            Encoding::uchr(0x2499) => "18.",
            Encoding::uchr(0x249A) => "19.",
            Encoding::uchr(0x249B) => "20.",
            Encoding::uchr(0x249C) => "(a)",
            Encoding::uchr(0x249D) => "(b)",
            Encoding::uchr(0x249E) => "(c)",
            Encoding::uchr(0x249F) => "(d)",
            Encoding::uchr(0x24A0) => "(e)",
            Encoding::uchr(0x24A1) => "(f)",
            Encoding::uchr(0x24A2) => "(g)",
            Encoding::uchr(0x24A3) => "(h)",
            Encoding::uchr(0x24A4) => "(i)",
            Encoding::uchr(0x24A5) => "(j)",
            Encoding::uchr(0x24A6) => "(k)",
            Encoding::uchr(0x24A7) => "(l)",
            Encoding::uchr(0x24A8) => "(m)",
            Encoding::uchr(0x24A9) => "(n)",
            Encoding::uchr(0x24AA) => "(o)",
            Encoding::uchr(0x24AB) => "(p)",
            Encoding::uchr(0x24AC) => "(q)",
            Encoding::uchr(0x24AD) => "(r)",
            Encoding::uchr(0x24AE) => "(s)",
            Encoding::uchr(0x24AF) => "(t)",
            Encoding::uchr(0x24B0) => "(u)",
            Encoding::uchr(0x24B1) => "(v)",
            Encoding::uchr(0x24B2) => "(w)",
            Encoding::uchr(0x24B3) => "(x)",
            Encoding::uchr(0x24B4) => "(y)",
            Encoding::uchr(0x24B5) => "(z)",
            Encoding::uchr(0x24B6) => "(A)",
            Encoding::uchr(0x24B7) => "(B)",
            Encoding::uchr(0x24B8) => "(C)",
            Encoding::uchr(0x24B9) => "(D)",
            Encoding::uchr(0x24BA) => "(E)",
            Encoding::uchr(0x24BB) => "(F)",
            Encoding::uchr(0x24BC) => "(G)",
            Encoding::uchr(0x24BD) => "(H)",
            Encoding::uchr(0x24BE) => "(I)",
            Encoding::uchr(0x24BF) => "(J)",
            Encoding::uchr(0x24C0) => "(K)",
            Encoding::uchr(0x24C1) => "(L)",
            Encoding::uchr(0x24C2) => "(M)",
            Encoding::uchr(0x24C3) => "(N)",
            Encoding::uchr(0x24C4) => "(O)",
            Encoding::uchr(0x24C5) => "(P)",
            Encoding::uchr(0x24C6) => "(Q)",
            Encoding::uchr(0x24C7) => "(R)",
            Encoding::uchr(0x24C8) => "(S)",
            Encoding::uchr(0x24C9) => "(T)",
            Encoding::uchr(0x24CA) => "(U)",
            Encoding::uchr(0x24CB) => "(V)",
            Encoding::uchr(0x24CC) => "(W)",
            Encoding::uchr(0x24CD) => "(X)",
            Encoding::uchr(0x24CE) => "(Y)",
            Encoding::uchr(0x24CF) => "(Z)",
            Encoding::uchr(0x24D0) => "(a)",
            Encoding::uchr(0x24D1) => "(b)",
            Encoding::uchr(0x24D2) => "(c)",
            Encoding::uchr(0x24D3) => "(d)",
            Encoding::uchr(0x24D4) => "(e)",
            Encoding::uchr(0x24D5) => "(f)",
            Encoding::uchr(0x24D6) => "(g)",
            Encoding::uchr(0x24D7) => "(h)",
            Encoding::uchr(0x24D8) => "(i)",
            Encoding::uchr(0x24D9) => "(j)",
            Encoding::uchr(0x24DA) => "(k)",
            Encoding::uchr(0x24DB) => "(l)",
            Encoding::uchr(0x24DC) => "(m)",
            Encoding::uchr(0x24DD) => "(n)",
            Encoding::uchr(0x24DE) => "(o)",
            Encoding::uchr(0x24DF) => "(p)",
            Encoding::uchr(0x24E0) => "(q)",
            Encoding::uchr(0x24E1) => "(r)",
            Encoding::uchr(0x24E2) => "(s)",
            Encoding::uchr(0x24E3) => "(t)",
            Encoding::uchr(0x24E4) => "(u)",
            Encoding::uchr(0x24E5) => "(v)",
            Encoding::uchr(0x24E6) => "(w)",
            Encoding::uchr(0x24E7) => "(x)",
            Encoding::uchr(0x24E8) => "(y)",
            Encoding::uchr(0x24E9) => "(z)",
            Encoding::uchr(0x24EA) => "(0)",
            Encoding::uchr(0x2500) => "-",
            Encoding::uchr(0x2501) => "=",
            Encoding::uchr(0x2502) => "|",
            Encoding::uchr(0x2503) => "|",
            Encoding::uchr(0x2504) => "-",
            Encoding::uchr(0x2505) => "=",
            Encoding::uchr(0x2506) => "|",
            Encoding::uchr(0x2507) => "|",
            Encoding::uchr(0x2508) => "-",
            Encoding::uchr(0x2509) => "=",
            Encoding::uchr(0x250A) => "|",
            Encoding::uchr(0x250B) => "|",
            Encoding::uchr(0x250C) => "+",
            Encoding::uchr(0x250D) => "+",
            Encoding::uchr(0x250E) => "+",
            Encoding::uchr(0x250F) => "+",
            Encoding::uchr(0x2510) => "+",
            Encoding::uchr(0x2511) => "+",
            Encoding::uchr(0x2512) => "+",
            Encoding::uchr(0x2513) => "+",
            Encoding::uchr(0x2514) => "+",
            Encoding::uchr(0x2515) => "+",
            Encoding::uchr(0x2516) => "+",
            Encoding::uchr(0x2517) => "+",
            Encoding::uchr(0x2518) => "+",
            Encoding::uchr(0x2519) => "+",
            Encoding::uchr(0x251A) => "+",
            Encoding::uchr(0x251B) => "+",
            Encoding::uchr(0x251C) => "+",
            Encoding::uchr(0x251D) => "+",
            Encoding::uchr(0x251E) => "+",
            Encoding::uchr(0x251F) => "+",
            Encoding::uchr(0x2520) => "+",
            Encoding::uchr(0x2521) => "+",
            Encoding::uchr(0x2522) => "+",
            Encoding::uchr(0x2523) => "+",
            Encoding::uchr(0x2524) => "+",
            Encoding::uchr(0x2525) => "+",
            Encoding::uchr(0x2526) => "+",
            Encoding::uchr(0x2527) => "+",
            Encoding::uchr(0x2528) => "+",
            Encoding::uchr(0x2529) => "+",
            Encoding::uchr(0x252A) => "+",
            Encoding::uchr(0x252B) => "+",
            Encoding::uchr(0x252C) => "+",
            Encoding::uchr(0x252D) => "+",
            Encoding::uchr(0x252E) => "+",
            Encoding::uchr(0x252F) => "+",
            Encoding::uchr(0x2530) => "+",
            Encoding::uchr(0x2531) => "+",
            Encoding::uchr(0x2532) => "+",
            Encoding::uchr(0x2533) => "+",
            Encoding::uchr(0x2534) => "+",
            Encoding::uchr(0x2535) => "+",
            Encoding::uchr(0x2536) => "+",
            Encoding::uchr(0x2537) => "+",
            Encoding::uchr(0x2538) => "+",
            Encoding::uchr(0x2539) => "+",
            Encoding::uchr(0x253A) => "+",
            Encoding::uchr(0x253B) => "+",
            Encoding::uchr(0x253C) => "+",
            Encoding::uchr(0x253D) => "+",
            Encoding::uchr(0x253E) => "+",
            Encoding::uchr(0x253F) => "+",
            Encoding::uchr(0x2540) => "+",
            Encoding::uchr(0x2541) => "+",
            Encoding::uchr(0x2542) => "+",
            Encoding::uchr(0x2543) => "+",
            Encoding::uchr(0x2544) => "+",
            Encoding::uchr(0x2545) => "+",
            Encoding::uchr(0x2546) => "+",
            Encoding::uchr(0x2547) => "+",
            Encoding::uchr(0x2548) => "+",
            Encoding::uchr(0x2549) => "+",
            Encoding::uchr(0x254A) => "+",
            Encoding::uchr(0x254B) => "+",
            Encoding::uchr(0x254C) => "-",
            Encoding::uchr(0x254D) => "=",
            Encoding::uchr(0x254E) => "|",
            Encoding::uchr(0x254F) => "|",
            Encoding::uchr(0x2550) => "=",
            Encoding::uchr(0x2551) => "|",
            Encoding::uchr(0x2552) => "+",
            Encoding::uchr(0x2553) => "+",
            Encoding::uchr(0x2554) => "+",
            Encoding::uchr(0x2555) => "+",
            Encoding::uchr(0x2556) => "+",
            Encoding::uchr(0x2557) => "+",
            Encoding::uchr(0x2558) => "+",
            Encoding::uchr(0x2559) => "+",
            Encoding::uchr(0x255A) => "+",
            Encoding::uchr(0x255B) => "+",
            Encoding::uchr(0x255C) => "+",
            Encoding::uchr(0x255D) => "+",
            Encoding::uchr(0x255E) => "+",
            Encoding::uchr(0x255F) => "+",
            Encoding::uchr(0x2560) => "+",
            Encoding::uchr(0x2561) => "+",
            Encoding::uchr(0x2562) => "+",
            Encoding::uchr(0x2563) => "+",
            Encoding::uchr(0x2564) => "+",
            Encoding::uchr(0x2565) => "+",
            Encoding::uchr(0x2566) => "+",
            Encoding::uchr(0x2567) => "+",
            Encoding::uchr(0x2568) => "+",
            Encoding::uchr(0x2569) => "+",
            Encoding::uchr(0x256A) => "+",
            Encoding::uchr(0x256B) => "+",
            Encoding::uchr(0x256C) => "+",
            Encoding::uchr(0x256D) => "+",
            Encoding::uchr(0x256E) => "+",
            Encoding::uchr(0x256F) => "+",
            Encoding::uchr(0x2570) => "+",
            Encoding::uchr(0x2571) => "/",
            Encoding::uchr(0x2572) => "\\",
            Encoding::uchr(0x2573) => "X",
            Encoding::uchr(0x257C) => "-",
            Encoding::uchr(0x257D) => "|",
            Encoding::uchr(0x257E) => "-",
            Encoding::uchr(0x257F) => "|",
            Encoding::uchr(0x25CB) => "o",
            Encoding::uchr(0x25E6) => "o",
            Encoding::uchr(0x2605) => "*",
            Encoding::uchr(0x2606) => "*",
            Encoding::uchr(0x2612) => "X",
            Encoding::uchr(0x2613) => "X",
            Encoding::uchr(0x2639) => ":-(",
            Encoding::uchr(0x263A) => ":-)",
            Encoding::uchr(0x263B) => "(-:",
            Encoding::uchr(0x266D) => "b",
            Encoding::uchr(0x266F) => "#",
            Encoding::uchr(0x2701) => "%<",
            Encoding::uchr(0x2702) => "%<",
            Encoding::uchr(0x2703) => "%<",
            Encoding::uchr(0x2704) => "%<",
            Encoding::uchr(0x270C) => "V",
            Encoding::uchr(0x2713) => Encoding::uchr(0x221A),
            Encoding::uchr(0x2714) => Encoding::uchr(0x221A),
            Encoding::uchr(0x2715) => "x",
            Encoding::uchr(0x2716) => "x",
            Encoding::uchr(0x2717) => "X",
            Encoding::uchr(0x2718) => "X",
            Encoding::uchr(0x2719) => "+",
            Encoding::uchr(0x271A) => "+",
            Encoding::uchr(0x271B) => "+",
            Encoding::uchr(0x271C) => "+",
            Encoding::uchr(0x271D) => "+",
            Encoding::uchr(0x271E) => "+",
            Encoding::uchr(0x271F) => "+",
            Encoding::uchr(0x2720) => "+",
            Encoding::uchr(0x2721) => "*",
            Encoding::uchr(0x2722) => "+",
            Encoding::uchr(0x2723) => "+",
            Encoding::uchr(0x2724) => "+",
            Encoding::uchr(0x2725) => "+",
            Encoding::uchr(0x2726) => "+",
            Encoding::uchr(0x2727) => "+",
            Encoding::uchr(0x2729) => "*",
            Encoding::uchr(0x272A) => "*",
            Encoding::uchr(0x272B) => "*",
            Encoding::uchr(0x272C) => "*",
            Encoding::uchr(0x272D) => "*",
            Encoding::uchr(0x272E) => "*",
            Encoding::uchr(0x272F) => "*",
            Encoding::uchr(0x2730) => "*",
            Encoding::uchr(0x2731) => "*",
            Encoding::uchr(0x2732) => "*",
            Encoding::uchr(0x2733) => "*",
            Encoding::uchr(0x2734) => "*",
            Encoding::uchr(0x2735) => "*",
            Encoding::uchr(0x2736) => "*",
            Encoding::uchr(0x2737) => "*",
            Encoding::uchr(0x2738) => "*",
            Encoding::uchr(0x2739) => "*",
            Encoding::uchr(0x273A) => "*",
            Encoding::uchr(0x273B) => "*",
            Encoding::uchr(0x273C) => "*",
            Encoding::uchr(0x273D) => "*",
            Encoding::uchr(0x273E) => "*",
            Encoding::uchr(0x273F) => "*",
            Encoding::uchr(0x2740) => "*",
            Encoding::uchr(0x2741) => "*",
            Encoding::uchr(0x2742) => "*",
            Encoding::uchr(0x2743) => "*",
            Encoding::uchr(0x2744) => "*",
            Encoding::uchr(0x2745) => "*",
            Encoding::uchr(0x2746) => "*",
            Encoding::uchr(0x2747) => "*",
            Encoding::uchr(0x2748) => "*",
            Encoding::uchr(0x2749) => "*",
            Encoding::uchr(0x274A) => "*",
            Encoding::uchr(0x274B) => "*",
            Encoding::uchr(0xFB00) => "ff",
            Encoding::uchr(0xFB01) => "fi",
            Encoding::uchr(0xFB02) => "fl",
            Encoding::uchr(0xFB03) => "ffi",
            Encoding::uchr(0xFB04) => "ffl",
            Encoding::uchr(0xFB05) => Encoding::uchr(0x17F)."t",
            Encoding::uchr(0xFB06) => "st",
            Encoding::uchr(0xFEFF) => "",
            Encoding::uchr(0xFFFD) => "?",
        );
        Encoding::$trans_map_built = true;
    }


    /**
     *
     *
     * @param unknown $str
     * @return boolean
     */
    public static function is_ascii($str) {
        $len=strlen($str);
        for ($i=0; $i<$len; $i++) {
            $c=ord($str[$i]);
            if ($c > 127) {
                return false;
            }
        }
        return true;
    }



    /**
     *
     *
     * @param latin1-encoded $str
     * @return boolean
     */
    public static function is_latin1($str) {
        $len=strlen($str);
        for ($i=0; $i<$len; $i++) {
            $c=ord($str[$i]);
            if ($c > 0x7f && $c < 0xa0) {
                return false;
            }
        }
        return true;
    }



    /**
     *
     *
     * @param windows1252-encoded $str
     * @return boolean
     */
    public static function is_cp1252($str) {
        if (!Encoding::is_latin1($str) &&
            !Encoding::is_ascii($str) &&
            preg_match('/[\x80-\x9f]/', $str)
        ) {
            return true;
        }
        return false;
    }



    /**
     *
     *
     * @param utf8-encoded $str
     * @return boolean
     */
    public static function is_utf8($str) {
        $c=0;
        $b=0;
        $bits=0;
        $len=strlen($str);
        for ($i=0; $i<$len; $i++) {
            $c=ord($str[$i]);
            if ($c > 127) {
                if (($c >= 254)) return false;
                elseif ($c >= 252) $bits=6;
                elseif ($c >= 248) $bits=5;
                elseif ($c >= 240) $bits=4;
                elseif ($c >= 224) $bits=3;
                elseif ($c >= 192) $bits=2;
                else return false;
                if (($i+$bits) > $len) return false;
                while ($bits > 1) {
                    $i++;
                    $b=ord($str[$i]);
                    if ($b < 128 || $b > 191) return false;
                    $bits--;
                }
            }
        }
        return true;
    }


    /**
     *
     *
     * @param unknown $thing
     * @return unknown
     */
    public static function json_encode_utf8($thing) {
        if (is_object($thing)) {
            throw new Exception("cannot json_encode_utf8 an object");
        }
        if (is_array($thing)) {
            $thing = Encoding::latin1_to_utf8_array($thing);
            return json_encode($thing);
        }
        else {
            return json_encode($thing);
        }
    }



    /**
     *
     *
     * @param unknown $array
     * @return unknown
     */
    public static function latin1_to_utf8_array($array) {
        if (!is_array($array)) {
            throw new Exception("argument is not an array");
        }
        foreach ($array as $i=>$v) {
            if (is_object($v)) {
                $v = (array)$v;
            }
            if (is_array($v)) {
                $v = Encoding::latin1_to_utf8_array($v);
            }
            elseif (!Encoding::is_utf8($v)) {
                $v = utf8_encode($v);
                if (Encoding::is_cp1252($v)) {
                    $v = Encoding::fix_cp1252_codepoints_in_utf8($v);
                }
            }

            if (is_integer($i)) {
                $array[$i] = $v;
            }
            else {
                if (!Encoding::is_utf8($i)) {
                    $utf8 = utf8_encode($i);
                    unset($array[$i]);
                    $array[$utf8] = $v;
                }
                else {
                    $array[$i] = $v;
                }
            }
        }
        return $array;
    }



    /**
     *
     *
     * @param unknown $str
     * @return unknown
     */
    public static function utf8_to_latin1($str) {
        if (Encoding::is_cp1252($str) && Encoding::is_utf8($str)) {
            $str = Encoding::fix_cp1252_codepoints_in_utf8($str);
        }
        if (Encoding::is_utf8($str) && !Encoding::is_ascii($str)) {
            $str = Encoding::transliterate($str);
        }

        return utf8_decode($str);
    }


    /**
     *
     *
     * @param unknown $str
     * @return unknown
     */
    public static function transliterate($str) {
        Encoding::_build_trans_map();
        foreach (Encoding::$trans_map as $utf8=>$latin1) {
            if (preg_match('/'.$utf8.'/', $str)) {
                $str = preg_replace('/'.$utf8.'/', $latin1, $str);
            }
        }
        return $str;
    }



    /**
     *
     *
     * @param utf8_string $str
     * @return utf8_string
     */
    public static function fix_cp1252_codepoints_in_utf8($str) {
        return strtr($str, Encoding::$cp1252_map);
    }


    /**
     * Convert non-UTF8 strings to UTF8.  Strings already in UTF8 will be
     * returned intact.
     *
     * @param string  $str
     * @return string
     */
    public static function convert_to_utf8($str) {
        if (Encoding::is_ascii($str)) {
            // valid! nothing to do
        }
        elseif (Encoding::is_cp1252($str)) {
            $str = utf8_encode($str);
            $str = Encoding::fix_cp1252_codepoints_in_utf8($str);
        }
        elseif (Encoding::is_latin1($str)) {
            $str = utf8_encode($str);
        }
        return $str;
    }


}
