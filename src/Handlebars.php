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
        $output = rtrim(rtrim($input, 'html'), 'hbs') . 'php';
        try {
            $php = $this->engine->compile(
                file_get_contents($input), 
                [
                    'flags' => \LightnCandy::FLAG_STANDALONE | \LightnCandy::FLAG_HANDLEBARSJS,
                    'helpers' => $this->helpers,
                    'hbhelpers' => $this->hbhelpers,
                    'blockhelpers' => $this->blockhelpers
                ]
            );
            file_put_contents($output, $php);
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    private function rsearch($folder, $extension) {
        $dir = new RecursiveDirectoryIterator($folder);
        $ite = new RecursiveIteratorIterator($dir);
        $files = new RegexIterator($ite, '/\.' . $extension . '$/', RegexIterator::GET_MATCH);
        $fileList = [];
        foreach ($files as $file) {
            $fileList = array_merge($fileList, $file);
        }
        return $fileList;
    }

    private function compileFolder ($folder, $type) {
        if (!file_exists($folder)) {
            return;
        }
        $files = glob($folder . '/*.' . $type);
        foreach ($files as $file) {
            $result = $this->compileFile($file);
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
        print_r ($this->rsearch($this->root . '/../public/layouts', 'html'));
        exit;
        $this->rsearch($this->root . '/../public/partials', 'hbs');
    }
}