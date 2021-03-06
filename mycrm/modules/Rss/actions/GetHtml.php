<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Rss_GetHtml_Action extends Mycrm_Action_Controller {

	 function checkPermission(Mycrm_Request $request) {
		$moduleName = $request->getModule();
		$record = $request->get('record');

		$currentUserPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if(!$currentUserPrivilegesModel->isPermitted($moduleName, 'ListView', $record)) {
			throw new AppException('LBL_PERMISSION_DENIED');
		}
	}

	public function process(Mycrm_Request $request) {
		$module = $request->get('module');
        $url = $request->get('url');
        $recordModel = Rss_Record_Model::getCleanInstance($module);
        $html = $recordModel->getHtmlFromUrl($url);

		$response = new Mycrm_Response();
		$response->setResult(array('html'=>$html));
		$response->emit();
	}
}
