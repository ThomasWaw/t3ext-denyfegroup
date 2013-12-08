<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2011-2013 b:dreizehn GmbH <typo3@b13.de>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * Main function that hooks into TYPO3
 *
 * @author	b:dreizehn GmbH <typo3@b13.de>
 * @package
 */
class tx_denyfegroup_main {

	// DB field name, can be configurable later on
	protected $fieldName = 'fe_group_deny';

	// usergroups (without -1, 0, etc)
	protected $usergroups;


	/**
	 * hook to modify the pages "enablecolumn" set in tslib_fe for
	 * t3lib_page where_groupAccess
	 */
	public function determineIdPostProc($params, &$pObj) {
		$usergroups = $this->getUsergroups();
		if (count($usergroups)) {
			$sql = $this->getMultipleGroupsWhereClause($this->fieldName, 'pages');
			$pObj->sys_page->where_groupAccess .= $this->getMultipleGroupsWhereClause($this->fieldName, 'pages');
		}
	}


	/**
	 *
	 * 	$_params = array(
	 *	    'table' => $table,
	 *	    'show_hidden' => $show_hidden,
	 *	    'ignore_array' => $ignore_array,
	 *	    'ctrl' => $ctrl
	 *	);
	 *
	 * @return string	an additional SQL query where
	 */
	public function addEnableColumn(&$params, $pObj) {
		$sql = '';
		if ($params['table'] == 'pages' || $params['table'] == 'tt_content') {
			$usergroups = $this->getUsergroups();
			if (count($usergroups)) {
				$sql = $this->getMultipleGroupsWhereClause($this->fieldName, $params['table']);
			}
		}
		return $sql;
	}

	/**
	 * inverse function of t3lib_page::getMultipleGroupsWhereClause()
	 *
	 * Creating where-clause for checking group access to elements in enableFields function
	 *
	 * @param	string		Field with group list
	 * @param	string		Table name
	 * @return	string		AND sql-clause
	 * @see enableFields()
	 */
	protected function getMultipleGroupsWhereClause($field, $table)	{
		$orChecks = array(
			$field . '=\'\'',	// If the field is empty, then OK
			$field . ' IS NULL',	// If the field is NULL, then OK
			$field . '=\'0\'',	// If the field contsains zero, then OK
		);

		$andChecks = array();
		foreach ($this->usergroups as $value) {
			$check = $GLOBALS['TYPO3_DB']->listQuery($field, $value, $table);
//			$check = str_replace(' LIKE ', ' NOT LIKE ', $GLOBALS['TYPO3_DB']->listQuery($field, $value, $table));
			$andChecks[] = '(NOT ' . $check . ')';
		}

		return ' AND ((' . implode(' OR ', $orChecks) . ') OR (' . implode(' AND ', $andChecks) . '))';
	}


	/**
	 * get all usergruops without the pseudo usergroups 0,-1,-2
	 * do some basic caching for this session
	 */
	protected function getUsergroups() {
		if (!is_array($this->usergroups)) {
			$this->usergroups = array();
			$allgroups = t3lib_div::intExplode(',', $GLOBALS['TSFE']->gr_list);
			foreach ($allgroups as $groupId) {
				if ($groupId <= 0) continue;
				$this->usergroups[] = $groupId;
			}
		}
		return $this->usergroups;
	}

}


?>