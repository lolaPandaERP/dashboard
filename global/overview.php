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

// Security check
if (empty($conf->tab->enabled)) accessforbidden('Module not enabled');
$socid = 0;
if ($user->socid > 0) { // Protection if external user
	accessforbidden();
}

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
 * Description Data
*/

$titleItem1 = "Chiffre d'affaires";
$info1 = "Chiffre d'affaire n-1";
$info2 = "Progression ";

$turnover = $object->turnover($firstDayYear, $lastDayYear); // fetch datas
$dataItem1 = price($turnover)."\n€"; // display datas

$turnoverLastYear = $object->turnover($firstDayLastYear, $lastDayLastYear);
$dataInfo1 = price($turnoverLastYear)."\n€";

$result = $object->progress($turnover, $turnoverLastYear);
$dataInfo2 = intval($result)  . "\n%";

// Display increase/decrease
if($dataInfo2 < 0){
	$dataInfo2 = '<p style=color:red>'.$dataInfo2.'</p>';
} else {
	$dataInfo2 = '<p style=color:green>'.$dataInfo2.'</p>';
}

/**
 * CHARTS
 */

$monthsArr = monthArray($langs, 1); // months

$file = "evolutionCAchart";
$fileurl = DOL_DOCUMENT_ROOT.'/custom/tab/img';

for($i = 0; $i <= 12; $i++){

	// on recupere toutes les factures de l'année 2022
 	$customerUnpaidInvoiceArrayOnYear = $object->fetchCA($firstDayYear, $lastDayYear);

	// fetch all invoices for current year
	foreach($customerUnpaidInvoiceArrayOnYear as $res){

		$invoice = new Facture($db);
	 	$invoice->fetch($res->rowid);

		// We get the total of customers invoices for each month
		if(date('n', $invoice->date_creation) == $i){
			$amountLastyear += $invoice->total_ht;
		 }
	}

	// fetch all invoices on last years
	$customerUnpaidInvoiceArrayOnLastYear = $object->fetchCA($firstDayLastYear, $lastDayLastYear);

	foreach($customerUnpaidInvoiceArrayOnLastYear as $rest){

		$invoice = new Facture($db);
	 	$invoice->fetch($rest->rowid);

		// We get the total of customers invoices for each month
		if(date('n', $invoice->date_creation) == $i){
			$amount += $invoice->total_ht;
		 }
	}

	$data[] = [
		html_entity_decode($monthsArr[$i]), // month
		$amount, // CA/months sur l'année courante
		$amountLastyear // CA/months l'année précédente

	];
}

$px = new DolGraph();
$mesg = $px->isGraphKo();
$legend = ["2021", "2022"];
if (!$mesg){
	$px->SetTitle("Evolution du chiffre d'affaires");
	$px->datacolor = array(array(255,206,126), array(138,233,232));
	$px->SetData($data);
	$px->SetLegend($legend);
	$px->SetType(array('lines'));
	$px->setHeight('250');
	$px->SetWidth('500');
	$turnoverChart = $px->draw($file, $fileurl);
} else {
	print "Pas assez de données...";
}

$graphiqueA = $px->show($turnoverChart);

// For first info popup
$firstPop_info1 = $titleItem1;
$firstPop_info2 = $info1;
$firstPop_info3 = $info2;

$firstPop_data1 = "Somme des factures clients sur l'année en cours (HORS BORUILLON)";
$firstPop_data2 = "Somme des factures clients sur l'année précédente (HORS BROUILLON)";
$firstPop_data3 = "Taux de variation : ( (VA - VD) / VA) x 100 ) ";

?>


<?php

/**
 * OUTSTANDING CUSTOMER AND SUPPLIER
 */

$titleItem2 = "Encours C/F";
$customerOutstandingYear = $object->outstandingBill($firstDayYear, $lastDayYear);
$supplierOutstandingYear = $object->outstandingSupplierOnYear($firstDayYear, $lastDayYear);

$totalOutstangdingCF = ($customerOutstandingYear - $supplierOutstandingYear);

$dataItem2 = price($totalOutstangdingCF)  ."\n€";

$info3 = "Encours C / mois ";
$customerOutstandingMonth = $object->outstandingBill($firstDayCurrentMonth, $lastDayCurrentMonth);
$dataInfo3 = price($customerOutstandingMonth) . "\n€";

if($dataInfo3 > 0){
	$dataInfo3 = '<p style=color:red>'.$dataInfo3.'</p>';
} else {
	$dataInfo3 = '<p>Aucun encours client pour le mois de '.$object->returnMonth($month).'</p>';
}

$info4 = "Encours F / mois";
$supplierOutstandingMonth = $object->outstandingSupplierOnCurrentMonth($firstDayCurrentMonth, $lastDayCurrentMonth);
$dataInfo4 = price($supplierOutstandingMonth) . "\n€";

