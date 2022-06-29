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

// Security check
if (empty($conf->tab->enabled)) accessforbidden('Module not enabled');
$socid = 0;
if ($user->socid > 0) { // Protection if external user
	accessforbidden();
}

// Load translation files required by the page
$langs->loadLangs(array("tab@tab"));
$action = GETPOST('action', 'aZ09');

/**
 * DEFINE TIME FOR REQUEST
 */

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


/**
 * PRODUCTION EN COURS
 */
$titleItem1 = "Productions en cours";
$object = new General($db);
$result = $object->fetchValidatedOrderOnCurrentYears($firstDayYear, $lastDayYear);
$dataItem1 = price($result). "\n€";


// commande m- 1
$info1 = "Montant des commandes livrées du mois dernier";
$deliveryOrderOnLastMonth = $object->fetchDeliveredOrderOnLastMonth($firstDayLastMonth, $lastDayLastMonth);
$dataInfo1 = price($deliveryOrderOnLastMonth) . "\n€";

// Progression
$info2 = "Progression";
$deliveryOrderOnCurrentMonth = $object->fetchDeliveredOrderOnCurrentMonth($firstDayCurrentMonth, $lastDayCurrentMonth);

$result = $object->progress($deliveryOrderOnCurrentMonth, $deliveryOrderOnLastMonth);
$dataInfo2 = $result  ."\n%";


// Infos popup for production in progress
$firstPop_info1 = $titleItem1;
$firstPop_info2 = $info1;
$firstPop_info3 = $info2;

$firstPop_data1 = "Somme du montant (HT) des commandes clients validées sur l'année en cours";
$firstPop_data2 = "Somme du montant (HT) des commandes clients validées du mois précédent";
$firstPop_data3 = "Progression du montant des commandes clients validées du mois par rapport au mois dernier";


/**
 * NOMBRE DE PRODUCTION EN COURS
 */
$titleItem2 = "Nb de productions en cours";
$result1 = $object->fetchNbDeliveryOrderByYear($firstDayYear, $lastDayYear);
$nbDeliveryOrderByYear = count($result1);

$dataItem2 = $nbDeliveryOrderByYear;

// nb de commande livrées le mois dernier
$info3 = "Nb des productions du mois dernier";
$result2 = $object->fetchNbDeliveryOrder($firstDayLastMonth, $lastDayLastMonth);
$nbDeliveryOrderByLastMonth = count($result2);
$dataInfo3 = $nbDeliveryOrderByLastMonth;

// sur le mois courant
$result3 = $object->fetchNbDeliveryOrder($firstDayCurrentMonth, $lastDayCurrentMonth);
$nbDeliveryOrderByCurrentMonth = count($result3);
$dataInfo4 = $nbDeliveryOrderByCurrentMonth;

$info4 = "Progression";
$result = $object->progress($nbDeliveryOrderByCurrentMonth, $nbDeliveryOrderByLastMonth);
$dataInfo4 = $result . "\n%";

// Info popup for number of production in progress

$secondPop_info1 = $titleItem2;
$secondPop_info2 = $info3;
$secondPop_info3 = $info4;

$secondPop_data1 = "Somme du nombre (HT) des commandes clients validées sur l'année en cours";
$secondPop_data2 = "Somme du nombre (HT) des commandes clients validées du mois précédent";
$secondPop_data3 = "Progression du nombre de commandes clients validées du mois par rapport au mois dernier";

// todo : creation d'une fonction javascript pour coloriser l'evolution (%)

/*
 * Actions
 */


/**
 * PAGINATION
 * */

// Le numéro de la page sur laquelle on se trouve
if(isset($_GET['page']) && !empty($_GET['page'])){
    $currentPage = (int) strip_tags($_GET['page']);
}else{
    $currentPage = 1; // page courante (index)
}

// Le nombre d'articles souhaités par page
$bypages = 5;

// Calcul du 1er element de la page
$firstElement = ($currentPage * $bypages) - $bypages;

