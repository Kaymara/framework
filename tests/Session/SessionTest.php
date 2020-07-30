<?php declare(strict_types=1);

namespace Test\Session;

use Compose\Session\Session;
use PHPUnit\Framework\TestCase;

class SessionTest extends TestCase
{
    public function testGetsName()
    {
        $session = Session::create('foo');

        $this->assertEquals('foo', $session->name());
    }

    public function testSetsName()
    {
        $session = Session::create();
        $session->name('foo');

        $this->assertEquals('foo', $session->name());
    }

    public function testGetsSetsID()
    {
        $session = Session::create();
        $session->setID('foo');

        $this->assertEquals('foo', $session->getID());
    }

    public function testGeneratesID()
    {
        $session = Session::create();
        $session->setID();

        $this->assertNotEmpty($session->getID());
    }

    public function testGetsSetsAttribute()
    {
        $session = Session::create();
        $session->set('foo', 'bar');

        $this->assertEquals('bar', $session->get('foo'));
    }

    public function testSetsMultipleAttributes()
    {
        $session = Session::create();
        $session->setMany($attributes = ['foo' => 'bar', 'baz' => 'amara']);

        foreach (['foo', 'baz'] as $key) {
            $this->assertEquals($attributes[$key], $session->get($key));
        }
    }
}