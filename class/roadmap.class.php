<?php

if (!class_exists('TObjetStd'))
{
	/**
	 * Needed if $form->showLinkedObjectBlock() is call
	 */
	define('INC_FROM_DOLIBARR', true);
	require_once dirname(__FILE__).'/../config.php';
}

require_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';
dol_include_once('/productcomposer/class/pcdbtool.class.php');
/*
 * Product composer roadmap
 */
class PCRoadMap extends SeedObject
{
	/**
	 * Draft status
	 */
	const STATUS_DRAFT = 0;
	/**
	 * Validated status
	 */
	const STATUS_VALIDATED = 1;
	
	public static $TStatus = array(
		self::STATUS_DRAFT => 'Draft'
		,self::STATUS_VALIDATED => 'Validate'
	);
	
	public $table_element = 'pcroadmap';

	public $element = 'pcroadmap';
	
	public $withChild = true;
	
	public $childtables = array(
	    'PCRoadMapStep'
	    
	);
	
	public $fk_element = 'fk_pcroadmap';
	
	
	public function __construct($db)
	{
		global $conf,$langs;
		
		$this->db = $db;
		
		$this->fields=array(
		    'label'  => array('type'=>'string')
		    ,'status' =>array('type'=>'integer','index'=>true) // date, integer, string, float, array, text
		    ,'entity' =>array('type'=>'integer','index'=>true)
		    ,'fk_categorie'=>array('type'=>'int') // la catégorie principal dans laquele chaque produits devra être associé 
		    ,'rank'=>array('type'=>'int')
		);
		
		
		$this->init();
		
		$this->status = self::STATUS_DRAFT;
		$this->entity = $conf->entity;
	}

	public function save()
	{
		global $user;
		
		if (!$this->getId()) $this->fk_user_author = $user->id;
		
		$res = $this->id>0 ? $this->updateCommon($user) : $this->createCommon($user);
		
		if ($addprov || !empty($this->is_clone))
		{
			
			if (!empty($this->is_clone)) $this->status = self::STATUS_DRAFT;
			
			$wc = $this->withChild;
			$this->withChild = false;
			$res = $this->id>0 ? $this->updateCommon($user) : $this->createCommon($user);
			$this->withChild = $wc;
		}
		
		return $res;
	}
	
	
	public function loadBy($value, $field, $annexe = false)
	{
		$res = parent::loadBy($value, $field, $annexe);
		
		return $res;
	}
	
	public function load($id, $ref, $loadChild = true)
	{
		global $db;
		
		$res = parent::fetchCommon($id, $ref);
		
		if ($loadChild) $this->fetchObjectLinked();
		
		return $res;
	}
	
	public function delete(User &$user)
	{
		global $user;
		
		$this->generic->deleteObjectLinked();
		
		parent::deleteCommon($user);
	}
	
	public function setDraft()
	{
		if ($this->status == self::STATUS_VALIDATED)
		{
			$this->status = self::STATUS_DRAFT;
			$this->withChild = false;
			
			return self::save();
		}
		
		return 0;
	}
	
	public function setValid()
	{
//		global $user;
		
		$this->ref = $this->getNumero();
		$this->status = self::STATUS_VALIDATED;
		
		return self::save();
	}
	
	
	
	public function getNomUrl($withpicto=0, $get_params='')
	{
		global $langs;

        $result='';
        $label = '<u>' . $langs->trans("Showroadmap") . '</u>';
        if (! empty($this->ref)) $label.= '<br><b>'.$langs->trans('Ref').':</b> '.$this->ref;
        
        $linkclose = '" title="'.dol_escape_htmltag($label, 1).'" class="classfortooltip">';
        $link = '<a href="'.dol_buildpath('/productcomposer/card.php', 1).'?id='.$this->getId(). $get_params .$linkclose;
       
        $linkend='</a>';

        $picto='generic';
		
        if ($withpicto) $result.=($link.img_object($label, $picto, 'class="classfortooltip"').$linkend);
        if ($withpicto && $withpicto != 2) $result.=' ';
		
        $result.=$link.$this->ref.$linkend;
		
        return $result;
	}
	
