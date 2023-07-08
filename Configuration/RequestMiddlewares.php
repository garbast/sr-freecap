<?php
/**
 * An array consisting of implementations of middlewares for a middleware stack to be registered
 *
 *  'stackname' => [
 *      'middleware-identifier' => [
 *         'target' => classname or callable
 *         'before/after' => array of dependencies
 *      ]
 *   ]
 */
return [
    'frontend' => [
        'sjbr/sr-freecap/captcha-request-handler' => [
            'target' => \SJBR\SrFreecap\Middleware\CaptchaRequestHandler::class,
            'after' => [
                'typo3/cms-frontend/tsfe',
                'typo3/cms-frontend/authentication',
                'typo3/cms-frontend/backend-user-authentication',
                'typo3/cms-frontend/site',
                'typo3/cms-frontend/prepare-tsfe-rendering'
            ]
        ]
    ]
];