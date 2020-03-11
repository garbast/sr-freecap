<?php

declare(strict_types=1);

use SJBR\SrFreecap\Middleware\EidHandler;

return [
    'frontend' => [
        'sjbr/sr-freecap/eid' => [
            'target' => EidHandler::class,
            'after' => [
                'typo3/cms-frontend/preprocessing'
            ]
        ],
    ],
];
