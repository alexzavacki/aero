<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Config\Loader\Merger;

/**
 * Config merger interface
 *
 * @category    Aero
 * @package     Aero_Config
 * @subpackage  Aero_Config_Loader
 * @subpackage  Aero_Config_Loader_Merger
 * @author      Alex Zavacki
 */
interface MergerInterface
{
    /**
     * @param  array $dest
     * @param  array $source
     * @return array
     */
    public function merge($dest, $source);
}
