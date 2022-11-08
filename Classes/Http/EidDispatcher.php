<?php
namespace SJBR\SrFreecap\Http;

/*
 * Copyright notice
 *
 * 2010 Daniel Lienert <daniel@lienert.cc>, Michael Knoll <mimi@kaktusteam.de>
 * 2012-2022 Stanislas Rolland <typo3AAAA(arobas)sjbr.ca>
 * All rights reserved
 *
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 */

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Error\Http\BadRequestException;
use TYPO3\CMS\Core\Http\NullResponse;
use TYPO3\CMS\Core\Localization\Locales;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\RootlineUtility;
use TYPO3\CMS\Extbase\Core\Bootstrap;
use TYPO3\CMS\Extbase\Mvc\Dispatcher;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Dispatch the eid request
 */
class EidDispatcher
{
	/**
	 * Array of all request Arguments
	 *
	 * @var array
	 */
	protected $requestArguments = [];

	/**
	 * @var string
	 */
	protected $vendorName = 'SJBR';

	/**
	 * @var string
	 */
	protected $extensionName = 'SrFreecap';

	/**
	 * @var string
	 */
	protected $pluginName;

	/**
	 * @var string
	 */
	protected $controllerName;

	/**
	 * @var string
	 */
	protected $actionName;

	/**
	 * @var string
	 */
	protected $formatName;

	/**
	 * @var array
	 */
	protected $arguments = [];

	/**
	 * Initializes and dispatches actions
	 * Call this function if you want to use this dispatcher "standalone"
	 * @param ServerRequestInterface $request
	 * @return Response
	 */
	public function initAndDispatch($request)
	{
		return $this->initTypoScriptConfiguration($request)
			->initLanguage($request)
			->initCallArguments()
			->dispatch();
	}

	/**
	 * Builds an extbase context and returns the response
	 *
	 * @return ResponseInterface
	 */
	protected function dispatch()
	{
		$bootstrap = GeneralUtility::makeInstance(Bootstrap::class);
		$configuration['vendorName'] = $this->vendorName;
		$configuration['extensionName'] = $this->extensionName;
		$configuration['pluginName'] = $this->pluginName;
		$bootstrap->initialize($configuration);
		$request = $this->buildRequest();
		try {
			$response = GeneralUtility::makeInstance(Dispatcher::class)->dispatch($request);
		} catch (\Exception $e) {
			throw new BadRequestException('An argument is missing or invalid', 1394587024);
		}
		return $response;
	}

	/**
	 * Get the TypoScript configuration
	 *
	 * @param ServerRequestInterface $request
	 * @return EidDispatcher
	 */
	protected function initTypoScriptConfiguration($request)
	{
		$controller = $request->getAttribute('frontend.controller');
		$controller->type = 0;
		$context = $controller->getContext();
		$controller->rootLine = GeneralUtility::makeInstance(RootlineUtility::class, $controller->id, $controller->MP, $context)->get();
		$controller->getConfigArray();
		return $this;
	}

	/**
	 * Set  language and locale
	 *
	 * @param ServerRequestInterface $request
	 * @return EidDispatcher
	 */
	protected function initLanguage($request)
	{
		$controller = $request->getAttribute('frontend.controller');
		$siteLanguage = $controller->getLanguage();
		$locales = GeneralUtility::makeInstance(Locales::class);
		$locales->setSystemLocaleFromSiteLanguage($siteLanguage);
		return $this;
	}

	/**
	 * Build a request object
	 *
	 * @return Request $request
	 */
	protected function buildRequest()
	{
		$controllerClassName = $this->vendorName . '\\' . $this->extensionName . '\\' . 'Controller' . '\\' . $this->pluginName . 'Controller';
		$request = GeneralUtility::makeInstance(Request::class, $controllerClassName);
		$request->setControllerObjectName($controllerClassName);
		$request->setPluginName($this->pluginName);
		$request->setControllerActionName($this->actionName);
		$request->setFormat($this->formatName);
		$request->setArguments($this->arguments);
		return $request;
	}

	/**
	 * Prepare the call arguments
	 *
	 * @return EidDispatcher
	 */
	public function initCallArguments() {
		$request = GeneralUtility::_GP('request');
		if ($request) {
			$this->setRequestArgumentsFromJSON($request);
		} else {
			$this->setRequestArgumentsFromGetPost();
		}
		return $this->setPluginName($this->requestArguments['pluginName'])
			->setControllerName()
			->setActionName($this->requestArguments['actionName'])
			->setFormatName($this->requestArguments['formatName'])
			->setArguments($this->requestArguments['arguments'] ?? []);
	}

	/**
	 * Set the request array from JSON
	 *
	 * @param string $request
	 */
	protected function setRequestArgumentsFromJSON($request)
	{
		$requestArray = json_decode($request, true);
		if (is_array($requestArray)) {
			ArrayUtility::mergeRecursiveWithOverrule($this->requestArguments, $requestArray);
		}
	}

	/**
	 * Set the request array from the getPost array
	 */
	protected function setRequestArgumentsFromGetPost()
	{
		$validArguments = ['pluginName', 'actionName', 'formatName', 'arguments'];
		foreach ($validArguments as $argument) {
			if (GeneralUtility::_GP($argument) ?? false) {
				$this->requestArguments[$argument] = GeneralUtility::_GP($argument);
			} else if (GeneralUtility::_GP('amp;' . $argument) ?? false) {
				// Something went wrong...
				$this->requestArguments[$argument] = GeneralUtility::_GP('amp;' . $argument);
			} else if ($argument !== 'arguments') {
				throw new BadRequestException('An argument is missing', 1394587023);
			}
		}
	}

	/**
	 * @param string $pluginName
	 * @return EidDispatcher
	 */
	protected function setPluginName($pluginName = 'ImageGenerator')
	{
		$this->pluginName = htmlspecialchars((string)$pluginName);
		return $this;
	}

	/**
	 * @return EidDispatcher
	 */
	protected function setControllerName()
	{
		$this->controllerName = $this->pluginName;
		return $this;
	}

	/**
	 * @param string $actionName
	 * @return EidDispatcher
	 */
	protected function setActionName($actionName = 'show')
	{
		$this->actionName = htmlspecialchars((string)$actionName);
		return $this;
	}

	/**
	 * @param string $formatName
	 * @return EidDispatcher
	 */
	protected function setFormatName($formatName = 'txt')
	{
		$this->formatName = htmlspecialchars((string)$formatName);
		return $this;
	}

	/**
	 * @param array $arguments
	 * @return EidDispatcher
	 */
	protected function setArguments($arguments)
	{
		if (!is_array($arguments)) {
			$this->arguments = [];
		} else {
			$this->arguments = $arguments;
		}
		return $this;
	}
}