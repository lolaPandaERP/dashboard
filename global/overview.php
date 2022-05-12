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
require_once DOL_DOCUMENT_ROOT . '/core/lib/bank.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/date.lib.php';

global $db, $conf;
// Load translation files required by the page
$langs->loadLangs(array("tab@tab"));
$action = GETPOST('action', 'aZ09');
$socid = GETPOST('socid', 'int');
$action = GETPOST('action', 'aZ09');

// fetch current bank account
$generalActivity = new General($db);
$ret = $generalActivity->getIdBankAccount();

$month = date('m');
$year = date('Y');

// First day and last day of current mounth
$firstDayCurrentMounth = date('Y-m-d', mktime(0, 0, 0, $month, 1, $year));
$lastDayCurrentMounth = date('Y-m-t', mktime(0, 0, 0, $month, 1, $year));

// M - 1
$firstDayLastMonth = date('Y-m-d', mktime(0, 0, 1, $month - 1, 1, $year));
$lastDayLastMonth = date('Y-m-t', mktime(0, 0, 1, $month - 1, 1, $year));

// First day and last day of current years
$firstDayYear = date('Y-m-d', mktime(0, 0, 0, 1, 1, $year));
$lastDayYear = date('Y-m-t', mktime(0, 0, 1, 12, 1, $year));

// N - 1
$firstDayLastYear = date('Y-m-d', mktime(0, 0, 1, 1, 1, $year - 1));
$lastDayLastYear = date('Y-m-t', mktime(0, 0, 1, 12, 1, $year - 1));



/**
 * TURNOVER
 */
$titleItem1 = "Chiffre d'affaires";
$info1 = "Chiffre d'affaire n-1";
$info2 = "Progression ";

// Chiffre d'affaires de l'annee en cours
$sql = "SELECT SUM(total_ht) as total_ht";
$sql .= " FROM " . MAIN_DB_PREFIX . "facture";
$sql .= " WHERE datef BETWEEN '" . $firstDayYear . "' AND '" . $lastDayYear . "' ";
$sql .= " AND fk_statut != 0";

$resql = $db->query($sql);

if ($resql) {
	if ($db->num_rows($resql)) {
		$obj = $db->fetch_object($resql);
		$turnover = $obj->total_ht;
	}
	$db->free($resql);
}

$dataItem1 = price($turnover). " € "; // GLOBAL TURNOVER

// chiffre d'affaire de l'annee precedente
$sql = "SELECT SUM(total_ht) as total_ht";
$sql .= " FROM " . MAIN_DB_PREFIX . "facture";
$sql .= " WHERE datef BETWEEN '" . $firstDayLastYear . "' AND '" . $lastDayLastYear . "' ";
$sql .= " AND fk_statut !=0 ";
$resql = $db->query($sql);

if ($resql) {
	if ($db->num_rows($resql)) {
		$obj = $db->fetch_object($resql);
		$turnoverLastYear = $obj->total_ht;
	}
	$db->free($resql);
}

$dataInfo1 = price($turnoverLastYear);

$dataInfo2 = intval((($turnover - $turnoverLastYear) / $turnoverLastYear) * 100)."\n%";

if($dataInfo2 < 0){
	$dataInfo2 = '<p style=color:red>'.$dataInfo2.'</p>';
} else {
	$dataInfo2 = '<p style=color:green>'.$dataInfo2.'</p>';
}

// OUTSTANDING CUSTOMER AND SUPPLIER
$titleItem2 = "Encours C/F";
$info3 = "Encours C / mois ";
$info4 = "Encours F / mois";

//  YEARS
$sql = "SELECT SUM(total_ht) as total_ht";
$sql .= " FROM " . MAIN_DB_PREFIX . "facture";
$sql .= " WHERE datef BETWEEN '" . $firstDayYear . "' AND '" . $lastDayYear . "'";
$sql .= " AND paye=0 ";
$sql .= " AND fk_statut != 0 ";
$resql = $db->query($sql);

if ($resql) {
	if ($db->num_rows($resql)) {
		$obj = $db->fetch_object($resql);
		$totalCustomer = $obj->total_ht;
	}
	$db->free($resql);
}
$totalCustomer = intval($totalCustomer);


$sql = "SELECT SUM(total_ht) as total_ht";
$sql .= " FROM " . MAIN_DB_PREFIX . "facture";
$sql .= " WHERE datef BETWEEN '" . $firstDayCurrentMounth . "' AND '" . $lastDayCurrentMounth . "' ";
$sql .= " AND paye=0 ";
$sql .= " AND fk_statut != 0 ";
$resql = $db->query($sql);

if ($resql) {
	if ($db->num_rows($resql)) {
		$obj = $db->fetch_object($resql);
		$dataInfo3 = $obj->total_ht;
	}
	$db->free($resql);
}

if ($dataInfo3 <= 0) {
	$dataInfo3 = "<h5 style='color : #90C274'>Aucun encours client pour " . htmlspecialchars($generalActivity->ReturnMonth($month)) . "</h5>";
} else {
	$dataInfo3 = price($dataInfo3) ." €";
}

$sql = "SELECT SUM(total_ht) as total_ht";
$sql .= " FROM " . MAIN_DB_PREFIX . "facture_fourn";
$sql .= " WHERE datef BETWEEN '" . $firstDayYear . "' AND '" . $lastDayYear . "'  ";
$sql .= " AND paye = 0 ";
$sql .= " AND fk_statut != 0 ";
$resql = $db->query($sql);

