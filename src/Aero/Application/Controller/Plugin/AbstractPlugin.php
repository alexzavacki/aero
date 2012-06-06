<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Application\Controller\Plugin;

use Aero\Application\Controller\ActionController;

/**
 *
 *
 * @package     Aero_Application
 * @subpackage  Aero_Application_Controller
 * @subpackage  Aero_Application_Controller_Plugin
 * @author      Alex Zavacki
 */
abstract class AbstractPlugin
{
    /**
     * @var \Aero\Application\Controller\ActionController
     */
    protected $controller;

    /**
     * Set controller object
     *
     * @param  \Aero\Application\Controller\ActionController $controller
     * @return \Aero\Application\Controller\Plugin\AbstractPlugin
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
}
