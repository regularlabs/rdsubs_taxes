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

JFactory::getDocument()->addStyleDeclaration(
	'
	.rdsubs_taxes .row { margin-left: 0; }
	.table-sales {font-size: 0.8em;}
	.price { white-space: nowrap; }
	.separator { width: 2px; display: inline-block; border-bottom: 1px #333 solid; margin: 0 1px; }
	th.right,td.right { text-align: right; }
	td.bold { font-weight: bold; }
	'
);
?>
	<div class="rdsubs_taxes">
		<div class="well">
			<h2>Totals</h2>
			<div class="row-fluid">
				<div class="span3">
					<table class="table table-striped table-bordered">
						<thead>
							<tr>
								<th>Year</th>
								<th class="right">Income (net)</th>
								<th class="right">Profit (60%)</th>
								<th class="right">Tax (40%)</th>
								<th class="right">VAT</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$total      = 0;
							$vat_amount = 0;
							?>
							<?php foreach ($this->data as $y => $ydata) : ?>
								<?php
								$total += $ydata->total;
								$vat_amount += $ydata->vat_amount;
								?>
								<tr>
									<td><?php echo round($y); ?></td>
									<td class="right"><?php echo formatNumber($ydata->total); ?></td>
									<td class="right"><?php echo formatNumber($ydata->total * 0.6); ?></td>
									<td class="right"><?php echo formatNumber($ydata->total * 0.4); ?></td>
									<td class="right"><?php echo formatNumber($ydata->vat_amount); ?></td>
								</tr>
							<?php endforeach; ?>
							<tr>
								<td colspan="5"></td>
							</tr>
							<tr>
								<th>Total</th>
								<th class="right"><?php echo formatNumber($total); ?></th>
								<th class="right"><?php echo formatNumber($total * 0.6); ?></th>
								<th class="right"><?php echo formatNumber($total * 0.4); ?></th>
								<th class="right"><?php echo formatNumber($vat_amount); ?></th>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<?php foreach ($this->data as $y => $ydata) : ?>
			<div class="well">
				<h2><?php echo round($y); ?></h2>

				<div class="row-fluid">
					<div class="span3">

						<table class="table table-striped table-bordered">
							<thead>
								<tr>
									<th>Quarter</th>
									<th class="right">Income (net)</th>
									<th class="right">Profit (60%)</th>
									<th class="right">Tax (40%)</th>
									<th class="right">VAT</th>
								</tr>
							</thead>
							<tbody>
								<?php
								$total      = 0;
								$vat_amount = 0;
								?>
								<?php foreach ($ydata->q as $q => $data) : ?>
									<?php
									$total += $data->total;
									$vat_amount += $data->vat_amount;
									?>
									<tr>
										<td>Q<?php echo $q; ?></td>
										<td class="right"><?php echo formatNumber($data->total); ?></td>
										<td class="right"><?php echo formatNumber($data->total * 0.6); ?></td>
										<td class="right"><?php echo formatNumber($data->total * 0.4); ?></td>
										<td class="right"><?php echo formatNumber($data->vat_amount); ?></td>
									</tr>
								<?php endforeach; ?>
								<?php if (count($ydata->q) > 1): ?>
									<tr>
										<td colspan="5"></td>
									</tr>
									<tr>
										<th>Total</th>
										<th class="right"><?php echo formatNumber($total); ?></th>
										<th class="right"><?php echo formatNumber($total * 0.6); ?></th>
										<th class="right"><?php echo formatNumber($total * 0.4); ?></th>
										<th class="right"><?php echo formatNumber($vat_amount); ?></th>
									</tr>
								<?php endif; ?>
							</tbody>
						</table>
					</div>
				</div>
				<div class="row-fluid">
					<?php foreach ($ydata->q as $q => $data) : ?>
						<?php
						$taxrate = ($y <= 2012 && $q <= 3) ? 19 : 21;
						?>
						<div class="well span3">
							<h2>Q<?php echo $q; ?></h2>

							<h3>Invoices</h3>
							<table class="table table-striped table-bordered table-sales">
								<thead>
									<tr>
										<th class="left">Type</th>
										<th class="left">Number</th>
										<th class="right">Net</th>
										<th class="right">Gross</th>
										<th class="right">VAT</th>
										<th class="right">VAT %</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td>NL</td>
										<td>L<?php echo $y; ?>Q<?php echo $q; ?>-NL</td>
										<td class="right bold"><?php echo formatNumber($data->nl->net); ?></td>
										<td class="right"><?php echo formatNumber($data->nl->gross); ?></td>
										<td class="right"><?php echo formatNumber($data->nl->vat_amount); ?></td>
										<td class="right"><?php echo $taxrate; ?>%</td>
									</tr>
									<?php if ($data->nl2->gross) : ?>
										<tr>
											<td>NL iDEAL</td>
											<td>L<?php echo $y; ?>Q<?php echo $q; ?>-NL2</td>
											<td class="right bold"><?php echo formatNumber($data->nl2->net); ?></td>
											<td class="right"><?php echo formatNumber($data->nl2->gross); ?></td>
											<td class="right"><?php echo formatNumber($data->nl2->vat_amount); ?></td>
											<td class="right"><?php echo $taxrate; ?>%</td>
										</tr>
									<?php endif; ?>
									<?php if ($y >= 2015) : ?>
										<?php
										$min_taxrate   = 18; // Finland
										$max_taxrate   = 24; // Malta
										$taxrate_range = $max_taxrate - $min_taxrate;

										$taxrate_avarage = ($data->eu->vat_amount / $data->eu->net) * 100;

										$taxrate_ratio = ($taxrate_avarage - $min_taxrate) / $taxrate_range; // 0 - 1

										$min_net = $data->eu->net * (1 - $taxrate_ratio);
										$max_net = $data->eu->net * $taxrate_ratio;

										$min = (object) array(
											'gross'      => $min_net * (100 + $min_taxrate) / 100,
											'net'        => $min_net,
											'vat_amount' => $min_net * $min_taxrate / 100,
										);
										$max = (object) array(
											'gross'      => $max_net * (100 + $max_taxrate) / 100,
											'net'        => $max_net,
											'vat_amount' => $max_net * $max_taxrate / 100,
										);
										?>
										<tr>
											<td>EU</td>
											<td></td>
											<td class="right"><?php echo formatNumber($data->eu->net); ?></td>
											<td class="right"><?php echo formatNumber($data->eu->gross); ?></td>
											<td class="right"><?php echo formatNumber($data->eu->vat_amount); ?></td>
											<td class="right"><?php echo round($taxrate_avarage, 2); ?>%</td>
										</tr>
										<tr>
											<td>EU part 1</td>
											<td>L<?php echo $y; ?>Q<?php echo $q; ?>-EU-1</td>
											<td class="right bold"><?php echo formatNumber($min->net); ?></td>
											<td class="right"><?php echo formatNumber($min->gross); ?></td>
											<td class="right"><?php echo formatNumber($min->vat_amount); ?></td>
											<td class="right"><?php echo $min_taxrate; ?>%</td>
										</tr>
										<tr>
											<td>EU part 2</td>
											<td>L<?php echo $y; ?>Q<?php echo $q; ?>-EU-2</td>
											<td class="right bold"><?php echo formatNumber($max->net); ?></td>
											<td class="right"><?php echo formatNumber($max->gross); ?></td>
											<td class="right"><?php echo formatNumber($max->vat_amount); ?></td>
											<td class="right"><?php echo $max_taxrate; ?>%</td>
										</tr>
									<?php else: ?>
										<tr>
											<td>EU</td>
											<td>L<?php echo $y; ?>Q<?php echo $q; ?>-EU</td>
											<td class="right bold"><?php echo formatNumber($data->eu->net); ?></td>
											<td class="right"><?php echo formatNumber($data->eu->gross); ?></td>
											<td class="right"><?php echo formatNumber($data->eu->vat_amount); ?></td>
											<td class="right"><?php echo $taxrate; ?>%</td>
										</tr>
									<?php endif; ?>
									<tr>
										<td>NL + EU (1a)</td>
										<td></td>
										<td class="right"><?php echo formatNumber(round($data->nl->net) + round($data->nl2->net) + round($data->eu->net)); ?></td>
										<td class="right"><?php echo formatNumber(round($data->nl->gross) + round($data->nl2->gross) + round($data->eu->gross)); ?></td>
										<td class="right"><?php echo formatNumber(round($data->nl->vat_amount) + round($data->nl2->vat_amount) + round($data->eu->vat_amount)); ?></td>
										<td class="right"></td>
									</tr>
									<tr>
										<td>EU VIES (3b)</td>
										<td>L<?php echo $y; ?>Q<?php echo $q; ?>-EU-B</td>
										<td class="right bold"><?php echo formatNumber($data->euvies->net); ?></td>
										<td colspan="2"></td>
										<td class="right">0%</td>
									</tr>
									<tr>
										<td>Non-EU (3a)</td>
										<td>L<?php echo $y; ?>Q<?php echo $q; ?>-NONEU</td>
										<td class="right bold"><?php echo formatNumber($data->noneu->net); ?></td>
										<td colspan="2"></td>
										<td class="right">0%</td>
									</tr>
								</tbody>
							</table>

							<?php if (count($data->moss)) : ?>
								<h3>EU MOSS</h3>

								<h4>Count: <?php echo count($data->moss); ?></h4>

								<?php
								$items = array();

								$i = 0;
								foreach ($data->moss as $country => $vat)
								{
									$items[] = loadTemplate(
										'moss_item',
										array(
											'id'      => $i++,
											'country' => $country,
											'amount'  => round($vat->amount, 2),
											'taxrate' => round($vat->taxrate, 2),
											'total'   => round(($vat->amount / $vat->taxrate) * 100, 2),
										)
									);
								}

								$html = loadTemplate(
									'moss_list',
									array(
										'count' => count($data->moss),
										'items' => implode("\n", $items),
									)
								);

								?>
								<textarea cols="30" rows="5" style="width:95%"><?php echo htmlentities($html); ?></textarea>

							<?php endif; ?>

							<h3>EU VIES Sales
							</h3>

							<?php $vies = array_chunk($data->vies, 100); ?>
							<?php foreach ($vies as $vies_items) : ?>
								<h4>Count: <?php echo count($vies_items); ?></h4>
								<?php
								$items = array();

								$i = 0;
								foreach ($vies_items as $item)
								{
									$items[] = loadTemplate(
										'vies_item',
										array(
											'id'        => $i++,
											'nr'        => $i,
											'country'   => $item->country,
											'vatnumber' => $item->vatnumber,
											'price'     => round($item->price),
										)
									);
								}

								$html = loadTemplate(
									'vies_list',
									array(
										'items' => implode("\n", $items),
									)
								);
								?>
								<textarea cols="30" rows="5" style="width:95%"><?php echo htmlentities($html); ?></textarea>
							<?php endforeach; ?>

							<a class="btn" href="javascript://" onclick="jQuery('#vies<?php echo $y; ?>-<?php echo $q; ?>').toggle();">Show details</a>

							<div id="vies<?php echo $y; ?>-<?php echo $q; ?>" style="display:none;">
								<table class="table table-striped table-bordered">
									<thead>
										<tr>
											<th>#</th>
											<th class="left" colspan="2">VAT Number</th>
											<th class="left" colspan="2">Amount</th>
										</tr>
									</thead>
									<tbody>
										<?php $i      = 0;
										$prev_country = '';
										$tot          = 0; ?>
										<?php foreach ($data->vies as $item) : ?>
											<tr>
												<td><?php echo ++$i; ?></td>
												<td<?php echo ($item->country == $prev_country) ? ' style="color:#CCCCCC;"' : ''; ?>><?php echo $item->country; ?></td>
												<td><?php echo $item->vatnumber; ?></td>
												<td>€</td>
												<td align="right"><?php echo formatNumber($item->price); ?></td>
											</tr>
											<?php $prev_country = $item->country;
											$tot += $item->price; ?>
										<?php endforeach; ?>
										<tr>
											<td></td>
											<td></td>
											<td></td>
											<td>€</td>
											<td align="right"><?php echo $tot; ?></td>
										</tr>
									</tbody>
								</table>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
<?php
function formatNumber($number, $rounding = 0)
{
	return '<span class="price">' . preg_replace('#([0-9])([0-9][0-9][0-9])$#', '\1<span class="separator"></span>\2', round($number, $rounding)) . '</span>';
}

function loadTemplate($template, $data = array())
{
	$html = file_get_contents(__DIR__ . '/template_' . $template . '.html');

	foreach ($data as $key => $val)
	{
		$html = str_replace('{' . $key . '}', $val, $html);
	}

	return $html;
}
