<?php

/*
 * This file is part of Eel-Monitor.
 *
 * (c) Marcin Chyłek <marcin@chylek.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EelMonitor\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use EelMonitor\Check\Check;

class CheckCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('check')
            ->setDescription('Check services.')
            ->addOption('config-file', null, InputOption::VALUE_OPTIONAL, 'config (eel-monitor.yml) path', 'eel-monitor.yml')
            ->setHelp(<<<EOT
<info>Eel-Monitor - Simple Web Server monitoring</info>

EOT
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $check = new Check(Check::RESPONSE_STATUS, true);
            $check->setConfigPath($input->getOption('config-file'));
            $response = $check->execute();
            
            $output->writeln('<info>'.$response.'</info>');            
        } catch (Exception $e) {
            
            $output->writeln('<error>'.$e->getMessage().'</error>');
        }
    }
}
