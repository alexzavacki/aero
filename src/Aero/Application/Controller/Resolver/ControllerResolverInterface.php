<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Application\Controller\Resolver;

/**
 * Controller resolver interface
 *
 * @category    Aero
 * @package     Aero_Application
 * @subpackage  Aero_Application_Controller
 * @author      Alex Zavacki
 */
interface ControllerResolverInterface
{
    /**
     * Controller resolver events
     */
    const NEW_CONTROLLER_CREATED = 'new.controller.created';


    /**
     * Returns the Controller instance associated with a Request.
     *
     * @param  mixed $matchedRoute
     * @return callback|null
     */
    public function getController($matchedRoute);

    /**
     * Returns the arguments to pass to the controller.
     *
     * @param  mixed    $matchedRoute
     * @param  callback $controller
     * @param  array    $variables
     * @return array
     */
    public function getArguments($matchedRoute, $controller, $variables = array());
}
