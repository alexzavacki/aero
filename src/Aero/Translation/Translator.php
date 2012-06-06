<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Translation;

/**
 *
 *
 * @category    Aero
 * @package     Aero_Translation
 * @author      Alex Zavacki
 */
class Translator
{
    /**
     * @var array
     */
    protected $messages = array();

    /**
     * @var array
     */
    protected $resources = array();

    /**
     * @var array
     */
    protected $loaders = array();

    /**
     * @var array
     */
    protected $loadersMap = array();

    /**
     * @var array
     */
    protected $newLoaders = array();

    /**
     * @var array
     */
    protected $formatReaders = array();

    /**
     * @var string
     */
    protected $defaultLocale;

    /**
     * @var string
     */
    protected $defaultDomain = 'messages';

    /**
     * @var array
     */
    protected $fallbackLocales = array();

    /**
     * @var bool
     */
    protected $useLangLocaleAsDefaultFallback = true;

    /**
     * @var array
     */
    protected $cachedFallbackLocales = array();

    /**
     * @var bool
     */
    protected $useIdAsFallbackTranslation = false;


    /**
     * Constructor.
     *
     * @throws \InvalidArgumentException
     *
     * @param array|string $options
     */
    public function __construct($options = array())
    {
        if (is_string($options)) {
            $options = array('default_locale' => $options);
        }
        elseif (!is_array($options)) {
            throw new \InvalidArgumentException('Translator options must be an array or a string');
        }

        $this->formatReaders = array_merge(
            (array) $this->getDefaultFormatReaders(),
            (array) $this->formatReaders,
            isset($options['readers']) ? (array) $options['readers'] : array()
        );

        $this->loaders = array();

        $this->loadersMap = array_merge(
            (array) $this->getDefaultLoadersMap(),
            (array) $this->loadersMap
        );

        $this->setOptions($options);
    }

    /**
     * @param  array $options
     * @return \Aero\Translation\Translator
     */
    public function setOptions(array $options)
    {
        foreach (array_change_key_case($options, CASE_LOWER) as $key => $value)
        {
            switch (str_replace(array('_', '-', '.'), '', $key))
            {
                case 'locale':
                case 'defaultlocale':
                    $this->setDefaultLocale($value);
                    break;

                case 'domain':
                case 'defaultdomain':
                    $this->setDefaultDomain($value);
                    break;

                case 'defaultfallbacklocale':
                    $this->setDefaultFallbackLocale($value);
                    break;
            }
        }

        return $this;
    }

    /**
     * @throws \InvalidArgumentException
     *
     * @param  string $format
     * @param  \Aero\Translation\Format\Reader\ReaderInterface|string $reader
     * @param  array $options
     *
     * @return \Aero\Translation\Translator
     */
    public function setFormatReader($format, $reader, $options = array())
    {
        if (is_string($reader)) {
            $reader = array($reader, $options);
        }
        elseif (!$reader instanceof Format\Reader\ReaderInterface) {
            throw new \InvalidArgumentException(
                'Format reader must be instance of Aero\\Translation\\Format\\Reader\\ReaderInterface or a string'
            );
        }

        $this->formatReaders[$format] = $reader;
        return $this;
    }

    /**
     * @throws \LogicException
     *
     * @param  string $format
     * @return \Aero\Translation\Format\Reader\ReaderInterface
     */
    public function getFormatReader($format)
    {
        if (!isset($this->formatReaders[$format])) {
            throw new \LogicException(sprintf('Reader for format "%s" not found', $format));
        }

        if ($this->formatReaders[$format] instanceof Format\Reader\ReaderInterface) {
            return $this->formatReaders[$format];
        }

        if (is_string($this->formatReaders[$format])) {
            $reader = $this->formatReaders[$format];
            $options = array();
        }
        elseif (is_array($this->formatReaders[$format])) {
            list($reader, $options) = $this->formatReaders[$format];
        }
        else {
            throw new \LogicException('Format reader must be a string or an array');
        }

        return $this->formatReaders[$format] = $this->createFormatReaderFromString($reader, $options);
    }

    /**
     * @return array
     */
    public function getFormatReaders()
    {
        return $this->formatReaders;
    }

    /**
     * @param  string $format
     * @return bool
     */
    public function supportsFormat($format)
    {
        return isset($this->formatReaders[$format]);
    }

