<?php
/* Copyright (C) 2017  Laurent Destailleur <eldy@users.sourceforge.net>
 * Copyright (C) ---Put here your own copyright and developer email---
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file        class/general.class.php
 * \ingroup     tab
 * \brief       This file is a CRUD class file for General (Create/Read/Update/Delete)
 */



// Put here all includes required by your class file
require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';
require_once DOL_DOCUMENT_ROOT.'/compta/facture/class/facturestats.class.php';

/**
 * Class for General
 */
class General extends FactureStats
{
	/**
	 * @var string ID of module.
	 */
	public $module = 'tab';

	/**
	 * @var string ID to identify managed object.
	 */
	public $element = 'general';

	/**
	 * @var string Name of table without prefix where object is stored. This is also the key used for extrafields management.
	 */
	public $table_element = 'tab_general';

	/**
	 * @var int  Does this object support multicompany module ?
	 * 0=No test on entity, 1=Test with field entity, 'field@table'=Test with link by field@table
	 */
	public $ismultientitymanaged = 0;

	/**
	 * @var int  Does object support extrafields ? 0=No, 1=Yes
	 */
	public $isextrafieldmanaged = 1;

	/**
	 * @var string String with name of icon for general. Must be the part after the 'object_' into object_general.png
	 */
	public $picto = 'general@tab';


	const STATUS_DRAFT = 0;
	const STATUS_VALIDATED = 1;
	const STATUS_CANCELED = 9;


	/**
	 *  'type' field format ('integer', 'integer:ObjectClass:PathToClass[:AddCreateButtonOrNot[:Filter]]', 'sellist:TableName:LabelFieldName[:KeyFieldName[:KeyFieldParent[:Filter]]]', 'varchar(x)', 'double(24,8)', 'real', 'price', 'text', 'text:none', 'html', 'date', 'datetime', 'timestamp', 'duration', 'mail', 'phone', 'url', 'password')
	 *         Note: Filter can be a string like "(t.ref:like:'SO-%') or (t.date_creation:<:'20160101') or (t.nature:is:NULL)"
	 *  'label' the translation key.
	 *  'picto' is code of a picto to show before value in forms
	 *  'enabled' is a condition when the field must be managed (Example: 1 or '$conf->global->MY_SETUP_PARAM)
	 *  'position' is the sort order of field.
	 *  'notnull' is set to 1 if not null in database. Set to -1 if we must set data to null if empty ('' or 0).
	 *  'visible' says if field is visible in list (Examples: 0=Not visible, 1=Visible on list and create/update/view forms, 2=Visible on list only, 3=Visible on create/update/view form only (not list), 4=Visible on list and update/view form only (not create). 5=Visible on list and view only (not create/not update). Using a negative value means field is not shown by default on list but can be selected for viewing)
	 *  'noteditable' says if field is not editable (1 or 0)
	 *  'default' is a default value for creation (can still be overwrote by the Setup of Default Values if field is editable in creation form). Note: If default is set to '(PROV)' and field is 'ref', the default value will be set to '(PROVid)' where id is rowid when a new record is created.
	 *  'index' if we want an index in database.
	 *  'foreignkey'=>'tablename.field' if the field is a foreign key (it is recommanded to name the field fk_...).
	 *  'searchall' is 1 if we want to search in this field when making a search from the quick search button.
	 *  'isameasure' must be set to 1 if you want to have a total on list for this field. Field type must be summable like integer or double(24,8).
	 *  'css' and 'cssview' and 'csslist' is the CSS style to use on field. 'css' is used in creation and update. 'cssview' is used in view mode. 'csslist' is used for columns in lists. For example: 'maxwidth200', 'wordbreak', 'tdoverflowmax200'
	 *  'help' is a 'TranslationString' to use to show a tooltip on field. You can also use 'TranslationString:keyfortooltiponlick' for a tooltip on click.
	 *  'showoncombobox' if value of the field must be visible into the label of the combobox that list record
	 *  'disabled' is 1 if we want to have the field locked by a 'disabled' attribute. In most cases, this is never set into the definition of $fields into class, but is set dynamically by some part of code.
	 *  'arraykeyval' to set list of value if type is a list of predefined values. For example: array("0"=>"Draft","1"=>"Active","-1"=>"Cancel")
	 *  'autofocusoncreate' to have field having the focus on a create form. Only 1 field should have this property set to 1.
	 *  'comment' is not used. You can store here any text of your choice. It is not used by application.
	 *
	 *  Note: To have value dynamic, you can set value to 0 in definition and edit the value on the fly into the constructor.
	 */

	// BEGIN MODULEBUILDER PROPERTIES
	/**
	 * @var array  Array with all fields and their property. Do not use it as a static var. It may be modified by constructor.
	 */
	public $fields=array(
		'rowid' => array('type'=>'integer', 'label'=>'TechnicalID', 'enabled'=>'1', 'position'=>1, 'notnull'=>1, 'visible'=>0, 'noteditable'=>'1', 'index'=>1, 'css'=>'left', 'comment'=>"Id"),
		'ref' => array('type'=>'varchar(128)', 'label'=>'Ref', 'enabled'=>'1', 'position'=>10, 'notnull'=>1, 'visible'=>4, 'noteditable'=>'1', 'default'=>'(PROV)', 'index'=>1, 'searchall'=>1, 'showoncombobox'=>'1', 'comment'=>"Reference of object"),
		'label' => array('type'=>'varchar(255)', 'label'=>'Label', 'enabled'=>'1', 'position'=>30, 'notnull'=>0, 'visible'=>1, 'searchall'=>1, 'css'=>'minwidth300', 'help'=>"Help text", 'showoncombobox'=>'1',),
		'amount' => array('type'=>'price', 'label'=>'Amount', 'enabled'=>'1', 'position'=>40, 'notnull'=>0, 'visible'=>1, 'default'=>'null', 'isameasure'=>'1', 'help'=>"Help text for amount",),
		'qty' => array('type'=>'real', 'label'=>'Qty', 'enabled'=>'1', 'position'=>45, 'notnull'=>0, 'visible'=>1, 'default'=>'0', 'isameasure'=>'1', 'css'=>'maxwidth75imp', 'help'=>"Help text for quantity",),
		'fk_soc' => array('type'=>'integer:Societe:societe/class/societe.class.php:1:status=1 AND entity IN (__SHARED_ENTITIES__)', 'label'=>'ThirdParty', 'enabled'=>'1', 'position'=>50, 'notnull'=>-1, 'visible'=>1, 'index'=>1, 'help'=>"LinkToThirparty",),
		'fk_project' => array('type'=>'integer:Project:projet/class/project.class.php:1', 'label'=>'Project', 'enabled'=>'1', 'position'=>52, 'notnull'=>-1, 'visible'=>-1, 'index'=>1,),
		'description' => array('type'=>'text', 'label'=>'Description', 'enabled'=>'1', 'position'=>60, 'notnull'=>0, 'visible'=>3,),
		'note_public' => array('type'=>'html', 'label'=>'NotePublic', 'enabled'=>'1', 'position'=>61, 'notnull'=>0, 'visible'=>0,),
		'note_private' => array('type'=>'html', 'label'=>'NotePrivate', 'enabled'=>'1', 'position'=>62, 'notnull'=>0, 'visible'=>0,),
		'date_creation' => array('type'=>'datetime', 'label'=>'DateCreation', 'enabled'=>'1', 'position'=>500, 'notnull'=>1, 'visible'=>-2,),
		'tms' => array('type'=>'timestamp', 'label'=>'DateModification', 'enabled'=>'1', 'position'=>501, 'notnull'=>0, 'visible'=>-2,),
		'fk_user_creat' => array('type'=>'integer:User:user/class/user.class.php', 'label'=>'UserAuthor', 'enabled'=>'1', 'position'=>510, 'notnull'=>1, 'visible'=>-2, 'foreignkey'=>'user.rowid',),
		'fk_user_modif' => array('type'=>'integer:User:user/class/user.class.php', 'label'=>'UserModif', 'enabled'=>'1', 'position'=>511, 'notnull'=>-1, 'visible'=>-2,),
		'last_main_doc' => array('type'=>'varchar(255)', 'label'=>'LastMainDoc', 'enabled'=>'1', 'position'=>600, 'notnull'=>0, 'visible'=>0,),
		'import_key' => array('type'=>'varchar(14)', 'label'=>'ImportId', 'enabled'=>'1', 'position'=>1000, 'notnull'=>-1, 'visible'=>-2,),
		'model_pdf' => array('type'=>'varchar(255)', 'label'=>'Model pdf', 'enabled'=>'1', 'position'=>1010, 'notnull'=>-1, 'visible'=>0,),
		'status' => array('type'=>'smallint', 'label'=>'Status', 'enabled'=>'1', 'position'=>1000, 'notnull'=>1, 'visible'=>1, 'index'=>1, 'arrayofkeyval'=>array('0'=>'Brouillon', '1'=>'Valid&eacute;', '9'=>'Annul&eacute;'),),
	);
	public $rowid;
	public $ref;
	public $label;
	public $amount;
	public $qty;
	public $fk_soc;
	public $fk_project;
	public $description;
	public $note_public;
	public $note_private;
	public $date_creation;
	public $tms;
	public $fk_user_creat;
	public $fk_user_modif;
	public $last_main_doc;
	public $import_key;
	public $model_pdf;
	public $status;


