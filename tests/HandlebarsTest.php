<?php
namespace Opine;

class HandlebarsTest extends \PHPUnit_Framework_TestCase {
    private $handlebars;
    private $separation;

    public function setup () {
        date_default_timezone_set('UTC');
        $root = __DIR__ . '/../public';
        $container = new Container($root, $root . '/../container.yml');
        $this->handlebars = $container->handlebarService;
        $this->handlebars->quiet();
        $this->separation = $container->separation;
    }

    private function normalizeResponse ($input) {
        return str_replace(['    ', "\n"], '', $input);
    }

    public function testSample () {
        $this->handlebars->build();
    }

    public function testCachedApp () {
        ob_start();
        $this->separation->
            app('app/test')->
            layout('layout')->
            data('test', ['test' => 'ABC'])->
            template()->
            write();
        $response = ob_get_clean();
        $this->assertTrue($this->normalizeResponse($response) == '<html><head><title></title></head><body><div><div>ABC</div></div></body></html>');
    }
}