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

// Load translation files required by the page
$langs->loadLangs(array("tab@tab"));

$action = GETPOST('action', 'aZ09');

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
$ac = new Account($db);

// Outstandings customer and suppliers
llxHeader('', $langs->trans("Encours Client/Fournisseur"));

print load_fiche_titre($langs->trans("Encours Client/Fournisseur"));

// Chargement du template de navigation pour l'activité "Global"
print $object->load_navbar();

// title of items from global activity
$titleItem1 = "Encours clients";
$titleItem4 = "Encours Fournisseurs";
$titleItem5 = "Encours C/F";
$titleItem2 = "Encours C dépassés";
$titleItem3 = "Encours F dépassés";

// For oustanding customer
$info1 = "Encours M-1";
$info2 = "Progression";

$info3 = "Encours clients M-1";
$info4 = "Progression";

// For customer and supplier oustanding
$info5 = "Encours clients / fournisseurs M-1";
$info6 = "Progression";

$info3 = "Banque 1 ";
$info4 = "Banque 2";
$info5 = "Banque 3";

// For overdue customer and supplier outstandings
$compareToLastMounth = "par rapport au mois dernier";

// Template inclusion for page boxes
include DOL_DOCUMENT_ROOT.'/custom/tab/template/template_boxes2.php';

?>
				<div class="grid-exceed">
					<div class="card bg-c-blue order-card">
						<!-- Corps de la carte -->
						<div class="card-body">
							<div class="card-block">
								<h4 class="text-left">
									<?php print $titleItem3 ?> <!-- Titre de la boxe -->
								</h4>
								<h1 class="text-left"><span id="info">
								<i class="bi bi-graph-up"></i>
									<?php
										print $dataItem3;  // Donnée chiffré à afficher
									?>
									</span></h1>
									<p class="text-left">Total  : <span class="f-center"><?php print $currentData1 ?> €</span></p> <!-- Total de la donnée courante -->
									<p class="text-left"><?php print $info1 ?> : <?php print $dataInfo1?>€ </p> <!-- Donnée par rapport à l'année ou au mois dernier -->
									<p class="text-left"><?php print $info2 ?><?php print $dataInfo2?> % </p> <!-- Progression -->
								 </div>
							</div>
						</div>
					</div>

					<div class="grid-exceed">
					<div class="card bg-c-blue order-card">
						<!-- Corps de la carte -->
						<div class="card-body">
							<div class="card-block">
								<h4 class="text-left">
									<?php print $titleItem4 ?> <!-- Titre de la boxe -->
								</h4>
								<h1 class="text-left"><span id="info">
								<i class="bi bi-graph-up"></i>
									<?php
										print $dataItem4;  // Donnée chiffré à afficher
									?>
									</span></h1>
									<p class="text-left">Total  : <span class="f-center"><?php print $currentData1 ?> €</span></p> <!-- Total de la donnée courante -->
									<p class="text-left"><?php print $info1 ?> : <?php print $dataInfo1?>€ </p> <!-- Donnée par rapport à l'année ou au mois dernier -->
									<p class="text-left"><?php print $info2 ?><?php print $dataInfo2?> % </p> <!-- Progression -->
								 </div>
							</div>
						</div>
					</div>

					<div class="grid-exceed">
					<div class="card bg-c-blue order-card">
						<!-- Corps de la carte -->
						<div class="card-body">
							<div class="card-block">
								<h4 class="text-left">
									<?php print $titleItem5 ?> <!-- Titre de la boxe -->
								</h4>
								<h1 class="text-left"><span id="info">
								<i class="bi bi-graph-up"></i>
									<?php
										print $dataItem3;  // Donnée chiffré à afficher
									?>
									</span></h1>
									<p class="text-left">Total  : <span class="f-center"><?php print $currentData1 ?> €</span></p> <!-- Total de la donnée courante -->
									<p class="text-left"><?php print $info1 ?> : <?php print $dataInfo1?>€ </p> <!-- Donnée par rapport à l'année ou au mois dernier -->
									<p class="text-left"><?php print $info2 ?><?php print $dataInfo2?> % </p> <!-- Progression -->
								 </div>
							</div>
						</div>
					</div>
<?php
// End of page
llxFooter();
$db->close();
