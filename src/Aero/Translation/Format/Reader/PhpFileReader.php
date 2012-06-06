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
class PhpFileReader implements ReaderInterface
{
    /**
     * Read resource, parse and return as array of translations
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     *
     * @param  mixed $resource
     * @param  array $options
     * @return array
     */
    public function read($resource, $options = array())
    {
        if (!is_array($resource)) {
            $resource = array($resource);
        }

        $messages = array();

        foreach ($resource as $file)
        {
            if (!is_file($file)) {
                throw new \InvalidArgumentException(
                    sprintf('File "%s" with translations does not exist', $file)
                );
            }

            ob_start();
            $fileMessages = require $file;
            ob_end_clean();

            if (!is_array($fileMessages)) {
                throw new \RuntimeException(
                    sprintf('File "%s" exists, but does not return array', $file)
                );
            }

            $messages = array_replace_recursive($messages, $fileMessages);
        }

        return $messages;
    }
}
