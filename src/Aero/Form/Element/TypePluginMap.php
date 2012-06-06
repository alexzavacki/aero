<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Form\Element;

use Aero\Std\Plugin\PluginMap as BasePluginMap;

/**
 *
 *
 * @package     Aero_Form
 * @subpackage  Aero_Form_Element
 * @author      Alex Zavacki
 */
class TypePluginMap extends BasePluginMap
{
    /**
     * @var array
     */
    protected static $staticMap = array();

    /**
     * Get array of default plugins
     *
     * @return array
     */
    public function getDefaultMap()
    {
        return array(
            'text' => __NAMESPACE__ . '\\Type\\TextElement',
            'password' => __NAMESPACE__ . '\\Type\\PasswordElement',
        );
    }
}
