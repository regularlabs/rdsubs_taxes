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

jimport('joomla.application.component.modellist');

class RDSubs_TaxesModelDefault extends JModelList
{
	public function getData()
	{
		$data = array();

		$date = new DateTime();
		$date->setDate(gmdate('Y'), (ceil(gmdate('m') / 3) * 3), 1);
		$date->setTime(0, 0, 0);
		$date->modify('+1 months');
		$date->modify('-1 day');

		$to   = clone $date;
		$from = clone $to;
		if (date('m', $to->format('U')) > 3)
		{
			$from->setDate(gmdate('Y') - 1, 1, 1);
		}
		else
		{
			$from->setDate(gmdate('Y') - 2, 1, 1);
		}

		$db = JFactory::getDBO();

		$query = $db->getQuery(true)
			->select(array(
				'country_2_code as country',
				'vat_percentage as vat',
			))
			->from($db->quoteName('#__rd_subs_countries'))
			->where(array(
				'vat_percentage > 0',
				'published = 1',
			))
			->order('country_2_code');
		$db->setQuery($query);

		$euvatrates = $db->loadAssocList('country', 'vat');

		$query = $db->getQuery(true)
			->select(array(
				'i.net_price - i.vat_amount as net',
				'i.gross_price as gross',
				'i.vat_amount',
				'i.vat_number as vatnumber',
				'u.vatnumber as vatnumber2',
				'c.requires_vat as vies',
				'i.provider as processor',
				'c.country_2_code as country',
				'YEAR(i.invoicedate) as year',
				'QUARTER(i.invoicedate) as quarter',
			))
			->from($db->quoteName('#__rd_subs_invoices', 'i'))
			->join('LEFT', $db->quoteName('#__rd_subs_users', 'u') . ' ON u.userid = i.userid')
			->join('LEFT', $db->quoteName('#__rd_subs_countries', 'c') . ' ON c.id = u.country')
			->where(array(
				'i.invoicedate > ' . $db->quote($from->format('Y-m-d')),
				'i.invoicedate < ' . $db->quote($to->format('Y-m-d')),
				'i.paid = 1',
				'i.net_price > 0',
				'i.refund_invoice = 0',
				'i.refund_reason = ' . $db->quote(''),
			))
			->order('i.invoicedate');
		$db->setQuery($query);

		$rows = $db->loadObjectList();

		foreach ($rows as $row)
		{
			$y = $row->year;
			$q = $row->quarter;

			if ($row->country == 'GR')
			{
				$row->country = 'EL';
			}

			if (!isset($data[$y]))
			{
				$data[$y]             = new stdClass();
				$data[$y]->q          = array();
				$data[$y]->total      = 0;
				$data[$y]->vat_amount = 0;
			}

			if (!isset($data[$y]->q[$q]))
			{
				$nulls           = array(
					'gross'      => 0,
					'net'        => 0,
					'vat_amount' => 0,
				);
				$data[$y]->q[$q] = (object) array(
					'total'      => 0,
					'vat_amount' => 0,
					'nl'         => (object) $nulls,
					'nl2'        => (object) $nulls,
					'eu'         => (object) $nulls,
					'euvies'     => (object) $nulls,
					'noneu'      => (object) $nulls,
					'vies'       => array(),
					'moss'       => array(),
				);
			}

			$vat = 0;

			if ($row->country == 'NL')
			{
				// NL
				$vat = 1;
				if ($row->processor == 'iDEAL')
				{
					$this->addData($data[$y]->q[$q]->nl2, $row);
				}
				else
				{
					$this->addData($data[$y]->q[$q]->nl, $row);
				}
			}
			else if (isset($euvatrates[$row->country]))
			{
				$vat_number = $this->getVatNumber($row->vatnumber, $row->vatnumber2);

				// EU
				if (!$row->vat_amount && $vat_number)
				{
					$this->addData($data[$y]->q[$q]->euvies, $row);
				}
				else
				{
					$vat = 1;
					$this->addData($data[$y]->q[$q]->eu, $row);

					if ($y >= 2015)
					{
						if (!isset($data[$y]->q[$q]->moss[$row->country]))
						{
							$data[$y]->q[$q]->moss[$row->country] = (object) array(
								'net'     => 0,
								'amount'  => 0,
								'taxrate' => $euvatrates[$row->country],
							);
						}
						$data[$y]->q[$q]->moss[$row->country]->net += $row->net;
						$data[$y]->q[$q]->moss[$row->country]->amount += $row->vat_amount;
					}
				}
			}
			else
			{
				// Non-EU
				$this->addData($data[$y]->q[$q]->noneu, $row);
			}

			if ($vat)
			{
				$data[$y]->q[$q]->total += $row->net;
				$data[$y]->q[$q]->vat_amount += $row->vat_amount;
				$data[$y]->total += $row->net;
				$data[$y]->vat_amount += $row->vat_amount;
			}
			else
			{
				$data[$y]->q[$q]->total += $row->gross;
				$data[$y]->total += $row->gross;
			}
		}

		$query = $db->getQuery(true)
			->select(array(
				'i.net_price - i.vat_amount as price',
				'i.vat_number as vatnumber',
				'u.vatnumber as vatnumber2',
				'c.country_2_code as country',
				'YEAR(i.invoicedate) as year',
				'QUARTER(i.invoicedate) as quarter',
			))
			->from($db->quoteName('#__rd_subs_invoices', 'i'))
			->join('LEFT', $db->quoteName('#__rd_subs_users', 'u') . ' ON u.userid = i.userid')
			->join('LEFT', $db->quoteName('#__rd_subs_countries', 'c') . ' ON c.id = u.country')
			->where(array(
				'i.invoicedate > ' . $db->quote($from->format('Y-m-d')),
				'i.invoicedate < ' . $db->quote($to->format('Y-m-d')),
				'i.paid = 1',
				'i.net_price > 0',
				'i.refund_invoice = 0',
				'i.refund_reason = ' . $db->quote(''),
				'i.vat_amount = 0',
				'(i.vat_number != ' . $db->quote('') . ' OR u.vatnumber != ' . $db->quote('') . ')',
				'c.country_2_code != ' . $db->quote('NL'),
			))
			->order('i.invoicedate');
		$db->setQuery($query);

		$rows = $db->loadObjectList();

		foreach ($rows as $row)
		{
			$row->vatnumber = $this->getVatNumber($row->vatnumber, $row->vatnumber2);

			if (empty($row->vatnumber))
			{
				continue;
			}

			if ($row->country == 'GR')
			{
				$row->country = 'EL';
			}

			$id = $row->country . '_' . $row->vatnumber;

			$y = $row->year;
			$q = $row->quarter;

			if (!isset($data[$y]->q[$q]->vies[$id]))
			{
				$data[$y]->q[$q]->vies[$id] = $row;
				continue;
			}

			$data[$y]->q[$q]->vies[$id]->price += $row->price;
		}

		krsort($data);
		foreach ($data as $y => $dat1)
		{
			krsort($data[$y]->q);
			foreach ($data[$y]->q as $q => $dat2)
			{
				ksort($data[$y]->q[$q]->vies);
			}
		}

		return $data;
	}

	private function getVatNumber($vat1, $vat2)
	{
		$vat1 = $this->cleanVatNumber($vat1);

		if (!empty($vat1))
		{
			return $vat1;
		}

		return $this->cleanVatNumber($vat2);
	}

	private function cleanVatNumber($vat)
	{
		// Remove non-alphanum characters
		$vat = strtoupper(preg_replace("#[^a-z0-9]#si", '', $vat));

		// Remve leading letters
		$vat = preg_replace("#^[a-z][a-z]#si", '', $vat);

		return trim($vat);
	}

	private function addData(&$object, $data)
	{
		$object->gross += $data->gross;
		$object->net += $data->net;
		$object->vat_amount += $data->vat_amount;
	}
}
