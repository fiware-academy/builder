<!--  
   Copyright (c) 2011, 2014 Engineering Group and others.
   All rights reserved. This program and the accompanying materials
   are made available under the terms of the Eclipse Public License v1.0
   which accompanies this distribution, and is available at
   http://www.eclipse.org/legal/epl-v10.html
 
   Contributors:
       Engineering Group - Course Builder
 -->

<?php
class Audio {
	public $source, $name, $target, $decoder, $encoder, $command;
	private $type, $bitrate, $mode,

	$encoders = array(
			'wav'=>array('lame','%1$s --silent -h -m %3$s -b %4$d %2$s -'),
			//'mp3'=>array('lame','%1$s --silent --nores -h -m %3$s -b %4$d --mp3input %2$s -')
			'mp3'=>array('lame','%1$s --silent -h -m %3$s -b %4$d --resample 16 %2$s -')
	),
	$decoders = array(
			'wav'=>array('lame',''),
			'mp3'=>array('lame','')
	);

	public function __construct($source, $name, $bitrate = 64, $mode = 'j', $path = '/usr/bin/')
	{
		$this->type = strtolower(pathinfo($source, PATHINFO_EXTENSION));
		$this->name = $name;		
		$this->target = dirname($source);
		
		$this->bitrate = $bitrate;
		$this->mode = $mode;

		if(!is_file($source))
			throw new Exception($source . ' source not found');
		
		$this->source = $this->secure_path($source);

		if(array_key_exists($this->type, $this->decoders))
			$this->decoder = $this->decoders[$this->type];
		else
			throw new Exception('No available decoder for ' . $this->type);

		if(array_key_exists($this->type, $this->encoders))
			$this->encoder = $this->encoders[$this->type];
		else
			throw new Exception('No available encoder for ' . $this->type);

		if(is_string($path)) {
			$exe = preg_match('/^WIN/',PHP_OS) ? '.exe' : '';
			$decoder = $path . DIRECTORY_SEPARATOR . $this->decoder[0] . $exe;
			$encoder = $path . DIRECTORY_SEPARATOR . $this->encoder[0] . $exe;
		}elseif(is_array($path)) {
			if(array_key_exists($this->decoder[0], $path))
				$decoder = $path[$this->decoder[0]];
			if(array_key_exists($this->encoder[0], $path))
				$encoder = $path[$this->encoder[0]];
		}

		if(!file_exists($decoder))
			throw new Exception($decoder . ' decoder not found');
		else
			$this->decoder[0] = $this->secure_path($decoder);

		if(!file_exists($encoder))
			throw new Exception($encoder . ' encoder not found');
		else
			$this->encoder[0] = $this->secure_path($encoder);
	}


	public function save() {
		$output = array();
		$result = -1;
		
		$this->convert();
		$this->set_target();
		
		exec($this->command . ' > ' . $this->secure_path($this->target), $output, $result);
		return $result;
	}


	private function set_target() {
		$this->target .= DIRECTORY_SEPARATOR .
		$this->name . '.mp3';

		if(!is_writable(dirname($this->target)))
			throw new Exception($this->target . ' target not writable');
	}

	private function convert() {
		$this->command = sprintf($this->decoder[1], $this->decoder[0], $this->source) .
		sprintf($this->encoder[1], $this->encoder[0], $this->source, $this->mode, $this->bitrate);
	}

	private function secure_path($path)	{
		if(file_exists($path))
			$path = realpath($path);
		if(strpos($path, ' '))
			$path = '"' . $path . '"';
		return $path;
	}
}
?>