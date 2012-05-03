<?php

/*
 * This file is part of Eel-Monitor.
 *
 * (c) Marcin Chyłek <marcin@chylek.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EelMonitor\Service;

use EelMonitor\Service\ServiceInfo;
use EelMonitor\Service\ServiceInterface;

abstract class Service implements ServiceInterface
{

    protected
            $options = array(),
            $serviceInfo = null;

    public function __construct($options)
    {
        $this->serviceInfo = new ServiceInfo();
        $this->options = $options;
        $this->serviceInfo->setType($this->options['type']);
    }

    public function preExecute()
    {
        $this->serviceInfo->setTimeStart(microtime(true));
    }

    public function postExecute()
    {
        $this->serviceInfo->setTimeStop(microtime(true));
    }

    /**
     * @return EelMonitor\Service\ServiceInfo ServiceInfo object 
     */
    public function getServiceInfo()
    {
        return $this->serviceInfo;
    }

}
