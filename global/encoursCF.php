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

// First day and last day
$firstDayYear = date('Y-m-d', mktime(0, 0, 0, 1, 1, $year)); // current years
$lastDayYear = date('Y-m-t', mktime(0, 0, 1, 12, 1, $year));

// First day and last day of current month
$firstDayCurrentMonth = date('Y-m-d', mktime(0, 0, 0, $month, 1, $year));
$lastDayCurrentMonth = date('Y-m-t', mktime(0, 0, 0, $month, 1, $year));

$firstDayLastYear = date('Y-m-d', mktime(0, 0, 1, 1, 1, $year - 1)); // last year
$lastDayLastYear = date('Y-m-t', mktime(0, 0, 1, 12, 1, $year - 1));

// Load start month fiscal year for datas box and graph
$startFiscalYear = $conf->global->START_FISCAL_YEAR;
$startMonthFiscalYear = $object->startMonthForGraphLadder($startFiscalYear, 12);

$invoice = new Facture($db);
/**
 * CUSTOMER OUTSTANDING
 */

$titleItem1 = "Encours clients";
$outstandingBillOnYear = $object->outstandingBill($startFiscalyear, $endYear);
$total_outstandingBillOnYear = array_sum($outstandingBillOnYear);
$dataItem1 = price($total_outstandingBillOnYear) . "\n€";

// $dataItem1 .= '<span class="classfortooltip" title="Encours clients HT : '.price($total_CustomerOutstandingTTC) .'">
// 				<span class="fas fa-info-circle"></span>';

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

/**
 *  Variation rate between :
 *  - the outstanding of last month on current year
 * 	- the outstanding of current month on current year
 *  */

// Total outstanding customer on current month on year
$outstandingCustomerCurrentMonth = $object->outstandingBill($firstDayCurrentMonth, $lastDayCurrentMonth);
$total_outstandingCurrentMonth = array_sum($outstandingCustomerCurrentMonth);

$info2 = "Progression ";
$resultat = $object->progress($total_outstandingCurrentMonth, $total_outstandingLastMonth);
$dataInfo2 = intval($resultat) . "\n%";

// Condition d'affichage pour la progression
if ($total_outstandingCurrentMonth > $total_outstandingLastMonth) {
	$dataInfo2 = '<p style=color:red>' . $dataInfo2;
} else {
	$dataInfo2 = '<p style=color:green>' . $dataInfo2;
}

// Load info for otstanding customer popupinfo
$firstPop_info1 = $titleItem1;
$firstPop_info2 = $info1;
$firstPop_info3 = $info2;

