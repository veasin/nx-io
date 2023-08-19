<?php

namespace nx\parts\output;

use nx\helpers\http\status;
use nx\helpers\output;

/**
 * @method void runtime(string $info)
 * @method mixed out()
 */
trait http{
	use status;
	public ?output $out = null;
	protected function render_http(output $out, callable $callback = null): void{
		$r = $out();
		$status = $out->status ?? (null !== $r ? 200 : 404);//todo 需要区分是out内容还是额外的http status、message和header
		$message ="$status " . (!empty($out->message) ? $out->message : (static::$http_code_messages[$status] ?? ''));
		$this->runtime("status: $message");
		header(($_SERVER["SERVER_PROTOCOL"] ?? "HTTP/1.1") . ' ' . $message);//HTTP/1.1
		header_remove('X-Powered-By');
		$headers = $out->header() ?? [];
		$headers['NX'] = 'V 2005-2023';
		foreach($headers as $header => $value){
			if(is_int($header)){
				if(is_array($value)){
					foreach($value as $v){
						header($header . ': ' . $v);
					}
				}
				elseif(is_string($value) || $value instanceof \Stringable){
					header($value);//['Status: 200']
				}
			}
			else header($header . ': ' . $value);
		}
		if(null !== $callback) $callback($r);
		else echo $r;
	}
	protected function nx_parts_output_http(): ?\Generator{
		if(!$this->out) $this->out = new output();
		$this->out->setRender($this->render_http(...));
		yield;
		$this->out = null;
	}
}