<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\View\Helper;

use Aero\View\View;

/**
 *
 *
 * @category    Aero
 * @package     Aero_View
 * @subpackage  Aero_View_Helper
 * @author      Alex Zavacki
 */
interface HelperInterface
{
    /**
     * Set view
     *
     * @param  \Aero\View\View $view
     * @return \Aero\View\Helper\HelperInterface
     */
    public function setView(View $view = null);

    /**
     * Get view
     *
     * @return \Aero\View\View
     */
    public function getView();
}
