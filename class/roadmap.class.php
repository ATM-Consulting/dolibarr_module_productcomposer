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

/**
 * Class PCRoadMap : Product composer roadmap
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

	public $addprov = false;

	public $childtables = array(
	    'PCRoadMapDet'
	);

	/**
	 * @var PCDbTool
	 */
	public $dbTool;

	public $fk_element = 'fk_pcroadmap';

	public $status;
	public $entity;
	public $fk_user_author;

	public function __construct($db)
	{
		global $conf;

		$this->db = $db;
		$this->dbTool = new PCDbTool($this->db);

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

		if ($this->addprov || !empty($this->is_clone))
		{
			if (!empty($this->is_clone)) $this->status = self::STATUS_DRAFT;

			$wc = $this->withChild;
			$this->withChild = false;
			$res = $this->id>0 ? $this->updateCommon($user) : $this->createCommon($user);
			$this->withChild = $wc;
		}

		return $res;
	}

	public function getId()
	{
	    return $this->id;
	}

	public function loadBy($value, $field, $annexe = false)
	{
		$res = parent::loadBy($value, $field, $annexe);
		return $res;
	}

	public function load($id, $ref='', $loadChild = true)
	{
		$res = parent::fetchCommon($id, $ref);
		if ($loadChild) $this->fetchChild();
		return $res;
	}

	public function delete(User &$user)
	{
		global $user;

		$this->fetchChild();
		$errors = 0;

		if(!empty($this->TPCRoadMapDet))
		{
		    foreach ($this->TPCRoadMapDet as $roadmapdet )
		    {
		        if($roadmapdet->delete($user) < 1)
		        {
		            $errors ++;
		        }
		    }
		}

		if(empty($errors))
		{
		    return parent::deleteCommon($user);
		}
		else {
		    return -1 * $errors;
		}

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

		// DRAFT is default status
		$statustrans='statut0';
		$keytrans='productcomposerStatusDraft';
		$shortkeytrans='Draft';

		if ($status==self::STATUS_VALIDATED) { $statustrans='statut1'; $keytrans='productcomposerStatusValidated'; $shortkeytrans='Validate'; }

		if ($mode == 0) return img_picto($langs->trans($keytrans), $statustrans);
		elseif ($mode == 1) return img_picto($langs->trans($keytrans), $statustrans).' '.$langs->trans($keytrans);
	}



	static public function updateRankOfLine($rowid,$rank)
	{
	    global $db;
	    $sql = 'UPDATE '.MAIN_DB_PREFIX.'pcroadmap SET rank = '.$rank;
	    $sql.= ' WHERE rowid = '.$rowid;

	    // echo $sql;
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

	/**
	 *      Load properties id_previous and id_next by rank
	 *
	 *      @param	string	$filter		Optional filter. Example: " AND (t.field1 = 'aa' OR t.field2 = 'bb')"
	 *	 	@param  string	$fieldid   	Name of field to use for the select MAX and MIN
	 *		@param	int		$nodbprefix	Do not include DB prefix to forge table name
	 *      @return int         		<0 if KO, >0 if OK
	 */
	function load_previous_next_ref($filter, $fieldid, $nodbprefix=0)
	{
	    $sql = 'SELECT te.rowid id';
	    $sql.= ' FROM '.MAIN_DB_PREFIX.$this->table_element.' as te';
	    $sql.= ' WHERE te.rank < '.intval($this->rank);
	    if (! empty($filter))
	    {
	        if (! preg_match('/^\s*AND/i', $filter)) $sql.=' AND ';   // For backward compatibility
	        $sql.=$filter;
	    }

	    if (isset($this->ismultientitymanaged) && $this->ismultientitymanaged == 1) $sql.= ' AND te.entity IN ('.getEntity($this->element).')';

	    $sql.= ' ORDER BY te.rank DESC';

	    $this->ref_previous =  $this->dbTool->getvalue($sql);

	    $sql = 'SELECT te.rowid id';
	    $sql.= ' FROM '.MAIN_DB_PREFIX.$this->table_element.' as te';
	    $sql.= ' WHERE te.rank > '.intval($this->rank);
	    if (! empty($filter))
	    {
	        if (! preg_match('/^\s*AND/i', $filter)) $sql.=' AND ';   // For backward compatibility
	        $sql.=$filter;
	    }

	    if (isset($this->ismultientitymanaged) && $this->ismultientitymanaged == 1) $sql.= ' AND te.entity IN ('.getEntity($this->element).')';

	    $sql.= ' ORDER BY te.rank ASC';

	    $this->ref_next = $this->dbTool->getvalue($sql);

	    return 1;
	}
}




