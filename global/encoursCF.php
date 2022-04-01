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


/* BEGIN CUSTOMER */

$titleItem1 = "Encours clients";

//  Unpaid customer invoices on current year
$sql = "SELECT SUM(total_ht) as total_ht";
$sql .= " FROM " . MAIN_DB_PREFIX . "facture";
$sql .= " WHERE datef BETWEEN '" . $firstDayYear . "' AND '" . $lastDayYear . "' ";
$sql .= " AND paye= 0";
$sql .= " AND fk_statut != 0 ";

$resql = $db->query($sql);

if ($resql) {
	if ($db->num_rows($resql)) {
		$obj = $db->fetch_object($resql);
		$dataItem1 = price($obj->total_ht);
	}
	$db->free($resql);
}


$info1 = "Encours du mois dernier";

// Encours M : for calculate progress between M and M-1
$sql = "SELECT SUM(total_ttc) as total_ttc";
$sql .= " FROM " . MAIN_DB_PREFIX . "facture";
$sql .= " WHERE datef BETWEEN '" . $firstDayCurrentMonth . "' AND '" . $firstDayCurrentMonth . "' ";
$sql .= " AND paye=0 ";
$sql .= " AND fk_statut != 0 ";

$resql = $db->query($sql);

if ($resql) {
	if ($db->num_rows($resql)) {
		$obj = $db->fetch_object($resql);
		$outstandingCurrentMonth = $obj->total_ttc;
	}
	$db->free($resql);
}

// Encours M - 1
$sql = "SELECT SUM(total_ttc) as total_ttc";
$sql .= " FROM " . MAIN_DB_PREFIX . "facture";
$sql .= " WHERE datef BETWEEN '" . $firstDayLastMonth . "' AND '" . $lastDayLastMonth . "' ";
$sql .= " AND paye=0 ";
$sql .= " AND fk_statut != 0 ";

$resql = $db->query($sql);

if ($resql) {
	if ($db->num_rows($resql)) {
		$obj = $db->fetch_object($resql);
		$dataInfo1 = $obj->total_ttc;
	}
	$db->free($resql);
}

$info2 = "Progression ";

if ($dataInfo1 <= 0) {
	$dataInfo1 = "<p style='color : #90C274'>Aucun encours client pour " . htmlspecialchars($general->ReturnMonth($month - 1)) . " </p>";
} else {
	$dataInfo1 = abs($dataInfo1) . "€";
	$dataInfo2 = (($outstandingCurrentMonth - $dataInfo1) / $dataInfo1) * 100 . "%";
}

// /* END CUSTOMER */


$titleItem2 = "Encours fournisseurs";

// Unpaid supplier invoices on current year
$sql = "SELECT SUM(total_ht) as total_ht";
$sql .= " FROM " . MAIN_DB_PREFIX . "facture_fourn";
$sql .= " WHERE datef BETWEEN '" . $firstDayYear . "' AND '" . $lastDayYear . "' ";
$sql .= " AND paye=0 ";
$sql .= " AND fk_statut != 0 ";

$resql = $db->query($sql);

if ($resql) {
	if ($db->num_rows($resql)) {
		$obj = $db->fetch_object($resql);
		$dataItem2 = price($obj->total_ht);
	}
	$db->free($resql);
}

// Encours M supplier : for calculate progress between M and M-1
$sql = "SELECT SUM(total_ht) as total_ht";
$sql .= " FROM " . MAIN_DB_PREFIX . "facture_fourn";
$sql .= " WHERE datef BETWEEN '" . $firstDayCurrentMonth . "' AND '" . $lastDayCurrentMonth . "'";
$sql .= " AND paye=0 ";
$sql .= " AND fk_statut != 0 ";

$resql = $db->query($sql);

if ($resql) {
	if ($db->num_rows($resql)) {
		$obj = $db->fetch_object($resql);
		$outSupplierCurrentMonth = $obj->total_ht;
	}
	$db->free($resql);
}

// Encours M - 1
$info3 = "Encours du mois dernier";

$sql = "SELECT SUM(total_ht) as total_ht";
$sql .= " FROM " . MAIN_DB_PREFIX . "facture_fourn";
$sql .= " WHERE datef BETWEEN '" . $firstDayLastMonth . "' AND '" . $lastDayLastMonth . "'";
$sql .= " AND paye= 0 ";
$sql .= " AND fk_statut != 0 ";

$resql = $db->query($sql);

if ($resql) {
	if ($db->num_rows($resql)) {
		$obj = $db->fetch_object($resql);
		$outSupplierLastMonth = $obj->total_ht;
	}
	$db->free($resql);
}
$dataInfo3 = price($outSupplierLastMonth);

$info4 = "Progression";

if ($dataInfo3 <= 0) {
	$dataInfo3 = "</hr><p style='color : #90C274'>Aucun encours fournisseur pour " . htmlspecialchars($general->ReturnMonth($month - 1)) . " </p>";
} else {
	$dataInfo3 = price($outSupplierLastMonth);
	$dataInfo4 = (($outSupplierCurrentMonth - $outSupplierLastMonth) / $outSupplierLastMonth) * 100 . "%";
}

/*
 * Actions
 */



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

$titleItem3 = "Encours C/F";
$dataItem3 = price($totalCustomer - $dataItem2); // total ttc E.C - total TTC E.F on current years

