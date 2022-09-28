<?php
/* Copyright (C) 2001-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2015 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012 Regis Houssin        <regis.houssin@inodbox.com>
 * Copyright (C) 2015      Jean-François Ferry	<jfefe@aternatik.fr>
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
 *	\file       tab/tabindex.php
 *	\ingroup    tab
 *	\brief      Home page of tab top menu
 */

// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) $res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"] . "/main.inc.php";
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME'];
$tmp2 = realpath(__FILE__);
$i = strlen($tmp) - 1;
$j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
	$i--;
	$j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1)) . "/main.inc.php")) $res = @include substr($tmp, 0, ($i + 1)) . "/main.inc.php";
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1))) . "/main.inc.php")) $res = @include dirname(substr($tmp, 0, ($i + 1))) . "/main.inc.php";
// Try main.inc.php using relative path
if (!$res && file_exists("../main.inc.php")) $res = @include "../main.inc.php";
if (!$res && file_exists("../../main.inc.php")) $res = @include "../../main.inc.php";
if (!$res && file_exists("../../../main.inc.php")) $res = @include "../../../main.inc.php";
if (!$res) die("Include of main fails");

require_once DOL_DOCUMENT_ROOT . '/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT . '/custom/tab/class/general.class.php';
require_once DOL_DOCUMENT_ROOT . '/compta/bank/class/account.class.php';
require_once DOL_DOCUMENT_ROOT . '/commande/class/commande.class.php';
require_once DOL_DOCUMENT_ROOT . '/compta/facture/class/facture.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/stats.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/dolgraph.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/bank.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/date.lib.php';
require_once DOL_DOCUMENT_ROOT.'/fourn/class/fournisseur.commande.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formmargin.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/tax.lib.php';
require_once DOL_DOCUMENT_ROOT.'/compta/tva/class/tva.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/report.lib.php';

global $db, $conf;

// Security check
if (empty($conf->tab->enabled)) accessforbidden('Module not enabled');
$socid = 0;

if ($user->socid > 0) { // Protection if external user
	accessforbidden();
}

if(empty($conf->global->START_FISCAL_YEAR) || empty($conf->global->START_FISCAL_LAST_YEAR) ){
	accessforbidden('Vous devez obligatoirement renseigner la date de début et de fin de l\'exercice fiscal dans la configuration du module');
} else {
	$startFiscalyear = $conf->global->START_FISCAL_YEAR;
	$startFiscalLastyear = $conf->global->START_FISCAL_LAST_YEAR;
}

// fetch current bank account
$object = new General($db);
$ret = $object->getIdBankAccount();

/**
 * Calcul for data's period
 */

$datetime = dol_now();
$year = dol_print_date($datetime, "%Y");
$month = dol_print_date($datetime, "%m");
$day = dol_print_date($datetime, "%d");

// Calcul for last day in current year according to the beginning of the fiscal year
$duree = 1;

// Transform date in timestamp
$TimestampCurrentYear = strtotime($startFiscalyear);
$TimestampCurrentLastYear = strtotime($startFiscalLastyear);

// the end date automatically for current and last year
$dateEndYear = date('Y-m-d', strtotime('+'.$duree.'year', $TimestampCurrentYear));
$TimestampendYear = strtotime($dateEndYear);
$endYear = date('Y-m-d', strtotime('-'.$duree.'day', $TimestampendYear));

$dateEndLastYear = date('Y-m-d', strtotime('+'.$duree.'year', $TimestampCurrentLastYear));
$TimestampendLastYear = strtotime($dateEndLastYear);
$endLastYear = date('Y-m-d', strtotime('-'.$duree.'day', $TimestampendLastYear));

// First day and last day of current month -
$firstDayCurrentMonth = date('Y-m-d', mktime(0, 0, 0, $month, 1, $year)); /// current year
$lastDayCurrentMonth = date('Y-m-t', mktime(0, 0, 0, $month, 1, $year));
$firstDayCurrentMonthLastYear = date('Y-m-d', mktime(0, 0, 1, $month, 1, $year - 1)); // last year
$lastDayCurrentMonthLastYear = date('Y-m-t', mktime(0, 0, 1, $month, 1, $year - 1));