	// END MODULEBUILDER PROPERTIES


	// If this object has a subtable with lines

	// /**
	//  * @var string    Name of subtable line
	//  */
	// public $table_element_line = 'tab_generalline';

	// /**
	//  * @var string    Field with ID of parent key if this object has a parent
	//  */
	// public $fk_element = 'fk_general';

	// /**
	//  * @var string    Name of subtable class that manage subtable lines
	//  */
	// public $class_element_line = 'Generalline';

	// /**
	//  * @var array	List of child tables. To test if we can delete object.
	//  */
	// protected $childtables = array();

	// /**
	//  * @var array    List of child tables. To know object to delete on cascade.
	//  *               If name matches '@ClassNAme:FilePathClass;ParentFkFieldName' it will
	//  *               call method deleteByParentField(parentId, ParentFkFieldName) to fetch and delete child object
	//  */
	// protected $childtablesoncascade = array('tab_generaldet');

	// /**
	//  * @var GeneralLine[]     Array of subtable lines
	//  */
	// public $lines = array();



	/**
	 * Constructor
	 *
	 * @param DoliDb $db Database handler
	 */
	public function __construct(DoliDB $db)
	{
		global $conf, $langs;

		$this->db = $db;

		if (empty($conf->global->MAIN_SHOW_TECHNICAL_ID) && isset($this->fields['rowid'])) $this->fields['rowid']['visible'] = 0;
		if (empty($conf->multicompany->enabled) && isset($this->fields['entity'])) $this->fields['entity']['enabled'] = 0;

		// Example to show how to set values of fields definition dynamically
		/*if ($user->rights->tab->general->read) {
			$this->fields['myfield']['visible'] = 1;
			$this->fields['myfield']['noteditable'] = 0;
		}*/

		// Unset fields that are disabled
		foreach ($this->fields as $key => $val)
		{
			if (isset($val['enabled']) && empty($val['enabled']))
			{
				unset($this->fields[$key]);
			}
		}

		// Translate some data of arrayofkeyval
		if (is_object($langs))
		{
			foreach ($this->fields as $key => $val)
			{
				if (!empty($val['arrayofkeyval']) && is_array($val['arrayofkeyval']))
				{
					foreach ($val['arrayofkeyval'] as $key2 => $val2)
					{
						$this->fields[$key]['arrayofkeyval'][$key2] = $langs->trans($val2);
					}
				}
			}
		}
	}





	/**
	 * Create object into database
	 *
	 * @param  User $user      User that creates
	 * @param  bool $notrigger false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, Id of created object if OK
	 */
	public function create(User $user, $notrigger = false)
	{
		return $this->createCommon($user, $notrigger);
	}

	/**
	 * Clone an object into another one
	 *
	 * @param  	User 	$user      	User that creates
	 * @param  	int 	$fromid     Id of object to clone
	 * @return 	mixed 				New object created, <0 if KO
	 */
	public function createFromClone(User $user, $fromid)
	{
		global $langs, $extrafields;
		$error = 0;

		dol_syslog(__METHOD__, LOG_DEBUG);

		$object = new self($this->db);

		$this->db->begin();

		// Load source object
		$result = $object->fetchCommon($fromid);
		if ($result > 0 && !empty($object->table_element_line)) $object->fetchLines();

		// get lines so they will be clone
		//foreach($this->lines as $line)
		//	$line->fetch_optionals();

		// Reset some properties
		unset($object->id);
		unset($object->fk_user_creat);
		unset($object->import_key);

		// Clear fields
		if (property_exists($object, 'ref')) $object->ref = empty($this->fields['ref']['default']) ? "Copy_Of_".$object->ref : $this->fields['ref']['default'];
		if (property_exists($object, 'label')) $object->label = empty($this->fields['label']['default']) ? $langs->trans("CopyOf")." ".$object->label : $this->fields['label']['default'];
		if (property_exists($object, 'status')) { $object->status = self::STATUS_DRAFT; }
		if (property_exists($object, 'date_creation')) { $object->date_creation = dol_now(); }
		if (property_exists($object, 'date_modification')) { $object->date_modification = null; }
		// ...
		// Clear extrafields that are unique
		if (is_array($object->array_options) && count($object->array_options) > 0)
		{
			$extrafields->fetch_name_optionals_label($this->table_element);
			foreach ($object->array_options as $key => $option)
			{
				$shortkey = preg_replace('/options_/', '', $key);
				if (!empty($extrafields->attributes[$this->table_element]['unique'][$shortkey]))
				{
					//var_dump($key); var_dump($clonedObj->array_options[$key]); exit;
					unset($object->array_options[$key]);
				}
			}
		}

		// Create clone
		$object->context['createfromclone'] = 'createfromclone';
		$result = $object->createCommon($user);
		if ($result < 0) {
			$error++;
			$this->error = $object->error;
			$this->errors = $object->errors;
		}

		if (!$error)
		{
			// copy internal contacts
			if ($this->copy_linked_contact($object, 'internal') < 0)
			{
				$error++;
			}
		}

		if (!$error)
		{
			// copy external contacts if same company
			if (property_exists($this, 'socid') && $this->socid == $object->socid)
			{
				if ($this->copy_linked_contact($object, 'external') < 0)
					$error++;
			}
		}

		unset($object->context['createfromclone']);

		// End
		if (!$error) {
			$this->db->commit();
			return $object;
		} else {
			$this->db->rollback();
			return -1;
		}
	}

	/**
	 * Load object in memory from the database
	 *
	 * @param int    $id   Id object
	 * @param string $ref  Ref
	 * @return int         <0 if KO, 0 if not found, >0 if OK
	 */
	public function fetch($id, $ref = null)
	{
		$result = $this->fetchCommon($id, $ref);
		if ($result > 0 && !empty($this->table_element_line)) $this->fetchLines();
		return $result;
	}

	/**
	 * Load object lines in memory from the database
	 *
	 * @return int         <0 if KO, 0 if not found, >0 if OK
	 */
	public function fetchLines()
	{
		$this->lines = array();

		$result = $this->fetchLinesCommon();
		return $result;
	}


	/**
	 * Load list of objects in memory from the database.
	 *
	 * @param  string      $sortorder    Sort Order
	 * @param  string      $sortfield    Sort field
	 * @param  int         $limit        limit
	 * @param  int         $offset       Offset
	 * @param  array       $filter       Filter array. Example array('field'=>'valueforlike', 'customurl'=>...)
	 * @param  string      $filtermode   Filter mode (AND or OR)
	 * @return array|int                 int <0 if KO, array of pages if OK
	 */
	public function fetchAll($sortorder = '', $sortfield = '', $limit = 0, $offset = 0, array $filter = array(), $filtermode = 'AND')
	{
		global $conf;

		dol_syslog(__METHOD__, LOG_DEBUG);

		$records = array();

		$sql = 'SELECT ';
		$sql .= $this->getFieldList();
		$sql .= ' FROM '.MAIN_DB_PREFIX.$this->table_element.' as t';
		if (isset($this->ismultientitymanaged) && $this->ismultientitymanaged == 1) $sql .= ' WHERE t.entity IN ('.getEntity($this->table_element).')';
		else $sql .= ' WHERE 1 = 1';
		// Manage filter
		$sqlwhere = array();
		if (count($filter) > 0) {
			foreach ($filter as $key => $value) {
				if ($key == 't.rowid') {
					$sqlwhere[] = $key.'='.$value;
				} elseif (in_array($this->fields[$key]['type'], array('date', 'datetime', 'timestamp'))) {
					$sqlwhere[] = $key.' = \''.$this->db->idate($value).'\'';
				} elseif ($key == 'customsql') {
					$sqlwhere[] = $value;
				} elseif (strpos($value, '%') === false) {
					$sqlwhere[] = $key.' IN ('.$this->db->sanitize($this->db->escape($value)).')';
				} else {
					$sqlwhere[] = $key.' LIKE \'%'.$this->db->escape($value).'%\'';
				}
			}
		}
		if (count($sqlwhere) > 0) {
			$sql .= ' AND ('.implode(' '.$filtermode.' ', $sqlwhere).')';
		}

		if (!empty($sortfield)) {
			$sql .= $this->db->order($sortfield, $sortorder);
		}
		if (!empty($limit)) {
			$sql .= ' '.$this->db->plimit($limit, $offset);
		}

		$resql = $this->db->query($sql);
		if ($resql) {
			$num = $this->db->num_rows($resql);
			$i = 0;
			while ($i < ($limit ? min($limit, $num) : $num))
			{
				$obj = $this->db->fetch_object($resql);

				$record = new self($this->db);
				$record->setVarsFromFetchObj($obj);

				$records[$record->id] = $record;

				$i++;
			}
			$this->db->free($resql);

			return $records;
		} else {
			$this->errors[] = 'Error '.$this->db->lasterror();
			dol_syslog(__METHOD__.' '.join(',', $this->errors), LOG_ERR);

			return -1;
		}
	}