if($dataInfo4 > 0){
	$dataInfo4 = '<p style=color:red>'.$dataInfo4.'</p>';
} else {
	$dataInfo4 = '<p>Aucun encours fournisseur pour le mois de '.$object->returnMonth($month).'</p>';
}

/**
 * CHARTS FOR OUTSTANDING CUSTOMER
 */

 // Pagination for switch mode customer to supplier
if(isset($_GET['page']) && !empty($_GET['page'])){
    $currentPage = (int) strip_tags($_GET['page']);
}else{
    $currentPage = 1;
}

	// Drawing the first graph for nb of customer invoices by month
	$stats = new FactureStats($db, $socid, $mode = 'customer', ($userid > 0 ? $userid : 0), ($typent_id > 0 ? $typent_id : 0), ($categ_id > 0 ? $categ_id : 0));

	if($mode == 'customer') {
		$dataGraph = $stats->getNbByMonthWithPrevYear($endyear, $startyear, 0, 0, 1);

		$filenamenb = $dir."/invoicesnbinyear-".$year.".png";
		$fileurlnb = DOL_URL_ROOT.'/viewimage.php?modulepart=billstats&amp;file=invoicesnbinyear-'.$year.'.png';

		$px2 = new DolGraph();
		$mesg = $px2->isGraphKo();

		// NB
		if (!$mesg) {
			$px2->SetData($dataGraph);
			$i = $startyear;
			$legend = array();
			while ($i <= $endyear) {
				$legend[] = $i;
				$i++;
			}
			$px2->SetLegend($legend);
			$px2->SetType(array('bar'));
			$px2->datacolor = array(array(208,255,126), array(255,206,126), array(138,233,232));
			$px2->SetMaxValue($px2->GetCeilMaxValue());
			$px2->SetWidth('500');
			$px2->SetHeight('250');
			$px2->SetTitle("Nb de factures client par mois");
			$px2->SetShading(3);
			$nbInvoiceByMonth = $px2->draw($filenamenb, $fileurlnb);
		}

		$graphiqueB = $px2->show($nbInvoiceByMonth);


		// Drawing the second graph for amount of customer invoices by month
		$stats = new FactureStats($db, $socid, $mode = 'customer', ($userid > 0 ? $userid : 0), ($typent_id > 0 ? $typent_id : 0), ($categ_id > 0 ? $categ_id : 0));
		$dataGraph = $stats->getAmountByMonthWithPrevYear($endyear, $startyear);

		$filenameamount = $dir."/invoicesamountinyear-".$year.".png";
		$fileurlamount = DOL_URL_ROOT.'/viewimage.php?modulepart=billstats&amp;file=invoicesamountinyear-'.$year.'.png';


		$data = [];
		$px3 = new DolGraph();
		$mesg = $px3->isGraphKo();

		if (!$mesg) {
			$px3->SetData($dataGraph);
			$i = $startyear;
			$legend = array();
			while ($i <= $endyear) {
				$legend[] = $i;
				$i++;
			}
			$px3->SetLegend($legend);
			$px3->SetType(array('bar'));
			$px3->datacolor = array(array(208,255,126), array(255,206,126), array(138,233,232));
			$px3->SetMaxValue($px3->GetCeilMaxValue());
			$px3->SetWidth('500');
			$px3->SetHeight('250');
			$px3->SetTitle("Montant de factures clients par mois");
			$px3->SetShading(3);
			$amountInvoiceByMonth = $px3->draw($filenameamount, $fileurlamount);
		}

		$graphiqueB1 = $px3->show($amountInvoiceByMonth);

	} elseif($mode == 'supplier') {
		print "c graphique fournisseurs";
	}


 // Drawing the second graph for amount invoices by month
// $data = [];
// $dataGraph = $stats->getAmountByMonthWithPrevYear($endyear, $startyear);

// $px4 = new DolGraph();
// $mesg = $px4->isGraphKo();
// if (!$mesg) {
// 	$px4->SetData($dataGraph);
// 	$i = $startyear;
// 	$legend = array();
// 	while ($i <= $endyear) {
// 		$legend[] = $i;
// 		$i++;
// 	}
// 	$px4->SetLegend($legend);
// 	$px4->datacolor = array(array(208,255,126), array(255,206,126), array(138,233,232));
// 	$px4->SetMaxValue($px4->GetCeilMaxValue());
// 	$px4->SetWidth('500');
// 	$px4->SetHeight('250');
// 	$px4->SetType(array('lines'));
// 	$px4->SetTitle("Montant des factures clients par mois - HT");
// 	$amountInvoiceByMonth = $px4->draw($filenameamount, $fileurlamount);
// 	}

// 	$graphiqueB3 = $px4->show($amountInvoiceByMonth);


// For second popup info
$secondPop_info1 = $titleItem2;
$secondPop_info2 = $info3;
$secondPop_info3 = $info4;

