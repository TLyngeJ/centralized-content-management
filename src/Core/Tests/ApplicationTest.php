<?php

namespace CCM\Core\Tests;

use CCM\Core\Application;
use React\Http\Request;
use React\Http\Response;
use React\Tests\Http\ConnectionStub;

class ApplicationTest extends \PHPUnit_Framework_TestCase {
    
    public function testRun() {
        $request = new Request('GET', '/');
        $response = new Response(new ConnectionStub());
        $app = new Application($request, $response);
        var_dump($request);
        $this->assertEquals(42, 43);
    }
}