<?php
/**
 * Created by PhpStorm.
 * User: Vea
 * Date: 2019/1/2 002
 * Time: 15:33
 */

namespace nx\parts;
trait o2{
	protected mixed $data = null;
	//IteratorAggregate
	/**
	 * @inheritDoc
	 */
	public function getIterator(): \ArrayIterator{ return new \ArrayIterator($this->data); } //foreach($this as ..)
	//Countable
	/**
	 * @inheritDoc
	 */
	public function count(): int{ return count($this->data ?? []); } //->count($this)
	//ArrayAccess
	/**
	 * @inheritDoc
	 */
	public function offsetSet($offset, $value): void{ $this->data[$offset] = $value; }   //$this['xx'] ='xx'
	/**
	 * @inheritDoc
	 */
	public function &offsetGet($offset): mixed{
		if(!isset($this->data[$offset])){
			$a =null;
			return $a;
		}
		if(is_callable($this->data[$offset]) || (is_object($this->data[$offset]) && method_exists($this->data[$offset], '__invoke'))) return $this->data[$offset]($this);
		return $this->data[$offset];
	}           //=$this['zz']
	/**
	 * @inheritDoc
	 */
	public function offsetExists($offset): bool{ return isset($this->data[$offset]); }       //isset($this['xx']
	/**
	 * @inheritDoc
	 */
	public function offsetUnset($offset): void{ unset($this->data[$offset]); }                //unset($this['xx']
	//php5.3
	public function __invoke(...$args){//php7
		switch(func_num_args()){
			case 0:// =$this()
				return $this->data;
			case 1:// $this($x)
				$this->data = $args[0];
				//return $this;
				break;
			default:// =$this($x, $y, $z , ...)
				$r = [];
				foreach($args as $arg){
					$r[$arg] = $this->data[$arg] ?? null;
				}
				return $r;
		}
	}
	public function __debugInfo():array{
		return $this->data;
	}
}