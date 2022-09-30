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
require_once DOL_DOCUMENT_ROOT . '/core/class/dolgraph.class.php';
require_once DOL_DOCUMENT_ROOT . '/compta/bank/class/account.class.php';
require_once DOL_DOCUMENT_ROOT . '/commande/class/commande.class.php';

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

// M - 1 on current year and last year
$firstDayLastMonth = date('Y-m-d', mktime(0, 0, 0, $month - 1, 1, $year)); /// current year
$lastDayLastMonth = date('Y-m-t', mktime(0, 0, 0, $month - 1, 1, $year));
$firstDayLastMonthLastYear = date('Y-m-d', mktime(0, 0, 0, $month - 1, 1, $year - 1)); /// current year
$lastDayLastMonthLastYear = date('Y-m-t', mktime(0, 0, 0, $month - 1, 1, $year - 1));

// First day and last day
$firstDayYear = date('Y-m-d', mktime(0, 0, 0, 1, 1, $year)); // current years
$lastDayYear = date('Y-m-t', mktime(0, 0, 1, 12, 1, $year));

$firstDayLastYear = date('Y-m-d', mktime(0, 0, 1, 1, 1, $year - 1)); // last year
$lastDayLastYear = date('Y-m-t', mktime(0, 0, 1, 12, 1, $year - 1));

// Load start month fiscal year for datas box and graph
$startFiscalYear = $conf->global->START_FISCAL_YEAR;
$startMonthFiscalYear = $object->startMonthForGraphLadder($startFiscalYear, 12);


/**
 * CUSTOMER OUTSTANDING
 */

$titleItem1 = "Encours clients";
$outstandingBillOnYear = $object->outstandingBill($startFiscalyear, $endYear);
$total_outstandingBillOnYear = array_sum($outstandingBillOnYear);
$dataItem1 = price($total_outstandingBillOnYear) . "\n€";

// Encours C sur le mois dernier
$info1 = "Encours du mois dernier";
$outstandingLastMonth = $object->outstandingBill($firstDayLastMonth, $lastDayLastMonth);
$total_outstandingLastMonth = array_sum($outstandingLastMonth);
$dataInfo1 = price($total_outstandingLastMonth) . "\n€";

// View data intuitively (positive or negative development)
if ($dataInfo1 <= 0) {
	$dataInfo1 = '<p class="badge badge-success" style="color:green;">Aucun encours';
} else {
	$dataInfo1 = $dataInfo1;
}

// Progression between the outstanding of last month on current year with the outstanding of last month on prev year
$outstandingLastMonthLastYear = $object->outstandingBill($firstDayLastMonthLastYear, $lastDayLastMonthLastYear); // Encours C sur le mois dernier de l'exercice fiscal précédent
$total_outstandingLastMonthLastYear = array_sum($outstandingLastMonthLastYear);

$info2 = "Progression ";
$resultat = $object->progress($total_outstandingLastMonth, $total_outstandingLastMonthLastYear);
$dataInfo2 = intval($resultat) . "\n%";

// Condition d'affichage pour la progression
if ($dataInfo2 > 0) {
	$dataInfo2 = '<p style=color:red>' . $dataInfo2;
} else {
	$dataInfo2 = '<p style=color:green>' . $dataInfo2;
}

// Load info for otstanding customer popupinfo
$firstPop_info1 = $titleItem1;
$firstPop_info2 = $info1;
$firstPop_info3 = $info2;

$firstPop_data1 = "Total du montant des factures clients impayées <strong>(" . price($total_outstandingBillOnYear) . "\n€)</strong> sur l'exercice en cours (HT)";
$firstPop_data2 = "Total du montant des factures clients impayées  <strong>(" . price($total_OutCustomerLastMonth) . "\n€)</strong> sur le mois précédent";
$firstPop_data3 = "Progression du montant des encours clients (du mois dernier sur l'exercice fiscal en cours) avec les encours du mois dernier sur l'exercice fiscal précédent
				   </br> Calcul : ( (VA - VD) / VA) x 100 )
				   </br> Soit : <strong>(( " . $total_outstandingLastMonth . " - " . $total_outstandingLastMonthLastYear . ") / " . $total_outstandingLastMonth . ") x 100 </strong>
				   </br> Où VA = valeur d'arrivée et VD = Valeur de départ";

