<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\View\Helper;

/**
 *
 *
 * @category    Aero
 * @package     Aero_View
 * @subpackage  Aero_View_Helper
 * @author      Alex Zavacki
 */
class SlotsHelper extends AbstractHelper
{
    protected $slots = array();
    protected $openSlots = array();

    /**
     * Starts a new slot.
     *
     * This method starts an output buffer that will be
     * closed when the stop() method is called.
     *
     * @param string $name  The slot name
     *
     * @throws \InvalidArgumentException if a slot with the same name is already started
     */
    public function start($name)
    {
        if (in_array($name, $this->openSlots)) {
            throw new \InvalidArgumentException(sprintf('A slot named "%s" is already started.', $name));
        }

        $this->openSlots[] = $name;
        $this->slots[$name] = '';

        ob_start();
        ob_implicit_flush(0);
    }

    /**
     * Stops a slot.
     *
     * @throws \LogicException if no slot has been started
     */
    public function stop()
    {
        if (!$this->openSlots) {
            throw new \LogicException('No slot started.');
        }

        $name = array_pop($this->openSlots);

        $this->slots[$name] = ob_get_clean();
    }

    /**
     * Returns true if the slot exists.
     *
     * @param string $name The slot name
     */
    public function has($name)
    {
        return isset($this->slots[$name]);
    }

    /**
     * Gets the slot value.
     *
     * @param string $name    The slot name
     * @param string $default The default slot content
     *
     * @return string The slot content
     */
    public function get($name, $default = false)
    {
        return isset($this->slots[$name]) ? $this->slots[$name] : $default;
    }

    /**
     * Sets a slot value.
     *
     * @param string $name    The slot name
     * @param string $content The slot content
     */
    public function set($name, $content)
    {
        $this->slots[$name] = $content;
    }

    /**
     * Output a slot.
     *
     * @param string $name
     * @param string $default
     *
     * @return bool
     */
    public function output($name, $default = false)
    {
        if (!isset($this->slots[$name])) {
            if (false !== $default) {
                echo $default;

                return true;
            }

            return false;
        }

        echo $this->slots[$name];

        return true;
    }
}
