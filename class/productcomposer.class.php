<?php

if (!class_exists('TObjetStd'))
{
	/**
	 * Needed if $form->showLinkedObjectBlock() is call
	 */
	define('INC_FROM_DOLIBARR', true);
	require_once dirname(__FILE__).'/../config.php';
}


dol_include_once('/productcomposer/class/roadmap.class.php');
dol_include_once('/productcomposer/class/helper_style.class.php');

class productcomposer 
{
	
    public $Tproduct = array();
	
	
	public function __construct($object)
	{
		global $conf,$langs;
		
		if(empty($object->db)) return false;
		
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
	
	public static function loadbyelement($id,$objectName)
	{
	    global $db;
	    
	    if(class_exists($objectName) )
	    {
	        $object = new $objectName($db);
	        $res = $object->fetch($id);
	        if($res>0)
	        {
	            return new self($object);
	        }
	        else
	        {
	            return $res;
	        }
	    }
	    
	    return 0;
	}
	
	public function print_roadmapSelection()
	{
	    // load all roadmaps
	    $PCRoadMap = new PCRoadMap($this->db);
	    $TRoadmaps = $PCRoadMap->getAll();
	    if(!empty($TRoadmaps))
	    {
	        print '<div class="roadmap-selector" >';
	        foreach ($TRoadmaps as $roadmap)
	        {
	            print '<div class="roadmap-item" >'.dol_htmlentities($roadmap->label).'</div>';
	        }
	        print '</div>';
	    }
	    else{
	        print hStyle::callout($this->langs->trans('Noproductcomposer'));
	    }
	}
	
}


