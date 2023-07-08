<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace SJBR\SrFreecap\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
//use TYPO3\CMS\Core\Error\Http\BadRequestException;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Localization\Locales;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Core\Bootstrap;

/**
 * Handles Freecap request
 */
class CaptchaRequestHandler implements MiddlewareInterface
{
	/**
	 * @var string
	 */
	protected $vendorName = 'SJBR';

	/**
	 * @var string
	 */
	protected $extensionName = 'SrFreecap';
	
    /**
     * Dispatches the request to the corresponding eID class or eID script
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $srFreecap = $request->getQueryParams()['srFreecap'] ?? null;

        if ($srFreecap === null) {
            return $handler->handle($request);
        }

        // Remove any output produced until now
        ob_clean();

        /** @var Response $response */
		$this->initLanguage($request);
		$response = $this->dispatch($request);
		$response = new Response();
		return $response->withStatus(200, 'Captcha sent');
        //return $response;
    }

	/**
	 * Builds an extbase context and returns the response
	 *
	 * @param ServerRequestInterface $request
	 */
	protected function dispatch(ServerRequestInterface $request)
	{
		$bootstrap = GeneralUtility::makeInstance(Bootstrap::class);
		$configuration = $request->getAttribute('frontend.typoscript')->getFlatSettings();
		$configuration['vendorName'] = $this->vendorName;
		$configuration['extensionName'] = $this->extensionName;
		$configuration['pluginName'] = htmlspecialchars((string)$request->getQueryParams()['pluginName'] ?? 'ImageGenerator');
		$configuration['actionName'] = htmlspecialchars((string)$request->getQueryParams()['actionName'] ?? 'show');
		$request = $bootstrap->initialize($configuration, $request);
		return $bootstrap->handleFrontendRequest($request);
	}

	/**
	 * Set locale
	 *
	 * @param ServerRequestInterface $request
	 */
	protected function initLanguage(ServerRequestInterface $request): void
	{
		$controller = $request->getAttribute('frontend.controller');
		$siteLanguage = $controller->getLanguage();
		$locales = GeneralUtility::makeInstance(Locales::class);
		$locales->setSystemLocaleFromSiteLanguage($siteLanguage);
	}
}