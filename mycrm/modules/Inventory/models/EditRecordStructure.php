<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

/**
 * Inventory Modules Edit View Record Structure Model
 */
class Inventory_EditRecordStructure_Model extends Mycrm_EditRecordStructure_Model {

	/**
	 * Function to get the values in stuctured format
	 * @return <array> - values in structure array('block'=>array(fieldinfo));
	 */
	public function getStructure() {
		if(!empty($this->structuredValues)) {
			return $this->structuredValues;
		}

		$values = array();
		$recordModel = $this->getRecord();
		$recordExists = !empty($recordModel);
        $recordId = $recordModel->getId();
		$moduleModel = $this->getModule();
		$blockModelList = $moduleModel->getBlocks();
		foreach($blockModelList as $blockLabel=>$blockModel) {
			$fieldModelList = $blockModel->getFields();
			if (!empty ($fieldModelList)) {
				$values[$blockLabel] = array();
				foreach($fieldModelList as $fieldName=>$fieldModel) {
					if($fieldModel->isEditable()) {
						if($recordExists) {
							$fieldValue = $recordModel->get($fieldName,null);
                            if($fieldName == 'terms_conditions' && $fieldValue == '') {
								$fieldValue = $recordModel->getInventoryTermsandConditions();
							} else if($fieldValue == '') {
                                $defaultValue = $fieldModel->getDefaultFieldValue();
                                if(!empty($defaultValue) && !$recordId)
                                    $fieldValue = $defaultValue;
                            }
							$fieldModel->set('fieldvalue', $fieldValue);
						}
						$values[$blockLabel][$fieldName] = $fieldModel;
					}
				}
			}
		}
		$this->structuredValues = $values;
		return $values;
	}
}
