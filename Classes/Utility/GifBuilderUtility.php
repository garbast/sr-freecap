<?php
namespace SJBR\SrFreecap\Utility;

/*
 *  Copyright notice
 *
 *  (c) 2012-2018 Stanislas Rolland <typo3(arobas)sjbr.ca>
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

use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Frontend\Imaging\GifBuilder;

/**
 * Utility extending Gif builder
 */
class GifBuilderUtility extends GifBuilder
{
	/**
	 * Returns the reference to a "resource" in TypoScript.
	 *
	 * @param string The resource value.
	 * @return string Returns the relative filepath
	 */
	public function checkFile($file)
	{
		$file = GeneralUtility::getFileAbsFileName(Environment::getPublicPath() . '/' . $file);
        $file = PathUtility::stripPathSitePrefix($file);
		return $file;
	}

	/**
	 * Writes the input GDlib image pointer to file
	 *
	 * @param resource The GDlib image resource pointer
	 * @param string The filename to write to
	 * @param int $quality The image quality (for JPEGs)
	 * @return mixed The output of either imageGif, imagePng or imageJpeg based on the filename to write
	 * @see maskImageOntoImage(), scale(), output()
	 */
	public function ImageWrite($destImg, $theImage, $quality = 0)
	{
		return parent::ImageWrite($destImg, $theImage, $quality);
 	}
}