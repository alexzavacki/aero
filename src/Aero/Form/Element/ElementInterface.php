<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Form\Element;

use Aero\Form\NodeInterface;

/**
 *
 *
 * @category    Aero
 * @package     Aero_Form
 * @subpackage  Aero_Form_Element
 * @author      Alex Zavacki
 */
interface ElementInterface extends NodeInterface
{
    /**
     * @param  mixed $value
     * @return \Aero\Form\Element\ElementInterface
     */
    public function setValue($value);

    /**
     * @return mixed
     */
    public function getValue();

    /**
     * @param  mixed $value
     * @param  mixed $context
     *
     * @return bool
     */
    public function isValid($value, $context = null);
}
