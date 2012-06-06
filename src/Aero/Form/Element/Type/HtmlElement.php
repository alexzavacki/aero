<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Form\Element\Type;

use Aero\Form\Element\AbstractElement;
use Aero\Form\Attributes;

/**
 *
 *
 * @category    Aero
 * @package     Aero_Form
 * @subpackage  Aero_Form_Element
 * @subpackage  Aero_Form_Element_Type
 * @author      Alex Zavacki
 */
class HtmlElement extends AbstractElement
{
    /**
     * @var string
     */
    protected $fieldType = 'text';


    /**
     * @return string
     */
    public function render()
    {
        $data = array(
            'id'       => $this->getId(),
            'name'     => $this->getFullName(),
            'value'    => $this->getValue(),
            'disabled' => false,
            'escape'   => true
        );

        return '<input type="' . $this->fieldType . '"'
            . ' name="'  . $data['name']  . '"'
            . ' id="'    . $data['id']    . '"'
            . ' value="' . $data['value'] . '"'
            . ($data['disabled'] ? ' disabled="disabled"' : '')
            . ' ' . $this->attributes()
            . ' />';
    }

    /**
     * @param  string $name
     * @param  string $value
     * @param  array  $attribs
     *
     * @return string
     */
    protected function hidden($name, $value = null, $attribs = array())
    {
        return '<input type="hidden"'
            . ' name="' . $name . '"'
            . ' value="' . $value . '"'
            . ' ' . Attributes::arrayToString($attribs)
            . ' />';
    }
}