// Nb de cmd validées au total
$result3 = $object->fetchOrderSortedByDeliveryDate($firstDayYear, $lastDayYear, $firstElement, $bypages);
$nbValidatedOrder = count($result3);

// Le nombre de pages au total
$totalPages = ceil($nbValidatedOrder / $bypages);


/*
 * View
 */

$form = new Form($db);
$formfile = new FormFile($db);
$object = new General($db);

llxHeader($head, $langs->trans("Net à produire"));

print load_fiche_titre($langs->trans("Net à produire"));

// Chargement du template de navigation pour l'activité "Global"
$currentPage = $_SERVER['PHP_SELF'];
print $object->load_navbar($currentPage);

include DOL_DOCUMENT_ROOT.'/custom/tab/template/template_boxes2.php';


// PRODUCTION LES + PROCHES
$titleItem1 = "<h4>Productions les + proches</h4>";

// Info for nearest production (popinfo)
$thirdPop_info1 = $titleItem1;
$thirdPop_data1 = "Trie par date prévu de livraison, toutes les commandes clients validées sur le mois courant";

// Date et client la + proche
$titleItem2 = "<h4>Commandes validées du jour</h4>";

$thirdPop_info1 = $titleItem2;
$thirdPop_data1 = "Date de livraison prévues d'une commande client validée du jour";

// CLIENTS A PRODUIRE
// todo : afficher un filtre pour la date de creation et date prevu de livraison + lien vers listes des commandes dynamiquement
$titleItem3 = "<h4>Clients à produire</h4>";

$thirdPop_info1 = $titleItem3;
$thirdPop_data2 = "Liste des tiers pour chaques commandes validées";

?>

<!-- PRODUCTION LES PLUS PROCHES -->

