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
		
		$this->db =& $object->db;
		$this->dbTool = new PCDbTool($object->db);
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
	            return -1;
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
	
	public function inlineData($data,$prefixKey=true){
	    $ret = '';
	    if(!empty($data) && is_array($data))
	    {
	        $ret .= ' ';
	        foreach ($data as $key => $value)
	        {
	            $ret .= ($prefixKey?'data-':'').dol_htmlentities($key);
	            $ret .= '="'.dol_htmlentities($value).'" ';
	        }
	    }
	    return $ret;
	}
	
	public function print_step($id)
	{
	    if(empty($id)){
	        print hStyle::callout($this->langs->trans('StepNotFound').' : '.$id, 'error');
	        return 0;
	    }
	    
	    //exit();
	    // load step
	    $curentStep = new PCRoadMapStep($this->db);
	    $loadRes = $curentStep->fetch($id);
	    if($loadRes>0)
	    {
	        print '<div id="step-wrap-'.$curentStep->id.'" class="productcomposer-selector" >';
	        
	        print '<h2><span class="rank" >'.$curentStep->rank.'.</span> '.dol_htmlentities($curentStep->label).'</h2>';
	        
	        if($curentStep->type == $curentStep::TYPE_SELECT_CATEGORY)
	        {
	            //print 'TYPE_SELECT_CATEGORY';
	            
	            
	            if($elements = $curentStep->getCatList())
	            {
	                print '<div class="productcomposer-catproduct" style="border-color: '.$curentStep->categorie->color.';" >';
	                foreach ($elements as $catid)
	                {
	                    $categorie = new Categorie($this->db);
	                    $categorie->fetch($catid);
	                    
	                    $this->print_catForStep($curentStep,$categorie);
	                    
	                }
	                print '</div>';
	            }
	            else{
	                print hStyle::callout($this->langs->trans('Noproductcomposer'), 'error');
	            }
	        }
	        elseif($curentStep->type == $curentStep::TYPE_SELECT_PRODUCT)
	        {
	            if($products = $curentStep->getProductList())
	            {
	                
	                $this->print_searchFilter(".productcomposer-catproduct");
	                
	                print '<div class="productcomposer-catproduct" style="border-color: '.$curentStep->categorie->color.';" >';
	                foreach ($products as $productid)
	                {
	                    $product = new Product($this->db);
	                    $product->fetch($productid);
	                    
	                    $this->print_productForStep($curentStep,$product);
	                    
	                }
	                print '</div>';
	            }
	            else{
	                print hStyle::callout($this->langs->trans('Noproductcomposer'), 'error');
	            }
	        }
	        
	        print '</div>';
	    }
	    else{
	        print hStyle::callout($this->langs->trans('StepNotFound').' : '.$id);
	    }
	}
	
	
	public function print_productForStep($curentStep,$product,$wrapData = false)
	{
	    global $conf;
	   
	   $maxvisiblephotos = 1;
	   $width=150;
	   $photo = $product->show_photos($conf->product->multidir_output[$product->entity],'small',$maxvisiblephotos,0,0,0,$width,$width,1);
	  
	   $data=array();
	   $data['id'] = $product->id;
	   $data['element'] = $product->element;
	   $data['fk_step'] = $curentStep->id;
	   
	   
	   $nextStep = $curentStep->getNext();
	   if(!empty($nextStep))
	   {
	       $data['target-action'] = 'addproductandnextstep';
	       $data['fk_nextstep'] = $nextStep->id;
	       
	   }
	   
	   
	   
	   
	   
	   
	   if(!empty($wrapData) && is_array($wrapData))
	   {
	       $data = array_replace($data, $wrapData);
	   }
	   
	   $attr = !empty($data)?$this->inlineData($data):'';
	   
	   print '<div class="productcomposer-product-item searchitem" '.$attr.' >';
	   
	   print '<div class="productcomposer-product-item-photo" >';
	   print $photo;
	   print '</div>';
	   
	   print '<div class="productcomposer-product-item-info" >';
	   
	   print '<span class="label" >'.$product->label.'</span><br/>';
	   print '<span class="ref" >#'.$product->ref.'</span>';
	   print '</div>';
	   
	   print '</div>';
	}
	
	public function print_catForStep($curentStep,$cat,$wrapData = false)
	{
	    global $conf;
	    
	    $maxvisiblephotos = 1;
	    $width=150;
	    
	    $data=array();
	    $data['id'] = $cat->id;
	    $data['element'] = $cat->element;
	    $data['fk_step'] = $curentStep->id;
	    
	    
	    $nextStep = $curentStep->getNext();
	    if(!empty($nextStep))
	    {
	        $data['target-action'] = 'selectcatandnextstep';
	        $data['fk_nextstep'] = $nextStep->id;
	        
	    }
	    
	    if(!empty($wrapData) && is_array($wrapData))
	    {
	        $data = array_replace($data, $wrapData);
	    }
	    
	    $attr = !empty($data)?$this->inlineData($data):'';
	    
	    print '<div class="productcomposer-cat-item searchitem" '.$attr.' >';
	    
	    print '<div class="productcomposer-cat-item-photo" >';
	    //print $photo;
	    print '</div>';
	    
	    print '<div class="productcomposer-cat-item-info" >';
	    
	    print '<span class="label" >'.$cat->label.'</span>';
	    print '</div>';
	    
	    print '</div>';
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
	        $res = $curentStep->fetch($curentStepId);
	        if($res > 0)
	        {
	            $nextStep = $curentStep->getNext();
	            $this->print_step($nextStep->id);
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
	
	
	public function print_searchFilter($target = '#search-filter-target')
	{
        global $langs;
	    print '<div class="search-filter-wrap"  >';
	    
	    print '<input type="text" id="item-filter" class="search-filter" data-target="'.$target.'" value="" placeholder="'.$langs->trans('Search').'" ';
	    
	    print '<span id="filter-count-wrap" >'.$langs->trans('Result').': <span id="filter-count" ></span></span>';
	    
	    print '</div>';
	}
	
	
	
	public function addProduct($productid,$stepid,$qty=1)
	{
	    $curQty = 0;
	    if(!empty($TcurentComposer['steps'][$stepid][$productid])){
	        $curQty = $TcurentComposer['steps'][$stepid][$productid];
	    }
	    
	    $TcurentComposer['steps'][$stepid][$productid] =$curQty + $qty;
	        
	}
	
	
	
}


