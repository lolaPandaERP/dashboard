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
require_once DOL_DOCUMENT_ROOT . '/core/class/dolgraph.class.php';

// Security check
if (empty($conf->tab->enabled)) accessforbidden('Module not enabled');
$socid = 0;
if ($user->socid > 0) { // Protection if external user
	accessforbidden();
}
if(empty($conf->global->START_FISCAL_YEAR) || empty($conf->global->START_FISCAL_LAST_YEAR) ){
	accessforbidden('Vous devez obligatoirement renseigner la date de début de l\'exercice fiscal dans la configuration du module');
} else {
	$startFiscalyear = $conf->global->START_FISCAL_YEAR;
	$startFiscalLastyear = $conf->global->START_FISCAL_LAST_YEAR;
}

// fetch current bank account
$object = new General($db);
$ret = $object->getIdBankAccount();

$datetime = dol_now();
$year = dol_print_date($datetime, "%Y");
$month = dol_print_date($datetime, "%m");
$day = dol_print_date($datetime, "%d");

// Calcul for last day in current year according to the beginning of the fiscal year
$duree = 1;

// Transform date in timestamp
$TimestampCurrentYear = strtotime($startFiscalyear);
$TimestampCurrentLastYear = strtotime($startFiscalLastyear);

// calcul the end date for current and last year
$endYear = date('Y-m-d', strtotime('+'.$duree.'year', $TimestampCurrentYear));
$endLastYear = date('Y-m-d', strtotime('+'.$duree.'year', $TimestampCurrentLastYear));

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

$nowyear = strftime("%Y", dol_now());
$year = GETPOST('year') > 0 ? GETPOST('year', 'int') : $nowyear;
$startyear = $year - (empty($conf->global->MAIN_STATS_GRAPHS_SHOW_N_YEARS) ? 2 : max(1, min(10, $conf->global->MAIN_STATS_GRAPHS_SHOW_N_YEARS)));
$endyear = $year;


if(!empty($conf->global->START_FISCAL_YEAR)){
	$startMonthTimestamp = strtotime($startFiscalyear);
	$duree = 12;
	$startMonthFiscalYear = date('n', strtotime('+'.$duree.'month', $startMonthTimestamp));
	$monthFiscalyear = $startMonthFiscalYear;
} else {
	$monthFiscalyear = 1;
}

/**
 * PRODUCTION EN COURS
 */
$titleItem1 = "Productions en cours";
$object = new General($db);
$validated_order_fiscalYear = $object->fetchOrder(1);
$dataItem1 = price($validated_order_fiscalYear). "\n€";

// total amount of delivery order on current month
$deliveryOrderOnCurrentMonth = $object->fetchDeliveredOrder($firstDayCurrentMonth, $lastDayCurrentMonth);
$total_deliveryOrderOnCurrentMonth = array_sum($deliveryOrderOnCurrentMonth);

// total amount of delivery order on last month
$info1 = "Montant des productions M-1";
$deliveryOrderOnLastMonth = $object->fetchDeliveredOrder($firstDayLastMonth, $lastDayLastMonth);
$total_deliveryOrderOnLastMonth = array_sum($deliveryOrderOnLastMonth);
$dataInfo1 = price($total_deliveryOrderOnLastMonth) . "\n€";

// Progression
$info2 = "Progression";

$result = $object->progress($total_deliveryOrderOnCurrentMonth, $total_deliveryOrderOnLastMonth);
$dataInfo2 = $result  ."\n%";

// Infos popup for production in progress
$firstPop_info1 = $titleItem1;
$firstPop_info2 = $info1;
$firstPop_info3 = $info2;

$firstPop_data1 = "Somme du montant total (HT) des commandes clients validées sur l'exercice fiscal en cours : <strong>(".price($validated_order_fiscalYear)."\n€)</strong>";
$firstPop_data2 = "Somme du montant total (HT) des commandes clients livrées du mois précédent";
$firstPop_data3 = "Progression du montant des commandes clients livrées du mois(VA) par rapport au mois dernier(VD) </br>
				  Formule : (( VA - VD ) / VD ) x 100 <br> Soit <strong>( ".price($total_deliveryOrderOnLastMonth)." - ".price($total_deliveryOrderOnCurrentMonth)." ) / ".price($total_deliveryOrderOnCurrentMonth).") x 100 </strong>";


/**
 * NOMBRE DE PRODUCTION EN COURS
 */
$titleItem2 = "Nb de productions en cours";
$result1 = $object->fetchDeliveredOrder($startFiscalyear, $endYear);
$nbDeliveryOrderByYear = count($result1);

$dataItem2 = $nbDeliveryOrderByYear;


/**
 * GRAPH 1
 */

$monthsArr = monthArray($langs, 1); // months

$file = "evolutionValidatedCustomerOrder";
$fileurl = DOL_DOCUMENT_ROOT.'/custom/tab/img';
$commande = new Commande($db);

