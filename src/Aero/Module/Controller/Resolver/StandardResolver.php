<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Module\Controller\Resolver;

use Aero\Application\Controller\Resolver\StandardResolver as BaseStandardResolver;
use Aero\Application\Controller\Resolver\ControllerNameParserInterface;

/**
 * Standard module controller resolver
 *
 * @category    Aero
 * @package     Aero_Module
 * @subpackage  Aero_Module_Controller
 * @author      Alex Zavacki
 */
class StandardResolver extends BaseStandardResolver
{
    /**
     * @var \Aero\Application\Controller\Resolver\ControllerNameParserInterface
     */
    protected $nameParser;


    /**
     * Returns a callable controller for the given string.
     *
     * @param  string $controller
     * @return callback
     */
    protected function parseControllerName($controller)
    {
        $count = substr_count($controller, ':');

        if ($count == 2) {
            // controller in the a:b:c notation then
            $controller = $this->nameParser->parse($controller);
        }
        else {
            throw new \LogicException(sprintf('Unable to parse the controller name "%s".', $controller));
        }
    }

    /**
     * @param  \Aero\Application\Controller\Resolver\ControllerNameParserInterface $nameParser
     * @return \Aero\Module\Controller\Resolver\StandardResolver
     */
    public function setNameParser(ControllerNameParserInterface $nameParser)
    {
        $this->nameParser = $nameParser;
        return $this;
    }

    /**
     * @return \Aero\Application\Controller\Resolver\ControllerNameParserInterface
     */
    public function getNameParser()
    {
        return $this->nameParser;
    }
}
