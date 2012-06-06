<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Application\Resource\Locator;

/**
 * Resource locator interface
 *
 * @category    Aero
 * @package     Aero_Application
 * @subpackage  Aero_Application_Resource
 * @subpackage  Aero_Application_Resource_Locator
 * @author      Alex Zavacki
 */
interface ResourceLocatorInterface
{
    /**
     * Find resource location
     *
     * @param  string $resource
     * @return string
     */
    public function locate($resource);
}
