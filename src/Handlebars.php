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

class Handlebars {
    private $root;
    private $engine;
    private $bundleRoute;
    private $quiet = false;

    public function __construct ($root, $engine, $bundleRoute) {
        $this->root = $root;
        $this->engine = $engine;
        $this->bundleRoute = $bundleRoute;
    }

    public function quiet () {
        $this->quiet = true;
    }

    private function compileFile ($input) {
        $output = rtrim(rtrim($input, 'html'), 'hbs') . 'php';
        $helpers = @include $this->root . '/../public/helpers/_build.php';
        $hbhelpers = @include $this->root . '/../public/hbhelpers/_build.php';
        $blockhelpers = @include $this->root . '/../public/blockhelpers/_build.php';
        try {
            $php = $this->engine->compile(
                file_get_contents($input), 
                [
                    'flags' => \LightnCandy::FLAG_STANDALONE | \LightnCandy::FLAG_HANDLEBARSJS,
                    'helpers' => ($helpers != false ? $helpers : []),
                    'hbhelpers' => ($hbhelpers != false ? $hbhelpers : []),
                    'blockhelpers' => ($blockhelpers != false ? $blockhelpers : [])
                ]
            );
            file_put_contents($output, $php);
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
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
        $this->compileFolder($this->root . '/../public/layouts', 'html');
        $this->compileFolder($this->root . '/../public/partials', 'hbs');
        $bundles = $this->bundleRoute->bundles();
        foreach ($bundles as $bundle) {
            $this->compileFolder($this->root . '/../bundles/' . $bundle . '/public/layouts', 'html');
            $this->compileFolder($this->root . '/../bundles/' . $bundle . '/public/partials', 'hbs');
        }
    }
}