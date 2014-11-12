<?php
namespace Opine;
use PHPUnit_Framework_TestCase;

class HandlebarsTest extends PHPUnit_Framework_TestCase {
    private $handlebars;
    private $layout;

    public function setup () {
        date_default_timezone_set('UTC');
        $root = __DIR__ . '/../public';
        $container = new Container($root, $root . '/../container.yml');
        $this->handlebars = $container->handlebarService;
        $this->handlebars->quiet();
        $this->layout = $container->separation;
    }

    private function normalizeResponse ($input) {
        return str_replace(['    ', "\n"], '', $input);
    }

    public function testSample () {
        $this->assertTrue($this->handlebars->build());
    }

    public function testCachedApp () {
        ob_start();
        $this->layout->
            app('app/test')->
            layout('layout')->
            data('test', ['test' => 'ABC'])->
            write();
        $response = ob_get_clean();
        $this->assertTrue($this->normalizeResponse($response) == '<html><head><title></title></head><body><div><div>ABC</div></div></body></html>');
    }
}