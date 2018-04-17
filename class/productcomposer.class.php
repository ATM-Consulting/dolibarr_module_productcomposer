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
dol_include_once('/commande/class/commande.class.php');
dol_include_once('/productcomposer/class/helper_style.class.php');

class productcomposer 
{
	
    public $Tcomposer = array();
    
    private $curentRoadMapIndex = 0;
    
    private $TcurentComposer = null;
    
    
	
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
	    
	    if($objectName == 'commande') $objectName = 'Commande';
	    
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
	    }else{ print 'no class '.$objectName;}
	    
	    return 0;
	}
	
	public function print_roadmapSelection()
	{
	    // load all roadmaps
	    $PCRoadMap = new PCRoadMap($this->db);
	    $TRoadmaps = $PCRoadMap->getAll();
	    if(!empty($TRoadmaps))
	    {
	        print '<div id="roadmap-selector" class="roadmap-selector productcomposer-selector" >';
	        foreach ($TRoadmaps as $roadmap)
	        {
	            $data = array();
	            $data[] = 'data-fk_pcroadmap="'.$roadmap->id.'"';
	            $data[] = 'data-target-action="loadnextstep"';
	            $data[] = 'data-fk_step="0"';
	            
	            print '<div  class="roadmap-item productcomposer-item" '.implode(' ', $data).' >'.dol_htmlentities($roadmap->label).'</div>';
	        }
	        print '</div>';
	    }
	    else{
	        print hStyle::callout($this->langs->trans('Noproductcomposer'));
	    }
	}
	
	public function print_step($id)
	{
	    
	    // load all roadmaps
	    $curentStep = new PCRoadMapStep($this->db);
	    $loadRes = $curentStep->fetch($id);
	    if($loadRes>0)
	    {
	        print '<div id="step-wrap-'.$curentStep->id.'" class="productcomposer-selector" >';
	        
	        print '<h4>'.dol_htmlentities($curentStep->label).'</h4>';
	        
	        print '</div>';
	    }
	    else{
	        print hStyle::callout($this->langs->trans('Noproductcomposer'));
	    }
	}
	
	public function print_nextstep($curentStepId)
	{
	    $curentStep = new PCRoadMapStep($this->db);
	    
	    if($curentStep->fetch($curentStepId) > 0)
	    {
	        $this->print_step($curentStep->getNext());
	    }
	}
	
	public function loadCurentRoadMap($roadmapid=0, $force=0){
	    
	    // loading curent roadmap
	    if(!empty($this->curentRoadMapIndex) && !$force)
	    {
	        $this->TcurentComposer =& $this->TcurentComposer[$this->curentRoadMapIndex];
	    }
	    /*elseif(!empty($roadmapid))
	    {
	        $this->TcurentComposer =& $this->TcurentComposer[$roadmapid];
	    }*/
	    
	}
	
}


