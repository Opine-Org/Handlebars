<?php
namespace Opine;

class HandlebarsTest extends \PHPUnit_Framework_TestCase {
    private $handlebars;

    public function setup () {
        date_default_timezone_set('UTC');
        $root = __DIR__ . '/../public';
        $container = new Container($root, $root . '/../container.yml');
        $this->handlebars = $container->handlebars;
        $this->handlebars->quiet();
    }

    public function testSample () {
        $this->handlebars->build();
    }
}