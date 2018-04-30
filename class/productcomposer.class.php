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
dol_include_once('/comm/propal/class/propal.class.php');
dol_include_once('/productcomposer/class/helper_style.class.php');

class productcomposer 
{
	
    public $Tcomposer = array();
    public $roadmap = null;
    
    public $curentRoadMapIndex = 0;
    
    public $TcurentComposer = null;
    
    

    
    
	
	public function __construct($object)
	{
		global $conf,$langs;
		
		if(empty($object->db) || empty($object->id)) return false;
		
		$this->db =& $object->db;
		$this->dbTool = new PCDbTool($object->db);
		$this->langs = $langs;
		$this->object = $object;
		
		if(!$this->load())
		{
		    //var_dump($_SESSION['roadmap'], $this->object->element,$this->object->id);
		    //print hStyle::callout($this->langs->trans('ErrorRoadMapNotLoaded'), 'error');
		}
		
	}
	
	
    /*
     * Dans un premier temps la sauvegarde va être basique
     */
	public function save()
	{
	    global $user;
	    
	    
	    $_SESSION['roadmap'][$this->object->element][$this->object->id] = array(
	        'curentRoadMapIndex' => $this->curentRoadMapIndex,
	        'Tcomposer' => $this->Tcomposer,
	    );
	    return true;
	}
	
	public function load()
	{
	    if(!empty($_SESSION['roadmap'][$this->object->element][$this->object->id])){
	        $this->Tcomposer   = $_SESSION['roadmap'][$this->object->element][$this->object->id]['Tcomposer'];
	        $index             = $_SESSION['roadmap'][$this->object->element][$this->object->id]['curentRoadMapIndex'];
	        $this->setCurentRoadMap($index);
	        return true;
	    }
	    
	    return false;
	}

	
	public function delete()
	{
	    unset($_SESSION['roadmap'][$this->object->element][$this->object->id]);
	    return true;
	}
	
	public function annuleCurent()
	{
	    unset( $this->Tcomposer[$this->curentRoadMapIndex] ); // remove curent
	    unset( $this->cache_PCRoadMap[$this->cache_PCRoadMap[$index]] ); // remove cache
	    $this->curentRoadMapIndex = 0;
	    
	    unset($this->roadmap);
	    
	}
	
	
	
