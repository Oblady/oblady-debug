<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2003 René Fritz (r.fritz@colorcube.de)
*	Dan Allen, http://mojavelinux.com/
*	Luite van Zelst <luite@aegee.org>
*  (c) 2011 Adrien LUCAS (adrien@oblady.fr)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The Typo3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/*
 * This is the controller class.
 * It handle errors and at last use reporters on them
 *
 */

class tx_obladydebug_ErrorList {

    protected static $instance = null;
    
	protected $elementData = array();

    protected $signatures = array();
    protected $conf = array();
    
	/*
     * Object constructor
     *
     */
	protected function __construct($conf, $setErrorHandler = false) {
		
		// :NOTE: dallen 2003/01/31 it might be a good idea to keep this on
		// if the console is used since some cases don't stop E_ERROR
#		ini_set('display_errors', false);

        if (!isset($conf['prependString']) || !isset($conf['appendString'])) {
            $conf['prependString'] = ini_get('error_prepend_string');
            $conf['appendString'] = ini_get('error_append_string');
        }
        
        if(!isset($conf['reportsDateFormat'])) {
            $conf['reportsDateFormat'] = '[Y-m-d H:i:s]';
        }

        $reporter = new tx_obladydebug_ErrorReporter();
        $reporter->setDateFormat($conf['reportsDateFormat']);
        $reporter->setStrictContext(false);
        $reporter->setContextLevel(E_ALL_NOT_NOTICE & !E_DEBUG);
        $reporter->setExcludeObjects(true);
		$this->reporter = $reporter;
        
        $this->setConfiguration($conf);
	}
	
    public function getInstance($conf = null, $variableName = 'error', $setErrorHandler=false){
        
        if (is_null(self::$instance)) {
            $c = __CLASS__;
            self::$instance = new $c($conf, $variableName = 'error', $setErrorHandler=false);
        }
        
        return self::$instance;
    }
    
    public function getReporter(){
        return $this->reporter;
    }
    
    public function setConfiguration(array $conf){
		
        if (isset($conf['exclude_objects']) && !empty($conf['exclude_objects'])) {
            $this->reporter->setExcludeObjects(tx_obladydebug_Div::evalErrorLevel($conf['exclude_objects']));
        }
        
        if (isset($conf['excludedClasses']) && !empty($conf['excludedClasses'])) {
            foreach(explode(',', $conf['excludedClasses']) as $exclude) {
                list($class, $lvl) = explode(':', $exclude);
				$this->reporter->setExcludedClasse($class, tx_obladydebug_Div::evalErrorLevel($lvl));
            }
        }
		
        $this->conf = array_merge($this->conf, $conf);
		
        $this->reportingConfigSet('Stdout');
        $this->reportingConfigSet('Popup');
        $this->reportingConfigSet('Firebug');
        $this->reportingConfigSet('FirePHP');
        $this->reportingConfigSet('SystemeLog');
        $this->reportingConfigSet('FileLog');
        $this->reportingConfigSet('MailLog');
        $this->reportingConfigSet('ServerLog');
        $this->reportingConfigSet('Browser');
        $this->reportingConfigSet('Redirect');
    }

	
	/*
     * Object destructor, called to show catched errors
     *
     */
	public function __destruct() {
		error_reporting(E_ALL ^ E_NOTICE);
        $iterator = new tx_obladydebug_ErrorIterator($this);
        $iterator->walk($this->reporter);
	}

	function debugEnd()	{
#		$this->__destructor();
	}

	public function add($error)	{

		// rearrange for eval'd code or create function errors
		$error['line'] = intval($error['line']);
		if (preg_match(';^(.*?)\((\d+)\) : (.*?)$;', $error['file'], $matches))	{
			$error['message'] .= $error['line'] ? ' on line ' . $error['line'] : '';
			$error['message'] .= ' in ' . $matches[3];
			$error['file'] = $matches[1];
			$error['line'] = $matches[2];
		}
		if ($error['line']) {
			$error['context'] = $this->_getContext($error['file'], $error['line']);
		}
        
        if (!isset($error['timestamp'])) {
            $error['timestamp'] = microtime(true);
        }
        
		$this->elementData[] = $error;
	}

	public function get($index)	{
		return $this->elementData[$index];
	}

	public function set($index, $o)	{
		$item = $this->elementData[$index];
		$this->elementData[$index] = $o;
		return $item;
	}

	public function size()	{
		return count($this->elementData);
	}

	public function clear()	{
		$this->elementData = array();
	}

	public function remove($index)	{
		$item = $this->elementData[$index];
		unset($this->elementData[$index]);
		$this->elementData = array_values($this->elementData);
		return $item;
	}

	public function indexOf($o)	{
		$index = array_search($o, $this->elementData, true);
		if (is_int($index))		{
			return $index;
		}
		return -1;
	}

	

