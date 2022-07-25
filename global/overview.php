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

$datetime = dol_now();
$year = dol_print_date($datetime, "%Y");
$month = dol_print_date($datetime, "%m");
$day = dol_print_date($datetime, "%d");

// Calcul for last day in current year according to the beginning of the fiscal year
$duree = 1;

// Transform date in timestamp
$TimestampCurrentYear = strtotime($startFiscalyear);
$TimestampCurrentLastYear = strtotime($startFiscalLastyear);

// calcul the end date for current and last year
$endYear = date('Y-m-d', strtotime('+'.$duree.'year', $TimestampCurrentYear));
$endLastYear = date('Y-m-d', strtotime('+'.$duree.'year', $TimestampCurrentLastYear));

// First day and last day of current mounth
$firstDayCurrentMonth = date('Y-m-d', mktime(0, 0, 0, $month, 1, $year));
$lastDayCurrentMonth = date('Y-m-t', mktime(0, 0, 0, $month, 1, $year));

// M - 1
$firstDayLastMonth = date('Y-m-d', mktime(0, 0, 1, $month - 1, 1, $year));
$lastDayLastMonth = date('Y-m-t', mktime(0, 0, 1, $month - 1, 1, $year));

// First day and last day of current years
$firstDayYear = date('Y-m-d', mktime(0, 0, 0, 1, 1, $year));
$lastDayYear = date('Y-m-t', mktime(0, 0, 1, 12, 1, $year));

// N - 1
$firstDayLastYear = date('Y-m-d', mktime(0, 0, 1, 1, 1, $year - 1));
$lastDayLastYear = date('Y-m-t', mktime(0, 0, 1, 12, 1, $year - 1));

$nowyear = strftime("%Y", dol_now());
$year = GETPOST('year') > 0 ? GETPOST('year', 'int') : $nowyear;
$startyear = $year - (empty($conf->global->MAIN_STATS_GRAPHS_SHOW_N_YEARS) ? 2 : max(1, min(10, $conf->global->MAIN_STATS_GRAPHS_SHOW_N_YEARS)));
$endyear = $year;

// Load start month fiscal year for datas graph
$startFiscalYear = $conf->global->START_FISCAL_YEAR;
$startMonthFiscalYear = $object->startMonthForGraphLadder($startFiscalYear, 12);

/**
 * BOX 1
*/

$titleItem1 = "Chiffre d'affaires";
$info1 = "Chiffre d'affaire n-1";
$info2 = "Progression ";

// Fiscal current years
$total_standard_invoice = $object->turnover($startFiscalyear, $endYear); // paye + imp
$total_avoir_invoice = $object->avoir($startFiscalyear, $endYear, $paye = ''); // paye + imp
$total_avoir_invoice = abs($total_avoir_invoice);

$total_CA = $total_standard_invoice + $total_avoir_invoice; // total

$dataItem1 = price($total_CA)."\n€"; // display datas

// Fiscal last years
$total_standard_invoice_lastYear = $object->turnover($startFiscalLastyear, $endLastYear); // paye + imp
$total_avoir_invoice_lastYear = $object->avoir($startFiscalLastyear, $endLastYear, $paye = ''); // paye + imp

$total_CA_lastYear = $total_standard_invoice_lastYear + $total_avoir_invoice_lastYear; // total

$dataInfo1 = price($total_CA_lastYear)."\n€"; // display data

// progression turnover
$result = $object->progress($total_CA, $total_CA_lastYear);
$dataInfo2 = intval($result)  . "\n%";

// Display increase/decrease
if($dataInfo2 < 0){
	$dataInfo2 = '<p style=color:red>'.$dataInfo2.'</p>';
} else {
	$dataInfo2 = '<p style=color:green>'.$dataInfo2.'</p>';
}

/**
 * GRAPH 1
 */

$monthsArr = monthArray($langs, 1); // months

$file = "evolutionCAchart";
$fileurl = DOL_DOCUMENT_ROOT.'/custom/tab/img';
$invoice = new Facture($db);
$total_CA = $total_standard_invoice + abs($total_avoir_invoice);

	for($i = $startMonthFiscalYear; $i <= 12 ; $i++){

		strtotime('Last Year');
		$lastyear = date($year-1);

		$lastDayMonth = cal_days_in_month(CAL_GREGORIAN, $i, $year);
		$lastDayMonthLastyear =  cal_days_in_month(CAL_GREGORIAN, $i, $lastyear);

		// Start and end of each month on current years
		$date_start = $year.'-'.$i.'-01';
		$date_end = $year.'-'.$i.'-'.$lastDayMonth;

		// Start and end of each month on last year
		$date_start_lastYear = $lastyear.'-'.$i.'-01';
		$date_end_lastYear = $lastyear.'-'.$i.'-'.$lastDayMonthLastyear;

		$total_standard_invoice_Year += $object->turnover($date_start, $date_end); // current
		$total_standard_invoice_LastYear += $object->turnover($date_start_lastYear, $date_end_lastYear); // last year

		if(date('n', $date_start) == $i){
			$total_standard_invoice_Year += $invoice->total_ht;
			$total_standard_invoice_LastYear += $invoice->total_ht;
		}

		// Ex : mois de début = Février ("2" + 10) //

			$data[] = [
				html_entity_decode($monthsArr[$i]),
				$total_standard_invoice_LastYear,
				$total_standard_invoice_Year
			];
		}


$px1 = new DolGraph();
$mesg = $px1->isGraphKo();
$legend = ['Année N-1', 'Année N'];

