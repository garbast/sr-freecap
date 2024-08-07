<?php
namespace SJBR\SrFreecap\ViewHelpers;

/*
 *  Copyright notice
 *
 *  (c) 2013-2023 Stanislas Rolland <typo3AAAA@sjbr.ca>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  A copy is found in the textfile GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 */

use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Translate a key from locallang. The files are loaded from the folder
 * "Resources/Private/Language/".
 *
 * Handle salutation suffixes
 *
 * == Examples ==
 *
 * <code title="Translate key">
 * <f:translate key="key1" />
 * </code>
 * <output>
 * // value of key "key1" in the current website language
 * </output>
 *
 * <code title="Keep HTML tags">
 * <f:translate key="htmlKey" htmlEscape="false" />
 * </code>
 * <output>
 * // value of key "htmlKey" in the current website language, no htmlspecialchars applied
 * </output>
 *
 * <code title="Translate key from custom locallang file">
 * <f:translate key="LLL:EXT:myext/Resources/Private/Language/locallang.xml:key1" />
 * </code>
 * <output>
 * // value of key "key1" in the current website language
 * </output>
 *
 * <code title="Inline notation with arguments and default value">
 * {f:translate(key: 'argumentsKey', arguments: {0: 'dog', 1: 'fox'}, default: 'default value')}
 * </code>
 * <output>
 * // value of key "argumentsKey" in the current website language
 * // with "%1" and "%2" are replaced by "dog" and "fox" (printf)
 * // if the key is not found, the output is "default value"
 * </output>
 */
class TranslateViewHelper extends AbstractViewHelper
{
	/**
	 * @var string Name of the extension this view helper belongs to
	 */
	protected $extensionName = 'SrFreecap';

	/**
	 * @var string Name of the extension this view helper belongs to
	 */
	protected $pluginName = 'tx_srfreecap';

	/**
	 * @var array List of allowed suffixes
	 */
	protected $allowedSuffixes = ['formal', 'informal'];

	/**
	 * @var ConfigurationManagerInterface
	 */
	protected $configurationManager;

	/**
	 * @param ConfigurationManagerInterface $configurationManager
	 * @return void
	 */
	public function injectConfigurationManager(ConfigurationManagerInterface $configurationManager)
	{
		$this->configurationManager = $configurationManager;
	}

	public function initializeArguments()
	{
		$this->registerArgument('key', 'string', 'The language key to translate', true);
		$this->registerArgument('default', 'string', 'Value to be used when the key is not found');
		$this->registerArgument('htmlEscape', 'boolean', 'Whether to escape html', false, true);
		$this->registerArgument('arguments', 'array', 'Arguments to be replaced in the string');
	}

	/**
	 * Translate a given key or use the tag body as default.
	 *
	 * @param string $key The locallang key
	 * @param string $default if the given locallang key could not be found, this value is used. . If this argument is not set, child nodes will be used to render the default
	 * @param boolean $htmlEscape true if the result should be htmlescaped. This won't have an effect for the default value
	 * @param array $arguments Arguments to be replaced in the resulting string
	 * @return string The translated key or tag body if key doesn't exist
	 * @author Christopher Hlubek <hlubek@networkteam.com>
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function render($key = null)
	{
		if ($this->hasArgument('key')) {
			$key = $this->arguments['key'];
		}
		$value = '';
		$default = $this->hasArgument('default') ? $this->arguments['default'] : '';
		$htmlEscape = $this->hasArgument('htmlEscape') ? $this->arguments['htmlEscape'] : false;
		$arguments = $this->hasArgument('arguments') ? $this->arguments['arguments'] : null;
		// If the suffix is allowed and we have a localized string for the desired salutation, we'll take that.
		$settings = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS, $this->extensionName, $this->pluginName);
		if (isset($settings['salutation']) && in_array($settings['salutation'], $this->allowedSuffixes, 1)) {
			$expandedKey = $key . '_' . $settings['salutation'];
			$value = LocalizationUtility::translate($expandedKey, $this->extensionName, $arguments);
		}
		if (!$value) {
			$value = LocalizationUtility::translate($key, $this->extensionName, $arguments);
		}
		if (!$value) {
			$value = $default !== null ? $default : $this->renderChildren();
		} elseif ($htmlEscape) {
			$value = htmlspecialchars($value);
		}
		return $value;
	}
}
