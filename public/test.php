<?php
interface PowerInterface 
{
	public function activate(array $target);
}
class Superman
{
    protected $powers;

    public function __construct($powers)
    {
        $this->powers = $powers;
    }
	
	public function getPower($power)
	{
		return isset($this->powers[$power])? $this->powers[$power] : null;
	}
}

class xpower implements PowerInterface
{
	public function activate(array $target){
		var_dump($target);
	}
}


class Container
{
    protected $binds;

    protected $instances;

    public function bind($abstract, $concrete)
    {
        if ($concrete instanceof Closure) {
            $this->binds[$abstract] = $concrete;
        } else {
            $this->instances[$abstract] = $concrete;
        }
    }

    public function make($abstract, $powers = [])
    {
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }


        return call_user_func_array($this->binds[$abstract], array(
			'container' => $this, 
			'powers' => $powers
		));
    }
}

$container = new Container;
$container->bind('superman', function($container, $powers) {	
	$powersObj = array();
	foreach($powers as $power){
		$powersObj[$power] = $container->make($power);
	}
    return new Superman($powersObj);
});
$container->bind('xpower', function($container) {
    return new XPower;
});

$superman_1 = $container->make('superman', ['xpower']);

var_dump($superman_1->getPower('xpower'));
