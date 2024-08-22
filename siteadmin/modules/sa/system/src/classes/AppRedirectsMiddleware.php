<?php


namespace sa\system;


use sacore\application\app;
use sacore\application\Middleware;
use sacore\application\responses\Redirect;

/**
 * Class SaAuthMiddleware
 * @package sa\system
 */
class AppRedirectsMiddleware extends Middleware
{
    /**
     * @param \sacore\application\Request $request
     * @return mixed
     */
    public function BeforeRoute($request)
    {
        $requireSSL = app::getInstance()->getConfiguration()->get('require_ssl')->getValue();

        if($requireSSL && !$request->isSecure()){
            $host = $request->getHost();
            $base = $request->getBaseUrl();
            $pathInfo = $request->getPathInfo();

            if($pathInfo == '/system/thread/test' || $pathInfo == '/system/thread/run'){
                return;
            }

            if (null !== $qs = $request->getQueryString()) {
                $qs = '?'.$qs;
            }

            return $redirect = new Redirect('https://'.$host.$base.$pathInfo.$qs, true);
        }
    }
}
