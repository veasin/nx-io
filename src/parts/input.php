<?php

namespace nx\parts;
/**
 * @method mixed in()
 */
trait input{
	public ?\nx\helpers\input $in = null;
	public function nx_parts_input(): void{
		if(!$this->in) $this->in = new \nx\helpers\input($this);
	}
}