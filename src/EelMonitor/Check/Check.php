<?php

/*
 * This file is part of Eel-Monitor.
 *
 * (c) Marcin Chyłek <marcin@chylek.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EelMonitor\Check;

use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\HttpFoundation\Response;
use EelMonitor\Check\CheckException;

class Check
{

    const RESPONSE_STATUS = 'status';

    private $config = null;
    private $isConsoleMode = false;
    private $responseType = null;

    public function __construct($responseType = Check::RESPONSE_STATUS, $isConsoleMode = false)
    {
        $this->responseType = $responseType;
        $this->isConsoleMode = $isConsoleMode;
    }

    /**
     * @throws CheckException 
     */
    public function execute()
    {
        if (!isset($this->config['service'])) {
            throw new CheckException('Service is not defined.');
        }

        return $this->executeStatus($this->config['service']);
    }

    private function executeStatus($services)
    {
        foreach ($services as $key => $service) {
            $info = $this->checkService($key, $service);
            
            if (!$info->getStatus()) {
                if ($this->isConsole()) {
                    throw new CheckException($info->getErrorMessage());
                } else {
                    $response = new Response($info->getErrorMessage(), 500, array('content-type' => 'text/html'));
                    return $response->send();
                }
            }
        }

        if ($this->isConsole()) {
            return 'OK';
        } else {
            $response = new Response('OK', 200, array('content-type' => 'text/html'));
            return $response->send();
        }
    }

    /**
     * Processing check service
     * 
     * @param string $serviceName
     * @param array $service
     * @throws CheckException 
     */
    public function checkService($serviceName, $service)
    {
        if (!isset($service['type'])) {
            throw new CheckException('Service '.$serviceName.' type not defined.');
        }

        $className = ucfirst($service['type']);
        $classNamespace = '\\EelMonitor\Service\\'.$className.'\\'.$className.'Service';

        $obj = new $classNamespace($service);
        $obj->preExecute();
        $obj->execute();
        $obj->postExecute();

        return $obj->getServiceInfo();
    }

    /**
     * Parsing configuration file
     * 
     * @param string $value
     * @throws CheckException 
     */
    public function setConfigParams($value)
    {
        try {
            $yaml = new Parser();
            $this->config = $yaml->parse($value);
        } catch (ParseException $e) {
            throw new CheckException($e->getMessage());
        }
    }

    /**
     * Read a file from the configuration
     * 
     * @param string $path 
     * @throws CheckException 
     */
    public function setConfigPath($path)
    {
        if (file_exists($path)) {
            if (is_readable($path)) {
                $this->setConfigParams(file_get_contents($path));
            } else {
                throw new CheckException("Config file $path is permission denied.");
            }
        } else {
            throw new CheckException("Config file $path does not exist.");
        }
    }

    /**
     * Return is started in console mode
     * 
     * @return boolean true - console, false - other
     */
    private function isConsole()
    {
        return $this->isConsoleMode;
    }
}