// M - 1
$firstDayLastMonth = date('Y-m-d', mktime(0, 0, 0, $month - 1, 1, $year)); /// current year
$lastDayLastMonth = date('Y-m-t', mktime(0, 0, 0, $month - 1, 1, $year));

// First day and last day
$firstDayYear = date('Y-m-d', mktime(0, 0, 0, 1, 1, $year)); // current years
$lastDayYear = date('Y-m-t', mktime(0, 0, 1, 12, 1, $year));

$firstDayLastYear = date('Y-m-d', mktime(0, 0, 1, 1, 1, $year - 1)); // last year
$lastDayLastYear = date('Y-m-t', mktime(0, 0, 1, 12, 1, $year - 1));

// Load start month fiscal year for datas box and graph
$startFiscalYear = $conf->global->START_FISCAL_YEAR;
$startMonthFiscalYear = $object->startMonthForGraphLadder($startFiscalYear, 12);

/**
 * BOX 1
*/

$titleItem1 = "Chiffre d'affaires";
$info1 = "Chiffre d'affaire n-1";
$info2 = "Progression ";

// Fiscal current years
$total_invoice_year = $object->turnover($startFiscalYear, $endYear);
$total_deposit_year = $object->avoir($startFiscalYear, $endYear);

$totalCA_year = $total_invoice_year + $total_deposit_year;
$dataItem1 = price($totalCA_year)."\n€"; // Display datas

// Fiscal last years
$total_paid_invoice_lastyear = $object->fetchInvoices($startFiscalLastyear, $endLastYear);
$total_unpaid_invoice_lastyear = $object->fetchUnpaidInvoice($startFiscalLastyear, $endLastYear);
$total_deposit_lastyear = $object->avoir($startFiscalLastyear, $endLastYear);

$totalCA_lastyear = $total_paid_invoice_lastyear + $total_unpaid_invoice_lastyear;
$dataInfo1 = price($totalCA_lastyear)."\n€"; // Display datas

/**
* Dats infos for turnover's progress
*/

// CA by current month on current year
$invoice_month_year = $object->turnover($startFiscalYear, date('Y-m-d',$datetime));
$deposit_month_year = $object->avoir($startFiscalYear, date('Y-m-d',$datetime));
$total_month_year = $invoice_month_year + $deposit_month_year;

// CA by current month on last year
$datenow_lastyear = date('Y-m-d', mktime(0, 0, 1, $month, date('d'), $year - 1)); // last year

$total_invoice_month_lastyear = $object->turnover($startFiscalLastyear, $datenow_lastyear);
$total_deposit_month_lastyear = $object->avoir($startFiscalLastyear, $datenow_lastyear);
$closed_invoice = $object->closedInvoice($startFiscalLastyear, $datenow_lastyear);
$total_month_lastyear = $total_invoice_month_lastyear + $total_deposit_month_lastyear - $closed_invoice;

$result = $object->progress($total_month_year, $total_month_lastyear);
$dataInfo2 = $result . "\n%";

// Display increase/decrease
if($dataInfo2 < 0){
	$dataInfo2 = '<p style=color:red>'.$dataInfo2.'</p>';
} else {
	$dataInfo2 = '<p style=color:green>+'.$dataInfo2.'</p>';
}

// For first info popup
$firstPop_info1 = $titleItem1;
$firstPop_info2 = $info1;
$firstPop_info3 = $info2;

$firstPop_data1 = 'Factures clients validées <strong>('.price($totalCA_year + $total_deposit_year).' €)</strong> + Avoirs clients validées <strong>('.price(abs($total_deposit_year)).' €)</strong> sur l\'exercice fiscal en cours (HORS BROUILLON)';
$firstPop_data2 = 'Factures clients validées <strong>('.price($totalCA_lastyear - $total_deposit_lastyear).' €)</strong> + Avoirs clients validées <strong>('.price(abs($total_deposit_lastyear)).' €)</strong> sur l\'exercice fiscal en cours (HORS BROUILLON)';
$firstPop_data3 = "Taux de variation : ( (VA - VD) / VA) x 100 ) où </br> <strong> ( (".$total_month_year." - ".$total_month_lastyear.") / ".$total_month_year.") x 100 </strong>";