if (!$mesg){
	$px1->SetTitle("Evolution du chiffre d'affaires");
	$px1->datacolor = array(array(255,206,126), array(138,233,232));
	$px1->SetData($data);
	$px1->SetLegend($legend);
	$px1->SetType(array('lines'));
	$px1->setHeight('250');
	$px1->SetWidth('500');
	$turnoverChart = $px1->draw($file, $fileurl);
}
$graphiqueA = $px1->show($turnoverChart);

// For first info popup
$firstPop_info1 = $titleItem1;
$firstPop_info2 = $info1;
$firstPop_info3 = $info2;

// All unpaid or paid invoice on current year and last year
$total_unpaid_invoice_year = $object->allInvoice($startFiscalyear, $endYear, $paye = 0);
$total_paid_invoice_year = $object->allInvoice($startFiscalyear, $endYear, $paye = 1);

$total_unpaid_invoice_lastyear = $object->allInvoice($startFiscalLastyear, $endLastYear, $paye = 0);
$total_paid_invoice_lastYear = $object->allInvoice($startFiscalLastyear, $endLastYear, $paye = 1);

// All Unpaid / pay desposit on current year and last year
$total_unpaid_deposit_year = $object->allDeposit($startFiscalyear, $endYear, $paye = 0);
$total_paid_deposit_year = $object->allDeposit($startFiscalyear, $endYear, $paye = 1);

$total_unpaid_deposit_lastYear = $object->allDeposit($startFiscalLastyear, $endLastYear, $paye = 0);
$total_paid_deposit_lastYear = $object->allDeposit($startFiscalLastyear, $endLastYear, $paye = 1);

$firstPop_data1 = 'Factures clients impayées <strong>('.price($total_unpaid_invoice_year).' €)</strong> + payées <strong>('.price($total_paid_invoice_year).' €)</strong> + Avoirs clients impayés <strong>('.price(abs($total_unpaid_deposit_year)).' €)</strong> + payés <strong>('.price(abs($total_paid_deposit_year)).' €)</strong> sur l\'exercice fiscal en cours (HORS BROUILLON)';
$firstPop_data2 = 'Factures clients impayées <strong>('.price($total_unpaid_invoice_lastyear).' €)</strong> + payées <strong>('.price($total_paid_invoice_lastYear).' €)</strong> + Avoirs clients impayés <strong>('.price($total_unpaid_deposit_lastYear).' €)</strong> + payés <strong>('.price($total_paid_deposit_lastYear).' €)</strong> sur l\'exercice fiscal en cours (HORS BROUILLON)';
$firstPop_data3 = "Taux de variation : ( (VA - VD) / VA) x 100 ) </br> <strong> ( (".$total_CA." - ".$total_CA_lastYear.") / ".$total_CA.") x 100 </strong>";


/**
 * OUTSTANDING CUSTOMER AND SUPPLIER
 */

$titleItem2 = "Encours C/F";

// supplier unpaid invoices on last year
$supplierOutstandingYear = $object->outstandingSupplier($startFiscalLastyear, $endYear, 0); // supplier
$total_supplierOutstandingYear = array_sum($supplierOutstandingYear);

$info3 = '<a href="'.DOL_URL_ROOT.'/custom/tab/global/overview.php?mode=customer" id="customer">Encours C </a>';

// customer unpaid invoices on n fiscal year
$customerOustandingYear = $object->outstandingBill($startFiscalYear, $endYear); // customer
$total_customerOustandingYear = array_sum($customerOustandingYear);

$dataInfo3 = price($total_customerOustandingYear) . "\n€";

if($dataInfo3 > 0){
	$dataInfo3 = '<p style=color:red>'.$dataInfo3.'</p>';
} else {
	$dataInfo3 = '<p style=color:green>Aucun encours client sur la période <strong>'.$startFiscalYear.' à '.$endYear.'</strong> </p>';
}

//  Supplier unpaid invoices on n fiscal year
$info4 = '<a href="'.DOL_URL_ROOT.'/custom/tab/global/overview.php?mode=supplier" id="supplier">Encours F </a>';
$supplierOustandingYear = $object->outstandingSupplier($startFiscalYear, $endYear, 0);
$total_supplierOutstandingYear = array_sum($supplierOustandingYear);

$dataInfo4 = price($total_supplierOutstandingYear) . "\n€";

if($dataInfo4 > 0){
	$dataInfo4 = '<p style=color:red>'.$dataInfo4.'</p>';
} else {
	$dataInfo4 = '<p>Aucun encours fournisseur</p>';
}

// Display total C/F
$totalOutstangdingCF = $total_customerOustandingYear - $total_supplierOutstandingYear;
$dataItem2 = price($totalOutstangdingCF)  ."\n€";


/**
 * Customer chart
 */

// Drawing the first graph for nb of customer invoices by month
$file = "oustandingCustomerChartNumber";
$fileurl = DOL_DOCUMENT_ROOT.'/custom/tab/img';
$invoice = new Facture($db);
$data = [];
$i = $startMonthFiscalYear;

