<?php
namespace App\Service\Traits;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;

trait WxGzTrait
{
    /**
     * @param string $key
     * @param null $lifeTime
     * @return FilesystemAdapter
     */
    protected function getCache($key = 'app.wxgzcache', $lifeTime = null)
    {
        if (!$lifeTime) $lifeTime = $this->container->getParameter('wx_cache_expire_time');
        return new FilesystemAdapter($key, $lifeTime);
    }

    protected function getCacheItem($cache, $key)
    {
        if ($cache) {
            if ($cache->hasItem($key)) {
                $obj = $cache->getItem($key);
                return $obj->get();
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    protected function saveCacheItem($cache, $key, $val)
    {
        if ($cache) {
            $obj = $cache->getItem($key);
            $obj->set($val);
            return $cache->save($obj);
        } else {
            return false;
        }
    }
}