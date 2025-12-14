<?php
/* -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2011 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

 LICENSE
*/
declare(strict_types=1);

// ----------------------------------------------------------------------
// Original Author of file: Javier David Marín Zafrilla
// Purpose of file:
// ----------------------------------------------------------------------/

/**
 * Summary of plugin_contextual_install
 * @return boolean
 */
 
include_once (GLPI_ROOT."/plugins/contextual/inc/profile.class.php");

/**
 * Install the plugin
 * @return boolean
 */
function plugin_contextual_install() {
    // Initialize profile rights
    PluginContextualProfile::initProfile();
    return true;
}

// [INICIO] [CRI] JMZ18G FUNCIÓN PARA CAMBIAR LEFT JOIN POR INNER JOIN CUANDO EL USUARIO NO TIENE PERMISOS DE LECTURA SOBRE EL PLUGIN
function plugin_contextual_addLeftJoin($itemtype,$ref_table,$new_table,$linkfield,&$already_link_tables) {
	
		switch ($itemtype) {
			case 'PluginContextualMessage':
		if ($new_table == 'glpi_plugin_contextual_messages_profiles') {
			/*Toolbox::logInFile("procedimientos", " ref_table: " . print_r($ref_table, TRUE) . "\r\n\r\n");
			Toolbox::logInFile("procedimientos", " new_table: " . print_r($new_table, TRUE) . "\r\n\r\n");
			Toolbox::logInFile("procedimientos", " linkfield: " . print_r($linkfield, TRUE) . "\r\n\r\n");
			Toolbox::logInFile("procedimientos", " already_link_tables: " . print_r($already_link_tables, TRUE) . "\r\n\r\n");*/
			$tabla = end($already_link_tables);
			if (!Session::haveRight('plugin_contextual_message', READ)) {
				return 'INNER JOIN `glpi_plugin_contextual_messages_profiles` AS `'.$tabla.'` ON '
					. '(`glpi_plugin_contextual_messages`.`id` = `'.$tabla.'`.`plugin_contextual_messages_id` '
					. 'AND `'.$tabla.'`.`profiles_id` = '.$_SESSION['glpiactiveprofile']['id'].')';
			}
		}
		break;
		default:
			break;
	}
	return "";
}


/**
 * Summary of plugin_contextual_uninstall
 * @return boolean
 */
function plugin_contextual_uninstall() {
   global $DB;
   
	$profileRight = new ProfileRight();
	foreach (PluginContextualProfile::getAllRights() as $right) {
      $profileRight->deleteByCriteria(array('name' => $right['field']));
	}
    PluginContextualProfile::removeRightsFromSession();    
    ProfileRight::deleteProfileRights(['plugin_contextual']);   
   
	$config = new Config();
	$config->deleteConfigurationValues('plugin:contextual', ['configuration' => false]);

    //delete display preferences for this item
    $pref = new DisplayPreference();
    $pref->deleteByCriteria([
        'itemtype' => 'PluginContextualContextual'
    ]);

    //delete display preferences for this item
    $log = new Log();
    $log->deleteByCriteria([
	'OR' => [
	'itemtype' => 'PluginContextualContextual',
	'itemtype_link' => 'PluginContextualContextual'
	]	
    ]);

  
   
	// Get list of contextual tables using proper API
	$tables = ['glpi_plugin_contextual_contextuals', 'glpi_plugin_contextual_message_users', 
	           'glpi_plugin_contextual_messages', 'glpi_plugin_contextual_message_profiles'];
	$rows = 0;
	$existing_tables = [];
	
	foreach ($tables as $table) {
		if ($DB->tableExists($table)) {
			$existing_tables[] = $table;
			$rows++;
		}
	}
	
	if ($rows > 0) {
		global $CFG_GLPI;
		
		$tabla='<table>
			  <tr>
				<td align="left"><img style="vertical-align:middle;" alt="" src="'.$CFG_GLPI['root_doc'].'/plugins/contextual/img/erase.png">&nbsp;&nbsp;</td>
				<td class="center">&nbsp;
				<strong>Desinstalación</strong> realizada con <strong><font color="green">Éxito</font></strong> <br>- - - - - - - - - - - - - - - - - - <br>
				<strong>Plugin Ayuda Contextual</strong> versión <strong><font color="green">'. PLUGIN_CONTEXTUAL_VERSION .'</font></strong>		
				</td>
			  </tr>
			  
			  <tr>
				<td class="center" colspan="2">- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -<br>
				<strong>Tablas eliminadas</strong>: <strong><FONT color="#620613">'.$rows.'</FONT></strong><br>
				- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -<br>
				</td>
			  </tr>
			  
			  ';
		
		foreach ($existing_tables as $table_name) {
			$DB->doQuery("DROP TABLE IF EXISTS `".$table_name."`");
			
			$tabla.='
			  <tr>
				<td colspan="2" align="left">&nbsp;&nbsp;<img style="vertical-align:middle;" alt="" src="'.$CFG_GLPI['root_doc'].'/plugins/contextual/img/minus.png">&nbsp;
				&nbsp;<strong><FONT color="#620613">'.$table_name.'</FONT>.</strong>				
				</td>
			  </tr>';				
		}

		$tabla.='<tr>
				<td class="center" colspan="2">- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -</td>
			  </tr></table>';

      		Session::addMessageAfterRedirect(__($tabla, 'plugin_contextual'),false, INFO);

	}
		     

   
   return true;
}
