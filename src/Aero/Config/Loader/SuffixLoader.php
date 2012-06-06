<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Config\Loader;

/**
 * Suffix loader
 *
 * @category    Aero
 * @package     Aero_Config
 * @subpackage  Aero_Config_Loader
 * @author      Alex Zavacki
 */
class SuffixLoader extends FileLoader
{
    /**
     * @var string Config file suffix
     */
    protected $suffix;


    /**
     * Constructor.
     *
     * @param string $suffix
     */
    public function __construct($suffix)
    {
        $this->suffix = (string) $suffix;
    }

    /**
     * @param  string $resource
     * @return string
     */
    public function getFormattedResource($resource)
    {
        $pathinfo = pathinfo($resource);

        if (!isset($pathinfo['dirname']) || !isset($pathinfo['filename'])) {
            return false;
        }

        $extension = isset($pathinfo['extension']) ? ".{$pathinfo['extension']}" : '';

        return $pathinfo['dirname'] . DIRECTORY_SEPARATOR
            . $pathinfo['filename'] . '.' . $this->suffix . $extension;
    }

    /**
     * @param  string $suffix
     * @return \Aero\Config\Loader\SuffixLoader
     */
    public function setSuffix($suffix)
    {
        $this->suffix = (string) $suffix;
        return $this;
    }

    /**
     * @return string
     */
    public function getSuffix()
    {
        return $this->suffix;
    }
}