$firstPop_data1 = "Total du montant des factures clients impayées <strong>(" . price($total_outstandingBillOnYear) . "\n€)</strong> sur l'exercice en cours (TTC)";
$firstPop_data2 = "Total du montant des factures clients impayées  <strong>(" . price($total_outstandingLastMonth) . "\n€)</strong> sur le mois précédent";
$firstPop_data3 = "Progression du montant des encours clients <strong> du mois dernier sur l'exercice fiscal en cours</strong> avec les encours <strong> du mois courant sur l'exercice fiscal en cours </strong>
				   </br> Calcul : ( (Valeur d'arrivée - Valeur de départ) / Valeur de départ) x 100 )
				   </br> Soit : <strong>(( " . $total_outstandingCurrentMonth . " - " . $total_outstandingLastMonth . ") / " . $total_outstandingLastMonth . ") x 100 </strong>";

/**
 * FIRST GRAPH - Outstanding customer
 */
$monthsArr = monthArray($langs, 1); // months
$file = "oustandingCustomerChartNumber";
$fileurl = DOL_DOCUMENT_ROOT . '/custom/tab/img';
$invoice = new Facture($db);
unset($yy);
$currentMonthLastYear = date('n', mktime(0, 0, 0, $month+1, 1, $year-1));
$currentMonthLastYear = (int)$currentMonthLastYear;

for($mm = $currentMonthLastYear; $mm < 13; $mm++){

	if(!$yy){
		$yy = $year-1;
	}

	if($mm == $currentMonthLastYear && $yy == $year){
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
	$px->SetTitle("Evolution des encours clients - TTC");
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

// View data intuitively (positive or negative development)
if ($dataInfo3 <= 0) {
	$dataInfo3 = '<p class="badge badge-success" style="color:green;">Aucun encours';
} else {
	$dataInfo3 = $dataInfo3;
}

/**
 *  Progression between :
 * - the outstanding suppliers of last month on current year
 * - the outstanding suppliers of current month on current year
*/

$info4 = "Progression";
$outstandingSupplierLastMonthCurrentMonth = $object->outstandingSupplier($firstDayCurrentMonth, $lastDayCurrentMonth, 0); // Encours C sur le mois dernier de l'exercice fiscal précédent
$total_outstandingSupplierCurrentMonth = array_sum($outstandingSupplierLastMonthCurrentMonth);

$resultat = $object->progress($total_outstandingSupplierCurrentMonth, $total_outstandingSupplierOnLastMonth);
$dataInfo4 = intval($resultat). "\n%";

// View data intuitively (positive or negative development)
if ($total_outstandingSupplierCurrentMonth > $total_outstandingSupplierOnLastMonth) {
	$dataInfo4 = '<p style=color:red>' . $dataInfo4;
} else {
	$dataInfo4 = '<p style=color:green>'. $dataInfo4;
}

// Load info for outstanding supplier popupinfo
$secondPop_data1 = $titleItem2;
$secondPop_info2 = $info3;
$secondPop_info3 = $info4;

$secondPop_data1 = "Total du montant des factures fournisseurs impayées <strong>(" . price($total_outstandingSupplierOnYear) . "\n€)</strong> sur l'exercice en cours (TTC)";
$secondPop_data2 = "Total du montant des factures fournisseurs impayées  <strong>(" . price($total_outstandingSupplierOnLastMonth) . "\n€)</strong> sur le mois précédent (de l'exercice fiscal en cours) - HT";
$secondPop_data3 = "Progression du montant des encours fournisseurs <strong> du mois dernier sur l'exercice fiscal en cours</strong> avec les encours <strong> du mois courant sur l'exercice fiscal en cours </strong>
				   </br> Calcul : ( (Valeur d'arrivée - Valeur de départ) / Valeur de départ) x 100 )
				   </br> Soit : <strong>(( " . price($total_outstandingSupplierCurrentMonth) . " - " . price($total_outstandingSupplierOnLastMonth) . ") / " . price($total_outstandingSupplierOnLastMonth) . ") x 100 </strong>";


/**
 * SECOND GRAPH - Outstanding supplier
 */
$file = "supplierOustandingChart";
$fileurl = DOL_DOCUMENT_ROOT . '/custom/tab/img';
$invoice_supplier = new FactureFournisseur($db);
unset($yy);
$currentMonthLastYear = date('n', mktime(0, 0, 0, $month+1, 1, $year-1));
$currentMonthLastYear = (int)$currentMonthLastYear;

for($mm = $currentMonthLastYear; $mm < 13; $mm++){

	if(!$yy){
		$yy = $year-1;
	}

	if($mm == $currentMonthLastYear && $yy == $year){
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
$dataItem3 = price($total_outstandingBillOnYear - $total_outstandingSupplierOnYear) . "\n€"; // soustraction des encours client et encours fournisseur sur l'exercice fiscal en cours

$info5 = "Encours total M-1";
$dataInfo5 = price($total_outstandingLastMonth - $total_outstandingSupplierOnLastMonth) . "\n€"; // encours client m-1 - encours fourn m-1

/**
 *  Progression between :
 * - the outstanding C/F of last month on current year
 * - the outstanding C/F of last month on current year
*/
$info6 = "Comparaison";
$resultat = ( ($total_outstandingBillOnYear - $total_outstandingSupplierOnYear) / $total_outstandingBillOnYear ) * 100;
$dataInfo6 = intval($resultat). "\n%";

// View data intuitively (positive or negative development)
if ($resultat <= 0 ) {
	$dataInfo6 = '<p style=color:red>' . $dataInfo6;
} else {
	$dataInfo6 = '<p style=color:green>'. $dataInfo6;
}

// Load info for outstanding C/F popupinfo
$thirdPop_info1 = $titleItem3;
$thirdPop_info2 = $info5;
$thirdPop_info3 = $info6;

$thirdPop_data1 = "Factures fournisseurs impayées <strong>(" . $dataItem3 . "\n€)</strong> l'exercice en cours (TTC) - Factures clients impayées sur l'exercice en cours (TTC)";
$thirdPop_data2 = "( Factures fournisseurs - factures clients impayées <strong>(" . $dataInfo5 . "\n€) du mois dernier </strong> sur l'exercice en cours (TTC) ) - ( Factures fournisseurs - factures clients impayées <strong> du mois courant </strong> (".$total_cf_currentmonth_year."€) sur l'exercice en cours (TTC) )";
$thirdPop_data3 = "Progression du montant d'encours fournisseurs/clients (mois courant) par rapport au mois dernier
					</br> Calcul : ((Valeur d'arrivee - valeur de depart) / valeur de depart) x 100 )
					</br> Soit : <strong>(( " . $total_outstandingBillOnYear . " - " . $total_outstandingSupplierOnYear . ") / " . $total_outstandingBillOnYear . ") x 100 </strong>";

/**
 * THIRD GRAPH - Outstanding customer and supplier
 */

$file = "CFOustandingChartCurrentYear";
$fileurl = DOL_DOCUMENT_ROOT . '/custom/tab/img';
$invoice_customer = new Facture($db);
$invoice_supplier = new FactureFournisseur($db);
unset($yy);
$currentMonthLastYear = date('n', mktime(0, 0, 0, $month+1, 1, $year-1));
$currentMonthLastYear = (int)$currentMonthLastYear;

for ($mm = $currentMonthLastYear; $mm <= 12; $mm++) {

	if(!$yy){
		$yy = $year-1;
	}

	if($mm == $currentMonthLastYear && $yy == $year){
		break;
	}

	strtotime('Last Year');
	$lastyear = date($year - 1);

	$lastDayMonth = cal_days_in_month(CAL_GREGORIAN, $mm, $yy);
	$lastDayMonthLastyear =  cal_days_in_month(CAL_GREGORIAN, $mm, $lastyear);

	/**
	 * CUSTOMER
	 */

	$date_start = $yy . '-' . $mm . '-01';
	$date_end = $yy . '-' . $mm . '-' . $lastDayMonth;

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

	$data[] = [
		html_entity_decode($monthsArr[$mm]), // months
		$amount_total_customer_outstanding_year,
		$amount_total_supplier_outstanding_year,
	];

	if($mm >= 12){
		$mm = 0;
		$yy++;
	}
}

$px3 = new DolGraph();
$mesg = $px3->isGraphKo();
$legend = ['Clients', 'Fournisseurs'];

if (!$mesg) {
	$px3->SetTitle("Evolution des encours C/F - TTC");
	$px3->datacolor = array(array(255, 165, 51), array(103, 187, 14));
	$px3->SetData($data);
	$px3->SetLegend($legend);
	$px3->SetType(array('bar'));
	$px3->setHeight('250');
	$px3->SetWidth('500');
	$amount_total_CFChart = $px3->draw($file, $fileurl);
}
$graphiqueC = $px3->show($amount_total_CFChart);


/**
 *  PAGINATION LISTES:
 */

// On détermine sur quelle page on se trouve
if(isset($_GET['page']) && !empty($_GET['page'])){
    $currentPage = (int) strip_tags($_GET['page']);
} else {
    $currentPage = 1;
}

/**
 * CUSTOMER OUSTANDING EXCEED
 */

$invoice = new Facture($db);
$date_now = date('Y-m-d', dol_now());

$arr_amount_exceed = $object->amountCustomerBillExceed($date_now);
$total_amount_exceed = array_sum($arr_amount_exceed);

// On détermine le nombre d'enregistrements par page
$byPage = 10;

// Calcul du premier enregistrement de la page
$first = ($currentPage * $byPage) - $byPage;

// Array of invoices who date due has passed - from oldest to newest
$invoiceExceedArray = $object->fetchCustomerBillExceed($date_now, $first, $byPage);
$nb_total_exceed = count($invoiceExceedArray); // nb invoices

// On calcule le nombre de pages total pour la pagination
$pagesCustomerInvoice = ceil($nb_total_exceed / $byPage);

$titleItem4 =  '<a href="'.DOL_URL_ROOT.'/compta/facture/list.php?sortfield=f.date_lim_reglement&sortorder=desc&begin=&socid=&contextpage=invoicelist&limit=500&search_datelimit_endday='.date('j', $datetime).'&search_datelimit_endmonth='.date('m', $datetime).'&search_datelimit_endyear='.$year.'&search_type=-1&search_status=1&search_options_paf=0">Encours clients dépassés (' . $nb_total_exceed . ') </a>';

if ($total_amount_exceed <= 0) {
	$dataItem4 = '<p class="badge badge-success" style="color:green;">Aucun encours clients dépassés';
} else {
	$dataItem4 = '<p class="badge badge-danger"><i class="fas fa-exclamation-triangle"></i>' . "\n" . price($total_amount_exceed) . "\n€" . '</p>';
}

// Load info for customer exceed popupinfo
$fourPop_info1 = $titleItem4;
$fourPop_data1 = "Somme des factures clients impayées <strong>(".price($total_amount_exceed)." €)</strong> dont la date d'échéance a été dépassée
				  (date limite de réglement est inférieure à la date d'aujourd'hui : <strong>".date('Y-m-d', dol_now())."</strong>";

foreach ($invoiceExceedArray as $res)
{
		$societe = new Societe($db);
		$societe->fetch($res->fk_soc);

		$invoice = new Facture($db);
		$invoice->fetch($res->rowid);

		$refInvoice = $invoice->ref;

		$listeA .= '<ul class="list-group">';
		$listeA .=	'<li class="list-group-item list-group-item-action">';
		$listeA .=	'<strong><a href="'.DOL_URL_ROOT.'/societe/card.php?socid='.$societe->id.'"></i>'.$societe->name.'</a></br></strong>';
		$listeA .=	'<a href="'.DOL_URL_ROOT.'/compta/facture/card.php?facid='.$invoice->id.'">';
		$listeA .= '<div class="pull-left" style="color:blue;">
						Réf. Facture :  '.$invoice->ref.'</br>
						Date : '.date('Y-m-d', $invoice->date_lim_reglement).'</a></div>';
		$listeA .= '<div class="pull-right">Montant :  '.price($invoice->total_ht).' € </span></div>';
		$listeA .= '</li></ul>';
}
/**
* SUPPLIER OUSTANDING EXCEED
*/

$supplier_invoice = new FactureFournisseur($db);
$arr_amount_supplier_exceed = $object->amountSupplierBillExceed($date_now);
$total_amount_supplier_exceed = array_sum($arr_amount_supplier_exceed);

// Array of suppliers invoices who date due has passed - from oldest to newest
$invoiceSupplierExceed = $object->fetchSupplierBillExceed($date_now, $first, $byPage);
$nb_supplier_exceed = count($invoiceSupplierExceed);

// On calcule le nombre de pages total pour la pagination
$pagesSupplierInvoices = ceil($nb_supplier_exceed / $byPage);

$titleItem5 =  '<a href="'.DOL_URL_ROOT.'/fourn/facture/list.php?sortfield=f.date_lim_reglement&sortorder=desc&begin=&socid=&contextpage=invoicelist&limit=500&search_datelimit_endday='.date('j', $datetime).'&search_datelimit_endmonth='.date('m', $datetime).'&search_datelimit_endyear='.$year.'&search_type=-1&search_status=1&search_options_paf=0">Encours fournisseurs dépassés (' . $nb_supplier_exceed . ') </a>';

if ($total_amount_supplier_exceed <= 0) {
	$dataItem5 = '<p class="badge badge-success" style="color:green;">Aucun encours fournisseurs dépassés</p>';
} else {
	$dataItem5 = '<p class="badge badge-danger"><i class="fas fa-exclamation-triangle"></i>' . "\n" . price($total_amount_supplier_exceed) . "\n€";
}
// Load info for supplier exceed popupinfo
$fivePop_info1 = $titleItem5;
$fivePop_data1 = "Somme des factures fournisseurs impayées <strong>(".price($total_amount_supplier_exceed)." €)</strong> dont la date d'échéance a été dépassée
				  (date limite de réglement est inférieure à la date d'aujourd'hui : <strong>".date('Y-m-d', dol_now())."</strong>";


	foreach ($invoiceSupplierExceed as $res2){
		$societe = new Societe($db);
		$societe->fetch($res2->fk_soc);

		$supplier_invoice->fetch($res2->rowid);

		$refInvoice = $supplier_invoice->ref;

		$listeB .= '<ul class="list-group">';
		$listeB .=	'<li class="list-group-item list-group-item-action">';
		$listeB .=	'<strong><a href="'.DOL_URL_ROOT.'/societe/card.php?socid='.$societe->id.'"></i>'.$societe->name.'</a></br></strong>';
		$listeB .=	'<a href="'.DOL_URL_ROOT.'/compta/facture/card.php?facid='.$supplier_invoice->id.'">';
		$listeB .= '<div class="pull-left" style="color:blue;">
						Réf. Facture :  '.$supplier_invoice->ref.'</br>
						Date : '.date('Y-m-d', $supplier_invoice->date_echeance).'</a></div>';
		$listeB .= '<div class="pull-right">Montant :  '.price($supplier_invoice->total_ht).' € </span></div>';
		$listeB .= '</li></ul>';
	}

/**
 * OUTSTANDING CUSTOMER EXCEED SINCE + 12 MONTHS
 *  */

// 1 er du mois courant - 12 mois (1an)
$exceedInvoiceOlderThanOneYear = date('Y-m-d', mktime(0, 0, 1, $month, 1, $year - 1));
$arr_amount_older_exceed = $object->amountCustomerBillExceed($exceedInvoiceOlderThanOneYear);
$total_amount_older_invoice_exceed = array_sum($arr_amount_older_exceed);

// Array of invoices who date due has passed - from oldest to newest
$invoiceOlderExceedArray = $object->fetchCustomerBillExceed($exceedInvoiceOlderThanOneYear, $first, $byPage);
$nb_total_older_invoice_exceed = count($invoiceOlderExceedArray);

// On calcule le nombre de pages total pour la pagination
$pagesOlderInvoice = ceil($nb_total_older_invoice_exceed / $byPage);

$titleItem6 =  '<a href="'.DOL_URL_ROOT.'/compta/facture/list.php?sortfield=f.date_lim_reglement&sortorder=desc&begin=&socid=&contextpage=invoicelist&limit=500&search_datelimit_endday='.date('j', $datetime).'&search_datelimit_endmonth='.date('m', $datetime).'&search_datelimit_endyear='.$year.'&search_type=-1&search_status=1&search_options_paf=0">Encours clients dépassés (' . $nb_total_older_invoice_exceed . ')
			    <strong> depuis + de 12 mois </strong></a>';

if ($total_amount_older_invoice_exceed <= 0) {
	$dataItem6 = '<p class="badge badge-success" style="color:green;">Aucun encours clients dépassés';
} else {
	$dataItem6 = '<p class="badge badge-danger"><i class="fas fa-exclamation-triangle"></i>' . "\n" . price($total_amount_older_invoice_exceed) . "\n€" . '</p>';
}

// Load info for customer older invoice exceed popupinfo
$sixPop_info1 = $titleItem6;
$sixPop_data1 = "Somme des factures clients impayées (".price($total_amount_older_invoice_exceed)." €)
				  dont la date d'échéance a été dépassé depuis <strong> + de 12 mois </strong>";

if(is_array($invoiceOlderExceedArray) && $invoiceOlderExceedArray != null){
	foreach ($invoiceOlderExceedArray as $res3)
	{
		$societe = new Societe($db);
		$societe->fetch($res3->fk_soc);

		$invoice = new Facture($db);
		$invoice->fetch($res3->rowid);

		$refInvoice = $invoice->ref;

		$listeC .= '<ul class="list-group">';
		$listeC .=	'<li class="list-group-item list-group-item-action">';
		$listeC .=	'<strong><a href="'.DOL_URL_ROOT.'/societe/card.php?socid='.$societe->id.'"></i>'.$societe->name.'</a></br></strong>';
		$listeC .=	'<a href="'.DOL_URL_ROOT.'/compta/facture/card.php?facid='.$invoice->id.'">';
		$listeC .= '<div class="pull-left" style="color:blue;">
						Réf. Facture :  '.$invoice->ref.'</br>
						Date : '.date('Y-m-d', $invoice->date_lim_reglement).'</a></div>';
		$listeC .= '<div class="pull-right">Montant :  '.price($invoice->total_ht).' € </span></div>';
		$listeC .= '</li></ul>';
	}
}

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
