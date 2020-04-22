<?php
// Configurations
class Line {
    // Urban rail system timetable configuration by miniute
    public static $timetable = [
        'peak'  => [
            'NS'     => 12,
            'NE'     => 12,
            'other'  => 10,
            'change' => 15,
        ],
        'night' => [
            'DT'     => PHP_INT_MAX,
            'CG'     => PHP_INT_MAX,
            'CE'     => PHP_INT_MAX,
            'TE'     => 8,
            'other'  => 10,
            'change' => 10,
        ],
        'other' => [
            'DT'     => 8,
            'TE'     => 8,
            'other'  => 10,
            'change' => 10,
        ],
    ];

    // Urban rail system peak hour configuration
    public static $period = [
        'peak' => [
            'hour' => [[6, 9], [18, 21]],
            'day'  => [1, 5],
        ],
        'night' => [
            'hour' => [[22, 23], [0, 6]],
            'day'  => [0, 6],
        ],
    ];
}