	/**
	 * Update object into database
	 *
	 * @param  User $user      User that modifies
	 * @param  bool $notrigger false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, >0 if OK
	 */
	public function update(User $user, $notrigger = false)
	{
		return $this->updateCommon($user, $notrigger);
	}

	/**
	 * Delete object in database
	 *
	 * @param User $user       User that deletes
	 * @param bool $notrigger  false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, >0 if OK
	 */
	public function delete(User $user, $notrigger = false)
	{
		return $this->deleteCommon($user, $notrigger);
		//return $this->deleteCommon($user, $notrigger, 1);
	}

	/**
	 *  Delete a line of object in database
	 *
	 *	@param  User	$user       User that delete
	 *  @param	int		$idline		Id of line to delete
	 *  @param 	bool 	$notrigger  false=launch triggers after, true=disable triggers
	 *  @return int         		>0 if OK, <0 if KO
	 */
	public function deleteLine(User $user, $idline, $notrigger = false)
	{
		if ($this->status < 0)
		{
			$this->error = 'ErrorDeleteLineNotAllowedByObjectStatus';
			return -2;
		}

		return $this->deleteLineCommon($user, $idline, $notrigger);
	}


	/**
	 *	Validate object
	 *
	 *	@param		User	$user     		User making status change
	 *  @param		int		$notrigger		1=Does not execute triggers, 0= execute triggers
	 *	@return  	int						<=0 if OK, 0=Nothing done, >0 if KO
	 */
	public function validate($user, $notrigger = 0)
	{
		global $conf, $langs;

		require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

		$error = 0;

		// Protection
		if ($this->status == self::STATUS_VALIDATED)
		{
			dol_syslog(get_class($this)."::validate action abandonned: already validated", LOG_WARNING);
			return 0;
		}

		/*if (! ((empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->tab->general->write))
		 || (! empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->tab->general->general_advance->validate))))
		 {
		 $this->error='NotEnoughPermissions';
		 dol_syslog(get_class($this)."::valid ".$this->error, LOG_ERR);
		 return -1;
		 }*/

		$now = dol_now();

		$this->db->begin();

		// Define new ref
		if (!$error && (preg_match('/^[\(]?PROV/i', $this->ref) || empty($this->ref))) // empty should not happened, but when it occurs, the test save life
		{
			$num = $this->getNextNumRef();
		} else {
			$num = $this->ref;
		}
		$this->newref = $num;

		if (!empty($num)) {
			// Validate
			$sql = "UPDATE ".MAIN_DB_PREFIX.$this->table_element;
			$sql .= " SET ref = '".$this->db->escape($num)."',";
			$sql .= " status = ".self::STATUS_VALIDATED;
			if (!empty($this->fields['date_validation'])) $sql .= ", date_validation = '".$this->db->idate($now)."'";
			if (!empty($this->fields['fk_user_valid'])) $sql .= ", fk_user_valid = ".$user->id;
			$sql .= " WHERE rowid = ".$this->id;

			dol_syslog(get_class($this)."::validate()", LOG_DEBUG);
			$resql = $this->db->query($sql);
			if (!$resql)
			{
				dol_print_error($this->db);
				$this->error = $this->db->lasterror();
				$error++;
			}

			if (!$error && !$notrigger)
			{
				// Call trigger
				$result = $this->call_trigger('GENERAL_VALIDATE', $user);
				if ($result < 0) $error++;
				// End call triggers
			}
		}

		if (!$error)
		{
			$this->oldref = $this->ref;

			// Rename directory if dir was a temporary ref
			if (preg_match('/^[\(]?PROV/i', $this->ref))
			{
				// Now we rename also files into index
				$sql = 'UPDATE '.MAIN_DB_PREFIX."ecm_files set filename = CONCAT('".$this->db->escape($this->newref)."', SUBSTR(filename, ".(strlen($this->ref) + 1).")), filepath = 'general/".$this->db->escape($this->newref)."'";
				$sql .= " WHERE filename LIKE '".$this->db->escape($this->ref)."%' AND filepath = 'general/".$this->db->escape($this->ref)."' and entity = ".$conf->entity;
				$resql = $this->db->query($sql);
				if (!$resql) { $error++; $this->error = $this->db->lasterror(); }

				// We rename directory ($this->ref = old ref, $num = new ref) in order not to lose the attachments
				$oldref = dol_sanitizeFileName($this->ref);
				$newref = dol_sanitizeFileName($num);
				$dirsource = $conf->tab->dir_output.'/general/'.$oldref;
				$dirdest = $conf->tab->dir_output.'/general/'.$newref;
				if (!$error && file_exists($dirsource))
				{
					dol_syslog(get_class($this)."::validate() rename dir ".$dirsource." into ".$dirdest);

					if (@rename($dirsource, $dirdest))
					{
						dol_syslog("Rename ok");
						// Rename docs starting with $oldref with $newref
						$listoffiles = dol_dir_list($conf->tab->dir_output.'/general/'.$newref, 'files', 1, '^'.preg_quote($oldref, '/'));
						foreach ($listoffiles as $fileentry)
						{
							$dirsource = $fileentry['name'];
							$dirdest = preg_replace('/^'.preg_quote($oldref, '/').'/', $newref, $dirsource);
							$dirsource = $fileentry['path'].'/'.$dirsource;
							$dirdest = $fileentry['path'].'/'.$dirdest;
							@rename($dirsource, $dirdest);
						}
					}
				}
			}
		}

		// Set new ref and current status
		if (!$error)
		{
			$this->ref = $num;
			$this->status = self::STATUS_VALIDATED;
		}

		if (!$error)
		{
			$this->db->commit();
			return 1;
		} else {
			$this->db->rollback();
			return -1;
		}
	}


	/**
	 *	Set draft status
	 *
	 *	@param	User	$user			Object user that modify
	 *  @param	int		$notrigger		1=Does not execute triggers, 0=Execute triggers
	 *	@return	int						<0 if KO, >0 if OK
	 */
	public function setDraft($user, $notrigger = 0)
	{
		// Protection
		if ($this->status <= self::STATUS_DRAFT)
		{
			return 0;
		}

		/*if (! ((empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->tab->write))
		 || (! empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->tab->tab_advance->validate))))
		 {
		 $this->error='Permission denied';
		 return -1;
		 }*/

		return $this->setStatusCommon($user, self::STATUS_DRAFT, $notrigger, 'GENERAL_UNVALIDATE');
	}

	/**
	 *	Set cancel status
	 *
	 *	@param	User	$user			Object user that modify
	 *  @param	int		$notrigger		1=Does not execute triggers, 0=Execute triggers
	 *	@return	int						<0 if KO, 0=Nothing done, >0 if OK
	 */
	public function cancel($user, $notrigger = 0)
	{
		// Protection
		if ($this->status != self::STATUS_VALIDATED)
		{
			return 0;
		}

		/*if (! ((empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->tab->write))
		 || (! empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->tab->tab_advance->validate))))
		 {
		 $this->error='Permission denied';
		 return -1;
		 }*/

		return $this->setStatusCommon($user, self::STATUS_CANCELED, $notrigger, 'GENERAL_CANCEL');
	}

	/**
	 *	Set back to validated status
	 *
	 *	@param	User	$user			Object user that modify
	 *  @param	int		$notrigger		1=Does not execute triggers, 0=Execute triggers
	 *	@return	int						<0 if KO, 0=Nothing done, >0 if OK
	 */
	public function reopen($user, $notrigger = 0)
	{
		// Protection
		if ($this->status != self::STATUS_CANCELED)
		{
			return 0;
		}

		/*if (! ((empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->tab->write))
		 || (! empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->tab->tab_advance->validate))))
		 {
		 $this->error='Permission denied';
		 return -1;
		 }*/

		return $this->setStatusCommon($user, self::STATUS_VALIDATED, $notrigger, 'GENERAL_REOPEN');
	}

