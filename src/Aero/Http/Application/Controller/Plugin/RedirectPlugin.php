<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Http\Application\Controller\Plugin;

use Aero\Application\Controller\Plugin\AbstractPlugin;

/**
 *
 *
 * @package     Aero_Http
 * @subpackage  Aero_Application
 * @subpackage  Aero_Application_Controller
 * @subpackage  Aero_Application_Controller_Plugin
 * @author      Alex Zavacki
 */
class RedirectPlugin extends AbstractPlugin
{
    public function __invoke($url)
    {
        header('Location: ' . $url);
        die;
    }
}
