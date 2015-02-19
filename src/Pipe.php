<?php
/**
 * @author Petr Grishin <petr.grishin@grishini.ru>
 */

namespace PetrGrishin\Pipe;


use Closure;
use ReflectionClass;

class Pipe {

    protected $pipes = array();
    protected $passable = array();

    public static function create($passable) {
        return new static(is_array($passable) ? $passable : func_get_args());
    }

    public function __construct($passable) {
        $this->passable = is_array($passable) ? $passable : func_get_args();
    }

    public function through($pipes) {
        $this->pipes = is_array($pipes) ? $pipes : func_get_args();
        return $this;
    }

    public function then(Closure $destination) {
        $firstSlice = $this->getInitialSlice($destination);
        $pipes = array_reverse($this->pipes);
        return call_user_func_array(
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
            return function () use ($stack, $pipe) {
                $passable = func_get_args();
                $passable[] = $stack;
                if ($pipe instanceof Closure) {
                    return call_user_func_array($pipe, $passable);
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
                return call_user_func_array($object, $passable);
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
            return call_user_func_array($destination, $passable);
        };
    }
}