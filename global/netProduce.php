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
require_once DOL_DOCUMENT_ROOT.'/commande/class/commande.class.php';
require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';
// Load translation files required by the page
$langs->loadLangs(array("tab@tab"));
$action = GETPOST('action', 'aZ09');

$nowyear = strftime("%Y", dol_now());
$year = GETPOST('year') > 0 ?GETPOST('year') : $nowyear;

$startyear = $year - 1;
$endyear = $year;

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

// CSS
$head = '<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">';
llxHeader($head, $langs->trans("Net à produire"));

print load_fiche_titre($langs->trans("Net à produire"));

// Chargement du template de navigation pour l'activité "Global"
print $object->load_navbar();

$titleItem1 = "Productions en cours";
$titleItem2 = "Nb de productions en cours";
$titleItem3 = "Productions les + proches";
$titleItem4 = "Date et client la + proche";
$titleItem5 = "Clients à produire";


// Montant des production en cours
 $info1 = "Montant des productions M-1 ";
 $info2 = "Progression du montant du montant des productions :";

// Nombre des production du mois dernier
$info3 = "Nombre des productions du mois dernier ";
$info4 = "Progression en % ";

// Nombre des production du mois dernier
$info5 = "Nombre des productions du mois dernier ";
$info6 = "Progression en % ";

// Nombre des production du mois dernier
$info7 = "Nombre des productions du mois dernier ";
$info8 = "Progression en % ";

// Nombre des production du mois dernier
$info9 = "Nombre des productions du mois dernier ";
$info10 = "Progression en % ";



include DOL_DOCUMENT_ROOT.'/custom/tab/template/template_boxes2.php';

?>
		<!-- BOXE WITHOUT STATS : box list -->
				<div class="grid-list">
					<div class="card bg-c-blue order-card">
						<!-- Corps de la carte -->
						<div class="card-body">
							<div class="card-block">
								<h4 class="text-center">
									<?= print $titleItem3 ?>
								</h4>
								<p class="text-center">
									<?php

									// Total amount of validated customer orders

									// $ret = $object->fetchAmountOrdersValidated(); // on recupere toutes les commandes validées

									// foreach($ret as $rets){

									// 	print '<p class="text-center">'.$rets->fk_soc.' - '.date($rets->date_livraison).'</p>';
									// 	print '<hr>';
									// }

									?>
									</p>

								 </div>
							</div>
						</div>
					</div>
					<!-- BOXE WITHOUT STATS -->


		<!-- BOXE WITHOUT STATS : box list -->
				<div class="grid-list">
					<div class="card bg-c-blue order-card">
						<!-- Corps de la carte -->
						<div class="card-body">
							<div class="card-block">
								<h4 class="text-center">
									<?php print $titleItem4 ?> <!-- Titre de la boxe -->
								</h4>
								<p class="text-center">
									<?php
									// List of third parties for each validated order
									// with the validation date
									$soc = new Societe($db);


									?>
									</p>
								 </div>
							</div>
						</div>
					</div>
					<!-- BOXE WITHOUT STATS -->


		<!-- BOXE WITHOUT STATS : box list -->
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
									// List of third parties for each validated order
									// with the validation date
									$soc = new Societe($db);
									$order = new Commande($db);

									$rets = $object->fetchOrdersValidated(); // On recupère toutes les commandes validées qu'on regroupe dans


									print '<p class="text-center">'.$ret->fk_soc.' - '.$ret->date_livraison.'</p>';
									print '<hr>';



									?>
									</p>
								 </div>
							</div>
						</div>
					</div>
					<!-- BOXE WITHOUT STATS -->


<?php
// End of page
llxFooter();
$db->close();
