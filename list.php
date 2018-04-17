<?php

require 'config.php';
// Libraries
require_once DOL_DOCUMENT_ROOT . "/core/lib/admin.lib.php";
dol_include_once( '/productcomposer/lib/productcomposer.lib.php');
dol_include_once('/productcomposer/class/roadmap.class.php');

if( empty($user->rights->productcomposer->read) && !$user->admin) accessforbidden();

$langs->load('abricot@abricot');
$langs->load('productcomposer@productcomposer');

$PDOdb = new TPDOdb;
$object = new PCRoadMap($db);

$hookmanager->initHooks(array('productcomposerlist'));

/*
 * Actions
 */

$parameters=array();
$reshook=$hookmanager->executeHooks('doActions',$parameters,$object);    // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

if (empty($reshook))
{
	// do action from GETPOST ... 
}


/*
 * View
 */

llxHeader('',$langs->trans('RoadmapList'),'','');

// Configuration header
$head = productcomposerAdminPrepareHead();
dol_fiche_head(
    $head,
    'roadmaps',
    $langs->trans("Module103998Name"),
    0,
    "productcomposer@productcomposer"
    );



//$type = GETPOST('type');
//if (empty($user->rights->productcomposer->all->read)) $type = 'mine';

// TODO ajouter les champs de son objet que l'on souhaite afficher
$sql = 'SELECT r.rowid, r.label, r.date_creation, \'\' AS action';

$sql.= ' FROM '.MAIN_DB_PREFIX.$object->table_element.' r ';

$sql.= ' WHERE 1=1';
//$sql.= ' AND t.entity IN ('.getEntity('productcomposer', 1).')';
//if ($type == 'mine') $sql.= ' AND t.fk_user = '.$user->id;


$formcore = new TFormCore($_SERVER['PHP_SELF'], 'form_list_productcomposer', 'GET');

$nbLine = !empty($user->conf->MAIN_SIZE_LISTE_LIMIT) ? $user->conf->MAIN_SIZE_LISTE_LIMIT : $conf->global->MAIN_SIZE_LISTE_LIMIT;

$r = new Listview($db, 'productcomposer');
echo $r->render($sql, array(
	'view_type' => 'list' // default = [list], [raw], [chart]
	,'limit'=>array(
		'nbLine' => $nbLine
	)
	,'subQuery' => array()
	,'link' => array()
	,'type' => array(
		//'date_creation' => 'date' // [datetime], [hour], [money], [number], [integer]
	)
	,'search' => array(
		//'date_creation' => array('recherche' => 'calendars', 'allow_is_null' => true)
		//'label' => array(
		    //'recherche' => true, 
		    //'table' => array('r'), 
		    //'field' => array('label')
		//) // input text de recherche sur plusieurs champs
	    //'status' => array('recherche' => PCRoadMap::$TStatus, 'to_translate' => true) // select html, la clé = le status de l'objet, 'to_translate' à true si nécessaire
	)
	,'translate' => array()
	,'hide' => array(
		'rowid'
	)
    ,'list' => array(
        'title'=>$langs->trans('RoadmapList')
		,'image' => 'title_generic.png'
		,'picto_precedent' => '<'
		,'picto_suivant' => '>'
		,'noheader' => 0
		,'messageNothing' => $langs->trans('Noproductcomposer')
		,'picto_search' => img_picto('','search.png', '', 0)
	)
    
	,'title'=>array(
		'label' => $langs->trans('Label')
		,'date_creation' => $langs->trans('DateCre')
	)
	,'eval'=>array(
//		'fk_user' => '_getUserNomUrl(@val@)' // Si on a un fk_user dans notre requête
	)
));

$parameters=array('sql'=>$sql);
$reshook=$hookmanager->executeHooks('printFieldListFooter', $parameters, $object);    // Note that $action and $object may have been modified by hook
print $hookmanager->resPrint;

$formcore->end_form();

llxFooter('');

/**
 * TODO remove if unused
 */
function _getUserNomUrl($fk_user)
{
	global $db;
	
	$u = new User($db);
	if ($u->fetch($fk_user) > 0)
	{
		return $u->getNomUrl(1);
	}
	
	return '';
}