for($i = $monthFiscalyear; $i <= 12 ; $i++){

	$lastDayMonth = cal_days_in_month(CAL_GREGORIAN, $i, $year);

	// Start and end of each month on current years
	$date_start = $year.'-'.$i.'-01';
	$date_end = $year.'-'.$i.'-'.$lastDayMonth;

	$total_customer_order_validated += $object->fetchValidatedOrder($date_start, $date_end); // current

	if(date('n', $date_start) == $i){
		$total_customer_order_validated += $commande->total_ht;
	}

	$data[] = [
		html_entity_decode($monthsArr[$i]),
		$total_customer_order_validated,
	];
}

$px1 = new DolGraph();
$mesg = $px1->isGraphKo();
$legend = ['Année N'];

if (!$mesg){
	$px1->SetTitle("Evolution du montant des commandes clients validées");
	$px1->datacolor = array(array(138,233,232));
	$px1->SetData($data);
	$px1->SetLegend($legend);
	$px1->SetType(array('lines'));
	$px1->setHeight('250');
	$px1->SetWidth('500');
	$amount_production_chart = $px1->draw($file, $fileurl);
}
$graphiqueA = $px1->show($amount_production_chart);


// number of validated orders on last momnth
$info3 = "Nb des productions du mois dernier";
$result2 = $object->fetchDeliveredOrder($firstDayLastMonth, $lastDayLastMonth);
$nbDeliveryOrderByLastMonth = count($result2);

$dataInfo3 = $nbDeliveryOrderByLastMonth;

// sur le mois courant
$result3 = $object->fetchDeliveredOrder($firstDayCurrentMonth, $lastDayCurrentMonth);
$nbDeliveryOrderByCurrentMonth = count($result3);
$dataInfo4 = $nbDeliveryOrderByCurrentMonth;

$info4 = "Progression";
$result = $object->progress($nbDeliveryOrderByCurrentMonth, $nbDeliveryOrderByLastMonth);
$dataInfo4 = $result . "\n%";

// Info popup for number of production in progress
$secondPop_info1 = $titleItem2;
$secondPop_info2 = $info3;
$secondPop_info3 = $info4;

$secondPop_data1 = "Nb de commandes clients validées sur l'exercice en cours <strong>(".$nbDeliveryOrderByYear.")</strong>";
$secondPop_data2 = "Nb de commandes clients livrées du mois précédent <strong>(".$nbDeliveryOrderByLastMonth.")</strong>";
$secondPop_data3 = "Progression du nombre de commandes clients livrées du mois courant par rapport au mois précédent : </br>
					<strong> Formule : (( VA - VD ) / VD ) x 100 </strong> </br>
					Où <strong> (( ".$nbDeliveryOrderByCurrentMonth." - ".$nbDeliveryOrderByLastMonth." ) / ".$nbDeliveryOrderByLastMonth." ) * 100 </strong>";

/**
 * GRAPH 2
 */

$monthsArr = monthArray($langs, 1); // months

$file = "evolutionNbValidatedCustomerOrder";
$fileurl = DOL_DOCUMENT_ROOT.'/custom/tab/img';
$commande = new Commande($db);
$data = []; // reset datas
for($i = $monthFiscalyear; $i <= 12 ; $i++){

	$lastDayMonth = cal_days_in_month(CAL_GREGORIAN, $i, $year);

	// Start and end of each month on current years
	$date_start = $year.'-'.$i.'-01';
	$date_end = $year.'-'.$i.'-'.$lastDayMonth;

	$array_delivery_customer_orders = $object->fetchDeliveredOrder($date_start, $date_end);
	$nb_delivery_customer_orders = count($array_delivery_customer_orders);

	if(date('n', $date_start) == $i){
		$nb_delivery_customer_orders += $commande->total_ht;
	}

	$data[] = [
		html_entity_decode($monthsArr[$i]),
		$nb_delivery_customer_orders,
	];
}

$px2 = new DolGraph();
$mesg = $px2->isGraphKo();
$legend = ['Année N'];

if (!$mesg){
	$px2->SetTitle("Evolution du nb de commandes clients validées");
	$px2->datacolor = array(array(138,233,232));
	$px2->SetData($data);
	$px2->SetLegend($legend);
	$px2->SetType(array('lines'));
	$px2->setHeight('250');
	$px2->SetWidth('500');
	$nb_production_chart = $px2->draw($file, $fileurl);
}

$graphiqueB = $px2->show($nb_production_chart);


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

print $object->load_navbar();

include DOL_DOCUMENT_ROOT.'/custom/tab/template/template_boxes4.php';

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
												$res = $societe->fetch($res->fk_soc);

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
							print '<a href="'.DOL_URL_ROOT.'/commande/card.php?id='.$commande->id.'"><span>Réf. commande :  '.$commande->ref.'</span></a></li>';
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
