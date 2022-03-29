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

global $db;

// Load translation files required by the page
$langs->loadLangs(array("tab@tab"));
$action = GETPOST('action', 'aZ09');

// Get parameters
$socid = GETPOST('socid', 'int');
$action = GETPOST('action', 'aZ09');

// fetch current bank account
$generalActivity = new General($db);
$ret = $generalActivity->getIdBankAccount();

$month = date('m');
$year = date('Y');

// First day and last day of month on n years
$firstDayCurrentMounth = date('Y-m-d', mktime(0, 0, 0, $month, 1, $year));
$lastDayCurrentMounth = date('Y-m-t', mktime(0, 0, 0, $month, 1, $year));

// First day and last day of current years
$firstDayYear = date('Y-m-d', mktime(0, 0, 0, 1, 1, $year));
$lastDayYear = date('Y-m-t', mktime(0, 0, 1, 12, 1, $year));

// N - 1
$firstDayLastYear = date('Y-m-d', mktime(0, 0, 1, 1, 1, $year - 1));
$lastDayLastYear = date('Y-m-t', mktime(0, 0, 1, 12, 1, $year - 1));


/**
 * CA BOX
 */
$titleItem1 = "Chiffre d'affaires";
$info1 = "Chiffre d'affaire n-1";
$info2 = "Progression ";


// TURNOVER
$sql = "SELECT SUM(total) as total";
$sql .= " FROM " . MAIN_DB_PREFIX . "facture";
$sql .= " WHERE datef BETWEEN '" . $firstDayYear . "' AND '" . $lastDayYear . "' AND fk_statut != 0 ";

$resql = $db->query($sql);

if ($resql) {
	if ($db->num_rows($resql)) {
		$obj = $db->fetch_object($resql);
		$turnover = $obj->total;
	}
	$db->free($resql);
}

$dataItem1 = price($turnover); // GLOBAL TURNOVER


// request for the last turnover (n-1)
$sql = "SELECT SUM(total) as total";
$sql .= " FROM " . MAIN_DB_PREFIX . "facture";
$sql .= " WHERE datef BETWEEN '" . $firstDayLastYear . "' AND '" . $lastDayLastYear . "' AND fk_statut != 0 ";
$resql = $db->query($sql);

if ($resql) {
	if ($db->num_rows($resql)) {
		$obj = $db->fetch_object($resql);
		$dataInfo1 = price($obj->total);
	}
	$db->free($resql);
}

$calculForProgress = (($dataItem1 - $dataInfo1) / $dataInfo1 * 100);
$dataInfo2 = intval($calculForProgress); // turnover progress


// OUTSTANDING CUSTOMER AND SUPPLIER
$titleItem2 = "Encours C/F";
$info3 = "Encours clients par mois";
$info4 = "Encours fournisseur par mois";


//  Unpaid customer invoices on current year
$sql = "SELECT SUM(total) as total";
$sql .= " FROM " . MAIN_DB_PREFIX . "facture";
$sql .= " WHERE datef BETWEEN '" . $firstDayYear . "' AND '" . $lastDayYear . "' AND paye=0 AND fk_statut != 0 ";
$resql = $db->query($sql);

$resql = $db->query($sql);
if ($resql) {
	if ($db->num_rows($resql)) {
		$obj = $db->fetch_object($resql);
		$totalCustomer = $obj->total;
	}
	$db->free($resql);
}

//  Unpaid suppliers invoices on current year
$sql = "SELECT SUM(total_ht) as total_ht";
$sql .= " FROM " . MAIN_DB_PREFIX . "facture_fourn";
$sql .= " WHERE datef BETWEEN '" . $firstDayYear . "' AND '" . $lastDayYear . "' AND paye=0 AND fk_statut != 0 ";
$resql = $db->query($sql);

$resql = $db->query($sql);
if ($resql) {
	if ($db->num_rows($resql)) {
		$obj = $db->fetch_object($resql);
		$totalSupplier = $obj->total_ht;
	}
	$db->free($resql);
}
// Total C/F outstanding
$dataItem2 = price($totalCustomer + $totalSupplier);


// Unpaid customers invoices on current mounth
$sql = "SELECT SUM(total) as total";
$sql .= " FROM " . MAIN_DB_PREFIX . "facture";
$sql .= " WHERE datef BETWEEN '" . $firstDayCurrentMounth . "' AND '" . $lastDayCurrentMounth . "' AND paye=0 AND fk_statut != 0 ";
$resql = $db->query($sql);

