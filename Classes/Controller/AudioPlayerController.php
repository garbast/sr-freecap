<?php
namespace SJBR\SrFreecap\Controller;

/*
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
 */

use Psr\Http\Message\ResponseInterface;
use SJBR\SrFreecap\Domain\Repository\WordRepository;
use SJBR\SrFreecap\View\AudioPlayer\PlayMp3;
use SJBR\SrFreecap\View\AudioPlayer\PlayWav;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Renders a wav audio version of the CAPTCHA
 */
class AudioPlayerController extends ActionController
{
	/**
	 * @var string Name of the extension this controller belongs to
	 */
	protected $extensionName = 'SrFreecap';

	/**
	 * @var WordRepository
	 */
	protected $wordRepository;

 	/**
	 * Dependency injection of the Word Repository
 	 *
	 * @param WordRepository $wordRepository
	 */
	public function injectWordRepository(WordRepository $wordRepository)
	{
		$this->wordRepository = $wordRepository;
	}

	/**
	 * Play the audio catcha
	 *
	 * @return ResponseInterface
	 */
	public function playAction(): ResponseInterface
	{
		$word = $this->wordRepository->getWord();
		$format = $this->request->getQueryParams()['formatName'];
		if ($format === 'mp3') {
			$this->view = GeneralUtility::makeInstance(PlayMp3::class);
		} else if ($format === 'wav') {
			$this->view = GeneralUtility::makeInstance(PlayWav::class);
		} else {
			throw new \Exception('Unknow audio format ' . $format);
		}
		$this->view->assign('word', $word);
		$this->view->render();
		$response = new Response();
		return $response->withStatus(200, 'Audio sent');
	}
}