/**
 * GRAPH 1
 */

$monthsArr = monthArray($langs, 1); // months

$file = "evolutionCAchart";
$fileurl = DOL_DOCUMENT_ROOT.'/custom/tab/img';
$invoice = new Facture($db);
unset($yy);
for($mm = $startMonthFiscalYear; $mm < 13; $mm++){

	if(!$yy){
		$yy = $year;
	}

	if($mm == $startMonthFiscalYear && $yy == $year+1){
		break;
	}

	strtotime('Last Year');
	$lastyear = date($yy-1);
	$month = date('n');
	$lastDayMonth = cal_days_in_month(CAL_GREGORIAN, $mm, $yy);
	$lastDayMonthLastyear =  cal_days_in_month(CAL_GREGORIAN, $mm, $lastyear);

	// Start and end of each month on current years
	$date_start = $yy.'-'.$mm.'-01';
	$date_end = $yy.'-'.$mm.'-'.$lastDayMonth;

	// Fiscal Year
	$total_invoice_month_year += $object->turnover($date_start, $date_end);
	$total_deposit_month_year += $object->avoir($date_start, $date_end);

	$total_month_year = $total_invoice_month_year + $total_deposit_month_year;

	// Start and end of each month on last year
	$date_start_lastYear = $lastyear.'-'.$mm.'-01';
	$date_end_lastYear = $lastyear.'-'.$mm.'-'.$lastDayMonthLastyear;

	$invoice_paid_month_lastyear += $object->fetchInvoices($date_start_lastYear, $date_end_lastYear);
	$invoice_unpaid_month_lastyear += $object->fetchUnpaidInvoice($date_start_lastYear, $date_end_lastYear);

	$total_month_lastyear = $invoice_paid_month_lastyear + $invoice_unpaid_month_lastyear;

	// Last fiscal year
	if(date('n', $date_start) == $mm)
	{
		$total_month_year += $invoice->total_ht;
		$total_month_lastyear += $invoice->total_ht;
	}

	$data1[] = [
		html_entity_decode($monthsArr[$mm]),
		$total_month_lastyear,
		$total_month_year
	];

	if($mm >= 12){
		$mm = 0;
		$yy++;
	}
}

$px1 = new DolGraph();
$mesg = $px1->isGraphKo();
$legend = ['Exercice N-1', 'Exercice N'];

if (!$mesg){
	$px1->SetTitle("Evolution du chiffre d'affaires");
	$px1->datacolor = array(array(255,206,126), array(138,233,232));
	$px1->SetData($data1);
	$px1->SetLegend($legend);
	$px1->SetType(array('lines'));
	$px1->setHeight('250');
	$px1->SetWidth('500');
	$turnoverChart = $px1->draw($file, $fileurl);
}
$graphiqueA = $px1->show($turnoverChart);


/**
 * OUTSTANDING CUSTOMER AND SUPPLIER
 */

$titleItem2 = "Encours C/F";

// customer unpaid invoices
$info3 = '<a href="'.DOL_URL_ROOT.'/custom/tab/global/overview.php?mode=customer" id="customer">Encours C </a>';
$customerOustandingYear = $object->fetchCustomerInvoices(); // customer
$total_customerOustandingYear = array_sum($customerOustandingYear);

$dataInfo3 = price($total_customerOustandingYear) . "\n€";

if($dataInfo3 > 0){
	$dataInfo3 = '<p style=color:red>'.$dataInfo3.'</p>';
} else {
	$dataInfo3 = '<p style=color:green>Aucun encours client</p>';
}

