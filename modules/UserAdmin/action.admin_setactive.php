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
#but WITHOUT ANY WARRANTY; without even the implied warranty of
#MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#GNU General Public License for more details.
#You should have received a copy of the GNU General Public License
#along with this program; if not, write to the Free Software
#Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
#$Id$

// security
if (!isset($gCms)) die("Can't call actions directly!");

$user = CmsLogin::get_current_user();
if (!CmsAcl::check_core_permission('Modify Users',$user)) die('permission denied');

// setup
if( !isset($params['uid']) )
	{
		die('insufficient parameters');
	}
$uid = (int)$params['uid'];

if( $uid != 1 )
	{
		$user = cms_orm('CmsUser')->find_by_id($uid);
		$user->active = !$user->active;
		$user->save();
	}

$this->Redirect->module_url(array('action' => 'defaultadmin', 'selected_tab' => 'users'));

# 
# EOF
#
?>
