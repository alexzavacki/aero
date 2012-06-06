<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Loader;

/**
 * Must be registered before StandardAutoloader (prepend=true)
 * and load (lazy) pack that contains requirement class (interface)
 *
 * $form = new \Aero\Form\Form(); -> loads Aero.Form.pack.php
 *
 * @category    Aero
 * @package     Aero_Loader
 * @author      Alex Zavacki
 */
class PackAutoloader
{

}