if ($resql) {
	if ($db->num_rows($resql)) {
		$obj = $db->fetch_object($resql);
		$totalSupplier = $obj->total_ht;
	}
	$db->free($resql);
}
$totalSupplier = intval($totalSupplier);

// Unpaid suppliers invoices on current mounth
$sql = "SELECT SUM(total_ht) as total_ht";
$sql .= " FROM " . MAIN_DB_PREFIX . "facture_fourn";
$sql .= " WHERE datef BETWEEN '" . $firstDayCurrentMounth . "' AND '" . $lastDayCurrentMounth . "' ";
$sql .= " AND paye=0 ";
$sql .= " AND fk_statut != 0 ";
$resql = $db->query($sql);

if ($resql) {
	if ($db->num_rows($resql)) {
		$obj = $db->fetch_object($resql);
		$dataInfo4 = $obj->total_ht;
	}
	$db->free($resql);
}

if ($dataInfo4 <= 0) {
	$dataInfo4 = "<h5 style='color : #90C274'>Aucun encours fournisseurs pour " . htmlspecialchars($generalActivity->ReturnMonth($month)) . "</h5>";
} else {
	$dataInfo4 = price($dataInfo4) ." €";
}

// TOTAL CF
$dataItem2 = price($totalCustomer - $totalSupplier) . "\n€";



// MARGIN BOXE
$titleItem3 = "Marge brute N";

// request for gross margin on N years
$sql = "SELECT SUM(total_ht) as total_ht";
$sql .= " FROM " . MAIN_DB_PREFIX . "commande";
$sql .= " WHERE date_commande BETWEEN '" . $firstDayYear . "' AND '" . $lastDayYear . "' ";
$sql .= " AND fk_statut =1";
$resql = $db->query($sql);

if ($resql) {
	if ($db->num_rows($resql)) {
		$obj = $db->fetch_object($resql);
		$marginGross = $obj->total_ht;
	}
	$db->free($resql);
}
$dataItem3 = price($marginGross) ."\n€";

// Margin To produce on current mounth
// Todo : montant total des commandes validées sur le mois courant - marge definit dans le module (anthony)
$info5 = "Marges restant à produire";

// request for gross margin on current mounth for calculate the margin to be produced
$sql = "SELECT SUM(total_ht) as total_ht";
$sql .= " FROM " . MAIN_DB_PREFIX . "commande";
$sql .= " WHERE date_commande BETWEEN '" . $firstDayCurrentMounth . "' AND '" . $lastDayCurrentMounth . "' ";
$sql .= " AND fk_statut =1";
$resql = $db->query($sql);

if ($resql) {
	if ($db->num_rows($resql)) {
		$obj = $db->fetch_object($resql);
		$marginByCurrentMounth = $obj->total_ht;
	}
	$db->free($resql);
}

// saisie manuelle
$marginToBeProduced = $conf->global->MARGIN_PRODUCED;
// conversion
$marginToBeProduced = (int)$marginToBeProduced;

// calcul for remaining margin to produce
$dataInfo5 = price($marginByCurrentMounth - $marginToBeProduced)."\n€";

$info6 = "Marge brut prévisionnelle";
$forecastMargin = $conf->global->FORECAST_MARGIN;
$forecastMargin = (int)$forecastMargin;
$dataInfo6 = $forecastMargin."\n€";


// TREASURY BOX
$titleItem4 = "Trésorerie nette";
$info7 = "Charges mensuelles";

$sql = "SELECT SUM(amount) as amount";
$sql .= " FROM " . MAIN_DB_PREFIX . "bank";
$sql .= " WHERE fk_account = " . $ret->rowid;
$resql = $db->query($sql);

if ($resql) {
	if ($db->num_rows($resql)) {
		$obj = $db->fetch_object($resql);
		$solde = $obj->amount;
	}
	$db->free($resql);
}

$dataItem4 = price($solde) . "\n€";

// Monthly Charge
$sql = "SELECT SUM(total_ht) as total_ht";
$sql .= " FROM " . MAIN_DB_PREFIX . "facture_fourn";
$sql .= " WHERE datef BETWEEN '" . $firstDayCurrentMounth . "' AND '" . $lastDayCurrentMounth . "' ";
$resql = $db->query($sql);

if ($resql) {
	if ($db->num_rows($resql)) {
		$obj = $db->fetch_object($resql);
		$monthlyCharges = $obj->total_ht;
	}
	$db->free($resql);
}

$info8 = "Recurrent mensuel";

if ($dataInfo7 <= 0) {
$dataInfo7 = "A CONFIRME";
	// $dataInfo7 = "<p style='color : #90C274'>Aucune factures fournisseur pour " . htmlspecialchars($generalActivity->ReturnMonth($month)) . " ";
}

/*
 * Actions
 */

// None


/*
 * View
 */

$form = new Form($db);
$formfile = new FormFile($db);
$generalActivity = new General($db);

// Template NavBar
llxHeader('', $langs->trans("Global - Général"));

print load_fiche_titre($langs->trans("Général"));

// Chargement du template de navigation pour l'activité "Global"
print $generalActivity->load_navbar();

include DOL_DOCUMENT_ROOT . '/custom/tab/template/template_boxes4.php';


// End of page
llxFooter();
$db->close();