for($i; $i <= 12; $i++){

	$lastyear = strtotime('Last Year');
	$lastyear = date($year-1);

	$lastDayMonth = cal_days_in_month(CAL_GREGORIAN, $i, $year);
	$lastDayMonthLastyear =  cal_days_in_month(CAL_GREGORIAN, $i, $lastyear);

	// Current Year
	$date_start = $year.'-'.$i.'-01';
	$date_end = $year.'-'.$i.'-'.$lastDayMonth;

	$array_customer_outstanding_year = $object->outstandingBill($date_start, $date_end);
	$nb_total_customer_outstanding_year = count($array_customer_outstanding_year);

	//  Last year
	$date_start_lastYear = $lastyear.'-'.$i.'-01';
	$date_end_lastYear = $lastyear.'-'.$i.'-'.$lastDayMonthLastyear;

	$array_customer_outstanding_LastYear = $object->outstandingBill($date_start_lastYear, $date_end_lastYear);
	$nb_total_customer_outstanding_LastYear = count($array_customer_outstanding_LastYear);


	if(date('n', $date_start) == $i ){
		$nb_total_customer_outstanding_year += $invoice->total_ttc;
		$nb_total_customer_outstanding_LastYear += $invoice->total_ttc;
	}

	$ladder = html_entity_decode($monthsArr[$i]);

	$data[] = [
		$ladder, // months
		$nb_total_customer_outstanding_LastYear, // last Year
		$nb_total_customer_outstanding_year // Current Year
	];
}

$px2 = new DolGraph();
$mesg = $px2->isGraphKo();
$legend = ['Année N-1', 'Année N'];

if (!$mesg){
	$px2->SetTitle("Nb d'encours clients par mois - TTC");
	$px2->datacolor = array(array(255,206,126), array(138,233,232));
	$px2->SetData($data);
	$px2->SetLegend($legend);
	$px2->SetType(array('bar'));
	$px2->setHeight('250');
	$px2->SetWidth('500');
	$customer_oustanding_number_chart = $px2->draw($file, $fileurl);
}

// Drawing the second  graph for amount of customer invoices by month
$file = "OustandingSupplierChartAmount";
$fileurl = DOL_DOCUMENT_ROOT.'/custom/tab/img';
$data = []; // reset data
$i = $startMonthFiscalYear;

for($i; $i <= 12; $i++){

	$lastyear = strtotime('Last Year');
	$lastyear = date($year-1);

	$lastDayMonth = cal_days_in_month(CAL_GREGORIAN, $i, $year);
	$lastDayMonthLastyear =  cal_days_in_month(CAL_GREGORIAN, $i, $lastyear);

	// Current Year
	$date_start = $year.'-'.$i.'-01';
	$date_end = $year.'-'.$i.'-'.$lastDayMonth;

	$array_customer_outstanding_year = $object->outstandingBill($date_start, $date_end);
	$amount_total_customer_outstanding_year = array_sum($array_customer_outstanding_year); // amount

	// Last year
	$date_start_lastYear = $lastyear.'-'.$i.'-01';
	$date_end_lastYear = $lastyear.'-'.$i.'-'.$lastDayMonthLastyear;


	$array_customer_outstanding_LastYear = $object->outstandingBill($date_start_lastYear, $date_end_lastYear);
	$amount_total_customer_outstanding_LastYear = array_sum($array_customer_outstanding_LastYear); // amount

	if(date('n', $date_start) == $i ){
		$amount_total_customer_outstanding_year += $invoice_customer->total_ttc;
		$amount_total_customer_outstanding_LastYear += $invoice_customer->total_ttc;
	}

	$ladder = html_entity_decode($monthsArr[$i]);

	$data[] = [
		$ladder, // months
		$amount_total_customer_outstanding_LastYear,
		$amount_total_customer_outstanding_year
	];
}

$px3 = new DolGraph();
$mesg = $px3->isGraphKo();
$legend = ['Année N-1', 'Année N'];

if (!$mesg){
	$px3->SetTitle("Montant des encours clients - TTC");
	$px3->datacolor = array(array(255,206,126), array(138,233,232));
	$px3->SetData($data);
	$px3->SetLegend($legend);
	$px3->SetType(array('bar'));
	$px3->setHeight('250');
	$px3->SetWidth('500');
	$customer_outstanding_amount_chart = $px3->draw($file, $fileurl);
}


// SUPPLIER CHART

// Drawing the first graph for nb of suppliers invoices by month
$file = "oustandingSupplierChartNumber";
$fileurl = DOL_DOCUMENT_ROOT.'/custom/tab/img';
$invoice = new Facture($db);
$data = [];
$i = $startMonthFiscalYear;

for($i; $i <= 12; $i++){

	$lastyear = strtotime('Last Year');
	$lastyear = date($year-1);

	$lastDayMonth = cal_days_in_month(CAL_GREGORIAN, $i, $year);
	$lastDayMonthLastyear =  cal_days_in_month(CAL_GREGORIAN, $i, $lastyear);

	// Current Year
	$date_start = $year.'-'.$i.'-01';
	$date_end = $year.'-'.$i.'-'.$lastDayMonth;

	$array_supplier_outstanding_year = $object->outstandingSupplier($date_start, $date_end, 0);
	$nb_total_supplier_outstanding_year = count($array_supplier_outstanding_year);

	//  Last year
	$date_start_lastYear = $lastyear.'-'.$i.'-01';
	$date_end_lastYear = $lastyear.'-'.$i.'-'.$lastDayMonthLastyear;

	$array_supplier_outstanding_LastYear = $object->outstandingSupplier($date_start_lastYear, $date_end_lastYear, 0);
	$nb_total_supplier_outstanding_LastYear = count($array_supplier_outstanding_LastYear);


	if(date('n', $date_start) == $i ){
		$nb_total_supplier_outstanding_year += $invoice->total_ttc;
		$nb_total_supplier_outstanding_LastYear += $invoice->total_ttc;
	}

	$ladder = html_entity_decode($monthsArr[$i]);

	$data[] = [
		$ladder, // months
		$nb_total_supplier_outstanding_LastYear, // last Year
		$nb_total_supplier_outstanding_year // Current Year
	];
}

