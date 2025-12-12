<?php
declare(strict_types=1);
/*
 * @version $Id: HEADER 15930 2011-10-25 10:47:55Z jmd $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2011 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

 LICENSE

 This file is part of GLPI.

 GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

// ----------------------------------------------------------------------
// Original Author of file: Javier David Marín Zafrilla
// Purpose of file:
// ----------------------------------------------------------------------

include ("../../../inc/includes.php");
                       
$plugin = new Plugin();

if ($plugin->isActivated("contextual")) {					   
					   
if (!isset($_GET["id"])) $_GET["id"] = "";
if (!isset($_GET["withtemplate"])) $_GET["withtemplate"] = "";

$PluginContextualContextual = new PluginContextualContextual();
  
	if (isset($_POST["add"])) {
	   
	   $PluginContextualContextual->add($_POST);
	   
	} else {

		if (isset($_POST['update'])) {
			
		$PluginContextualContextual->update($_POST);		
		   
		} else {
			
				if (isset($_POST['purge'])) {
		
				$PluginContextualContextual->delete($_POST, 1);
					
				}
	
		}

	}		

	if (isset($_POST["mark"])) {
		Html::redirect(html::getBackUrl()."#".$_POST["mark"]);
	} else {
		Html::back();	
	}
	     
// Or display a "Not found" error
} else {
	
   Html::displayNotFoundError();
}
?>
