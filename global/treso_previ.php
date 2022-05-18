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

// Load translation files required by the page
$langs->loadLangs(array("tab@tab"));
$action = GETPOST('action', 'aZ09');
$month = date('m');
$year = date('Y');
$day = date('Y-m-d');
$object = new General($db);


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
 * TRESURY
 */
$titleItem1 = "Trésorerie";
$result = $object->fetchSoldeOnYear();
$dataItem1 = price($result) ."\n€";

$info1 = "Trésorerie M-1";
$soldeOnLastMonth = $object->fetchSoldeOnLastMonth($firstDayLastMonth, $lastDayLastMonth);
$dataInfo1 = $soldeOnLastMonth."\n€";

$info2 = "Progression";



$info3 = "Charges fixes";
$staticExpenses = $object->fetchStaticExpenses($firstDayYear, $lastDayYear);
$dataInfo3 = price($staticExpenses) . "\n€";


$info4 = "Charges variables";
$variablesExpenses = $object->fetchVariablesExpenses($firstDayYear, $lastDayYear);
$dataInfo4 = price($variablesExpenses) . "\n€";


$titleItem2 = "Charge totale du mois";
$result3 = ($variablesExpenses + $staticExpenses);
$dataItem2 = price($result3). "\n€";

$titleItem3 = "Encours clients à 30 jours";
$info5 = "Clients à encaisser";
$info6 = "Fournisseurs à payer";
$info7 = "Reste en banque";
$info8 = "Solde des comptes";


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

// Chargement du template de navigation pour l'activité "Global"
print $object->load_navbar();

include DOL_DOCUMENT_ROOT.'/custom/tab/template/template_boxes2.php';

$dataItem3 = 100;
$info7 = "reste en banque";
$info8 = "solde des comptes";

$dataInfo7 = 100;
$dataInfo8 = 100;
?>
<!--
<div class=".table-responsive">
<div class="container-fluid-2">
	<div class="card bg-c-blue order-card">
		<div class="card-body">
			<h4 class="text-center">
				<?php print $titleItem3 ?>
			</h4>

			<div class="row text-center pb-md-4 justify-content-sm-center ">
            <div class="col-12  col-md-4 m-auto">
			<i class="bi bi-bank">tpoto</i>
              <h5 class="h5 mt-2 mb-3">Fully Responsive</h5>
            </div>
            <div class="col-12  col-md-4 m-auto">
			<i class="bi bi-bank"></i>
              <h5 class="h5 mt-2 mb-3">Bootstrap 4 Ready</h5>

            </div>
            <div class="col-12  col-md-4 m-auto">
              <h5 class="h5 mt-2 mb-3">Easy to Use</h5>
			  <i class="bi bi-bank"></i>
            </div>
          </div>

			<div class="col-lg-15">
				<div class="center-block">
					<div class="pull-left"><?php print $info7 ?> : <h4 class="center"><?php print $dataInfo7 ?></h4></div>
					<div class="pull-right"><?php print $info8 ?> : <h4 class="center"><?php print $dataInfo8 ?></h4></div>
				</div>
			</div>
		</div>
		<a href="#" class="btn btn-primary">GRAPHIQUE</a>
	</div>
</div> -->


<?php
// End of page
llxFooter();
$db->close();
