<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
Copyright (C) 2003 - 2011 EllisLab, Inc.

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
ELLISLAB, INC. BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

Except as contained in this notice, the name of EllisLab, Inc. shall not be
used in advertising or otherwise to promote the sale, use or other dealings
in this Software without prior written authorization from EllisLab, Inc.
*/

/**
 * ExpressionEngine - by EllisLab
 *
 * @package		ExpressionEngine
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2003 - 2011, EllisLab, Inc.
 * @license		http://expressionengine.com/user_guide/license.html
 * @link		http://expressionengine.com
 * @since		Version 2.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Duplicate Email Accessory
 *
 * @package		ExpressionEngine
 * @subpackage	Control Panel
 * @category	Accessories
 * @author		ExpressionEngine Dev Team
 * @link		http://expressionengine.com
 */
class Duplicate_email_check_acc {

	var $name			= 'Duplicate Email Check';
	var $id				= 'ee_dupe_emails';
	var $version		= '1.0';
	var $description	= 'Checks for duplicate email addresses for registered members';
	var $sections		= array();

	/**
	 * Constructor
	 */
	function __construct()
	{
		$this->EE =& get_instance();
	}

	// --------------------------------------------------------------------

	/**
	 * Set Sections
	 *
	 * Set content for the accessory
	 *
	 * @access	public
	 * @return	void
	 */
	 function set_sections()
	{
		$this->EE->lang->loadfile('duplicate_email_check');

		// localize Accessory display name
		$this->name = $this->EE->lang->line('duplicate_email_check');
		
		// set the sections
		$this->sections[$this->EE->lang->line('duplicate_emails')] = $this->_fetch_duplicates();
	}
	
	
	// --------------------------------------------------------------------

	/**
	 * Find Duplicates
	 *
	 * Queries for duplicate emails
	 *
	 * @access	private
	 * @return	string
	 */
	 function _fetch_duplicates()
	{
		$this->EE->load->library('table');
		$this->EE->load->helper(array('url', 'snippets'));

		$dupes = array();
		
		$this->EE->db->select('email');
		$this->EE->db->group_by('email'); 
		$this->EE->db->having('COUNT(*) > 1'); 
		$email_q = $this->EE->db->get('members');

		if ($email_q->num_rows() > 0)
		{
			foreach ($email_q->result_array() as $row)
			{
   				$dupes[] = $row['email'];
			}
		}
		
		if (empty($dupes))
		{
			return $this->EE->lang->line('no_dupes');
		}

		$show_link = TRUE;

		if ( ! $this->EE->cp->allowed_group('can_access_members'))
		{
			$show_link = FALSE;
		}

		$this->EE->db->select('email, username, screen_name, member_id');
		$this->EE->db->where_in('email', $dupes); 
		$query = $this->EE->db->get('members');
			
		foreach ($query->result_array() as $row)
		{
			$member_link = ($show_link) ? '<a href="'.BASE.AMP.'C=myaccount'.AMP.'id='.$row['member_id'].'">'.$row['username'].'</a>' : $row['username'];
			$email_search_link = $row['email'];
			
			$this->EE->table->add_row($member_link, $email_search_link);	
		}

		$this->EE->table->set_heading(
			lang('username'),
			lang('email')
			);
		
		$ret = $this->EE->table->generate();
		$this->EE->table->clear();
		
		return $ret;
	}
}
// END CLASS

/* End of file acc.duplicate_email_check.php */
/* Location: ./system/expressionengine/accessories/acc.duplicate_email_check.php */