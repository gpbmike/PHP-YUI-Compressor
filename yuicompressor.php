<?php

class YUICompressor
{
	const DEFAULT_JAR_PATH = 'yuicompressor.jar';
 	const DEFAULT_TMP_DIR = '/tmp/yuicompressor';

    private $JAR_PATH;
    private $TEMP_FILES_DIR;
    private $options = array('type'		 	=> 'js',
                             'linebreak' 	=> false,
                             'verbose'	 	=> false,
                             'nomunge'		=> false,
                             'semi'			=> false,
                             'nooptimize'	=> false);
    private $files = array();
    private $string = '';
    
    // construct with a path to the YUI jar and a path to a place to put temporary files
    function __construct($JAR_PATH = null, $TEMP_FILES_DIR = null, $options = array())
	{
		$this->JAR_PATH = realpath ( $JAR_PATH ?: self::DEFAULT_JAR_PATH );
        $this->TEMP_FILES_DIR = realpath ( $TEMP_FILES_DIR ?: self::DEFAULT_TMP_DIR );
		
		if ( ! file_exists ( $this->JAR_PATH) )
			throw new Exception ( "Cannot find the JAR file: " . $this->JAR_PATH );
		if ( ! file_exists ( $this->TEMP_FILES_DIR) )
			throw new Exception ( "Cannot find the specified directory: " . $this->TEMP_FILES_DIR);	

        foreach ($options as $option => $value)
        {
            $this->setOption($option, $value);
        }
    }
    
	/**
	 * Sets an option to the YUICompressor
	 * @see http://yui.github.com/yuicompressor
	 */
    public function setOption ($option, $value)
    {
        $this->options[$option] = $value;
    }

	/**
	 * Adds a file which should be compressed.
	 * Path may be relative or absolute
	 */
    public function addFile ($file)
	{
		$file = realpath ($file);

		if ( ! file_exists ($file))
			throw new Exception ( "Cannot locate file: " . $file);

		array_push($this->files, $file);

		$this->string .= file_get_contents ($file);
    }
    
	/**
	 * Used to pass a string instead of a file,
	 * i.e. with file_get_contents()
	 */
    public function addString ($string)
    {
        $this->string .= ' ' . $string;
    }
    
	/**
	 * Compresses the given files and returns the compressed
	 * data as a string.
	 */
    public function compress()
    {
		if ( strlen ( $this->string) == 0 )
			throw new Exception ("Invalid State: Cannot compress when no data is given.");
    	
        // create single file from all input
        $input_hash = sha1($this->string);
        $file = $this->TEMP_FILES_DIR . DIRECTORY_SEPARATOR . $input_hash . '.txt';
		$fh = fopen($file, 'w');

		if ( ! $fh )
			throw new Exception ( "Cannot write data to temp file" );

        fwrite($fh, $this->string);
        fclose($fh);
    	
    	// start with basic command
        $cmd = "java -Xmx32m -jar " . escapeshellarg($this->JAR_PATH) . ' ' . escapeshellarg($file) . " --charset UTF-8";
    
        // set the file type
    	$cmd .= " --type " . (strtolower($this->options['type']) == "css" ? "css" : "js");
    	
    	// and add options as needed
    	if ($this->options['linebreak'] && intval($this->options['linebreak']) > 0)
            $cmd .= ' --line-break ' . intval($this->options['linebreak']);

    	if ($this->options['verbose'])
    	   $cmd .= " -v";
            
		if ($this->options['nomunge'])
			$cmd .= ' --nomunge';
		
		if ($this->options['semi'])
			$cmd .= ' --preserve-semi';
		
		if ($this->options['nooptimize'])
			$cmd .= ' --disable-optimizations';
    
    	exec($cmd . ' 2>&1', $raw_output);
    	unlink($file);
    	
    	return $raw_output;
    }
}
