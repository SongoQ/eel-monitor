<?php

/*
 * This file is part of Eel-Monitor.
 *
 * (c) Marcin Chyłek <marcin@chylek.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EelMonitor;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;

class Compiler
{

    private $versionGit = null;

    /**
     * Compiles eel-monitor into a single phar file
     *
     * @throws \RuntimeException
     * @param string $pharFile The full path to the file to create
     */
    public function compile($pharFile = 'eel-monitor.phar')
    {

        if (file_exists($pharFile)) {
            unlink($pharFile);
        }

        $process = new Process('git log --pretty="%h" -n1 HEAD', __DIR__);
        if ($process->run() != 0) {
            throw new \RuntimeException('Can\'t run git log.');
        }

        $this->versionGit = trim($process->getOutput());

        try {
            $phar = new \Phar($pharFile, 0, 'eel-monitor.phar');
            $phar->setSignatureAlgorithm(\Phar::SHA1);

            $phar->startBuffering();

            $finder = new Finder();
            $finder->files()
                    ->ignoreVCS(true)
                    ->name('*.php')
                    ->notName('Compiler.php')
                    ->notName('EelMonitor.php')
                    ->in(__DIR__.'/..')
            ;

            foreach ($finder as $file) {
                $this->addFile($phar, $file);
            }

            $finder = new Finder();
            $finder->files()
                    ->ignoreVCS(true)
                    ->name('*.php')
                    ->exclude('Tests')
                    ->in(__DIR__.'/../../vendor')
            ;

            foreach ($finder as $file) {
                $this->addFile($phar, $file);
            }

            $this->addFilterFile($phar, new \SplFileInfo(__DIR__.'/../../bin/eel-monitor'));
            $this->addFilterFile($phar, new \SplFileInfo(__DIR__.'/../../src/EelMonitor/EelMonitor.php'));

            // Stubs
            $phar->setStub($this->getStub());
            $this->addFile($phar, new \SplFileInfo(__DIR__.'/../../LICENSE'));

            $phar->stopBuffering();
            unset($phar);

            chmod($pharFile, 0777);
        } catch (\UnexpectedValueException $e) {
            echo $e->getMessage();
        }
    }

    private function addFile(\Phar $phar, $file)
    {

        $path = str_replace(dirname(dirname(__DIR__)).DIRECTORY_SEPARATOR, '', $file->getRealPath());
        $phar->addFile($file, $path);
    }

    private function addFilterFile(\Phar $phar, $file)
    {

        $path = str_replace(dirname(dirname(__DIR__)).DIRECTORY_SEPARATOR, '', $file->getRealPath());
        $content = file_get_contents($file);

        $content = preg_replace('{^#!/usr/bin/env php\s*}', '', $content);
        $content = preg_replace('@(VERSION = \'(.+?))\';@', '$1-'.$this->versionGit.'\';', $content);

        $phar->addFromString($path, $content);
    }

    private function getStub()
    {

        return <<<'EOF'
#!/usr/bin/env php
<?php

/*
 * This file is part of Eel-Monitor.
 *
 * (c) Marcin Chyłek <marcin@chylek.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

Phar::mapPhar('eel-monitor.phar');

require 'phar://eel-monitor.phar/bin/eel-monitor';

__HALT_COMPILER();
EOF;
    }

}