    /**
     * @throws \InvalidArgumentException
     *
     * @param  string $reader
     * @param  array $options
     *
     * @return \Aero\Translation\Format\Reader\ReaderInterface
     */
    public function createFormatReaderFromString($reader, $options = array())
    {
        if (!class_exists($reader)) {
            throw new \InvalidArgumentException(
                sprintf('Cant create format reader for "%s"', $reader)
            );
        }

        $reader = new $reader($options);

        if (!$reader instanceof Format\Reader\ReaderInterface) {
            throw new \InvalidArgumentException(
                'Format reader must be instance of Aero\\Translation\\Format\\Reader\\ReaderInterface or a string'
            );
        }

        return $reader;
    }

    /**
     * @return array
     */
    public function getDefaultFormatReaders()
    {
        return array(
            'array' => __NAMESPACE__ . '\\Format\\Reader\\ArrayReader',
            'php'   => __NAMESPACE__ . '\\Format\\Reader\\PhpFileReader',
        );
    }

    /**
     * @throws \InvalidArgumentException
     *
     * @param  string $format
     * @param  mixed  $resource
     * @param  string $locale
     * @param  string $domain
     * @param  mixed  $options
     *
     * @return \Aero\Translation\Translator
     */
    public function addResource($format, $resource, $locale = null, $domain = null, $options = null)
    {
        if (!is_string($domain)) {
            $domain = (string) $this->defaultDomain;
        }
        $domain = strtolower($domain);

        if (!is_string($locale)) {
            $locale = (string) $this->defaultLocale;
        }

        if ($domain == '' || $locale == '') {
            throw new \InvalidArgumentException('Domain and locale must be non-empty string');
        }

        $this->resources[$locale][$domain][] = array($format, $resource, $options);
        return $this;
    }

    /**
     * @throws \InvalidArgumentException
     *
     * @param  \Aero\Translation\Loader\LoaderInterface|string $loader
     * @param  array $options
     *
     * @return \Aero\Translation\Translator
     */
    public function addLoader($loader, $options = array())
    {
        if (is_string($loader)) {
            $loader = $this->createLoaderFromString($loader, $options);
        }

        if (!$loader instanceof Loader\LoaderInterface) {
            throw new \InvalidArgumentException(
                'Loader must be instance of Aero\\Translation\\Loader\\LoaderInterface or a string'
            );
        }

        foreach ($this->newLoaders as $locale => $domains) {
            $this->newLoaders[$locale] = array_merge_recursive(
                $domains,
                array_fill_keys(array_keys($domains), array($loader))
            );
        }

        $this->loaders[] = $loader;
        return $this;
    }

    /**
     * @throws \InvalidArgumentException
     *
     * @param  string $loader
     * @param  array $options
     *
     * @return \Aero\Translation\Loader\LoaderInterface
     */
    public function createLoaderFromString($loader, $options = array())
    {
        if (isset($this->loadersMap[$loader])) {
            $loader = $this->loadersMap[$loader];
        }

        if (is_array($loader)) {
            list($loader, $options) = $loader;
        }
        elseif ($loader instanceof Loader\LoaderInterface) {
            return $loader;
        }
        elseif (!is_string($loader)) {
            throw new \InvalidArgumentException('Loader must be a string or an array');
        }

        if (!class_exists($loader)) {
            throw new \InvalidArgumentException(sprintf('Reader class "%s" not found', $loader));
        }

        $loader = new $loader($options);

        if (!$loader instanceof Loader\LoaderInterface) {
            throw new \InvalidArgumentException(
                'Loader must be instance of Aero\\Translation\\Loader\\LoaderInterface or a string'
            );
        }

        return $loader;
    }

    /**
     * @return \Aero\Translation\Translator
     */
    public function clearLoaders()
    {
        $this->loaders = array();
        return $this;
    }

    /**
     * @return array
     */
    public function getDefaultLoadersMap()
    {
        return array(
            'pattern' => __NAMESPACE__ . '\\Loader\\PatternLoader',
        );
    }

