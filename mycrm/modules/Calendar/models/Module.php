<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

vimport('~~/vtlib/Mycrm/Module.php');

/**
 * Calendar Module Model Class
 */
class Calendar_Module_Model extends Mycrm_Module_Model {

	/**
	 * Function returns the default view for the Calendar module
	 * @return <String>
	 */
	public function getDefaultViewName() {
		return $this->getCalendarViewName();
	}

	/**
	 * Function returns the calendar view name
	 * @return <String>
	 */
	public function getCalendarViewName() {
		return 'Calendar';
	}

	/**
	 *  Function returns the url for Calendar view
	 * @return <String>
	 */
	public function getCalendarViewUrl() {
		return 'index.php?module='.$this->get('name').'&view='.$this->getCalendarViewName();
	}

	/**
	 * Function to check whether the module is summary view supported
	 * @return <Boolean> - true/false
	 */
	public function isSummaryViewSupported() {
		return false;
	}

	/**
	 * Function returns the URL for creating Events
	 * @return <String>
	 */
	public function getCreateEventRecordUrl() {
		return 'index.php?module='.$this->get('name').'&view='.$this->getEditViewName().'&mode=Events';
	}

	/**
	 * Function returns the URL for creating Task
	 * @return <String>
	 */
	public function getCreateTaskRecordUrl() {
		return 'index.php?module='.$this->get('name').'&view='.$this->getEditViewName().'&mode=Calendar';
	}

    /**
     * Function that returns related list header fields that will be showed in the Related List View
     * @return <Array> returns related fields list.
     */
    public function getRelatedListFields() {
		$entityInstance = CRMEntity::getInstance($this->getName());
        $list_fields = $entityInstance->list_fields;
        $list_fields_name = $entityInstance->list_fields_name;
        $relatedListFields = array();
        foreach ($list_fields as $key => $fieldInfo) {
            foreach ($fieldInfo as $columnName) {
                if(array_key_exists($key, $list_fields_name)){
                    if($columnName == 'lastname' || $columnName == 'activity' || $columnName == 'due_date' || $columnName == 'time_end') continue;
					if ($columnName == 'status') $relatedListFields[$columnName] = 'taskstatus';
					else $relatedListFields[$columnName] = $list_fields_name[$key];
                }
            }
        }
        return $relatedListFields;
	}

	/**
	 * Function to get list of field for related list
	 * @return <Array> empty array
	 */
	public function getConfigureRelatedListFields() {
        return array();
	}

	/**
	 * Function to get list of field for summary view
	 * @return <Array> empty array
	 */
	public function getSummaryViewFieldsList() {
		return array();
	}
	/**
	 * Function to get the Quick Links for the module
	 * @param <Array> $linkParams
	 * @return <Array> List of Mycrm_Link_Model instances
	 */
	public function getSideBarLinks($linkParams) {
		$linkTypes = array('SIDEBARLINK', 'SIDEBARWIDGET');
		$links = Mycrm_Link_Model::getAllByType($this->getId(), $linkTypes, $linkParams);

		$quickLinks = array(
			array(
				'linktype' => 'SIDEBARLINK',
				'linklabel' => 'LBL_CALENDAR_VIEW',
				'linkurl' => $this->getCalendarViewUrl(),
				'linkicon' => '',
			),
			array(
				'linktype' => 'SIDEBARLINK',
				'linklabel' => 'LBL_SHARED_CALENDAR',
				'linkurl' => $this->getSharedCalendarViewUrl(),
				'linkicon' => '',
			),
			array(
				'linktype' => 'SIDEBARLINK',
				'linklabel' => 'LBL_RECORDS_LIST',
				'linkurl' => $this->getListViewUrl(),
				'linkicon' => '',
			),
		);
		foreach($quickLinks as $quickLink) {
			$links['SIDEBARLINK'][] = Mycrm_Link_Model::getInstanceFromValues($quickLink);
		}

		$quickWidgets = array();

		if ($linkParams['ACTION'] == 'Calendar') {
			$quickWidgets[] = array(
				'linktype' => 'SIDEBARWIDGET',
				'linklabel' => 'LBL_ACTIVITY_TYPES',
				'linkurl' => 'module='.$this->get('name').'&view=ViewTypes&mode=getViewTypes',
				'linkicon' => ''
			);
		}

		if ($linkParams['ACTION'] == 'SharedCalendar') {
			$quickWidgets[] = array(
				'linktype' => 'SIDEBARWIDGET',
				'linklabel' => 'LBL_ADDED_CALENDARS',
				'linkurl' => 'module='.$this->get('name').'&view=ViewTypes&mode=getSharedUsersList',
				'linkicon' => ''
			);
		}

		$quickWidgets[] = array(
			'linktype' => 'SIDEBARWIDGET',
			'linklabel' => 'LBL_RECENTLY_MODIFIED',
			'linkurl' => 'module='.$this->get('name').'&view=IndexAjax&mode=showActiveRecords',
			'linkicon' => ''
		);

		foreach($quickWidgets as $quickWidget) {
			$links['SIDEBARWIDGET'][] = Mycrm_Link_Model::getInstanceFromValues($quickWidget);
		}

		return $links;
	}

