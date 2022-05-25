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


ob_start();
ob_get_clean();

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


global $db, $conf, $user;

$general = new General($db);

// Load translation files required by the page
$langs->loadLangs(array("tab@tab"));
$action = GETPOST('action', 'aZ09');

$month = date('m');
$year = date('Y');
$day = date('Y-m-d');

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


/**
 * CUSTOMER OUTSTANDING
 */
$titleItem1 = "Encours clients";
$outstandingBillOnYear = $general->outstandingBillOnYear($firstDayYear, $lastDayYear);
$dataItem1 = price($outstandingBillOnYear)."\n€";

// Encours C sur le mois dernier
$info1 = "Encours du mois dernier";
$accOfPastYears = $general->total_outstandingBillPastYear($firstDayLastMonth, $lastDayLastMonth);
$dataInfo1 = price($accOfPastYears)."\n€";

// sur le mois courant (pour calcul progression)
$outstandingCurrentMonth = $general->outstandingCustomerOnCurrentMonth($firstDayCurrentMonth, $lastDayCurrentMonth);

// progression de l'encours client par rapport au mois dernier
$info2 = "Progression ";

$resultat = $general->progress($outstandingCurrentMonth, $accOfPastYears);
$dataInfo2 = $resultat."\n%";

// Condition d'affichage pour la progression
if($dataInfo2 > 0){
	$dataInfo2 = '<p style=color:red>+'.$dataInfo2;
} else {
	$dataInfo2 = '<p style=color:green>'.$dataInfo2;
}



/**
 *  SUPPLIERS OUTSTANDING
 */

$titleItem2 = "Encours fournisseurs";
$outstandingSupplierOnYear = $general->outstandingSupplierOnYear($firstDayYear, $lastDayYear);
$dataItem2 = price($outstandingSupplierOnYear) . "\n€";

$info3 = "Encours fournisseur du mois dernier";
$outstandingSupplierOnLastMonth = $general->outstandingSupplierOnLastMonth($firstDayLastMonth, $lastDayLastMonth);
$dataInfo3 = price($outstandingSupplierOnLastMonth)."\n€";

$info4 = "Progression";
$outSupplierCurrentMonth = $general->outstandingSupplierOnCurrentMonth($firstDayCurrentMonth, $lastDayCurrentMonth);

$resultat = $general->progress($outSupplierCurrentMonth, $outstandingSupplierOnLastMonth);
$dataInfo4 = $resultat."\n%";

// Condition d'affichage pour l'augmentation/diminution des encours fournisseurs
if ($dataInfo3 <= 0) {
	$dataInfo3 = "<p style='color : #90C274'>Aucun encours fournisseur </p>";
}


/**
 * CUSTOMER AND SUPPLIERS OUTSATNDING
 */
$titleItem3 = "Encours C/F";
$dataItem3 = price($outstandingBillOnYear - $outstandingSupplierOnYear)."\n€"; // soustraction des encours client et encours fournisseur

$info5 = "Encours total le mois dernier" ;
$dataInfo5 = floatval($accOfPastYears - $outstandingSupplierOnLastMonth) . "\n€"; // encours client m-1 - encours fourn m-1

$info6 = "Progression";
$outCFCurrentMonth = ($outstandingCurrentMonth - $outstandingSupplierOnLastMonth); //

$resultat = $general->progress($outCFCurrentMonth, $dataInfo5);
$dataInfo6= $resultat."\n%";


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
print $object->load_navbar();

// template for boxes
include DOL_DOCUMENT_ROOT . '/custom/tab/template/template_boxes2.php';

// Customer outstandings exceeded
$titleItem4 = "Encours clients dépassés";
$outCustomerExceeded = $general->fetchCustomerBillExceed();
$dataItem4 = '<i class="fas fa-exclamation-triangle"></i>'."\n".price($outCustomerExceeded) . "\n€";

?>

<!-- CUSTOMER OUTSTANDING -->
			<div class="grid-1">
				<div class="card bg-c-blue order-card">
					<div class="card-body">
						<h3 class="text-center">
							<?php print $titleItem3 ?>
						</h3>
						<h2 class="text-center">
							<?php print $dataItem3 ?>
						</h2>
						<div class="col-lg-12">
  							<div class="center-block">
   		 						<div class="pull-left"><?php print $info5 ?> : <h4><?php print $dataInfo5?></h4></div><hr>
								<div class="pull-right"><?php print $info6 ?> : <h4><?php print $dataInfo6 ?></h4></div>
							</div>
							</div>
						</div>
					<a href="#" class="btn btn-primary">GRAPHIQUE</a>
				</div>
			</div>

<?php

// Supplier outstandings exceeded
$titleItem5 = "Encours fournisseurs dépassés";
$outSupplierExceeded = $general->fetchSupplierBillExceed();
$dataItem5 = '<i class="fas fa-exclamation-triangle"></i>'."\n".price($outSupplierExceeded) . "\n€";

?>

<!-- end Outstanding suppliers exceed -->
<div class="card-deck">
  <div class="card">
    <div class="card-body">
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
