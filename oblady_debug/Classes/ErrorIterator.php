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

class tx_obladydebug_ErrorIterator {

	protected $errorList;
	protected $index;

	
	// __constructor()
	public function __construct(tx_obladydebug_ErrorList $errorList)	{
		$this->errorList = $errorList;
		$this->reset();
	}

	public function reset()	{
		$this->index = 0;
	}

	public function next()	{
		$this->index++;
	}

	public function isValid()	{
		return ($this->index < $this->errorList->size());
	}

	public function getCurrent()	{
		return $this->errorList->get($this->index);
	}
    
    public function walk(tx_obladydebug_ErrorReporter $manipulator) {
        
		$this->reset();
		if ($this->errorList->size() > 0)	{
			$manipulator->prepare();
            
            while ($this->isValid()) {
                $current = $this->getCurrent();
                
                if ($this->index)	{
                    $manipulator->between($this->index);
                }
                $manipulator->current($current, $this->index);
                $this->next();
            }
            
			$manipulator->finish($index);
		}
        
		return $this->index;
	}
}