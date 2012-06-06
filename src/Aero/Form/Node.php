<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Form;

/**
 * Abstract form node
 *
 * @category    Aero
 * @package     Aero_Form
 * @author      Alex Zavacki
 */
abstract class Node implements NodeInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var \Aero\Form\Node
     */
    protected $parent;

    /**
     * @var \Aero\Form\Collection
     */
    protected $nodes;

    /**
     * @var \Aero\Form\Attributes
     */
    protected $attributes;

    /**
     * @var array
     */
    protected $customErrors = array();

    /**
     * @var bool
     */
    protected $isArray = false;


    /**
     * Constructor.
     *
     * @param string $name
     * @param array  $options
     */
    public function __construct($name = null, $options = array())
    {
        if (is_string($name)) {
            $this->setName($name);
        }
        elseif (is_array($name)) {
            $options = $name;
            $name = null;
        }

        if (is_array($options)) {
            $this->setOptions($options);
        }

        $this->preInit();
        $this->init();
    }

    /**
     * @return void
     */
    protected function preInit()
    {
        // do nothing in abstract node
    }

    /**
     * @return void
     */
    public function init()
    {
        // do nothing in abstract node
    }

    /**
     * @param  array $options
     * @return \Aero\Form\Node
     */
    public function setOptions(array $options)
    {
        // do nothing in abstract node
        return $this;
    }

    /**
     * @return \Aero\Form\Node
     */
    public function reset()
    {
        // do nothing in abstract node
        return $this;
    }

    /**
     * @throws \InvalidArgumentException
     *
     * @param  string $name
     * @return \Aero\Form\Node
     */
    public function setName($name)
    {
        if ($name !== null)
        {
            $name = Attributes::filterName($name);

            if ($name == '') {
                throw new \InvalidArgumentException('Node name must be non-empty string');
            }
        }

        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @throws \InvalidArgumentException
     *
     * @param  \Aero\Form\Node $parent
     * @return \Aero\Form\Node
     */
    public function setParent($parent)
    {
        if ($this === $parent) {
            $parent = null;
        }

        if ($parent !== null && !$parent instanceof self) {
            throw new \InvalidArgumentException('Parent must be instance of Aero\\Form\\Node');
        }

        $this->parent = $parent;
        return $this;
    }

    /**
     * @return \Aero\Form\Node
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @throws \LogicException
     *
     * @return array
     */
    public function getParents()
    {
        $parents = array();
        $prevParent = null;

        $parent = $prevParent = $this;
        while ($parent = $parent->getParent())
        {
            /** @var $parent \Aero\Form\Node */
            if (!$parent instanceof self) {
                break;
            }

            // strict search is important
            if ($parent === $prevParent || in_array($parent, $parents, true)) {
                throw new \LogicException('Recursive parents chain detected');
            }

            $parents[] = $parent;
            $prevParent = $parent;
        }

        return array_reverse($parents);
    }

    /**
     * @return array
     */
    public function getParentNames()
    {
        $names = array();

        foreach ($this->getParents() as $parent) {
            /** @var $parent \Aero\Form\Node */
            $names[] = $parent->getName();
        }

        return $names;
    }

    /**
     * @return string
     */
    public function getParentFullName()
    {
        $names = array();

        foreach (array_reverse($this->getParents()) as $parent) {
            /** @var $parent \Aero\Form\Node */
            if (!$parent->isArray()) {
                break;
            }
            $names[] = $parent->getName();
        }

        $names = array_reverse($names);
        $namesCount = count($names);

        if ($namesCount > 1) {
            return array_shift($names) . '[' . implode('][', $names) . ']';
        }
        elseif ($namesCount == 1) {
            return current($names);
        }

        return '';
    }

    /**
     * @return string
     */
    public function getFullName()
    {
        $name = $this->name;

        if (($parentName = $this->getParentFullName()) != '') {
            $name = $parentName . '[' . $name . ']';
        }

        return $name;
    }

    /**
     * @return string|null
     */
    public function render()
    {
        return null;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }

    /**
     * @param  array|null $errors
     * @param  string $listClass
     *
     * @return string
     */
    public function renderErrors($errors = null, $listClass = 'validation-errors')
    {
        if ($errors === null) {
            $errors = $this->getErrorMessages();
        }

        if (!is_array($errors) || !$errors) {
            return '';
        }

        return '<ul class="' . (string) $listClass . '"><li>'
            . implode('</li><li>', $errors)
            . '</li></ul>';
    }

    /**
     * @param  string $listClass
     * @return string
     */
    public function renderCustomErrors($listClass = 'validation-errors')
    {
        return $this->renderErrors($this->customErrors, $listClass);
    }

    /**
     * @return \Aero\Form\Collection
     */
    public function nodes()
    {
        if (!$this->nodes instanceof Collection) {
            $this->nodes = new Collection();
        }
        return $this->nodes;
    }

    /**
     * @return \Aero\Form\Attributes
     */
    public function attributes()
    {
        if (!$this->attributes instanceof Attributes) {
            $this->attributes = new Attributes();
        }
        return $this->attributes;
    }

    /**
     * @return string
     */
    public function getId()
    {
        if (($id = $this->attributes()->get('id')) !== null) {
            return $id;
        }

        $id = $this->getFullName();

        if (!strstr($id, '[')) {
            return $id;
        }

        return Attributes::formatId($id);
    }

    /**
     * @return array
     */
    public function getErrorMessages()
    {
        return $this->customErrors;
    }

    /**
     * @throws \InvalidArgumentException
     *
     * @param  string|array $messages
     * @return \Aero\Form\Node
     */
    public function addCustomError($messages)
    {
        if (is_string($messages)) {
            $messages = array($messages);
        }
        elseif (!is_array($messages)) {
            throw new \InvalidArgumentException('Message must be a string or an array of strings');
        }

        $this->customErrors = array_merge($this->customErrors, $messages);
        return $this;
    }

    /**
     * @return array
     */
    public function getCustomErrors()
    {
        return $this->customErrors;
    }

    /**
     * @return bool
     */
    public function hasCustomErrors()
    {
        return $this->customErrors ? true : false;
    }

    /**
     * @return \Aero\Form\Node
     */
    public function clearCustomErrors()
    {
        $this->customErrors = array();
        return $this;
    }

    /**
     * @param  bool $flag
     * @return \Aero\Form\Form
     */
    public function setIsArray($flag = true)
    {
        $this->isArray = (bool) $flag;
        return $this;
    }

    /**
     * @return bool
     */
    public function isArray()
    {
        return $this->isArray;
    }
}
