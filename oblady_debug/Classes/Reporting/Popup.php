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

class tx_obladydebug_Reporting_Popup extends tx_obladydebug_Reporting_Base{
    
    protected $errorList = array();
    
    public function report($error, $index, $showSource){
        
        $rendering = new tx_obladydebug_Rendering_HTML;
        
        $message = $this->classicRendering($error, $rendering);
            
        /*$format = '<div style="display: none;">%date% </div>'.
            '<span class="errorLevel">[%type%]</span>'.
            ' in %file% on line <span style="text-decoration: underline; padding-bottom: 1px; border-bottom: 1px solid black;">%line%</span>'.
            ' <div class="errorMessage">%message%</div>' . "\n".
            ($showSource ? '<div style="margin: 5px 5px 0 5px; font-family: sans-serif; font-size: 10px;">==> Source report from %file% around line %line% (%context_start%-%context_end%)</div><div style="margin: 0 5px 5px 5px; background-color: #EEEEEE; border: 1px dotted #B0B0B0;">%context_source%</div>' : '').
            '<div style="margin: 5px 5px 0 5px; font-family: sans-serif; font-size: 10px;">==> Variable scope report</div><div style="margin: 0px 5px 5px 5px;">%variables%</div>';
        */
        if ($error['context']['source'] && $showSource) {
            $message .= $rendering->context($error['context'], $error['file'], $error['line']);
        }
        
        $message .= $rendering->variables($error['variables'], $error['depth']);
        
        
        $this->errorList[$index + 1] = strtr($message, array("\t" => '\\t', "\n" => '\\n', "\r" => '\\r', '\\' => '&#092;', "'" => '&#39;'));
    }
    
    public function finish(){
        
        if(!empty($this->errorList)) {
            //Templating
            $errorsList = '';
            $templatesPath = t3lib_extMgm::extPath('oblady_debug').'Templates/';
            $containerTemplate = file_get_contents($templatesPath.'Container.html');
            $errorTemplate = file_get_contents($templatesPath.'Error.html');
            
            foreach ($this->errorList as $index => $error) {
                $replacements = array(
                    '###INDEX###' => $index,
                    '###ERROR###' => $error,
                );
                $errorsList .= str_replace(array_keys($replacements), $replacements, $errorTemplate);
            }
            
            $replacements = array(
                '###PHP_SELF###' => $_SERVER['PHP_SELF'],
                '###ERRORS###' => $errorsList,
                '###EXT_PATH###' => (preg_replace('#^'.$GLOBALS['_SERVER']["DOCUMENT_ROOT"].'#','',t3lib_extMgm::extPath('oblady_debug')))
            );
            
            echo str_replace(array_keys($replacements), $replacements, $containerTemplate);
        }
        
    }
    
}