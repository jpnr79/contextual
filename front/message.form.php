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
define('GLPI_ROOT', '../../..');
include (GLPI_ROOT."/inc/includes.php");

Session::checkLoginUser();

	if (Session::haveRight('plugin_contextual_message',READ)) {
		Html::header(__('contextual', 'contextual'), $_SERVER['PHP_SELF'] ,"admin", "PluginContextualMessage", "contextual");
	} else {
		Html::header(__('contextual', 'contextual'), $_SERVER['PHP_SELF']);	
	}

	$message = new PluginContextualMessage();

	if (isset($_POST["add"])) {
	$_POST["duration"]=(strtotime($_POST["end_date"])-strtotime($_POST["begin_date"]));
	$newID=$message->add($_POST);
    Html::redirect($_SERVER['HTTP_REFERER']);
	
	}  else  {

	if (isset($_POST["update"])) {
	$_POST["duration"]=(strtotime($_POST["end_date"])-strtotime($_POST["begin_date"]));
	$message->update($_POST);
	Html::redirect($_SERVER['HTTP_REFERER']);
 
	} else {

	if (isset($_POST["purge"])) {
	$message->delete($_POST);
	$message->redirectToList();
	}

	} }  

    if (Session::haveRight('plugin_contextual_message',READ)) {
			
			if ((isset($_GET["_in_modal"])) AND ($_GET["_in_modal"]>0)){

				$message->getFromDB($_GET["id"]); 
				
				echo plugin_show_content($message);
				Html::footer();

			} else {

				$message->display($_GET);
				Html::footer();

			}

	} else {
			
		$PluginContextualMessage_Profile = new PluginContextualMessage_Profile();

		$criteria = ['plugin_contextual_messages_id' => $_GET["id"], 
								 'profiles_id' => $_SESSION['glpiactiveprofile']['id']
								];	   
	
		if (countElementsInTable(PluginContextualMessage_Profile::getTable(), $criteria)>0){

			$message->getFromDB($_GET["id"]); 
			
			echo plugin_show_content($message);
			Html::footer();

		} else {

			Html::displayNotFoundError();

		}	

	}

