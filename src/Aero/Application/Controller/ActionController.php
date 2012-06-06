<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Application\Controller;

use Aero\Std\Plugin\PluginMap;
use Aero\Std\Plugin\PluginBroker as BasePluginBroker;

use Aero\Std\Di\ServiceLocator;
use Aero\Std\Di\ServiceLocatorAware;

use Aero\Application\ApplicationEvent;
use Aero\Application\InjectApplicationEventInterface;

/**
 * Abstract action controller
 *
 * @category    Aero
 * @package     Aero_Application
 * @subpackage  Aero_Application_Controller
 * @author      Alex Zavacki
 */
abstract class ActionController implements ServiceLocatorAware, InjectApplicationEventInterface
{
    /**
     * @var \Aero\Std\Di\ServiceLocator Application service locator
     */
    protected $locator;

    /**
     * @var \Aero\Application\ApplicationEvent
     */
    protected $event;

    /**
     * @var \Aero\Std\Plugin\PluginBroker
     */
    protected $pluginBroker;

    /**
     * @var \Aero\Std\Plugin\PluginBroker
     */
    protected static $staticPluginBroker;

    /**
     * @var bool
     */
    protected static $useStaticPluginBroker = true;


    /**
     * Set the locator
     *
     * @param  \Aero\Std\Di\ServiceLocator $locator
     * @return \Aero\Application\Controller\ActionController
     */
    public function setLocator(ServiceLocator $locator)
    {
        $this->locator = $locator;
        return $this;
    }

    /**
     * Get the locator
     *
     * @return \Aero\Std\Di\ServiceLocator
     */
    public function getLocator()
    {
        return $this->locator;
    }

    /**
     * Assign application event
     *
     * @param  \Aero\Application\ApplicationEvent $event
     * @return \Aero\Application\Controller\ActionController
     */
    public function setEvent(ApplicationEvent $event)
    {
        $this->event = $event;
        return $this;
    }

    /**
     * Get application event
     *
     * @return \Aero\Application\ApplicationEvent
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Set plugin broker
     *
     * @param  \Aero\Std\Plugin\PluginBroker $pluginBroker
     * @return \Aero\Application\Controller\ActionController
     */
    public function setPluginBroker(BasePluginBroker $pluginBroker)
    {
        if ($pluginBroker instanceof PluginBroker) {
            /** @var $pluginBroker \Aero\Application\Controller\PluginBroker */
            $pluginBroker->setController($this);
        }

        $this->pluginBroker = $pluginBroker;

        return $this;
    }

    /**
     * @return \Aero\Std\Plugin\PluginBroker
     */
    public function getPluginBroker()
    {
        if (!$this->pluginBroker instanceof BasePluginBroker) {
            $this->pluginBroker = $this->createDefaultPluginBroker();
        }
        return $this->pluginBroker;
    }

    /**
     * @return \Aero\Application\Controller\PluginBroker
     */
    protected function createDefaultPluginBroker()
    {
        return new PluginBroker();
    }

    /**
     * @param \Aero\Std\Plugin\PluginBroker $pluginBroker
     */
    public static function setStaticPluginBroker(BasePluginBroker $pluginBroker)
    {
        static::$staticPluginBroker = $pluginBroker;
    }

    /**
     * @return \Aero\Std\Plugin\PluginBroker
     */
    public static function getStaticPluginBroker()
    {
        if (!static::$staticPluginBroker instanceof BasePluginBroker) {
            static::$staticPluginBroker = static::createDefaultStaticPluginBroker();
        }
        return static::$staticPluginBroker;
    }

    /**
     * @return \Aero\Application\Controller\PluginBroker
     */
    protected static function createDefaultStaticPluginBroker()
    {
        return new PluginBroker();
    }

    /**
     * @param  bool|null $flag
     * @return bool
     */
    public static function useStaticPluginBroker($flag = null)
    {
        if ($flag !== null) {
            static::$useStaticPluginBroker = (bool) $flag;
        }
        return static::$useStaticPluginBroker;
    }

    /**
     * Get plugin instance
     *
     * @param  string $name
     * @param  array $options
     * @return object
     */
    public function plugin($name, array $options = array())
    {
        $pluginBroker = static::$useStaticPluginBroker === true
            ? static::getStaticPluginBroker()
            : $this->getPluginBroker();

        return $pluginBroker->getPlugin($name, $options);
    }

    /**
     * Method overloading: return/call plugins
     *
     * If the plugin is a functor, call it, passing the parameters provided.
     * Otherwise, return the plugin instance.
     *
     * @param  string $method
     * @param  array $params
     * @return mixed
     */
    public function __call($method, array $params)
    {
        $plugin = $this->plugin($method);

        if (is_callable($plugin)) {
            return call_user_func_array($plugin, $params);
        }

        return $plugin;
    }

    /**
     * Transform an action name into a method name
     *
     * @param  string $action
     * @return string
     */
    public static function getActionMethod($action)
    {
        if (substr($action, -6) == 'Action') {
            return $action;
        }

        $method = str_replace(array('.', '-', '_'), ' ', $action);
        $method = ucwords($method);
        $method = str_replace(' ', '', $method);
        $method = lcfirst($method);

        return $method . 'Action';
    }
}