// Drawing the first graph for nb of customer invoices by month
$monthsArr = monthArray($langs, 1); // months
$file = "oustandingCustomerChartNumber";
$fileurl = DOL_DOCUMENT_ROOT . '/custom/tab/img';
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

	// Current Year
	$date_start = $yy.'-'.$mm.'-01';
	$date_end = $yy.'-'.$mm.'-'.$lastDayMonth;

	$array_customer_outstanding_year = $object->outstandingBill($date_start, $date_end);
	$nb_total_customer_outstanding_year = count($array_customer_outstanding_year); // number
	$amount_total_customer_outstanding_year = array_sum($array_customer_outstanding_year); // amount

	if(date('n', $date_start) == $mm){
		$nb_total_customer_outstanding_year += $invoice->total_ttc;
		$amount_total_customer_outstanding_year += $invoice->total_ttc;
	}

	$data1[] = [
		$ladder = html_entity_decode($monthsArr[$mm]), // months
		$nb_total_customer_outstanding_year, // nb
		$amount_total_customer_outstanding_year, // amount
	];

	if($mm >= 12){
		$mm = 0;
		$yy++;
	}
}

$px = new DolGraph();
$mesg = $px->isGraphKo();
$legend = ['Nombre', 'Montant'];

if (!$mesg) {
	$px->SetTitle("Evolution des encours clients par mois - TTC");
	$px->datacolor = array(array(255, 99, 71), array(128, 187, 240));
	$px->SetData($data1);
	$px->SetLegend($legend);
	$px->SetType(array('bar'));
	$px->setHeight('250');
	$px->SetWidth('500');
	$customer_oustandingChart = $px->draw($file, $fileurl);
}

$graphiqueA = $px->show($customer_oustandingChart);


/**
 *  SUPPLIERS OUTSTANDING
 */

$titleItem2 = "Encours fournisseurs";
$outstandingSupplierOnYear = $object->outstandingSupplier($startFiscalyear, $endYear, 0);
$total_outstandingSupplierOnYear = array_sum($outstandingSupplierOnYear); // fetch total in current year
$dataItem2 = price($total_outstandingSupplierOnYear) . "\n€";

$info3 = "Encours fournisseur M-1";
$outstandingSupplierOnLastMonth = $object->outstandingSupplier($firstDayLastMonth, $lastDayLastMonth, 0);
$total_outstandingSupplierOnLastMonth = array_sum($outstandingSupplierOnLastMonth); // fetch total in last month
$dataInfo3 = price($total_outstandingSupplierOnLastMonth) . "\n€";

/**
 *  Progression between :
 * - the outstanding suppliers of last month on current year
 * - the outstanding suppliers of last month on prev year
*/

$info4 = "Progression";
$outstandingSupplierLastMonthLastYear = $object->outstandingSupplier($firstDayLastMonthLastYear, $lastDayLastMonthLastYear, 0); // Encours C sur le mois dernier de l'exercice fiscal précédent
$total_outstandingSupplierLastMonthLastYear = array_sum($outstandingSupplierLastMonthLastYear);

$resultat = $object->progress($total_outstandingSupplierOnLastMonth, $total_outstandingSupplierLastMonthLastYear);
$dataInfo4 = $resultat . "\n%";

// View data intuitively (positive or negative development)
if ($dataInfo3 <= 0) {
	$dataInfo3 = '<p class="badge badge-success" style="color:green;">Aucun encours';
} else {
	$dataInfo3 = $dataInfo3;
}

// Load info for outstanding supplier popupinfo
$secondPop_info1 = $titleItem2;
$secondPop_info2 = $info3;
$secondPop_info3 = $info4;

$secondPop_data1 = "Total du montant des factures fournisseurs impayées <strong>(" . price($total_outstandingSupplierOnYear) . "\n€)</strong> sur l'exercice en cours (HT)";
$secondPop_data2 = "Total du montant des factures fournisseurs impayées  <strong>(" . price($total_outstandingSupplierOnLastMonth) . "\n€)</strong> sur le mois précédent (de l'exercice fiscal en cours)";
$secondPop_data3 = "Progression du nombre d'encours fournisseurs par rapport au mois dernier
					</br> Calcul : ((VA - VD) / VA) x 100 )
					</br> Soit : <strong>(( " . $total_outstandingSupplierOnLastMonth . " - " . $total_outstandingSupplierLastMonthLastYear . ") / " . $total_outstandingSupplierOnLastMonth . ") x 100 </strong>
					</br> Où VA = valeur d'arrivée et VD = Valeur de départ";


