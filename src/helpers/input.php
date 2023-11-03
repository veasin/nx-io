<?php

namespace nx\helpers;

use nx\parts\o2;

/**
 * @method null|mixed header(string $key = null) 返回指定 header 或全部
 * @method null|mixed body(string $key = null) 返回指定 body 或全部
 * @method null|mixed query(string $key = null) 返回指定 query 或全部
 * @method null|mixed uri(string $key = null) 返回指定 uri 或全部
 * @method null|mixed cookie(string $key = null) 返回指定 cookie 或全部
 * @method string|bool method(string $method = null) 返回当前请求的method或验证method是否正确
 */
class input implements \ArrayAccess, \Countable, \IteratorAggregate{
	use o2;

	protected mixed $app;
	private array $bodyContentTypeParseMap = [];
	public function __construct(mixed $app = null){
		$this->app = $app;
		if(PHP_SAPI === 'cli'){
			$argv = ($_SERVER['argv'] ?? []) + [];
			array_shift($argv);
			$this->data['params'] = $argv;
		}
		else $this->data['params'] = [];
		$this->data['method'] = strtolower($_SERVER['REQUEST_METHOD'] ?? 'cli');
		$this->data['uri'] = $_SERVER['REQUEST_URI'] ?? implode(' ', $_SERVER['argv']);
	}
	public function __call(string $from, array $arguments){
		$count = count($arguments);
		if(0 === $count){
			return $this[$from];
		}
		elseif(1 === $count){
			$this->app?->runtime("    ->{$from}[{$arguments[0]}]", 'in');
			if('uri' === $from) return $this->data['params'][$arguments[0]];
			elseif('method' === $from) return $this->data[$from] === strtolower($arguments[0]);
			$data = $this[$from];
			return $data[$arguments[0]] ?? null;
		}
		else return null;//todo error ?
	}
	public function &offsetGet($offset): mixed{
		if(!isset($this->data[$offset])){
			switch($offset){
				case 'ip':
					if(!empty($_SERVER['HTTP_CLIENT_IP'])) $this->data['ip'] = $_SERVER['HTTP_CLIENT_IP'];
					elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) $this->data['ip'] = $_SERVER['HTTP_X_FORWARDED_FOR'];
					else $this->data['ip'] = $_SERVER['REMOTE_ADDR'];
					break;
				case 'query':
					$this->data['query'] = $_GET;
					break;
				case 'post':
					$this->data['post'] = $_POST;
					break;
				case 'cookie':
					$this->data['cookie'] = $_COOKIE;
					break;
				case 'file':
					$this->data['file'] = $_FILES;
					break;
				case 'header':
					if(!function_exists('getallheaders')){
						$this->data['header'] = [];
						foreach($_SERVER as $name => $value){
							if(str_starts_with($name, 'HTTP_')) $this->data['header'][str_replace(' ', '-', strtolower(str_replace('_', ' ', substr($name, 5))))] = $value;
						}
					}
					else{
						foreach(getallheaders() as $key => $value){
							$this->data['header'][strtolower($key)] = $value;
						}
					}
					break;
				case 'input':
					$this->data['input'] = file_get_contents('php://input');
					break;
				case 'body':
					$content_type = $this->header('content-type');
					if($content_type){
						$content_type = strtolower(trim(str_contains($content_type, ';') ? explode(';', $content_type)[0] : $content_type));
						if(array_key_exists($content_type, $this->bodyContentTypeParseMap) && is_callable($this->bodyContentTypeParseMap[$content_type])){
							$this->data['body'] = call_user_func($this->bodyContentTypeParseMap[$content_type], $this['input']);
						}
						else{
							switch($content_type){//触发header更新
								case 'multipart/form-data':
									$this->data['body'] = $_POST;
									break;
								case 'application/x-www-form-urlencoded':
									parse_str($this['input'], $vars);
									$this->data['body'] = $vars;
									break;
								case 'application/json':
									try{
										$this->data['body'] = json_decode($this['input'], true, 512, JSON_THROW_ON_ERROR);
									}catch(\JsonException){
										$this->data['body'] = [];
									}
									break;
								case 'text/plain':
								case 'text/html':
								default:
									$this->data['body'] = $this['input'];
									break;
							}
						}
					}
					else $this->data['body'] = null;
					break;
				default:
					$this->data[$offset] = null;
			}
		}
		return $this->data[$offset];
	}
	public function file($arg): ?array{
		$f = $this->data['file'][$arg];
		return (isset($f['name'], $f['type'], $f['size'], $f['tmp_name'], $f['error']) && ($f['error'] === UPLOAD_ERR_OK) && is_file($f['tmp_name']) && is_uploaded_file($f['tmp_name'])
			&& is_readable($f['tmp_name'])) ? $f : null;
	}
	public function registerContentTypeParse(string $contentType, callable $callable): void{
		$this->bodyContentTypeParseMap[$contentType] = $callable;
	}
}