//  Supplier unpaid invoices
$info4 = '<a href="'.DOL_URL_ROOT.'/custom/tab/global/overview.php?mode=supplier" id="supplier">Encours F </a>';
$supplierOustandingYear = $object->fetchSupplierInvoices();
$total_supplierOutstandingYear = array_sum($supplierOustandingYear);

$dataInfo4 = price($total_supplierOutstandingYear) . "\n€";

if($dataInfo4 > 0){
	$dataInfo4 = '<p style=color:red>'.$dataInfo4.'</p>';
} else {
	$dataInfo4 = '<p style=color:green>Aucun encours fournisseur</p>';
}

// Display total C/F
$totalOutstangdingCF = $total_customerOustandingYear - $total_supplierOutstandingYear;
$dataItem2 = price($totalOutstangdingCF) ."\n€";

/**
 * GRAPH OUSTANDING BILL
*/

// Current Year | CUSTOMER
$file = "oustandingCustomerChartNumberAndAmount";
$fileurl = DOL_DOCUMENT_ROOT.'/custom/tab/img';
$invoice = new Facture($db);
unset($yy);

for($mm = $startMonthFiscalYear; $mm < 13; $mm++){

	if(!$yy){
		$yy = $year;
	}

	if($mm == $startMonthFiscalYear && $yy == $year+1){
		break;
	}

	// Current Year
	$date_start = $yy.'-'.$mm.'-01';
	$date_end = $yy.'-'.$mm.'-'.$lastDayMonth;

	$array_customer_outstanding_year = $object->outstandingBill($date_start, $date_end);
	$nb_total_customer_outstanding_year = count($array_customer_outstanding_year); // number
	$amount_total_customer_outstanding_year = array_sum($array_customer_outstanding_year); // amount


	if(date('n', $date_start) == $mm){
		$nb_total_customer_outstanding_year = $invoice->total_ttc;
		$amount_total_customer_outstanding_year = $invoice->total_ttc;
	}

	$data2[] = [
		$ladder = html_entity_decode($monthsArr[$mm]), // months
		$nb_total_customer_outstanding_year, // nb
		$amount_total_customer_outstanding_year, // amount
	];

	if($mm >= 12){
		$mm = 0;
		$yy++;
	}
}

$px2 = new DolGraph();
$mesg = $px2->isGraphKo();
$legend = ['Nombre', 'Montant'];

if (!$mesg){
	$px2->SetTitle("Evolution des encours clients - HT ");
	$px2->datacolor = array(array(255,99,71), array(128, 187, 240));
	$px2->SetData($data2);
	$px2->SetLegend($legend);
	$px2->SetType(array('bar'));
	$px2->setHeight('250');
	$px2->SetWidth('500');
	$total_supplier_outstanding_year = $px2->draw($file, $fileurl);
}


/**
 * Supplier chart
 */

 // Current Year | supplier
$file = "supplierChartNumberAndAmountofYear";
$fileurl = DOL_DOCUMENT_ROOT.'/custom/tab/img';
$invoice_supplier = new FactureFournisseur($db);
unset($yy);

for($mm = $startMonthFiscalYear; $mm < 13; $mm++){

	if(!$yy){
		$yy = $year;
	}

	if($mm == $startMonthFiscalYear && $yy == $year+1){
		break;
	}

	// Current Year
	$date_start = $yy.'-'.$mm.'-01';
	$date_end = $yy.'-'.$mm.'-'.$lastDayMonth;

	$array_supplier_outstanding_year = $object->outstandingSupplier($date_start, $date_end, 0);

	$nb_total_supplier_outstanding_year = count($array_supplier_outstanding_year); // number
	$amount_total_supplier_outstanding_year = array_sum($array_supplier_outstanding_year); // amount


	if(date('n', $date_start) == $mm){
		$nb_total_supplier_outstanding_year += $invoice_supplier->total_ttc;
		$amount_total_supplier_outstanding_year += $invoice_supplier->total_ttc;
	}

	$data3[] = [
		html_entity_decode($monthsArr[$mm]), // months
		$nb_total_supplier_outstanding_year, // nb
		$amount_total_supplier_outstanding_year, // amount
	];

	if($mm >= 12){
		$mm = 0;
		$yy++;
	}
}