<div class="card-deck">
   <div class="card">
      <div class="card-body">
		  <div class="text-center">
		  <div class="pull-left">
					<div class="popup" onclick="showPop3()">
						<span class="classfortooltip" style="padding: 0px; padding: 0px; padding-right: 3px !important;" title=""><span class="fas fa-info-circle  em088 opacityhigh"></span>
							<span class="popuptext" id="prodPop">
								<h4> Détails des informations / calculs </h4>
							<ul>
								<li><strong><?php print $thirdPop_info1 ?></strong><br><?php print $thirdPop_data1 ?></li><hr>

							</ul>
							</span>
						</div>
						</div>
						<script>
							// When the user clicks on div, open the popup
							function showPop3() {
								var popup = document.getElementById("prodPop");
								popup.classList.toggle("show");
							}
						</script>
						<?php print $titleItem1  ."\n".	"(".count($result3).")" ?>

							<p class="card-text">

									<?php
										if(is_array($result3) && $result3 != null){


											foreach ($result3 as $res)
											{
												$societe = new Societe($db);
												$societe->fetch($res->fk_soc);

												$commande = new Commande($db);
												$commande->fetch($res->rowid);
												$customer = $societe->name;

												print '<ul class="list-group">';
												print '<li class="list-group-item d-flex justify-content-between align-items-center">';
												print  '<i class="fas fa-address-card"></i><a href="'.DOL_URL_ROOT.'/societe/card.php?socid='.$societe->id.'">'.$customer.'</hr></a>';

												if($commande->date_livraison != null) {
													print '<p><span class="badge badge-pill badge-primary">Date de livraison prévue : '.date('j-m-Y', $commande->date_livraison).'</span></p></li>'."\n";
												} else {
													print '<span class="badge badge-pill badge-warning">Aucune date de livraison spécifiée</span></li>';
												}
												print '</ul>';
											}
										}
									?>

									</div>
								</div>
							</div>



	<div class="card">
		<div class="card-body">
				<h4 class="text-center">
				<?php print $titleItem3 ."\n" ?>
				</h4>
				</br>
				 <ul class="pagination">
					<!-- Lien vers la page précédente (désactivé si on se trouve sur la 1ère page) -->
						<li class="page-item <?= ($currentPage == 1) ? "disabled" : "" ?>">
							<a href="./netProduce.php?page=<?= $currentPage - 1 ?>" class="page-link">Précédente</a>
						</li>

							<?php for($page = 1; $page <= $totalPages; $page++): ?>
								<!-- Lien vers chacune des pages (activé si on se trouve sur la page correspondante) -->
								<li class="page-item <?= ($currentPage == $page) ? "active" : "" ?>">
									<a href="./netProduce.php?page=<?= $page ?>" class="page-link"><?= $page ?></a>
							</li>
								<?php endfor ?>
								<!-- Lien vers la page suivante (désactivé si on se trouve sur la dernière page) -->
								<li class="page-item <?= ($currentPage == $totalPages) ? "disabled" : "" ?>">
									<a href="./netProduce.php?page=<?= $currentPage + 1 ?>" class="page-link">Suivante</a>
								</li>
							</ul>
						</nav>
					<?php

					$rets = $object->fetchOrder(1, $firstElement, $bypages);

					if(is_array($rets) && $rets != null){

						foreach ($rets as $ret)
						{
							$societe = new Societe($db);
							$societe->fetch($ret->fk_soc);

							$commande = new Commande($db);
							$commande->fetch($ret->rowid);

							print '<ul class="list-group">';
							print '<li class="list-group-item d-flex justify-content-between align-items-center">';
							print  '<i class="fas fa-address-card"></i><a href="'.DOL_URL_ROOT.'/societe/card.php?socid='.$societe->id.'">'.$societe->name.'</a>';
							print '<span class="badge badge-pill badge-primary">';
							print '<a href="'.DOL_URL_ROOT.'/commande/card.php?id='.$commande->id.'"><span>Réf. commande :  '.$commande->ref.'</span></li>';
							print '</ul>';
						}
					}
				?>
				</div>
			</div>
		</div>


	<!-- CUSTOMER TO PRODUCE -->
	<div class="card">
		<div class="card-body">
			<div class="pull-left">
			<div class="popup" onclick="showPop5()">
				<span class="classfortooltip" style="padding: 0px; padding: 0px; padding-right: 3px !important;" title=""><span class="fas fa-info-circle  em088 opacityhigh"></span>
					<span class="popuptext" id="fourPop">
						<h4> Détails des informations / calculs </h4>
						<ul>
							<li><strong><?php print $fivePop_info1 ?></strong><br><?php print $fivePop_data1 ?></li><hr>
						</ul>
					</span>
				</span>
			</div>
		</div>
		<script>
		// When the user clicks on div, open the popup
		function showPop5() {
			var fourPopup = document.getElementById("fourPop");
			fourPopup.classList.toggle("show");
		}
		</script>
				<?php print $titleItem2 ."\n" ?>
			</div>
			</br>
					<?php

					$result = $object->fetchDeliveredOrderToday();

					if($result != null){

						foreach ($result as $res)
						{
							$societe = new Societe($db);
							$societe->fetch($res->fk_soc);

							$commande = new Commande($db);
							$commande->fetch($res->rowid);

							print '<ul class="list-group">';
							print '<li class="list-group-item d-flex justify-content-between align-items-center">';

							print '<a href="'.DOL_URL_ROOT.'/societe/card.php?socid='.$societe->id.'">'.$societe->name.'</hr></a>';
							print '<span class="badge badge-pill badge-primary">Réf. commande :  '.$commande->ref.'</span>';

							if($commande->date_livraison != null) {
								print '<p><strong>Date de création : </strong>'.date('j-m-Y', $commande->date_commande).'';
							} else {
								print '<span class="badge badge-pill badge-warning">Aucune date de livraison spécifiée</span>';
							}
							print '</li>';
							print '</ul>';
						}

					} else {
						print '<p class="center"><span class="badge badge-pill badge-danger">Aucune commande validées pour ce jour </span></p>';
						print '</br>';
					}

					?>
					</div>
				</div>
			</div>
<?php

// End of page
llxFooter();
$db->close();
