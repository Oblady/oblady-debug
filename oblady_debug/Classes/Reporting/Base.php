<?php
/***************************************************************
*  Copyright notice
*
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
/** @todo find why the require in ext_autoload doesn't works sometimes */
$extensionPath = t3lib_extMgm::extPath('oblady_debug');
//Load interfaces
require_once($extensionPath . 'Interfaces/Reporting.php');

abstract class tx_obladydebug_Reporting_Base implements tx_obladydebug_Reporting{
    
    protected $level = null;
    
    public function __construct($level) {
        $this->level = (int) $level;
    }
    
    public function level($l) {
        $l = (int) $l;
        return (!is_null($this->level) && ($this->level & $l));
    }
    
    protected function classicRendering(array $error, tx_obladydebug_Rendering $rendering){
        return $rendering->date($error['timestamp']).
            $rendering->level($error['level']).
            $rendering->file($error['file']).
            $rendering->line($error['line']).
            $rendering->message($error['message']);
        /*@todo : wait fore PHP 5.3
         return $rendering::date($error['timestamp']).
            $rendering::level($error['level']).
            $rendering::file($error['file']).
            $rendering::line($error['line']).
            $rendering::message($error['message']);
        */
    }
    
    public function report($error, $index, $showSource){
        throw new Exception();
    }
    
    public function finish(){
        throw new Exception();
    }
    
}