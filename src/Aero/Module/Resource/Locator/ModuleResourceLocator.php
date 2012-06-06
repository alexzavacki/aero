<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Module\Resource\Locator;

use Aero\Application\Resource\Locator\StandardResourceLocator;

/**
 * Standard resource locator
 *
 * @category    Aero
 * @package     Aero_Module
 * @subpackage  Aero_Module_Resource
 * @subpackage  Aero_Module_Resource_Locator
 * @author      Alex Zavacki
 */
class ModuleResourceLocator extends StandardResourceLocator
{
    /**
     * Find resource location
     *
     * @param  string $resource
     * @return string
     */
    public function locate($resource)
    {
        if (strpos($resource, ':') === false) {
            return parent::locate($resource);
        }

        list($module, $controller, $path) = explode(':', $resource);

        $basepath = $this->appDir ? $this->appDir . '/' : '';

        return $basepath . 'modules/'
            . trim($module, '/')
            . '/Resources/views/'
            . trim($controller, '/') . '/'
            . $path;
    }
}
