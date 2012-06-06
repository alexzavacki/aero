<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Module\Controller\Resolver;

use Aero\Application\Controller\Resolver\ControllerNameParserInterface;

/**
 * Standard module controller name parser
 *
 * @category    Aero
 * @package     Aero_Module
 * @subpackage  Aero_Module_Controller
 * @author      Alex Zavacki
 */
class StandardNameParser implements ControllerNameParserInterface
{
    /**
     * Parse controller string and convert it to a class::method.
     *
     * @param  string $controller
     * @return string
     */
    public function parse($controller)
    {
        if (count($parts = explode(':', $controller)) != 3) {
            throw new \InvalidArgumentException(
                sprintf('The "%s" controller is not a valid a:b:c controller string.', $controller)
            );
        }

        list($module, $controller, $action) = $parts;
        $controller = str_replace('/', '\\', $controller);

        $class = ucfirst($module).  '\\Controller\\' . ucfirst($controller) . 'Controller';

        return $class . '::' . $action;
    }
}
