<?php
/**
 * Opine\Handlebars
 *
 * Copyright (c)2013, 2014 Ryan Mahoney, https://github.com/Opine-Org <ryan@virtuecenter.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
namespace Opine;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use Exception;
use LightnCandy;

class Handlebars {
    private $root;
    private $engine;
    private $quiet = false;
    private $helpers = [];
    private $hbhelpers = [];
    private $blockhelpers = [];

    public function __construct ($root, $engine) {
        $this->root = $root;
        $this->engine = $engine;
        $this->helpersLoad();
    }

    public function helpersLoad () {
        $helpersFile = $root . '/../cache/helpers.php';
        if (file_exists($helpersFile)) {
            $this->helpers = require $helpersFile;
        }
        $helpersFile = $root . '/../cache/hbhelpers.php';
        if (file_exists($helpersFile)) {
            $this->hbhelpers = require $helpersFile;
        }
        $helpersFile = $root . '/../cache/blockhelpers.php';
        if (file_exists($helpersFile)) {
            $this->blockhelpers = require $helpersFile;
        }
    }

    public function quiet () {
        $this->quiet = true;
    }

    private function compileFile ($input) {
        $output = str_replace('/public/', '/cache/', $input);
        try {
            $php = $this->engine->compile(
                file_get_contents($input), 
                [
                    'flags' => LightnCandy::FLAG_STANDALONE | LightnCandy::FLAG_HANDLEBARSJS | LightnCandy::FLAG_ERROR_EXCEPTION,
                    'helpers' => $this->helpers,
                    'hbhelpers' => $this->hbhelpers,
                    'blockhelpers' => $this->blockhelpers
                ]
            );
            $pathParts = pathinfo($output);
            if (!file_exists($pathParts['dirname'])) {
                mkdir($pathParts['dirname'], 0777, true);
            }
            file_put_contents($output, $php);
            if (filesize($output) == 0) {
                echo 'Bad Compile: ', $output, "\n";
            } else {
//                $result = shell_exec(PHP_BINARY . ' -l ' . escapeshellarg($output));
//                if (substr_count($result, 'No syntax errors detected') <  1) {
//                   echo 'Syntax Errors: ', $output, "\n";
//                }
            }
            return true;
        } catch (Exception $e) {
            echo $input, ': ', $e->getMessage(), "\n";
            return $e->getMessage();
        }
    }

    private function rsearch($folder, $extension) {
        $dir = new RecursiveDirectoryIterator($folder);
        $ite = new RecursiveIteratorIterator($dir);
        $files = new RegexIterator($ite, '/' . $extension . '$/');
        $files->setMode(RegexIterator::MATCH);
        $fileList = [];
        foreach ($files as $file) {
            $fileList[] = $file->getPathname();
        }
        return $fileList;
    }

    private function compileFolder ($folder, $type) {
        if (!file_exists($folder)) {
            return;
        }
        $files = glob($folder . '/*.' . $type);
        foreach ($files as $file) {
            echo $file, "\n";
            $result = $this->compileFile($file);
            var_dump($result);
            if ($this->quiet === true) {
                continue;
            }
            if ($result === true) {
                echo 'COMPILED: ', $file, "\n";
            } else {
                echo 'ERROR: ', $file, ': ', $result, "\n";
            }
        }
    }

    public function build () {
        foreach ($this->rsearch($this->root . '/../public/layouts', 'html') as $file) {
            $this->compileFile($file);
        }
        foreach ($this->rsearch($this->root . '/../public/partials', 'hbs') as $file) {
            $this->compileFile($file);
        }
    }
}