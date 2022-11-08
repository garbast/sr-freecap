<?php
namespace SJBR\SrFreecap\Validation\Validator;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012-2018 Stanislas Rolland <typo3@sjbr.ca>
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
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

/**
 * Validator for True Type Font file existence
 *
 */
class TtfFileValidator extends AbstractValidator
{
	/**
	 * Returns true, if the given property ($propertyValue) is a valid number in the given range.
	 *
	 * If at least one error occurred, the result is false.
	 *
	 * @param mixed $value The value that should be validated
	 * @return boolean true if the value is within the range, otherwise false
	 */
	protected function isValid($value)
	{
		$isValid = true;
		$absoluteFileName = GeneralUtility::getFileAbsFileName($value);
		// Check file existence
		if (!is_file($absoluteFileName)) {
			// A file with the given name could not be found.
			$this->addError(
				$this->translateErrorMessage(
					'9221561046',
					'SrFreecap'
				),
				9221561046
			);
			$isValid = false;
		} else {
			// Check file extension
			$pathInfo = pathinfo($absoluteFileName);
			if (strtolower($pathInfo['extension']) !== 'ttf') {
				// The specified file is not a True Type Font file.
				$this->addError(
					$this->translateErrorMessage(
						'9221561047',
						'SrFreecap'
					),
					9221561046
				);
				$isValid = false;
			}
		}
		return $isValid;
	}
}