$px4 = new DolGraph();
$mesg = $px4->isGraphKo();
$legend = ['Année N-1', 'Année N'];

if (!$mesg){
	$px4->SetTitle("Nb d'encours fournisseurs par mois - TTC");
	$px4->datacolor = array(array(255,206,126), array(138,233,232));
	$px4->SetData($data);
	$px4->SetLegend($legend);
	$px4->SetType(array('bar'));
	$px4->setHeight('250');
	$px4->SetWidth('500');
	$supplier_oustanding_number_chart = $px4->draw($file, $fileurl);
}

// Drawing the second graph for amount of supplier invoices by month
$file = "oustandingSupplierChartAmount";
$fileurl = DOL_DOCUMENT_ROOT.'/custom/tab/img';
$invoice = new Facture($db);
$data = [];
$i = $startMonthFiscalYear;

for($i; $i <= 12; $i++){

	$lastyear = strtotime('Last Year');
	$lastyear = date($year-1);

	$lastDayMonth = cal_days_in_month(CAL_GREGORIAN, $i, $year);
	$lastDayMonthLastyear =  cal_days_in_month(CAL_GREGORIAN, $i, $lastyear);

	// Current Year
	$date_start = $year.'-'.$i.'-01';
	$date_end = $year.'-'.$i.'-'.$lastDayMonth;

	$array_supplier_outstanding_year = $object->outstandingSupplier($date_start, $date_end, 0);
	$amount_total_supplier_outstanding_year = array_sum($array_supplier_outstanding_year);

	//  Last year
	$date_start_lastYear = $lastyear.'-'.$i.'-01';
	$date_end_lastYear = $lastyear.'-'.$i.'-'.$lastDayMonthLastyear;

	$array_supplier_outstanding_LastYear = $object->outstandingSupplier($date_start_lastYear, $date_end_lastYear, 0);
	$amount_total_supplier_outstanding_LastYear = array_sum($array_supplier_outstanding_LastYear);


	if(date('n', $date_start) == $i ){
		$amount_total_supplier_outstanding_year += $invoice->total_ttc;
		$amount_total_supplier_outstanding_LastYear += $invoice->total_ttc;
	}

	$ladder = html_entity_decode($monthsArr[$i]);

	$data[] = [
		$ladder, // months
		$amount_total_supplier_outstanding_LastYear, // last Year
		$amount_total_supplier_outstanding_year // Current Year
	];
}

$px5 = new DolGraph();
$mesg = $px5->isGraphKo();
$legend = ['Année N-1', 'Année N'];

if (!$mesg){
	$px5->SetTitle("Montant des encours fournisseurs par mois - TTC");
	$px5->datacolor = array(array(255,206,126), array(138,233,232));
	$px5->SetData($data);
	$px5->SetLegend($legend);
	$px5->SetType(array('bar'));
	$px5->setHeight('250');
	$px5->SetWidth('500');
	$supplier_outstanding_amount_chart = $px5->draw($file, $fileurl);
}



// Display type of graph (customer or supplier)
$mode = $_GET['mode'];

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
			$graphiqueB = $px2->show($customer_oustanding_number_chart);
			$graphiqueB1 = $px3->show($customer_outstanding_amount_chart);

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
		$graphiqueB2 = $px4->show($supplier_outsanding_number_chart);
		$graphiqueB3 = $px5->show($supplier_outstanding_amount_chart);
}

// For second popup info
$secondPop_info1 = $titleItem2;
$secondPop_info2 = $info3;
$secondPop_info3 = $info4;

$secondPop_data1 = " Factures clients impayées <strong>(".price($total_customerOustandingYear)." €)</strong> - factures fournisseurs impayées <strong>(".price($total_supplierOutstandingYear)." €)</strong>";
$secondPop_data2 = " Somme de toutes les factures clients impayées sur l'exercice en cours (Hors brouillon) : <strong>".price($total_customerOustandingYear)."\n€</strong>";
$secondPop_data3 = "Somme de toutes les factures fournisseurs impayées sur l'exercice en cours (hors brouillon) : <strong>".price($total_supplierOutstandingYear)."\n€</strong>";


/**
 *  MARGIN BOXE : total marge par facture (prix de vente - prix de revient total ( où PR = prix de revient * qty))
 */
$titleItem3 = "Marge brute N";

// For calcul gross Margin
$total_standard_invoice = $object->turnover($startFiscalyear, $endYear); // paye + imp
$total_avoir_invoice = $object->avoir($startFiscalyear, $endYear, $paye = ''); // paye + imp
$total_avoir_invoice = abs($total_avoir_invoice);

$total = $total_standard_invoice + $total_avoir_invoice; // total
$total_invoices = $total;

$invoicesArr = $object->fetchInvoice($startFiscalyear, $endYear);

foreach($invoicesArr as $fac){

	$res = $invoice->fetch($fac->rowid);
	$linesArr = $invoice->lines;

	foreach($linesArr as $line){
		$costprice += $line->pa_ht * $line->qty;
		$totalHT += $line->total_ht;
	}
	 $margin = $totalHT - $costprice;
}

$dataItem3 = price($margin) . "\n€";

$info6 = "Marge brut prévisionnelle";
$forecastMargin = $conf->global->FORECAST_MARGIN; // manual entry
$dataInfo6 = $forecastMargin."\n€";

