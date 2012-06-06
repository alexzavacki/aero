<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Filter\Custom;

use Aero\Std\Plugin\PluginMap as BasePluginMap;

/**
 *
 *
 * @package     Aero_Filter
 * @subpackage  Aero_Filter_Custom
 * @author      Alex Zavacki
 */
class TypePluginMap extends BasePluginMap
{
    /**
     * @var array
     */
    protected static $staticMap = array();

    /**
     * Get array of default plugins
     *
     * @return array
     */
    public function getDefaultMap()
    {
        return array(
            'zend2' => __NAMESPACE__ . '\\Type\\Zend2Type',
            'zf2'   => __NAMESPACE__ . '\\Type\\Zend2Type',
        );
    }
}
