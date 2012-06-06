<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Http\Application\Controller;

use Aero\Application\Controller\PluginBroker as BasePluginBroker;

/**
 *
 *
 * @package     Aero_Http
 * @subpackage  Aero_Application
 * @subpackage  Aero_Application_Controller
 * @subpackage  Aero_Application_Controller_Plugin
 * @author      Alex Zavacki
 */
class PluginBroker extends BasePluginBroker
{
    /**
     * @var string
     */
    protected $defaultPluginMapClass = 'Aero\\Http\\Application\\Controller\\PluginMap';
}
