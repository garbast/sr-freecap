<?php
/*
 * Extension Manager configuration file for ext "sr_freecap".
 *
 */
$EM_CONF[$_EXTKEY] = [
	'title' => 'freeCap CAPTCHA',
	'description' => 'A TYPO3 integration of freeCap CAPTCHA.',
	'category' => 'fe',
	'version' => '12.4.1',
	'state' => 'stable',
	'clearcacheonload' => 0,
	'author' => 'Stanislas Rolland',
	'author_email' => 'typo3AAAA(arobas)sjbr.ca',
	'author_company' => 'SJBR',
	'constraints' => [
		'depends' => [
			'typo3' => '12.4.0-12.4.99'
		],
		'conflicts' => [],
		'suggests' => []
    ],
    'autoload' => [
        'psr-4' => [
        	'SJBR\\SrFreecap\\' => 'Classes'
        ]
    ]
];