<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\View;

/**
 *
 *
 * @category    Aero
 * @package     Aero_View
 * @author      Alex Zavacki
 */
interface TemplateResolverInterface
{
    /**
     * Get template file name
     *
     * @param  string $name
     * @return string
     */
    public function resolve($name);
}