	/**
	 * Function returns the url that shows Calendar Import result
	 * @return <String> url
	 */
	public function getImportResultUrl() {
		return 'index.php?module='.$this->getName().'&view=ImportResult';
	}

	/**
	 * Function to get export query
	 * @return <String> query;
	 */
	public function getExportQuery($focus = '', $where = '') {
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$userId = $currentUserModel->getId();
		$query = "SELECT mycrm_activity.*, mycrm_crmentity.description, mycrm_activity_reminder.reminder_time FROM mycrm_activity
					INNER JOIN mycrm_crmentity ON mycrm_activity.activityid = mycrm_crmentity.crmid
					LEFT JOIN mycrm_activity_reminder ON mycrm_activity_reminder.activity_id = mycrm_activity.activityid AND mycrm_activity_reminder.recurringid = 0
					WHERE mycrm_crmentity.deleted = 0 AND mycrm_crmentity.smownerid = $userId AND mycrm_activity.activitytype NOT IN ('Emails')";
		return $query;
	}

	/**
	 * Function to set event fields for export
	 */
	public function setEventFieldsForExport() {
		$moduleFields = array_flip($this->getColumnFieldMapping());
		$userModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();

		$keysToReplace = array('taskpriority');
		$keysValuesToReplace = array('taskpriority' => 'priority');

		foreach($moduleFields as $fieldName => $fieldValue) {
            $fieldModel = Mycrm_Field_Model::getInstance($fieldName, $this);
            if($fieldName != 'id' && $fieldModel->getPermissions()) {
				if(!in_array($fieldName, $keysToReplace)) {
					$eventFields[$fieldName] = 'yes';
				} else {
					$eventFields[$keysValuesToReplace[$fieldName]] = 'yes';
				}
			}
		}
		$this->set('eventFields', $eventFields);
	}

	/**
	 * Function to set todo fields for export
	 */
	public function setTodoFieldsForExport() {
		$moduleFields = array_flip($this->getColumnFieldMapping());
		$userModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();

		$keysToReplace = array('taskpriority', 'taskstatus');
		$keysValuesToReplace = array('taskpriority' => 'priority', 'taskstatus' => 'status');

		foreach($moduleFields as $fieldName => $fieldValue) {
			$fieldModel = Mycrm_Field_Model::getInstance($fieldName, $this);
            if($fieldName != 'id' && $fieldModel->getPermissions()) {
				if(!in_array($fieldName, $keysToReplace)) {
					$todoFields[$fieldName] = 'yes';
				} else {
					$todoFields[$keysValuesToReplace[$fieldName]] = 'yes';
				}
			}
		}
		$this->set('todoFields', $todoFields);
	}

