<?php

namespace nx\helpers;

use nx\parts\o2;

class output implements \ArrayAccess, \Countable, \IteratorAggregate{
	use o2;

	private mixed $_render = null;
	private mixed $_render_callback = null;
	private bool $_has_render = false;
	public ?int $status;
	public ?string $message;
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
		$r = '';
		if($this->_render){
			ob_start();
			call_user_func($this->_render, $this, $this->_render_callback);
			$r = ob_get_clean();
		}
		return $r;
	}
	public function __toString(): string{
		return $this->render();
	}
	public function __destruct(){
		if(!$this->_has_render && $this->_render) call_user_func($this->_render, $this, $this->_render_callback);
	}
}