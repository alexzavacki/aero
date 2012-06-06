<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Config\Loader\Cache;

/**
 * Config loader from cache
 *
 * @category    Aero
 * @package     Aero_Config
 * @subpackage  Aero_Config_Loader
 * @subpackage  Aero_Config_Loader_Cache
 * @author      Alex Zavacki
 */
interface CacheInterface
{
    /**
     * @param  mixed $resource
     * @return array|null
     */
    public function load($resource);

    /**
     * @param  mixed $resource
     * @param  mixed $data
     * @param  array $params
     * @return \Aero\Config\Loader\Cache\CacheInterface
     */
    public function set($resource, $data, $params = array());
}
