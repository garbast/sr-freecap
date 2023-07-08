<?php
namespace SJBR\SrFreecap\Controller;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012-2023 Stanislas Rolland <typo3AAAA(arobas)sjbr.ca>
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
 ***************************************************************/

use SJBR\SrFreecap\Domain\Model\Font;
use SJBR\SrFreecap\Domain\Repository\FontRepository;
use TYPO3\CMS\Backend\Attribute\Controller;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Font Maker controller
 */
class FontMakerController  extends ActionController
{
	/**
	 * @var string Name of the extension this controller belongs to
	 */
	protected $extensionName = 'SrFreecap';

    /**
     * Dependency injection of the Module Template Factory
     *
     * @param ModuleTemplateFactory $moduleTemplateFactory
     * @return void
     */
    public function injectModuleTemplateFactory(ModuleTemplateFactory $moduleTemplateFactory)
    {
        $this->moduleTemplateFactory = $moduleTemplateFactory;
    }

    /**
     * Init module state.
     * This isn't done within __construct() since the controller
     * object is only created once in extbase when multiple actions are called in
     * one call. When those change module state, the second action would see old state.
     */
    public function initializeAction(): void
    {
        $this->moduleData = $this->request->getAttribute('moduleData');
        $this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        $this->moduleTemplate->setTitle(LocalizationUtility::translate('LLL:EXT:sr_freecap/Resources/Private/Language/locallang_mod.xlf:mlang_labels_tablabel'));
        $this->moduleTemplate->setFlashMessageQueue($this->getFlashMessageQueue());
    }

	/**
	 * Display the font maker form
	 *
	 * @param Font $font
	 * @return string An HTML form for creating a new font
	 */
	public function newAction(Font $font = null)
	{
		if (!is_object($font)) {
			$font = new Font();
		}
        $this->moduleTemplate->assign('font', $font);
        return $this->moduleTemplate->renderResponse('New');
	}	

	/**
	 * Create the font file and display the result
	 *
	 * @param Font $font
	 * @return string HTML presenting the new font that was created
	 */
	public function createAction(Font $font)
	{
		// Create the font data
		$font->createGdFontFile();
		// Store the GD font file
		$fontRepository = GeneralUtility::makeInstance(FontRepository::class);
		$fontRepository->writeFontFile($font);
        $this->moduleTemplate->assign('font', $font);
        $imageUrl = $font->getPngImageFileName();
        $imageUrl = $this->request->getAttribute('normalizedParams')->getSiteUrl() . $imageUrl;
        $fontFilename = $font->getGdFontFileName();
        $fontFilename = str_replace(Environment::getPublicPath(), '', $fontFilename);
        $this->moduleTemplate->assign('imageUrl', $imageUrl);
        $this->moduleTemplate->assign('fontFilename', $fontFilename);
        return $this->moduleTemplate->renderResponse('Create');
	}
}