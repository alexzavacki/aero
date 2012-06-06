<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Filter;

/**
 * Filter interface
 *
 * @category    Aero
 * @package     Aero_Filter
 * @author      Alex Zavacki
 */
interface FilterInterface
{
    /**
     * Returns the result of filtering $value
     *
     * @param  mixed $value
     * @return mixed
     */
    public function filter($value);
}
