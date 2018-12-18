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
$fk_pcroadmap = GETPOST('fk_pcroadmap', 'int');

$mode = 'view';
if (empty($user->rights->productcomposer->write)) $mode = 'view'; // Force 'view' mode if can't edit object
else if ($action == 'create' || $action == 'edit') $mode = 'edit';



$object = new PCRoadMapDet($db);

if (!empty($id)) $object->load($id);
elseif (!empty($ref)) $object->loadBy($ref, 'ref');

if ($action == 'create' && empty($fk_pcroadmap) )
{
    exit();
}
if(empty($object->fk_pcroadmap)) $object->fk_pcroadmap=$fk_pcroadmap;

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
	        //$object->fk_pcroadmap = $fk_pcroadmap;
	        if(empty($object->fk_pcroadmap)) $object->fk_pcroadmap=$fk_pcroadmap;
	        
	        $optional = GETPOST('optional');
	        $object->optional = 0;
	        if($optional==='yes'){
	            $object->optional = 1;
	        }
	        
	        
	        $linked = GETPOST('linked');
	        $object->linked = 1;
	        if($linked==='yes'){
	            $object->linked = 1;
	        }
	        elseif($linked==='no'){
	            $object->linked = 0;
	        }
	        
	        $linked = GETPOST('step_cat_linked');
	        $object->step_cat_linked = 0;
	        if($linked==='yes'){
	            $object->step_cat_linked = 1;
	        }
	        elseif($linked==='no'){
	            $object->step_cat_linked = 0;
	        }
	        
	        $flag_desc = GETPOST('flag_desc');
	        $object->flag_desc = 0;
	        if($flag_desc==='yes'){
	            $object->flag_desc = 1;
	        }
	        elseif($flag_desc==='no'){
	            $object->flag_desc = 0;
	        }
	        
	        $noPrice = GETPOST('noPrice');
	        $object->noPrice = 0;
	        if($noPrice==='yes'){
	            $object->noPrice = 1;
	        }
	        elseif($noPrice==='no'){
	            $object->noPrice = 0;
	        }
	        
	        
	        if(empty($object->label)){
	            $error++;
	            setEventMessage($langs->trans('LabelIsEmpty'), 'errors');
	        }
	        
	        if($object->type === $object->TYPE_GOTO && $object->fk_pcroadmapdet < 1){
	            $error++;
	            setEventMessage($langs->trans('GotoIsEmpty'), 'errors');
	        }
	        
	        
	        //var_dump($fk_pcroadmap);
	        //var_dump($object);exit;
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
			
			header('Location: '.dol_buildpath('/productcomposer/card_roadmapdet.php', 1).'?id='.$object->getId());
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
	$pageName = $langs->trans("AddNewRoadMapStep");
	load_fiche_titre($pageName);
	print_fiche_titre($pageName);
	
	$head = roadmap_prepare_head();
	$h = count($head) +1;
	$head[$h][0] = dol_buildpath('/productcomposer/card.php', 1).'?id='.$object->fk_pcroadmap;
	$head[$h][1] = $langs->trans("productcomposerCard");
	$head[$h][2] = 'card';
	$h++;
	$head[$h][0] = dol_buildpath('/productcomposer/card_roadmapdet.php', 1).'?id='.$object->id;
	$head[$h][1] = $langs->trans("RoadMapStepCard");
	$head[$h][2] = 'roadmapdetcard';
	
	dol_fiche_head($head, 'roadmapdetcard', $langs->trans("productcomposer"), 0, $picto);
}
else
{
    
    $pageName = $langs->trans("RoadMapStepCard").' : '.$object->label;
    load_fiche_titre($pageName);
    print_fiche_titre($pageName);
    
    $head = roadmap_prepare_head();
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



$formcore = new TFormCore();
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


if ($mode == 'edit') echo $formcore->begin_form($_SERVER['PHP_SELF'], 'form_productcomposer');

$formUrl = dol_buildpath('/productcomposer/card.php?id='.$object->fk_pcroadmap, 2);
$linkback = '<a href="'.$formUrl .'">' . $langs->trans("BackToList") . '</a>';



print $TBS->render('tpl/card_roadmapdet.tpl.php'
	,array() // Block
	,array(
		'object'=>$object
		,'view' => array(
			'mode' => $mode
			,'action' => 'save'
			,'realaction' => $action
			,'urlcard' => dol_buildpath('/productcomposer/card_roadmapdet.php', 1)
		    ,'urllist' => dol_buildpath('/productcomposer/card.php?id='.$object->fk_pcroadmap, 1)
			//,'showRef' => ($action == 'create') ? $langs->trans('Draft') : $form->showrefnav($object->generic, 'ref', $linkback, 1, 'ref', 'ref', '')
			,'showLabel' => $formcore->texte('', 'label', $object->label, 80, 255)
		    //			,'showNote' => $formcore->zonetexte('', 'note', $object->note, 80, 8)
		    ,'showCat' => ($mode == 'edit')? $form->select_all_categories('product', $object->fk_categorie,"fk_categorie") : $categorieLabel
		    ,'showType' => ($mode == 'edit')? $form->selectarray('type', $object->listType(), empty($object->type)?2:$object->type ) : $object->typeLabel()
		    ,'fk_pcroadmap' => $object->fk_pcroadmap
		    ,'showGoto' => ($mode == 'edit')? $form->selectarray('fk_pcroadmapdet', $object->listSteps(array($object->id)),$object->fk_pcroadmapdet,1 ) : $object->getLabel($object->fk_pcroadmapdet)
		    
		    
		    ,'showOptional' => ($mode == 'edit')? $form->selectyesno('optional',$object->optional) : (empty($object->optional)?$langs->trans('No'):$langs->trans('Yes'))
		    ,'showNoPrice' => ($mode == 'edit')? $form->selectyesno('noPrice',$object->noPrice) : (empty($object->noPrice)?$langs->trans('No'):$langs->trans('Yes'))
		    
		    ,'showFlagDesc' => ($mode == 'edit')? $form->selectyesno('flag_desc',$object->flag_desc) : (empty($object->flag_desc)?$langs->trans('No'):$langs->trans('Yes'))
		    
		    
		    
		    ,'showLinkToRoadmapCat' => ($mode == 'edit')? $form->selectyesno('linked',$object->linked) : (empty($object->linked)?$langs->trans('No'):$langs->trans('Yes'))
		    ,'showLinkToPrevCat' => ($mode == 'edit')? $form->selectyesno('step_cat_linked',$object->step_cat_linked) : (empty($object->step_cat_linked)?$langs->trans('No'):$langs->trans('Yes'))
		)
	    ,'help' => array(
	        'help_LinkToRoadmapCat' => $form->textwithtooltip($langs->trans('CatIslinked'), $langs->trans('help_LinkToRoadmapCat'),2,1,img_help(1,'')),
	        'help_Goto' => $form->textwithtooltip($langs->trans('Goto'), $langs->trans('help_Goto'),2,1,img_help(1,'')),
	        'help_LinkToPrevCat' => $form->textwithtooltip($langs->trans('CatIslinkedToPrevius'), $langs->trans('help_LinkToPrevCat'),2,1,img_help(1,'')),
	        'help_optional' => $form->textwithtooltip($langs->trans('Optional'), $langs->trans('help_Optional'),2,1,img_help(1,'')),
	        'help_forcePriceToZero' => $form->textwithtooltip($langs->trans('ForcePriceToZero'), $langs->trans('help_ForcePriceToZero'),2,1,img_help(1,'')),
	        'help_forcePriceToZero' => $form->textwithtooltip($langs->trans('ForcePriceToZero'), $langs->trans('help_ForcePriceToZero'),2,1,img_help(1,'')),
	        'help_AddDesc' => $form->textwithtooltip($langs->trans('AddDesc'), $langs->trans('help_AddDesc'),2,1,img_help(1,'')),
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