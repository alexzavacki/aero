<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Filter;

/**
 * Abstract filter
 *
 * @category    Aero
 * @package     Aero_Filter
 * @author      Alex Zavacki
 */
abstract class AbstractFilter implements FilterInterface
{
    /**
     * Invoke filter as a command
     *
     * @throws \RuntimeException If filtering $value is impossible
     *
     * @param  mixed $value
     * @return mixed
     */
    public function __invoke($value)
    {
        return $this->filter($value);
    }

    /**
     * Set filter options
     *
     * @param  array $options
     * @return \Aero\Filter\AbstractFilter
     */
    public function setOptions($options = array())
    {
        // do nothing in abstract filter
        return $this;
    }
}
