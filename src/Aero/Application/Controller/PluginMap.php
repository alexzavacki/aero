<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Application\Controller;

use Aero\Std\Plugin\PluginMap as BasePluginMap;

/**
 *
 *
 * @package     Aero_Application
 * @subpackage  Aero_Application_Controller
 * @subpackage  Aero_Application_Controller_Plugin
 * @author      Alex Zavacki
 */
class PluginMap extends BasePluginMap
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
        return array();
    }
}