	public static function getStaticNomUrl($id, $withpicto=0)
	{
		global $db;
		
		$object = new PCRoadMap($db);
		$object->load($id, '',false);
		
		return $object->getNomUrl($withpicto);
	}
	
	public function getLibStatut($mode=0)
    {
        return self::LibStatut($this->status, $mode);
    }
	
	public static function LibStatut($status, $mode)
	{
		global $langs;
		$langs->load('productcomposer@productcomposer');

		if ($status==self::STATUS_DRAFT) { $statustrans='statut0'; $keytrans='productcomposerStatusDraft'; $shortkeytrans='Draft'; }
		if ($status==self::STATUS_VALIDATED) { $statustrans='statut1'; $keytrans='productcomposerStatusValidated'; $shortkeytrans='Validate'; }

		if ($mode == 0) return img_picto($langs->trans($keytrans), $statustrans);
		elseif ($mode == 1) return img_picto($langs->trans($keytrans), $statustrans).' '.$langs->trans($keytrans);
	}
	
	
	
	static public function updateRankOfLine($rowid,$rank)
	{
	    global $db;
	    $sql = 'UPDATE '.MAIN_DB_PREFIX.$tis->table_element.' SET rank = '.$rank;
	    $sql.= ' WHERE rowid = '.$rowid;
	    
	    if (! $db->query($sql))
	    {
	        dol_print_error($db->db);
	    }
	}
	
	public function getAll($returntype = 'object', $active=1 )
	{
	    $TResult = array();
	    
	    $sql = 'SELECT r.rowid as id, r.label, r.date_creation';
	    $sql.= ' FROM '.MAIN_DB_PREFIX.$this->table_element.' r ';
	    
	    if($active>=0)
	    {
	        $sql.= ' WHERE status = '.intval($active);
	    }
	    
	    
	    $res = $this->db->query($sql);
	    if ($res)
	    {
	        while ($obj = $this->db->fetch_object($res))
	        {
	            
	            if($returntype=='id')
	            {
	                $TResult[] = $obj->id;
	            }
	            else
	            {
	                $objectElement = new self($this->db);
	                $objectElement->fetch($obj->id);
	                $TResult[$obj->id] = $objectElement;
	            }
	        }
	    }
	    
	    return $TResult;
	    
	    
	}
	
	public function getFirstStepId()
	{
	    if(empty($this->id)) return -1;
	    
	    $sql = 'SELECT rowid as id';
	    $sql.= ' FROM '.MAIN_DB_PREFIX.'pcroadmapdet  ';
	    $sql.= ' WHERE fk_pcroadmap = '.$this->id . ' ORDER BY rank ASC LIMIT 1 ';

	    $res = $this->db->query($sql);
	    if ($res)
	    {
	        $obj = $this->db->fetch_object($res);
	        return $obj->id;
	    }
	    
	    
	    return 0;
	    
	    
	}
}




class PCRoadMapStep extends SeedObject
{
    
    public $table_element = 'pcroadmapdet';
    
    
    public $element = 'pcroadmapdet';
    public $type;
    public $label;
    public $fk_categorie;
    public $rank;
    public $categorie;
    
    /**
     * Type status
     */
    const TYPE_SELECT_CATEGORY = 1;
    const TYPE_SELECT_PRODUCT  = 2;
    
    
    public function __construct($db)
    {
        global $conf,$langs;
        
        $this->db =& $db;
        $this->dbTool = new PCDbTool($this->db);
        
        $this->fields=array(
            
            'fk_pcroadmap'=>array('type'=>'int')
            ,'label'=>array('type'=>'string')
            ,'type'=>array('type'=>'int')
            ,'fk_categorie'=>array('type'=>'int')
            ,'rank'=>array('type'=>'int')
        );
        
        
        $this->init();
        
        $this->entity = $conf->entity;
    }
    
