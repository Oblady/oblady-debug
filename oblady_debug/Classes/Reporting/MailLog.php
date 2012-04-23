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

class tx_obladydebug_Reporting_MailLog extends tx_obladydebug_Reporting_WithDestination{
    
    public function report($error, $index, $showSource){
        
        $rendering = new tx_obladydebug_Rendering_PlainText;
        $message = $this->classicRendering($error, $rendering);
        
        $r = function_exists('error_log') && @error_log($message, MAIL_LOG, $this->destination);
        
        if ($r === false) {
            
            //Set server admin address
            if(!isset($_SERVER['SERVER_ADMIN'])) {
                
                if(!isset($_SERVER['SERVER_NAME'])) {
                    $_SERVER['SERVER_NAME'] = 'localhost';
                }
                
                $_SERVER['SERVER_ADMIN'] = get_current_user().'@'.$_SERVER['SERVER_NAME'];
            }
            
            //Set server admin as sender
            $headers = 'From: '.$_SERVER['SERVER_ADMIN']."\r\n" .
                'Reply-To: '.$_SERVER['SERVER_ADMIN']."\r\n" .
                'X-Mailer: PHP/' . phpversion();
                
            @mail($this->destination, 'PHP error_log message', $message, $headers);
        }
    }
    
    public function finish(){}
}