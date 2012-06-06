<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Form;

/**
 * Node interface
 *
 * @category    Aero
 * @package     Aero_Form
 * @author      Alex Zavacki
 */
interface NodeInterface
{
    /**
     * @return \Aero\Form\NodeInterface
     */
    public function reset();

    /**
     * @param  string $name
     * @return \Aero\Form\NodeInterface
     */
    public function setName($name);

    /**
     * @return string
     */
    public function getName();

    /**
     * @param  \Aero\Form\NodeInterface $parent
     * @return \Aero\Form\NodeInterface
     */
    public function setParent($parent);

    /**
     * @return \Aero\Form\NodeInterface
     */
    public function getParent();

    /**
     * @return string
     */
    public function render();
}
