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

class tx_obladydebug_Reporting_Firebug extends tx_obladydebug_Reporting_Base{
    
    protected $errorList = array();
    
    public function report($error, $index, $showSource){
        foreach ($error['variables'] as $name => $var){
            $this->errorList[$index] = array('what'=> $var, 'why'=> $name, 'where' => 'in '.$error['file'].' on line '.$error['line']);
        }
    }
    
    public function finish(){
        
        $content = '';
        
        if (!empty($this->errorList)) {
            
            $debugs = '<script>if(typeof(console) != "undefined" && typeof(console.group) == "function") { ';
            
            foreach ($this->errorList as $out) {
                $debugs .=
                    'console.groupCollapsed(\''.addcslashes($out['why'], '\'').'\'+"\n"+\''.addcslashes($out['where'], '\'').'\'); '.
                    //'console.log(\''.str_replace("\n", '\'+"\n"+\'', addcslashes(str_replace('\\\'', '\'', $out['what']), '\'')).'\'); '.
                    'console.log('.json_encode($out['what']).'); '.
                    'console.groupEnd();';
            }
            
            $content = str_replace(chr(10), '', $debugs) . ' } </script>';
        }
        
        echo $content;
    }
}