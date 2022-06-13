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

global $db, $conf, $user;

// Security check
if (empty($conf->tab->enabled)) accessforbidden('Module not enabled');
$socid = 0;
if ($user->socid > 0) { // Protection if external user
	accessforbidden();
}

// number page for pagination
if(isset($_GET['page']) && !empty($_GET['page'])){
    $currentPage = (int) strip_tags($_GET['page']);
}else{
    $currentPage = 1;
}

// Load translation files required by the page
$langs->loadLangs(array("tab@tab"));
$action = GETPOST('action', 'aZ09');
$month = date('m');
$year = date('Y');
$day = date('Y-m-d');
$monthsArr = monthArray($langs, 1); // months



$object = new General($db);

/**
 * DEFINE TIME FOR REQUEST
 */

// First day and last day of month on n years
$firstDayCurrentMonth = date('Y-m-d', mktime(0, 0, 0, $month, 1, $year));
$lastDayCurrentMonth = date('Y-m-t', mktime(0, 0, 0, $month, 1, $year));

// First day and last day of current years
$firstDayYear = date('Y-m-d', mktime(0, 0, 0, 1, 1, $year));
$lastDayYear = date('Y-m-t', mktime(0, 0, 1, 12, 1, $year));

// N - 1
$firstDayLastYear = date('Y-m-d', mktime(0, 0, 1, 1, 1, $year - 1));
$lastDayLastYear = date('Y-m-t', mktime(0, 0, 1, 12, 1, $year - 1));

// M - 1
$firstDayLastMonth = date('Y-m-d', mktime(0, 0, 1, $month - 1, 1, $year));
$lastDayLastMonth = date('Y-m-t', mktime(0, 0, 1, $month - 1, 1, $year));

$startyear = $year - (empty($conf->global->MAIN_STATS_GRAPHS_SHOW_N_YEARS) ? 2 : max(1, min(10, $conf->global->MAIN_STATS_GRAPHS_SHOW_N_YEARS)));
$endyear = $year;

/**
 * CUSTOMER OUTSTANDING
 */

$titleItem1 = "Encours clients";
$outstandingBillOnYear = $object->outstandingBill($firstDayYear, $lastDayYear);
$dataItem1 = price($outstandingBillOnYear)."\n€";

// Encours C sur le mois dernier
$info1 = "Encours du mois dernier";
$accOfPastYears = $object->outstandingBill($firstDayLastMonth, $lastDayLastMonth);
$dataInfo1 = price($accOfPastYears)."\n€";

// nombre d'encours sur le mois courant et sur M-1 (pour calcul progression)
$outstandingCurrentMonth = $object->nbCustomerOutstanding($firstDayCurrentMonth, $lastDayCurrentMonth);
$nbUnpaidInvoices = count($outstandingCurrentMonth);

// TODO : progression du NOMBRE (et non du montant) d'encours client par rapport au mois dernier
$info2 = "Progression ";

$resultat = $object->progress($nbInvoices, $nbInvoices2);
$dataInfo2 = intval($resultat)."\n%";

// Condition d'affichage pour la progression
if($dataInfo2 > 0){
	$dataInfo2 = '<p style=color:red>+'.$dataInfo2;
} else {
	$dataInfo2 = '<p style=color:green>'.$dataInfo2;
}

// Load info for otstanding customer popupinfo
$firstPop_info1 = $titleItem1;
$firstPop_info2 = $info1;
$firstPop_info3 = $info2;

$firstPop_data1 = "Total des factures clients impayées sur l'année en cours (HT)";
$firstPop_data2 = "Total des factures clients impayées sur le mois en cours";
$firstPop_data3 = "Progression du nombre total d'encours clients pa rapport au mois dernier";

// GRAPH

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
		$px2->SetTitle("Evolution du nb d'encours client");
		$px2->SetShading(3);
		$nbInvoiceByMonth = $px2->draw($filenamenb, $fileurlnb);
	}
	$graphiqueA = $px2->show($nbInvoiceByMonth);

}


/**
 *  SUPPLIERS OUTSTANDING
 */

$titleItem2 = "Encours fournisseurs";
$outstandingSupplierOnYear = $object->outstandingSupplierOnYear($firstDayYear, $lastDayYear);
$dataItem2 = price($outstandingSupplierOnYear) . "\n€";

$info3 = "Encours fournisseur du mois dernier";
$outstandingSupplierOnLastMonth = $object->outstandingSupplierOnLastMonth($firstDayLastMonth, $lastDayLastMonth);
$dataInfo3 = price($outstandingSupplierOnLastMonth)."\n€";