// Supplier chart
$file = "supplierOustandingChart";
$fileurl = DOL_DOCUMENT_ROOT . '/custom/tab/img';
$invoice_supplier = new FactureFournisseur($db);
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

	$data2[] = [
		$ladder = html_entity_decode($monthsArr[$mm]), // months
		$nb_total_supplier_outstanding_year, // nb
		$amount_total_supplier_outstanding_year, // amount
	];

	if($mm >= 12){
		$mm = 0;
		$yy++;
	}
}

$px2 = new DolGraph();
$mesg = $px2->isGraphKo();
$legend = ['Nombre', 'Montant'];

if (!$mesg) {
	$px2->SetTitle("Evolution des encours fournisseurs - TTC");
	$px2->datacolor = array(array(255, 99, 71), array(128, 187, 240));
	$px2->SetData($data2);
	$px2->SetLegend($legend);
	$px2->SetType(array('bar'));
	$px2->setHeight('250');
	$px2->SetWidth('500');
	$total_supplier_outstandingChart = $px2->draw($file, $fileurl);
}
$graphiqueB = $px2->show($total_supplier_outstandingChart);


/**
 * CUSTOMER AND SUPPLIERS OUTSTANDING
 */
$titleItem3 = "Encours C/F ";
$dataItem3 = price($total_outstandingBillOnYear - $total_outstandingSupplierOnYear) . "\n€"; // soustraction des encours client et encours fournisseur

$info5 = "Encours total M-1";
$dataInfo5 = intval($total_outstandingLastMonth - $total_outstandingSupplierOnLastMonth) . "\n€"; // encours client m-1 - encours fourn m-1

$info6 = "Progression";
$outCFCurrentMonth = ($total_OutCustomerCurrentMonth - $total_OutSupplierCurrentMonth);

// $resultat = $object->progress($total_OutCustomerCurrentMonth, $dataInfo5);
$dataInfo6 = $resultat . "\n%";

// Load info for outstanding C/F popupinfo
$thirdPop_info1 = $titleItem3;
$thirdPop_info2 = $info5;
$thirdPop_info3 = $info6;

// C/F chart on current year
$file = "CFOustandingChartCurrentYear";
$fileurl = DOL_DOCUMENT_ROOT . '/custom/tab/img';
$invoice_customer = new Facture($db);
$invoice_supplier = new FactureFournisseur($db);

for ($i = $startMonthFiscalYear; $i <= 12; $i++) {

	strtotime('Last Year');
	$lastyear = date($year - 1);

	$lastDayMonth = cal_days_in_month(CAL_GREGORIAN, $i, $year);
	$lastDayMonthLastyear =  cal_days_in_month(CAL_GREGORIAN, $i, $lastyear);

	/**
	 * CUSTOMER
	 */

	// Current Year
	$date_start = $year . '-' . $i . '-01';
	$date_end = $year . '-' . $i . '-' . $lastDayMonth;

	$array_customer_outstanding_year = $object->outstandingBill($date_start, $date_end);
	$amount_total_customer_outstanding_year = array_sum($array_customer_outstanding_year);

	/**
	 * SUPPLIERS
	 */
	$total_supplier_outstanding_year = $object->outstandingSupplier($date_start, $date_end, 0);
	$amount_total_supplier_outstanding_year = array_sum($total_supplier_outstanding_year);

	if (date('n', $date_start) == $i) {
		$amount_total_customer_outstanding_year += $invoice_customer->total_ttc;
		$amount_total_supplier_outstanding_year += $invoice_supplier->total_ttc;
	}

	$ladder = html_entity_decode($monthsArr[$i]);

	$data[] = [
		$ladder, // months
		$amount_total_customer_outstanding_year,
		$amount_total_supplier_outstanding_year,
	];
}

$px3 = new DolGraph();
$mesg = $px3->isGraphKo();
$legend = ['Clients', 'Fournisseurs'];

if (!$mesg) {
	$px3->SetTitle("Evolution sur l'exercice en cours (TTC)");
	$px3->datacolor = array(array(255, 165, 51), array(103, 187, 14));
	$px3->SetData($data);
	$px3->SetLegend($legend);
	$px3->SetType(array('bar'));
	$px3->setHeight('250');
	$px3->SetWidth('500');
	$amount_total_CFChart = $px3->draw($file, $fileurl);
}
$graphiqueC = $px3->show($amount_total_CFChart);


