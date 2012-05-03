<?php

/*
 * This file is part of Eel-Monitor.
 *
 * (c) Marcin Chyłek <marcin@chylek.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EelMonitor\Console;

use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use EelMonitor\Command;
use EelMonitor\EelMonitor;

class Application extends BaseApplication
{

    public function __construct()
    {

        parent::__construct('eel-monitor', EelMonitor::VERSION);
    }

    public function doRun(InputInterface $input, OutputInterface $output)
    {

        $this->registerCommands();
        return parent::doRun($input, $output);
    }

    /**
     * Initializes all the Eel-Monitor commands
     */
    protected function registerCommands()
    {

        $this->add(new Command\CheckCommand());
        $this->add(new Command\UpdateCommand());
    }

}
