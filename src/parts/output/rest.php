<?php
/**
 * Created by PhpStorm.
 * User: Vea
 * Date: 2019/02/25 025
 * Time: 11:22
 */

namespace nx\parts\output;

use nx\helpers\output;

/**
 * @method mixed out()
 * @method header($headers=[])
 */
trait rest{
	use http;

	protected function nx_parts_output_rest(): ?\Generator{
		$http = $this->nx_parts_output_http();
		$http->current();
		$this->out->setRenderCallback(function($r){
			if(!is_null($r)){
				header('Content-Type:application/json; charset=UTF-8');
				try{
					echo json_encode($r, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
				}catch(\JsonException){
					echo "Error Format Output.";
				}
			}
		});
		yield;
		$http->next();
	}
}