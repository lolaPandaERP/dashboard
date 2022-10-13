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
require_once DOL_DOCUMENT_ROOT . '/commande/class/commande.class.php';

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

// the end date automatically for current and last year
$dateEndYear = date('Y-m-d', strtotime('+'.$duree.'year', $TimestampCurrentYear));
$TimestampendYear = strtotime($dateEndYear);
$endYear = date('Y-m-d', strtotime('-'.$duree.'day', $TimestampendYear));

$dateEndLastYear = date('Y-m-d', strtotime('+'.$duree.'year', $TimestampCurrentLastYear));
$TimestampendLastYear = strtotime($dateEndLastYear);
$endLastYear = date('Y-m-d', strtotime('-'.$duree.'day', $TimestampendLastYear));

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
 * TRESURY
 */
$titleItem1 = "Trésorerie nette";

$idaccounts = $object->fetchAllBankAccount();
$currentAccount = min($idaccounts);
$currentAccount = (int)$currentAccount;

// Current balance on n year
$solde = $object->totalSoldeCurrentAccount($currentAccount);

$dataItem1 = '<a href="'.DOL_URL_ROOT.'/compta/bank/bankentries_list.php?id='.$currentAccount.'">';
$dataItem1 .= price($solde) ."\n€";
$dataItem1 .= '</a>';

$info1 = "Trésorerie M-1 :";

$info2 = "Progression : ";

// For tresury (popupinfo)
$firstPop_info1 = $titleItem1;
$firstPop_info2 = $info1;
$firstPop_info3 = $info2;

$firstPop_data1 = "Solde du compte courant sur l'exercice fiscal en cours";
$firstPop_data2 = "Solde du compte courant du mois précédent sur l'exercice fiscal en cours ";
$firstPop_data3 = "Taux de variation : ( (VA - VD) / VD) x 100) où :
					</br> <strong> VA </strong> = solde du mois courant sur l'exercice fiscal en cours
					</br> <strong> VD </strong> = solde de mois courant sur l'exercice fiscal précédent
				   <strong> ( (".$total_month_year." - ".$total_month_lastyear.") / ".$total_month_year.") x 100 </strong>";

$monthsArr = monthArray($langs, 1); // months

// Graph tresury
$file = "tresuryChart";
$fileurl = DOL_DOCUMENT_ROOT.'/custom/tab/img';

// todo graph

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
	$solde = $object->totalSoldeCurrentAccount($currentAccount);

	$data1[] = [
		html_entity_decode($monthsArr[$mm]),
		$solde,
	];

	if($mm >= 12){
		$mm = 0;
		$yy++;
	}

}

$px1 = new DolGraph();
$mesg = $px1->isGraphKo();
$legend = ['Exercice N'];

if (!$mesg){
	$px1->SetTitle("Evolution de la trésorerie nette");
	$px1->datacolor = array(array(67,94,246));
	$px1->SetData($data1);
	$px1->SetLegend($legend);
	$px1->SetType(array('lines'));
	$px1->setHeight('250');
	$px1->SetWidth('500');
	$turnoverChart = $px1->draw($file, $fileurl);
}
$graphiqueA = $px1->show($turnoverChart);


// TOTAL CHARGES BOX
$info3 = "Charges fixes";

/**
 * Details charges : static charges + variable charges
 */

// Salarys
$arr_salarys = $object->fetchSalarys($startFiscalYear, $endYear, $currentAccount);

// Sociales Taxes and charges
$socialesTaxes_charges = $object->fetchSocialAndTaxesCharges($startFiscalYear, $endYear);

// Validated supplier invoice (excluding loan)
$arr_supp_invoices_exluding_loan = $object->static_charge_excluding_loan($startFiscalYear, $endYear);
$total_supp_invoices_exluding_loan = array_sum($arr_supp_invoices_exluding_loan);

// Loan
$arr_loan = $object->fetchEmprunts($startFiscalYear, $endYear);
$total_loan = array_sum($arr_loan);

$total_static_charges = ($arr_salarys + $socialesTaxes_charges + $total_supp_invoices_exluding_loan + $total_loan); // static expenses total

$dataInfo3 = price($total_static_charges)."\n€";

/**
 * Variable charges
 */
$info4 = "Charges variables";

// validated supplier invoices (excluding static charges)
$array_suppliers_invoice_paid = $object->outstandingSupplier($startFiscalYear, $endYear, 1); // paid
$array_suppliers_invoice_unpaid = $object->outstandingSupplier($startFiscalYear, $endYear, 0); // paid
$total_suppliers_invoice_paid = array_sum($array_suppliers_invoice_paid);
$total_suppliers_invoice_unpaid = array_sum($array_suppliers_invoice_unpaid);

$total_supplier_invoices = $total_suppliers_invoice_unpaid + $total_suppliers_invoice_paid;

