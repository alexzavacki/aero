<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Form;

/**
 * Form
 *
 * @category    Aero
 * @package     Aero_Form
 * @author      Alex Zavacki
 */
class Form extends Node
{
    const METHOD_DELETE = 'delete';
    const METHOD_GET    = 'get';
    const METHOD_POST   = 'post';
    const METHOD_PUT    = 'put';

    const ENCTYPE_URLENCODED = 'application/x-www-form-urlencoded';
    const ENCTYPE_MULTIPART  = 'multipart/form-data';

    /**
     * @var array
     */
    protected $dynamicSubforms = array();

    /**
     * @var bool
     */
    protected $isDynamic = false;

    /**
     * @var \Aero\Form\Element\ElementFactory
     */
    protected $elementFactory;

    /**
     * @var \Aero\Form\Element\ElementFactory
     */
    protected static $staticElementFactory;


    /**
     * Form cloning
     */
    public function __clone()
    {
        if ($this->attributes instanceof Attributes) {
            $this->attributes = clone $this->attributes;
        }

        $this->dynamicSubforms = array();

        if ($this->nodes instanceof Collection) {
            $this->nodes = clone $this->nodes;
            foreach ($this->nodes->all() as $node) {
                /** @var $node \Aero\Form\Node */
                $node->setParent($this);
            }
        }
    }

    /**
     * @return \Aero\Form\Node|\Aero\Form\Form
     */
    public function reset()
    {
        $this->dynamicSubforms = array();

        if ($this->nodes instanceof Collection) {
            foreach ($this->nodes->all() as $node) {
                /** @var $node \Aero\Form\Node */
                $node->reset();
            }
        }

        return $this;
    }

    /**
     * @param  array $options
     * @return \Aero\Form\Form|\Aero\Form\Node
     */
    public function setOptions(array $options)
    {
        foreach (array_change_key_case($options, CASE_LOWER) as $key => $value)
        {
            switch (str_replace(array('_', '-', '.'), '', $key))
            {
                case 'name':
                    $this->setName($value);
                    break;
            }
        }

        return $this;
    }

    /**
     * @throws \LogicException
     * @throws \InvalidArgumentException
     *
     * @param  string|\Aero\Form\Element\ElementInterface $element
     * @param  string $name
     * @param  array $options
     *
     * @return \Aero\Form\Form
     */
    public function addElement($element, $name = null, $options = null)
    {
        $nodes = $this->nodes();

        if (is_string($element)) {
            if ($name === null || $name == '') {
                throw new \InvalidArgumentException('Element name must be non-empty string');
            }
            $element = $this->createElement($element, $name, $options);
            $nodes->set($name, $element);
        }
        elseif ($element instanceof Element\ElementInterface)
        {
            /** @var $element \Aero\Form\Element\ElementInterface */
            if (!is_string($name) || $name == '') {
                $name = $element->getName();
            }
            if ($name == '') {
                throw new \LogicException('Element name must be non-empty string');
            }
            $nodes->set($name, $element);
        }
        else {
            throw new \InvalidArgumentException(
                'Element must be a string or instance of Aero\\Form\\Element\\ElementInterface'
            );
        }

        $element->setParent($this);

        return $this;
    }

    /**
     * @param  string $type
     * @param  string $name
     * @param  array  $options
     *
     * @return \Aero\Form\Element\ElementInterface
     */
    public function createElement($type, $name, $options = null)
    {
        if ($this->elementFactory instanceof Element\ElementFactory) {
            try {
                $element = $this->elementFactory->create($type, $name, $options);
                if ($element instanceof Element\ElementInterface) {
                    return $element;
                }
            }
            catch (\LogicException $e) {}
        }

        return static::getStaticElementFactory()->create($type, $name, $options);
    }

    /**
     * @throws \LogicException
     *
     * @param  string $name
     * @return \Aero\Form\Element\ElementInterface
     */
    public function getElement($name)
    {
        $element = $this->nodes()->get($name);

        if (!$element instanceof Element\ElementInterface) {
            throw new \LogicException(sprintf('Element "%s" not found', $name));
        }

        return $element;
    }

    /**
     * @throws \InvalidArgumentException
     *
     * @param  \Aero\Form\Form $subform
     * @param  string|null     $name
     *
     * @return \Aero\Form\Form
     */
    public function addSubform(Form $subform, $name = null)
    {
        $subformName = $name === null ? $subform->getName() : $name;

        if (!is_string($subformName) || $subformName == '') {
            throw new \InvalidArgumentException('Form name must be a non-empty string');
        }

        if ($name !== null) {
            $subform->setName($name);
        }
        $subform->setParent($this);

        $this->nodes()->set($subformName, $subform);

        return $this;
    }

