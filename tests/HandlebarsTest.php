<?php
namespace Opine;

use PHPUnit_Framework_TestCase;
use Opine\Config\Service as Config;
use Opine\Container\Service as Container;

class HandlebarsTest extends PHPUnit_Framework_TestCase
{
    private $handlebars;
    private $layout;

    public function setup()
    {
        $root = __DIR__.'/../public';
        $config = new Config($root);
        $config->cacheSet();
        $container = Container::instance($root, $config, $root.'/../config/containers/test-container.yml');
        $this->handlebars = $container->get('handlebarService');
        $this->handlebars->quiet();
        $this->layout = $container->get('layout');
    }

    private function normalizeResponse($input)
    {
        return str_replace(['    ', "\n"], '', $input);
    }

    public function testSample()
    {
        $this->assertTrue($this->handlebars->build());
    }

    public function testCachedApp()
    {
        $context = ['test' => ['test' => 'ABC']];
        ob_start();
        $this->layout->
            config('test', $context)->
            container('layout')->
            write();
        $response = ob_get_clean();
        $this->assertTrue($this->normalizeResponse($response) == '<html><head><title></title></head><body><div><div>ABC</div></div></body></html>');
    }
}