$resql = $db->query($sql);
if ($resql) {
	if ($db->num_rows($resql)) {
		$obj = $db->fetch_object($resql);
		$totalCustomerByMounth = $obj->total;
	}
	$db->free($resql);
}
$dataInfo3 = price($totalCustomerByMounth);

// Unpaid suppliers invoices on current mounth
$sql = "SELECT SUM(total_ht) as total_ht";
$sql .= " FROM " . MAIN_DB_PREFIX . "facture_fourn";
$sql .= " WHERE datef BETWEEN '" . $firstDayCurrentMounth . "' AND '" . $lastDayCurrentMounth . "' AND paye=0 AND fk_statut != 0 ";
$resql = $db->query($sql);

$resql = $db->query($sql);
if ($resql) {
	if ($db->num_rows($resql)) {
		$obj = $db->fetch_object($resql);
		$totalSupplierByMounth = $obj->total_ht;
	}
	$db->free($resql);
}
$dataInfo4 = price($totalSupplierByMounth);

// MARGIN BOXE

$titleItem3 = "Marge brute N";
$info5 = "Marges restant à produire";
$info6 = "Marge brut prévisionnelle";

// request for gross margin on N years
$sql = "SELECT SUM(total_ht) as total_ht";
$sql .= " FROM " . MAIN_DB_PREFIX . "propal";
$sql .= " WHERE datep BETWEEN '2022-01-01' AND '2022-01-31' AND fk_statut = 2";
$resql = $db->query($sql);

$resql = $db->query($sql);
if ($resql) {
	if ($db->num_rows($resql)) {
		$obj = $db->fetch_object($resql);
		$marginGross = $obj->total_ht;
	}
	$db->free($resql);
}
$dataItem3 = price($marginGross);

// Margin To produce on current mounth : return gross margin of all validated customer orders on n year

$sql = "SELECT SUM(total_ht) as total_ht";
$sql .= " FROM " . MAIN_DB_PREFIX . "commande";
$sql .= " WHERE date_commande BETWEEN  '" . $firstDayYear . "' AND '" . $lastDayYear . "' AND fk_statut = 1";
$resql = $db->query($sql);

$resql = $db->query($sql);
if ($resql) {
	if ($db->num_rows($resql)) {
		$obj = $db->fetch_object($resql);
		$salePrice = $obj->total_ht; // Prix de vente
	}
	$db->free($resql);
}
// Sale price
$salePrice = price($salePrice);




/**
 * COST PRICE :
 * il faut aller chercher dans les lignes de la commande
 * -> la somme total du PU.HT pour recuperer le prix de revient
 */


//   $marginGross = $salePrice - $costPrice;

//    $sql = "SELECT SUM(total_ht) as total_ht";
//    $sql .= " FROM ".MAIN_DB_PREFIX."propal";
//    $sql .= " WHERE datep BETWEEN '2022-03-01' AND '2022-03-31' AND fk_statut = 2";
//    $resql = $db->query($sql);

// 	$resql = $db->query($sql);
// 	if ($resql)
// 	{
// 		if ($db->num_rows($resql))
// 		{
// 			$obj = $db->fetch_object($resql);
// 			$marginToProduce = $obj->total_ht;
// 		}
// 			$db->free($resql);

// 	}
// 	if($conf->margin->enabled){
// 		$dataInfo5 = price($marginToProduce - $marginConfiguration);
// 	}



// TREASURY BOX

$titleItem4 = "Trésorerie nette";
$info7 = "Charges mensuelles";
$info8 = "Recurrent mensuel";

$sql = "SELECT SUM(amount) as amount";
$sql .= " FROM " . MAIN_DB_PREFIX . "bank";
$sql .= " WHERE fk_account = " . $ret->rowid;
$resql = $db->query($sql);

$resql = $db->query($sql);
if ($resql) {
	if ($db->num_rows($resql)) {
		$obj = $db->fetch_object($resql);
		$solde = $obj->amount;
	}
	$db->free($resql);
}

$dataItem4 = price($solde);

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


// color for Info
if ($dataInfo2 < 0 || $dataInfo6 < 0 || $dataInfo8 < 0) {

	?>
		<style>
			p#progress {
				color: #E55934;
				font-weight: bolder;
			}
		</style>
	<?php
	} else {
	?>
		<style>
			p#progress {
				color: #90C274;
			}
		</style>
	<?php
	}


// Template NavBar
llxHeader('', $langs->trans("Global - Général"));

print load_fiche_titre($langs->trans("Général"));

// Chargement du template de navigation pour l'activité "Global"
print $generalActivity->load_navbar();

include DOL_DOCUMENT_ROOT . '/custom/tab/template/template_boxes4.php';


// End of page
llxFooter();
$db->close();
