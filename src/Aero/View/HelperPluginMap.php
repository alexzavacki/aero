<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\View;

use Aero\Std\Plugin\PluginMap;

/**
 *
 *
 * @category    Aero
 * @package     Aero_View
 * @author      Alex Zavacki
 */
class HelperPluginMap extends PluginMap
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
            'test' => __NAMESPACE__ . '\\Helper\\TestHelper',
            'slots' => __NAMESPACE__ . '\\Helper\\SlotsHelper',
        );
    }
}
