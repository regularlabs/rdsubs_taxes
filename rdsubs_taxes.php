<?php
/**
 * @package            RD-Subscriptions Taxes
 * @version            1.0.0
 *
 * @author             Peter van Westen <info@regularlabs.com>
 * @link               http://www.regularlabs.com
 * @copyright          Copyright © 2016 Regular Labs All Rights Reserved
 * @license            http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die;

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_rdsubs_taxes'))
{
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

$lang = JFactory::getLanguage();
$lang->load('com_rdsubs_taxes', JPATH_ADMINISTRATOR, null, 1);

$controller = JControllerLegacy::getInstance('RDSubs_Taxes');
$controller->execute(JRequest::getCmd('task'));
$controller->redirect();
