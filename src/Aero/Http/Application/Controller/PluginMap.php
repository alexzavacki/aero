<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Http\Application\Controller;

use Aero\Application\Controller\PluginMap as BasePluginMap;

/**
 *
 *
 * @package     Aero_Http
 * @subpackage  Aero_Application
 * @subpackage  Aero_Application_Controller
 * @subpackage  Aero_Application_Controller_Plugin
 * @author      Alex Zavacki
 */
class PluginMap extends BasePluginMap
{
    /**
     * Get array of default plugins
     *
     * @return array
     */
    public function getDefaultMap()
    {
        return array_merge(
            (array) parent::getDefaultMap(),
            array(
                'redirect' => __NAMESPACE__ . '\\Plugin\\RedirectPlugin',
            )
        );
    }
}
