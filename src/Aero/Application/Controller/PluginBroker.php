<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Application\Controller;

use Aero\Std\Plugin\PluginBroker as BasePluginBroker;

/**
 *
 *
 * @package     Aero_Application
 * @subpackage  Aero_Application_Controller
 * @subpackage  Aero_Application_Controller_Plugin
 * @author      Alex Zavacki
 */
class PluginBroker extends BasePluginBroker
{
    /**
     * @var string
     */
    protected $defaultPluginMapClass = 'Aero\\Application\\Controller\\PluginMap';

    /**
     * @var \Aero\Application\Controller\ActionController
     */
    protected $controller;


    /**
     * Set controller object
     *
     * @param  \Aero\Application\Controller\ActionController $controller
     * @return \Aero\Application\Controller\PluginBroker
     */
    public function setController(ActionController $controller)
    {
        $this->controller = $controller;
        return $this;
    }

    /**
     * Get controller object
     *
     * @return \Aero\Application\Controller\ActionController
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * Get plugin by name
     *
     * Injects the controller object into the plugin
     *
     * @param  string $name
     * @param  array $options
     * @return object
     */
    public function getPlugin($name, $options = array())
    {
        $plugin = parent::getPlugin($name, $options);

        if (method_exists($plugin, 'setController')) {
            if (($controller = $this->getController()) !== null) {
                $plugin->setController($controller);
            }
        }

        return $plugin;
    }
}