$info4 = "Progression";
// $outSupplierCurrentMonth = $object->outstandingSupplierOnCurrentMonth($firstDayCurrentMonth, $lastDayCurrentMonth);

$resultat = $object->progress($outSupplierCurrentMonth, $outstandingSupplierOnLastMonth);
$dataInfo4 = $resultat."\n%";

// Condition d'affichage pour l'augmentation/diminution des encours fournisseurs
if ($dataInfo3 <= 0) {
	$dataInfo3 = "<p style='color : #90C274'>Aucun encours fournisseur </p>";
}

// Load info for otstanding customer popupinfo
$secondPop_info1 = $titleItem2;
$secondPop_info2 = $info3;
$secondPop_info3 = $info4;

$secondPop_data1 = "Total des factures fournisseurs impayées sur l'année en cours (HT)";
$secondPop_data2 = "Total des factures fournisseurs impayées sur le mois en cours";
$secondPop_data3 = "Progression du nombre total d'encours fournisseurs pa rapport au mois dernier";

/**
 * CUSTOMER AND SUPPLIERS OUTSATNDING
 */
$titleItem3 = "Encours C/F";
$dataItem3 = price($outstandingBillOnYear - $outstandingSupplierOnYear)."\n€"; // soustraction des encours client et encours fournisseur

$info5 = "Encours total le mois dernier" ;
$dataInfo5 = floatval($accOfPastYears - $outstandingSupplierOnLastMonth) . "\n€"; // encours client m-1 - encours fourn m-1

$info6 = "Progression";
// $outCFCurrentMonth = ($outstandingCurrentMonth - $outstandingSupplierOnLastMonth);

$resultat = $object->progress($outCFCurrentMonth, $dataInfo5);
$dataInfo6= $resultat."\n%";

// Load info for outstanding C/F popupinfo
$thirdPop_info1 = $titleItem3;
$thirdPop_info2 = $info5;
$thirdPop_info3 = $info6;

$thirdPop_data1 = "Total des factures clients impayées (TTC) - total des factures fournisseurs impayées (TTC) sur l'année en cours ";
$thirdPop_data2 = "Total des factures clients impayées (TTC) - total des factures fournisseurs impayées (TTC) sur le mois précédent";
$thirdPop_data3 = "Evolution du montant total (TTC) des encours C/F du mois par rapport au mois précédent ";

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
$currentPage = $_SERVER['PHP_SELF'];
print $object->load_navbar($currentPage);

// template for boxes
include DOL_DOCUMENT_ROOT . '/custom/tab/template/template_boxes2.php';


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
								<li><strong><?php print $thirdPop_info1 ?></strong><br><?php print $thirdPop_data1 ?></li><hr>
								<li><strong><?php print $thirdPop_info2 ?></strong><br><?php print $thirdPop_data2 ?> </li><hr>
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
   		 						<div class="pull-left"><?php print $info5 ?> : <h4 class="center"><?php print $dataInfo5 ?></h4></div>
								<div class="pull-right"><?php print $info6 ?> : <h4 class="center"><?php print $dataInfo6 ?></h4></div>
							</div>
							</div>
						</div>
							<?php print $graphiqueA ?>
					</div>
				</div>
<?php

// Supplier outstandings exceeded
$titleItem5 = "Encours fournisseurs dépassés";
$outSupplierExceeded = $object->fetchSupplierBillExceed();
$dataItem5 = '<i class="fas fa-exclamation-triangle"></i>'."\n".price($outSupplierExceeded) . "\n€";

// Load info for supplier exceed popupinfo
$fivePop_info1 = $titleItem5;
$fivePop_data1 = "Somme des factures fournisseurs impayées (TTC) dont la date d'échéance a été dépassée";

// Customer outstandings exceeded
$titleItem4 = "Encours clients dépassés";
$outCustomerExceeded = $object->fetchCustomerBillExceed();
$dataItem4 = '<i class="fas fa-exclamation-triangle"></i>'."\n".price($outCustomerExceeded) . "\n€";

// Load info for customer exceed popupinfo
$fourPop_info1 = $titleItem4;
$fourPop_data1 = "Somme des factures clients impayées (TTC) dont la date d'échéance a été dépassée";


?>

<!-- end Outstanding suppliers exceed -->
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
		</h3><hr>
		</br>
		<h4 class="text-center">
			<?php print $dataItem4 ?>
		</h4>
    </div>

  </div>
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
		</h3><hr>
		</br>
		<h4 class="text-center">
			<?php print $dataItem5 ?>
		</h4>
    </div>
</div>


<?php

// End of page
llxFooter();
$db->close();