	/**
	 *  Return a link to the object card (with optionaly the picto)
	 *
	 *  @param  int     $withpicto                  Include picto in link (0=No picto, 1=Include picto into link, 2=Only picto)
	 *  @param  string  $option                     On what the link point to ('nolink', ...)
	 *  @param  int     $notooltip                  1=Disable tooltip
	 *  @param  string  $morecss                    Add more css on link
	 *  @param  int     $save_lastsearch_value      -1=Auto, 0=No save of lastsearch_values when clicking, 1=Save lastsearch_values whenclicking
	 *  @return	string                              String with URL
	 */
	public function getNomUrl($withpicto = 0, $option = '', $notooltip = 0, $morecss = '', $save_lastsearch_value = -1)
	{
		global $conf, $langs, $hookmanager;

		if (!empty($conf->dol_no_mouse_hover)) $notooltip = 1; // Force disable tooltips

		$result = '';

		$label = img_picto('', $this->picto).' <u>'.$langs->trans("General").'</u>';
		if (isset($this->status)) {
			$label .= ' '.$this->getLibStatut(5);
		}
		$label .= '<br>';
		$label .= '<b>'.$langs->trans('Ref').':</b> '.$this->ref;

		$url = dol_buildpath('/tab/general_card.php', 1).'?id='.$this->id;

		if ($option != 'nolink')
		{
			// Add param to save lastsearch_values or not
			$add_save_lastsearch_values = ($save_lastsearch_value == 1 ? 1 : 0);
			if ($save_lastsearch_value == -1 && preg_match('/list\.php/', $_SERVER["PHP_SELF"])) $add_save_lastsearch_values = 1;
			if ($add_save_lastsearch_values) $url .= '&save_lastsearch_values=1';
		}

		$linkclose = '';
		if (empty($notooltip))
		{
			if (!empty($conf->global->MAIN_OPTIMIZEFORTEXTBROWSER))
			{
				$label = $langs->trans("ShowGeneral");
				$linkclose .= ' alt="'.dol_escape_htmltag($label, 1).'"';
			}
			$linkclose .= ' title="'.dol_escape_htmltag($label, 1).'"';
			$linkclose .= ' class="classfortooltip'.($morecss ? ' '.$morecss : '').'"';
		} else $linkclose = ($morecss ? ' class="'.$morecss.'"' : '');

		$linkstart = '<a href="'.$url.'"';
		$linkstart .= $linkclose.'>';
		$linkend = '</a>';

		$result .= $linkstart;

		if (empty($this->showphoto_on_popup)) {
			if ($withpicto) $result .= img_object(($notooltip ? '' : $label), ($this->picto ? $this->picto : 'generic'), ($notooltip ? (($withpicto != 2) ? 'class="paddingright"' : '') : 'class="'.(($withpicto != 2) ? 'paddingright ' : '').'classfortooltip"'), 0, 0, $notooltip ? 0 : 1);
		} else {
			if ($withpicto) {
				require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

				list($class, $module) = explode('@', $this->picto);
				$upload_dir = $conf->$module->multidir_output[$conf->entity]."/$class/".dol_sanitizeFileName($this->ref);
				$filearray = dol_dir_list($upload_dir, "files");
				$filename = $filearray[0]['name'];
				if (!empty($filename)) {
					$pospoint = strpos($filearray[0]['name'], '.');

					$pathtophoto = $class.'/'.$this->ref.'/thumbs/'.substr($filename, 0, $pospoint).'_mini'.substr($filename, $pospoint);
					if (empty($conf->global->{strtoupper($module.'_'.$class).'_FORMATLISTPHOTOSASUSERS'})) {
						$result .= '<div class="floatleft inline-block valignmiddle divphotoref"><div class="photoref"><img class="photo'.$module.'" alt="No photo" border="0" src="'.DOL_URL_ROOT.'/viewimage.php?modulepart='.$module.'&entity='.$conf->entity.'&file='.urlencode($pathtophoto).'"></div></div>';
					} else {
						$result .= '<div class="floatleft inline-block valignmiddle divphotoref"><img class="photouserphoto userphoto" alt="No photo" border="0" src="'.DOL_URL_ROOT.'/viewimage.php?modulepart='.$module.'&entity='.$conf->entity.'&file='.urlencode($pathtophoto).'"></div>';
					}

					$result .= '</div>';
				} else {
					$result .= img_object(($notooltip ? '' : $label), ($this->picto ? $this->picto : 'generic'), ($notooltip ? (($withpicto != 2) ? 'class="paddingright"' : '') : 'class="'.(($withpicto != 2) ? 'paddingright ' : '').'classfortooltip"'), 0, 0, $notooltip ? 0 : 1);
				}
			}
		}

		if ($withpicto != 2) $result .= $this->ref;

		$result .= $linkend;
		//if ($withpicto != 2) $result.=(($addlabel && $this->label) ? $sep . dol_trunc($this->label, ($addlabel > 1 ? $addlabel : 0)) : '');

		global $action, $hookmanager;
		$hookmanager->initHooks(array('generaldao'));
		$parameters = array('id'=>$this->id, 'getnomurl'=>$result);
		$reshook = $hookmanager->executeHooks('getNomUrl', $parameters, $this, $action); // Note that $action and $object may have been modified by some hooks
		if ($reshook > 0) $result = $hookmanager->resPrint;
		else $result .= $hookmanager->resPrint;

		return $result;
	}

	/**
	 *  Return the label of the status
	 *
	 *  @param  int		$mode          0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto, 6=Long label + Picto
	 *  @return	string 			       Label of status
	 */
	public function getLibStatut($mode = 0)
	{
		return $this->LibStatut($this->status, $mode);
	}

	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 *  Return the status
	 *
	 *  @param	int		$status        Id status
	 *  @param  int		$mode          0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto, 6=Long label + Picto
	 *  @return string 			       Label of status
	 */
	public function LibStatut($status, $mode = 0)
	{
		// phpcs:enable
		if (empty($this->labelStatus) || empty($this->labelStatusShort))
		{
			global $langs;
			//$langs->load("tab@tab");
			$this->labelStatus[self::STATUS_DRAFT] = $langs->trans('Draft');
			$this->labelStatus[self::STATUS_VALIDATED] = $langs->trans('Enabled');
			$this->labelStatus[self::STATUS_CANCELED] = $langs->trans('Disabled');
			$this->labelStatusShort[self::STATUS_DRAFT] = $langs->trans('Draft');
			$this->labelStatusShort[self::STATUS_VALIDATED] = $langs->trans('Enabled');
			$this->labelStatusShort[self::STATUS_CANCELED] = $langs->trans('Disabled');
		}

		$statusType = 'status'.$status;
		//if ($status == self::STATUS_VALIDATED) $statusType = 'status1';
		if ($status == self::STATUS_CANCELED) $statusType = 'status6';

		return dolGetStatus($this->labelStatus[$status], $this->labelStatusShort[$status], '', $statusType, $mode);
	}

	/**
	 *	Load the info information in the object
	 *
	 *	@param  int		$id       Id of object
	 *	@return	void
	 */
	public function info($id)
	{
		$sql = 'SELECT rowid, date_creation as datec, tms as datem,';
		$sql .= ' fk_user_creat, fk_user_modif';
		$sql .= ' FROM '.MAIN_DB_PREFIX.$this->table_element.' as t';
		$sql .= ' WHERE t.rowid = '.$id;
		$result = $this->db->query($sql);
		if ($result)
		{
			if ($this->db->num_rows($result))
			{
				$obj = $this->db->fetch_object($result);
				$this->id = $obj->rowid;
				if ($obj->fk_user_author)
				{
					$cuser = new User($this->db);
					$cuser->fetch($obj->fk_user_author);
					$this->user_creation = $cuser;
				}

				if ($obj->fk_user_valid)
				{
					$vuser = new User($this->db);
					$vuser->fetch($obj->fk_user_valid);
					$this->user_validation = $vuser;
				}

				if ($obj->fk_user_cloture)
				{
					$cluser = new User($this->db);
					$cluser->fetch($obj->fk_user_cloture);
					$this->user_cloture = $cluser;
				}

				$this->date_creation     = $this->db->jdate($obj->datec);
				$this->date_modification = $this->db->jdate($obj->datem);
				$this->date_validation   = $this->db->jdate($obj->datev);
			}

			$this->db->free($result);
		} else {
			dol_print_error($this->db);
		}
	}

