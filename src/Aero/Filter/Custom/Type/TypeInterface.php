<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Filter\Custom\Type;

/**
 *
 *
 * @category    Aero
 * @package     Aero_Filter
 * @subpackage  Aero_Filter_Custom
 * @subpackage  Aero_Filter_Custom_Type
 * @author      Alex Zavacki
 */
interface TypeInterface
{
    /**
     * Constructor.
     *
     * @param mixed $filter
     * @param array $options
     */
    public function __construct($filter, $options = array());

    /**
     * Returns the result of filtering $value
     *
     * @param  mixed $value
     * @return mixed
     */
    public function filter($value);

    /**
     * @param  mixed $filter
     * @return mixed
     */
    public function setFilter($filter);

    /**
     * @return mixed
     */
    public function getFilter();
}
