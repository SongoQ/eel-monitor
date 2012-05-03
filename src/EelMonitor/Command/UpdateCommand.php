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
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use EelMonitor\EelMonitor;

class UpdateCommand extends Command
{

    protected function configure()
    {
        $this
                ->setName('update')
                ->addArgument('file', InputArgument::OPTIONAL, 'path to eel-monitor.phar', $_SERVER['argv'][0])
                ->setDescription('Updates eel-monitor.phar to the latest version.')
                ->setHelp(<<<EOT
The <info>update</info> command checks http://downloads.eel-monitor.ampluso.com for newer
versions and if found, installs the latest.

<info>./eel-monitor.phar update</info> or <info>eel-monitor.phar update</info>

<info>./eel-monitor.phar update eel-monitor.phar</info> or <info>eel-monitor.phar update eel-monitor.phar</info>
EOT
                )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $latestVersion = trim(file_get_contents('http://downloads.eel-monitor.ampluso.com/version'));

        if (EelMonitor::VERSION !== $latestVersion) {

            $latestFile = file_get_contents('http://downloads.eel-monitor.ampluso.com/eel-monitor.phar');
            $localFilename = $input->getArgument('file');

            file_put_contents($localFilename, $latestFile);
            chmod($localFilename, 0777);

            $output->writeln(sprintf("Updating to version <info>%s</info>.", $latestVersion));
        } else {
            $output->writeln("<info>You are using the latest eel-monitor version.</info>");
        }
    }

}
