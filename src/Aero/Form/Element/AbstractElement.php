<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Form\Element;

use Aero\Form\Node;
use Aero\Form\Collection;

use Aero\Form\Form;
use Aero\Form\Attributes;

use Aero\Form\Element\FilterCollection;
use Aero\Form\Element\ValidatorCollection;

/**
 *
 *
 * @category    Aero
 * @package     Aero_Form
 * @subpackage  Aero_Form_Element
 * @author      Alex Zavacki
 */
abstract class AbstractElement extends Node implements ElementInterface
{
    /**
     * @var mixed
     */
    protected $value;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var \Aero\Form\Element\FilterCollection
     */
    protected $filters;

    /**
     * @var \Aero\Form\Element\ValidatorCollection
     */
    protected $validators;

    /**
     * @var array
     */
    protected $validationErrors = array();


    /**
     * @param  array $options
     * @return \Aero\Form\Element\AbstractElement|\Aero\Form\Node
     */
    public function setOptions(array $options)
    {
        foreach (array_change_key_case($options, CASE_LOWER) as $key => $value)
        {
            switch (str_replace(array('_', '-', '.'), '', $key))
            {
                case '':
                    break;
            }
        }

        return $this;
    }

    /**
     * @return \Aero\Form\Element\AbstractElement|\Aero\Form\Node
     */
    public function reset()
    {
        $this->setValue(null);

        if ($this->nodes instanceof Collection) {
            foreach ($this->nodes->all() as $node) {
                /** @var $node \Aero\Form\Node */
                $node->reset();
            }
        }

        return $this;
    }

    /**
     * @param  mixed $value
     * @param  mixed $context
     *
     * @return bool
     */
    public function isValid($value, $context = null)
    {
        $this->validationErrors = array();

        // Set raw value
        $this->setValue($value);

        // Get filtered value
        $value = $this->getValue();

        $valid = true;

        foreach ($this->validators()->all() as $validator)
        {
            if ($this->isArray && is_array($value)) {
                foreach ($value as $val) {
                    if (!$validator->isValid($val, $context)) {
                        $valid = false;
                        break;
                    }
                }
                if ($valid) {
                    continue;
                }
            }
            elseif ($validator->isValid($value, $context)) {
                continue;
            }

            $valid = false;
            $this->validationErrors = array_merge(
                $this->validationErrors, $validator->getMessages()
            );

            if ($validator->afBreakChainOnFailure) {
                break;
            }
        }

        if ($this->customErrors) {
            return false;
        }

        return $valid;
    }

    /**
     * @return string
     */
    public function getFullName()
    {
        $name = parent::getFullName();

        if ($this->isArray) {
            $name .= '[]';
        }

        return $name;
    }

    /**
     * @param  mixed $value
     * @return \Aero\Form\Element\AbstractElement
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRawValue()
    {
        return $this->value;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        $filteredValue = $this->value;

        if ($this->isArray && is_array($filteredValue)) {
            array_walk_recursive($filteredValue, array($this, 'filterValue'));
        }
        else {
            $this->filterValue($filteredValue);
        }

        return $filteredValue;
    }

    /**
     * @param  mixed $value
     * @param  mixed $key
     *
     * @return void
     */
    protected function filterValue(&$value, $key = null)
    {
        foreach ($this->filters()->all() as $filter) {
            //$value = $filter->filter($value);
        }
    }

    /**
     * @return \Aero\Form\Element\FilterCollection
     */
    public function filters()
    {
        if (!$this->filters instanceof FilterCollection) {
            $this->filters = new FilterCollection();
        }
        return $this->filters;
    }

    /**
     * @return \Aero\Form\Element\ValidatorCollection
     */
    public function validators()
    {
        if (!$this->validators instanceof ValidatorCollection) {
            $this->validators = new ValidatorCollection();
        }
        return $this->validators;
    }

    /**
     * @param  string $label
     * @return \Aero\Form\Element\AbstractElement
     */
    public function setLabel($label)
    {
        $this->label = (string) $label;
        return $this;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return string
     */
    public function renderLabel()
    {
        $id    = $this->getId();
        $label = $this->getLabel();

        if ($id == '' || $label == '') {
            return '';
        }

        return '<label for="' . $id . '">' . $label . '</label>';
    }

    /**
     * @param  string $listClass
     * @return string
     */
    public function renderValidationErrors($listClass = 'validation-errors')
    {
        return $this->renderErrors($this->validationErrors, $listClass);
    }

    /**
     * @return array
     */
    public function getErrorMessages()
    {
        return array_merge((array) $this->validationErrors, (array) $this->customErrors);
    }

    /**
     * @return bool
     */
    public function hasErrors()
    {
        return $this->validationErrors || $this->customErrors;
    }

    /**
     * @return array
     */
    public function getValidationErrors()
    {
        return $this->validationErrors;
    }

    /**
     * @return bool
     */
    public function hasValidationErrors()
    {
        return $this->validationErrors ? true : false;
    }

    /**
     * @return \Aero\Form\Element\AbstractElement|\Aero\Form\Node
     */
    public function clearValidationErrors()
    {
        $this->validationErrors = array();
        return $this;
    }

}
