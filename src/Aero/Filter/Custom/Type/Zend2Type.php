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
class Zend2Type extends AbstractType
{
    /**
     * Returns the result of filtering $value
     *
     * @throws \LogicException
     *
     * @param  mixed $value
     * @return mixed
     */
    public function filter($value)
    {
        if (!$this->filter instanceof \Zend\Filter\Filter) {
            throw new \LogicException(
                'Validator object not set or does not implement Zend\\Filter\\Filter'
            );
        }
        return $this->filter->filter($value);
    }

    /**
     * @throws \InvalidArgumentException
     *
     * @param  mixed $filter
     * @return \Aero\Filter\Custom\Type\AbstractType
     */
    public function setFilter($filter)
    {
        var_dump($filter);
        if (!$filter instanceof \Zend\Filter\Filter) {
            throw new \InvalidArgumentException('Filter must implement Zend\\Filter\\Filter');
        }
        $this->filter = $filter;
        return $this;
    }
}
