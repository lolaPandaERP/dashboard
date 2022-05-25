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

global $db, $conf;

$WIDTH = DolGraph::getDefaultGraphSizeForStats('width');
$HEIGHT = DolGraph::getDefaultGraphSizeForStats('height');

// fetch current bank account
$object = new General($db);
$ret = $object->getIdBankAccount();

$datetime = dol_now();
$year = dol_print_date($datetime, "%Y");
$month = dol_print_date($datetime, "%m");
$day = dol_print_date($datetime, "%d");


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

// Load translation files required by the page
$langs->loadLangs(array("tab@tab"));
$action = GETPOST('action', 'aZ09');
$socid = GETPOST('socid', 'int');
$action = GETPOST('action', 'aZ09');
$userid = GETPOST('userid', 'int');


/**
 * TURNOVER
 */

$titleItem1 = "Chiffre d'affaires";
$turnover = $object->fetchTurnover($firstDayYear, $lastDayYear);
$dataItem1 = price($turnover)."\n€";

$info1 = "Chiffre d'affaire n-1";
$turnoverLastYear = $object->fetchTurnover($firstDayLastYear, $lastDayLastYear);
$dataInfo1 = price($turnoverLastYear)."\n€";

$info2 = "Progression ";

$result = $object->progress($turnover, $turnoverLastYear);
$dataInfo2 = intval($result)  . "\n%";

if($dataInfo2 < 0){
	$dataInfo2 = '<p style=color:red>'.$dataInfo2.'</p>';
} else {
	$dataInfo2 = '<p style=color:green>'.$dataInfo2.'</p>';
}

/**
 * CHARTS
 */
$monthsArr = monthArray($langs, 1); // months

if($turnover != null){

	$file = "evolutionCAchart";
	$fileurl = DOL_DOCUMENT_ROOT.'/custom/tab/img';

	for($i = 1; $i <= 12; $i++){

		$data[] = [
			html_entity_decode($monthsArr[$i]),
			$turnoverLastYear,
			$turnover
		];
	}

	// Traçage du graph
	$px1 = new DolGraph();
	$mesg = $px1->isGraphKo();
	$legend = ["2022", "2021"];

	if (!$mesg){
		$px1->SetTitle("Evolution du Chiffres d'affaires entre ". ($year - 1). " et $year");
		$px1->datacolor = array(array(240,128,128), array(128, 187, 240));
		$px1->SetMaxValue($px1->GetCeilMaxValue());
		$px1->SetData($data);
		$px1->SetLegend($legend);
		$px1->SetType(array('lines')); // Array with type for each serie. Example: array('type1', 'type2', ...) where type can be: 'pie', 'piesemicircle', 'polar', 'lines', 'linesnopoint', 'bars', 'horizontalbars'...
		$px1->setHeight('200');
		$px1->SetWidth('300');
		$turnoverChart = $px1->draw($file, $fileurl);
	}
	$graphiqueA = $px1->show($turnoverChart);
}
?>


<?php

/**
 * OUTSTANDING CUSTOMER AND SUPPLIER
 */

$titleItem2 = "Encours C/F";
$customerOutstandingYear = $object->outstandingBillOnYear($firstDayYear, $lastDayYear);
$supplierOutstandingYear = $object->outstandingSupplierOnYear($firstDayYear, $lastDayYear);

$totalOutstangdingCF = $customerOutstandingYear - $supplierOutstandingYear;

$dataItem2 = price($totalOutstangdingCF)  ."\n€";

$info3 = "Encours C / mois ";
$customerOutstandingMonth = $object->outstandingCustomerOnCurrentMonth($firstDayCurrentMonth, $lastDayCurrentMonth);
$dataInfo3 = price($customerOutstandingMonth) . "\n€";

$info4 = "Encours F / mois";
$supplierOutstandingMonth = $object->outstandingSupplierOnCurrentMonth($firstDayCurrentMonth, $lastDayCurrentMonth);
$dataInfo4 = price($supplierOutstandingMonth) . "\n€";