$px4 = new DolGraph();
$mesg = $px4->isGraphKo();
$legend = ['Nombre', 'Montant'];

if (!$mesg){
	$px4->SetTitle("Evolution des encours fournisseurs TTC - ".$year);
	$px4->datacolor = array(array(49, 27, 146), array(56, 142, 60));
	$px4->SetData($data3);
	$px4->SetLegend($legend);
	$px4->SetType(array('bar'));
	$px4->setHeight('250');
	$px4->SetWidth('500');
	$total_customer_outstanding_year = $px4->draw($file, $fileurl);
}



// Display type of graph (customer or supplier)
$mode = $_GET['mode'];
$filter = $_GET['filter'];
$lastyear = strtotime('Last Year');
$lastyear = date($year-1);


if($mode == 'customer'){
 	?>
	<style>
		a#customer
		{
			border-bottom: 1px solid #29a3a3;
			color: #29a3a3;
		}
	</style>
		<?php
			$graphiqueB = $px2->show($total_customer_outstanding_year);
	}
	elseif($mode == 'supplier')
	{
		?>
		<style>
		a#supplier
		{
			border-bottom: 1px solid #29a3a3;
			color: #29a3a3;
		}

		</style>
		<?php

			$graphiqueB2 = $px4->show($total_supplier_outstanding_year);
		}

// For second popup info
$secondPop_info1 = $titleItem2;
$secondPop_info2 = $info3;
$secondPop_info3 = $info4;

$secondPop_data1 = " Factures clients impayées <strong>(".price($total_customerOustandingYear)." €)</strong> - factures fournisseurs impayées <strong>(".price($total_supplierOutstandingYear)." €)</strong>";
$secondPop_data2 = " Somme de toutes les factures clients impayées (Hors brouillon - cumul des années passées) : <strong>".price($total_customerOustandingYear)."\n€</strong>";
$secondPop_data3 = "Somme de toutes les factures fournisseurs impayées (hors brouillon - cumul des années passées) : <strong>".price($total_supplierOutstandingYear)."\n€</strong>";


/**
 *  MARGIN BOXE
 */
$titleItem3 = "Marge brute N";

// For calcul gross Margin
$arrDepositMargin = $object->avoirForMargin($startFiscalYear, $endYear);

// On parcours les avoirs, et on recupere la somme total de la marge
foreach($arrDepositMargin as $val){
	$res1 = $invoice->fetch($val->rowid);
	$linesArr = $invoice->lines;

	foreach($linesArr as $line){
		$costpriceDeposit += $line->pa_ht * $line->qty;
		$totalHTdeposit += $line->total_ht;
	}
	$marginDeposit = $costpriceDeposit + $totalHTdeposit;
}

// On parcours les factures, et on recupere la somme total de la marge
$invoicesArr = $object->fetchInvoice($startFiscalYear, $endYear);
$invoice = new Facture($db);
foreach($invoicesArr as $fac){

	$res2 = $invoice->fetch($fac->rowid);
	$linesArr = $invoice->lines;

	foreach($linesArr as $line){
		$costprice += $line->pa_ht * $line->qty;
		$totalHT += $line->total_ht;
	}
	$margin = $totalHT - $costprice;
}

$grossMargin = $margin + $marginDeposit;
// On additionne la somme total de la marge des factures + celle des avoirs
$dataItem3 = price($grossMargin) . "\n€";

$info6 = "Marge brut prévisionnelle";
$forecastMargin = $conf->global->FORECAST_MARGIN; // manual entry
$dataInfo6 = $forecastMargin."\n€";

// Margin To produce on current mounth
$info5 = "Marge restant à produire";
$dataInfo5 = price($forecastMargin - $grossMargin);


// Graph
$file = "marginChart";
$fileurl = DOL_DOCUMENT_ROOT.'/custom/tab/img';
$invoice = new Facture($db);
unset($yy);

