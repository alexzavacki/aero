<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Config\Loader;

/**
 * Common config loader interface
 *
 * @category    Aero
 * @package     Aero_Config
 * @subpackage  Aero_Config_Loader
 * @author      Alex Zavacki
 */
interface LoaderInterface
{
    /**
     * Load config(s)
     * @param  mixed $resources
     * @return array
     */
    public function load($resources);
}