	/**
	 * Initialise object with example values
	 * Id must be 0 if object instance is a specimen
	 *
	 * @return void
	 */
	public function initAsSpecimen()
	{
		$this->initAsSpecimenCommon();
	}

	/**
	 * 	Create an array of lines
	 *
	 * 	@return array|int		array of lines if OK, <0 if KO
	 */
	public function getLinesArray()
	{
		$this->lines = array();

		$objectline = new GeneralLine($this->db);
		$result = $objectline->fetchAll('ASC', 'position', 0, 0, array('customsql'=>'fk_general = '.$this->id));

		if (is_numeric($result))
		{
			$this->error = $this->error;
			$this->errors = $this->errors;
			return $result;
		} else {
			$this->lines = $result;
			return $this->lines;
		}
	}

	/**
	 *  Returns the reference to the following non used object depending on the active numbering module.
	 *
	 *  @return string      		Object free reference
	 */
	public function getNextNumRef()
	{
		global $langs, $conf;
		$langs->load("tab@tab");

		if (empty($conf->global->TAB_GENERAL_ADDON)) {
			$conf->global->TAB_GENERAL_ADDON = 'mod_general_standard';
		}

		if (!empty($conf->global->TAB_GENERAL_ADDON))
		{
			$mybool = false;

			$file = $conf->global->TAB_GENERAL_ADDON.".php";
			$classname = $conf->global->TAB_GENERAL_ADDON;

			// Include file with class
			$dirmodels = array_merge(array('/'), (array) $conf->modules_parts['models']);
			foreach ($dirmodels as $reldir)
			{
				$dir = dol_buildpath($reldir."core/modules/tab/");

				// Load file with numbering class (if found)
				$mybool |= @include_once $dir.$file;
			}

			if ($mybool === false)
			{
				dol_print_error('', "Failed to include file ".$file);
				return '';
			}

			if (class_exists($classname)) {
				$obj = new $classname();
				$numref = $obj->getNextValue($this);

				if ($numref != '' && $numref != '-1')
				{
					return $numref;
				} else {
					$this->error = $obj->error;
					//dol_print_error($this->db,get_class($this)."::getNextNumRef ".$obj->error);
					return "";
				}
			} else {
				print $langs->trans("Error")." ".$langs->trans("ClassNotFound").' '.$classname;
				return "";
			}
		} else {
			print $langs->trans("ErrorNumberingModuleNotSetup", $this->element);
			return "";
		}
	}

	/**
	 *  Create a document onto disk according to template module.
	 *
	 *  @param	    string		$modele			Force template to use ('' to not force)
	 *  @param		Translate	$outputlangs	objet lang a utiliser pour traduction
	 *  @param      int			$hidedetails    Hide details of lines
	 *  @param      int			$hidedesc       Hide description
	 *  @param      int			$hideref        Hide ref
	 *  @param      null|array  $moreparams     Array to provide more information
	 *  @return     int         				0 if KO, 1 if OK
	 */
	public function generateDocument($modele, $outputlangs, $hidedetails = 0, $hidedesc = 0, $hideref = 0, $moreparams = null)
	{
		global $conf, $langs;

		$result = 0;
		$includedocgeneration = 1;

		$langs->load("tab@tab");

		if (!dol_strlen($modele)) {
			$modele = 'standard_general';

			if (!empty($this->model_pdf)) {
				$modele = $this->model_pdf;
			} elseif (!empty($conf->global->GENERAL_ADDON_PDF)) {
				$modele = $conf->global->GENERAL_ADDON_PDF;
			}
		}

		$modelpath = "core/modules/tab/doc/";

		if ($includedocgeneration && !empty($modele)) {
			$result = $this->commonGenerateDocument($modelpath, $modele, $outputlangs, $hidedetails, $hidedesc, $hideref, $moreparams);
		}

		return $result;
	}

	/**
	 * Action executed by scheduler
	 * CAN BE A CRON TASK. In such a case, parameters come from the schedule job setup field 'Parameters'
	 * Use public function doScheduledJob($param1, $param2, ...) to get parameters
	 *
	 * @return	int			0 if OK, <>0 if KO (this function is used also by cron so only 0 is OK)
	 */
	public function doScheduledJob()
	{
		global $conf, $langs;

		//$conf->global->SYSLOG_FILE = 'DOL_DATA_ROOT/dolibarr_mydedicatedlofile.log';

		$error = 0;
		$this->output = '';
		$this->error = '';

		dol_syslog(__METHOD__, LOG_DEBUG);

		$now = dol_now();

		$this->db->begin();

		// ...

		$this->db->commit();

		return $error;
	}

	/**
	 * ---- FUNCTIONS FOR DASHBOARD ----
	*/

	/**
	 * Retourne le compte courant utilisé par la dite entreprise
	 */
	public function getIdBankAccount(){
		$sql = "SELECT MIN(rowid)";
		$sql .= " FROM ".MAIN_DB_PREFIX."bank_account";
		$resql = $this->db->query($sql);

		if ($resql) {
			if ($this->db->num_rows($resql)) {
				$obj = $this->db->fetch_object($resql);
				$result = $obj->rowid;
			}
			$this->db->free($resql);
		}
		return $result;
	}

	/**
	 * Retourne tous les comptes en banque
	 */
	public function fetchAllBankAccount(){
		$sql = "SELECT * ";
		$sql .= " FROM ".MAIN_DB_PREFIX."bank_account";
		$resql = $this->db->query($sql);

		$result = [];
		if($resql){
			while($obj = $this->db->fetch_object(($resql))){
				$result[] = $obj;
			}
		}
		return $result;
	}

	// Detail et lié avec la table gérant les comptes et les ecritures bancaires pour retrouver le solde de chaque compte
	public function fetchAllDetailBankAccount(){
		$sql = "SELECT amount as amount ";
		$sql .= " FROM ".MAIN_DB_PREFIX."bank as b";
		$sql .= " INNER JOIN ".MAIN_DB_PREFIX."bank_account as ba";
		$sql .= " WHERE b.fk_account = ba.rowid";

		$resql = $this->db->query($sql);

		// $result = [];
		if($resql){
			while($obj = $this->db->fetch_object(($resql))){
				$result[] = $obj->amount;
			}
		}
		return $result;
	}


	/**
	 * Loading NavBar Template
	 */
	public function load_navbar()
	{
			$path = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
			$current = basename($path);
			$datetime = dol_now();
			$year = dol_print_date($datetime, "%Y");

		?>
			<div class="navbar">
				<li class="<?php if ($current == "overview.php?mode=customer&filter=".$year || $current == 'overview.php?mode=supplier' ){ print 'current';} else{ echo'no_current';}?>">
				<a href="overview.php?mode=customer&filter=2022"><i class="fa fa-fw fa-home"></i> Général</a>
				</li>
				<li class="<?php if ($current == "./encoursCF.php?filter=".$year){ echo 'current';} else{ echo'no_current';}?>">
				<a href="./encoursCF.php?filter=2022"><i class="fa fa-pie-chart"></i> Encours C/F</a>
				</li>
				<li class="<?php if ($current == 'treso_previ.php'){ echo 'current';} else{ echo'no_current';}?>">
				<a href="treso_previ.php"><i class="fa fa-bank"></i> Trésorerie</a>
				</li>
				<li class="<?php if ($current == 'netProduce.php'){ echo 'current';} else{ echo'no_current';}?>">
				<a href="netProduce.php"><i class="fa fa-briefcase"></i> Net à produire</a>
				</li>
			</div>
			<?php
	}



	/**
	 * Determines the starting month (based on fiscal year - configuration)
	 *  @param  int		$duree                  	Increment one day/one month/one year
	 *  @param  string  $startMonthTimestamp        On what the link point to ('nolink', ...)
	 *  @param  int     $start                  	date of start month fiscal year/last year or 1
	 *  @return	string|int                          1 or string (date)
	 */
	public function startMonthForGraphLadder($startFiscalyear, $duree) {
		global $conf;

		if(!empty($conf->global->START_FISCAL_YEAR)){
			$startMonthTimestamp = strtotime($startFiscalyear);
			$duree = 12;
			$startMonthFiscalYear = date('n', strtotime('+'.$duree.'month', $startMonthTimestamp));
			$start = $startMonthFiscalYear;
		} else {
			$start = 1;
		}
		return $start;
	}

	/**
	 * Return le cumul des montants d'un compte courant
	 */
	public function totalSoldeCurrentAccount($account){

		$sql = "SELECT SUM(amount) as amount
				FROM `llx_bank`
				WHERE fk_account = ".$account;
		$resql = $this->db->query($sql);

		if ($resql) {
			if ($this->db->num_rows($resql)) {
				$obj = $this->db->fetch_object($resql);
				$result = $obj->amount;
			}
			$this->db->free($resql);
		}
		return $result;
	}

