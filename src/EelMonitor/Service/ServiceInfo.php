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

class ServiceInfo
{

    private $type = null,
            $timeStart = null,
            $timeStop = null,
            $status = null;

    public function setTimeStart($value)
    {
        $this->timeStart = $value;
    }

    public function getTimeStart()
    {
        return $this->timeStart;
    }

    public function setTimeStop($value)
    {
        $this->timeStop = $value;
    }

    public function getTimeStop()
    {
        return $this->timeStop;
    }

    public function setType($value)
    {
        $this->type = $value;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setStatus($value)
    {
        $this->status = $value;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setErrorMessage($value)
    {
        $this->errroMessage = $value;
    }

    public function getErrorMessage()
    {
        return $this->errroMessage;
    }

}