// Customer outstandings exceeded
$invoice = new Facture($db);

// si date limite de regelemnt est inferieur au jour d'aujrdh - 1mois car date limite de reglement accorde delai d'1 mois
$date = date('Y-m-d');

$array_customer_exceed = $object->fetchCustomerBillExceed();
// var_dump($array_customer_exceed);

$total_amount_exceed = array_sum($array_customer_exceed);
$nb_total_exceed = count($array_customer_exceed);

$titleItem4 = "Encours clients dépassés (" . $nb_total_exceed . ") ";

if ($total_amount_exceed <= 0) {
	$dataItem4 = '<p class="badge badge-success" style="color:green;">Aucun encours clients dépassés';
} else {
	$dataItem4 = '<p class="badge badge-danger"><i class="fas fa-exclamation-triangle"></i>' . "\n" . price($total_amount_exceed) . "\n€" . '</p>';
}

// Load info for customer exceed popupinfo
$fourPop_info1 = $titleItem4;
$fourPop_data1 = "Somme des factures clients impayées (TTC) dont la date d'échéance a été dépassée";


// Supplier outstandings exceeded
$date = dol_now();

$array_supplier_exceed = $object->fetchSupplierrBillExceed($date, $startFiscalLastyear, $endYear, 2);
$array_supplier_credit_exceed = $object->fetchSupplierrBillExceed($date, $startFiscalLastyear, $endYear, 0);

$total_amount_supplier_exceed = array_sum($array_supplier_exceed + $array_supplier_credit_exceed);
$nb_total_supplier_exceed = count($array_supplier_exceed + $array_supplier_credit_exceed);


$titleItem5 = "Encours fournisseur dépassé (" . $nb_total_supplier_exceed . ")";

if ($total_amount_supplier_exceed <= 0) {
	$dataItem5 = '<p class="badge badge-success" style="color:green;">Aucun encours fournisseurs dépassés</p>';
} else {
	$dataItem5 = '<p class="badge badge-danger"><i class="fas fa-exclamation-triangle"></i>' . "\n" . price($total_amount_supplier_exceed) . "\n€";
}
// Load info for supplier exceed popupinfo
$fivePop_info1 = $titleItem5;
$fivePop_data1 = "Somme des factures fournisseurs impayées (TTC) dont la date d'échéance a été dépassée";

// Supplier outstandings exceeded
$date = dol_now();

$array_supplier_exceed = $object->fetchSupplierrBillExceed($date, $startFiscalLastyear, $endYear, 2);
$array_supplier_credit_exceed = $object->fetchSupplierrBillExceed($date, $startFiscalLastyear, $endYear, 0);

$total_amount_supplier_exceed = array_sum($array_supplier_exceed + $array_supplier_credit_exceed);
$nb_total_supplier_exceed = count($array_supplier_exceed + $array_supplier_credit_exceed);


$titleItem6 = "Encours clients dépassé (" . $nb_total_supplier_exceed . ") depuis </br> <strong>+ de 12 mois</strong>";

if ($total_amount_supplier_exceed <= 0) {
	$dataItem6 = '<p class="badge badge-success" style="color:green;">Aucun encours fournisseurs dépassés</p>';
} else {
	$dataItem6 = '<p class="badge badge-danger"><i class="fas fa-exclamation-triangle"></i>' . "\n" . price($total_amount_supplier_exceed) . "\n€";
}
// Load info for supplier exceed popupinfo
$fivePop_info1 = $titleItem6;
$fivePop_data1 = "Somme des factures fournisseurs impayées (TTC) dont la date d'échéance a été dépassée";


/*
 * View
 */

$form = new Form($db);
$formfile = new FormFile($db);
$object = new General($db);
$ac = new Account($db);

// Outstandings customer and suppliers
llxHeader('', $langs->trans("Encours Client/Fournisseur"));

print load_fiche_titre($langs->trans("Encours Client/Fournisseur"));

// Template for nav
$object->load_navbar();



// template for boxes
include DOL_DOCUMENT_ROOT . '/custom/tab/template/template_boxesGraph3.php';



// End of page
llxFooter();
$db->close();
