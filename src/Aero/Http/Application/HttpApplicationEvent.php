<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Http\Application;

use Aero\Application\ApplicationEvent;

use Aero\Http\Request;
use Aero\Http\Response;

/**
 * Http application event
 *
 * @category    Aero
 * @package     Aero_Http
 * @subpackage  Aero_Http_Application
 * @author      Alex Zavacki
 */
class HttpApplicationEvent extends ApplicationEvent
{
    /**
     * Application events
     */
    const REQUEST       = 'application.request';

    const ROUTE         = 'application.route';
    const ROUTE_POST    = 'application.route.post';

    const DISPATCH      = 'application.dispatch';

    const VIEW          = 'application.view';
    const RESPONSE      = 'application.response';

    const EXCEPTION     = 'application.exception';

    const ROUTE_NOT_FOUND      = 'application.no.route';
    const CONTROLLER_NOT_FOUND = 'application.no.contoller';


    /**
     * @return \Aero\Http\Request
     */
    public function getRequest()
    {
        return $this->getParam('request');
    }

    /**
     * @param  \Aero\Http\Request $request
     * @return \Aero\Http\Application\HttpApplicationEvent
     */
    public function setRequest(Request $request)
    {
        $this->setParam('request', $request);
        return $this;
    }

    /**
     * @return \Aero\Http\Response
     */
    public function getResponse()
    {
        return $this->getParam('response');
    }

    /**
     * @param  \Aero\Http\Response $response
     * @return \Aero\Http\Application\HttpApplicationEvent
     */
    public function setResponse(Response $response)
    {
        $this->setParam('response', $response);
        return $this;
    }
}