class PCRoadMapDet extends SeedObject
{
    public $table_element = 'pcroadmapdet';
    public $element = 'pcroadmapdet';
    public $type;
    public $label;
    public $fk_categorie;
    public $rank;
    public $categorie;
    public $fk_pcroadmap;
    public $fk_pcroadmapdet;
    public $noPrice;
    public $flag_desc;
    public $addprov = false;
	public $linked;
	public $flag_dimensions;
	public $step_cat_linked;


    /**
     * Type status
     */
    const TYPE_SELECT_CATEGORY = 1; // n'est finalement pas utiliser
    const TYPE_SELECT_PRODUCT  = 2;
    const TYPE_GOTO = 3; // permet de boucler sur une étape

    public function __construct($db)
    {
        global $conf,$langs;

        $this->db =& $db;
        $this->dbTool = new PCDbTool($this->db);

        $this->fields=array(
            'fk_pcroadmap'=>array('type'=>'int')
            ,'label'=>array('type'=>'string') // le libelle
            ,'type'=>array('type'=>'int')
            ,'fk_categorie'=>array('type'=>'int')
            ,'rank'=>array('type'=>'int')
            ,'linked' =>array('type'=>'int') // si les elements liés à la catégorie doivent aussi êtres liés à la catégorie de la feuille de route
            ,'step_cat_linked' =>array('type'=>'int') // si les elements liés à la catégorie doivent aussi êtres liés à la catégorie de l'étape précédante
            // type goto
            ,'fk_pcroadmapdet'=>array('type'=>'int')
            // type product
            ,'optional' =>array('type'=>'int') // si l'étape est optionnelle
            ,'noPrice' =>array('type'=>'int') // lors de l'import force le prix à zero
            ,'needRoadmapCat' =>array('type'=>'int') // la liste des produits est filtrée aussi avec la catégorie de la feuille de route
            ,'flag_desc' =>array('type'=>'int') // Permet la modification de la description du produit
            ,'flag_dimensions' =>array('type'=>'int') // Permet de saisir les dimensions
            //,'needPreviusCat' =>array('type'=>'int')
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
    public function fetch($id, $loadChild = true, $ref = NULL)
    {
        $res = parent::fetch($id, $loadChild, $ref);

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

        if(empty($this->id)){ $this->rank = $this->getMaxRank() + 1; }

        $res = $this->id>0 ? $this->updateCommon($user) : $this->createCommon($user);

        if ($this->addprov || !empty($this->is_clone))
        {
            $wc = $this->withChild;
            $this->withChild = false;
            $res = $this->id>0 ? $this->updateCommon($user) : $this->createCommon($user);
            $this->withChild = $wc;
        }

        return $res;
    }

    public function getMaxRank()
    {
        global $db;
        $sql = 'SELECT MAX(rank) FROM '.MAIN_DB_PREFIX.'pcroadmapdet ';
        $sql.= ' WHERE fk_pcroadmap = '.$this->fk_pcroadmap;

        return $this->dbTool->getvalue($sql);
    }


    public function getId()
    {
        return $this->id;
    }

    public function loadBy($value, $field, $annexe = false)
    {
        $res = parent::loadBy($value, $field, $annexe);
        return $res;
    }

    public function load($id, $ref='', $loadChild = true)
    {
        $res = parent::fetchCommon($id, $ref);
        if ($loadChild) $this->fetchObjectLinked();
        return $res;
    }

    public function delete(User &$user)
    {
        global $user;
        return parent::deleteCommon($user);
    }


    static public function updateRankOfLine($rowid,$rank)
    {
        global $db;
        $sql = 'UPDATE '.MAIN_DB_PREFIX.'pcroadmapdet SET rank = '.$rank;
        $sql.= ' WHERE rowid = '.$rowid;

        if (! $db->query($sql))
        {
            dol_print_error($db->db);
        }
    }


    public function getClosest($next = false)
    {
        $operateur =  !empty($next)?'>':'<';
        $order =  !empty($next)?'ASC':'DESC';

        $sql = 'SELECT rowid as id FROM '.MAIN_DB_PREFIX.$this->table_element;
        $sql.= ' WHERE fk_pcroadmap = '.$this->fk_pcroadmap . ' AND rank '.$operateur.' '.$this->rank;
        $sql.= ' ORDER BY rank '.$order;
        $sql.= ' LIMIT 1 ';

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

    public function getProductList($fk_category=0){

        if(empty($fk_category)){ $fk_category = $this->fk_categorie;}

        $sql = "SELECT c.fk_product as id" ;
        $sql .= " FROM " . MAIN_DB_PREFIX . "categorie_product c ";
        $sql .= " JOIN  " . MAIN_DB_PREFIX . "categorie o ON (c.fk_categorie = o.rowid) ";
        $sql .= " JOIN " . MAIN_DB_PREFIX . "product p ON (p.rowid = c.fk_product) ";
        $sql .= " WHERE o.entity IN (" . getEntity('category').")";
        $sql .= " AND c.fk_categorie = ".intval($fk_category);
        $sql .= " ORDER BY p.label ASC";

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


    public function getProductListInMultiCat($TCategory=array()){

        if(!is_array($TCategory) || empty($TCategory) ) return 0;
        //var_dump($TCategory);
        // récupération des produits lié à la feuille de route
        $Tall = array();

        $i=0;
        foreach ($TCategory as $fk_category)
        {
            $list = $this->getProductList($fk_category);

            if(!empty($list))
            {
                if($i==0){
                    $Tall = $list;
                }
                else {
                    $Tall = array_intersect($Tall, $list);
                }

                $i++;
            }
            else
            {
                $Tall = array();
                break;
            }
        }

        $Tall = array_unique ( $Tall);

        if(!empty($Tall)){
            $Tall = array_map('intval', $Tall);
            // pour le tri des produits
            $sql = "SELECT p.rowid as id" ;
            $sql .= " FROM " . MAIN_DB_PREFIX . "product ";
            $sql .= " WHERE p.rowid IN (" . implode(',', $Tall).")";
            $sql .= " ORDER BY p.label ASC";

            $products = $this->dbTool->executeS($sql);
            if($products)
            {
                $Tall = array();
                foreach ($products as $obj)
                {
                    $Tall[] = $obj->id;
                }
            }
        }

        return $Tall;

    }




    public function getCatList($fk_category=0){

        if(empty($fk_category)){ $fk_category = $this->fk_categorie;}

        $sql = "SELECT o.rowid as id" ;
        $sql .= " FROM " . MAIN_DB_PREFIX . "categorie o";
        $sql .= " WHERE o.entity IN (" . getEntity('category').")";
        $sql .= " AND o.fk_parent = ".intval($fk_category);

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

    public function catHaveChild($fk_category=0,$TCategory=array(), $type = 'all'){

        if(empty($fk_category)){ $fk_category = $this->fk_categorie;}

        $TCategory[] = $fk_category;

        if($type=='all' || $type=='category')
        {
			$res = $this->getCatList($fk_category);
        	if(!empty($res)){
				return true;
			}
        }

        if( ($type=='all' || $type=='product' ))
        {
			$res = $this->getProductListInMultiCat($TCategory);
			if(!empty($res)){
				return true;
			}
        }

        return false;
    }



    /**
     *      Load properties id_previous and id_next by rank
     *
     *      @param	string	$filter		Optional filter. Example: " AND (t.field1 = 'aa' OR t.field2 = 'bb')"
     *	 	@param  string	$fieldid   	Name of field to use for the select MAX and MIN
     *		@param	int		$nodbprefix	Do not include DB prefix to forge table name
     *      @return int         		<0 if KO, >0 if OK
     */
    function load_previous_next_ref($filter, $fieldid, $nodbprefix=0)
    {
        $sql = 'SELECT te.rowid id';
        $sql.= ' FROM '.MAIN_DB_PREFIX.$this->table_element.' as te';
        $sql.= ' WHERE te.rank < '.intval($this->rank);
        if (! empty($filter))
        {
            if (! preg_match('/^\s*AND/i', $filter)) $sql.=' AND ';   // For backward compatibility
            $sql.=$filter;
        }

        if (isset($this->ismultientitymanaged) && $this->ismultientitymanaged == 1) $sql.= ' AND te.entity IN ('.getEntity($this->element).')';

        $sql.= ' ORDER BY te.rank DESC';

        $this->ref_previous =  $this->dbTool->getvalue($sql);

        $sql = 'SELECT te.rowid id';
        $sql.= ' FROM '.MAIN_DB_PREFIX.$this->table_element.' as te';
        $sql.= ' WHERE te.rank > '.intval($this->rank);
        if (! empty($filter))
        {
            if (! preg_match('/^\s*AND/i', $filter)) $sql.=' AND ';   // For backward compatibility
            $sql.=$filter;
        }

        if (isset($this->ismultientitymanaged) && $this->ismultientitymanaged == 1) $sql.= ' AND te.entity IN ('.getEntity($this->element).')';

        $sql.= ' ORDER BY te.rank ASC';

        $this->ref_next = $this->dbTool->getvalue($sql);

        return 1;
    }

    // used for form
    static function listType(){
        return array(
            self::TYPE_SELECT_CATEGORY => self::translateTypeConst(self::TYPE_SELECT_CATEGORY ),
            self::TYPE_SELECT_PRODUCT => self::translateTypeConst(self::TYPE_SELECT_PRODUCT ),
			self::TYPE_GOTO => self::translateTypeConst(self::TYPE_GOTO )
        );
    }

    // used for form
    public function listSteps($notIn = array()){

        $TRet = array();

        $sql = 'SELECT s.rowid id, s.label label';
        $sql.= ' FROM '.MAIN_DB_PREFIX.$this->table_element.' as s';
        $sql.= ' WHERE  s.fk_pcroadmap = ' . $this->fk_pcroadmap;

        if(!empty($notIn) && is_array($notIn)){
            $notIn = array_map('intval', $notIn);
            $sql.= ' AND  s.rowid NOT IN('.implode(',', $notIn).')';
        }

        $sql.= ' ORDER BY s.rank ASC';

        $Tlist = $this->dbTool->executeS($sql);

        foreach ($Tlist as $step)
        {
            $TRet[$step->id] = $step->label;
        }

        return $TRet;

    }

    static function translateTypeConst($key){
        global $langs;
        switch ($key) {
            case self::TYPE_SELECT_CATEGORY :
                return $langs->trans('TYPE_SELECT_CATEGORY');
                break;
            case self::TYPE_SELECT_PRODUCT :
                return $langs->trans('TYPE_SELECT_PRODUCT');
                break;
            case self::TYPE_GOTO :
                return $langs->trans('TYPE_GOTO');
                break;
        }
    }


    public function typeLabel(){
        return self::translateTypeConst($this->type);
    }

    public function getLabel($id){
        $gotoStep = new PCRoadMapDet($this->db);
        $ret = $gotoStep->fetch($id);
        if($ret>0){
            return $gotoStep->label;
        }
        else
        {
            return false;
        }
    }
}
