<?php

namespace App\Middleware;

class CacheKillerMiddleware extends Middleware
{
    public function __invoke($request, $response, $next)
    {
        $cachedViewsDirectory = $this->container['settings']['renderer']['blade_cache_path'];

        if ($handle = opendir($cachedViewsDirectory)) {

            while (false !== ($entry = readdir($handle))) {
                if(strpos($entry, '.') === 0) continue;
                @unlink($cachedViewsDirectory .DIRECTORY_SEPARATOR. $entry);
            }
            closedir($handle);
        }

        $response = $next($request, $response);
        return $response;
    }
}