    /**
     *	Get object and children from database
     *
     *	@param      int			$id       		Id of object to load
     * 	@param		bool		$loadChild		used to load children from database
     *	@return     int         				>0 if OK, <0 if KO, 0 if not found
     */
    public function fetch($id, $loadChild = true)
    {
        $res = parent::fetch($id, $loadChild);
        
        if($res>0 && !empty($this->fk_categorie))
        {
            $this->categorie = new Categorie($this->db);
            $this->categorie->fetch($this->fk_categorie);
        }
        
        return $res;
    }
    
    
    public function save()
    {
        global $user;
        
        if (!$this->getId()) $this->fk_user_author = $user->id;
        
        $res = $this->id>0 ? $this->updateCommon($user) : $this->createCommon($user);
        
        if ($addprov || !empty($this->is_clone))
        {
            
            
            $wc = $this->withChild;
            $this->withChild = false;
            $res = $this->id>0 ? $this->updateCommon($user) : $this->createCommon($user);
            $this->withChild = $wc;
        }
        
        return $res;
    }
    
    
    public function loadBy($value, $field, $annexe = false)
    {
        $res = parent::loadBy($value, $field, $annexe);
        
        return $res;
    }
    
    public function load($id, $ref, $loadChild = true)
    {
        global $db;
        
        $res = parent::fetchCommon($id, $ref);
        
        if ($loadChild) $this->fetchObjectLinked();
        
        return $res;
    }
    
    public function delete(User &$user)
    {
        global $user;
        
        $this->generic->deleteObjectLinked();
        
        parent::deleteCommon($user);
    }
    
    
    public function updateRankOfLine($rowid,$rank)
    {
        global $db;
        $sql = 'UPDATE '.MAIN_DB_PREFIX.$this->table_element.' SET rank = '.$rank;
        $sql.= ' WHERE rowid = '.$rowid;
        
        if (! $db->query($sql))
        {
            dol_print_error($db->db);
        }
    }
    
    
    public function getClosest($next = false)
    {
        
        $operateur =  !empty($next)?'>':'<';
        
        $sql = 'SELECT rowid as id FROM '.MAIN_DB_PREFIX.$this->table_element;
        $sql.= ' WHERE fk_pcroadmap = '.$this->fk_pcroadmap . ' AND rank '.$operateur.' '.$this->rank.' LIMIT 1 ';
        $TResult = array();
        
        
        $res = $this->db->query($sql);
        if ($res)
        {
            return  $this->db->fetch_object($res);
        }
        
        return 0;
        
    }
    
    public function getNext(){
        return $this->getClosest(true);
    }
    
    public function getPrevious(){
        return $this->getClosest();
    }
    
    public function getProductList(){
    
        $sql = "SELECT c.fk_product as id" ;
        $sql .= " FROM " . MAIN_DB_PREFIX . "categorie_product as c, " . MAIN_DB_PREFIX . "categorie o";
        $sql .= " WHERE o.entity IN (" . getEntity('category').")";
        $sql .= " AND c.fk_categorie = ".$this->fk_categorie;
        $sql .= " AND c.fk_categorie = o.rowid";
        $products = $this->dbTool->executeS($sql);
        if($products)
        {
            $Tlist = array();
            foreach ($products as $obj)
            {
                $Tlist[] = $obj->id;
            }
            return $Tlist;
        }
    
        return false;
    }
    
    
    
    public function getCatList(){
        
        $sql = "SELECT o.rowid as id" ;
        $sql .= " FROM " . MAIN_DB_PREFIX . "categorie o";
        $sql .= " WHERE o.entity IN (" . getEntity('category').")";
        $sql .= " AND o.fk_parent = ".$this->fk_categorie;
        
        $results = $this->dbTool->executeS($sql);
        if($results)
        {
            $Tlist = array();
            foreach ($results as $obj)
            {
                $Tlist[] = $obj->id;
            }
            return $Tlist;
        }
        
        return false;
    }
    
}

