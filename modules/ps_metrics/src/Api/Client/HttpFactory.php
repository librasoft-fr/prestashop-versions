<?php

/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */
namespace PrestaShop\Module\Ps_metrics\Api\Client;

use PrestaShop\Module\Ps_metrics\Middleware\CheckResponseMiddleware;
use PrestaShop\Module\Ps_metrics\Middleware\LogMiddleware;
use PrestaShop\Module\Ps_metrics\Middleware\Middleware;
use PrestaShop\Module\Ps_metrics\Middleware\ResponseMiddleware;
use PrestaShop\Module\Ps_metrics\Middleware\SentryMiddleware;
use Symfony\Component\HttpClient\HttpClient;
class HttpFactory
{
    /**
     * @var array
     */
    private $header;
    /**
     * @var string
     */
    private $url;
    /**
     * @var string
     */
    private $route = '';
    /**
     * @var Middleware
     */
    private $middlewareManager;
    /**
     * @var CheckResponseMiddleware
     */
    private $checkResponseMiddleware;
    /**
     * @var LogMiddleware
     */
    private $logMiddleware;
    /**
     * @var SentryMiddleware
     */
    private $sentryMiddleware;
    /**
     * @var ResponseMiddleware
     */
    private $responseMiddleWare;
    /**
     * ClientFactory constructor.
     *
     * @param CheckResponseMiddleware $checkResponseMiddleware
     * @param LogMiddleware $logMiddleware
     * @param SentryMiddleware $sentryMiddleware
     * @param ResponseMiddleware $responseMiddleWare
     */
    public function __construct(CheckResponseMiddleware $checkResponseMiddleware, LogMiddleware $logMiddleware, SentryMiddleware $sentryMiddleware, ResponseMiddleWare $responseMiddleWare)
    {
        $this->checkResponseMiddleware = $checkResponseMiddleware;
        $this->logMiddleware = $logMiddleware;
        $this->sentryMiddleware = $sentryMiddleware;
        $this->responseMiddleWare = $responseMiddleWare;
    }
    /**
     * @return array
     */
    public function get()
    {
        $response = HttpClient::create()->request('GET', $this->getUrl() . $this->getRoute());
        return $this->getMiddlewareManager()->execute($response);
    }
    /**
     * @param array $postBody
     *
     * @return array
     */
    public function post($postBody = [])
    {
        $response = HttpClient::create()->request('POST', $this->getUrl() . $this->getRoute(), ['headers' => ['Content-Type' => 'application/json; charset=utf-8'], 'body' => \json_encode($postBody)]);
        return $this->getMiddlewareManager()->execute($response);
    }
    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }
    /**
     * @param string $url
     *
     * @return void
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }
    /**
     * @return string
     */
    public function getRoute()
    {
        return $this->route;
    }
    /**
     * @param string $route
     *
     * @return void
     */
    public function setRoute($route)
    {
        $this->route = $route;
    }
    /**
     * @return mixed
     */
    public function getHeader()
    {
        return $this->header;
    }
    /**
     * @param array $header
     *
     * @return void
     */
    public function setHeader($header)
    {
        $this->header = $header;
    }
    /**
     * @return Middleware
     */
    public function getMiddlewareManager()
    {
        return $this->middlewareManager;
    }
    /**
     * @param Middleware $middlewareManager
     *
     * @return void
     */
    public function setMiddlewareManager($middlewareManager)
    {
        $this->middlewareManager = $middlewareManager;
    }
    /**
     * @return void
     */
    public function setMiddlewares()
    {
        $middlewareManager = $this->checkResponseMiddleware;
        $middlewareManager->linkWith($this->logMiddleware)->linkWith($this->sentryMiddleware)->linkWith($this->responseMiddleWare);
        $this->setMiddlewareManager($middlewareManager);
    }
}
