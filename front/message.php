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

	include ('../../../inc/includes.php');
	Session::checkLoginUser();
	if (Session::haveRight('plugin_contextual_message',READ)) {
		Html::header(__('contextual', 'contextual'), $_SERVER['PHP_SELF'] ,"admin", "PluginContextualMessage", "contextual");
	} else {
		Html::header(__('contextual', 'contextual'), $_SERVER['PHP_SELF']);	
	}
	// Check if plugin is activated...
	$plugin = new Plugin();
	if(!$plugin->isInstalled('contextual') || !$plugin->isActivated('contextual')) {
		Html::displayNotFoundError();
	} 
//	$data['sql']['search']	= $sql;													
//	$data['sql']['count'] =	[];
//	var_dump($data);
//	Search::Show('PluginContextualMessage');

	$params = Search::manageParams('PluginContextualMessage', $_GET);
	echo "<div class='search_page'>";
	Search::showGenericSearch('PluginContextualMessage', $params);

	$data = Search::prepareDatasForSearch('PluginContextualMessage', $params);
	Search::constructSQL($data);	
	if (!Session::haveRight('plugin_contextual_message',READ)) {													
	$data['sql']['count'] =	[];
	}

	Search::constructData($data);
	Search::displayData($data);	

	echo "</div>";

	Html::footer();