// Margin To produce on current mounth
$info5 = "Marge restant à produire";
$dataInfo5 = price($margin - $forecastMargin);

// Graph
$file = "marginChart";
$fileurl = DOL_DOCUMENT_ROOT.'/custom/tab/img';
$data = [];
$invoice = new Facture($db);

for($i = $startMonthFiscalYear; $i <= 12; $i++){

	$lastDayMonth = cal_days_in_month(CAL_GREGORIAN, $i, $year);
	$lastDayMonthLastyear =  cal_days_in_month(CAL_GREGORIAN, $i, $lastyear);

	$lastyear = strtotime('Last Year');
	$lastyear = date($year-1);

	// Current Year
	$date_start = $year.'-'.$i.'-01';
	$date_end = $year.'-'.$i.'-'.$lastDayMonth;

	// Start and end of each month on last year
	$date_start_lastYear = $lastyear.'-'.$i.'-01';
	$date_end_lastYear = $lastyear.'-'.$i.'-'.$lastDayMonthLastyear;

	// foreach($invoicesArr as $fac){

	// 	$res = $invoice->fetch($fac->rowid);
	// 	$linesArr = $invoice->lines;

	// 	foreach($linesArr as $line){
	// 		$costprice += $line->pa_ht * $line->qty;
	// 		$totalHT += $line->total_ht;
	// 	}
	// 	 $margin = $totalHT - $costprice;
	// }

	$data[] = [
		html_entity_decode($monthsArr[$i]), // month
		$margin,

	];

}

$px6 = new DolGraph();
$mesg = $px6->isGraphKo();
$legend = ['Année N-1', 'Année N'];

