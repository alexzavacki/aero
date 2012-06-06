<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Translation\Format\Reader;

/**
 *
 *
 * @category    Aero
 * @package     Aero_Translation
 * @subpackage  Aero_Translation_Format
 * @subpackage  Aero_Translation_Format_Reader
 * @author      Alex Zavacki
 */
interface ReaderInterface
{
    /**
     * Read resource, parse and return as array of translations
     *
     * @throws \InvalidArgumentException
     *
     * @param  mixed $resource
     * @param  array $options
     * @return array
     */
    public function read($resource, $options = array());
}
