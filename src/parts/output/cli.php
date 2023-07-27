<?php

namespace nx\parts\output;

use nx\helpers\output;

/**
 * @method mixed out()
 */
trait cli{
	public ?output $out = null;
	protected function render_cli(output $out, callable $callback = null): void{
		//todo exit code
		$r = $out();
		if(null !== $callback) $callback($r);
		else echo $r;
	}
	protected function nx_parts_output_cli(): ?\Generator{
		if(!$this->out) $this->out = new output();
		$this->out->setRender($this->render_cli(...));
		yield;
		$this->out = null;
	}
}