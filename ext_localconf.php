<?php
defined('TYPO3') or die();

call_user_func(
    function($extKey)
    {
		// Dispatching requests to image generator and audio player
		$GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['sr_freecap']['eIDSR_include']['sr_freecap_EidDispatcher'] = \SJBR\SrFreecap\Http\EidDispatcher::class . '::initAndDispatch';
		
		if (isset($GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'])) {
		    $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'freecapSet';
		} else {
			$GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'] = ['freecapSet'];
		}
		
		$extensionName = \TYPO3\CMS\Core\Utility\GeneralUtility::underscoredToUpperCamelCase($extKey);
		// Configuring the captcha image generator
		\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
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
		\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
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