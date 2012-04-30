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
        $check = new Check(Check::RESPONSE_STATUS, true);
        $check->checkService('test', $service);
    }
    
    public function testCheckServiceOK()
    {
        $service = array('type' => 'url', 'url' => 'http://google.com/', 'status_code' => 200);
        $check = new Check(Check::RESPONSE_STATUS, true);
        $check->checkService('test', $service);
    }
    
    /**
     * @expectedException           EelMonitor\Check\CheckException
     * @expectedExceptionMessage    Service is not defined.
     */
    public function testExecuteServiceIsNotDefined()
    {
        $check = new Check(Check::RESPONSE_STATUS, true);
        $check->setConfigParams(<<<EOT
test:
  
  example_1:
    type:         url
    url:          http://eel-monitor.ampluso.com/
    status_code:  200

EOT
        );
        
        $check->execute();
    }
    
    /**
     * @expectedException           EelMonitor\Check\CheckException
     */
    public function testExecuteConsoleError()
    {
        $check = new Check(Check::RESPONSE_STATUS, true);
        $check->setConfigParams(<<<EOT
service:
  
  example_1:
    type:         url
    url:          http://eel-monitor.ampluso.com/error
    status_code:  200

EOT
        );
        
        $check->execute();
    }
    
    public function testExecuteOK()
    {
        $check = new Check(Check::RESPONSE_STATUS, false);
        $check->setConfigParams(<<<EOT
service:
  
  example_1:
    type:          url
    url:           http://eel-monitor.ampluso.com/
    status_code:   200

EOT
        );
        
        $response = $check->execute();
        $this->assertEquals($response->getStatusCode(), 200);
        $this->assertEquals($response->getContent(), 'OK');
    }
    
    public function testExecuteError()
    {
        $check = new Check(Check::RESPONSE_STATUS, false);
        $check->setConfigParams(<<<EOT
service:
  
  example_1:
    type:          url
    url:           http://eel-monitor.ampluso.com/error
    status_code:   200

EOT
        );
        
        $response = $check->execute();
        
        $this->assertEquals($response->getStatusCode(), 500);
        $this->assertEquals($response->getContent(), 'Service "http://eel-monitor.ampluso.com/error" response status code: 404');
    }
    
    public function testExecuteConsoleOK()
    {
        $check = new Check(Check::RESPONSE_STATUS, true);
        $check->setConfigParams(<<<EOT
service:
  
  example_1:
    type:          url
    url:           http://eel-monitor.ampluso.com/
    status_code:   200

EOT
        );
        
        $this->assertEquals($check->execute(), 'OK');
    }
    
}