    public function debug($variable, $name='*variable*', $line='*line*', $file='*file*', $recursiveDepth=3, $debugLevel=E_DEBUG) {
        
        if($name == '*variable*'){
            $name = '';
        }
        
        if($name == '' || $line == '*line*' || $file == '*file*') {
            $backtrace = debug_backtrace(false);
            $line = $backtrace[1]['line'];
            $file = $backtrace[1]['file'];
            
            if(strlen($name)<20){
                $name .= ' ('.$backtrace[2]['class'].' :: '.$backtrace[2]['function'].')';
            }
        } else {
            $line = intval($line);
        }
        
		$error = array(
			'level'		=> intval($debugLevel),
			'message'	=> 'user variable debug',
			'file'		=> $file,
			'line'		=> $line,
			'variables' => array($name => $variable),
			'signature'	=> mt_rand(),
			'depth'	=> $recursiveDepth,
		);
		$this->add($error);
	}



	public function _getContext($file, $line)	{
		if ($line==0 OR !$this->reporter->contextLines OR !@is_readable($file)) {
		    return array(
		    	'start'		=> 0,
		    	'end'		=> 0,
		    	'source'	=> '',
		    	'variables'	=> array(),
		    );
        }

		$sourceLines = file($file);
		$offset = max($line - 1 - $this->reporter->contextLines, 0);
		$numLines = 2 * $this->reporter->contextLines + 1;
		$sourceLines = array_slice($sourceLines, $offset, $numLines);
		$numLines = count($sourceLines);
		// add line numbers
		foreach ($sourceLines as $index => $line)	{
			$sourceLines[$index] = ($offset + $index + 1)  . ': ' . $line;
		}

		$source = tx_obladydebug_Div::addPhpTags(join('', $sourceLines));
		preg_match_all(';\$([[:alnum:]]+);', $source, $matches);
		$variables = array_values(array_unique($matches[1]));
		return array(
			'start'		=> $offset + 1,
			'end'		=> $offset + $numLines,
			'source'	=> $source,
			'variables'	=> $variables,
		);
	}
    
    public function trapError() {
         
    
        // error event has been caught
        if (func_num_args() == 5)	{
    
            // return on silenced error (using @)
            if (error_reporting() == 0)	{
                return;
            }
    
            $args = func_get_args();
    
            // return on not fitting errors depending on the global error level
            if (error_reporting() & !$args[0])	{
                return;
            }
            
            // weed out duplicate errors (coming from same line and file)
            $signature = md5($args[1] . ':' . $args[2] . ':' . $args[3]);
            if (isset($this->signatures[$signature]))	{
                return;
            } else {
                $this->signatures[$signature] = true;
            }
    
            // cut out the fat from the variable context (we get back a lot of junk)
    #		$variables =& $args[4];
            $variables = $args[4];
            $variablesFiltered = array();
            $excludeObjects = $GLOBALS[$variable]->reporter->excludeObjects;
            foreach (array_keys($variables) as $variableName)	{
                // these are server variables most likely
                if ($variableName == strtoupper($variableName))	{
                    continue;
                } elseif ($variableName{0} == '_')	{
                    continue;
                } elseif ($variableName == 'argv' || $variableName == 'argc')	{
                    continue;
                } elseif ($excludeObjects && gettype($variables[$variableName]) == 'object')          {
                    continue;
    
                // don't allow instance of errorstack to come through
                } elseif (is_a($variables[$variableName], 'tx_ccdebug_ErrorList') ||
                        is_a($variables[$variableName], 'tx_ccdebug_ErrorReporter'))	{
                    continue;
                }
                
                // :WARNING: dallen 2003/01/31 This could lead to a memory leak,
                // maybe only copy up to a certain size
                // make a copy to preserver the state at time of error
                $variablesFiltered[$variableName] = $variables[$variableName];
            }
    
            $error = array(
                'level'		=> $args[0],
                'message'	=> $prependString . $args[1] . $appendString,
                'file'		=> $args[2],
                'line'		=> $args[3],
                'variables'	=> $variablesFiltered,
                'signature'	=> $signature,
            );
    
            $this->add($error);
        }
    }

	public function trapException($e){
		
		$error = array(
			'level'		=> E_ERROR,
			'message'	=> $e->getMessage(),
			'file'		=> $e->getFile(),
			'line'		=> $e->getLine(),
			'variables'	=> array()
		);
		
		$error['signature'] = md5($error['message']. ':' .$error['file']. ':' .$error['line']);
		$this->add($error);
	}
	
    public function reportingConfigSet($KEY){
        $key = strtolower($KEY);
        if(isset($this->conf['reporting_'.$key]) && $this->conf['reporting_'.$key] && isset($this->conf['errlevel_'.$key])){
            $lvl = tx_obladydebug_Div::evalErrorLevel($this->conf['errlevel_'.$key]);
            
            if($lvl !== false) {
                $this->reporter->addReporter($KEY, $lvl);
            }
        }
    }
}



