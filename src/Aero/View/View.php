<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\View;

if (!defined('ENT_SUBSTITUTE')) {
    define('ENT_SUBSTITUTE', 8);
}

/**
 *
 *
 * @category    Aero
 * @package     Aero_View
 * @author      Alex Zavacki
 */
class View implements \ArrayAccess
{
    /**
     * @var string
     */
    protected $charset = 'UTF-8';

    /**
     * @var array
     */
    protected $globals = array();

    /**
     * @var array
     */
    protected $escapers = array();

    /**
     * @var string
     */
    protected $defaultEscapeContext;

    /**
     * @var array
     */
    protected $parents = array();

    /**
     * @var string
     */
    protected $currentTemplateKey;

    /**
     * @var array
     */
    protected $childContentStack = array();

    /**
     * @var \Aero\View\TemplateResolverInterface
     */
    protected $templateResolver;

    /**
     * @var \Aero\View\HelperPluginBroker
     */
    protected $helperPluginBroker;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->initEscapers();
    }

    /**
     * @throws \InvalidArgumentException
     *
     * @param  string $name
     * @param  array $vars
     * @return string
     */
    public function render($name, $vars = array())
    {
        $template = $this->getTemplateResolver()->resolve($name);

        if (!is_string($template)) {
            throw new \InvalidArgumentException(sprintf('Template "%s" not found', $name));
        }

        $this->currentTemplateKey = $template;
        $this->parents[$this->currentTemplateKey] = null;

        $allVars = array_replace($this->globals, $vars);

        $content = $this->evaluate($template, $allVars);

        if ($this->parents[$this->currentTemplateKey]) {
            $this->childContentStack[] = $content;
            list($parentTemplate, $parentVars) = $this->parents[$this->currentTemplateKey];
            $content = $this->render($parentTemplate, array_replace($vars, $parentVars));
            array_pop($this->childContentStack);
        }

        return $content;
    }

    /**
     * @throws \InvalidArgumentException
     *
     * @param  string $template
     * @param  array $vars
     * @return string
     */
    protected function evaluate($template, $vars = array())
    {
        foreach (array('__template__', '__vars__', 'this') as $var) {
            if (isset($vars[$var])) {
                throw new \InvalidArgumentException("Vars contain reserved parameter '$var'");
            }
        }

        $__template__ = $template;
        $__vars__     = $vars;

        unset($var, $template, $vars);

        extract($__vars__);

        ob_start();
        require $__template__;
        return ob_get_clean();
    }

    /**
     * Decorates the current template with another one
     *
     * @param  string $template
     * @param  array  $vars
     * @return \Aero\View\View
     */
    public function extend($template, $vars = array())
    {
        $this->parents[$this->currentTemplateKey] = array($template, $vars);
        return $this;
    }

    /**
     * @param  mixed $default
     * @return mixed
     */
    public function getChildContent($default = null)
    {
        if (!$this->childContentStack) {
            return $default;
        }

        end($this->childContentStack);
        return current($this->childContentStack);
    }

    /**
     * @param  array $options
     * @return \Aero\View\View
     */
    public function setOptions(array $options)
    {
        foreach ($options as $key => $value)
        {
            switch (strtolower($key))
            {
                case 'charset':
                    $this->setCharset($value);
                    break;
            }
        }

        return $this;
    }

    /**
     * Set helper broker
     *
     * @param  \Aero\View\HelperPluginBroker|string $broker
     * @return \Aero\View\View
     *
     * @throws \InvalidArgumentException
     */
    public function setHelperPluginBroker($broker)
    {
        if (is_string($broker)) {
            if (!class_exists($broker)) {
                throw new \InvalidArgumentException(sprintf(
                    'Invalid helper broker class provided (%s)',
                    $broker
                ));
            }
            $broker = new $broker();
        }

        if (!$broker instanceof HelperPluginBroker) {
            throw new \InvalidArgumentException(sprintf(
                'Helper broker must extend Aero\View\HelperBroker; got type "%s" instead',
                (is_object($broker) ? get_class($broker) : gettype($broker))
            ));
        }

        $this->helperPluginBroker = $broker;
        $this->helperPluginBroker->setView($this);

        return $this;
    }

    /**
     * Get plugin broker instance
     *
     * @return \Aero\View\HelperPluginBroker
     */
    public function getHelperPluginBroker()
    {
        if ($this->helperPluginBroker === null) {
            $this->setHelperPluginBroker($this->createDefaultHelperPluginBroker());
        }
        return $this->helperPluginBroker;
    }

    /**
     * @return \Aero\View\HelperPluginBroker
     */
    protected function createDefaultHelperPluginBroker()
    {
        return new HelperPluginBroker();
    }

    /**
     * Get helper instance
     *
     * @param  string $name
     * @param  array  $options
     *
     * @return \Aero\View\Helper\HelperInterface
     */
    public function helper($name, array $options = array())
    {
        return $this->getHelperPluginBroker()->getPlugin($name, $options);
    }

    /**
     * Overloading: proxy to helpers
     *
     * Proxies to the attached plugin broker to retrieve, return, and potentially
     * execute helpers.
     *
     * * If the helper does not define __invoke, it will be returned
     * * If the helper does define __invoke, it will be called as a functor
     *
     * @param  string $method
     * @param  array $argv
     *
     * @return mixed
     */
    public function __call($method, $argv)
    {
        $helper = $this->helper($method);

        if (is_callable($helper)) {
            return call_user_func_array($helper, $argv);
        }

        return $helper;
    }

    /**
     * @param  \Aero\View\TemplateResolverInterface $templateResolver
     * @return \Aero\View\View
     */
    public function setTemplateResolver(TemplateResolverInterface $templateResolver)
    {
        $this->templateResolver = $templateResolver;
        return $this;
    }

    /**
     * @return \Aero\View\TemplateResolverInterface
     */
    public function getTemplateResolver()
    {
        if (!$this->templateResolver instanceof TemplateResolverInterface) {
            $this->templateResolver = $this->createDefaultTemplateResolver();
        }
        return $this->templateResolver;
    }

    /**
     * @return \Aero\View\TemplateResolverInterface
     */
    protected function createDefaultTemplateResolver()
    {
        return new TemplateResolver();
    }

    /**
     * @param  string $charset
     * @return \Aero\View\View
     */
    public function setCharset($charset)
    {
        $this->charset = (string) $charset;
        return $this;
    }

    /**
     * @return string
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * Set global var
     *
     * @param  string $name
     * @param  mixed $value
     *
     * @return \Aero\View\View
     */
    public function addGlobal($name, $value)
    {
        $this->globals[$name] = $value;
        return $this;
    }

    /**
     * Get global var value
     *
     * @return array
     */
    public function getGlobals()
    {
        return $this->globals;
    }

    /**
     * Escape a string
     *
     * @param  mixed  $value
     * @param  string $context
     *
     * @return string
     */
    public function escape($value, $context = null)
    {
        if (!is_string($context)) {
            $context = $this->defaultEscapeContext;
        }
        return call_user_func($this->getEscaper($context), $value);
    }

    /**
     * @param  string $defaultEscapeContext
     * @return \Aero\View\View
     */
    public function setDefaultEscapeContext($defaultEscapeContext)
    {
        $this->defaultEscapeContext = (string) $defaultEscapeContext;
        return $this;
    }

    /**
     * @return string
     */
    public function getDefaultEscapeContext()
    {
        return $this->defaultEscapeContext;
    }

    /**
     * @param  string   $context
     * @param  callback $escaper
     *
     * @return \Aero\View\View
     */
    public function setEscaper($context, $escaper)
    {
        $this->escapers[$context] = $escaper;
    }

    /**
     * Get escaper for given context
     *
     * @throws \InvalidArgumentException
     *
     * @param  string $context
     * @return callback
     */
    public function getEscaper($context)
    {
        if (!isset($this->escapers[$context])) {
            throw new \InvalidArgumentException(sprintf('No registered escaper for context "%s".', $context));
        }
        return $this->escapers[$context];
    }

    /**
     * Initialize the built-in escapers
     *
     * @return \Aero\View\View
     */
    public function initEscapers()
    {
        if (!is_array($this->escapers)) {
            $this->escapers = array();
        }

        $this->escapers = array_merge($this->escapers, $this->getDefaultEscapers());

        return $this;
    }

    /**
     * Get default escapers
     *
     * @throws \InvalidArgumentException
     *
     * @author Fabien Potencier
     *
     * @return array
     */
    public function getDefaultEscapers()
    {
        $view = $this;

        return array(
            'html' =>
                /**
                 * Runs the PHP function htmlspecialchars on the value passed.
                 *
                 * @param string $value the value to escape
                 *
                 * @return string the escaped value
                 */
                function ($value) use ($view)
                {
                    /** @var $view \Aero\View\View */

                    // Numbers and Boolean values get turned into strings which can cause problems
                    // with type comparisons (e.g. === or is_int() etc).
                    return is_string($value)
                        ? htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, $view->getCharset(), false)
                        : $value;
                },

            'js' =>
                /**
                 * A function that escape all non-alphanumeric characters
                 * into their \xHH or \uHHHH representations
                 *
                 * @param string $value the value to escape
                 * @return string the escaped value
                 */
                function ($value) use ($view)
                {
                    /** @var $view \Aero\View\View */

                    if ('UTF-8' != $view->getCharset()) {
                        $value = $view->convertEncoding($value, 'UTF-8', $view->getCharset());
                    }

                    $callback = function ($matches) use ($view)
                    {
                        /** @var $view \Aero\View\View */

                        $char = $matches[0];

                        // \xHH
                        if (!isset($char[1])) {
                            return '\\x'.substr('00'.bin2hex($char), -2);
                        }

                        // \uHHHH
                        $char = $view->convertEncoding($char, 'UTF-16BE', 'UTF-8');

                        return '\\u'.substr('0000'.bin2hex($char), -4);
                    };

                    if (null === $value = preg_replace_callback('#[^\p{L}\p{N} ]#u', $callback, $value)) {
                        throw new \InvalidArgumentException('The string to escape is not a valid UTF-8 string.');
                    }

                    if ('UTF-8' != $view->getCharset()) {
                        $value = $view->convertEncoding($value, $view->getCharset(), 'UTF-8');
                    }

                    return $value;
                },
        );
    }

    /**
     * Convert a string from one encoding to another.
     *
     * @throws \RuntimeException if no suitable encoding function is found (iconv or mbstring)
     *
     * @author Fabien Potencier
     *
     * @param string $string The string to convert
     * @param string $to     The input encoding
     * @param string $from   The output encoding
     *
     * @return string The string with the new encoding
     */
    public function convertEncoding($string, $to, $from)
    {
        if (function_exists('iconv')) {
            return iconv($from, $to, $string);
        }
        elseif (function_exists('mb_convert_encoding')) {
            return mb_convert_encoding($string, $to, $from);
        }

        throw new \RuntimeException('No suitable convert encoding function (use UTF-8 as your encoding or install the iconv or mbstring extension).');
    }

    /**
     * Get helper instance
     *
     * @param  string $name
     * @return \Aero\View\Helper\HelperInterface
     */
    public function offsetGet($name)
    {
        return $this->helper($name);
    }

    /**
     * Check if helper is registered
     *
     * @param  string $name
     * @return bool
     */
    public function offsetExists($name)
    {
        return $this->getHelperPluginBroker()->isRegistered($name);
    }

    /**
     * Register helper
     *
     * @throws \InvalidArgumentException
     *
     * @param  string $name
     * @param  \Aero\View\Helper\HelperInterface $helper
     *
     * @return void
     */
    public function offsetSet($name, $helper)
    {
        if (!$helper instanceof Helper\HelperInterface) {
            throw new \InvalidArgumentException(
                'Helper must be instance of Aero\\View\\Helper\\HelperInterface'
            );
        }
        $this->getHelperPluginBroker()->register($name, $helper);
    }

    /**
     * Remove a helper
     *
     * @throws \LogicException
     *
     * @param  string $name
     * @return void
     */
    public function offsetUnset($name)
    {
        throw new \LogicException(sprintf("Helpers cannot be removed", $name));
    }
}
