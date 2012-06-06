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
abstract class AbstractType implements TypeInterface
{
    /**
     * @var mixed
     */
    protected $filter;


    /**
     * Constructor.
     *
     * @param mixed $filter
     * @param array $options
     */
    public function __construct($filter, $options = array())
    {
        $this->setFilter($filter);

        if ($options) {
            $this->setOptions($options);
        }
    }

    /**
     * @param  array $options
     * @return \Aero\Filter\Custom\Type\AbstractType
     */
    public function setOptions(array $options)
    {
        // do nothing in abstract type
        return $this;
    }

    /**
     * @param  mixed $filter
     * @return \Aero\Filter\Custom\Type\AbstractType
     */
    public function setFilter($filter)
    {
        $this->filter = $filter;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFilter()
    {
        return $this->filter;
    }
}
