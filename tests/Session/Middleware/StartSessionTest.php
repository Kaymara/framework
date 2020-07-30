<?php declare(strict_types=1);

namespace Test\Session\Middleware;

use Compose\Http\Request;
use \Compose\Http\Response;
use Compose\Session\Middleware\StartSession;
use Compose\Session\Session;
use PHPUnit\Framework\TestCase;

class StartSessionTest extends TestCase
{
    public function testItStartsASession()
    {
        $middleware = new StartSession;

        $middleware(new Request, new Response, [], fn() => 'foo');

        $this->assertEquals(PHP_SESSION_ACTIVE, session_status());
    }

    // it gets session
    public function testItRetrievesSession()
    {
        $middleware = new StartSession;

        $this->assertInstanceOf(Session::class, $middleware->getSession(new Request));
    }

    // it creates a cookie with session ID
    // it can set session attributes
    // it garbage collects
}