    /**
     * @throws \InvalidArgumentException
     *
     * @param  string $domain
     * @param  string $id
     * @param  array  $vars
     * @param  string $locale
     * @param  bool   $useFallback
     *
     * @return string
     */
    public function domainTranslate($domain, $id, $vars = array(), $locale = null, $useFallback = true)
    {
        if ($domain === null) {
            $domain = (string) $this->defaultDomain;
        }
        $domain = strtolower($domain);

        if ($locale === null || !is_string($locale)) {
            $locale = (string) $this->defaultLocale;
        }

        if ($domain == '' || $locale == '') {
            throw new \InvalidArgumentException('Domain and locale must be non-empty string');
        }

        $newResources = false;
        if (isset($this->resources[$locale][$domain]) && $this->resources[$locale][$domain]) {
            $newResources = true;
        }
        elseif (!isset($this->newLoaders[$locale][$domain]) || $this->newLoaders[$locale][$domain]) {
            $newResources = true;
        }

        if ($newResources) {
            $this->doLoadTranslations($locale, $domain);
        }

        if (isset($this->messages[$locale][$domain][$id])) {
            $message = $this->messages[$locale][$domain][$id];
        }
        else {
            if (!$useFallback) {
                return null;
            }

            $fallbackLocales = $this->getFallbackLocaleChain($locale);

            foreach ($fallbackLocales as $locale) {
                $fallbackMessage = $this->domainTranslate($domain, $id, $vars, $locale, false);
                if ($fallbackMessage !== null) {
                    return $fallbackMessage;
                }
            }

            $message = $this->useIdAsFallbackTranslation ? $id : '';
        }

        if (preg_match_all('#{{plural\:\s*([^\|]+)\|(.*?)}}#', $message, $matches))
        {
            foreach ($matches[0] as $key => $match)
            {
                $pluralVar = trim($matches[1][$key]);

                if (!isset($vars[$pluralVar])) {
                    continue;
                }

                $pluralVarValue = (int) $vars[$pluralVar];
                $choices = explode('|', $matches[2][$key]);

                $pluralIndex = Plural::get($pluralVarValue, $locale);
                $replace = isset($choices[$pluralIndex]) ? $choices[$pluralIndex] : '';

                $message = str_replace($match, $replace, $message);
            }
        }

        return strtr($message, $vars);
    }

    /**
     * @throws \LogicException
     *
     * @param  string $locale
     * @param  string $domain
     * @return void
     */
    protected function doLoadTranslations($locale, $domain)
    {
        if (!isset($this->messages[$locale][$domain])
            || !is_array($this->messages[$locale][$domain])
        ) {
            $this->messages[$locale][$domain] = array();
        }

        $newMessages = array();

        // Translation autoloaders
        if (!isset($this->newLoaders[$locale][$domain])) {
            $this->newLoaders[$locale][$domain] = $this->loaders;
        }

        if (is_array($this->newLoaders[$locale][$domain]))
        {
            foreach ($this->newLoaders[$locale][$domain] as $key => $loader)
            {
                unset($this->newLoaders[$locale][$domain][$key]);

                if (!$loader instanceof Loader\LoaderInterface) {
                    throw new \LogicException(
                        'Loader must be instance of Aero\\Translation\\Loader\\LoaderInterface'
                    );
                }
                /** @var $loader \Aero\Translation\Loader\LoaderInterface */
                $this->messages[$locale][$domain] = array_merge(
                    (array) $this->messages[$locale][$domain],
                    (array) $loader->load($this, $locale, $domain)
                );
            }
        }

        // Custom resources
        if (isset($this->resources[$locale][$domain]))
        {
            foreach ($this->resources[$locale][$domain] as $key => $resource)
            {
                unset($this->resources[$locale][$domain][$key]);

                list($format, $resource, $options) = $resource;
                $newMessages = array_replace_recursive(
                    $newMessages,
                    (array) $this->getFormatReader($format)->read($resource, $options)
                );
            }
        }

        $this->messages[$locale][$domain] = array_replace_recursive(
            $this->messages[$locale][$domain],
            $newMessages
        );
    }

    /**
     * Alias of domainTranslate()
     *
     * @param  string $domain
     * @param  string $id
     * @param  array  $vars
     * @param  string $locale
     * @param  bool   $useFallback
     *
     * @return string
     */
    public function _d($domain, $id, $vars = array(), $locale = null, $useFallback = true)
    {
        return $this->domainTranslate($domain, $id, $vars, $locale, $useFallback);
    }

    /**
     *
     *
     * @param  string $id
     * @param  array  $vars
     * @param  string $locale
     * @param  string $domain
     * @param  bool   $useFallback
     *
     * @return string
     */
    public function translate($id, $vars = array(), $locale = null, $domain = null, $useFallback = true)
    {
        return $this->domainTranslate($domain, $id, $vars, $locale, $useFallback);
    }

    /**
     *
     *
     * @param  string $id
     * @param  array  $vars
     * @param  string $locale
     * @param  string $domain
     * @param  bool   $useFallback
     *
     * @return string
     */
    public function _($id, $vars = array(), $locale = null, $domain = null, $useFallback = true)
    {
        return $this->domainTranslate($domain, $id, $vars, $locale, $useFallback);
    }

    //
    public function translateChoice() {}
    public function domainTranslateChoice() {}

    /**
     * @param  string $defaultLocale
     * @return \Aero\Translation\Translator
     */
    public function setDefaultLocale($defaultLocale)
    {
        $this->defaultLocale = (string) $defaultLocale;
        return $this;
    }

    /**
     * @return string
     */
    public function getDefaultLocale()
    {
        return $this->defaultLocale;
    }

