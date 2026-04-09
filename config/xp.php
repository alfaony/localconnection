<?php

/*
|--------------------------------------------------------------------------
| XP Level Thresholds
|--------------------------------------------------------------------------
|
| Definisi level achievement berdasarkan total XP.
| Atur nilai minimum setiap level via .env untuk fleksibilitas tanpa
| harus deploy ulang kode.
|
| ENV variables:
|   XP_LEVEL_BRONZE   = 0      (default)
|   XP_LEVEL_SILVER   = 500    (default)
|   XP_LEVEL_GOLD     = 1000   (default)
|   XP_LEVEL_PLATINUM = 2000   (default)
|   XP_LEVEL_DIAMOND  = 5000   (default)
|   XP_LEVEL_GINNER   = 5000000 (default)
|
*/

return [

    'levels' => [
        [
            'key'   => 'beginner',
            'label' => 'Beginner',
            'badge' => '🎓',
            'color' => '#57c363ff',
            'min'   => (int) env('XP_LEVEL_BEGINNER', 0),
        ],
        [
            'key'   => 'bronze',
            'label' => 'Bronze',
            'badge' => '🔶',
            'color' => '#cd7f32',
            'min'   => (int) env('XP_LEVEL_BRONZE',500),
        ],
        [
            'key'   => 'silver',
            'label' => 'Silver',
            'badge' => '⭐',
            'color' => '#9ca3af',
            'min'   => (int) env('XP_LEVEL_SILVER', 1000),
        ],
        [
            'key'   => 'gold',
            'label' => 'Gold',
            'badge' => '🌟',
            'color' => '#f5a623',
            'min'   => (int) env('XP_LEVEL_GOLD', 100000),
        ],
        [
            'key'   => 'platinum',
            'label' => 'Platinum',
            'badge' => '🔮',
            'color' => '#a78bfa',
            'min'   => (int) env('XP_LEVEL_PLATINUM', 500000),
        ],
        [
            'key'   => 'diamond',
            'label' => 'Diamond',
            'badge' => '💎',
            'color' => '#4facfe',
            'min'   => (int) env('XP_LEVEL_DIAMOND', 1000000),
        ]
    ],

];