	public static function loadbyelement($id,$objectName)
	{
	    global $db;
	    
	    if($objectName == 'commande') $objectName = 'Commande';
	    if($objectName == 'propal') $objectName = 'Propal';
	    
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
	
	
	
	public function print_step($id,$param=false)
	{
	    if(empty($id)){
	        print hStyle::callout($this->langs->trans('StepNotFound').' : '.$id, 'error');
	        return 0;
	    }
	    
	    //exit();
	    // load step
	    $curentStep = new PCRoadMapDet($this->db);
	    $loadRes = $curentStep->fetch($id);
	    if($loadRes>0)
	    {
	        
	        
	        
	        print '<div id="step-wrap-'.$curentStep->id.'" class="productcomposer-selector" >';
	     
	        $curentSelectedRoadMapLabel = '';
	        if(!empty($this->TcurentComposer['fk_categorie_selected']))
	        {
	            $categorie = new Categorie($this->db);
	            $categorie->fetch($this->TcurentComposer['fk_categorie_selected']);
	            $curentSelectedRoadMapLabel =  '('.$categorie->label.')';
	        }
	        
	        
	        print '<h2><span class="rank" >'.($curentStep->rank + 1).'.</span> '.dol_htmlentities($curentStep->label).' '.$curentSelectedRoadMapLabel.'</h2>';
	        
	        if($curentStep->type == $curentStep::TYPE_SELECT_CATEGORY)
	        {
	            
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
	            // Gestion des options
	            
	            if(empty($param['fk_categorie'])){
	                $param['fk_categorie'] = 0;
	            }
	            
	            $elements = $curentStep->getCatList($param['fk_categorie']);
	            
	            if(!empty($elements))
	            {
	                print '<div class="productcomposer-catproduct" style="border-color: '.$curentStep->categorie->color.';" >';
	                foreach ($elements as $catid)
	                {
	                    $categorie = new Categorie($this->db);
	                    $categorie->fetch($catid);
	                    
	                    $data['target-action'] = 'loadstep';
	                    $data['fk_nextstep'] = $nextStep->id;
	                    $data['fk_categorie'] = $catid;
	                    
	                    
	                    $this->print_catForStep($curentStep,$categorie,$data);
	                    
	                }
	                print '</div>';
	            }
	            else 
	            {
	                $products = $curentStep->getProductListInMultiCat(array($this->TcurentComposer['fk_categorie_selected'], $param['fk_categorie']) );
	                var_dump($products);
	                
	                if($products)
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
	            
	            
	            
	        }
	        elseif($curentStep->type == $curentStep::TYPE_GOTO)
	        {
	            // goto 
	            $goToData['target-action'] = 'loadstep';
	            $goToData['fk_step'] = $curentStep->fk_pcroadmapdet;
	            $gotoAttr = $this->inlineData($goToData);
	            
	            // next step
	            $nextStep = $curentStep->getNext();
	            if(!empty($nextStep))
	            {
	                $data['target-action'] = 'loadstep';
	                $data['fk_step'] = $nextStep->id;
	                $nextAttr = $this->inlineData($data);
	            }
	            
	            print '<table >';
	            print '    <td>';
	            print '        <tr style="text-align:right;padding:10px;">';
	            print '            <span class="butAction" '.$gotoAttr.' >'.$curentStep->getLabel($curentStep->fk_pcroadmapdet).'</span>';
	            print '        </tr>';
	            print '        <tr style="text-align:left;padding:10px;" >';
	            
	            if(!empty($nextStep))
	            {
	                print '<span class="butAction" '.$nextAttr.' >'.$curentStep->getLabel($nextStep->id).'</span>';
	            }
	            
	            print '        </tr>';
	            print '    </td>';
                print '</table>';
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
	   
	   print '<span class="label" >'.$product->label.'</span>';
	   print '<span class="ref" >#'.$product->ref.'</span>';
	   print '</div>';
	   
	   print '</div>';
	}
	
	public function print_cat($object,$wrapData = false)
	{
	    global $conf;
	    
	    $maxvisiblephotos = 1;
	    $maxWidth=$maxHeight=150;
	    
	    
	    $upload_dir = $conf->categorie->multidir_output[$object->entity];
	    $pdir = get_exdir($object->id,2,0,0,$object,'category') . $object->id ."/photos/";
	    $dir = $upload_dir.'/'.$pdir;
	    
	    $photo = '';
	    foreach ($object->liste_photos($dir) as $key => $obj)
	    {
	        $nbphoto++;
	      
	        // Si fichier vignette disponible, on l'utilise, sinon on utilise photo origine
	        if ($obj['photo_vignette'])
	        {
	            $filename=$obj['photo_vignette'];
	        }
	        else
	        {
	            $filename=$obj['photo'];
	        }
	        
	        // Nom affiche
	        $viewfilename=$obj['photo'];
	        
	        // Taille de l'image
	        $object->get_image_size($dir.$filename);
	        $imgWidth = ($object->imgWidth < $maxWidth) ? $object->imgWidth : $maxWidth;
	        $imgHeight = ($object->imgHeight < $maxHeight) ? $object->imgHeight : $maxHeight;
	        
	        $photo = '<img border="0" src="'.DOL_URL_ROOT.'/viewimage.php?modulepart=category&entity='.$object->entity.'&file='.urlencode($pdir.$filename).'">';
	        break;
	    }
	    
	    if(empty($photo)){
	        $photo = '<span class="no-img-placeholder"></span>';
	    }
	 
	    
	    $forced_color='categtextwhite';
	    
	    if(empty($object->color))
	    {
	        $object->color = "ebebeb";
	    }
	    
	    if ($object->color)
	    {
	        if (colorIsLight($cat->color)) $forced_color='categtextblack';
	    }
	    
	    $data=array();
	    $data['id'] = $object->id;
	    $data['element'] = $object->element;
	    
	   
	    
	    if(!empty($wrapData) && is_array($wrapData))
	    {
	        $data = array_replace($data, $wrapData);
	    }
	    
	    $attr = !empty($data)?$this->inlineData($data):'';
	    
	    print '<div class="productcomposer-cat-item searchitem" '.$attr.' >';
	    
	    print '<div class="productcomposer-cat-item-photo" >';
	    print $photo;
	    print '</div>';
	    
	    print '<div class="productcomposer-cat-item-info" style="background:'.(!empty($object->color)?'#':'').$object->color.'"  >';
	    
	    print '<span class="label '.$forced_color.'"  >'.$object->label.'</span>';
	    print '</div>';
	    
	    print '</div>';
	}
	
	public function print_catForStep($curentStep,$object,$wrapData = false)
	{
	    global $conf;
	    
	    
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
	    
	    return $this->print_cat($object,$data);
	}
	
	
	public function print_nextstep($curentStepId, $param = false)
	{
	    if(empty($curentStepId))
	    { 
	        $firstStepId = $this->roadmap->getFirstStepId();
	        if(empty($firstStepId))
	        {
	            print hStyle::callout($this->langs->trans('NoFirstStepFound'), 'error');
	            return;
	        }
	        
	        if(!empty($this->roadmap->fk_categorie))
	        {
	            
	            $roadmapCat = new Categorie($this->db);
	            if(!empty($param['fk_categorie'])){
	                $res = $roadmapCat->fetch($param['fk_categorie']);
	            }
	            else{
	                $res = $roadmapCat->fetch($this->roadmap->fk_categorie);
	            }
	            //var_dump($this->roadmap->fk_categorie,$roadmapCat->get_filles());
	            if($res>0 && $elements = $roadmapCat->get_filles())
	            {
	                print '<div class="productcomposer-catproduct" style="border-color: '.$roadmapCat->color.';" >';
	                foreach ($elements as $categorie)
	                {
	                    $data  = array(
	                        'target-action' => 'selectroadmapcategorie',
	                        'fk_step' => $curentStepId,
	                    );
	                    
	                    $this->print_cat($categorie, $data);
	                }
	                print '</div>';
	            }
	            else{
	                //print hStyle::callout($this->langs->trans('NoChildCat'), 'error');
	                
	                // if no child cat so $this->roadmap->fk_categorie is the selected cat (or param
	                $this->TcurentComposer['fk_categorie_selected'] = !empty($param['fk_categorie'])?$param['fk_categorie']:$this->roadmap->fk_categorie;
	                $this->print_step($firstStepId);
	            }
	            $this->save();
	        }
	        else{
	            print hStyle::callout($this->langs->trans('NoRoadmapCat'), 'error');
	        }
	    }
	    else
	    {
	        if(empty($this->TcurentComposer['fk_categorie_selected']))
	        {
	            print hStyle::callout($this->langs->trans('NoSelectedRoadmapCat'), 'error');
	        }
	        
	        $curentStep = new PCRoadMapDet($this->db);
	        $res = $curentStep->fetch($curentStepId);
	        if($res > 0)
	        {
	            $nextStep = $curentStep->getNext();
	            $this->print_step($nextStep->id,$param);
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
	    
	    // IMPORTANT : supression de la référence précédante
	    unset($this->TcurentComposer);
	    
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
	    if(!empty($this->TcurentComposer['steps'][$stepid][$productid])){
	        $curQty = $this->TcurentComposer['steps'][$stepid][$productid];
	    }
	    
	    $this->TcurentComposer['steps'][$stepid][$productid] =$curQty + $qty;
	        
	}
	
	
	
}


