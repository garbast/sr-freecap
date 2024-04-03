<?php
defined('TYPO3') or die();

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

call_user_func(
    function($extKey)
    {	
		if (isset($GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'])) {
		    $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'freecapSet';
		} else {
			$GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'] = ['freecapSet'];
		}
		$GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'formatName';

		// GDlib is a requirement for the Font Maker module
		if ($GLOBALS['TYPO3_CONF_VARS']['GFX']['gdlib'] ?? false) {
			// Add module configuration setup
			ExtensionManagementUtility::addTypoScript($extKey, 'setup', '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:' . $extKey . '/Configuration/TypoScript/FontMaker/setup.typoscript">');
		}
		
		$extensionName = GeneralUtility::underscoredToUpperCamelCase($extKey);
		// Configuring the captcha image generator
		ExtensionUtility::configurePlugin(
			// The extension name (in UpperCamelCase) or the extension key (in lower_underscore)
			$extensionName,
			// A unique name of the plugin in UpperCamelCase
			'ImageGenerator',
			// An array holding the controller-action-combinations that are accessible
			[
				// The first controller and its first action will be the default
				\SJBR\SrFreecap\Controller\ImageGeneratorController::class => 'show',
			],
			// An array of non-cachable controller-action-combinations (they must already be enabled)
			[
				\SJBR\SrFreecap\Controller\ImageGeneratorController::class => 'show',
			]
		);

		// Configuring the audio captcha player
		ExtensionUtility::configurePlugin(
			// The extension name (in UpperCamelCase) or the extension key (in lower_underscore)
			$extensionName,
			// A unique name of the plugin in UpperCamelCase
			'AudioPlayer',
			// An array holding the controller-action-combinations that are accessible
			[
				// The first controller and its first action will be the default
				\SJBR\SrFreecap\Controller\AudioPlayerController::class => 'play',
			],
			// An array of non-cachable controller-action-combinations (they must already be enabled)
			[
				\SJBR\SrFreecap\Controller\AudioPlayerController::class => 'play',
			]
		);
	},
	'sr_freecap'
);