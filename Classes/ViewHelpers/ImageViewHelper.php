<?php
namespace SJBR\SrFreecap\ViewHelpers;

/*
 *  Copyright notice
 *
 *  (c) 2013-2023 Stanislas Rolland <typo3AAAA(arobas)sjbr.ca>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
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

use Psr\Http\Message\ServerRequestInterface;
use SJBR\SrFreecap\ViewHelpers\TranslateViewHelper;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Domain\ConsumableString;
use TYPO3\CMS\Core\Page\AssetCollector;
use TYPO3\CMS\Core\Session\Backend\Exception\SessionNotFoundException;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;

class ImageViewHelper extends AbstractTagBasedViewHelper
{
	/**
	 * @var string Name of the extension this view helper belongs to
	 */
	protected $extensionName = 'SrFreecap';

	/**
	 * @var string Name of the extension this view helper belongs to
	 */
	protected $extensionKey = 'sr_freecap';

	/**
	 * @var string Name of the plugin this view helper belongs to
	 */
	protected $pluginName = 'tx_srfreecap';

	/**
	 * @var Context
	 */
	protected $context;

	/**
	 * @param Context $context
	 */
	public function injectContext(Context $context)
	{
		$this->context = $context;
	}

	public function initializeArguments()
	{
		parent::initializeArguments();
		$this->registerArgument('suffix', 'string', 'Suffix to be appended to the extenstion key when forming css class names', false, '');
	}

	/**
	 * Render the captcha image html
	 *
	 * @param string $suffix
	 * @return string The html used to render the captcha image
	 */
    public function render($suffix = ''): string
	{
		// This viewhelper needs a frontend user session
		if (!is_object($this->getTypoScriptFrontendController()) || !isset($this->getTypoScriptFrontendController()->fe_user)) {
			throw new SessionNotFoundException('No frontend user found in session!');
		}
		$value = '';

		// Include the required JavaScript
		$assetCollector = GeneralUtility::makeInstance(AssetCollector::class);
		$nonceAttribute = $this->getRequest()->getAttribute('nonce');
		if ($nonceAttribute instanceof ConsumableString) {
			$nonce = $nonceAttribute->consume();
		}
		$assetCollector->addJavaScript('sr-freecap', 'EXT:sr_freecap/Resources/Public/JavaScript/freeCap.js', isset($nonce) ? ['nonce' => $nonce] : []);

		// Disable caching
		$this->getTypoScriptFrontendController()->no_cache = true;

		// Get the translation view helper
		$translator = GeneralUtility::makeInstance(TranslateViewHelper::class);

		// Generate the image url
		$pageUid = $this->getTypoScriptFrontendController()->id;
		$site = GeneralUtility::makeInstance(SiteFinder::class)->getSiteByPageId($pageUid);
		$fakeId = substr(md5(uniqid(rand())), 0, 5);
		$languageAspect = $this->context->getAspect('language');
		$urlParams = [
			'srFreecap' => '1',
			'pluginName' => 'ImageGenerator',
			'actionName' => 'show',
			'formatName' => 'png',
			'L' => $languageAspect->getId()
		];
		if ($this->getTypoScriptFrontendController()->MP) {
			$urlParams['MP'] = $this->getTypoScriptFrontendController()->MP;
		}
		$imageUrl = (string)$site->getRouter($this->context)->generateUri((string)$pageUid, $urlParams) . '&freecapSet=' . $fakeId;

		// Generate the html text
		$value = '<img' . $this->getClassAttribute('image', $suffix) . ' id="tx_srfreecap_captcha_image_' . $fakeId . '"'
			. ' src="' . htmlspecialchars($imageUrl) . '"'
			. ' alt="' . $translator->render('altText') . ' "/>'
			. '<span' . $this->getClassAttribute('cant-read', $suffix) . '>' . $translator->render('cant_read1')
			. ' <a id="tx_srfreecap_captcha_image_' . $fakeId . '_link" >'
			. $translator->render('click_here') . '</a>'
			. $translator->render('cant_read2') . '</span>';
		$imageOnClickScript = $this->extensionName . 'ImageLinkOnClickFunction = function(event) {
	            event.preventDefault();
	            document.getElementById("tx_srfreecap_captcha_image_' . $fakeId . '_link").blur();' . 
	            $this->extensionName . '.newImage(\'' . $fakeId . '\', \'' . $translator->render('noImageMessage') .'\');
	            return false;
	        };
			document.getElementById("tx_srfreecap_captcha_image_' . $fakeId . '_link").addEventListener("click", ' . $this->extensionName . 'ImageLinkOnClickFunction, false);';
	    $value .= '<script' . (isset($nonce) ? ' nonce="' . $nonce . '"' : '') . '>' . $imageOnClickScript .'</script>';
		return $value;
	}

	/**
	 * Returns a class attribute with a class-name prefixed with $this->pluginName and with all underscores substituted to dashes (-)
	 *
	 * @param string $class The class name (or the END of it since it will be prefixed by $this->pluginName.'-')
	 * @param string suffix to be appended to the extenstion key when forming css class names
	 * @return string the class attribute with the combined class name (with the correct prefix)
	 */
	protected function getClassAttribute($class, $suffix = '')
	{
		return ' class="' . trim(str_replace('_', '-', $this->pluginName) . ($suffix ? '-' . $suffix . '-' : '-') . $class) . '"';
	}

    /**
     * @return TypoScriptFrontendController
     */
    private function getTypoScriptFrontendController()
    {
    	return $this->getRequest()->getAttribute('frontend.controller');
    }

	private function getRequest(): ServerRequestInterface
	{
		return $GLOBALS['TYPO3_REQUEST'];
	}
}