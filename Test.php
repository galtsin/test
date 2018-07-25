<?php

namespace src\Integration;

interface DataProviderInterface
{
    /**
     * @param array $request
     *
     * @return array 
     **/
    public function get(array $request);
}
?>

<?php

namespace src\Integration;


class SourceXProvider implements DataProviderInterface
{
    /**
     * @param array $config
     */
    public function __constructor(array $config)
    {
        // init
    }
    
    /**
     * @param array $request
     *
     * @return array 
     **/
    public function get(array $request)
    {
        // do something
    }
}
?>

<?php

namespace src\Integration;

use DateTime;
use Exception;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;

class DataProviderManager implements DataProviderInterface
{
    private $dataProvider;
    private $cache;
    private $logger;
    
    /**
     * @param DataProviderInterface $dataProvider
     * @param CacheItemPoolInterface $cache
     * @param LoggerInterface $logger
     */
    public function __constructor(DataProviderInterface $dataProvider, CacheItemPoolInterface $cache, LoggerInterface $logger)
    {
        $this->dataProvider = $dataProvider;
        $this->cache = $cache;
        $this->logger = $logger;
    }
    
    /**
     * @param array $request
     *
     * @return array 
     **/
    public function get(array $request)
    {
        try {
            $cacheKey = $this->getCacheKey($request);
            $cacheItem = $this->cache->getItem($cacheKey);
            if ($cacheItem->isHit()) {
                return $cacheItem->get();
            }

            $result = $this->dataProvider->get($request);

            $cacheItem
                ->set($result)
                ->expiresAt(
                    (new DateTime())->modify('+1 day')
                );

            return $result;
        } catch (Exception $e) {
            $this->logger->critical('Error');
        }

        return [];
    }
}
?>
