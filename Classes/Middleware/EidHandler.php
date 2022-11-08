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
use TYPO3\CMS\Core\Http\DispatcherInterface;
use TYPO3\CMS\Core\Http\NullResponse;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Lightweight alternative to regular frontend requests; used when $_GET[eID] is set.
 * In the future, logic from the EidUtility will be moved to this class, however in most cases
 * a custom PSR-15 middleware will be better suited for whatever job the eID functionality does currently.
 *
 * @internal
 */
class EidHandler implements MiddlewareInterface
{
    /**
     * @var DispatcherInterface
     */
    protected $dispatcher;

	/**
	 * @param DispatcherInterface $dispatcher
	 */
	public function __construct(DispatcherInterface $dispatcher)
	{
		$this->dispatcher = $dispatcher;
	}
	
    /**
     * Dispatches the request to the corresponding eID class or eID script
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $eID = $request->getParsedBody()['eIDSR'] ?? $request->getQueryParams()['eIDSR'] ?? null;

        if ($eID === null) {
            return $handler->handle($request);
        }

        // Remove any output produced until now
        ob_clean();

        /** @var Response $response */
        $response = new Response();
        if (empty($eID) || !isset($GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['sr_freecap']['eIDSR_include'][$eID])) {
            $response = $response->withStatus(404, 'eIDSR not registered');
        } else {
            $configuration = $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['sr_freecap']['eIDSR_include'][$eID];
            $response = new NullResponse();
            // Simple check to make sure that it's not an absolute file (to use the fallback)
            if (strpos($configuration, '::') !== false || is_callable($configuration)) {
                $frontendUser = $request->getAttribute('frontend.user');
                $request = $request->withAttribute('target', $configuration);
                $response = $this->dispatcher->dispatch($request);
            }
        }
        return $response;
    }
}