for($mm = $startMonthFiscalYear; $mm < 13; $mm++){

	if(!$yy){
		$yy = $year;
	}

	if($mm == $startMonthFiscalYear && $yy == $year+1){
		break;
	}

	strtotime('Last Year');
	$lastyear = date($year-1);

	$lastDayMonth = cal_days_in_month(CAL_GREGORIAN, $mm, $year);
	$lastDayMonthLastyear =  cal_days_in_month(CAL_GREGORIAN, $mm, $lastyear);

	// Current Year
	$date_start = $yy.'-'.$mm.'-01';
	$date_end = $yy.'-'.$mm.'-'.$lastDayMonth;

	$arrStandardInvoiceYear = $object->fetchInvoice($date_start, $date_end); // current
	$arrDepositMarginForGraph = $object->avoirForMargin($date_start, $date_end);


	if(date('n', $date_start) == $mm){
		$totalMarginGraph += $invoice->total_ht;
		// $total_standard_invoice_LastYear += $invoice->total_ht;
	}

	// On parcours les avoirs, et on recupere la somme total de la marge pour chaque mois
	foreach($arrDepositMarginForGraph as $dep){
		$invoice->fetch($dep->rowid);
		$linesArr = $invoice->lines;

		foreach($linesArr as $line){
			$costpriceDepositGraph += $line->pa_ht * $line->qty;
			$totalHTdepositGraph += $line->total_ht;
		}
		$marginDepositGraph = $costpriceDepositGraph + $totalHTdepositGraph;
	}

	foreach($arrStandardInvoiceYear as $acc){

		$invoice->fetch($acc->rowid);
		$linesArr = $invoice->lines;

		foreach($linesArr as $line){
			$costpriceGraph += $line->pa_ht * $line->qty;
			$totalHTGraph += $line->total_ht;
		}
		$marginYear = $totalHTGraph - $costpriceGraph;
	}
	$totalMarginGraph = $marginYear + $marginDepositGraph ;


	$data[] = [
		html_entity_decode($monthsArr[$mm]), // month
		$totalMarginGraph,
	];

	if($mm >= 12){
		$mm = 0;
		$yy++;
	}
}

$px6 = new DolGraph();
$mesg = $px6->isGraphKo();
$legend = ['Exercice N'];

if (!$mesg){
	$px6->SetTitle("Evolution du montant de la marge brute");
	$px6->datacolor = array(array(138,233,232));
	$px6->SetData($data);
	$px6->SetLegend($legend);
	$px6->SetType(array('lines')); // Array with type for each serie. Example: array('type1', 'type2', ...) where type can be: 'pie', 'piesemicircle', 'polar', 'lines', 'linesnopoint', 'bars', 'horizontalbars'...
	$px6->setHeight('250');
	$px6->SetWidth('500');
	$marginChart = $px6->draw($file, $fileurl);
}

$graphiqueC = $px6->show($marginChart);


// For margin info popup
$thirdPop_info1 = $titleItem3;
$thirdPop_info2 = $info5;
$thirdPop_info3 = $info6;

$thirdPop_data1 = "Somme totale de la marge des factures client validées <strong>(".price($grossMargin)."\n€)</strong> sur l'exercice fiscal en cours";
$thirdPop_data2 = "La marge brute sur l'exercice fiscal en cours <strong>(".price($grossMargin)."\n€)</strong> - la marge brute prévisionnelle <strong>(".price($grossMargin)."\n€)</strong> ";
$thirdPop_data3 = "Définit dans la configuration du module : <strong>(".price($forecastMargin)."\n€)</strong>";

/**
 * ---- TREASURY BOX -------
 */

 // the smallest id of the base will always correspond to the current account of the said company
$idaccounts = $object->fetchAllBankAccount();
$currentAccount = min($idaccounts);
$currentAccount = (int)$currentAccount;

 // Current balance on n year
$solde = $object->totalSoldeCurrentAccount($currentAccount);

// details datas for popupinfo (variables charges)
$date_now = date('Y-m-d', dol_now());

$titleItem4 = "Trésorerie prévisionnelle";
$info7 = "Charges mensuelles";
$info8 = "Recurrent mensuel";