/**
 * CHARTS FOR OUTSTANDING CUSTOMER
 */
	// Drawing the first graph for number of invoices by month
	$stats = new FactureStats($db, $socid, $mode = 'customer', ($userid > 0 ? $userid : 0), ($typent_id > 0 ? $typent_id : 0), ($categ_id > 0 ? $categ_id : 0));

	if($mode == 'customer'){
		$dataGraph = $stats->getNbByMonthWithPrevYear($endyear, $startyear);

		$filenamenb = $dir."/invoicesnbinyear-".$year.".png";
		if ($mode == 'customer') {
			$fileurlnb = DOL_URL_ROOT.'/viewimage.php?modulepart=billstats&amp;file=invoicesnbinyear-'.$year.'.png';
		}
		if ($mode == 'supplier') {
			$fileurlnb = DOL_URL_ROOT.'/viewimage.php?modulepart=billstatssupplier&amp;file=invoicesnbinyear-'.$year.'.png';
		}

		$px1 = new DolGraph();
		$mesg = $px1->isGraphKo();

		if (!$mesg) {
			$px1->SetData($dataGraph);
			$i = $startyear;
			$legend = array();
			while ($i <= $endyear) {
				$legend[] = $i;
				$i++;
			}
			$px1->SetLegend($legend);
			$px1->SetType(array('bar'));
			$px1->datacolor = array(array(208,255,126), array(255,206,126), array(138,233,232));
			$px1->SetMaxValue($px1->GetCeilMaxValue());
			$px1->SetWidth('500');
			$px1->SetHeight('250');
			$px1->SetTitle("Nombre de factures client par mois");
			$px1->SetShading(3);
			$nbInvoiceByMonth = $px1->draw($filenamenb, $fileurlnb);
		}

		$graphiqueB = $px1->show($nbInvoiceByMonth);

		// Drawing the second graph for amount invoices supplier by month
		$dataGraph = $stats->getAmountByMonthWithPrevYear($endyear, $startyear);

		$filenameamount = $dir."/invoicesamountinyear-".$year.".png";
		if ($mode == 'customer') {
			$fileurlamount = DOL_URL_ROOT.'/viewimage.php?modulepart=billstats&amp;file=invoicesamountinyear-'.$year.'.png';
		}
		if ($mode == 'supplier') {
			$fileurlamount = DOL_URL_ROOT.'/viewimage.php?modulepart=billstatssupplier&amp;file=invoicesamountinyear-'.$year.'.png';
		}

		$px2 = new DolGraph();
		$mesg = $px2->isGraphKo();
		if (!$mesg) {
			$px2->SetData($dataGraph);
			$i = $startyear;
			$legend = array();
			while ($i <= $endyear) {
				$legend[] = $i;
				$i++;
			}
			$px2->SetLegend($legend);
			$px2->datacolor = array(array(208,255,126), array(255,206,126), array(138,233,232));
			$px2->SetMaxValue($px2->GetCeilMaxValue());
			$px2->SetWidth('500');
			$px2->SetHeight('250');
			$px2->SetType(array('lines'));
			$px2->SetTitle("Montant factures clients par mois - HT");
			$amountInvoiceByMonth = $px2->draw($filenameamount, $fileurlamount);
		}

		$graphiqueB1 = $px2->show($amountInvoiceByMonth);
	}
	// SUPPLIER OUTSTANDING
	// elseif($mode == 'supplier'){

	// 	// Drawing the first graph for number of invoices by month
	// 	$stats = new FactureStats($db, $socid, $mode = 'supplier', ($userid > 0 ? $userid : 0), ($typent_id > 0 ? $typent_id : 0), ($categ_id > 0 ? $categ_id : 0));
	// 	$dataGraph = $stats->getNbByMonthWithPrevYear($endyear, $startyear);

	// 	$px3 = new DolGraph();
	// 	$mesg = $px3->isGraphKo();

	// 	if (!$mesg) {
	// 		$px3->SetData($dataGraph);
	// 		$i = $startyear;
	// 		$legend = array();
	// 		while ($i <= $endyear) {
	// 			$legend[] = $i;
	// 			$i++;
	// 		}
	// 		$px3->SetLegend($legend);
	// 		$px3->SetType(array('bar'));
	// 		$px3->datacolor = array(array(208,255,126), array(255,206,126), array(138,233,232));
	// 		$px3->SetMaxValue($px3->GetCeilMaxValue());
	// 		$px3->SetWidth('500');
	// 		$px3->SetHeight('250');
	// 		$px3->SetTitle("Nombre de factures clients par mois");
	// 		$px3->SetShading(3);
	// 		$nbInvoiceByMonth = $px3->draw($filenamenb, $fileurlnb);
	// 	}

	// 	$graphiqueB2 = $px3->show($nbInvoiceByMonth);

	// 	// Drawing the second graph for amount invoices by month
	// 	$dataGraph = $stats->getAmountByMonthWithPrevYear($endyear, $startyear);

	// 	$px4 = new DolGraph();
	// 	$mesg = $px4->isGraphKo();
	// 	if (!$mesg) {
	// 		$px4->SetData($dataGraph);
	// 		$i = $startyear;
	// 		$legend = array();
	// 		while ($i <= $endyear) {
	// 			$legend[] = $i;
	// 			$i++;
	// 		}
	// 		$px4->SetLegend($legend);
	// 		$px4->datacolor = array(array(208,255,126), array(255,206,126), array(138,233,232));
	// 		$px4->SetMaxValue($px4->GetCeilMaxValue());
	// 		$px4->SetWidth('500');
	// 		$px4->SetHeight('250');
	// 		$px4->SetType(array('lines'));
	// 		$px4->SetTitle("Montant des factures clients par mois - HT");
	// 		$amountInvoiceByMonth = $px4->draw($filenameamount, $fileurlamount);
	// 	}

	// 	$graphiqueB3 = $px4->show($amountInvoiceByMonth);
	// }




