<?php

use Kirby\Cms\App as Kirby;

Kirby::plugin('baukasten/srcset', [
    'hooks' => [
        'your-plugin.your-hook' => function ($page) {
            // do whatever you need
        },
    ]
]);