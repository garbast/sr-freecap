<?php
namespace SJBR\SrFreecap\Controller;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012-2021 Stanislas Rolland <typo3AAAA(arobas)sjbr.ca>
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
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

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
		$this->view->assign('font', $font);
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
		$this->view->assign('font', $font);
        $imageUrl = $font->getPngImageFileName();
        $imageUrl = $this->request->getAttribute('normalizedParams')->getSiteUrl() . $imageUrl;
        $fontFilename = $font->getGdFontFileName();
        $fontFilename = str_replace(Environment::getPublicPath(), '', $fontFilename);
        $this->view->assign('imageUrl', $imageUrl);
        $this->view->assign('fontFilename', $fontFilename);
	}
}