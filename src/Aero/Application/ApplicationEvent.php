<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Application;

use Aero\Std\Event\Event;

/**
 * Application event
 *
 * @category    Aero
 * @package     Aero_Application
 * @author      Alex Zavacki
 */
class ApplicationEvent extends Event
{
    /**
     * Application events
     */
    const BOOTSTRAP = 'application.bootstrap';


    /**
     * @return \Aero\Application\AbstractApplication
     */
    public function getApplication()
    {
        return $this->getParam('application');
    }

    /**
     * @param  \Aero\Application\AbstractApplication $app
     * @return \Aero\Application\ApplicationEvent
     */
    public function setApplication(AbstractApplication $app)
    {
        $this->setParam('application', $app);
        return $this;
    }
}
