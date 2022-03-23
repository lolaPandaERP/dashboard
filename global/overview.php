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

use Stripe\Balance;
use Stripe\BankAccount;

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
require_once DOL_DOCUMENT_ROOT.'/compta/bank/class/account.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/dolgraph.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/bank.lib.php';

// Load translation files required by the page
$langs->loadLangs(array("tab@tab"));
$action = GETPOST('action', 'aZ09');

// Get parameters
$socid = GETPOST('socid', 'int');
$action = GETPOST('action', 'aZ09');

/**
 * Title of infos into each items
 */

$titleItem1 = "Chiffre d'affaires";
$titleItem2 = "Encours C/F";
$titleItem3 = "Marge brute N";
$titleItem4 = "Trésorerie nette";

 // CA
 $info1 = "Chiffre d'affaire n-1";
 $info2 = "Progression";

 // Outstanding customer and suppliers
 $info3 = "Total du mois";
 $info4 = "Total des encours clients";

 // Margins
 $info5 = "Marges restant à produire";
 $info6 = "Marge brut prévisionnelle";

 // Treso
 $info7 = "Charges mensuelles";
 $info8 = "Recurrent mensuel";

 /**
  *  Load Data
  */
$dataItem1 = price(1000);
$dataItem2 = price(1000);
$dataItem3 = price(1000);
$dataItem4 = price(1000);


 // Tot for each items
 $currentData1  = 25;
 $currentData2 = 25;
 $currentData3 = 4178;
 $currentData4 = 25;


/*
 * Actions
 */

// None


/*
 * View
 */

$form = new Form($db);
$formfile = new FormFile($db);
$generalitem = new General($db);

// Template NavBar
llxHeader('', $langs->trans("Global - Général"));

print load_fiche_titre($langs->trans("Général"));

// Chargement du template de navigation pour l'activité "Global"
print $generalitem->load_navbar();

// Data BOX 1
$account = new Account($db);

include DOL_DOCUMENT_ROOT.'/custom/tab/template/template_boxes4.php';



// End of page
llxFooter();
$db->close();
