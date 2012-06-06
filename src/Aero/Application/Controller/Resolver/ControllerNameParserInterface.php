<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Application\Controller\Resolver;

/**
 * Controller name parser interface
 *
 * @category    Aero
 * @package     Aero_Application
 * @subpackage  Aero_Application_Controller
 * @author      Alex Zavacki
 */
interface ControllerNameParserInterface
{
    /**
     * Parse controller string and convert it to a class::method.
     *
     * @param  string $controller
     * @return string
     */
    public function parse($controller);
}
