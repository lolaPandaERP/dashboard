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

use Stripe\BankAccount;

ob_start();
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
require_once DOL_DOCUMENT_ROOT.'/custom/tab/class/technique.class.php';


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
$general = new General($db);
$object = new Technique($db);

llxHeader($head, $langs->trans("Commande"));

print load_fiche_titre($langs->trans("Commande"));

// Chargement du template de navigation pour l'activité "Global"
print $object->load_navbar();

$titleItem1 = "Commandes en cours";
$titleItem2 = "Commandes en cours";
$titleItem3 = "Commandes clients en cours";

include DOL_DOCUMENT_ROOT.'/custom/tab/template/template_boxes2.php';

$titleItem4 = "Commandes clients en cours";
$titleItem5 = "Commandes les + importantes";
$titleItem6 = "Commandes les + anciennes";

?>

<!-- DATE ET CLIENT LA + PROCHE  -->
<div class="grid-list">
	<div class="card bg-c-blue order-card">
		<!-- Corps de la carte -->
		<div class="card-body">
			<div class="card-block">
				<h4 class="text-center">
					<?php print $titleItem4 ?>
				</h4>
				<hr>
				<p class="text-center">
					<?php

					// $result3 = $object->fetchOrderSortedByDeliveryDate($firstDayYear, $lastDayYear);

					// if(is_array($result3) && $result3 != null){

					// 	foreach ($result3 as $res)
					// 	{
					// 		$societe = new Societe($db);
					// 		$societe->fetch($res->fk_soc);

					// 		$commande = new Commande($db);
					// 		$commande->fetch($res->rowid);

					// 		print '<ul class="list-group">';
					// 		print '<li class="list-group-item d-flex justify-content-between align-items-center">';
					// 		print  '<i class="fas fa-address-card fa-2x"></i>'.$societe->name;

					// 		if($commande->date_livraison != null) {
					// 			print '<span class="badge-* badge-pill badge-primary">Date de livraison prévue : '.date('j-m-Y', $commande->date_livraison).'</span></li>';
					// 		} else {
					// 			print '<span class="badge-* badge-pill badge-warning">Aucune date de livraison spécifiée</span></li>';
					// 		}
					// 		print '</ul>';
					// 	}

					// }

					?>
					</p>
				 </div>
			</div>
		</div>
	</div>
	<!-- BOXE WITHOUT STATS -->

	<!-- PRODUCTION LES + PROCHES -->
<div class="grid-list">
	<div class="card bg-c-blue order-card">
		<!-- Corps de la carte -->
		<div class="card-body">
			<div class="card-block">
				<h4 class="text-center">
					<?php print $titleItem5 ?>
				</h4>
				<hr>
				<p class="text-center">
					<?php

					//  $result = $object->fetchDeliveredOrderToday();

					// if($result != null){

					// 	foreach ($result as $res)
					// 	{
					// 		$societe = new Societe($db);
					// 		$societe->fetch($res->fk_soc);

					// 		$commande = new Commande($db);
					// 		$commande->fetch($res->rowid);

					// 		print '<ul class="list-group">';
					// 		print '<li class="list-group-item d-flex justify-content-between align-items-center">';
					// 		print  '<i class="fas fa-address-card fa-2x"></i>'.$societe->name;

					// 		if($commande->date_livraison != null) {
					// 			print '<span class="badge-* badge-pill badge-primary">Date de création : '.date('j-m-Y', $commande->date_commande).'</span></li>';
					// 		} else {
					// 			print '<span class="badge-* badge-pill badge-warning">Aucune date de livraison spécifiée</span></li>';
					// 		}
					// 		print '</ul>';
					// 	}
					// } else {
					// 	print '<p class="center"><pan class="badge-* badge-pill badge-danger">Aucune commande validées pour ce jour </spa</p>';
					// }

					?>
					</p>
				 </div>
			</div>
		</div>
	</div>


<!-- CUSTOMER TO BE PRODUCED  -->
<div class="grid-list">
	<div class="card bg-c-blue order-card">
		<!-- Corps de la carte -->
		<div class="card-body">
			<div class="card-block">
				<h4 class="text-center">
					<?php print $titleItem6 ?>
				</h4>
				<hr>
				<p class="text-center">
					<?php

					// $rets = $object->fetchOrder(1);

					// if(is_array($rets) && $rets != null){

					// 	foreach ($rets as $ret)
					// 	{
					// 		$societe = new Societe($db);
					// 		$societe->fetch($ret->fk_soc);

					// 		$commande = new Commande($db);
					// 		$commande->fetch($ret->rowid);

					// 		print '<ul class="list-group">';
					// 		print '<li class="list-group-item d-flex justify-content-between align-items-center">';
					// 		print  '<i class="fas fa-address-card fa-2x"></i>'.$societe->name; // redirection vers module tiers - onglet client
					// 		print '<span class="badge-* badge-pill badge-primary">Réf. commande :  '.$commande->ref.'</span></li>';
					// 		print '</ul>';
					// 	}
					// }

					?>
					</p>
				 </div>
			</div>
		</div>
	</div>

	<?php

// End of page
llxFooter();
$db->close();
