<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Config\Loader\Merger;

/**
 * Non-recursive merger
 *
 * @category    Aero
 * @package     Aero_Config
 * @subpackage  Aero_Config_Loader
 * @subpackage  Aero_Config_Loader_Merger
 * @author      Alex Zavacki
 */
class NonRecursiveMerger implements MergerInterface
{
    /**
     * @param  array $dest
     * @param  array $source
     * @return array
     */
    public function merge($dest, $source)
    {
        return array_merge((array) $dest, (array) $source);
    }
}
