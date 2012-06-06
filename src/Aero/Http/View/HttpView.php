<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Http\View;

use Aero\View\View;

/**
 *
 *
 * @category    Aero
 * @package     Aero_Http
 * @subpackage  Aero_Http_View
 * @author      Alex Zavacki
 */
class HttpView extends View
{
    /**
     * @var string
     */
    protected $defaultEscapeContext = 'html';


    /**
     * @return \Aero\Http\View\HelperPluginBroker
     */
    protected function createDefaultHelperPluginBroker()
    {
        return new HelperPluginBroker();
    }
}
