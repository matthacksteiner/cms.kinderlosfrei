<?php

use Kirby\Toolkit\Str;

Kirby::plugin('baukasten/mime', [
    'fileTypes' => [
        'ico' => [
            'mime' => 'image/ico',
            'type' => 'image',
        ],
    ]
]);
