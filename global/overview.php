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
require_once DOL_DOCUMENT_ROOT . '/core/class/dolgraph.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/stats.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/bank.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/date.lib.php';
require_once DOL_DOCUMENT_ROOT.'/comm/propal/class/propalestats.class.php';

global $db, $conf;

$WIDTH = DolGraph::getDefaultGraphSizeForStats('width');
$HEIGHT = DolGraph::getDefaultGraphSizeForStats('height');

// fetch current bank account
$object = new General($db);
$ret = $object->getIdBankAccount();

$month = date('m');
$year = date('Y');

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
$turnover = $object->fetchTurnoverOnYear($firstDayYear, $lastDayYear);
$dataItem1 = price($turnover)."\n€";

$info1 = "Chiffre d'affaire n-1";
$turnoverLastYear = $object->fetchTurnoverOnLastYear($firstDayLastYear, $lastDayLastYear);
$dataInfo1 = price($turnoverLastYear)."\n€";

$info2 = "Progression ";

$result = $object->progress($turnover, $turnoverLastYear);
$dataInfo2 = $result  ."\n%";

if($dataInfo2 < 0){
	$dataInfo2 = '<p style=color:red>'.$dataInfo2.'</p>';
} else {
	$dataInfo2 = '<p style=color:green>'.$dataInfo2.'</p>';
}

/**
 * OUTSTANDING CUSTOMER AND SUPPLIER
 */

$titleItem2 = "Encours C/F";
$customerOutstandingYear = $object->outstandingBillOnYear($firstDayYear, $lastDayYear);
$supplierOutstandingYear = $object->outstandingSupplierOnYear($firstDayYear, $lastDayYear);

$result = $customerOutstandingYear - $supplierOutstandingYear;

$dataItem2 = price($result)  ."\n€";

$info3 = "Encours C / mois ";
$customerOutstandingMonth = $object->outstandingCustomerOnCurrentMonth($firstDayCurrentMonth, $lastDayCurrentMonth);
$dataInfo3 = price($customerOutstandingMonth) . "\n€";

$info4 = "Encours F / mois";
$supplierOutstandingMonth = $object->outstandingSupplierOnCurrentMonth($firstDayCurrentMonth, $lastDayCurrentMonth);
$dataInfo4 = price($supplierOutstandingMonth) . "\n€";




/**
 *  MARGIN BOXE
 */
$titleItem3 = "Marge brute N";
$grossMargin = $object->grossMarginOnYear($firstDayYear, $lastDayYear);
$dataItem3 = price($grossMargin) . "\n€";

// Margin To produce on current mounth
// Todo : montant total des commandes validées sur le mois courant - marge definit dans le module (anthony)
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




/**
 * TREASURY BOX
 */
$titleItem4 = "Trésorerie nette";
$solde = $object->fetchSoldeOnYear();
$dataItem4 = price($solde) . "\n€";

$info7 = "Charges mensuelles";

$info8 = "Recurrent mensuel";


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