	// Retourne le montant des soldes de tous les comptes enregistrés
	public function totalSoldes(){

		$sql = "SELECT SUM(amount) as amount";
		$sql .= " FROM " . MAIN_DB_PREFIX . "bank";
		$resql = $this->db->query($sql);

		if ($resql) {
			if ($this->db->num_rows($resql)) {
				$obj = $this->db->fetch_object($resql);
				$result = $obj->amount;
			}
			$this->db->free($resql);
		}
		return $result;
	}


	/**
	 * Return the progress between 2 values.
	 * It's variation rate
	 * The rate of change measures the evolution of a variable between two dates compared to its starting value.
	 * This relative variation is expressed as a percentage (%).
	 */
	public function progress($arrival_value, $starting_value){

		// ((VA - VD) / VA) * 100
		$arrival_value;
		$starting_value;
		$res = ($arrival_value - $starting_value);
		$res = ($res / $starting_value);
		$resultat = $res * 100;
		$resultat = round($resultat, 2);

		return $resultat;
	}

	/**
	 * Return all order by passing the status as a parameter to filter the search
	 */
	function fetchOrder($fk_statut){

		global $db;

		$sql = "SELECT * FROM ".MAIN_DB_PREFIX."commande";
		$sql .= " WHERE fk_statut = ".$fk_statut." ORDER BY date_livraison ASC ";
		$resql = $db->query($sql);
		$resql = $this->db->query($sql);

		if ($resql) {
			if ($this->db->num_rows($resql)) {
				$obj = $this->db->fetch_object($resql);
				$result = $obj->total_ht;
			}
			$this->db->free($resql);
		}
		return $result;
	}


	function fetchValidatedOrder($date_start, $date_end){


		global $db;

		$sql = "SELECT * FROM ".MAIN_DB_PREFIX."commande";
		$sql .= " WHERE date_commande BETWEEN '" . $date_start . "' AND '" . $date_end . "' ";
		$sql .= " AND fk_statut = 1";
		$resql = $db->query($sql);
		$resql = $this->db->query($sql);

		if ($resql) {
			if ($this->db->num_rows($resql)) {
				$obj = $this->db->fetch_object($resql);
				$result = $obj->total_ht;
			}
			$this->db->free($resql);
		}

		return $result;
	}

	function fetchDeliveredOrder($date_start, $date_end){


		global $db;

		$sql = "SELECT * FROM ".MAIN_DB_PREFIX."commande";
		$sql .= " WHERE date_commande BETWEEN '" . $date_start . "' AND '" . $date_end . "' ";
		$sql .= " AND fk_statut = 3";
		$resql = $db->query($sql);
		$result = [];

		if($resql){
			while($obj = $db->fetch_object(($resql))){
				$result[] = $obj;
			}
		}

		return $result;
	}


	// Retourne les commandes livrées aujourd'hui
	function fetchDeliveredOrderToday(){

		global $db;
		$today = date('Y-m-d');

		// request
		$sql = "SELECT * FROM " . MAIN_DB_PREFIX . "commande";
		$sql .= " WHERE date_commande = \"$today\" ";
		$sql .= "AND fk_statut = 1";
		$resql = $db->query($sql);
		$result = [];

		if($resql){
			while($obj = $db->fetch_object(($resql))){
				$result[] = $obj;
			}
		}
		return $result;
	}


	// Retourne les commande validées triées par date de livraison
	function fetchOrderSortedByDeliveryDate($date_start, $date_end){

		global $db;
		// request
		$sql = "SELECT * FROM " . MAIN_DB_PREFIX . "commande";
		$sql .= " WHERE date_commande BETWEEN '" . $date_start . "' AND '" . $date_end . "' ";
		$sql .= "AND fk_statut = 1 ORDER BY date_livraison ASC ";
		$resql = $db->query($sql);
		$result = [];

		if($resql){
			while($obj = $db->fetch_object(($resql))){
				$result[] = $obj;
			}
		}
		return $result;
	}


	/**
	 * INVOICES :
	 * Retourne les factures clients (tout types) payées sur une période xx
	*/

	function fetchInvoices($date_start, $date_end){

		$sql = "SELECT SUM(total_ht) as total_ht";
		$sql .= " FROM " . MAIN_DB_PREFIX . "facture";
		$sql .= " WHERE datef BETWEEN '" . $date_start . "' AND '" . $date_end . "' ";
		$sql .= " AND paye=1";
		$sql .= " AND fk_statut !=0";

		$resql = $this->db->query($sql);

		if ($resql) {
			if ($this->db->num_rows($resql)) {
				$obj = $this->db->fetch_object($resql);
				$result = $obj->total_ht;
			}
			$this->db->free($resql);
		}
		return $result;
	 }


	 // Retourne les facturs impayées (hors brouillon et hors acomptes) sur une periode donnée
	function fetchUnpaidInvoice($date_start, $date_end){

		$sql = "SELECT SUM(total_ht) as total_ht";
		$sql .= " FROM " . MAIN_DB_PREFIX . "facture";
		$sql .= " WHERE datef BETWEEN '" . $date_start . "' AND '" . $date_end . "' ";
		$sql .= " AND paye = 0 ";
		$sql .= " AND fk_statut =1 "; // are validated

		$resql = $this->db->query($sql);

		if ($resql) {
			if ($this->db->num_rows($resql)) {
				$obj = $this->db->fetch_object($resql);
				$result = $obj->total_ht;
			}
			$this->db->free($resql);
		}
		return $result;
	 }

	 /**
	  * @param	result array
	  */
	 function fetchInvoice($date_start, $date_end){

		$sql = "SELECT * ";
		$sql .= " FROM " . MAIN_DB_PREFIX . "facture";
		$sql .= " WHERE datef BETWEEN '" . $date_start . "' AND '" . $date_end . "' ";
		$sql .= " AND fk_statut != 0";
		$sql .= " AND type != 3 AND type != 2";
		$resql = $this->db->query($sql);
		$result = [];

		if($resql){
			while($obj = $this->db->fetch_object(($resql))){
				$result[] = $obj;
			}
		}
		return $result;
	 }

	 // Retourne toutes les factures standard payées + impayées(hors brouillon /) sur une période donnée
	public function turnover($date_start, $date_end){
		global $db, $conf;

		$sql = "SELECT SUM(total_ht) as total_ht ";
		$sql .= " FROM " . MAIN_DB_PREFIX . "facture";
		$sql .= " WHERE datef BETWEEN '" . $date_start . "' AND '" . $date_end . "' ";
		$sql .= " AND type != 3 AND type!=2"; // not accompte and not deposit
		$sql .= " AND fk_statut != 0"; // not draft - are validated

		$resql = $db->query($sql);

		if ($resql) {
			if ($db->num_rows($resql)) {
				$obj = $db->fetch_object($resql);
				$standard_invoice = $obj->total_ht;
			}
			$db->free($resql);
		}
		return $standard_invoice;
	 }


	 // retourne les avoirs impayes et/ou payés (hors brouillon) sur une période donnée
	 public function avoir($startfiscalyear, $lastDayYear){
		global $db, $conf;

		$sql = "SELECT SUM(total_ht) as total_ht ";
		$sql .= " FROM " . MAIN_DB_PREFIX . "facture";
		$sql .= " WHERE datef BETWEEN '" . $startfiscalyear . "' AND '" . $lastDayYear . "' ";
		$sql .= "AND fk_statut != 0 ";
		$sql .= "AND type = 2 "; // avoir

		$resql = $db->query($sql);

		if ($resql) {
			if ($db->num_rows($resql)) {
				$obj = $db->fetch_object($resql);
				$avoir = $obj->total_ht;
			}
			$db->free($resql);
		}
		return $avoir;
	 }

	  // retourne un tableau d'avoirs impayes et/ou payés (hors brouillon) sur une période donnée
	  public function avoirForMargin($startfiscalyear, $lastDayYear){
		global $db, $conf;

		$sql = "SELECT * ";
		$sql .= " FROM " . MAIN_DB_PREFIX . "facture";
		$sql .= " WHERE datef BETWEEN '" . $startfiscalyear . "' AND '" . $lastDayYear . "' ";
		$sql .= "AND fk_statut != 0 ";
		$sql .= "AND type = 2 "; // avoir
		$resql = $db->query($sql);
		$result = [];

		if($resql){
			while($obj = $this->db->fetch_object(($resql))){
				$result[] = $obj;
			}
		}
		return $result;
	 }