/**
 * OUTSTADNING SUPPLIER CHART - NB AND AMOUNT BY MONTH
 */






/**
 *  MARGIN BOXE
 */
$titleItem3 = "Marge brute N";
$grossMargin = $object->grossMarginOnYear($firstDayYear, $lastDayYear);
$dataItem3 = price($grossMargin) . "\n€";

// Margin To produce on current mounth
$info5 = "Marges restant à produire";

// request for gross margin on current mounth for calculate the margin to be produced
$grossMarginOnCurrentMonth = $object->grossMarginOnCurrentMonth($firstDayCurrentMonth, $lastDayCurrentMonth);
$dataInfo5 = price($grossMarginOnCurrentMonth) . "\n€";

// saisie manuelle
$marginToBeProduced = $conf->global->MARGIN_PRODUCED;

// calcul for remaining margin to produce
$dataInfo5 = price($grossMarginOnCurrentMonth - $marginToBeProduced)."\n€";

$info6 = "Marge brut prévisionnelle";
$forecastMargin = $conf->global->FORECAST_MARGIN; // manual entry
$dataInfo6 = $forecastMargin."\n€";


$file = "marginChart"; // id javascript
$fileurl = DOL_DOCUMENT_ROOT.'/custom/tab/img';
$data[] = array(50, 62, 485, 12, 54);

$px4= new DolGraph();
$mesg = $px4->isGraphKo();

if (!$mesg){

	$px4->SetData($data);
	$i = $startyear;
	$legend = ["2022"];
	$px4->datacolor = array(array(240,128,128), array(128, 187, 240));
	$px4->SetTitle("Marge brute sur $year");
	$px4->SetLegend($legend);
	$px4->SetType(array('lines')); // Array with type for each serie. Example: array('type1', 'type2', ...) where type can be: 'pie', 'piesemicircle', 'polar', 'lines', 'linesnopoint', 'bars', 'horizontalbars'...
	$px4->setHeight('200');
	$px4->SetWidth('300');
	$grossMargin = $px4->draw($file, $fileurl);
} else {
	setEventMessage("erreur", 'error');
}
$graphiqueC =  $px4->show($grossMargin);


/**
 * TREASURY BOX
 */
$titleItem4 = "Trésorerie nette";
$solde = $object->fetchSoldeOnYear();
$dataItem4 = price($solde) . "\n€";

$info7 = "Charges mensuelles";

$info8 = "Recurrent mensuel";


/**
 * TRESURY CHART
 */


// $file = "tresuryChart"; // id javascript
// $fileurl = DOL_DOCUMENT_ROOT.'/custom/tab/img';

// for($i = 1; $i <= $month; $i++){


// }

// $px5= new DolGraph();
// $mesg = $px5->isGraphKo();

// if (!$mesg){

// 	$px5->SetData($data);
// 	$i = $startyear;
// 	$legend = ["2022"];

// 	$px5->datacolor = array(array(240,128,128), array(128, 187, 240));
// 	$px5->SetTitle("Evolution de la tresorerie sur $year");
// 	$px5->SetLegend($legend);
// 	$px5->SetType(array('lines')); // Array with type for each serie. Example: array('type1', 'type2', ...) where type can be: 'pie', 'piesemicircle', 'polar', 'lines', 'linesnopoint', 'bars', 'horizontalbars'...
// 	$px5->setHeight('200');
// 	$px5->SetWidth('300');
// 	$tresuryChart = $px5->draw($file, $fileurl);
// } else {
// 	setEventMessage("erreur", 'error');
// }
// $graphiqueD =  $px5->show($tresuryChart);



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

include DOL_DOCUMENT_ROOT . '/custom/tab/template/template_boxes4.php';


// End of page
llxFooter();
$db->close();
