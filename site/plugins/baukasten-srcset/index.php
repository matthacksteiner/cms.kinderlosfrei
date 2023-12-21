<?php

use Kirby\Cms\App as Kirby;

Kirby::plugin('baukasten/srcset', [
    'hooks' => [
        'file.create:after' => function ($file) {
            // Check if the file is an image
            if ($file->type() === 'image') {
                // Get different sizes for srcset
                $thumbSmall = $file->thumb(['width' => 320]);
                $thumbMedium = $file->thumb(['width' => 640]);
                $thumbLarge = $file->thumb(['width' => 1024]);

                // Store the URLs of the generated images in the file's metadata
                $file->update([
                    'thumbSmall' => $thumbSmall->url(),
                    'thumbMedium' => $thumbMedium->url(),
                    'thumbLarge' => $thumbLarge->url(),
                ]);
            }
        },
    ]
]);