// Various paiements
$variousPaiements = $object->fetchVariousPaiements($startFiscalYear, $endYear, $currentAccount);
$total_various_paiements = array_sum($variousPaiements);

$variables_charges = $total_supplier_invoices + $total_vat_by_month + $total_various_paiements;

// VAT
// TODO : Total VAT of current month

// Expense reports paid
$totalExpenses = $object->fetchExpenses($startFiscalYear, $endYear);

$dataInfo4 = price($variables_charges)."\n€";

$titleItem2 = "Charge totale";
$result3 = ($variables_charges + $total_static_charges);
$dataItem2 = price($result3). "\n€";

// For tresury (popupinfo)
$secondPop_info1 = $titleItem2;
$secondPop_info2 = $info3;
$secondPop_info3 = $info4;

$secondPop_data1 = "Charges fixes (".price($total_static_charges)." €) + charges variables (".price($variables_charges)." €)  sur l'exercice en cours ";
$secondPop_data2 = " <strong> Additions des dépenses fixes sur l'exercice en cours </strong>
					</br> Calcul : Salaire ( ".price($arr_salarys)." €) + charges sociales et fiscales ( ".price($socialesTaxes_charges)." €) + emprunts (<i>crédits</i> - ".price($total_loan)." €) + factures fournisseurs validées (hors emprunts : ".price($total_supp_invoices_exluding_loan)." €) ";
$secondPop_data3 = " <strong> Additions des dépenses variables fixes sur l'exercice en cours</strong> :
					</br> Calcul : Factures fournisseurs (hors brouillon) payées ( ".price($total_supplier_invoices)." €) + le montant total de TVA du mois courant (indisponible) + paiements divers ( ".price($total_various_paiements)." €) + notes de frais payés ( ".price($totalExpenses)." ) sur l'exercice en cours ";

/**
 * GRAPH 2 : TOTAL CHARGES
 */

$file = "ChargesGraph";
$fileurl = DOL_DOCUMENT_ROOT.'/custom/tab/img';
$supplier_invoice = new FactureFournisseur($db);
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

	// Variables charges

	// validated supplier invoices (excluding static charges)
	$array_suppliers_invoice_paid = $object->outstandingSupplier($date_start, $date_end, 1); // paid
	$array_suppliers_invoice_unpaid = $object->outstandingSupplier($date_start, $date_end, 0); // paid
	$total_suppliers_invoice_paid = array_sum($array_suppliers_invoice_paid);
	$total_suppliers_invoice_unpaid = array_sum($array_suppliers_invoice_unpaid);

	$total_supplier_invoices = $total_suppliers_invoice_unpaid + $total_suppliers_invoice_paid;

	// Various paiements
	$variousPaiements = $object->fetchVariousPaiements($date_start, $date_end, $currentAccount);
	$total_various_paiements = array_sum($variousPaiements);

	$variables_charges = $total_supplier_invoices + $total_vat_by_month + $total_various_paiements;

	// Static expenses
	// Salarys
	$arr_salarys = $object->fetchSalarys($date_start, $date_end, $currentAccount);

	// Sociales Taxes and charges
	$socialesTaxes_charges = $object->fetchSocialAndTaxesCharges($date_start, $date_end);

	// Validated supplier invoice (excluding loan)
	$arr_supp_invoices_exluding_loan = $object->static_charge_excluding_loan($date_start, $date_end);
	$total_supp_invoices_exluding_loan = array_sum($arr_supp_invoices_exluding_loan);

	// Loan
	$arr_loan = $object->fetchEmprunts($date_start, $date_end);
	$total_loan = array_sum($arr_loan);

	$staticExpenses = ($arr_salarys + $socialesTaxes_charges + $total_supp_invoices_exluding_loan + $total_loan); // static expenses total

	if(date('n', $date_start) == $mm){
		$staticExpenses += $supplier_invoice->total_ttc;
		$variables_charges += $supplier_invoice->total_ttc;
	}

	$data2[] = [
		$ladder = html_entity_decode($monthsArr[$mm]), // months
		$staticExpenses, // nb
		$variables_charges, // amount
	];

	if($mm >= 12){
		$mm = 0;
		$yy++;
	}
}

$px2 = new DolGraph();
$mesg = $px2->isGraphKo();
$legend = ['Charges fixes', 'Charges variables'];

