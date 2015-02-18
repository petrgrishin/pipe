<?php
/**
 * @author Petr Grishin <petr.grishin@grishini.ru>
 */

namespace PetrGrishin\Pipe;


use Closure;
use ReflectionClass;

class Pipe {

    protected $pipes = array();
    protected $passable;

    public static function create($passable) {
        return new static($passable);
    }

    public function __construct($passable){
        $this->passable = $passable;
    }

    public function through($pipes) {
        $this->pipes = is_array($pipes) ? $pipes : func_get_args();
        return $this;
    }

    public function then(Closure $destination) {
        $firstSlice = $this->getInitialSlice($destination);
        $pipes = array_reverse($this->pipes);
        return call_user_func(
            array_reduce($pipes, $this->getSlice(), $firstSlice), $this->passable
        );
    }

    /**
     * Get a Closure that represents a slice of the application onion.
     *
     * @return Closure
     */
    protected function getSlice() {
        return function ($stack, $pipe) {
            return function ($passable) use ($stack, $pipe) {
                if ($pipe instanceof Closure) {
                    return call_user_func($pipe, $passable, $stack);
                }
                if (is_array($pipe)) {
                    $class = array_shift($pipe);
                    /** @var Closure $object */
                    $reflectionClass = new ReflectionClass($class);
                    $object = $reflectionClass->newInstanceArgs($pipe);
                } else {
                    /** @var Closure $object */
                    $object = new $pipe();
                }
                return $object($passable, $stack);
            };
        };
    }

    /**
     * Get the initial slice to begin the stack call.
     *
     * @param  Closure  $destination
     * @return Closure
     */
    protected function getInitialSlice(Closure $destination) {
        $passable = $this->passable;
        return function () use ($destination, $passable) {
            return call_user_func($destination, $passable);
        };
    }
}