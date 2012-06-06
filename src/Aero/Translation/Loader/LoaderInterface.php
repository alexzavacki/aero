<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Translation\Loader;

use Aero\Translation\Translator;

/**
 *
 *
 * @category    Aero
 * @package     Aero_Translation
 * @subpackage  Aero_Translation_Loader
 * @author      Alex Zavacki
 */
interface LoaderInterface
{
    /**
     * Load domain translations and return as array
     *
     * @param  \Aero\Translation\Translator $translator
     * @param  string $locale
     * @param  string $domain
     *
     * @return array
     */
    public function load(Translator $translator, $locale, $domain);
}
