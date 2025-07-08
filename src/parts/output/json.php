<?php
/**
 * Created by PhpStorm.
 * User: Vea
 * Date: 2019/02/25 025
 * Time: 11:22
 */

namespace nx\parts\output;

/**
 * @method mixed out()
 * @method header($headers=[])
 */
trait json{
	use http;

	protected function nx_parts_output_json(): ?\Generator{
		$http = $this->nx_parts_output_http();
		$http->current();
		$this->out->setRenderCallback(function($r){
			if(null !==$r){
				header('Content-Type: application/json; charset=UTF-8');
				try{
					echo json_encode($r, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
				}catch(\JsonException){
					header("HTTP/1.1 500 Error Output Format.");
				}
			}
		});
		yield;
		$http->next();
	}
}