	/**
	 * Function to get the url to view Details for the module
	 * @return <String> - url
	 */
	public function getDetailViewUrl($id) {
		return 'index.php?module=Calendar&view='.$this->getDetailViewName().'&record='.$id;
	}

	/**
	* To get the lists of sharedids
	* @param $id --  user id
	* @returns <Array> $sharedids
	*/
	public static function getCaledarSharedUsers($id){
		$db = PearDatabase::getInstance();

        $query = "SELECT mycrm_users.user_name, mycrm_sharedcalendar.* FROM mycrm_sharedcalendar
				LEFT JOIN mycrm_users ON mycrm_sharedcalendar.sharedid=mycrm_users.id WHERE userid=?";
        $result = $db->pquery($query, array($id));
        $rows = $db->num_rows($result);

		$sharedids = Array();
		$focus = new Users();
        for($i=0; $i<$rows; $i++){
			$sharedid = $db->query_result($result,$i,'sharedid');
			$userId = $db->query_result($result, $i, 'userid');
			$sharedids[$sharedid]=$userId;
        }
		return $sharedids;
	}

	/**
	* To get the lists of sharedids
	* @param $id --  user id
	* @returns <Array> $sharedids
	*/
	public static function getSharedUsersOfCurrentUser($id){
		$db = PearDatabase::getInstance();

		$query = "SELECT mycrm_users.first_name,mycrm_users.last_name, mycrm_users.id as userid
			FROM mycrm_sharedcalendar RIGHT JOIN mycrm_users ON mycrm_sharedcalendar.userid=mycrm_users.id and status= 'Active'
			WHERE sharedid=? OR (mycrm_users.status='Active' AND mycrm_users.calendarsharedtype='public' AND mycrm_users.id <> ?);";
        $result = $db->pquery($query, array($id, $id));
        $rows = $db->num_rows($result);

		$userIds = Array();
        for($i=0; $i<$rows; $i++){
			$id = $db->query_result($result,$i,'userid');
			$userName = $db->query_result($result,$i,'first_name').' '.$db->query_result($result,$i,'last_name');
			$userIds[$id] =$userName;
        }

		return $sharedids[$id] = $userIds;
	}

	/**
	* To get the lists of sharedids and colors
	* @param $id --  user id
	* @returns <Array> $sharedUsers
	*/
	public static function getSharedUsersInfoOfCurrentUser($id){
		$db = PearDatabase::getInstance();

		$query = "SELECT shareduserid,color,visible FROM mycrm_shareduserinfo where userid = ?";
        $result = $db->pquery($query, array($id));
        $rows = $db->num_rows($result);

		$sharedUsers = Array();
        for($i=0; $i<$rows; $i++){
			$sharedUserId = $db->query_result($result,$i,'shareduserid');
			$color = $db->query_result($result,$i,'color');
			$visible = $db->query_result($result,$i,'visible');
			$sharedUsers[$sharedUserId] = array('visible' => $visible , 'color' => $color);
        }

		return $sharedUsers;
	}

