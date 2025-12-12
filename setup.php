<?php

declare(strict_types=1);
/*
 * @version $Id: HEADER 15930 2011-10-25 10:47:55Z jmd $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2011 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

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
// ----------------------------------------------------------------------/

define ("PLUGIN_CONTEXTUAL_VERSION", "1.1.2");

// Init the hooks of the plugins -Needed
function plugin_init_contextual() {

    global $PLUGIN_HOOKS;

	$PLUGIN_HOOKS['csrf_compliant']['contextual'] = true;
	
    Html::requireJs('charts');
    $Plugin = new Plugin();
// ...existing code...
// ...existing code...

		// Registro de clases	 
		Plugin::registerClass('PluginContextualProfile',         ['addtabon' => array('Profile')]); 
        Plugin::registerClass('PluginContextualMessage_User',    ['addtabon' => array('PluginContextualContextual')]); 
        Plugin::registerClass('PluginContextualMessage_Profile', ['addtabon' => array('PluginContextualContextual')]); 
		 				
    }	
		  							     
    $PLUGIN_HOOKS['change_profile']['contextual'] = array('PluginContextualProfile', 'initProfile');
	
	if (Session::haveRight("plugin_contextual",READ)) {

	if (!isset($_SESSION['glpi_js_toload']['tinymce'])) {
	Html::requireJs('tinymce');
	}

//var_dump(plugin_contextual_find_objects());
//exit();		
    Plugin::registerClass('PluginContextualContextual', ['addtabon' => plugin_contextual_find_objects()]); 

	}

	if (Session::haveRight("plugin_contextual_message",READ)) {
       // Mientras no se apruebe plugin ocultamos menu
        $PLUGIN_HOOKS['menu_toadd']['contextual'] = array('admin' => 'PluginContextualMessage');
        
    }
	
    if (!isset($Plugin) || !$Plugin instanceof Plugin) {
        $Plugin = new Plugin();
    }
    if ($Plugin->isActivated('contextual')) {

        //if (strpos($_SERVER["SCRIPT_FILENAME"], 'message.form.php') !== false) {
            if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], "message.form.php") !== false
               && isset($_GET['id'])) {            	
            
            $PLUGIN_HOOKS['add_javascript']['contextual']=array('lib/searchform/js/search_form.js',
                                                                'lib/searchform/js/chartist.min.js',
                                                                'lib/searchform/js/chartist-plugin-fill-donut.js');   

            $PLUGIN_HOOKS['add_css']['contextual']=array('lib/searchform/css/contextual.css',
                                                         'lib/searchform/css/chartist.min.css');
        
        } else {

            $PLUGIN_HOOKS['add_javascript']['contextual']=array('lib/searchform/js/search_form.js');  

            $PLUGIN_HOOKS['add_css']['contextual']=array('lib/searchform/css/contextual.css');
            
        }      

		$PLUGIN_HOOKS['pre_show_tab']['contextual'] = ['PluginContextualContextual',
                                                         'pre_show_tab'];
														 
		$PLUGIN_HOOKS['post_show_tab']['contextual'] = ['PluginContextualContextual',
                                                         'post_show_tab'];

		$PLUGIN_HOOKS['pre_show_item']['contextual'] = ['PluginContextualContextual',
                                                         'pre_show_tab'];
														 
		$PLUGIN_HOOKS['post_show_item']['contextual'] = ['PluginContextualContextual',
                                                         'post_show_tab'];
	}	
// removed unmatched closing brace

// Get the name and the version of the plugin - Needed
function plugin_version_contextual() {
    global $LANG;

    return  ['name'           => 'Ayuda Contextual',
             'version'        => PLUGIN_CONTEXTUAL_VERSION,
             'author'         => 'CARM',
             'license'        => 'GPLv2+',
             'homepage'       => 'http://www.carm.es',
             'requirements'   => ['glpi' => ['min' => '11.0', 'max' => '12.0']]];
}

// Optional : check prerequisites before install : may print errors or add to message after redirect
function plugin_contextual_check_prerequisites() {
   if (version_compare(GLPI_VERSION, '9.4', 'lt')) {
       echo "This plugin requires GLPI >= 9.4";
       return false;
   } else {
       return true;
   }
}


// Check configuration process for plugin : need to return true if succeeded
// Can display a message only if failure and $verbose is true
function plugin_contextual_check_config($verbose=false) {
   if (true) {
      // Always true ...
      return true;
   }

   if ($verbose) {
      _e('Installed / not configured', 'avisos');
   }
   return false;
}

//Función que muestra el html de un mensaje o ayuda contextual
function plugin_show_content($message) {
    $options=[];
    $message->updateCounter($message->fields['id']);
    $user_message = new PluginContextualMessage_User();

    if ($user_message->getFromDBByCrit([PluginContextualMessage_User::$items_id_1 => $message->fields['id'],
                                        PluginContextualMessage_User::$items_id_2 => $_SESSION['glpiID']])) {   
                
        $user_message->updateCounter($message->fields['id']);

    } else {

        $params = [
            PluginContextualMessage_User::$items_id_1 => $message->fields['id'],
            PluginContextualMessage_User::$items_id_2 => $_SESSION['glpiID'],
            'view' => 1
            ];
            
        $user_message->addItem($params);

    }

    $out = "<table class='tab_cadre_fixe'>";
    $out.= "<tr><th colspan='4'>". $message->fields['name'];
    $out.= "</th></tr>";

    $out.= "<tr><td class='left' colspan='4'><h2>".__('Content')."</h2>\n";

    $out.= "<div id='kbanswer'>";

    $answer = html_entity_decode($message->fields['content']);
    $answer = Toolbox::unclean_html_cross_side_scripting_deep($answer);	

    $callback = function ($matches) {
        //1 => tag name, 2 => existing attributes, 3 => title contents
        $tpl = '<%tag%attrs id="%slug"><a href="#%slug">%icon</a>%title</%tag>';

        $title = str_replace(
             ['%tag', '%attrs', '%slug', '%title', '%icon'],
             [
                    $matches[1],
                    $matches[2],
                    Toolbox::slugify($matches[3]),
                    $matches[3],
                    '<svg aria-hidden="true" height="16" version="1.1" viewBox="0 0 16 16" width="16"><path d="M4 9h1v1H4c-1.5 0-3-1.69-3-3.5S2.55 3 4 3h4c1.45 0 3 1.69 3 3.5 0 1.41-.91 2.72-2 3.25V8.59c.58-.45 1-1.27 1-2.09C10 5.22 8.98 4 8 4H4c-.98 0-2 1.22-2 2.5S3 9 4 9zm9-3h-1v1h1c1 0 2 1.22 2 2.5S13.98 12 13 12H9c-.98 0-2-1.22-2-2.5 0-.83.42-1.64 1-2.09V6.25c-1.09.53-2 1.84-2 3.25C6 11.31 7.55 13 9 13h4c1.45 0 3-1.69 3-3.5S14.5 6 13 6z"/></svg>'
             ],
             $tpl
        );

        return $title;
    };
    $pattern = '|<(h[1-6]{1})(.?[^>])?>(.+)</h[1-6]{1}>|';
    $answer = preg_replace_callback($pattern, $callback, $answer);
     

    $out.= $answer;
    $out.= "</div>";
    $out.= "</td></tr>";
    $out.= "</table>";

    echo $out;    

}

function plugin_contextual_find_objects() {
	global $DB;
	$tab   = [];
	
	// Use criteria API instead of direct query
	$criteria = [
		'FROM' => 'information_schema.TABLES',
		'WHERE' => [
			'TABLE_SCHEMA' => $DB->dbdefault,
			'TABLE_NAME' => ['NOT LIKE', '%glpi_displaypreferences%'],
			'TABLE_NAME' => ['LIKE', 'glpi_%'],
			'TABLE_TYPE' => 'BASE TABLE'
		]
	];
	
	try {
		$result = $DB->request($criteria);
		
		if ($result->count() > 0) {
			foreach ($result as $data) {
				if (count(explode("glpi_plugin_formcreator_question", $data["TABLE_NAME"])) == 1) {	
					array_push($tab, getItemTypeForTable($data["TABLE_NAME"]));
				}
			}
		}
	} catch (Exception $e) {
		// Fallback: manually list common GLPI tables
		$common_tables = ['glpi_alerts', 'glpi_documents', 'glpi_entities', 'glpi_groups', 'glpi_items_tickets', 'glpi_tickets', 'glpi_users', 'glpi_computers', 'glpi_monitors', 'glpi_printers', 'glpi_peripherals', 'glpi_software', 'glpi_softwareversions', 'glpi_softwarelicenses', 'glpi_contracts'];
		foreach ($common_tables as $table) {
			if ($DB->tableExists($table)) {
				array_push($tab, getItemTypeForTable($table));
			}
		}
	}
	
	return $tab;
	
}