    /**
     * @throws \LogicException
     *
     * @param  string $name
     * @return \Aero\Form\Form
     */
    public function getSubform($name)
    {
        $subform = $this->nodes()->get($name);

        if (!$subform instanceof self) {
            throw new \LogicException(sprintf('Subform "%s" not found', $name));
        }

        return $subform;
    }

    /**
     * @throws \InvalidArgumentException
     *
     * @param  string|array $names
     * @return \Aero\Form\Form
     */
    protected function addDynamicSubform($names)
    {
        if (is_string($names)) {
            $names = array($names);
        }
        elseif (!is_array($names)) {
            throw new \InvalidArgumentException('Dynamic subform name must be a string or an array');
        }

        foreach ($names as $name) {
            if (isset($this->dynamicSubforms[$name])) {
                continue;
            }
            $subform = $this->createDynamicSubform();
            $subform->setName($name);
            $this->dynamicSubforms[$name] = $subform;
        }

        return $this;
    }

    /**
     * @return \Aero\Form\Form
     */
    protected function createDynamicSubform()
    {
        $form = clone $this;

        $form->reset();
        $form->setParent($this);
        $form->setIsDynamic(false);

        return $form;
    }

    /**
     * @throws \LogicException
     *
     * @param  string $name
     * @return \Aero\Form\Form
     */
    public function getDynamicSubform($name)
    {
        if (!isset($this->dynamicSubforms[$name])) {
            throw new \LogicException(sprintf('Dynamic subform with name "%s" not found', $name));
        }

        $subform = $this->dynamicSubforms[$name];

        if (!$subform instanceof self) {
            throw new \LogicException(sprintf('Invalid type of dynamic subform "%s"', $name));
        }

        return $subform;
    }

    /**
     * @return array
     */
    public function getDynamicSubforms()
    {
        return $this->dynamicSubforms;
    }

    /**
     * @throws \LogicException
     *
     * @param  array $values
     * @return \Aero\Form\Form
     */
    public function setValues($values)
    {
        if (!is_array($values) || !$values) {
            return $this;
        }

        if ($this->isArray) {
            if ($this->name === null) {
                throw new \LogicException('Form name must be non-empty string');
            }
            if (isset($values[$this->name])) {
                $values = $values[$this->name];
            }
        }
        elseif ($this->isDynamic) {
            throw new \LogicException('Dynamic subform must be array form');
        }

        if (!$this->isDynamic)
        {
            foreach ($this->nodes()->all() as $name => $node)
            {
                if ($node instanceof Element\ElementInterface) {
                    /** @var $node \Aero\Form\Element\ElementInterface */
                    if (array_key_exists($name, $values)) {
                        $node->setValue($values[$name]);
                    }
                }
                elseif ($node instanceof self) {
                    /** @var $node \Aero\Form\Form */
                    if (isset($values[$name])) {
                        $node->setValues($values[$name]);
                    }
                }
                else {
                    throw new \LogicException(sprintf('Unknown type of node "%s"', gettype($node)));
                }
            }
        }
        else {
            $this->addDynamicSubform(array_keys($values));

            foreach ($this->dynamicSubforms as $name => $subform) {
                /** @var $subform \Aero\Form\Form */
                if (isset($values[$name])) {
                    $subform->setValues($values[$name]);
                }
            }
        }

        return $this;
    }

    /**
     * @throws \LogicException
     *
     * @return array
     */
    public function getValues()
    {
        $values = array();

        if (!$this->isDynamic)
        {
            foreach ($this->nodes()->all() as $name => $node)
            {
                if ($node instanceof Element\ElementInterface) {
                    /** @var $node \Aero\Form\Element\ElementInterface */
                    $values[$name] = $node->getValue();
                }
                elseif ($node instanceof self) {
                    /** @var $node \Aero\Form\Form */
                    $values[$name] = $node->getValues();
                }
                else {
                    throw new \LogicException(sprintf('Unknown type of node "%s"', gettype($node)));
                }
            }
        }
        else {
            foreach ($this->dynamicSubforms as $name => $subform) {
                /** @var $subform \Aero\Form\Form */
                $values[$name] = $subform->getValues();
            }
        }

        return $values;
    }

