<?php

if (!class_exists('TObjetStd'))
{
	/**
	 * Needed if $form->showLinkedObjectBlock() is call
	 */
	define('INC_FROM_DOLIBARR', true);
	require_once dirname(__FILE__).'/../config.php';
}


class productcomposer 
{
	
    public $Tproduct = array();
	
	
	public function __construct($object)
	{
		global $conf,$langs;
		
		$this->db = $object->db;
		$this->langs = $langs;
		
	}

	public function save()
	{
		global $user;
		
		
	}
	
	public function load()
	{
		
	}

	
	public function delete()
	{
		
	}
	
	
	
	
}


