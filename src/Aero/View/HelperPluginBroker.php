<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\View;

use Aero\Std\Plugin\PluginBroker;

/**
 *
 *
 * @category    Aero
 * @package     Aero_View
 * @author      Alex Zavacki
 */
class HelperPluginBroker extends PluginBroker
{
    /**
     * @var string
     */
    protected $defaultPluginMapClass = 'Aero\\View\\HelperPluginMap';

    /**
     * @var \Aero\View\View
     */
    protected $view;


    /**
     * Set view
     *
     * @param  \Aero\View\View $view
     * @return \Aero\View\HelperPluginBroker
     */
    public function setView(View $view = null)
    {
        $this->view = $view;

        foreach ($this->plugins as $helper) {
            if ($helper instanceof Helper\HelperInterface) {
                /** @var $helper \Aero\View\Helper\HelperInterface */
                $helper->setView($this->view);
            }
        }

        return $this;
    }

    /**
     * Get view
     *
     * @return \Aero\View\View
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * Create and return plugin by name
     *
     * @throws \InvalidArgumentException|\RuntimeException
     *
     * @param  string $name
     * @param  array $options
     * @return object
     */
    public function createPlugin($name, $options = array())
    {
        $helper = parent::createPlugin($name, $options);

        if ($helper instanceof Helper\HelperInterface) {
            /** @var $helper \Aero\View\Helper\HelperInterface */
            $helper->setView($this->view);
        }

        return $helper;
    }
}
