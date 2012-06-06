<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Http\View;

use Aero\View\HelperPluginBroker as BaseHelperPluginBroker;

/**
 *
 *
 * @category    Aero
 * @package     Aero_Http
 * @subpackage  Aero_Http_View
 * @subpackage  Aero_Http_View_Helper
 * @author      Alex Zavacki
 */
class HelperPluginBroker extends BaseHelperPluginBroker
{
    /**
     * @var string
     */
    protected $defaultPluginMapClass = 'Aero\\Http\\View\\HelperPluginMap';
}
