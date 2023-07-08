<?php
declare(strict_types = 1);

/**
 * Registers the Font Maker backend module, if enabled
 */

// GDlib is a requirement for the Font Maker module
if ($GLOBALS['TYPO3_CONF_VARS']['GFX']['gdlib'] ?? false) {
	return [
		'fontmaker' => [
			'parent' => 'tools',
			'position' => [],
			'access' => 'user,group',
			'workspaces' => '*',
			'identifier' => 'fontmaker',
			'isStandalone' => false,
			'path' => '/module/tools/fontmaker',
			'iconIdentifier' => 'sr-freecap-icon',
			'labels' => 'LLL:EXT:sr_freecap/Resources/Private/Language/locallang_mod.xlf',
			'extensionName' => 'SrFreecap',
			'controllerActions' => [
				\SJBR\SrFreecap\Controller\FontMakerController::class => [
					'new',
					'create'
				]
			]
		]
	];
} else {
	return [];
}