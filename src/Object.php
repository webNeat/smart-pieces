<?php

namespace wn;

class Object extends \stdClass implements \Countable {

	public function __construct($list = [], $recursive = true){
		if(is_array($list) && count($list) > 0){
			$i = 1;
			foreach ($list as $name => $value) {
				$name = strtolower(trim($name));
				if( ! $name ){
					$name = 'field_' . $i;
					$i ++;
				}
				if($recursive && is_array($value)){
					$this->$name = new Object($value);
				} else {
					$this->$name = $value;
				}
			}
		}
	}

	public function count(){
		return count((array) $this);
	}
}