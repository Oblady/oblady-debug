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

class tx_obladydebug_Div {

	public static function strrpos2($string, $needle, $offset = 0){
		$addLen = strlen($needle);
		$endPos = $offset - $addLen;

		while (1)
		{
			if (($newPos = strpos($string, $needle, $endPos + $addLen)) === false) {
				break;
			}
			$endPos = $newPos;
		}
		return ($endPos >= 0) ? $endPos : false;
	}

	public static function addPhpTags($source){
		$startTag  = '<'.'?php';
		$endTag = '?'.'>';

		$firstStartPos  = ($pos = strpos($source, $startTag)) !== false ? $pos : -1;
		$firstEndPos = ($pos = strpos($source, $endTag)) !== false ? $pos : -1;

		// no tags found then it must be solid php since html can't throw a php error
		if ($firstStartPos === false && $firstEndPos === false) {
			return $startTag . "\n" . $source . "\n" . $endTag;
		}

		// found an end tag first, so we are missing a start tag
		if ($firstEndPos  !== false && ($firstStartPos === false || $firstStartPos > $firstEndPos)) {
			$source = $startTag . "\n" . $source;
		}

		$sourceLength = strlen($source);
		$lastStartPos  = ($pos = tx_obladydebug_Div::strrpos2($source, $startTag)) !== false ? $pos : $sourceLength + 1;
		$lastEndPos  = ($pos = tx_obladydebug_Div::strrpos2($source, $endTag)) !== false ? $pos : $sourceLength + 1;

		if ($lastEndPos < $lastStartPos || ($lastEndPos > $lastStartPos && $lastEndPos > $sourceLength)) {
			$source .= $endTag;
		}

		return $source;
	}

	public static function removePhpTags ($source) {
		return preg_replace(':(&lt;\?php(<br />)*|\?&gt;):', '', $source);
	}
    
    public static function evalErrorLevel($expr){
        $r = false;
	$expr = rtrim($expr, ';');
        if(preg_match('#^[a-zA-Z0-9_&|~ ]+$#', $expr)) {
            eval('$r = intval('.$expr.');');
        } else {
            trigger_error('"'.$expr.'" contains illegal characters.');
        }
        return $r;
    }

}