    /**
     * @param  string $defaultDomain
     * @return \Aero\Translation\Translator
     */
    public function setDefaultDomain($defaultDomain)
    {
        $this->defaultDomain = (string) $defaultDomain;
        return $this;
    }

    /**
     * @return string
     */
    public function getDefaultDomain()
    {
        return $this->defaultDomain;
    }

    /**
     * @throws \InvalidArgumentException
     *
     * @param  string $source
     * @param  string $target
     *
     * @return \Aero\Translation\Translator
     */
    public function addFallbackLocale($source, $target = null)
    {
        if (is_string($source)) {
            $source = array($source => $target);
        }
        elseif (!is_array($source)) {
            throw new \InvalidArgumentException('Source param must be a string or an array');
        }

        foreach ($source as $src => $target)
        {
            if (is_string($target)) {
                $target = array($target);
            }
            elseif (!is_array($target)) {
                throw new \InvalidArgumentException('Target locale must be a string or an array');
            }

            if (!isset($this->fallbackLocales[$src])) {
                $this->fallbackLocales[$src] = array();
            }

            $this->fallbackLocales[$src] = array_merge(
                (array) $this->fallbackLocales[$src],
                (array) $target
            );
        }

        return $this;
    }

    /**
     * @param  string|array $target
     * @return \Aero\Translation\Translator
     */
    public function setDefaultFallbackLocale($target)
    {
        $this->clearDefaultFallbackLocale();
        $this->addDefaultFallbackLocale($target);

        return $this;
    }

    /**
     * @param  string|array $target
     * @return \Aero\Translation\Translator
     */
    public function addDefaultFallbackLocale($target)
    {
        $this->addFallbackLocale('*', $target);
        return $this;
    }

    /**
     * @param  string $locale
     * @return \Aero\Translation\Translator
     */
    public function clearFallbackLocale($locale = null)
    {
        if ($locale === null) {
            $this->fallbackLocales = array();
            $this->cachedFallbackLocales = array();
        }
        else {
            if (isset($this->fallbackLocales[$locale])) {
                unset($this->fallbackLocales[$locale]);
            }
            if (isset($this->cachedFallbackLocales[$locale])) {
                unset($this->cachedFallbackLocales[$locale]);
            }
        }

        return $this;
    }

    /**
     * @return \Aero\Translation\Translator
     */
    public function clearDefaultFallbackLocale()
    {
        $this->clearFallbackLocale('*');
        return $this;
    }

    /**
     * @return array
     */
    public function getFallbackLocales()
    {
        return $this->fallbackLocales;
    }

    /**
     * @param  string $locale
     * @return array
     */
    public function getFallbackLocaleChain($locale = null)
    {
        if ($locale === null) {
            $locale = $this->defaultLocale;
        }

        $localeLower = strtolower($locale);

        if (isset($this->cachedFallbackLocales[$localeLower])) {
            return $this->cachedFallbackLocales[$localeLower];
        }

        $chain = array();

        if (isset($this->fallbackLocales[$locale])) {
            $chain = array_merge($chain, (array) $this->fallbackLocales[$locale]);
        }

        // lang locale
        if ($this->useLangLocaleAsDefaultFallback && strrchr($locale, '_') !== false) {
            $langLocale = substr($locale, 0, -strlen(strrchr($locale, '_')));
            if (!in_array($langLocale, $chain)) {
                array_push($chain, $langLocale);
                if (isset($this->fallbackLocales[$langLocale])) {
                    $chain = array_merge($chain, (array) $this->fallbackLocales[$langLocale]);
                }
            }
        }

        // default
        if (isset($this->fallbackLocales['*'])) {
            $chain = array_merge($chain, (array) $this->fallbackLocales['*']);
        }

        foreach ($chain as $key => $item) {
            if (strtolower($item) == $localeLower) {
                unset($chain[$key]);
            }
        }

        return $this->cachedFallbackLocales[$localeLower] = array_unique($chain);
    }

    /**
     * @param  bool|null $value
     * @return \Aero\Translation\Translator|bool
     */
    public function useLangLocaleAsDefaultFallback($value = null)
    {
        if (is_bool($value)) {
            $this->useLangLocaleAsDefaultFallback = $value;
            return $this;
        }
        return $this->useLangLocaleAsDefaultFallback;
    }

    /**
     * @param  bool|null $value
     * @return \Aero\Translation\Translator|bool
     */
    public function useIdAsFallbackTranslation($value = null)
    {
        if (is_bool($value)) {
            $this->useIdAsFallbackTranslation = $value;
            return $this;
        }
        return $this->useIdAsFallbackTranslation;
    }
}
