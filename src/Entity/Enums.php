<?php

namespace App\Entity;

class Enums
{
    public const DATE_FORMAT = 'Y-m-d';
    public const DATE_FORMAT_START = 'Y-m-d 12:00:00';
    public const DATE_FORMAT_END = 'Y-m-d 23:59:59';

    public const ZODIAC_SIGNS = [
        'aries' => 'aries',
        'taurus' => 'taurus',
        'gemini' => 'gemini',
        'cancer' => 'cancer',
        'leo' => 'leo',
        'virgo' => 'virgo',
        'libra' => 'libra',
        'scorpio' => 'scorpio',
        'sagittarius' => 'sagittarius',
        'capricorn' => 'capricorn',
        'aquarius' => 'aquarius',
        'pisces' => 'pisces'
    ];
    public const CSILLAGJEGYEK = [
        'aries' => 'kos',
        'taurus' => 'bika',
        'gemini' => 'ikrek',
        'cancer' => 'rák',
        'leo' => 'oroszlán',
        'virgo' => 'szűz',
        'libra' => 'mérleg',
        'scorpio' => 'skorpió',
        'sagittarius' => 'nyilas',
        'capricorn' => 'bak',
        'aquarius' => 'vízöntő',
        'pisces' => 'halak'
    ];
}
