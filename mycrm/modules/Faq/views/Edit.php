<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Faq_Edit_View extends Mycrm_Edit_View {

	public function process(Mycrm_Request $request) {
		$viewer = $this->getViewer ($request);
		$moduleName = $request->getModule();
		$record = $request->get('record');

		if(!empty($record) && $request->get('isDuplicate') == true) {
			$recordModel = Mycrm_Record_Model::getInstanceById($record, $moduleName);
			$viewer->assign('MODE', '');
		} else if(!empty($record)) {
			$recordModel = Mycrm_Record_Model::getInstanceById($record, $moduleName);
			$viewer->assign('RECORD_ID', $record);
			$viewer->assign('MODE', 'edit');
		} else {
			$recordModel = Mycrm_Record_Model::getCleanInstance($moduleName);
			$viewer->assign('MODE', '');

			$parentId = $request->get('parentId');
			$parentModule = $request->get('parentModule');
			if ($parentId && $parentModule === 'HelpDesk') {
				$parentRecordModel = Mycrm_Record_Model::getInstanceById($parentId, $parentModule);
				$recordModel = Faq_Record_Model::getInstanceFromHelpDesk($parentRecordModel);
			}
		}

		$moduleModel = $recordModel->getModule();
		$fieldList = $moduleModel->getFields();
		$requestFieldList = array_intersect_key($request->getAll(), $fieldList);

		foreach($requestFieldList as $fieldName=>$fieldValue) {
			$fieldModel = $fieldList[$fieldName];
			if($fieldModel->isEditable()) {
				$recordModel->set($fieldName, $fieldModel->getDBInsertValue($fieldValue));
			}
		}
		$recordStructureInstance = Mycrm_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Mycrm_RecordStructure_Model::RECORD_STRUCTURE_MODE_EDIT);

		$viewMode = $request->get('view_mode');
		if(!empty($viewMode)) {
			$viewer->assign('VIEW_MODE', $viewMode);
		}
		
		$picklistDependencyDatasource = Mycrm_DependencyPicklist::getPicklistDependencyDatasource($moduleName);

		$viewer->assign('PICKIST_DEPENDENCY_DATASOURCE',Zend_Json::encode($picklistDependencyDatasource));
		$viewer->assign('RECORD_STRUCTURE_MODEL', $recordStructureInstance);
		$viewer->assign('RECORD_STRUCTURE', $recordStructureInstance->getStructure());
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('CURRENTDATE', date('Y-n-j'));
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());

		$isRelationOperation = $request->get('relationOperation');

		//if it is relation edit
		$viewer->assign('IS_RELATION_OPERATION', $isRelationOperation);
		if($isRelationOperation) {
			$viewer->assign('SOURCE_MODULE', $request->get('sourceModule'));
			$viewer->assign('SOURCE_RECORD', $request->get('sourceRecord'));
		}

		$viewer->assign('MAX_UPLOAD_LIMIT_MB', Mycrm_Util_Helper::getMaxUploadSize());
		$viewer->assign('MAX_UPLOAD_LIMIT', vglobal('upload_maxsize'));
		$viewer->view('EditView.tpl', $moduleName);
	}
}