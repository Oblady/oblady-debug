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
require_once($extensionPath . 'Interfaces/Rendering.php');

class tx_obladydebug_Rendering_PlainText implements tx_obladydebug_Rendering{
    
    protected static $dateFormat = '';
    
    public static function setDateFormat($format){
        self::$dateFormat = (string) $format;
    }
    
    public static function date($timestamp){
        return date(self::$dateFormat, $timestamp);
    }
    
    public static function level($level){
        if ($level & E_ERROR_ALL) {
            $r = 'error';
        } else if ($level & E_WARNING_ALL) {
            $r = 'warning';
        } else if ($level & E_NOTICE_ALL) {
            $r = 'notice';
        } else if ($level & E_DEBUG) {
            $r = 'debug';
        } else if ($level & E_LOG) {
            $r = 'log';
        } else {
            $r = 'unknown';
        }
        return $r;
    }
    
    public static function file($file){
        return ' in '.$file;
    }
    
    public static function line($line){
        return ' on line '.$line;
    }
    
    public static function message($message){
        return $message."\n";
    }
    
    public static function context($context, $file, $line) {
        return '';
    }
    
    public static function variables($variables){
        $r = '';
        foreach($variables as $var) {
            $r .= var_export($var, true);
        }
        return $r;
    }   
}