	 // Retourne les factures abandonnées sur une periode d
	 public function closedInvoice($startfiscalyear, $lastDayYear){
		global $db;

		$sql = "SELECT SUM(total_ht) as total_ht ";
		$sql .= " FROM " . MAIN_DB_PREFIX . "facture";
		$sql .= " WHERE datef BETWEEN '" . $startfiscalyear . "' AND '" . $lastDayYear . "' ";
		$sql .= "AND fk_statut = 3";

		$resql = $db->query($sql);

		if ($resql) {
			if ($db->num_rows($resql)) {
				$obj = $db->fetch_object($resql);
				$closed_invoices = $obj->total_ht;
			}
			$db->free($resql);
		}
		return $closed_invoices;
	 }

	// Retourne un tableau de toutes les factures TTC (hors brouillon) impayées sur une période donnée
	 public function outstandingBill($date_start, $date_end){

		 	// Encours client total sur l'exercice fiscal
			$sql = "SELECT * ";
			$sql .= " FROM " . MAIN_DB_PREFIX . "facture";
			$sql .= " WHERE datef BETWEEN '" . $date_start . "' AND '" . $date_end . "' ";
			$sql .= " AND paye = 0";
			$sql .= " AND fk_statut != 0 ";

			$resql = $this->db->query($sql);
			$result = [];

			if($resql){
				while($obj = $this->db->fetch_object(($resql))){
					$result[] = $obj->total_ht;
				}
			}
			return $result;
	 }


	/**
	 * Retourne un tableau de toutes les factures standard
	 * (hors brouillon - TTC) impayées (hors periode)
	 * */
	public function fetchCustomerInvoices(){

	   $sql = "SELECT SUM(total_ht) as total_ht";
	   $sql .= " FROM " . MAIN_DB_PREFIX . "facture";
	   $sql .= " WHERE paye = 0 ";
	   $sql .= " AND fk_statut =1";

	   $resql = $this->db->query($sql);
	   $result = [];

	   if($resql){
		   while($obj = $this->db->fetch_object(($resql))){
			   $result[] = $obj->total_ht;
		   }
	   }
	   return $result;
	}

	 /*
	  Fetch all unpaid customer invoices whose due date has passed
	  */
	public function fetchCustomerBillExceed($date = '', $date_start, $date_end, $type=''){
		global $db;

	   $sql = "SELECT *";
	   $sql .= " FROM " . MAIN_DB_PREFIX . "facture";
	   $sql .= " WHERE date_lim_reglement <= ".dol_now();
	   $sql .= " AND fk_statut != 0";
	   $sql .= " AND paye = 0";
	   $sql .= " AND type = ".$type;
	   $sql .= " AND datec BETWEEN '" . $date_start . "' AND '" . $date_end . "' ";

	   $resql = $this->db->query($sql);

	   $result = [];

	   if($resql){
		   while($obj = $this->db->fetch_object(($resql))){
			   $result[] = $obj->total_ht;
		   }
	   }
	   return $result;
	}


	/**
	 * -------- SUPPLIER INVOICE ----------
	 */


	 	// Retourne un tableau de toutes les factures fournisseurs (hors brouillon) impayées (hors period)
	public function fetchSupplierInvoices(){

		$sql = "SELECT SUM(total_ht) as total_ht";
		$sql .= " FROM " . MAIN_DB_PREFIX . "facture_fourn";
		$sql .= " WHERE paye = 0";
		$sql .= " AND fk_statut != 0 ";

		$resql = $this->db->query($sql);
		$result = [];

		if($resql){
			while($obj = $this->db->fetch_object(($resql))){
				$result[] = $obj->total_ht;
			}
		}
		return $result;
	 }


	 /**
	  * Return all unpaid supplier invoice on period
	  */
	 public function outstandingSupplier($date_start, $date_end, $paye){

	   $sql = "SELECT * ";
	   $sql .= " FROM " . MAIN_DB_PREFIX . "facture_fourn";
	   $sql .= " WHERE datef BETWEEN '" . $date_start . "' AND '" . $date_end . "'";
	   $sql .= " AND paye = ".$paye;
	   $sql .= " AND fk_statut != 0 ";

	   $resql = $this->db->query($sql);
	   $result = [];

	   if($resql){
		   while($obj = $this->db->fetch_object(($resql))){
			   $result[] = $obj->total_ht;
		   }
	   }
	   return $result;
	}

	 /*
	  Fetch all unpaid supplier invoices whose due date has passed
	  */
	  public function fetchSupplierrBillExceed($date = '', $date_start, $date_end, $type=''){
		global $db;

	   $sql = "SELECT *";
	   $sql .= " FROM " . MAIN_DB_PREFIX . "facture_fourn";
	   $sql .= " WHERE date_lim_reglement <= ".dol_now();
	   $sql .= " AND fk_statut != 0";
	   $sql .= " AND paye = 0";
	   $sql .= " AND datec BETWEEN '" . $date_start . "' AND '" . $date_end . "' ";
	   $sql .= " AND type = ".$type;
	   $resql = $this->db->query($sql);

	   $result = [];

	   if($resql){
		   while($obj = $this->db->fetch_object(($resql))){
			   $result[] = $obj->total_ht;
		   }
	   }
	   return $result;
	}

	 /*
	  Retourne la somme total (TTC) des factures récurrentes
	  */
	  public function fetchModelInvoices($firstDayCurrentMonth, $lastDayCurrentMonth){
		global $db;

	   $sql = "SELECT SUM(total_ht) as total_ht";
	   $sql .= " FROM " . MAIN_DB_PREFIX . "facture_rec";
	   $sql .= " WHERE date_last_gen BETWEEN '" . $firstDayCurrentMonth . "' AND '" . $lastDayCurrentMonth . "'";
		//    $sql .= " AND date_when BETWEEN '" . $firstDayCurrentMonth . "' AND '" . $lastDayCurrentMonth . "'";
	   $sql .= " AND suspended = 0";

	   $resql = $this->db->query($sql);

	   $result = [];

	   if($resql){
		   while($obj = $this->db->fetch_object(($resql))){
			   $result[] = $obj->total_ht;
		   }
	   }
	   return $result;
	}




	/**
	 * ---------------- TRESURY ---------------------
	 */


	/**
	 * Retourne le montant total de la TVA
	 */

	public function fetchTVA($firstDayCurrentMonth, $date_now = ''){

		$date_now = dol_now();

		$sql = "SELECT SUM(amount) as amount";
		$sql .= " FROM " . MAIN_DB_PREFIX . "c_tva";
		$sql .= " WHERE datec BETWEEN '" . $firstDayCurrentMonth . "' AND '" . $date_now. "'";
		$resql = $this->db->query($sql);

		if ($resql) {
			if ($this->db->num_rows($resql)) {
				$obj = $this->db->fetch_object($resql);
				$tva = $obj->amount;
			}
			$this->db->free($resql);
		}

		return $tva;
	}

	/**
	 * Retourne le montant total des notes de frais sur une période donnée
	 */
	public function fetchExpenses($date_start, $date_end = ''){

		$sql = "SELECT SUM(total_ht) as total_ht";
		$sql .= " FROM " . MAIN_DB_PREFIX . "expensereport";
		$sql .= " WHERE date_debut >= '" . $date_start . "' AND date_fin <= '" . $date_end. "'";
		$sql .= " AND fk_statut != 0";
		$resql = $this->db->query($sql);

		if ($resql) {
			if ($this->db->num_rows($resql)) {
				$obj = $this->db->fetch_object($resql);
				$tva = $obj->total_ht;
			}
			$this->db->free($resql);
		}

		return $tva;
	}

	/**
	 * Calcul pour le montant des charges variables
	 * Doit prendre en compte :
	 *		- factures fournisseurs impayées
	 *		- le total de la TVA du mois en cours
	 */
	 public function fetchVariablesExpenses($date_start, $date_end, $supplier_invoice, $tva){

		$supplier_invoice = $this->outstandingSupplier($date_start, $date_end, 0);
		$tva = $this->fetchTVA($date_start, $date_end);

		$resultat = ($supplier_invoice + $tva);
		return $resultat;
	 }

	/**
	 * Retourne le montant total des salaires sur une période donnée
	 */
	 public function fetchSalarys($firstDayLastMonth, $lastDayLastMonth, $currentAccount){

		// SALARY
		$sql = "SELECT SUM(amount) as amount";
		$sql .= " FROM " . MAIN_DB_PREFIX . "salary" ;
		$sql .= " WHERE datesp and dateep BETWEEN '" . $firstDayLastMonth .  "' AND '" . $lastDayLastMonth . "'";
		$sql .= "AND fk_account = ".$currentAccount;

		$resql = $this->db->query($sql);

		if ($resql) {
			if ($this->db->num_rows($resql)) {
				$obj = $this->db->fetch_object($resql);
				$result = $obj->amount;
			}
			$this->db->free($resql);
		}

		return $result;
	 }


