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

if (empty($conf->global->START_FISCAL_YEAR) || empty($conf->global->START_FISCAL_LAST_YEAR)) {
	accessforbidden('Vous devez obligatoirement renseigner la date de début de l\'exercice fiscal dans la configuration du module');
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
$endYear = date('Y-m-d', strtotime('+' . $duree . 'year', $TimestampCurrentYear));
$endLastYear = date('Y-m-d', strtotime('+' . $duree . 'year', $TimestampCurrentLastYear));

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
$monthsArr = monthArray($langs, 1); // months

// Load start month fiscal year for datas graph
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

// nb oustanding on current and last month (for progress calcul)
$OutCustomerCurrentMonth = $object->outstandingBill($firstDayCurrentMonth, $lastDayCurrentMonth);
$OutCustomerLastMonth = $object->outstandingBill($firstDayLastMonth, $lastDayLastMonth);

$total_OutCustomerCurrentMonth = array_sum($OutCustomerCurrentMonth);
$total_OutCustomerLastMonth = array_sum($OutCustomerLastMonth);

$nbOutCustomerCurrentMonth = count($OutCustomerCurrentMonth);
$nbOuCustomerLastMonth = count($OutCustomerLastMonth);

$info2 = "Progression ";
$resultat = $object->progress($nbOutCustomerCurrentMonth, $nbOuCustomerLastMonth);
$dataInfo2 = intval($resultat) . "\n%";

// Condition d'affichage pour la progression
if ($dataInfo2 > 0) {
	$dataInfo2 = '<p style=color:red>+' . $dataInfo2;
} else {
	$dataInfo2 = '<p style=color:green>' . $dataInfo2;
}

// Load info for otstanding customer popupinfo
$firstPop_info1 = $titleItem1;
$firstPop_info2 = $info1;
$firstPop_info3 = $info2;

$firstPop_data1 = "Total du montant des factures clients impayées <strong>(" . price($total_outstandingBillOnYear) . "\n€)</strong> sur l'exercice en cours (HT)";
$firstPop_data2 = "Total du montant des factures clients impayées  <strong>(" . price($total_OutCustomerLastMonth) . "\n€)</strong> sur le mois précédent";
$firstPop_data3 = "Progression du nombre d'encours clients par rapport au mois dernier </br> ( (VA - VD) / VA) x 100 ) </br> <strong>(( " . $nbOutCustomerCurrentMonth . " - " . $nbOuCustomerLastMonth . ") / " . $nbOuCustomerLastMonth . ") x 100 </strong> </br> Où VA = valeur d'arrivée et VD = Valeur de départ";

// Drawing the first graph for nb of customer invoices by month
$file = "oustandingCustomerChartNumber";
$fileurl = DOL_DOCUMENT_ROOT . '/custom/tab/img';
$invoice = new Facture($db);

for ($i = $startMonthFiscalYear; $i <= 12; $i++) {

	$lastyear = strtotime('Last Year');
	$lastyear = date($year - 1);

	$lastDayMonth = cal_days_in_month(CAL_GREGORIAN, $i, $year);
	$lastDayMonthLastyear =  cal_days_in_month(CAL_GREGORIAN, $i, $lastyear);

	// Current Year
	$date_start = $year . '-' . $i . '-01';
	$date_end = $year . '-' . $i . '-' . $lastDayMonth;

	$array_customer_outstanding_year = $object->outstandingBill($date_start, $date_end);

	$nb_total_customer_outstanding_year = count($array_customer_outstanding_year); // number
	$amount_total_customer_outstanding_year += array_sum($array_customer_outstanding_year); // amount

	if (date('n', $date_start) == $i) {
		$nb_total_customer_outstanding_year += $invoice->total_ttc;
		$amount_total_customer_outstanding_year += $invoice->total_ttc;
	}

	$data[] = [
		$ladder = html_entity_decode($monthsArr[$i]), // months
		$nb_total_customer_outstanding_year, // nb
		$amount_total_customer_outstanding_year, // amount
	];
}

$px = new DolGraph();
$mesg = $px->isGraphKo();
$legend = ['Nombre', 'Montant'];

if (!$mesg) {
	$px->SetTitle("Evolution des encours clients par mois - TTC");
	$px->datacolor = array(array(255, 99, 71), array(128, 187, 240));
	$px->SetData($data);
	$px->SetLegend($legend);
	$px->SetType(array('lines'));
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

$info4 = "Progression";
$OutSupplierCurrentMonth = $object->outstandingSupplier($firstDayCurrentMonth, $lastDayCurrentMonth, 0);
$total_OutSupplierCurrentMonth = array_sum($OutSupplierCurrentMonth); // fetch total in current month

$OutSupplierLastMonth = $object->outstandingSupplier($firstDayLastMonth, $lastDayLastMonth, 0);

$nbOutSupplierCurrentMonth = count($OutSupplierCurrentMonth);
$nbOutSupplierLastMonth = count($OutSupplierLastMonth);

// Progression du nb d'encours entre le mois dernier / courant
$resultat = $object->progress($nbOutSupplierCurrentMonth, $nbOutSupplierLastMonth);
$dataInfo4 = $resultat . "\n%";

// View data intuitively (positive or negative development)
if ($dataInfo3 <= 0) {
	$dataInfo3 = '<p class="badge badge-success" style="color:green;">Aucun encours';
} else {
	$dataInfo3 = $dataInfo3;
}

// Supplier chart
$file = "supplierOustandingChart";
$fileurl = DOL_DOCUMENT_ROOT . '/custom/tab/img';
$data = []; // reset datas
$invoice_supplier = new FactureFournisseur($db);

for ($i = $startMonthFiscalYear; $i <= 12; $i++) {

	// Current Year
	$date_start = $year . '-' . $i . '-01';
	$date_end = $year . '-' . $i . '-' . $lastDayMonth;

	$array_supplier_outstanding_year = $object->outstandingSupplier($date_start, $date_end, 0);

	$nb_total_supplier_outstanding_year = count($array_supplier_outstanding_year); // number
	$amount_total_supplier_outstanding_year += array_sum($array_supplier_outstanding_year); // amount

	if (date('n', $date_start) == $i) {
		$nb_total_supplier_outstanding_year += $invoice_supplier->total_ttc;
		$amount_total_supplier_outstanding_year += $invoice_supplier->total_ttc;
	}

	$ladder = html_entity_decode($monthsArr[$i]);

	$data[] = [
		$ladder, // months
		$nb_total_supplier_outstanding_year, // nb
		$amount_total_supplier_outstanding_year, // amount
	];
}

$px2 = new DolGraph();
$mesg = $px2->isGraphKo();
$legend = ['Nombre', 'Montant'];

if (!$mesg) {
	$px2->SetTitle("Evolution des encours fournisseurs - TTC");
	$px2->datacolor = array(array(255, 99, 71), array(128, 187, 240));
	$px2->SetData($data);
	$px2->SetLegend($legend);
	$px2->SetType(array('lines'));
	$px2->setHeight('250');
	$px2->SetWidth('500');
	$total_supplier_outstandingChart = $px2->draw($file, $fileurl);
}
$graphiqueB = $px2->show($total_supplier_outstandingChart);


// Load info for otstanding customer popupinfo
$secondPop_info1 = $titleItem2;
$secondPop_info2 = $info3;
$secondPop_info3 = $info4;

$secondPop_data1 = "Total du montant des factures fournisseurs impayées <strong>(" . price($total_outstandingSupplierOnYear) . "\n€)</strong> sur l'exercice en cours (HT)";
$secondPop_data2 = "Total du montant des factures fournisseurs impayées  <strong>(" . price($total_outstandingSupplierOnLastMonth) . "\n€)</strong> sur le mois précédent";
$secondPop_data3 = "Progression du nombre d'encours fournisseurs par rapport au mois dernier </br> ( (VA - VD) / VA) x 100 ) </br> <strong>(( " . $nbOutSupplierCurrentMonth . " - " . $nbOutSupplierLastMonth . ") / " . $nbOutSupplierLastMonth . ") x 100 </strong> </br> Où VA = valeur d'arrivée et VD = Valeur de départ";

/**
 * CUSTOMER AND SUPPLIERS OUTSATNDING
 */
$titleItem3 = "Encours C/F ";
$dataItem3 = price($total_outstandingBillOnYear - $total_outstandingSupplierOnYear) . "\n€"; // soustraction des encours client et encours fournisseur

$info5 = "Encours total M-1";
$dataInfo5 = intval($total_OutCustomerLastMonth - $total_outstandingSupplierOnLastMonth) . "\n€"; // encours client m-1 - encours fourn m-1

$info6 = "Progression";
$outCFCurrentMonth = ($total_OutCustomerCurrentMonth - $total_OutSupplierCurrentMonth);

$resultat = $object->progress($total_OutCustomerCurrentMonth, $dataInfo5);
$dataInfo6 = $resultat . "\n%";

// Load info for outstanding C/F popupinfo
$thirdPop_info1 = $titleItem3;
$thirdPop_info2 = $info5;
$thirdPop_info3 = $info6;


// C/F chart on current year
$file = "CFOustandingChartCurrentYear";
$fileurl = DOL_DOCUMENT_ROOT . '/custom/tab/img';
$data = [];
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

// Chart for oustanding C/F on last year
$file = "CFOustandingChartLastYear";
$fileurl = DOL_DOCUMENT_ROOT . '/custom/tab/img';
$data = [];

for ($i = $startMonthFiscalYear; $i <= 12; $i++) {

	strtotime('Last Year');
	$lastyear = date($year - 1);
	$lastDayMonthLastyear =  cal_days_in_month(CAL_GREGORIAN, $i, $lastyear);

	/**
	 * CUSTOMER
	 */
	// Start and end of each month on last year
	$date_start_lastYear = $lastyear . '-' . $i . '-01';
	$date_end_lastYear = $lastyear . '-' . $i . '-' . $lastDayMonthLastyear;

	$array_customer_outstanding_Lastyear = $object->outstandingBill($date_start_lastYear, $date_end_lastYear);
	$amount_total_customer_outstanding_Lastyear = array_sum($array_customer_outstanding_Lastyear);

	/**
	 * SUPPLIERS
	 */
	$total_supplier_outstanding_Lastyear = $object->outstandingSupplier($date_start_lastYear, $date_end_lastYear, 0);
	$amount_total_supplier_outstanding_Lastyear = array_sum($total_supplier_outstanding_Lastyear);

	if (date('n', $date_start) == $i) {
		$amount_total_customer_outstanding_Lastyear += $invoice_customer->total_ttc;
		$amount_total_supplier_outstanding_Lastyear += $invoice_supplier->total_ttc;
	}

	$ladder = html_entity_decode($monthsArr[$i]);

	$data[] = [
		$ladder, // months
		$amount_total_customer_outstanding_Lastyear,
		$amount_total_supplier_outstanding_Lastyear,
	];
}

$px3bis = new DolGraph();
$mesg = $px3bis->isGraphKo();
$legend = ['Clients', 'Fournisseurs'];

if (!$mesg) {
	$px3bis->SetTitle("Evolution sur l'exercice précédent - (TTC)");
	$px3bis->datacolor = array(array(255, 165, 51), array(103, 187, 14));
	$px3bis->SetData($data);
	$px3bis->SetLegend($legend);
	$px3bis->SetType(array('bar'));
	$px3bis->setHeight('250');
	$px3bis->SetWidth('500');
	$amount_total_CFChart_lastyear = $px3bis->draw($file, $fileurl);
}

// filter on year for oustanding C/F graph
$filter = $_GET['filter'];

if ($filter == $year) {
	$graphiqueC = $px3->show($amount_total_CFChart);
} elseif ($filter == $lastyear) {
	$graphiqueC1 = $px3bis->show($amount_total_CFChart_lastyear);
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
include DOL_DOCUMENT_ROOT . '/custom/tab/template/template_boxes2.php';
$lastyear = strtotime('Last Year');
$lastyear = date($year - 1);

?>

<!-- CUSTOMER OUTSTANDING -->
<div class="grid-container-4">
	<div class="grid-1">
		<div class="card bg-c-blue order-card">
			<div class="card-body">
				<div class="pull-left">
					<div class="popup" onclick="showGraph3()">
						<span class="classfortooltip" style="padding: 0px; padding: 0px; padding-right: 3px !important;" title=""><span class="fas fa-info-circle  em088 opacityhigh"></span>
							<span class="popuptext" id="thirdPop">
								<h4> Détails des informations / calculs </h4>
								<ul>
									<li><strong><?php print $thirdPop_info1 ?></strong><br><?php print $thirdPop_data1 ?></li>
									<hr>
									<li><strong><?php print $thirdPop_info2 ?></strong><br><?php print $thirdPop_data2 ?> </li>
									<hr>
									<li><strong><?php print $thirdPop_info3 ?></strong><br><?php print $thirdPop_data3 ?> </li>
								</ul>
							</span>
					</div>
				</div>
				<script>
					// When the user clicks on div, open the popup
					function showGraph3() {
						var firstPopup = document.getElementById("thirdPop");
						firstPopup.classList.toggle("show");
					}
				</script>
				<h4 class="text-center">
					<?php print $titleItem3 ?>
				</h4>
				<h1 class="text-center">
					<?php print $dataItem3 ?>
				</h1>
				<hr>
				<div class="col-lg-14">
					<div class="center-block">
						<div class="pull-left"><?php print $info5 ?> : <h4 class="center"><?php print $dataInfo5 ?></h4>
						</div>
						<div class="pull-right"><?php print $info6 ?> : <h4 class="center"><?php print $dataInfo6 ?></h4>
						</div>
					</div>
				</div>
			</div>
			<?php
			print $graphiqueC;
			print $graphiqueC1
			?>
			<div class="center">
				<h5>Filtre selon l'année</h5>
				<?php
				if ($_GET['filter'] == '2021') {
				?> <a id='anneeN-1' href="./encoursCF.php?filter=2021" style="color:red"><?php print $lastyear ?></a></hr>
					<a id='anneeN' href="./encoursCF.php?filter=2022"><?php print $year ?></a>
				<?php
				} elseif ($_GET['filter'] == '2022') {
				?> <a id='anneeN-1' href="./encoursCF.php?filter=2022"><?php print $lastyear ?></a></hr>
					<a id='anneeN' href="./encoursCF.php?filter=2022" style="color:red"><?php print $year ?></a>
				<?php
				}
				?>
			</div>
		</div>
	</div>
</div>
<?php

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


// Customer outstandings exceeded
$invoice = new Facture($db);

// si date limite de regelemnt est inferieur au jour d'aujrdh - 1mois car date limite de reglement accorde delai d'1 mois
$date = date('Y-m-d');

$array_customer_exceed = $object->fetchCustomerBillExceed($date, $startFiscalLastyear, $endYear, 2);
$array_customer_credit_exceed = $object->fetchCustomerBillExceed($date, $startFiscalLastyear, $endYear, 0);

$total_amount_exceed = array_sum($array_customer_exceed + $array_customer_credit_exceed);
$nb_total_exceed = count($array_customer_exceed + $array_customer_credit_exceed);

$titleItem4 = "Encours clients dépassés (" . $nb_total_exceed . ") ";

if ($total_amount_exceed <= 0) {
	$dataItem4 = '<p class="badge badge-success" style="color:green;">Aucun encours clients dépassés';
} else {
	$dataItem4 = '<p class="badge badge-danger"><i class="fas fa-exclamation-triangle"></i>' . "\n" . price($total_amount_exceed) . "\n€" . '</p>';
}

// Load info for customer exceed popupinfo
$fourPop_info1 = $titleItem4;
$fourPop_data1 = "Somme des factures clients impayées (TTC) dont la date d'échéance a été dépassée";

// Datas for exceed oustanding customer
$file = "customerOutstandingExceed";
$fileurl = DOL_DOCUMENT_ROOT . '/custom/tab/img';
$invoice = new Facture($db);
$data =  [];

for ($i = $startMonthFiscalYear; $i <= 12; $i++) {

	$lastDayMonth = cal_days_in_month(CAL_GREGORIAN, $i, $year);

	// Current Year
	$date_start = $year . '-' . $i . '-01';
	$date_end = $year . '-' . $i . '-' . $lastDayMonth;
	$date = date('Y-m-d');

	// Array of standard invoices / credit note invoice
	$array_customer_exceed = $object->fetchCustomerBillExceed($date, $date_start, $date_end, 2);
	$array_customer_credit_exceed = $object->fetchCustomerBillExceed($date, $date_start, $date_end, 0);

	$total_amount_exceed = array_sum($array_customer_exceed);
	$total_amount_credit_deposit = array_sum($array_customer_credit_exceed);

	$nb_customer_exceed = count($array_customer_exceed);
	$nb_credit_deposit = count($array_customer_credit_exceed);

	$total_nb_exceed = ($nb_customer_exceed + $nb_credit_deposit);
	$total_amount_exceed = ($total_amount_exceed + $total_amount_credit_deposit);

	$nb_outCustomerExceeded = count($array_customer_exceed + $array_customer_credit_exceed);

	if (date('n', $date_start == $i)) {
		$total_nb_exceed += $invoice->total_ttc;
		$total_amount_exceed += $invoice->total_ttc;
	}

	$ladder = html_entity_decode($monthsArr[$i]);

	$data[] = [
		$ladder,
		$total_nb_exceed,
		$total_amount_exceed,
	];
}

$px4 = new DolGraph();
$mesg = $px4->isGraphKo();
$legend = ['Nombre', 'Montant'];

if (!$mesg) {
	$px4->SetTitle("Evolution des encours dépassés");
	$px4->datacolor = array(array(255, 99, 71), array(128, 187, 240));
	$px4->SetData($data);
	$px4->SetLegend($legend);
	$px4->SetType(array('bar'));
	$px4->setHeight('250');
	$px4->SetWidth('500');
	$total_customer_exceed = $px4->draw($file, $fileurl);
}

$graphiqueD = $px4->show($total_customer_exceed);


// Datas for exceed oustanding supplier
$file = "supplierOutstandingExceed";
$fileurl = DOL_DOCUMENT_ROOT . '/custom/tab/img';
$invoice_supplier = new FactureFournisseur($db);
$data =  [];

for ($i = $startMonthFiscalYear; $i <= 12; $i++) {

	$lastDayMonth = cal_days_in_month(CAL_GREGORIAN, $i, $year);

	// Current Year
	$date_start = $year . '-' . $i . '-01';
	$date_end = $year . '-' . $i . '-' . $lastDayMonth;
	$date = date('Y-m-d');

	// Array of standard invoices / credit note invoice
	$array_supplier_exceed = $object->fetchSupplierrBillExceed($date, $date_start, $date_end, 2);
	$array_supplier_credit_exceed = $object->fetchSupplierrBillExceed($date, $date_start, $date_end, 0);

	$total_amount_exceed = array_sum($array_supplier_exceed);
	$total_amount_credit_deposit = array_sum($array_supplier_credit_exceed);

	$nb_supplier_exceed = count($array_supplier_exceed);
	$nb_credit_deposit = count($array_supplier_credit_exceed);

	$total_nb_exceed = ($nb_supplier_exceed + $nb_credit_deposit);
	$total_amount_exceed = ($total_amount_exceed + $total_amount_credit_deposit);

	$nb_outsupplierExceeded = count($array_supplier_exceed + $array_supplier_credit_exceed);

	if (date('n', $date_start == $i)) {
		$total_nb_exceed += $invoice_supplier->total_ttc;
		$total_amount_exceed += $invoice_supplier->total_ttc;
	}

	$ladder = html_entity_decode($monthsArr[$i]);

	$data[] = [
		$ladder,
		$total_nb_exceed,
		$total_amount_exceed,
	];
}

$px5 = new DolGraph();
$mesg = $px5->isGraphKo();
$legend = ['Nombre', 'Montant'];

if (!$mesg) {
	$px5->SetTitle("Evolution des encours fournisseurs dépassés");
	$px5->datacolor = array(array(255, 99, 71), array(128, 187, 240));
	$px5->SetData($data);
	$px5->SetLegend($legend);
	$px5->SetType(array('bar'));
	$px5->setHeight('250');
	$px5->SetWidth('500');
	$total_supplier_exceed = $px5->draw($file, $fileurl);
}

$graphiqueE = $px5->show($total_supplier_exceed);


?>

<!-- end Outstanding suppliers exceed -->
<div class="row">
	<div class="card-deck">
		<div class="card">
			<div class="card-body">
				<div class="pull-left">
					<div class="popup" onclick="showGraph4()">
						<span class="classfortooltip" style="padding: 0px; padding: 0px; padding-right: 3px !important;" title=""><span class="fas fa-info-circle  em088 opacityhigh"></span>
							<span class="popuptext" id="fourPop">
								<h4> Détails des informations / calculs </h4>
								<ul>
									<li><strong><?php print $fourPop_info1 ?></strong><br><?php print $fourPop_data1 ?></li>
								</ul>
							</span>
					</div>
				</div>
				<script>
					// When the user clicks on div, open the popup
					function showGraph4() {
						var firstPopup = document.getElementById("fourPop");
						firstPopup.classList.toggle("show");
					}
				</script>
				<h3 class="text-center">
					<?php print $titleItem4 ?>
				</h3>
				<hr>
				</br>
				<h4 class="text-center">
					<?php print $dataItem4 ?>
				</h4>
				<div>
					<?php print $graphiqueD ?>
				</div>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="card">
			<div class="card-body">
				<div class="pull-left">
					<div class="popup" onclick="showPop5()">
						<span class="classfortooltip" style="padding: 0px; padding: 0px; padding-right: 3px !important;" title=""><span class="fas fa-info-circle  em088 opacityhigh"></span>
							<span class="popuptext" id="fivePop">
								<h4> Détails des informations / calculs </h4>
								<ul>
									<li><strong><?php print $fivePop_info1 ?></strong><br><?php print $fivePop_data1 ?></li>
								</ul>
							</span>
					</div>
				</div>
				<script>
					// When the user clicks on div, open the popup
					function showPop5() {
						var firstPopup = document.getElementById("fivePop");
						firstPopup.classList.toggle("show");
					}
				</script>
				<h3 class="text-center">
					<?php print $titleItem5 ?>
				</h3>
				<hr>
				</br>
				<h4 class="text-center">
					<?php print $dataItem5 ?>
				</h4>
				<div>
					<?php print $graphiqueE ?>
				</div>
			</div>
		</div>

		<?php

		// End of page
		llxFooter();
		$db->close();
