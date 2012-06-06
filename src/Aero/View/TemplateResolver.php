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
class TemplateResolver implements TemplateResolverInterface
{
    /**
     * Get template file name
     *
     * @param  string $name
     * @return string|bool
     */
    public function resolve($name)
    {
        return is_file($name) ? $name : false;
    }
}
