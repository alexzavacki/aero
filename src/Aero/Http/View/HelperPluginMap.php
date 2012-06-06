<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Http\View;

use Aero\View\HelperPluginMap as BaseHelperPluginMap;

/**
 *
 *
 * @category    Aero
 * @package     Aero_Http
 * @subpackage  Aero_Http_View
 * @subpackage  Aero_Http_View_Helper
 * @author      Alex Zavacki
 */
class HelperPluginMap extends BaseHelperPluginMap
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
                '' => __NAMESPACE__ . '\\Helper\\TestHelper',
            )
        );
    }
}
