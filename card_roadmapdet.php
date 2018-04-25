<?php

require 'config.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';
dol_include_once('/productcomposer/lib/productcomposer.lib.php');
dol_include_once('/productcomposer/class/roadmap.class.php');

if( empty($user->rights->productcomposer->read) && !$user->admin) accessforbidden();

$langs->load('productcomposer@productcomposer');

$action = GETPOST('action');
$id = GETPOST('id', 'int');
$ref = GETPOST('ref');

$mode = 'view';
if (empty($user->rights->productcomposer->write)) $mode = 'view'; // Force 'view' mode if can't edit object
else if ($action == 'create' || $action == 'edit') $mode = 'edit';

$object = new PCRoadMapStep($db);

if (!empty($id)) $object->load($id);
elseif (!empty($ref)) $object->loadBy($ref, 'ref');

$hookmanager->initHooks(array('productcomposercard', 'globalcard'));

/*
 * Actions 
 */

$parameters = array('id' => $id, 'ref' => $ref, 'mode' => $mode);
$reshook = $hookmanager->executeHooks('doActions', $parameters, $object, $action); // Note that $action and $object may have been modified by some
if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

// Si vide alors le comportement n'est pas remplacé
if (empty($reshook))
{
	$error = 0;
	switch ($action) {
		case 'save':
			$object->setValues($_REQUEST);  // Set standard attributes
//			$object->date_other = dol_mktime(GETPOST('starthour'), GETPOST('startmin'), 0, GETPOST('startmonth'), GETPOST('startday'), GETPOST('startyear'));

			// Check parameters
//			if (empty($object->date_other))
//			{
//				$error++;
//				setEventMessages($langs->trans('warning_date_must_be_fill'), array(), 'warnings');
//			}
			
			// ... 
			
			if ($error > 0)
			{
				$mode = 'edit';
				break;
			}
			
			$object->save(empty($object->ref));
			
			header('Location: '.dol_buildpath('/productcomposer/card.php', 1).'?id='.$object->getId());
			exit;
			
			break;
	
		case 'modif':
			if (!empty($user->rights->productcomposer->write)) $object->setDraft();
				
			break;

		case 'confirm_delete':
		    
		    $listUrl = dol_buildpath('/productcomposer/card.php', 1).'?id='.$object->fk_pcroadmap;
		    if($object->delete($user)>0){
		        setEventMessage($langs->trans('RoadmapDetDeleteSuccess'));
		        header('Location: '.$listUrl);
		    }
		    else {
		        setEventMessage($langs->trans('RoadmapDetDeleteError', 'errors'));
		        header('Location: '.dol_buildpath('/productcomposer/card_roadmapdet.php', 1).'?id='.$object->id);
		    }
			exit;
			break;
	}
}


/**
 * View
 */

$title=$langs->trans("productcomposer");
llxHeader('',$title);

if ($action == 'create' && $mode == 'edit')
{
	load_fiche_titre($langs->trans("Newproductcomposer"));
	dol_fiche_head();
}
else
{
    $head = productcomposerAdminPrepareHead();
    $h = count($head) +1; 
    $head[$h][0] = dol_buildpath('/productcomposer/card.php', 1).'?id='.$object->fk_pcroadmap;
    $head[$h][1] = $langs->trans("productcomposerCard");
    $head[$h][2] = 'card';
    $h++;
    $head[$h][0] = dol_buildpath('/productcomposer/card_roadmapdet.php', 1).'?id='.$object->id;
    $head[$h][1] = $langs->trans("RoadMapStepCard");
    $head[$h][2] = 'roadmapdetcard';
    
	$picto = 'generic';
	dol_fiche_head($head, 'roadmapdetcard', $langs->trans("productcomposer"), 0, $picto);
}

$formcore = new TFormCore;
$formcore->Set_typeaff($mode);

$form = new Form($db);

$formconfirm = getFormConfirmproductcomposer($PDOdb, $form, $object, $action);
if (!empty($formconfirm)) echo $formconfirm;

$TBS=new TTemplateTBS();
$TBS->TBS->protect=false;
$TBS->TBS->noerr=true;

$categorieLabel = '';
if(!empty($object->fk_categorie))
{
    $category = new Categorie($db);
    if( $category->fetch($object->fk_categorie) > 0 )
    {
        $categorieLabel = $category->label;
    }
}


$formUrl = dol_buildpath('/productcomposer/card.php?id='.$object->fk_pcroadmap, 2);
if ($mode == 'edit') echo $formcore->begin_form($_SERVER['PHP_SELF'], 'form_productcomposer');

$linkback = '<a href="'.$formUrl .'">' . $langs->trans("BackToList") . '</a>';
print $TBS->render('tpl/card_roadmapdet.tpl.php'
	,array() // Block
	,array(
		'object'=>$object
		,'view' => array(
			'mode' => $mode
			,'action' => 'save'
			,'urlcard' => dol_buildpath('/productcomposer/card_roadmapdet.php', 1)
		    ,'urllist' => dol_buildpath('/productcomposer/card.php?id='.$object->fk_pcroadmap, 1)
			//,'showRef' => ($action == 'create') ? $langs->trans('Draft') : $form->showrefnav($object->generic, 'ref', $linkback, 1, 'ref', 'ref', '')
			,'showLabel' => $formcore->texte('', 'label', $object->label, 80, 255)
//			,'showNote' => $formcore->zonetexte('', 'note', $object->note, 80, 8)
		    ,'showCat' => ($mode == 'edit')? $form->select_all_categories('product', $object->fk_categorie,"fk_categorie") : $categorieLabel
		    
		)
		,'langs' => $langs
		,'user' => $user
		,'conf' => $conf
	)
);

if ($mode == 'edit')
{
    echo $formcore->end_form();
}



llxFooter();