	/**
	* To get the lists of sharedids and colors
	* @param $id --  user id
	* @returns <Array> $sharedUsers
	*/
	public static function getCalendarViewTypes($id){
		$db = PearDatabase::getInstance();

		$query = "SELECT * FROM mycrm_calendar_user_activitytypes 
			INNER JOIN mycrm_calendar_default_activitytypes on mycrm_calendar_default_activitytypes.id=mycrm_calendar_user_activitytypes.defaultid 
			WHERE mycrm_calendar_user_activitytypes.userid=?";
        $result = $db->pquery($query, array($id));
        $rows = $db->num_rows($result);

		$calendarViewTypes = Array();
        for($i=0; $i<$rows; $i++){
			$activityTypes = $db->query_result_rowdata($result, $i);
			$moduleInstance = Mycrm_Module::getInstance($activityTypes['module']);
			$fieldInstance = Mycrm_Field::getInstance($activityTypes['fieldname'], $moduleInstance);
			if($fieldInstance) {
				$fieldLabel = $fieldInstance->label;
			} else {
				$fieldLabel = $activityTypes['fieldname'];
			}
			if($activityTypes['visible'] == '1') {
				$calendarViewTypes['visible'][] = array('module'=>$activityTypes['module'], 'fieldname'=>$activityTypes['fieldname'], 'fieldlabel'=>$fieldLabel, 'visible' => $activityTypes['visible'] , 'color' => $activityTypes['color']);
			} else {
				$calendarViewTypes['invisible'][] = array('module'=>$activityTypes['module'], 'fieldname'=>$activityTypes['fieldname'], 'fieldlabel'=>$fieldLabel, 'visible' => $activityTypes['visible'] , 'color' => $activityTypes['color']);
			}
        }
		return $calendarViewTypes;
	}

	/**
	 *  Function returns the url for Shared Calendar view
	 * @return <String>
	 */
	public function getSharedCalendarViewUrl() {
		return 'index.php?module='.$this->get('name').'&view=SharedCalendar';
	}

	/**
	 * Function to delete shared users
	 * @param type $currentUserId
	 */
	public function deleteSharedUsers($currentUserId){
		$db = PearDatabase::getInstance();
		$delquery = "DELETE FROM mycrm_sharedcalendar WHERE userid=?";
		$db->pquery($delquery, array($currentUserId));
	}

	/**
	 * Function to insert shared users
	 * @param type $currentUserId
	 * @param type $sharedIds
	 */
	public function insertSharedUsers($currentUserId, $sharedIds, $sharedType = FALSE){
		$db = PearDatabase::getInstance();
		foreach ($sharedIds as $sharedId) {
			if($sharedId != $currentUserId) {
				$sql = "INSERT INTO mycrm_sharedcalendar VALUES (?,?)";
				$db->pquery($sql, array($currentUserId, $sharedId));
			}
		}
	}

	/**
	 * Function to get shared type
	 * @param type $currentUserId
	 * @param type $sharedIds
	 */
	public function getSharedType($currentUserId){
		$db = PearDatabase::getInstance();

		$query = "SELECT calendarsharedtype FROM mycrm_users WHERE id=?";
        $result = $db->pquery($query, array($currentUserId));
		if($db->num_rows($result) > 0){
			$sharedType = $db->query_result($result,0,'calendarsharedtype');
        }
		return $sharedType;
	}

	/**
	 * Function to get Alphabet Search Field
	 */
	public function getAlphabetSearchField(){
		return 'subject';
	}

	/**
	 * Function to get the list of recently visisted records
	 * @param <Number> $limit
	 * @return <Array> - List of Calendar_Record_Model
	 */
	public function getRecentRecords($limit=10) {
		$db = PearDatabase::getInstance();

		$currentUserModel = Users_Record_Model::getCurrentUserModel();
        $deletedCondition = parent::getDeletedRecordCondition();
		$nonAdminQuery .= Users_Privileges_Model::getNonAdminAccessControlQuery($this->getName());

		$query = 'SELECT * FROM mycrm_crmentity ';
		if($nonAdminQuery){
			$query .= " INNER JOIN mycrm_activity ON mycrm_crmentity.crmid = mycrm_activity.activityid ".$nonAdminQuery;
		}
		$query .= ' WHERE setype=? AND '.$deletedCondition.' AND modifiedby = ? ORDER BY modifiedtime DESC LIMIT ?';
		$params = array($this->getName(), $currentUserModel->id, $limit);
		$result = $db->pquery($query, $params);
		$noOfRows = $db->num_rows($result);
		$recentRecords = array();
		for($i=0; $i<$noOfRows; ++$i) {
			$row = $db->query_result_rowdata($result, $i);
			$row['id'] = $row['crmid'];
			$recentRecords[$row['id']] = $this->getRecordFromArray($row);
		}
		return $recentRecords;
	}

