<?php // -*- mode:php; tab-width:4; indent-tabs-mode:t; c-basic-offset:4; -*-
#CMS - CMS Made Simple
#(c)2004-2008 by Ted Kulp (ted@cmsmadesimple.org)
#This project's homepage is: http://cmsmadesimple.org
#
#This program is free software; you can redistribute it and/or modify
#it under the terms of the GNU General Public License as published by
#the Free Software Foundation; either version 2 of the License, or
#(at your option) any later version.
#
#This program is distributed in the hope that it will be useful,
#BUT withOUT ANY WARRANTY; without even the implied warranty of
#MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#GNU General Public License for more details.
#You should have received a copy of the GNU General Public License
#along with this program; if not, write to the Free Software
#Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
#$Id$

/**
 * Represents a user group in the database.
 *
 * @author Ted Kulp
 * @since 2.0
 * @version $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 * @license GPL
 **/
class CmsGroup extends CmsObjectRelationalMapping
{
	var $params = array('id' => -1, 'name' => '', 'active' => true);
	var $field_maps = array('group_name' => 'name', 'group_id' => 'id');
	var $id_field = 'group_id';
	var $table = 'groups';
	
	public function __construct()
	{
		parent::__construct();
	}
	
	public function setup($first_time = false)
	{
		$this->create_has_and_belongs_to_many_association('users', 'CmsUser', 'user_groups', 'user_id', 'group_id');
	}
	
	public function validate()
	{
		$this->validate_not_blank('name', lang('nofieldgiven',array(lang('username'))));
		
		// Username validation
		if ($this->name != '')
		{
			// Make sure the name is unique
			$result = $this->find_by_name($this->name);
			if ($result)
			{
				if ($result->id != $this->id)
				{
					$this->add_validation_error(lang('The group name is already in use'));
				}
			}
			
			// Make sure the name has no illegal characters
			if ( !preg_match("/^[a-zA-Z0-9\.]+$/", $this->name) ) 
			{
				$this->add_validation_error(lang('illegalcharacters', array(lang('groupname'))));
			}
		}
	}

	public function add_user($user)
	{
		if ($this->id > -1)
		{
			return cms_db()->Execute("INSERT INTO {user_groups} (user_id, group_id, create_date, modified_date) VALUES (?,?,NOW(),NOW())", array($user->id, $this->id));
		}
		
		return false;
	}

	public function remove_user($user)
	{
		if ($this->id > -1)
		{
			return cms_db()->Execute('DELETE FROM {user_groups} WHERE user_id = ? AND group_id = ?', array($user->id, $this->id));
		}

		return false;
	}
	
	//Callback handlers
	public function before_save()
	{
		CmsEventManager::send_event( 'Core', ($this->id == -1 ? 'AddGroupPre' : 'EditGroupPre'), array('group' => &$this));
	}
	
	public function after_save(&$result)
	{
		//Add the group to the aro table so we can do acls on it
		//Only happens on a new insert
		if ($this->create_date == $this->modified_date)
		{
			//CmsAcl::add_aro($this->id, 'Group');
		}
		CmsEventManager::send_event( 'Core', ($this->create_date == $this->modified_date ? 'AddGroupPost' : 'EditGroupPost'), array('group' => &$this));
	}
	
	public function before_delete()
	{
		cms_db()->Execute('DELETE FROM '.cms_db_prefix().'user_groups WHERE group_id = ?',
						  array($this->id));
		CmsEventManager::send_event('Core', 'DeleteGroupPre', array('group' => &$this));
	}
	
	public function after_delete()
	{
		//CmsAcl::delete_aro($this->id, 'Group');
		CmsEventManager::send_event('Core', 'DeleteGroupPost', array('group' => &$this));
	}
	
	public static function get_groups_for_dropdown($add_everyone = false)
	{
		$result = array();
		
		if ($add_everyone)
		{
			$result[-1] = lang('Everyone');
		}
		

		$groups = cms_orm('CmsGroup')->find_all(array('order' => 'name ASC'));
		foreach ($groups as $group)
		{
			$result[$group->id] = $group->name;
		}
		
		return $result;
	}
}

# vim:ts=4 sw=4 noet
?>
