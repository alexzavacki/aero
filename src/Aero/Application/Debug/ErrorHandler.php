<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Application\Debug;

/**
 * Application error handler
 *
 * @category    Aero
 * @package     Aero_Application
 * @subpackage  Aero_Application_Debug
 * @author      Alex Zavacki
 */
class ErrorHandler
{
    /**
     * @var array
     */
    protected $levels = array(
        E_WARNING           => 'Warning',
        E_NOTICE            => 'Notice',
        E_USER_ERROR        => 'User Error',
        E_USER_WARNING      => 'User Warning',
        E_USER_NOTICE       => 'User Notice',
        E_STRICT            => 'Runtime Notice',
        E_RECOVERABLE_ERROR => 'Catchable Fatal Error',
    );

    /**
     * @var int
     */
    protected $level;


    /**
     * Register the error handler.
     *
     * @param  int $level
     * @return \Aero\Application\Debug\ErrorHandler
     */
    public static function register($level = null)
    {
        $handler = new static();
        $handler->setLevel($level);

        set_error_handler(array($handler, 'handle'));

        return $handler;
    }

    /**
     * @param int $level
     * @return \Aero\Application\Debug\ErrorHandler
     */
    public function setLevel($level)
    {
        $this->level = $level !== null ? (int) $level : error_reporting();
        return $this;
    }

    /**
     * Error handle method
     *
     * @param  int $level
     * @param  string $message
     * @param  string $file
     * @param  int $line
     * @param  array $context
     * @return bool
     *
     * @throws \ErrorException When error_reporting returns error
     */
    public function handle($level, $message, $file, $line, $context)
    {
        if ($this->level === 0) {
            return false;
        }

        if (error_reporting() & $level && $this->level & $level) {
            throw new \ErrorException(
                sprintf(
                    '%s: %s in %s line %d',
                    isset($this->levels[$level]) ? $this->levels[$level] : $level,
                    $message,
                    $file,
                    $line
                )
            );
        }

        return false;
    }
}
