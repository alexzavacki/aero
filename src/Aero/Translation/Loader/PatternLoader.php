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
class PatternLoader implements LoaderInterface
{
    /**
     * @var string
     */
    protected $pattern;

    /**
     * @var array
     */
    protected $dirs = array();

    /**
     * @var string
     */
    protected $format;


    /**
     * Constructor.
     *
     * @param array $options
     */
    public function __construct($options = array())
    {
        if ($options) {
            $this->setOptions($options);
        }
    }

    /**
     * Load domain translations and return as array
     *
     * @throws \LogicException
     *
     * @param  \Aero\Translation\Translator $translator
     * @param  string $locale
     * @param  string $domain
     *
     * @return array
     */
    public function load(Translator $translator, $locale, $domain)
    {
        if (!$this->format) {
            throw new \LogicException('Format for pattern loader not set');
        }

        if (!$translator->supportsFormat($this->format)) {
            throw new \LogicException(sprintf('Format "%s" is not supported', $this->format));
        }

        $messages = array();
        $formatReader = $translator->getFormatReader($this->format);

        $translationSubPath = ltrim(str_replace(
            array('{locale}', '{domain}', '{format}'),
            array($locale, $domain, $this->format),
            $this->pattern
        ), '\\/');

        foreach ($this->dirs as $dir) {
            $filename = rtrim($dir, '\\/') . DIRECTORY_SEPARATOR . $translationSubPath;
            if (is_file($filename)) {
                $messages = array_replace_recursive($messages, $formatReader->read($filename));
            }
        }

        return $messages;
    }

    /**
     * @param  array $options
     * @return \Aero\Translation\Loader\PatternLoader
     */
    public function setOptions(array $options)
    {
        foreach (array_change_key_case($options) as $key => $value)
        {
            switch (str_replace(array('_', '-', '.'), '', $key))
            {
                case 'pattern':
                    $this->setPattern($value);
                    break;

                case 'dir':
                case 'dirs':
                    $this->setDirs($value);
                    break;

                case 'format':
                    $this->setFormat($value);
                    break;
            }
        }
        return $this;
    }

    /**
     * @param  string $pattern
     * @return \Aero\Translation\Loader\PatternLoader
     */
    public function setPattern($pattern)
    {
        $this->pattern = (string) $pattern;
        return $this;
    }

    /**
     * @return string
     */
    public function getPattern()
    {
        return $this->pattern;
    }

    /**
     * @param  array $dirs
     * @return \Aero\Translation\Loader\PatternLoader
     */
    public function setDirs($dirs)
    {
        $this->dirs = array();
        $this->addDir($dirs);
        return $this;
    }

    /**
     * @throws \InvalidArgumentException
     *
     * @param  string|array $dirs
     * @return \Aero\Translation\Loader\PatternLoader
     */
    public function addDir($dirs)
    {
        if (is_string($dirs)) {
            $dirs = array($dirs);
        }
        elseif (!is_array($dirs)) {
            throw new \InvalidArgumentException('Dir must be a string or an array');
        }

        $this->dirs = array_merge($this->dirs, $dirs);
        return $this;
    }

    /**
     * @return array
     */
    public function getDirs()
    {
        return $this->dirs;
    }

    /**
     * @param  string $format
     * @return \Aero\Translation\Loader\PatternLoader
     */
    public function setFormat($format)
    {
        $this->format = (string) $format;
        return $this;
    }

    /**
     * @return array
     */
    public function getFormat()
    {
        return $this->format;
    }
}
