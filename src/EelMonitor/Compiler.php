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

class Compiler {

    /**
     * Compiles eel-monitor into a single phar file
     *
     * @throws \RuntimeException
     * @param string $pharFile The full path to the file to create
     */
    public function compile($pharFile = 'eel-monitor.phar') {
        
        if (file_exists($pharFile)) {
            unlink($pharFile);
        }

        $phar = new \Phar($pharFile, 0, 'eel-monitor.phar');
        $phar->setSignatureAlgorithm(\Phar::SHA1);
        
        $phar->startBuffering();

        $finder = new Finder();
        $finder->files()
                ->ignoreVCS(true)
                ->name('*.php')
                ->notName('Compiler.php')
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

        $this->addBinFile($phar);

        // Stubs
        $phar->setStub($this->getStub());
        $this->addFile($phar, new \SplFileInfo(__DIR__.'/../../LICENSE'));

        $phar->stopBuffering();
        unset($phar);
    }

    private function addFile(\Phar $phar, $file) {

        $path = str_replace(dirname(dirname(__DIR__)).DIRECTORY_SEPARATOR, '', $file->getRealPath());
        $phar->addFile($file, $path);
    }

    private function addBinFile(\Phar $phar) {

        $content = file_get_contents(__DIR__.'/../../bin/eel-monitor');
        $content = preg_replace('{^#!/usr/bin/env php\s*}', '', $content);
        $phar->addFromString('bin/eel-monitor', $content);
    }

    private function getStub() {
        
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
