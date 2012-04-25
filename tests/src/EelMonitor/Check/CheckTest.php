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

class CheckTest extends \PHPUnit_Framework_TestCase
{
    private $object;
    
    protected function setUp()
    {
        chmod(__DIR__.'/../../fixtures/eel-monitor_error.yml', 0666);
        $this->object = new Check();
    }

    protected function tearDown()
    {
        chmod(__DIR__.'/../../fixtures/eel-monitor_error.yml', 0666);
    }

    /**
     * @expectedException           EelMonitor\Check\CheckException
     * @expectedExceptionMessage    Config file example-eel-monitor.yml does not exist.
     */
    public function testSetConfigPathNotExist()
    {
        $this->object->setConfigPath('example-eel-monitor.yml');
    }
    
    public function testSetConfigPathExistOK()
    {
        $this->object->setConfigPath(__DIR__.'/../../fixtures/eel-monitor_ok.yml');
    }
    
    public function testSetConfigPathExistPermission()
    {
        try {
            chmod(__DIR__.'/../../fixtures/eel-monitor_error.yml', 0000);
            $this->object->setConfigPath(__DIR__.'/../../fixtures/eel-monitor_error.yml');
        } catch (\EelMonitor\Check\CheckException $e) {
            $this->assertRegExp('/Config file (.+) is permission denied\./im', $e->getMessage(), $e->getMessage());
        }
    }
    
    /**
     * @expectedException           EelMonitor\Check\CheckException
     */
    public function testSetConfigPathExistError()
    {
        $this->object->setConfigPath(__DIR__.'/../../fixtures/eel-monitor_error.yml');
    }
    
    public function testSetConfigParamsOKNull()
    {
        $this->object->setConfigParams('');
    }
    
    public function testSetConfigParamsOKConfig()
    {
        $this->object->setConfigParams(<<<EOT
service:
  
  example_1:
    type:   url
    url:    http://google.com/
    status: 200

EOT
        );
    }
    
    /**
     * @expectedException           EelMonitor\Check\CheckException
     */
    public function testSetConfigParamsError()
    {
        $this->object->setConfigParams('error config
            ');
    }

    /**
     * @expectedException           EelMonitor\Check\CheckException
     */
    public function testCheckServiceError()
    {
        $service = array('url' => 'http://google.com/', 'status' => 200);
        $this->object->checkService('test', $service);
    }
    
    public function testCheckServiceOK()
    {
        $service = array('type' => 'url', 'url' => 'http://google.com/', 'status_code' => 200);
        $this->object->checkService('test', $service);
    }
    
    /**
     * @expectedException           EelMonitor\Check\CheckException
     * @expectedExceptionMessage    Service not defined.
     */
    public function testExecuteError()
    {
        $this->object->setConfigParams(<<<EOT
test:
  
  example_1:
    type:         url
    url:          http://google.com/
    status_code:  200

EOT
        );
        
        $this->object->execute();
    }
    
    public function testExecuteOK()
    {
        $this->object->setConfigParams(<<<EOT
service:
  
  example_1:
    type:          url
    url:           http://google.com/
    status_code:   200

EOT
        );
        
        $this->object->execute();
    }
}
