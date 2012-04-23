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

function trapError() {
	static $variable, $signatures = array();

	if (!isset($prependString) || !isset($appendString))	{
		$prependString = ini_get('error_prepend_string');
		$appendString = ini_get('error_append_string');
	}

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
		// I don't like 'Undefined ...' messages even if error level includes them
		if (preg_match('/^Undefined index:/', $args[1]) OR preg_match('/^Undefined offset:/', $args[1]) OR preg_match('/^Undefined variable:/', $args[1])) {
			return;
		}

		// weed out duplicate errors (coming from same line and file)
		$signature = md5($args[1] . ':' . $args[2] . ':' . $args[3]);
		if (isset($signatures[$signature]))	{
			return;
		} else {
			$signatures[$signature] = true;
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
			} elseif (is_a($variables[$variableName], 'tx_obladydebug_ErrorList') ||
					is_a($variables[$variableName], 'tx_obladydebug_ErrorReporter'))	{
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

		$GLOBALS[$variable]->add($error);
	} elseif (func_num_args() == 1)	{
			// if only one arg is passed it's the name of the reporter object
		$variable = func_get_arg(0);
	} else {
		return $variable;
	}
}