/**
 * datas calcul tresury
*/

/**
 *  Money flow out :
*/

// Static Expenses details (on prev month)
var_dump($firstDayLastMonth, $lastDayLastMonth);
$arr_salarys = $object->fetchSalarys($firstDayLastMonth, $lastDayLastMonth, $currentAccount);

$socialesTaxes_charges = $object->fetchSocialAndTaxesCharges($firstDayLastMonth, $lastDayLastMonth, $currentAccount);
$emprunts = $object->fetchEmprunts($firstDayLastMonth, $lastDayLastMonth, $currentAccount);
$staticExpenses = ($arr_salarys + $socialesTaxes_charges + $emprunts); // static expenses total

// TODO : vat by current month
// $total_vat_by_month = $object->fetchTVA($firstDayLastMonth, $lastDayLastMonth);
$total_expense = $object->fetchExpenses($firstDayLastMonth, $lastDayLastMonth); // expenses

// Total Money flow out
$totalMoneyOut = ($staticExpenses + $total_vat_by_month + $total_expense + $total_supplierOutstandingYear);

/**
 *  Money flow in
 */

// facture client impayes
$array_modelInvoice = $object->fetchModelInvoices($firstDayCurrentMonth, $lastDayCurrentMonth);
$total_modelInvoice = array_sum($array_modelInvoice);

// Monthly Charge
$dataInfo8 = price($total_modelInvoice) . "\n€";

// avoir fournisseur impayees
$creditnote_unpaid_supplier_year = $object->allSupplierUnpaidDeposit($startFiscalYear, $endYear);
$creditnote_unpaid_supplier_year = abs($creditnote_unpaid_supplier_year); //convert negative amount to positive for calculation

$moneyFlowIn = $total_modelInvoice + $creditnote_unpaid_supplier_year;

/**
 *  End Money flow in
 */

// Variable expenses
$array_suppliers_invoice_paid = $object->outstandingSupplier($firstDayCurrentMonth, $lastDayCurrentMonth, 1 ); // paid
$total_suppliers_invoice_paid = array_sum($array_suppliers_invoice_paid);

$array_suppliers_invoice_unpaid = $object->outstandingSupplier($firstDayCurrentMonth, $lastDayCurrentMonth, 0); // unpaid
$total_suppliers_invoice_unpaid = array_sum($array_suppliers_invoice_unpaid);
$total_suppliers_invoice_paid_and_unpaid = $total_suppliers_invoice_unpaid + $total_suppliers_invoice_paid;

$variousPaiements = $object->fetchVariousPaiements($firstDayCurrentMonth, $lastDayCurrentMonth, $currentAccount);
$variablesExpenses = $total_suppliers_invoice_paid_and_unpaid + $total_vat_by_month + $variousPaiements;

// Monthly charge
$totalMonthlyExpenses = floatval( ($variablesExpenses + $staticExpenses));
$dataInfo7 = price($totalMonthlyExpenses) . "\n€";

$tresury = $solde - $totalMoneyOut + $moneyFlowIn; // calcul for net tresury
$dataItem4 = price($tresury) . "\n€"; // Display tresury

// Graph tresury
$file = "tresuryChart";
$fileurl = DOL_DOCUMENT_ROOT.'/custom/tab/img';
unset($yy);

for($mm = 7; $mm < 13; $mm++){

	if(!$yy){
		$yy = $year;
	}

	if($mm == $startMonthFiscalYear && $yy == $year+1){
		break;
	}

	$lastDayMonth = cal_days_in_month(CAL_GREGORIAN, $mm, $year);

	// Current Year
	// $date_start = $yy.'-'.$mm.'-01'; // first day of month
	// $date_end = $yy.'-'.$mm.'-'.$lastDayMonth; // last day of month

	$soldeByMonth = $object->totalSoldeCurrentAccount($currentAccount);


	// if(date('n', $date_start) == $mm) {
		$tresury = $soldeByMonth - $totalMoneyOut + $moneyFlowIn;
	// }

	$data5[] = [
		html_entity_decode($monthsArr[$mm]), // month
		$tresury,
	];

	if($mm >= 12){
		$mm = 0;
		$yy++;
	}
}

	// var_dump($total_solde, $totalMoneyOut, $moneyFlowIn);

