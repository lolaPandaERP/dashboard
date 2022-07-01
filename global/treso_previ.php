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
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) $res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME']; $tmp2 = realpath(__FILE__); $i = strlen($tmp) - 1; $j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) { $i--; $j--; }
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1))."/main.inc.php")) $res = @include substr($tmp, 0, ($i + 1))."/main.inc.php";
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php")) $res = @include dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php";
// Try main.inc.php using relative path
if (!$res && file_exists("../main.inc.php")) $res = @include "../main.inc.php";
if (!$res && file_exists("../../main.inc.php")) $res = @include "../../main.inc.php";
if (!$res && file_exists("../../../main.inc.php")) $res = @include "../../../main.inc.php";
if (!$res) die("Include of main fails");

require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/tab/class/general.class.php';
require_once DOL_DOCUMENT_ROOT.'/compta/bank/class/account.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/dolgraph.class.php';

// Security check
if (empty($conf->tab->enabled)) accessforbidden('Module not enabled');
$socid = 0;
if ($user->socid > 0) { // Protection if external user
	accessforbidden();
}
if(empty($conf->global->START_FISCAL_YEAR) || empty($conf->global->START_FISCAL_LAST_YEAR) ){
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


if(!empty($conf->global->START_FISCAL_YEAR)){
	$startMonthTimestamp = strtotime($startFiscalyear);
	$duree = 12;
	$startMonthFiscalYear = date('n', strtotime('+'.$duree.'month', $startMonthTimestamp));
	$monthFiscalyear = $startMonthFiscalYear;
} else {
	$monthFiscalyear = 1;
}


/**
 * TRESURY
 */
$titleItem1 = "Trésorerie";

$idBankAccount = $object->getIdBankAccount();
$array_tresury = $object->fetchSoldeOnYear($startFiscalyear, $endYear, 3);
$total_tresury = array_sum($array_tresury);

$dataItem1 = price($account) ."\n€";

$info1 = "Trésorerie M-1";

// $sql = "SELECT SUM(amount) as amount";
// $sql .= " FROM ".MAIN_DB_PREFIX."bank";
// $sql .= " WHERE dateo BETWEEN '".$firstDayLastMonth."' AND '".$lastDayLastMonth.'" ';
// $resql = $db->query($sql);

// if ($resql) {
// 	if ($this->db->num_rows($resql)) {
// 		$obj = $this->db->fetch_object($resql);
// 		$result = $obj->amount;
// 	}
// 	$this->db->free($resql);
// }
// $account = $object->getIdBankAccount();
// $soldeOnLastMonth = $object->fetchSolde($firstDayLastMonth, $lastDayLastMonth, 3);
// $dataInfo1 = price($result) ."\n€";

$info2 = "Progression";

// For tresury (popupinfo)
$firstPop_info1 = $titleItem1;
$firstPop_info2 = $info1;
$firstPop_info3 = $info2;

$firstPop_data1 = "Cumul des montants du compte courant sur l'exercice en cours";
$firstPop_data2 = "Cumul des montants du compte courant sur l'exercice précédent (N-1)";
$firstPop_data3 = "Taux de variation : ( (VA - VD) / VA) x 100) ";


$monthsArr = monthArray($langs, 1); // months

$file = "tresuryChart";
$fileurl = DOL_DOCUMENT_ROOT.'/custom/tab/img';
$acc = new Account($db);

for($i = $monthFiscalyear; $i <= 12 ; $i++){

 	strtotime('Last Year');
	$lastyear = date($year-1);

	$lastDayMonth = cal_days_in_month(CAL_GREGORIAN, $i, $year);

	// Start and end of each month on current years
	$date_start = $year.'-'.$i.'-01';
	$date_end = $year.'-'.$i.'-'.$lastDayMonth;

	$solde = $object->fetchSolde($acc->id, $date_start, $date_end); // current
	$total_solde = array_sum($solde); // current

	if(date('n', $date_start) == $i){
		$total_solde += $acc->amount;
	}

	$data[] = [
		html_entity_decode($monthsArr[$i]),
		$total_solde
	];

}

$px = new DolGraph();
$mesg = $px->isGraphKo();
$legend = ['Année N-1', 'Année N'];

if (!$mesg){
	$px->SetTitle("Evolution du chiffre d'affaires");
	$px->datacolor = array(array(255,206,126), array(138,233,232));
	$px->SetData($data);
	$px->SetLegend($legend);
	$px->SetType(array('lines'));
	$px->setHeight('250');
	$px->SetWidth('500');
	$tresuryChart = $px->draw($file, $fileurl);
}
$graphiqueA = $px->show($tresuryChart);



// Total charge of current month
$info3 = "Charges fixes";
$staticExpenses = $object->fetchStaticExpenses($startFiscalyear, $endYear);
$dataInfo3 = price($staticExpenses) . "\n€";

$info4 = "Charges variables";
$variablesExpenses = $object->fetchVariablesExpenses($startFiscalyear, $endYear);
$dataInfo4 = price($variablesExpenses) . "\n€";

$titleItem2 = "Charge totale";
$result3 = ($variablesExpenses + $staticExpenses);
$dataItem2 = price($result3). "\n€";


// For tresury (popupinfo)
$secondPop_info1 = $titleItem2;
$secondPop_info2 = $info3;
$secondPop_info3 = $info4;

$secondPop_data1 = "Charges fixes + charges variables";
$secondPop_data2 = "Additions des dépenses fixes : salaire + charges sociales et fiscales + emprunts (crédits) + paiements divers";
$secondPop_data3 = "Additions des dépenses variables : total (ht) des factures fournisseurs (hors brouillon) + le montant total de TVA sur l'exercice en cours ";

/**
 * GRAPH 2
 */
$file = "ChargesGraph";
$fileurl = DOL_DOCUMENT_ROOT.'/custom/tab/img';
$data = [];
$supplier_invoice = new FactureFournisseur($db);

for($i = $monthFiscalyear; $i <= 12 ; $i++){

 	strtotime('Last Year');
	$lastyear = date($year-1);

	$lastDayMonth = cal_days_in_month(CAL_GREGORIAN, $i, $year);

	// Start and end of each month on current years
	$date_start = $year.'-'.$i.'-01';
	$date_end = $year.'-'.$i.'-'.$lastDayMonth;

	$staticExpenses += $object->fetchStaticExpenses($date_start, $date_end); // static
	$variableExpenses += $object->fetchVariablesExpenses($date_start, $date_end); // variables

	$total_charges = $staticExpenses + $variableExpenses;

	$data[] = [
		html_entity_decode($monthsArr[$i]),
		$total_charges,
	];

}

$px2 = new DolGraph();
$mesg = $px2->isGraphKo();
$legend = ['Année N'];

if (!$mesg){
	$px2->SetTitle("Evolution du chiffre d'affaires");
	$px2->datacolor = array(array(255,206,126), array(138,233,232));
	$px2->SetData($data);
	$px2->SetLegend($legend);
	$px2->SetType(array('lines'));
	$px2->setHeight('250');
	$px2->SetWidth('500');
	$chargeGraph = $px2->draw($file, $fileurl);
}
$graphiqueB = $px2->show($chargeGraph);



/*
 * Actions
 */

// None


/*
 * View
 */

$form = new Form($db);
$formfile = new FormFile($db);

llxHeader('', $langs->trans("Trésorerie et Prévisionnel"));

print load_fiche_titre($langs->trans("Trésorerie et Prévisionnel"));

// Include template
$currentPage = $_SERVER['PHP_SELF'];

print $object->load_navbar($currentPage);

include DOL_DOCUMENT_ROOT.'/custom/tab/template/template_boxes2.php';


/**
 *  CUSTOMER OUTSTANDING AT 30 DAYS
 */


$titleItem3 = "Encours clients à 30 jours";

$dataItem3 = 100;

$accounts = $object->fetchAllBankAccount();
$nbAccount = count($accounts);

/**
 * CUSTOMER TO CASH
 */

// total factures impayées et commandes clients validées sur l'année
$info7 = "Clients à encaisser";
$customer_validated_orders = $object->fetchValidatedOrder($startFiscalyear, $endYear); // unpaid invoices
$customer_validated_invoices = $object->outstandingBill($startFiscalyear, $endYear); // validated orders
$total_customer_validated_invoices = array_sum($customer_validated_invoices);

$customerToCash = ($total_customer_validated_invoices + $customer_validated_orders);

$dataInfo7 = price($customerToCash) . "\n€";


/**
 * STAY IN BANK
 */

$info8 = "Reste en banque";
$totalSoldesAccount = $object->totalSoldes();
$stayBank = ($totalSoldesAccount + $customerToCash); // addition du solde des 3 comptes bq + "client a encaisser"
$dataInfo8 = price($stayBank) . "\n€";


/**
 * SUPPLIER TO PAID
 */
$info10 = "Fournisseurs à payer"; // total factures F impayées et commandes fournisseurs validées
$supplier_unpaid_invoices = $object->outstandingSupplier($startFiscalyear, $endYear);
$total_supplier_unpaid_invoices = array_sum($supplier_unpaid_invoices);

$supplier_ordered_order = $object->supplier_ordered_orders($startFiscalyear, $endYear);

$supplierToPaid = $total_supplier_unpaid_invoices + $supplier_ordered_order;

$dataInfo10 = price($supplierToPaid) . "\n€";

/**
 * SOLDES ACCOUNTS
 */
$info9 = "Solde des comptes"; //  addition du solde des 3 comptes bq - le montant "fournisseur a payer"
$soldesAccount = ($totalSoldesAccount - $supplierToPaid);
$dataInfo9 =  price($soldesAccount) . "\n€";

// For outstanding customer 30 days (popupinfo)
$thirdPop_info1 = "Solde des banques";
$thirdPop_info2 = $info7;
$thirdPop_info3 = $info8;
$thirdPop_info4 = $info9;
$thirdPop_info5 = $info10;

$thirdPop_data1 = "Banques | Caisses - Comptes bancaires : solde du compte ";
$thirdPop_data2 = "Total des factures clients impayées + commandes client validées sur l'exercice en cours";
$thirdPop_data3 = "Addition du solde des 3 comptes bancaires et du 'reste à payer' sur les factures fournisseurs";
$thirdPop_data4 = "Addition du solde des 3 comptes bancaires - le montant 'fournisseurs à payer' ";
$thirdPop_data5 = "Addition des factures fournisseurs impayées et des commandes fournisseurs validées";

?>

<!-- BOX FOR OUTSTANDING -->
<div class=".table-responsive">
<div class="container-fluid-2">
	<div class="card bg-c-white order-card">
		<div class="card-body">
			<div class="pull-left">
				<div class="popup" onclick="showGraph3()">
					<span class="classfortooltip" style="padding: 0px; padding: 0px; padding-right: 3px !important;" title=""><span class="fas fa-info-circle  em088 opacityhigh"></span>
						<span class="popuptext" id="popup30days">
							<h4> Détails des informations / calculs </h4>
						<ul>
							<li><strong><?php print $thirdPop_info1 ?></strong><br><?php print $thirdPop_data1 ?></li><hr>
							<li><strong><?php print $thirdPop_info2 ?></strong><br><?php print $thirdPop_data2 ?> </li><hr>
							<li><strong><?php print $thirdPop_info3 ?></strong><br><?php print $thirdPop_data3 ?> </li><hr>
							<li><strong><?php print $thirdPop_info4 ?></strong><br><?php print $thirdPop_data4 ?> </li><hr>
							<li><strong><?php print $thirdPop_info5 ?></strong><br><?php print $thirdPop_data5 ?> </li>
						</ul>
						</span>
					</div>
					</div>
					<script>
						// When the user clicks on div, open the popup
						function showGraph3() {
							var popup = document.getElementById("popup30days");
							popup.classList.toggle("show");
						}
					</script>
			<h3 class="text-center">
				<?php print $titleItem3 ?>
			</h3>
			<hr>
			<div class="row text-center pb-md-4 justify-content-sm-center ">
				<?php

					foreach($accounts as $account){

						$acc = new Account($db);
						$acc->fetch($account->rowid);

						if($conf->multicurrency->enabled){

						}
						$solde = $acc->solde(1);

						print '<i class="bi bi-bank"></i>';
						print '<button type="button" class="btn btn-success">
								<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="white" class="bi bi-bank">
									<path d="m8 0 6.61 3h.89a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.5.5H15v7a.5.5 0 0 1 .485.38l.5 2a.498.498 0 0 1-.485.62H.5a.498.498 0 0 1-.485-.62l.5-2A.501.501 0 0 1 1 13V6H.5a.5.5 0 0 1-.5-.5v-2A.5.5 0 0 1 .5 3h.89L8 0ZM3.777 3h8.447L8 1 3.777 3ZM2 6v7h1V6H2Zm2 0v7h2.5V6H4Zm3.5 0v7h1V6h-1Zm2 0v7H12V6H9.5ZM13 6v7h1V6h-1Zm2-1V4H1v1h14Zm-.39 9H1.39l-.25 1h13.72l-.25-1Z"/>
								</svg>
								</button>';

						print '<p class="center"><a href="'.DOL_URL_ROOT.'/compta/bank/card.php?id='.$account->rowid.'">' . $account->label . '</a>';
						print '</br>'.price($solde) . "\n€" .'</p>';

					}

					?>
          		</div>

				<div class="center-block">
					<div class="pull-left">
						<?php print $info7 ?> : <h4 class="center"><?php print $dataInfo7 ?></h4><hr>
						<?php print $info8 ?> : <h4 class="center"><?php print $dataInfo8 ?></h4>
					</div>

					<div class="pull-right">
						<?php print $info9 ?> : <h4 class="center"><?php print $dataInfo9 ?></h4><hr>
						<?php print $info10 ?> : <h4 class="center"><?php print $dataInfo10 ?></h4>
					</div>
				</div>
			</div>


<?php
// End of page
llxFooter();
$db->close();
