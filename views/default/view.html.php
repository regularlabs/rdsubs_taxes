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

jimport('joomla.application.component.view');

/**
 * View class for default list view
 */
class RDSubs_TaxesViewDefault extends JViewLegacy
{
	protected $items;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$this->data = $this->get('Data');

		$this->addToolbar();
		// Include the component HTML helpers.
		JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar
	 */
	protected function addToolbar()
	{
		$document = JFactory::getDocument();
		$document->setTitle(JText::_('COM_RDSUBS_TAXES'));

		JToolBarHelper::title(JText::_('COM_RDSUBS_TAXES'));
	}
}