	/**
	 * Function returns Calendar Reminder record models
	 * @return <Array of Calendar_Record_Model>
	 */
	public static function getCalendarReminder() {
		$db = PearDatabase::getInstance();
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$activityReminder = $currentUserModel->getCurrentUserActivityReminderInSeconds();
		$recordModels = array();

		if($activityReminder != '' ) {
			$currentTime = time();
			$date = date('Y-m-d', strtotime("+$activityReminder seconds", $currentTime));
			$time = date('H:i',   strtotime("+$activityReminder seconds", $currentTime));
			$reminderActivitiesResult = "SELECT reminderid, recordid FROM mycrm_activity_reminder_popup
            					INNER JOIN mycrm_activity on mycrm_activity.activityid = mycrm_activity_reminder_popup.recordid
								INNER JOIN mycrm_crmentity ON mycrm_activity_reminder_popup.recordid = mycrm_crmentity.crmid
								WHERE mycrm_activity_reminder_popup.status = 0
								AND mycrm_crmentity.smownerid = ? AND mycrm_crmentity.deleted = 0
								AND ((DATE_FORMAT(mycrm_activity_reminder_popup.date_start,'%Y-%m-%d') <= ?)
								AND (TIME_FORMAT(mycrm_activity_reminder_popup.time_start,'%H:%i') <= ?))
                                AND mycrm_activity.eventstatus <> 'Held' AND (mycrm_activity.status <> 'Completed' OR mycrm_activity.status IS NULL) LIMIT 20";
			$result = $db->pquery($reminderActivitiesResult, array($currentUserModel->getId(), $date, $time));
			$rows = $db->num_rows($result);
			for($i=0; $i<$rows; $i++) {
				$recordId = $db->query_result($result, $i, 'recordid');
				$recordModels[] = Mycrm_Record_Model::getInstanceById($recordId, 'Calendar');
			}
		}
		return $recordModels;
	}

	/**
	 * Function gives fields based on the type
	 * @param <String> $type - field type
	 * @return <Array of Mycrm_Field_Model> - list of field models
	 */
	public function getFieldsByType($type) {
		$restrictedField = array('picklist'=>array('eventstatus', 'recurringtype', 'visibility', 'duration_minutes'));

        if(!is_array($type)) {
            $type = array($type);
        }
		$fields = $this->getFields();
		$fieldList = array();
		foreach($fields as $field) {
			$fieldType = $field->getFieldDataType();
			if(in_array($fieldType,$type)) {
				$fieldName = $field->getName();
				if($fieldType == 'picklist' && in_array($fieldName, $restrictedField[$fieldType])) {
				} else {
					$fieldList[$fieldName] = $field;
				}
			}
		}
		return $fieldList;
	}

	/**
	 * Function returns Settings Links
	 * @return Array
	 */
	public function getSettingLinks() {
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$settingLinks = array();

		if($currentUserModel->isAdminUser()) {
			$settingLinks[] = array(
					'linktype' => 'LISTVIEWSETTING',
					'linklabel' => 'LBL_EDIT_FIELDS',
					'linkurl' => 'index.php?parent=Settings&module=LayoutEditor&sourceModule='.$this->getName(),
					'linkicon' => Mycrm_Theme::getImagePath('LayoutEditor.gif')
			);

            $settingLinks[] = array(
					'linktype' => 'LISTVIEWSETTING',
					'linklabel' => 'LBL_EDIT_PICKLIST_VALUES',
					'linkurl' => 'index.php?parent=Settings&module=Picklist&view=Index&source_module='.$this->getName(),
					'linkicon' => ''
			);
		}
		return $settingLinks;
	}

	/**
	 * Function to get orderby sql from orderby field
	 */
	public function getOrderBySql($orderBy){
		 if($orderBy == 'status'){
			 return $orderBy;
		 }
		 return parent::getOrderBySql($orderBy);
	}
}