if (!$mesg){
	$px2->SetTitle("Evolution du montant des charges - TTC");
	$px2->datacolor = array(array(255,123,143), array(123,123,255));
	$px2->SetData($data2);
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

print $object->load_navbar($currentPage);

include DOL_DOCUMENT_ROOT.'/custom/tab/template/template_boxes3.php';

/**
 *  CUSTOMER OUTSTANDING AT 30 DAYS
 */

$titleItem3 = "Encours clients à 30 jours";
$dataItem3 = 100;

$accounts = $object->fetchAllBankAccount();
$nbAccount = count($accounts);

// Graph
$data = [];
$file = "EvolutionAccountsChart";
$fileurl = DOL_DOCUMENT_ROOT.'/custom/tab/img';
$account = new Account($db);

// Construire le tableau de tout les comptes en bq
$array_total_account = $object->fetchAllBankAccount($date_start, $date_end);

for($i = $startMonthFiscalYear; $i <= 12; $i++){

	$lastDayMonth = cal_days_in_month(CAL_GREGORIAN, $i, $year);

	$lastyear = strtotime('Last Year');
	$lastyear = date($year-1);

	// Current Year
	$date_start = $year.'-'.$i.'-01';
	$date_end = $year.'-'.$i.'-'.$lastDayMonth;

		foreach($array_total_account as $acc){
			$idaccount = $acc->rowid;

			$solde = $object->soldeOfCurrentAccount($date_start, $date_end, $idaccount, $year);
			// $amount_treso_by_account = array_sum($solde);

			if(date('n', $date_start) == $i){
				$amount_treso_by_account += $acc->amount;
			}
		}

		$data[] = [
			html_entity_decode($monthsArr[$i]), // month
			$amount_treso_by_account,
			$amount_treso_by_account,
			$amount_treso_by_account,
		];

}

$px3 = new DolGraph();
$mesg = $px3->isGraphKo();
$legend = ['Compte 1', 'Compte 2', 'Compte 3'];

if (!$mesg){
	$px3->SetTitle("Evolution des comptes");
	$px3->datacolor = array(array(93, 173, 226), array(82, 190, 128), array(230, 126, 34));
	$px3->SetData($data);
	$px3->SetLegend($legend);
	$px3->SetType(array('lines')); // Array with type for each serie. Example: array('type1', 'type2', ...) where type can be: 'pie', 'piesemicircle', 'polar', 'lines', 'linesnopoint', 'bars', 'horizontalbars'...
	$px3->setHeight('250');
	$px3->SetWidth('500');
	$customerToDaysChart = $px3->draw($file, $fileurl);
}
$graphiqueC = $px3->show($customerToDaysChart);



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

$info8 = "Reste en banque - HT";
$totalSoldesAccount = $object->totalSoldes();
$stayBank = ($totalSoldesAccount + $customerToCash); // addition du solde des 3 comptes bq + "client a encaisser"
$dataInfo8 = price($stayBank) . "\n€";

/**
 * SUPPLIER TO PAID
 */
$info10 = "Fournisseurs à payer - HT"; // total factures F impayées et commandes fournisseurs validées
$supplier_unpaid_invoices = $object->outstandingSupplier($startFiscalyear, $endYear, 0);
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

<!-- BOX FOR OUTSTANDING 30 DAYS -->
<div class="container-fluid-1">
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
					<div class="pull-left">
						<?php
							foreach($accounts as $account){

								$acc = new Account($db);
								$acc->fetch($account->rowid);

								$solde = $acc->solde(1);

								$listAccount .= '<ul class="list-group">';
								$listAccount .= '<div">';
								$listAccount .= '<strong><i class="bi bi-bank"></i>';
								$listAccount .= '<button type="button" class="btn btn-success" style="text-align: center;">
										<a href="'.DOL_URL_ROOT.'/compta/bank/bankentries_list.php?id='.$account->rowid.'">
											<svg xmlns="http://www.w3.org/2000/svg" width="20" height="16" fill="white" class="bi bi-bank">
												<path d="m8 0 6.61 3h.89a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.5.5H15v7a.5.5 0 0 1 .485.38l.5 2a.498.498 0 0 1-.485.62H.5a.498.498 0 0 1-.485-.62l.5-2A.501.501 0 0 1 1 13V6H.5a.5.5 0 0 1-.5-.5v-2A.5.5 0 0 1 .5 3h.89L8 0ZM3.777 3h8.447L8 1 3.777 3ZM2 6v7h1V6H2Zm2 0v7h2.5V6H4Zm3.5 0v7h1V6h-1Zm2 0v7H12V6H9.5ZM13 6v7h1V6h-1Zm2-1V4H1v1h14Zm-.39 9H1.39l-.25 1h13.72l-.25-1Z"/>
											</svg></i>'.$account->label.'</a></br></strong>';
								$listAccount .= '<div class="pull-right">Solde du compte :  '.price($solde).' € </span></div></br>';
								$listAccount .= '</li></ul></br>';

							}
							print $listAccount;
							?>
					</div>
					<div class="pull-right">
						<?php print $info7 ?> : <?php print $dataInfo7 ?></h4><hr>
						<?php print $info8 ?> : <?php print $dataInfo8 ?></h4><hr>
						<?php print $info9 ?> : <?php print $dataInfo9 ?></h4><hr>
						<?php print $info10 ?> : <?php print $dataInfo10 ?></h4>
					</div>
					<div>
						<?php
							print $graphiqueC;
						?>
							</div>
						</div>
				</div>


<?php
// End of page
llxFooter();
$db->close();
