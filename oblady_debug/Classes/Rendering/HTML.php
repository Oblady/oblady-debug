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

class tx_obladydebug_Rendering_HTML extends tx_obladydebug_Rendering_PlainText{
    
    public static function date($timestamp) {
        return sprintf('<div style="display: none;">%s </div>', parent::date($timestamp));
    }
    
    public static function level($level) {
        return sprintf('<span class="errorLevel">[%s]</span>', parent::level($level));
    }
    
    public static function file($file) {
        return parent::file($file);
    }
    
    public static function line($line) {
        return sprintf(' on line <span style="text-decoration: underline; padding-bottom: 1px; border-bottom: 1px solid black;">%d</span>', $line);
    }
    
    public static function message($message) {//div class="errorMessage">%message%</div>
        return sprintf('<div class="errorMessage">%s</div>', parent::message($message));
    }
    
    public static function context($context, $file, $line) {
        return sprintf('<div style="margin: 5px 5px 0 5px; font-family: sans-serif; font-size: 10px;">==> Source report from %s around line %d (%s-%s)</div><div style="margin: 0 5px 5px 5px; background-color: #EEEEEE; border: 1px dotted #B0B0B0;">%s</div>',
            $file,
            $line,
            $context['start'],
            $context['end'],
            str_replace('  ', '&nbsp; ', str_replace('&nbsp;', ' ', tx_obladydebug_Div::removePhpTags(highlight_string($context['source'], true))))
        );
    }
    
    public static function variables($variables, $depth = 3) {
        $r = '';
        foreach($variables as $name => $var) {
            $r .= self::debugvar($var, $name, $depth);
        }
        
        return sprintf('<div style="margin: 5px 5px 0 5px; font-family: sans-serif; font-size: 10px;">==> Variable scope report</div><div style="margin: 0px 5px 5px 5px;">%s</div>', $r);
    }
    
    protected static function debugvar($var, $name = '', $level = 3, $recursive = false) {
                $style= array();
		$style[0] = 'font-size:10px;font-family:verdana,arial;border-collapse:collapse;background:#E7EEEE;';
		$style[1] = 'border-width:1px;border-style:dotted; border-color:#A0AEB0;border-right-style:dotted;';
		$style[2] = 'border-width:1px;border-style:dotted; border-color:#A0AEB0;border-right-style:dotted;border-left-style:dotted;';
		$style[3] = 'border-width:1px;border-style:dotted; border-color:#A0AEB0;border-left-style:dotted;';
                  $line = '';
		if (@is_null($var)) {
			$type = 'Mixed';
			$var = 'NULL';
			$style[3] .= 'color:red;font-style:italic;';
		} else if(@is_array($var)) {
			$type = 'Array';
			$len = '&nbsp;('. sizeof($var) .')';
			if($level > -1) {
				$multiple = true;
                                foreach ($var as $key=>$val) {
					$line .= self::debugvar($val, $key, $level - 1, true);
				} 
				$var = sprintf("<table style=\"%s\">\n%s\n</table >\n",
					$style[0],
					$line
				);
			} else {
				$var = 'Array not debugged. Set higher "level" if you want to debug this.';
				$style[3] .= 'color:red;font-style:italic;';
			}
			$style[1] .= 'color:grey;font-face:bold;';
			$style[2] .= 'color:grey;font-face:bold;';
			$style[3].= 'padding:0px;';
		} else if(@is_object($var)) {
			$type = @get_class($var);// . '&nbsp;(extends&nbsp;' . @get_parent_class($var) . ')&nbsp;';
			$style[1] .= 'color:purple;';
			$style[3] .= 'color:purple;';
			if($level > -1) {
				$multiple = true;
				$vars = (array) @get_object_vars($var);
				while(list($key, $val) = each($vars)) {
					$line .= self::debugvar($val, $key, $level -1, true);
				}
				$methods = (array) @get_class_methods($var);
				while(list($key, $val) = each($methods)) {
					$line .= sprintf("<tr ><td style=\"%s\">Method</td ><td colspan=\"2\" style=\"%s\">%s</td ></tr >",
						$style[1],
						$style[3],
						$val . '&nbsp;(&nbsp;)'
					);
				}
				$var = sprintf("<table style=\"%s\">\n%s\n</table >\n",
					$style[0],
					$line
				);
				$len = '&nbsp;('. sizeof($vars) . '&nbsp;+&nbsp;' . sizeof($methods) .')';
			} else {
				$var = 'Object not debugged. Set higher "level" if you want to debug this.';
				$style[3] .= 'color:red;font-style:italic;';
			}
			$style[3].= 'padding:0px;';
		} else if(@is_bool($var)) {
			$type = 'Boolean';
			$style[1] .= 'color:#906;';
			$style[2] .= 'color:#906;';
			if(!$var) $style[3] .= 'color:red;';
			if($var == 0) $var = 'FALSE';
		} else if(@is_float($var)) {
			$type = 'Float';
			$style[1] .= 'color:#066;';
			$style[2] .= 'color:#066;';
		} else if(@is_int($var)) {
			$type = 'Integer';
			$style[1] .= 'color:green;';
			$style[2] .= 'color:green;';
		} else if(@is_string($var)) {
			$type = 'String';
			$style[1] .= 'color:darkblue;';
			$style[2] .= 'color:darkblue;';
			$var = nl2br(@htmlspecialchars($var));
			$len = '&nbsp;('.strlen($var).')';
			if($var == '') $var = '&nbsp;';
		} else {
			$type = 'Unknown!';
			$style[1] .= 'color:red;';
			$style[2] .= 'color:red;';
			$var = @htmlspecialchars($var);
		}
		if(! $recursive) {
			if($name == '') {
				$name = '(no name given)';
				$style[2] .= 'font-style:italic;';
			}
			$style[2] .= 'color:red;';

			if($multiple) {
				$html = "<table cellpadding=1 style=\"%s\">\n<tr >\n<td width=\"0\" style=\"%s\">%s</td ></tr ><tr >\n<td style=\"%s\">%s</td>\n</tr >\n<tr >\n <td colspan=\"2\" style=\"%s\">%s</td>\n</tr >\n</table >\n";
			} else {
				$html = "<table cellpadding=1 style=\"%s\">\n<tr >\n<td style=\"%s\">%s</td>\n<td style=\"%s\">%s</td ><td style=\"%s\">%s</td >\n</tr >\n</table>\n";
			}
			return sprintf($html, $style[0],
				$style[1], $type . $len,
				$style[2], $name, 
				$style[3], $var
			);
		} else {
			return 	sprintf("<tr >\n<td style=\"%s\">\n%s\n</td >\n<td style=\"%s\">%s</td >\n<td style=\"%s\">\n%s\n</td ></tr >",
						$style[1],
						$type . $len,
						$style[2],
						$name,
						$style[3],
						$var
					);
		}
	}
}