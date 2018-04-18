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
    private $roadmap = null;
    
    
	
	public function __construct($object)
	{
		global $conf,$langs;
		
		if(empty($object->db) || empty($object->id)) return false;
		
		$this->db = $object->db;
		$this->langs = $langs;
		
		$this->load();
		
	}
	
	
    /*
     * Dans un premier temps la sauvegarde va être basique
     */
	public function save()
	{
	    global $user;
	    $_SESSION['roadmap'][$object->element][$object->id] = array(
	        'curentRoadMapIndex' => $this->curentRoadMapIndex,
	        'Tcomposer' => $_SESSION['roadmap'][$object->element][$object->id]
	    );
	    return true;
	}
	
	public function load()
	{
	    if(!empty($_SESSION['roadmap'][$object->element][$object->id])){
	        $this->Tcomposer = $_SESSION['roadmap'][$object->element][$object->id]['Tcomposer'];
	        $index = $_SESSION['roadmap'][$object->element][$object->id]['curentRoadMapIndex'];
	        $this->setCurentRoadMap($index);
	        return true;
	    }
	    
	    return false;
	}

	
	public function delete()
	{
	    unset($_SESSION['roadmap'][$object->element][$object->id]);
	    return true;
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
	
	public function print_roadmapSelection($new = true)
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
	            $data[] = 'data-target-action="newroadmap"';
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
	    if(empty($id)){
	        print hStyle::callout($this->langs->trans('StepNotFound').' : '.$id, 'error');
	        return 0;
	    }
	    
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
	        print hStyle::callout($this->langs->trans('StepNotFound').' : '.$id);
	    }
	}
	
	public function print_nextstep($curentStepId)
	{
	    if(empty($curentStepId))
	    {
	        $stepId = $this->roadmap->getFirstStepId();
	        $this->print_step($stepId);
	    }
	    else
	    {
	        $curentStep = new PCRoadMapStep($this->db);
	        if($curentStep->fetch($curentStepId) > 0)
	        {
	            $this->print_step($curentStep->getNext());
	        }
	    }
	}
	
	
	
	public function addRoadmap($roadmapid,$setcurent=true)
	{
	    $roadMap = new PCRoadMap($this->db);
	    if($roadMap->fetch($roadmapid)>0)
	    {
	        
	        $T = array(
	            'roadmapid' => $roadmapid,
	            'steps'  =>array()
	        );
	        
	        if(!empty($this->Tcomposer)){
	            $this->Tcomposer[] = $T;
	        }
	        else
	        {
	            $this->Tcomposer[1] = $T; // pour eviter les index à 0
	        }
	        
	        $keys = array_keys($this->Tcomposer);
	        
	        $index = end($keys);
	        
	        $this->cache_PCRoadMap[$index][$roadMap->id] = $roadMap;
	        if($setcurent)
	        {
	            return $this->setCurentRoadMap($index);
	        }
	        else
	        {
	            return $this->curentRoadMapIndex;
	        }
	    }
	    
	    return -1;
	    
	}
	
	public function setCurentRoadMap($index,$cache=true){
	    
	    if(empty($index) && !isset($this->Tcomposer[$index])) return 0;
	    
	    // set curent roadmap
	    $this->curentRoadMapIndex = $index;
	    $this->TcurentComposer =& $this->Tcomposer[$this->curentRoadMapIndex];
	    
	    if($cache && !empty($this->cache_PCRoadMap[$this->curentRoadMapIndex][$this->TcurentComposer['roadmapid']]))
	    {
	        $this->roadmap = $this->cache_PCRoadMap[$this->curentRoadMapIndex][$this->TcurentComposer['roadmapid']];
	    }
	    else{
	        $this->roadmap = new PCRoadMap($this->db);
	        if($this->roadmap->fetch($this->TcurentComposer['roadmapid'])<1)
	        {
	           return -1;
	        }
	        
	    }
	    
	    return $this->curentRoadMapIndex;
	}
	
	
	
}