$px7 = new DolGraph();
$mesg = $px7->isGraphKo();
$legend = ['Année N'];

if (!$mesg){
	$px7->SetTitle("Evolution de la trésorerie nette");
	$px7->datacolor = array(array(138,233,232));
	$px7->SetLegend($legend);
	$px7->SetData($data5);
	$px7->SetType(array('lines'));
	$px7->setHeight('250');
	$px7->SetWidth('500');
	$tresuryChart = $px7->draw($file, $fileurl);
}

$graphiqueD = $px7->show($tresuryChart);

/**
 * For tresury info popup
 */
$fourPop_info1 = $titleItem4;
$fourPop_info2 = $info7;
$fourPop_info3 = $info8;

// Reset of the variable $currentAccount to fill in the information of the popup
$currentAccount = min($idaccounts);

$fourPop_data1 = '<i>Solde du compte en banque <strong>'.$currentAccount->bank.'</strong> : ('.price($solde).'€) - sortie d\'argent ('.$totalMoneyOut.'€) + entrée d\'argent ('.$moneyFlowIn.'€)</i>
				  <br> <strong> SORTIE </strong> :
				  <ul>
				  	<li><strong>Charges fixes*</strong> ('.price($staticExpenses).' €) </li>
					<li><strong>Factures fournisseurs impayées </strong> : ('.price($total_supplierOutstandingYear).' €) <i style="color:red;"></br> Attention, elles doivent obligatoiremment renseigner une <strong>date limite de réglement</strong></i></li>
					<li><strong>TVA (client et fournisseur)</strong> : indisponible </li>
					<li><strong>Notes de frais (validée et approuvée)</strong> ('.price($total_expense).'€)</li>
				  </ul>
				  <br> <strong> ENTREE </strong> :
				  <ul>
				  	<li><strong>Factures clients impayées </strong> ('.price($total_modelInvoice).'€)</li>
					<li><strong>Avoir fournisseurs impayées </strong> : ('.price($creditnote_unpaid_supplier_year).'€) </br> <i style="color:red;"> Attention, ils doivent obligatoiremment renseigner une <strong>date limite de réglement</strong></i></li>
				  </ul></br>';

$fourPop_data2 = "<ul><li>charges variables (".price($variablesExpenses)."\n€)</strong> + charges fixes (".price($staticExpenses)."\n€)</strong> </li>

				<br> <strong> <li>Détail charges fixes </strong> : Salaires (".price($arr_salarys)."\n€) </strong> +  emprunts (".price($emprunts)."\n€) </strong> + charges sociales et fiscales (".price($socialesTaxes_charges)."\n€) </strong> </li>
				<i style='color:blue;'>Les charges fixes sont calculées sur le mois précédent </i></br>
				<br> <strong> <li> Détail charges variables </strong> :  Factures fournisseurs impayées + payées sur le mois courant (".price($total_suppliers_invoice_paid_and_unpaid)."\n€) </strong> + paiements divers (".price($variousPaiements)."\n€)</strong> + TVA du mois courant (indisponible) </strong> </li>
				</ul>";

$fourPop_data3 = "Montant total (TTC) des modèles de factures client ".price($total_modelInvoice)."\n€";

/*
 * Actions
 */

// None

/*
 * View
 */

$form = new Form($db);
$formfile = new FormFile($db);
$object = new General($db);

// Display NavBar
llxHeader('', $langs->trans("Global - Général"));

print load_fiche_titre($langs->trans("Général"));

print $object->load_navbar();

// Load translation files required by the page
$langs->loadLangs(array("other", "compta", "banks", "bills", "companies", "product", "trips", "admin"));

include DOL_DOCUMENT_ROOT . '/custom/tab/template/template_boxes4.php';

// End of page
llxFooter();
$db->close();
