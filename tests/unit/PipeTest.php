<?php
use PetrGrishin\Pipe\Pipe;

/**
 * @author Petr Grishin <petr.grishin@grishini.ru>
 */

class PipeTest extends PHPUnit_Framework_TestCase {
    public function testPipe() {
        $passable = 'test';
        $i = 0;
        $that = $this;
        PipeTest__Middleware::$run = function($_passable) use ($passable, $that, &$i) {
            $i = $i + 1;
            $that->assertEquals($passable, $_passable);
        };
        $this->assertEquals(0, $i);
        Pipe::create($passable)
            ->through(array('PipeTest__Middleware'))
            ->then(function ($_passable) use ($passable, $that, &$i) {
                $that->assertEquals(1, $i);
                $i = $i + 1;
                $that->assertEquals($passable, $_passable);
            });
        $this->assertEquals(2, $i);
    }

    public function testPipeWithArguments() {
        $passable = 'test';
        $i = 0;
        $argValue = 'argValue';
        $that = $this;
        PipeTest__Middleware::$run = function($_passable, $arg1) use ($passable, $that, $argValue, &$i) {
            $i = $i + 1;
            $that->assertEquals($passable, $_passable);
            $that->assertEquals($argValue, $arg1);
        };
        $this->assertEquals(0, $i);
        Pipe::create($passable)
            ->through(array(array('PipeTest__Middleware', $argValue)))
            ->then(function ($_passable) use ($passable, $that, &$i) {
                $that->assertEquals(1, $i);
                $i = $i + 1;
                $that->assertEquals($passable, $_passable);
            });
        $this->assertEquals(2, $i);
    }

    public function testPipeWithTwoPassable() {
        $passable1 = 'test1';
        $passable2 = 'test2';
        $i = 0;
        $that = $this;
        PipeTest__MiddlewareTwoPassable::$run = function($_passable1, $_passable2) use ($passable1, $passable2, $that, &$i) {
            $i = $i + 1;
            $that->assertEquals($passable1, $_passable1);
            $that->assertEquals($passable2, $_passable2);
        };
        $this->assertEquals(0, $i);
        Pipe::create($passable1, $passable2)
            ->through(array('PipeTest__MiddlewareTwoPassable'))
            ->then(function ($_passable1, $_passable2) use ($passable1, $passable2, $that, &$i) {
                $that->assertEquals(1, $i);
                $i = $i + 1;
                $that->assertEquals($passable1, $_passable1);
                $that->assertEquals($passable2, $_passable2);
            });
        $this->assertEquals(2, $i);
    }

    public function testPipeWithTwoThrough() {
        $passable = 'test';
        $i = 0;
        $that = $this;
        PipeTest__Middleware::$run = function($_passable) use ($passable, $that, &$i) {
            $i = $i + 1;
            $that->assertEquals($passable, $_passable);
        };
        $this->assertEquals(0, $i);
        Pipe::create($passable)
            ->through(array('PipeTest__Middleware'))
            ->through(array('PipeTest__Middleware'))
            ->then(function ($_passable) use ($passable, $that, &$i) {
                $that->assertEquals(2, $i);
                $i = $i + 1;
                $that->assertEquals($passable, $_passable);
            });
        $this->assertEquals(3, $i);
    }
}

class PipeTest__Middleware {
    /** @var Closure */
    public static $run;


    public function __construct($arg1 = null) {
        $this->arg1 = $arg1;
    }

    public function __invoke($passable, Closure $next) {
        $run = self::$run;
        if ($this->arg1) {
            $run($passable, $this->arg1);
        } else {
            $run($passable);
        }
        return $next($passable);
    }
}

class PipeTest__MiddlewareTwoPassable {
    /** @var Closure */
    public static $run;


    public function __construct($arg1 = null) {
        $this->arg1 = $arg1;
    }

    public function __invoke($passable1, $passable2, Closure $next) {
        $run = self::$run;
        if ($this->arg1) {
            $run($passable1, $passable2, $this->arg1);
        } else {
            $run($passable1, $passable2);
        }
        return $next($passable1, $passable2);
    }
}