$secondPop_data1 = " Addition de l'ensemble des factures (client et fournisseur) impayées sur l'année en cours";
$secondPop_data2 = " Somme de toutes les factures clients impayées sur le mois courant (HT - hors brouillon)";
$secondPop_data3 = "Somme du 'total_ht' de toutes les factures fournisseurs impayées sur le mois en courant (HT - hors brouillon) ";




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

// Cart
$file = "marginChart";
$fileurl = DOL_DOCUMENT_ROOT.'/custom/tab/img';

$marginArray = $object->grossMargin($firstDayYear, $lastDayYear);

for($i = 0; $i <= 12; $i++){

	// We get the total of customers validated order for each month
	foreach($marginArray as $ret){
		$cmd = new Commande($db);
		$rest = $cmd->fetch($ret->rowid);

		if(date('n', $cmd->date_commande) == $i){
			 $validatedOrder += $cmd->total_ht;
		}
	}

	// We add datas in the graph
	$data[] = [
		html_entity_decode($monthsArr[$i]),
		$validatedOrder,
	];
}

$data = [];
$px5 = new DolGraph();
$mesg = $px5->isGraphKo();
$legend = ["2022"];

if (!$mesg){
	$px5->SetTitle("Evolution de la marge brute sur l'année");
	$px5->datacolor = array(array(240,128,128), array(128, 187, 240));
	$px5->SetData($data);
	$px5->SetLegend($legend);
	$px5->SetType(array('lines')); // Array with type for each serie. Example: array('type1', 'type2', ...) where type can be: 'pie', 'piesemicircle', 'polar', 'lines', 'linesnopoint', 'bars', 'horizontalbars'...
	$px5->setHeight('400');
	$px5->SetWidth('600');
	$marginChart = $px5->draw($file, $fileurl);
}

$graphiqueC = $px5->show($marginChart);




// For margin info popup
$thirdPop_info1 = $titleItem3;
$thirdPop_info2 = $info5;
$thirdPop_info3 = $info6;

$thirdPop_data1 = "Somme (HT) des commandes clients validées - sur l'année en cours";
$thirdPop_data2 = "Total de la marge des commandes validées - la marge définit dans la configuration du module";
$thirdPop_data3 = "Définit dans la configuration du module";



/**
 * ---- TREASURY BOX -------
 */
$titleItem4 = "Trésorerie nette";
$solde = $object->fetchSoldeOnYear();
$dataItem4 = price($solde) . "\n€";

$info7 = "Charges mensuelles"; // CV + CF
$staticExpenses = $object->fetchStaticExpenses($firstDay, $lastDayYear); // static charge
$variablesExpenses = $object->fetchVariablesExpenses($firstDayYear, $lastDayYear); // variable charge

$result3 = ( ($variablesExpenses + $staticExpenses) / 12);
$dataInfo7 = price($result3) . "\n€";

$info8 = "Recurrent mensuel";

// Graph
$file = "tresuryChart"; // id javascript
$fileurl = DOL_DOCUMENT_ROOT.'/custom/tab/img';
$tresuryArray = $object->fetchSolde($firstDayYear, $lastDayYear);

$data = [];
$dataGraph = $stats->getAmountByMonthWithPrevYear($endyear, $startyear);

$px4 = new DolGraph();


$px4 = new DolGraph();
$mesg = $px4->isGraphKo();

if (!$mesg) {
	$px4->SetData($dataGraph);
	$i = $startyear;
	$legend = array();
	while ($i <= $endyear) {
		$legend[] = $i;
		$i++;
	}
	$px4->SetTitle("Evolution de la trésorerie nette sur l'année");
	$px4->datacolor = array(array(240,128,128), array(128, 187, 240));
	$px4->SetData($data);
	$px4->SetLegend($legend);
	$px4->SetType(array('lines')); // Array with type for each serie. Example: array('type1', 'type2', ...) where type can be: 'pie', 'piesemicircle', 'polar', 'lines', 'linesnopoint', 'bars', 'horizontalbars'...
	$px4->setHeight('400');
	$px4->SetWidth('600');
	$tst = $px4->draw($file, $fileurl);
}

$graphiqueD = $px4->show($tst);



/**
 * For tresury info popup
 */

$fourPop_info1 = $titleItem4;
$fourPop_info2 = $info7;
$fourPop_info3 = $info8;

$fourPop_data1 = "Correspond à l'argent en banque (solde du compte - Banques & Caisse) - le 'reste à payer' qui a été encaissé sur les factures fournisseurs - 'reste à payer' qui a été réglé sur les factures fournisseurs";
$fourPop_data2 = "Sommes des charges variables et fixes sur l'année en cours";
$fourPop_data3 = "Le MMR (Monthly Recurring Revenue) est un terme désignant les revenus issus des clients réguliers";




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

$currentPage = $_SERVER['PHP_SELF'];

print $object->load_navbar($currentPage);


include DOL_DOCUMENT_ROOT . '/custom/tab/template/template_boxes4.php';

// End of page
llxFooter();
$db->close();
