<?php

namespace wn;

class ArgumentsParser {
	protected $data;
	/**
	 * format of an argument
	 * String format:
	 * [ type => 'string' ]
	 * Array format:
	 * [
	 * 	type => 'array',
	 * 	separator => ',' (default: ',')
	 * 	format => format, (format of every element)
	 * ]
	 * Object format:
	 * [
	 * 	type => 'object',
	 * 	separator => ':' (default: ':')
	 * 	fields => [
	 * 		'field1' => format, (format of the field)
	 * 		...
	 * 	],
	 * 	flags => ['flag1', ...]
	 * ]
	 * @var Array
	 */
	protected $format;

	public function __construct($format = null){
		$this->data = [];
		if(is_array($format))
			$this->format = new Object($format);
		else
			$this->format = $format;
	}

	public function parse($args){
		$this->data = [];
		foreach ($args as $arg) {
			$this->data[] = $this->parseElement($arg, $this->format);
		}
		return $this;
	}

	public function dump(){
		print_r($this->data);
	}

	public function get(){
		return $this->data;
	}

	protected function parseElement($element, $format){
		$result = $element;
		if(isset($format->type)){
			switch($format->type){
				case 'array':
					$separator = ',';
					if(isset($format->separator))
						$separator = $format->separator;
					$result = [];
					$elements = explode($separator, $element);
					foreach ($elements as $e){
						$result[] = (isset($format->format)) ? 
							$this->parseElement($e, $format->format) : 
							$this->parseElement($e);
					}
				break;
				case 'object':
					$separator = ':';
					if(isset($format->separator))
						$separator = $format->separator;
					$result = new Object;
					$elements = explode($separator, $element);
					$i = 0;
					$elementsCount = count($elements);
					$fieldsCount = count($format->fields);
					if($elementsCount < $fieldsCount)
						throw new \Exception('Cannot parse arguments, some fields are missing !');
					
					if(isset($format->flags)){
						foreach ($format->flags as $flag){
							if(in_array($flag, $elements))
								$result->$flag = true;
							else
								$result->$flag = false;
						}
					}

					foreach ($format->fields as $name => $fieldFormat){
						$result->$name = $this->parseElement($elements[$i], $fieldFormat);
						$i ++;
					}
				break;
			}			
		}
		return $result;
	}
}