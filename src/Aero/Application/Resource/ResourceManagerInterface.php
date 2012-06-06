<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Application\Resource;

/**
 * Resource manager interface
 *
 * @category    Aero
 * @package     Aero_Application
 * @subpackage  Aero_Application_Resource
 * @author      Alex Zavacki
 */
interface ResourceManagerInterface
{
    /**
     * Find resource location
     *
     * @param  string $resource
     * @return string
     */
    public function locate($resource);
}