    /**
     * @throws \InvalidArgumentException
     * @throws \LogicException
     *
     * @param  array $values
     * @return bool
     */
    public function isValid($values)
    {
        if ($values === null) {
            $values = $this->getValues();
        }
        elseif (!is_array($values)) {
            throw new \InvalidArgumentException('Values must be an array');
        }

        if ($this->isArray) {
            if ($this->name === null) {
                throw new \LogicException('Form name must be non-empty string');
            }
            if (isset($values[$this->name])) {
                $values = $values[$this->name];
            }
        }
        elseif ($this->isDynamic) {
            throw new \LogicException('Dynamic subform must be array form');
        }

        $valid = true;
        $subforms = array();

        if (!$this->isDynamic)
        {
            foreach ($this->nodes()->all() as $name => $node)
            {
                if ($node instanceof Element\ElementInterface) {
                    /** @var $node \Aero\Form\Element\ElementInterface */
                    if (isset($values[$name])) {
                        $valid = $node->isValid($values[$name], $values) && $valid;
                    }
                    else {
                        $valid = $node->isValid(null, $values) && $valid;
                    }
                }
                elseif ($node instanceof self) {
                    /** @var $node \Aero\Form\Form */
                    $subforms[$name] = $node;
                }
                else {
                    throw new \LogicException(sprintf('Unknown type of node "%s"', gettype($node)));
                }
            }
        }
        else {
            $this->addDynamicSubform(array_keys($values));
            $subforms = $this->dynamicSubforms;
        }

        foreach ($subforms as $name => $subform)
        {
            /** @var $subform \Aero\Form\Form */
            if (isset($values[$name])) {
                $valid = $subform->isValid($values[$name]) && $valid;
            }
            else {
                $valid = $subform->isValid(array()) && $valid;
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
    public function renderOpen()
    {
        $attribs = $this->attributes()->all();

        $attribs['id'] = $this->getId();
        $attribs['action'] = $this->getAction();
        $attribs['enctype'] = $this->getEnctype();

        $attribs['method'] = isset($attribs['method'])
            ? strtolower($attribs['method'])
            : $this->getMethod();

        return '<form ' . Attributes::arrayToString($attribs) . '>' . PHP_EOL;
    }

    /**
     * @return string
     */
    public function renderClose()
    {
        return '</form>' . PHP_EOL;
    }

    /**
     * @return string
     */
    public function render()
    {
        $text = '';

        foreach ($this->nodes()->all() as $node) {
            /** @var $node \Aero\Form\Node */
            $text .= $node->render() . "\n";
        }

        return $text;
    }

    /**
     * @return string
     */
    public function renderAll()
    {
        return $this->renderOpen() . $this->render() . $this->renderClose();
    }

    /**
     * @param  string $action
     * @return \Aero\Form\Form
     */
    public function setAction($action)
    {
        $this->attributes()->set('action', (string) $action);
        return $this;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        $action = $this->attributes()->get('action');

        if ($action === null) {
            $action = '';
            $this->setAction($action);
        }

        return $action;
    }

    /**
     * @param  string $method
     * @return \Aero\Form\Form
     */
    public function setMethod($method)
    {
        $this->attributes()->set('method', strtolower($method));
        return $this;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        $method = $this->attributes()->get('method');

        if ($method === null) {
            $method = self::METHOD_POST;
            $this->setMethod('method', $method);
        }

        return $method;
    }

    /**
     * @param  string $enctype
     * @return \Aero\Form\Form
     */
    public function setEnctype($enctype)
    {
        $this->attributes()->set('enctype', $enctype);
        return $this;
    }

    /**
     * @return string
     */
    public function getEnctype()
    {
        $enctype = $this->attributes()->get('enctype');

        if ($enctype === null) {
            $enctype = self::ENCTYPE_URLENCODED;
            $this->setEnctype('enctype', $enctype);
        }

        return $enctype;
    }

    /**
     * @param  bool $flag
     * @return \Aero\Form\Form
     */
    public function setIsDynamic($flag = true)
    {
        $this->isDynamic = (bool) $flag;
        return $this;
    }

    /**
     * @return bool
     */
    public function isDynamic()
    {
        return $this->isDynamic;
    }

    /**
     * @param  \Aero\Form\Element\ElementFactory $elementFactory
     * @return \Aero\Form\Form
     */
    public function setElementFactory(Element\ElementFactory $elementFactory = null)
    {
        $this->elementFactory = $elementFactory;
        return $this;
    }

    /**
     * @return \Aero\Form\Element\ElementFactory
     */
    public function getElementFactory()
    {
        if (!$this->elementFactory instanceof Element\ElementFactory) {
            $this->elementFactory = $this->createDefaultElementFactory();
        }
        return $this->elementFactory;
    }

    /**
     * @return \Aero\Form\Element\ElementFactory
     */
    protected function createDefaultElementFactory()
    {
        return new Element\ElementFactory();
    }

    /**
     * @param \Aero\Form\Element\ElementFactory|null $elementFactory
     */
    public static function setStaticElementFactory(Element\ElementFactory $elementFactory = null)
    {
        self::$staticElementFactory = $elementFactory;
    }

    /**
     * @return \Aero\Form\Element\ElementFactory
     */
    public static function getStaticElementFactory()
    {
        if (!self::$staticElementFactory instanceof Element\ElementFactory) {
            self::$staticElementFactory = static::createDefaultStaticElementFactory();
        }
        return self::$staticElementFactory;
    }

    /**
     * @return \Aero\Form\Element\ElementFactory
     */
    protected static function createDefaultStaticElementFactory()
    {
        return new Element\ElementFactory();
    }
}
