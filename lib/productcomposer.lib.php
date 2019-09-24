<?php
/* <one line to give the program's name and a brief idea of what it does.>
 * Copyright (C) 2015 ATM Consulting <support@atm-consulting.fr>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *	\file		lib/productcomposer.lib.php
 *	\ingroup	productcomposer
 *	\brief		This file is an example module library
 *				Put some comments here
 */

function productcomposerAdminPrepareHead()
{
    global $langs, $conf;

    $langs->load("productcomposer@productcomposer");

    $h = 0;
    $head = array();

    $head[$h][0] = dol_buildpath("/productcomposer/admin/productcomposer_setup.php", 1);
    $head[$h][1] = $langs->trans("Parameters");
    $head[$h][2] = 'settings';
    $h++;
    $head[$h][0] = dol_buildpath("/productcomposer/admin/productcomposer_about.php", 1);
    $head[$h][1] = $langs->trans("About");
    $head[$h][2] = 'about';
    $h++;
    $head[$h][0] = dol_buildpath("/productcomposer/list.php", 1);
    $head[$h][1] = $langs->trans("Roadmaps");
    $head[$h][2] = 'roadmaps';
    $h++;

    // Show more tabs from modules
    // Entries must be declared in modules descriptor with line
    //$this->tabs = array(
    //	'entity:+tabname:Title:@productcomposer:/productcomposer/mypage.php?id=__ID__'
    //); // to add new tab
    //$this->tabs = array(
    //	'entity:-tabname:Title:@productcomposer:/productcomposer/mypage.php?id=__ID__'
    //); // to remove a tab
    complete_head_from_modules($conf, $langs, $object, $head, $h, 'productcomposer');

    return $head;
}

/**
 * Return array of tabs to used on pages for third parties cards.
 *
 * @param 	Tproductcomposer	$object		Object company shown
 * @return 	array				Array of tabs
 */
function roadmap_prepare_head()
{
    global $langs, $conf;

    $langs->load("productcomposer@productcomposer");

    $h = 0;
    $head = array();

/*
    $head[$h][0] = dol_buildpath("/productcomposer/list.php", 1);
    $head[$h][1] = $langs->trans("Roadmaps");
    $head[$h][2] = 'roadmaps';
    $h++;*/

    // Show more tabs from modules
    // Entries must be declared in modules descriptor with line
    //$this->tabs = array(
    //	'entity:+tabname:Title:@productcomposer:/productcomposer/mypage.php?id=__ID__'
    //); // to add new tab
    //$this->tabs = array(
    //	'entity:-tabname:Title:@productcomposer:/productcomposer/mypage.php?id=__ID__'
    //); // to remove a tab
    complete_head_from_modules($conf, $langs, $object, $head, $h, 'productcomposer');

    return $head;
}

function getFormConfirmproductcomposer(&$PDOdb, &$form, &$object, $action)
{
    global $langs,$conf,$user;

    $formconfirm = '';

    if ($action == 'validate' && !empty($user->rights->productcomposer->write))
    {
        $text = $langs->trans('ConfirmValidateproductcomposer', $object->ref);
        $formconfirm = $form->formconfirm($_SERVER['PHP_SELF'] . '?id=' . $object->id, $langs->trans('Validateproductcomposer'), $text, 'confirm_validate', '', 0, 1);
    }
    elseif ($action == 'delete' && !empty($user->rights->productcomposer->write))
    {
        $text = $langs->trans('ConfirmDeleteproductcomposer');
        $formconfirm = $form->formconfirm($_SERVER['PHP_SELF'] . '?id=' . $object->id, $langs->trans('Deleteproductcomposer'), $text, 'confirm_delete', '', 0, 1);
    }
    elseif ($action == 'clone' && !empty($user->rights->productcomposer->write))
    {
        $text = $langs->trans('ConfirmCloneproductcomposer', $object->ref);
        $formconfirm = $form->formconfirm($_SERVER['PHP_SELF'] . '?id=' . $object->id, $langs->trans('Cloneproductcomposer'), $text, 'confirm_clone', '', 0, 1);
    }

    return $formconfirm;
}



/**
 *      Creates HTML units selector (code => label)
 *
 *      @param	string	$selected       Preselected Unit ID
 *      @param  string	$htmlname       Select name
 *      @param	int		$showempty		Add a nempty line
 * 		@return	string                  HTML select
 */
function selectUnits($selected = '', $htmlname = 'units', $showempty = 0, $type = false, $moretags = '')
{
	global $langs, $db;

	$langs->load('products');
	$langs->load('other');

	$return= '<select class="flat" id="'.$htmlname.'" name="'.$htmlname.'" '.$moretags.'>';

	$sql = 'SELECT rowid, label, code from '.MAIN_DB_PREFIX.'c_units';
	$sql.= ' WHERE active > 0';
	$sql.= ' AND unit_type = \''.$db->escape($type).'\'';

	$resql = $db->query($sql);
	if($resql && $db->num_rows($resql) > 0)
	{
		if ($showempty) $return .= '<option value="none"></option>';

		while($res = $db->fetch_object($resql))
		{
			$unitLabel = $langs->trans($res->label);

			if (! empty($langs->tab_translate['unit'.$res->code]))	// check if Translation is available before
			{
				$unitLabel = $langs->trans('unit'.$res->code)!=$res->label?$langs->trans('unit'.$res->code):$res->label;
			}

			if ($selected == $res->rowid)
			{
				$return.='<option value="'.$res->rowid.'" selected>'.$unitLabel.'</option>';
			}
			else
			{
				$return.='<option value="'.$res->rowid.'">'.$unitLabel.'</option>';
			}
		}
		$return.='</select>';
	}
	return $return;
}

function getScaleOfUnitPow($id)
{
	global $db;
	$dbtool = new PCDbTool($db);
	$scale = $dbtool->getvalue('SELECT scale from '.MAIN_DB_PREFIX.'c_units WHERE rowid = '.intval($id));

	return pow ( 10, doubleval($scale));
}
