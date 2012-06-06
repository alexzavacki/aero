<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Application\Resource\Locator;

/**
 * Standard resource locator
 *
 * @category    Aero
 * @package     Aero_Application
 * @subpackage  Aero_Application_Resource
 * @subpackage  Aero_Application_Resource_Locator
 * @author      Alex Zavacki
 */
class StandardResourceLocator implements ResourceLocatorInterface
{
    /**
     * @var string
     */
    protected $appDir = '';


    /**
     * Constructor.
     *
     * @param array $options
     */
    public function __construct($options = array())
    {
        if (isset($options['app_dir'])) {
            $this->setAppDir($options['app_dir']);
        }
    }

    /**
     * Find resource location
     *
     * @param  string $resource
     * @return string
     */
    public function locate($resource)
    {
        $basepath = $this->appDir ? $this->appDir . '/' : '';
        return $basepath . 'Resources/' . $resource;
    }

    /**
     * @param  string $appDir
     * @return \Aero\Application\Resource\Locator\StandardResourceLocator
     */
    public function setAppDir($appDir)
    {
        if (!is_dir($appDir = realpath($appDir))) {
            throw new \InvalidArgumentException('Application dir must exist');
        }
        $this->appDir = $appDir;
        return $this;
    }

    /**
     * @return string
     */
    public function getAppDir()
    {
        return $this->appDir;
    }
}
