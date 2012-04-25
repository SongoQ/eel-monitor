<?php

/*
 * This file is part of Eel-Monitor.
 *
 * (c) Marcin Chyłek <marcin@chylek.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EelMonitor\Service\Url;

use EelMonitor\Service\Service;

class UrlService extends Service
{
    public function execute()
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->options['url']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                
        $output = curl_exec($ch);
        $info = curl_getinfo($ch);
        
        if ($output === false || $info['http_code'] != $this->options['status_code']) {
            $this->getServiceInfo()->setStatus(false);
            $this->getServiceInfo()->setErrorMessage('Service "'.$this->options['url'].'" response status code: '.$info['http_code']);
        } else {
            $this->getServiceInfo()->setStatus(true);
        }
        
        curl_close($ch);
    }
    
}
