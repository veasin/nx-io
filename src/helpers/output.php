<?php

namespace nx\helpers;

use nx\parts\o2;

/**
 * @method header(string[] $headers = null)
 */
class output implements \ArrayAccess, \Countable, \IteratorAggregate{
	use o2;

	private mixed $_render = null;
	private mixed $_render_callback = null;
	private bool $_has_render = false;
	protected mixed $app;
	public ?int $status;
	public ?string $message;
	public function __construct($app = null){
		$this->app = $app;
	}
	public function __call(string $name, array $arguments){
		$count = count($arguments);
		if(0 === $count) return $this->app["output:$name"];
		elseif(1 === $count) $this->app["output:$name"] = $arguments[0];
	}
	public function status(int $status, string $message = null): void{
		$this->status = $status;
		$this->message = $message;
	}
	public function setRender(callable $render, callable $callback = null): void{
		$this->_render = $render;
		$this->_render_callback = $callback;
	}
	public function setRenderCallback(callable $callback = null): void{
		$this->_render_callback = $callback;
	}
	public function render(): string{
		$this->_has_render = true;
		if(null === $this->_render) return '';
		ob_start();
		call_user_func($this->_render, $this, $this->_render_callback);
		return ob_get_clean();
	}
	public function __toString(): string{
		return $this->render();
	}
	public function __destruct(){
		if(!$this->_has_render && $this->_render) call_user_func($this->_render, $this, $this->_render_callback);
	}
}