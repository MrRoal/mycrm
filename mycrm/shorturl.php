<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

//Overrides GetRelatedList : used to get related query
//TODO : Eliminate below hacking solution
include_once 'include/Webservices/Relation.php';

include_once 'vtlib/Mycrm/Module.php';
include_once dirname(__FILE__) . '/includes/Loader.php';

vimport ('includes.runtime.EntryPoint');

Mycrm_ShortURL_Helper::handle(vtlib_purify($_REQUEST['id']));