if (!$mesg){
	$px6->SetTitle("Evolution du montant de la marge brute");
	$px6->datacolor = array(array(255,206,126), array(138,233,232));
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

$thirdPop_data1 = "Somme totale de la marge des factures client validées <strong>(".price($margin)."\n€)</strong> sur l'exercice fiscal en cours";
$thirdPop_data2 = "La marge brute prévisionnelle <strong>(".price($forecastMargin)."\n€)</strong> - le total de la marge des factures <strong>(".price($margin)."\n€)</strong> ";
$thirdPop_data3 = "Définit dans la configuration du module : <strong>(".price($forecastMargin)."\n€)</strong>";


/**
 * ---- TREASURY BOX -------
 */

 // Current balance on n year
$solde = $object->fetchSoldeOnYear($startFiscalyear, $endYear, $currentAccount);
$total_solde = array_sum($solde);

// details datas for popupinfo (variables charges)
$date_now = date('Y-m-d', dol_now());


$titleItem4 = "Trésorerie prévisionnelle";
$info7 = "Charges mensuelles";
$info8 = "Recurrent mensuel";

// Fecth current bank account
$currentAccount = $object->getIdBankAccount();
$currentAccount = intval($currentAccount);

/**
 * datas calcul tresury
*/

// Current balance on n year
$solde = $object->fetchSoldeOnYear($startFiscalyear, $endYear, $currentAccount);
$total_solde = array_sum($solde);

/**
 *  Money flow out :
 */

// Static Expenses details (on prev month)
$arr_salarys = $object->fetchSalarys($firstDayLastMonth, $lastDayLastMonth, $currentAccount);
$socialesTaxes_charges = $object->fetchSocialAndTaxesCharges($firstDayLastMonth, $lastDayLastMonth, $currentAccount);
$emprunts = $object->fetchEmprunts($firstDayLastMonth, $lastDayLastMonth, $currentAccount);
$variousPaiements = $object->fetchVariousPaiements($firstDayLastMonth, $lastDayLastMonth, $currentAccount);

$staticExpenses = ($arr_salarys + $socialesTaxes_charges + $emprunts + $variousPaiements); // static expenses total

// Others datas
// $total_vat_by_month = $object->fetchTVA($firstDayLastMonth, $lastDayLastMonth); // todo
$total_expense = $object->fetchExpenses($firstDayLastMonth, $lastDayLastMonth); // expenses

// Total Money flow out
$totalMoneyOut = ($staticExpenses + $total_vat_by_month + $total_expense + $total_supplierOutstandingYear);

/**
 *  Money flow in
 */

// facture client impayes (todo : aire requete sql antoine)
$array_modelInvoice = $object->fetchModelInvoices($firstDayLastMonth, $lastDayLastMonth, $firstDayCurrentMonth, $lastDayCurrentMonth);
$total_modelInvoice = array_sum($array_modelInvoice);

// Monthly Charge
$dataInfo8 = price($total_modelInvoice) . "\n€"; // TODO A MODIFIER

// avoir fournisseur impayees
$creditnote_unpaid_supplier_year = $object->allSupplierUnpaidDeposit($startFiscalYear, $endYear);
$creditnote_unpaid_supplier_year = abs($creditnote_unpaid_supplier_year); //convert negative amount to positive for calculation

$moneyFlowIn = $total_modelInvoice + $creditnote_unpaid_supplier_year;

// Variable expenses
$array_suppliers_invoice_paid = $object->outstandingSupplier($firstDayLastMonth, $lastDayLastMonth, 1);
$total_suppliers_invoice_paid = array_sum($array_suppliers_invoice_paid);

$array_suppliers_invoice_unpaid = $object->outstandingSupplier($firstDayLastMonth, $lastDayLastMonth, 0);
$total_suppliers_invoice_unpaid = array_sum($array_suppliers_invoice_unpaid);

$total_suppliers_invoice_paid_and_unpaid = $total_suppliers_invoice_unpaid + $total_suppliers_invoice_paid;
$variablesExpenses = $total_suppliers_invoice_paid_and_unpaid + $total_vat_by_month ;

// Monthly charge
$totalMonthlyExpenses = floatval( ($variablesExpenses + $staticExpenses));
$dataInfo7 = price($totalMonthlyExpenses) . "\n€";
$tresury = ($total_solde - $totalMoneyOut + $moneyFlowIn); // calcul for net tresury
$dataItem4 = price($tresury) . "\n€"; // Display tresury

// Graph tresury
$data = [];
$file = "tresuryChart";
$fileurl = DOL_DOCUMENT_ROOT.'/custom/tab/img';

	for($i = $startMonthFiscalYear; $i <= 12; $i++){

		$lastDayMonth = cal_days_in_month(CAL_GREGORIAN, $i, $year);

		// Current Year
		$date_start = $year.'-'.$i.'-01'; // first day of month
		$date_end = $year.'-'.$i.'-'.$lastDayMonth; // last day of montH

		$solde = $object->fetchSoldeOnYear($date_start, $date_end, $idaccount);
		$total_solde += array_sum($solde);

		if(date('n', $date_start) == $i) {
			$tresury += ($total_solde - $totalMoneyOut + $moneyFlowIn);
		}

		$data[] = [
			html_entity_decode($monthsArr[$i]), // month
			$tresury,
		];
	}


$px7 = new DolGraph();
$mesg = $px7->isGraphKo();
$legend = ['Année N'];

if (!$mesg){
	$px7->SetTitle("Evolution de la trésorerie nette");
	$px7->datacolor = array(array(138,233,232));
	$px7->SetLegend($legend);
	$px7->SetData($data);
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


$fourPop_data1 = '<i>Solde du compte en banque  ('.price($total_solde).'€) - sortie d\'argent ('.$totalMoneyOut.'€) + entrée d\'argent ('.$moneyFlowIn.'€)</i>
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

				<br> <strong> <li>Détail charges fixes </strong> : Salaires (".price($arr_salarys)."\n€) </strong> +  Paiements divers (".price($variousPaiements)."\n€) </strong> +  emprunts (".price($emprunts)."\n€) </strong> + charges sociales et fiscales (".price($socialesTaxes_charges)."\n€) </strong> </li>
				<i style='color:blue;'>Les charges fixes sont calculées sur le mois précédent </i></br>
				<br> <strong> <li> Détail charges variables </strong> :  Factures fournisseurs impayées + payées sur l'exercice courant (".price($total_suppliers_invoice_paid_and_unpaid)."\n€) </strong> + TVA du mois courant(".intval($total_tva).") </strong> </li>
				</ul>";

$fourPop_data3 = "Montant total (TTC) des modèles de factures client ".price($total_modelInvoice)."\n€";

// include DOL_DOCUMENT_ROOT.'/compta/tva/initdatesforvat.inc.php';
// $form = new Form($db);
// $company_static = new Societe($db);
// $tva = new Tva($db);
// $period = $form->selectDate($firstDayCurrentMonth, 'date_start', 0, 0, 0, '', 1, 0).' - '.$form->selectDate($lastDayCurrentMonth, 'date_end', 0, 0, 0, '', 1, 0);

// 	$tmp = dol_getdate($firstDayCurrentMonth);
// 	var_dump($tmp);
// 	$y = $tmp['year'];
// 	$m = $tmp['mon'];
// 	$tmp = dol_getdate($lastDayCurrentMonth);
// 	$yend = $tmp['year'];
// 	$mend = $tmp['mon'];
// 	// var_dump($yend);
// 	$total = 0;
// 	$subtotalcoll = 0;
// 	$subtotalpaye = 0;
// 	$subtotal = 0;
// 	$i = 0;
// 	$mcursor = 0;

// 	while ((($y < $yend) || ($y == $yend && $m <= $mend)) && $mcursor < 1000) {    // $mcursor is to avoid too large loop
// 		//$m = $conf->global->SOCIETE_FISCAL_MONTH_START + ($mcursor % 12);
// 		if ($m == 13) {
// 			$y++;
// 		}
// 		if ($m > 12) {
// 			$m -= 12;
// 		}
// 		$mcursor++;

// 		$x_coll = tax_by_rate('vat', $db, $y, 0, 0, 0, $modetax, 'sell', $m);
// 		$x_paye = tax_by_rate('vat', $db, $y, 0, 0, 0, $modetax, 'buy', $m);

// 		$x_both = array();
// 		//now, from these two arrays, get another array with one rate per line
// 		foreach (array_keys($x_coll) as $my_coll_rate) {
// 			$x_both[$my_coll_rate]['coll']['totalht'] = $x_coll[$my_coll_rate]['totalht'];
// 			$x_both[$my_coll_rate]['coll']['vat'] = $x_coll[$my_coll_rate]['vat'];
// 			$x_both[$my_coll_rate]['paye']['totalht'] = 0;
// 			$x_both[$my_coll_rate]['paye']['vat'] = 0;
// 			$x_both[$my_coll_rate]['coll']['links'] = '';
// 			$x_both[$my_coll_rate]['coll']['detail'] = array();
// 			foreach ($x_coll[$my_coll_rate]['facid'] as $id => $dummy) {
// 				//$invoice_customer->id=$x_coll[$my_coll_rate]['facid'][$id];
// 				//$invoice_customer->ref=$x_coll[$my_coll_rate]['facnum'][$id];
// 				//$invoice_customer->type=$x_coll[$my_coll_rate]['type'][$id];
// 				//$company_static->fetch($x_coll[$my_coll_rate]['company_id'][$id]);
// 				$x_both[$my_coll_rate]['coll']['detail'][] = array(
// 					'id' => $x_coll[$my_coll_rate]['facid'][$id],
// 					'descr' => $x_coll[$my_coll_rate]['descr'][$id],
// 					'pid' => $x_coll[$my_coll_rate]['pid'][$id],
// 					'pref' => $x_coll[$my_coll_rate]['pref'][$id],
// 					'ptype' => $x_coll[$my_coll_rate]['ptype'][$id],
// 					'payment_id' => $x_coll[$my_coll_rate]['payment_id'][$id],
// 					'payment_amount' => $x_coll[$my_coll_rate]['payment_amount'][$id],
// 					'ftotal_ttc' => $x_coll[$my_coll_rate]['ftotal_ttc'][$id],
// 					'dtotal_ttc' => $x_coll[$my_coll_rate]['dtotal_ttc'][$id],
// 					'dtype' => $x_coll[$my_coll_rate]['dtype'][$id],
// 					'datef' => $x_coll[$my_coll_rate]['datef'][$id],
// 					'datep' => $x_coll[$my_coll_rate]['datep'][$id],
// 					//'company_link'=>$company_static->getNomUrl(1,'',20),
// 					'ddate_start' => $x_coll[$my_coll_rate]['ddate_start'][$id],
// 					'ddate_end' => $x_coll[$my_coll_rate]['ddate_end'][$id],
// 					'totalht' => $x_coll[$my_coll_rate]['totalht_list'][$id],
// 					'vat' => $x_coll[$my_coll_rate]['vat_list'][$id],
// 					//'link'      =>$invoice_customer->getNomUrl(1,'',12)
// 				);
// 			}
// 		}

// 		// tva paid
// 		foreach (array_keys($x_paye) as $my_paye_rate) {
// 			$x_both[$my_paye_rate]['paye']['totalht'] = $x_paye[$my_paye_rate]['totalht'];
// 			$x_both[$my_paye_rate]['paye']['vat'] = $x_paye[$my_paye_rate]['vat'];
// 			if (!isset($x_both[$my_paye_rate]['coll']['totalht'])) {
// 				$x_both[$my_paye_rate]['coll']['totalht'] = 0;
// 				$x_both[$my_paye_rate]['coll']['vat'] = 0;
// 			}
// 			$x_both[$my_paye_rate]['paye']['links'] = '';
// 			$x_both[$my_paye_rate]['paye']['detail'] = array();

// 			foreach ($x_paye[$my_paye_rate]['facid'] as $id => $dummy) {
// 				// ExpenseReport
// 				if ($x_paye[$my_paye_rate]['ptype'][$id] == 'ExpenseReportPayment') {
// 					//$expensereport->id=$x_paye[$my_paye_rate]['facid'][$id];
// 					//$expensereport->ref=$x_paye[$my_paye_rate]['facnum'][$id];
// 					//$expensereport->type=$x_paye[$my_paye_rate]['type'][$id];

// 					$x_both[$my_paye_rate]['paye']['detail'][] = array(
// 						'id' => $x_paye[$my_paye_rate]['facid'][$id],
// 						'descr' => $x_paye[$my_paye_rate]['descr'][$id],
// 						'pid' => $x_paye[$my_paye_rate]['pid'][$id],
// 						'pref' => $x_paye[$my_paye_rate]['pref'][$id],
// 						'ptype' => $x_paye[$my_paye_rate]['ptype'][$id],
// 						'payment_id' => $x_paye[$my_paye_rate]['payment_id'][$id],
// 						'payment_amount' => $x_paye[$my_paye_rate]['payment_amount'][$id],
// 						'ftotal_ttc' => price2num($x_paye[$my_paye_rate]['ftotal_ttc'][$id]),
// 						'dtotal_ttc' => price2num($x_paye[$my_paye_rate]['dtotal_ttc'][$id]),
// 						'dtype' => $x_paye[$my_paye_rate]['dtype'][$id],
// 						'ddate_start' => $x_paye[$my_paye_rate]['ddate_start'][$id],
// 						'ddate_end' => $x_paye[$my_paye_rate]['ddate_end'][$id],
// 						'totalht' => price2num($x_paye[$my_paye_rate]['totalht_list'][$id]),
// 						'vat' => $x_paye[$my_paye_rate]['vat_list'][$id],
// 						//'link'				=>$expensereport->getNomUrl(1)
// 					);
// 				} else {
// 					//$invoice_supplier->id=$x_paye[$my_paye_rate]['facid'][$id];
// 					//$invoice_supplier->ref=$x_paye[$my_paye_rate]['facnum'][$id];
// 					//$invoice_supplier->type=$x_paye[$my_paye_rate]['type'][$id];
// 					//$company_static->fetch($x_paye[$my_paye_rate]['company_id'][$id]);
// 					$x_both[$my_paye_rate]['paye']['detail'][] = array(
// 						'id' => $x_paye[$my_paye_rate]['facid'][$id],
// 						'descr' => $x_paye[$my_paye_rate]['descr'][$id],
// 						'pid' => $x_paye[$my_paye_rate]['pid'][$id],
// 						'pref' => $x_paye[$my_paye_rate]['pref'][$id],
// 						'ptype' => $x_paye[$my_paye_rate]['ptype'][$id],
// 						'payment_id' => $x_paye[$my_paye_rate]['payment_id'][$id],
// 						'payment_amount' => $x_paye[$my_paye_rate]['payment_amount'][$id],
// 						'ftotal_ttc' => price2num($x_paye[$my_paye_rate]['ftotal_ttc'][$id]),
// 						'dtotal_ttc' => price2num($x_paye[$my_paye_rate]['dtotal_ttc'][$id]),
// 						'dtype' => $x_paye[$my_paye_rate]['dtype'][$id],
// 						'datef' => $x_paye[$my_paye_rate]['datef'][$id],
// 						'datep' => $x_paye[$my_paye_rate]['datep'][$id],
// 						//'company_link'=>$company_static->getNomUrl(1,'',20),
// 						'ddate_start' => $x_paye[$my_paye_rate]['ddate_start'][$id],
// 						'ddate_end' => $x_paye[$my_paye_rate]['ddate_end'][$id],
// 						'totalht' => price2num($x_paye[$my_paye_rate]['totalht_list'][$id]),
// 						'vat' => $x_paye[$my_paye_rate]['vat_list'][$id],
// 						//'link'      =>$invoice_supplier->getNomUrl(1,'',12)
// 					);
// 				}
// 			}
// 		}
// 		//now we have an array (x_both) indexed by rates for coll and paye
// 		$action = "tva";
// 		$object = array(&$x_coll, &$x_paye, &$x_both);
// 		$parameters["mode"] = $modetax;
// 		$parameters["year"] = $y;
// 		$parameters["month"] = $m;
// 		$parameters["type"] = 'vat';

// 		$x_coll_sum = 0;
// 		foreach (array_keys($x_coll) as $rate) {
// 			$subtot_coll_total_ht = 0;
// 			$subtot_coll_vat = 0;

// 			foreach ($x_both[$rate]['coll']['detail'] as $index => $fields) {
// 				// Payment
// 				$ratiopaymentinvoice = 1;
// 				if ($modetax != 1) {
// 					// Define type
// 					// We MUST use dtype (type in line). We can use something else, only if dtype is really unknown.
// 					$type = (isset($fields['dtype']) ? $fields['dtype'] : $fields['ptype']);
// 					// Try to enhance type detection using date_start and date_end for free lines where type
// 					// was not saved.
// 					if (!empty($fields['ddate_start'])) {
// 						$type = 1;
// 					}
// 					if (!empty($fields['ddate_end'])) {
// 						$type = 1;
// 					}

// 					if (($type == 0 && $conf->global->TAX_MODE_SELL_PRODUCT == 'invoice')
// 						|| ($type == 1 && $conf->global->TAX_MODE_SELL_SERVICE == 'invoice')) {
// 						//print $langs->trans("NA");
// 					} else {
// 						if (isset($fields['payment_amount']) && price2num($fields['ftotal_ttc'])) {
// 							$ratiopaymentinvoice = ($fields['payment_amount'] / $fields['ftotal_ttc']);
// 						}
// 					}
// 				}
// 				// var_dump('type='.$type.' '.$fields['totalht'].' '.$ratiopaymentinvoice);
// 				$temp_ht = $fields['totalht'] * $ratiopaymentinvoice;
// 				$temp_vat = $fields['vat'] * $ratiopaymentinvoice;
// 				$subtot_coll_total_ht += $temp_ht;
// 				$subtot_coll_vat += $temp_vat;
// 				$x_coll_sum += $temp_vat;
// 			}
// 		}

// 		$x_paye_sum = 0;
// 		foreach (array_keys($x_paye) as $rate) {
// 			$subtot_paye_total_ht = 0;
// 			$subtot_paye_vat = 0;

// 			foreach ($x_both[$rate]['paye']['detail'] as $index => $fields) {
// 				// Payment
// 				$ratiopaymentinvoice = 1;
// 				if ($modetax != 1) {
// 					// Define type
// 					// We MUST use dtype (type in line). We can use something else, only if dtype is really unknown.
// 					$type = (isset($fields['dtype']) ? $fields['dtype'] : $fields['ptype']);
// 					// Try to enhance type detection using date_start and date_end for free lines where type
// 					// was not saved.
// 					if (!empty($fields['ddate_start'])) {
// 						$type = 1;
// 					}
// 					if (!empty($fields['ddate_end'])) {
// 						$type = 1;
// 					}

// 					if (($type == 0 && $conf->global->TAX_MODE_SELL_PRODUCT == 'invoice')
// 						|| ($type == 1 && $conf->global->TAX_MODE_SELL_SERVICE == 'invoice')) {
// 						//print $langs->trans("NA");
// 					} else {
// 						if (isset($fields['payment_amount']) && price2num($fields['ftotal_ttc'])) {
// 							$ratiopaymentinvoice = ($fields['payment_amount'] / $fields['ftotal_ttc']);
// 						}
// 					}
// 				}
// 				//var_dump('type='.$type.' '.$fields['totalht'].' '.$ratiopaymentinvoice);
// 				$temp_ht = $fields['totalht'] * $ratiopaymentinvoice;
// 				$temp_vat = $fields['vat'] * $ratiopaymentinvoice;
// 				$subtot_paye_total_ht += $temp_ht;
// 				$subtot_paye_vat += $temp_vat;
// 				$x_paye_sum += $temp_vat;
// 			}
// 		}

// 		$subtotalcoll = $subtotalcoll + $x_coll_sum;
// 		$subtotalpaye = $subtotalpaye + $x_paye_sum;

// 		$diff = $x_coll_sum - $x_paye_sum;
// 		$total_vat_by_month = $diff;
// 	}

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
