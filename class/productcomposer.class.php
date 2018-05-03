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
	        print '<div id="roadmap-selector" class="roadmap-selector" >';
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
	
	
	
	public function print_step($id,$param=false, $disableBackBtn=false)
	{
	    global $langs;
	    
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
	        
	        
	        // Add history navigation
	        $prev = $curentStep->getPrevious();
	        // goto
	        $backData['target-action'] = 'loadstep';
	        if(!empty($prev) && $prev->id > 0){
	            $backData['fk_step'] = $prev->id;
	        }else{
	            $backData['fk_step'] = $curentStep->id;
	        }
	        $backAttr = $this->inlineData($backData);
	        
	        if(!$disableBackBtn)
	        {
	           print '<span class="back-to-the-future" '.$backAttr.' ><i class="fa fa-chevron-left"></i> '.$langs->trans('GoBack').' </span>';
	           print '<div style="clear:both;" ></div>';
	        }
	        
	        
	        $stepTitle = '<h2><span class="rank" >'.($curentStep->rank + 1).'.</span> '.dol_htmlentities($curentStep->label).' '.$curentSelectedRoadMapLabel.'</h2>';
	        
	        if($curentStep->type == $curentStep::TYPE_SELECT_CATEGORY)
	        {
	            print $stepTitle;
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
	                
	                // Vérification 
	                $TdisplayStatus = array();
	                foreach ($elements as $catid)
	                {
	                    $TCategory=array();
	                    if($curentStep->linked){
	                       $TCategory= array($this->TcurentComposer['fk_categorie_selected']);
	                    }
	                    
	                    if($curentStep->catHaveChild($catid,$TCategory) )
	                    {
	                        $TdisplayStatus[$catid] = true;
	                    }
	                }
	                
	                if(!empty($TdisplayStatus))
	                {
	                    
	                    print $stepTitle;
	                    
	                    print '<div class="productcomposer-catproduct" style="border-color: '.$curentStep->categorie->color.';" >';
	                    foreach ($elements as $catid)
	                    {
	                        if(empty($TdisplayStatus[$catid])) continue;
	                        
	                        $categorie = new Categorie($this->db);
	                        $categorie->fetch($catid);
	                        
	                        $data['target-action'] = 'loadstep';
	                        $data['fk_nextstep'] = $nextStep->id;
	                        $data['fk_categorie'] = $catid;
	                        
	                        
	                        $this->print_catForStep($curentStep,$categorie,$data);
	                    }
	                    print '</div>';
	                }
	                elseif($curentStep->optional)
	                {
	                    print $this->print_nextstep($curentStep->id,false,true);
	                }
	                else 
	                {
	                    print $stepTitle;
	                    
	                    print hStyle::callout($this->langs->trans('NothingToView'), 'error');
	                }
	                
	            }
	            else 
	            {
	                
	                if($curentStep->linked){
	                    $Tcat = array($this->TcurentComposer['fk_categorie_selected'], $param['fk_categorie']);
	                    $products = $curentStep->getProductListInMultiCat( $Tcat );
	                }
	                else {
	                    $stepTitle = '<h2><span class="rank" >'.($curentStep->rank + 1).'.</span> '.dol_htmlentities($curentStep->label).'</h2>';
	                    
	                    $products = $curentStep->getProductList($param['fk_categorie']);
	                }
	                
	                
	                
	                if($products)
	                {
	                    
	                    print $stepTitle;
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
	                elseif($curentStep->optional)
	                {
	                    print $this->print_nextstep($curentStep->id,false,true);
	                }
	                else{
	                    
	                    print $stepTitle;
	                    print hStyle::callout($this->langs->trans('Noproductcomposer'), 'error');
	                }
	            }
	            
	            
	            
	        }
	        elseif($curentStep->type == $curentStep::TYPE_GOTO)
	        {
	            // goto 
	            $goToData['target-action'] = 'loadstep';
	            $goToData['fk_step'] = $curentStep->fk_pcroadmapdet;
	            $goToData['goto'] = 1;
	            
	            
	            
	            $gotoAttr = $this->inlineData($goToData);
	            
	            // next step
	            $nextStep = $curentStep->getNext();
	            if(!empty($nextStep))
	            {
	                $data['target-action'] = 'loadstep';
	                $data['fk_step'] = $nextStep->id;
	                $nextAttr = $this->inlineData($data);
	            }
	            print '<div style="clear:both; margin-top:20px;" ></div>';
	            print '<table >';
	            print '    <td>';
	            print '        <tr style="text-align:right;padding:10px;">';
	            print '            <span class="butAction" '.$gotoAttr.' ><i class="fa fa-chevron-left"></i> '.$curentStep->getLabel($curentStep->fk_pcroadmapdet).'</span>';
	            print '        </tr>';
	            print '        <tr style="text-align:left;padding:10px;" >';
	            
	            if(!empty($nextStep))
	            {
	                print '<span class="butAction" '.$nextAttr.' >'.$curentStep->getLabel($nextStep->id).' <i class="fa fa-chevron-right"></i></span>';
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
	   $width=300;
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
	    $maxWidth=$maxHeight=300;
	    
	    
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
	
	
	public function print_nextstep($curentStepId, $param = false, $disableBackBtn = false)
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
	                $this->print_step($firstStepId, false, $disableBackBtn);
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
	            $this->print_step($nextStep->id,$param, $disableBackBtn);
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
	    global $conf;
	    
	    // Init currentCycle
	    if(empty($this->TcurentComposer['currentCycle'])) $this->TcurentComposer['currentCycle'] = 0;
	    
	    $currentCycle = $this->TcurentComposer['currentCycle'];
	    
	    $curQty = 0;
	    
	    if(!empty($conf->global->PC_DO_NOT_CLEAR_ON_ADD_PRODUCT))
	    {
	        if(!empty($this->TcurentComposer['products'][$currentCycle][$stepid][$productid])){
	            $curQty = $this->TcurentComposer['products'][$currentCycle][$stepid][$productid];
	        }
	        
	        $this->TcurentComposer['products'][$currentCycle][$stepid][$productid] = $curQty + $qty;
	    }
	    else 
	    {
	        
	        $this->TcurentComposer['products'][$currentCycle][$stepid] = array($productid => $qty );
	    }
	    
	    
	    $this->save();
	}
	
	
	public function deleteProduct($cycle,$step,$product,$allAfter=true)
	{
	    global $conf;
	    
	    $cycle= intval($cycle);
	    $step= intval($step);
	    $product= intval($product);
	    
        if(!$allAfter){
            unset($this->TcurentComposer['products'][$cycle][$step][$product]);
        }
	    else
	    {
	        $deleteAllRightNow=false;
	        
	        foreach ( $this->TcurentComposer['products'] as $Kcycle => $Tstep )
	        {
	            // DELETE All After
	            if($deleteAllRightNow){
	                unset($this->TcurentComposer['products'][$Kcycle]);
	                continue;
	            }
	            
	            foreach($Tstep as $Kstep => $Tproduct)
	            {
	                // DELETE All After
	                if($deleteAllRightNow){
	                    unset($this->TcurentComposer['products'][$Kcycle][$Kstep]);
	                    continue;
	                }
	                
	                foreach ($Tproduct as $Kproduct => $qty)
	                {
	                    // DELETE All After
	                    if($deleteAllRightNow)
	                    {
	                        unset($this->TcurentComposer['products'][$Kcycle][$Kstep][$Kproduct]);
	                        continue;
	                    }
	                    /*var_dump(array(
	                        array($cycle,$Kcycle , $step , $Kstep , $product ,$Kproduct),
	                        $cycle == $Kcycle , $step == $Kstep , $product == $Kproduct
	                    ));*/
	                    if($cycle === $Kcycle && $step === $Kstep && $product === $Kproduct)
	                    {
	                        unset($this->TcurentComposer['products'][$Kcycle][$Kstep][$Kproduct]);
	                        $deleteAllRightNow = true;
	                       // echo 'rrr';
	                    }
	                }
	            }
	        }
	    }
            
	    
	    $this->save();
	}
	
	public function printCart()
	{
	    global $langs,$conf;
	    if(!empty($this->TcurentComposer['products']))
	    {
	        $columns = 2;
	        print '<table class="border" >';
	        print '<thead>';
	        print '<tr class="liste_titre" ><th>'.$langs->trans('Product').'</th>';
	        if(!empty($conf->global->PC_SHOW_QUANTITY)){
	           print '<th>'.$langs->trans('Quantity').'</th>';
	           $columns++;
	        }
	        print '<th></th></tr>';
	        print '</thead>';
	        
	        
	        print '<tbody>';
	        $lastCycle = 0;
	        foreach ( $this->TcurentComposer['products'] as $cycle => $steps )
	        {
	            if($cycle != $lastCycle)
	            {
	                print '<tr><td colspan="'.$columns.'" ><hr/></td></tr>';
	                $lastCycle = $cycle;
	            }
	            
	            foreach($steps as $stepId => $products)
	            {
	                $stepObj = new PCRoadMapDet($this->db);
	                $stepObj->fetch($stepId);
	                
	                foreach ($products as $productId => $qty)
	                {
	                    $product = new Product($this->db);
	                    if($product->fetch($productId) > 0)
	                    {
	                        print '<tr><td>';
	                        print '<em>'.$stepObj->label.'</em><br/>';
	                        print '<strong>'.$product->ref.'</strong> '.$product->desc.'</td>';
	                        
	                        if(!empty($conf->global->PC_SHOW_QUANTITY)){
	                            print '<td >'.$qty.'</td>';
	                        }
	                        
	                        $data = array(
	                            'target-action' => 'delete-product',
	                            'cycle' => $cycle,
	                            'step' => $stepId,
	                            'product' => $productId,
	                            'load-in' => '#composer-cart',
	                        );
	                        $attr = !empty($data)?$this->inlineData($data):'';
	                        print '<td ><span class="pcbtn delete" title="'.$langs->trans('Delete').'" '.$attr.' ><i class="fa fa-trash"></i></span></td>';

                            print '</tr>';
	                    }
	                }
	            }
	        }
	        print '</tbody>';
	        
	        $data  = array(
	            'target-action' => 'import'
	        );
	        
	        /*print '<tfoot>';
	        print '<tr><td colspan="'.$columns.'" style="text-align:right;" ><span class="butAction" '.$this->inlineData($data).'  > '.$langs->trans('ImportInDocument').'</span></td></tr>';
	        print '</tfoot>';*/
	        
	        print '</table>' ;
	    }
	    
	}
	
	
	public function import()
	{
	    global $langs, $hookmanager;;
	    
	    $errors = 0;
	    $linesImported =0;
	    
	    $curentRank = count($this->object->lines) + 1;
	    
	    if(!empty($this->TcurentComposer['products']))
	    {
	        // Ajout du titre
	        $roadmapCat = new Categorie($this->db);
	        $roadmapCat->fetch($this->TcurentComposer['fk_categorie_selected']);
	        
	        $txtva = 0 ;
	        $titleDesc =$roadmapCat->description;
	        $titlelabel = $this->roadmap->label.' : '.$roadmapCat->label;
	        $array_options = array();
	        $this->subtotalAddTitle($titleDesc,1,-1,  $array_options, $txtva, $titlelabel );
	        
	        
	        $lastCycle = 0;
	        foreach ( $this->TcurentComposer['products'] as $cycle => $steps )
	        {
	            if($cycle != $lastCycle)
	            {
	                $lastCycle = $cycle;
	            }
	            
	            
	            foreach($steps as $stepId => $products)
	            {
	                $stepObj = new PCRoadMapDet($this->db);
	                $stepObj->fetch($stepId);
	                
	                foreach ($products as $productId => $qty)
	                {
	                    $product = new Product($this->db);
	                    if($product->fetch($productId) > 0)
	                    {
	                        //$this->object->
	                        $curentRank++;
	                        
	                     
	                        $parameters=array(
	                            'curentRank' => $curentRank,
	                            'lastCycle' => $lastCycle,
	                            'cycle' => $cycle,
	                            'stepId' => $stepId,
	                            'product' =>& $product,
	                            'qty' =>& $qty
	                        );

	                            
                            $reshook=$hookmanager->executeHooks('pcImportProductInDocument',$parameters,$this);    // Note that $action and $object may have been modified by hook
                            if ($reshook < 0) setEventMessages($hookmanager->error,$hookmanager->errors,'errors');
                            if (!$reshook)
                            {
                                $desc = '';
                                $pu_ht = $product->price;
                                //$qty; already set in foreach
                                $txtva = $product->tva_tx;
                                $txlocaltax1=0;
                                $txlocaltax2=0;
                                $fk_product = $product->id;
                                $remise_percent=0;
                                $info_bits=0;
                                $fk_remise_except=0;
                                $price_base_type='HT';
                                $pu_ttc=0;
                                $date_start='';
                                $date_end='';
                                $type=0;
                                $rang=$curentRank;
                                $special_code=0;
                                $fk_parent_line=0;
                                $fk_fournprice=null;
                                $pa_ht=0;
                                $label='';
                                $array_options=0;
                                $fk_unit=$product->fk_unit;
                                $origin='';
                                $origin_id=0;
                                $pu_ht_devise = 0;
                                
                                
                                if($this->object->element == 'commande'){
                                    $res = $this->object->addline($desc, $pu_ht, $qty, $txtva, $txlocaltax1, $txlocaltax2, $fk_product, $remise_percent, $info_bits, $fk_remise_except, $price_base_type, $pu_ttc, $date_start, $date_end, $type, $rang, $special_code, $fk_parent_line, $fk_fournprice, $pa_ht, $label,$array_options, $fk_unit, $origin, $origin_id, $pu_ht_devise);
                                }elseif($this->object->element == 'propal'){
                                    $this->object->addline($desc, $pu_ht, $qty, $txtva, $txlocaltax1, $txlocaltax2, $fk_product, $remise_percent, $price_base_type, $pu_ttc, $info_bits, $type, $rang, $special_code, $fk_parent_line, $fk_fournprice, $pa_ht, $label,$date_start, $date_end,$array_options, $fk_unit, $origin, $origin_id, $pu_ht_devise, $fk_remise_except);
                                }
                                
                                if($res<1)
                                {
                                    $errors++;
                                }
                                //print $stepObj->label;
                                //print $product->ref.$product->desc.$qty;
                            }
	                        
	                        
	                    }
	                }
	            }
	        }
	        
	        $subTotalLabel = $langs->trans('Subtotal');
	        $curentRank++;
	        $level=1;
	        $this->subtotalAddTotal($subTotalLabel, $level, $curentRank);

	    }
	   
	    $this->annuleCurent();
	}
	
	
	public function  subtotalAddTotal($label, $level=0, $rang=-1)
	{
	    if(!class_exists('TSubtotal')){
	        dol_include_once('subtotal/class/subtotal.class.php');
	    }
	    
	    if(class_exists('TSubtotal')){
	        TSubtotal::addTotal($this->object, $label, $level, $rang);
	    }
	}
	
	// subtotal add title 
	function subtotalAddTitle($desc ='', $level=0, $rang = -1, $array_options =0,$txtva =0,$label='')
	{
	    if(!class_exists('TSubtotal')){
	        $subtotalModuleNumber = 104777;
	        dol_include_once('subtotal/class/subtotal.class.php');
	    }
	    
	    
	    
	    $qty = $level;
	    
	    if(!empty($level))
	    {
	        $qty = $level;
	    }
	    
	    if(class_exists('TSubtotal')){
	       $subtotalModuleNumber = TSubtotal::$module_number;
	    }
	    
	    /**
	     * @var $object Facture
	     */
	    if($this->object->element=='facture') return  $this->object->addline($desc, 0,$qty,0,0,0,0,0,'','',0,0,'','HT',0,9,$rang, $subtotalModuleNumber, '', 0, 0, null, 0, $label,$array_options);
	    /**
	     * @var $object Propal
	     */
	    else if($this->object->element=='propal') return $this->object->addline($desc, 0,$qty,0,0,0,0,0,'HT',0,0,9,$rang, $subtotalModuleNumber, 0, 0, 0, $label,'', '',$array_options);
	    /**
	     * @var $object Commande
	     */
	    else if($this->object->element=='commande') return $this->object->addline($desc, 0,$qty, $txtva,0,0,0,0,0,0,0,0,0,0,9,$rang, $subtotalModuleNumber, 0, 0, 0, $label,$array_options);
	    
	 
	}
	
}