	/**
	 * Retourne le montant total des charges sociales et fiscales sur une période donnée
	 */
		public function fetchSocialAndTaxesCharges($date_start, $date_end, $currentAccount){

		$sql = "SELECT SUM(amount) as amount";
		$sql .= " FROM " . MAIN_DB_PREFIX . "chargesociales";
		$sql .= " WHERE date_ech BETWEEN '" . $date_start . "' AND '" . $date_end . "'";
		$sql .= "AND fk_account = ".$currentAccount;

		$resql = $this->db->query($sql);

		if ($resql) {
			if ($this->db->num_rows($resql)) {
				$obj = $this->db->fetch_object($resql);
				$socialAndTaxesCharges = $obj->amount;
			}
			$this->db->free($resql);
		}
		return $socialAndTaxesCharges;
	}

	/**
	 * Retourne le montant total (capital) des emprunts sur une période donnée
	 */
	public function fetchEmprunts($date_start, $date_end, $currentAccount){

		// EMPRUNTS
		$sql = "SELECT SUM(capital) as capital";
		$sql .= " FROM " . MAIN_DB_PREFIX . "loan";
		$sql .= " WHERE datestart BETWEEN '" . $date_start . "' AND '" . $date_end . "'";
		// $sql .= "AND fk_bank = ".$currentAccount;

		$resql = $this->db->query($sql);

		if ($resql) {
			if ($this->db->num_rows($resql)) {
				$obj = $this->db->fetch_object($resql);
				$emprunts = $obj->capital;
			}
			$this->db->free($resql);
		}
		return $emprunts;
	}

	/**
	 * Retourne le montant total des paiements divers sur une période donnée
	 */
	public function fetchVariousPaiements($date_start, $date_end, $currentAccount){

		$sql = "SELECT SUM(amount) as amount";
		$sql .= " FROM ". MAIN_DB_PREFIX . "payment_various as vp" ;
		// $sql .= " INNER JOIN ".MAIN_DB_PREFIX."bank as b";
		// $sql .= "ON vp.fk_bank = b.rowid";
		// $sql .= " INNER JOIN ".MAIN_DB_PREFIX."bank_account as ba";
		$sql .= " WHERE vp.datep BETWEEN \"2022-06-01\" AND \"2022-06-30\" ";
		$sql .= "AND vp.sens = 0 ";

		// $sql .= "AND b.fk_account = ba.rowid";


		$resql = $this->db->query($sql);

		if ($resql) {
			if ($this->db->num_rows($resql)) {
				$obj = $this->db->fetch_object($resql);
				$variouspaiments = $obj->amount;
			}
			$this->db->free($resql);
		}

		return $variouspaiments;
	}

	 /**
	  * Calcul pour retourner le montant total des charges fixes
	  * Doit prendre en compte :
	  *      - salaire
	  *		 - Charges sociales et fiscales
	  *		 - Emprunts
	  *		 - Paiements divers
	  */
	  	public function fetchStaticExpenses($date_start, $date_end, $currentAccount){

		$salarys = $this->fetchSalarys($date_start, $date_end, $currentAccount);
		$socialesTaxes_charges = $this->fetchSocialAndTaxesCharges($date_start, $date_end, $currentAccount);
		$emprunts = $this->fetchEmprunts($date_start, $date_end, $currentAccount);
		$variousPaiements = $this->fetchVariousPaiements($date_start, $date_end, $currentAccount);

		$resultat = ($salarys + $socialesTaxes_charges + $emprunts + $variousPaiements);

		return $resultat;
	}


	// Retourne les factures fournisseurs réglées sur l'exercice fiscal
	public function allSupplierUnpaidInvoices($firstDayYear, $lastDayYear){

	$sql = "SELECT SUM(total_ht) as total_ht";
	$sql .= " FROM " . MAIN_DB_PREFIX . "facture_fourn";
	$sql .= " WHERE datec BETWEEN '" . $firstDayYear . "' AND '" . $lastDayYear . "'";
	$sql .= " AND fk_statut != 0 AND paye = 0 AND type=0";

	$resql = $this->db->query($sql);

	if ($resql) {
		if ($this->db->num_rows($resql)) {
			$obj = $this->db->fetch_object($resql);
			$result = $obj->total_ht;
		}
		$this->db->free($resql);
	}

	return $result;
}

	/*
	* Retourne les factures fournisseurs réglées sur l'exercice fiscal
	*/
	public function allSupplierUnpaidDeposit($firstDayYear, $lastDayYear){

		$sql = "SELECT SUM(total_ht) as total_ht";
		$sql .= " FROM " . MAIN_DB_PREFIX . "facture_fourn";
		$sql .= " WHERE datec BETWEEN '" . $firstDayYear . "' AND '" . $lastDayYear . "'";
		$sql .= " AND fk_statut != 0 AND paye = 0 AND type = 2";

		$resql = $this->db->query($sql);

		if ($resql) {
			if ($this->db->num_rows($resql)) {
				$obj = $this->db->fetch_object($resql);
				$result = $obj->total_ht;
			}
			$this->db->free($resql);
		}

		return $result;
	}


	/**
	 * MARGIN REQUEST
	 * Retourne le montant total de la marge (des factures clients validées)
	 */

	 /**
	  * Retourne un tableau des prix de revient pour les facturs validée sur l'exercice en cours
	  */
	public function getBuyPriceHT(){

		$sql = 'SELECT SUM(buy_price_ht) as buy_price_ht FROM '.MAIN_DB_PREFIX.'facturedet';

		$resql = $this->db->query($sql);

		if ($resql) {
			if ($this->db->num_rows($resql)) {
				$obj = $this->db->fetch_object($resql);
				$buyPriceHT = $obj->buy_price_ht;
			}
			$this->db->free($resql);
		}

		return $buyPriceHT;
	}

	/**
	 *  Calcul de la marge brute sur l'exercice en cours
	 * */
	// public function grossMargin($date_start, $date_end){

	// 	$array_total_ht = $this->outstandingBill($date_start, $date_end);
	// 	$result_total_ht = array_sum($array_total_ht);

	// 	$array_buy_price_ht = $this->getBuyPriceHT($date_start, $date_end);
	// 	$result_buy_price_ht = array_sum($array_buy_price_ht);

	// 	$resultat = $result_total_ht - $result_buy_price_ht;
	// 	return $resultat;

	// }

	 public function monthlyCharges($firstDayCurrentMonth, $lastDayCurrentMonth){

			$sql = "SELECT SUM(total_ht) as total_ht";
			$sql .= " FROM " . MAIN_DB_PREFIX . "facture_fourn";
			$sql .= " WHERE datef BETWEEN '" . $firstDayCurrentMonth . "' AND '" . $lastDayCurrentMonth . "' ";
			$resql = $this->db->query($sql);

			if ($resql) {
				if ($this->db->num_rows($resql)) {
					$obj = $this->db->fetch_object($resql);
					$result = $obj->total_ht;
				}
				$this->db->free($resql);
			}
			return $result;
	 }

	 public function supplier_ordered_orders($firstDayYear, $lastDayYear){

		$sql = "SELECT SUM(total_ht) as total_ht";
		$sql .= " FROM " . MAIN_DB_PREFIX . "commande_fournisseur";
		$sql .= " WHERE date_creation BETWEEN '" . $firstDayYear . "' AND '" . $lastDayYear . "' ";
		$sql .= " AND fk_statut = 3 ";
		$resql = $this->db->query($sql);

		if ($resql) {
			if ($this->db->num_rows($resql)) {
				$obj = $this->db->fetch_object($resql);
				$result = $obj->total_ht;
			}
			$this->db->free($resql);
		}
		return $result;
	 }


}




require_once DOL_DOCUMENT_ROOT.'/core/class/commonobjectline.class.php';

/**
 * Class GeneralLine. You can also remove this and generate a CRUD class for lines objects.
 */
class GeneralLine extends CommonObjectLine
{
	// To complete with content of an object GeneralLine
	// We should have a field rowid, fk_general and position

	/**
	 * @var int  Does object support extrafields ? 0=No, 1=Yes
	 */
	public $isextrafieldmanaged = 0;

	/**
	 * Constructor
	 *
	 * @param DoliDb $db Database handler
	 */
	public function __construct(DoliDB $db)
	{
		$this->db = $db;
	}
}
