<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero;

/**
 * Framework version holder
 *
 * @category    Aero
 * @package     Aero_Version
 * @author      Alex Zavacki
 */
final class Version
{
    const VERSION = '0.0.1-dev';

    /**
     * Compare the current version of Aero framework with the specified $version
     *
     * Method returns:
     *  -1 if $version is older
     *   0 if versions are equal
     *  +1 if $version is newer
     *
     * @param  string $version
     * @return int
     */
    public function compare($version)
    {
        return version_compare($version, self::VERSION);
    }
}