$info5 = "Total du mois dernier";
if ($dataInfo5 <= 0) {
	$dataInfo5 = "<p style='color : #90C274'>Aucun encours C/F pour " . htmlspecialchars($general->ReturnMonth($month - 1)) . " </p>";
} else {
	$dataInfo5 = ($outstandingLastMonth - $outSupplierLastMonth); // total outstanding CF on m-1
}

$info6 = "Progression";
$totalCFCurrentMonth = ($outstandingCurrentMonth - $outSupplierCurrentMonth); // total ttc E.C - total TTC E.F current month

// $dataInfo6 = intval(($totalCFCurrentMonth - $dataInfo5) / $totalCFCurrentMonth) * 100;


// Customer outstandings exceeded
$sql = "SELECT SUM(total_ttc) as total_ttc";
$sql .= " FROM " . MAIN_DB_PREFIX . "facture";
$sql .= " WHERE datef BETWEEN '" . $firstDayYear . "' AND '" . $lastDayYear . "' ";
$sql .= "AND date_lim_reglement <= '" . $db->idate(dol_now()) . "'";
$sql .= "AND paye = 0";

$resql = $db->query($sql);

if ($resql) {
	if ($db->num_rows($resql)) {
		$obj = $db->fetch_object($resql);
		$outExceed = $obj->total_ttc;
	}
	$db->free($resql);
}

$dataItem2 = price($outExceed);

// Customer outstandings exceeded
$sql = "SELECT SUM(total_ttc) as total_ttc";
$sql .= " FROM " . MAIN_DB_PREFIX . "facture_fourn";
$sql .= " WHERE datef BETWEEN '" . $firstDayYear . "' AND '" . $lastDayYear . "' ";
$sql .= "AND date_lim_reglement <= '" . $db->idate(dol_now()) . "'";
$sql .= "AND paye = 0";

$resql = $db->query($sql);

if ($resql) {
	if ($db->num_rows($resql)) {
		$obj = $db->fetch_object($resql);
		$outSupplierExceed = $obj->total_ttc;
	}
	$db->free($resql);
}

price($outSupplierExceed);

?>
<!-- Outstanding supplier -->
<!-- bloc 1 -->
<div class="grid-1">
	<div class="card bg-c-blue order-card">
		<!-- Corps de la carte -->
		<div class="card-body">
			<div class="card-block">
				<h4 class="text-center">
					<?php print $titleItem3 ?>
					<!-- Titre de la boxe -->
				</h4>
				<h1 class="text-center">
					<span class="success">
						<?= print $dataItem3 ?> €
					</span>
				</h1>
				<hr>
				<div class="element">
					<p class="text-right"></p> <!-- Donnée par rapport à l'année ou au mois dernier -->
					<?php print $info5 ?> :
					</br><?php print $dataInfo5 ?>
					<hr class="vertical">
					<p class="text-right"></p> <!-- Donnée par rapport à l'année ou au mois dernier -->
					<?php print $info6 ?> :
					</br>
					<p class="text-right" id="progress"><?php print $dataInfo6 ?></p>
				</div>
			</div>
		</div>
		<a href="#" class="btn btn-primary">GRAPHIQUE</a>
	</div>
</div>
<!-- end bloc 1 -->

<!-- end outstanding suppliers -->

<?php

$titleItem4 = "Encours clients dépassés";
$info7 = "par rapport au mois dernier";

$titleItem5 = "Encours fournisseurs dépassés";
$info8 = "par rapport au mois dernier";


?>
<!-- Outstanding customer exceed -->
<div class="grid-3">
	<div class="card bg-c-blue order-card">
		<!-- Corps de la carte -->
		<div class="card-body">
			<div class="card-block">
				<h4 class="text-center">
					<?php print $titleItem4 ?>
					<!-- Titre de la boxe -->
				</h4>
				<h1 class="text-center">
					<span class="success">
						<?= print $dataItem4 ?> €
					</span>
				</h1>
				<hr>
				<div class="element">
					<p class="text-right"></p> <!-- Donnée par rapport à l'année ou au mois dernier -->
					<p class="text-right" id="progress"><?php print $dataInfo8 ?> %
					<?php print $info8 ?>
				</div>
			</div>
		</div>
		<a href="#" class="btn btn-primary">GRAPHIQUE</a>
	</div>
</div>
<!-- end Outstanding customer exceed -->

<!-- Outstanding suppliers exceed -->
<div class="grid-3">
	<div class="card bg-c-blue order-card">
		<!-- Corps de la carte -->
		<div class="card-body">
			<div class="card-block">
				<h4 class="text-center">
					<?php print $titleItem5 ?>
					<!-- Titre de la boxe -->
				</h4>
				<h1 class="text-center">
					<span class="success">
						<?= print $dataItem5 ?> €
					</span>
				</h1>
				<hr>
				<div class="element">
					<p class="text-right"></p> <!-- Donnée par rapport à l'année ou au mois dernier -->
					<p class="text-right" id="progress"><?php print $dataInfo8 ?> %
					<?php print $info8 ?>
				</div>
			</div>
		</div>
		<a href="#" class="btn btn-primary">GRAPHIQUE</a>
	</div>
</div>
</body>
<!-- end Outstanding suppliers exceed -->

<?php

// End of page
llxFooter();
$db->close();
