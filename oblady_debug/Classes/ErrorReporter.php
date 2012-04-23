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


class tx_obladydebug_ErrorReporter {
    
	public $contextLines = 3;
	protected $contextLevel = 0;
	protected $strictContext = true;
    
	protected $dateFormat = '';
    
	protected $classExcludeList = array();
    protected $excludeObjects = true;
    
	protected $errorList = array();
	protected $reporting = array();
	
	protected $reportingSignatures = array();
	
	// __constructor
	public function __construct() {
		$this->contextLevel = (E_ERROR_ALL | E_WARNING_ALL);
	}

	public function setDateFormat($format) {
		$this->dateFormat = $format;
	}


	public function setContextLines($lines) {
		$this->contextLines = intval($lines);
	}


	public function setContextLevel($level) {
		$this->contextLevel = $level;
	}

	public function setStrictContext($boolean) {
		$this->strictContext = $boolean ? true : false;
	}


	public function setExcludeObjects($lvl) {
        $this->excludeObjects = (int) $lvl;
		/*} else {
			$list = func_get_args();
			$this->classExcludeList = array_map('strtolower', $list);
		}*/
	}
    
    public function setExcludedClasse($class, $lvl) {
        
    }

	public function addReporter($reporter, $level, $destination = null, $avoidSingleton = false) {
        $c = 'tx_obladydebug_Reporting_'. (string) $reporter;
        if (class_exists($c)) {
            if($c instanceof tx_obladydebug_Reporting_WithDestination) {
				if(!is_null($destination)){
					$sign = md5($c.$level.$destination);
					if($avoidSingleton || !in_array($sign, $this->reportingSignatures)){
						$this->reporting[] = new $c($level, $destination);
						$this->reportingSignatures[] = $sign;
					}
				} else {
					throw new Exception('Trying to add '.$c.' as reporter with no destination parameter.', 1330617626);
				}
            } else {
				$sign = md5($c.$level);
				if($avoidSingleton || !in_array($sign, $this->reportingSignatures)){
					$this->reporting[] = new $c($level);
					$this->reportingSignatures[] = $sign;
				}
            }
        } else {
			throw new Exception('"'.$c.'" is not loadable.', 1330617642);
        }
	}

	public function prepare(){}

	public function current($error, $index)	{
        
        foreach (array_keys($error['variables']) as $varName) {
            
            //Unset some context's variables if...
            $exclude = false;
            
            //... using the "strict context" option and variable is not in error's context
            if (($this->strictContext && !in_array($varName, $error['context']['variables']))) {
                $exclude = true;
            }
            
            //... it's an object and object are not exportable on this error's level
            if (!is_null($this->excludeObjects) && is_object($error['variables'][$varName]) && ($this->excludeObjects & $error['level'])) {
                $exclude = true;
            }
            
            //... it's an object and the class is excluded on this error's level
            /*
            foreach($this->exclusions as $def) {
                if (!isset($def['l']) || ($def['l'] & $error['level'])) {
                    
                }
            }
                || (
                    !empty()
                    && is_object($error['variables'][$varName])
                )
            ) {
                unset($error['variables'][$varName]);
            }*/
            if ($exclude) {
                unset($error['variables'][$varName]);
            }
        }
    
        // if this is an object and the class is in the exclude list, skip it
        if (!is_object($contents) || !in_array(get_class($contents), $this->classExcludeList)) {
            
            //Set good-looking date
            $error['formated_date'] = date($this->dateFormat, intval($error['timestamp']));
            
            //Report error in each reporting objects wich handle errors of that level
            foreach ($this->reporting as $r) {
                if ($r->level($error['level'])) {
                    
                    $showContext = ($error['level'] & $this->contextLevel);
                     
                    $r->report($error, $index, $showContext);
                }
            }
        }
        

	}

	public function between()	{
        
	}

	public function finish()	{
        foreach ($this->reporting as $r) {
            $r->finish();
        }
	}

	
}
