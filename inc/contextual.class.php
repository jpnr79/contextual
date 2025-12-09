<?php
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
// ----------------------------------------------------------------------/

class PluginContextualContextual extends CommonDBTM {
   public $dohistory=true; // EN LA CABECERA
   
   static $rightname = "plugin_contextual";

  static function canView(): bool {
      return Session::haveRight(self::$rightname, READ);
   }

   function canViewItem(): bool {
      return (Session::haveRight(self::$rightname, READ)
              );
   }

   function canCreateItem(): bool {
      return Session::haveRight(self::$rightname, CREATE);
   }
 
   function canUpdateItem(): bool {
      return ((Session::haveRight(self::$rightname, UPDATE)) || ($_SESSION['glpiactiveprofile']['id']==='4'));
   } 

   function canDeleteItem(): bool {
      return ((Session::haveRight(self::$rightname, CREATE)) || ($_SESSION['glpiactiveprofile']['id']==='4'));
   } 

   function canPurgeItem(): bool {
      return ((Session::haveRight(self::$rightname, CREATE)) || ($_SESSION['glpiactiveprofile']['id']==='4'));
   }   

   // Should return the localized name of the type
   static function getTypeName($nb = 0) {
      return 'Ayuda Contextual';
   }

   /**
    * @see CommonGLPI::getMenuName()
   **/
   static function getMenuName() {
      return __('Ayuda Contextual');
   }



   function defineTabs($options = []) {

      $ong = [];
      $this->addDefaultFormTab($ong);	 
      $this->addStandardTab('Log', $ong, $options);

      return $ong;
   }
   
   static function DisplayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {      
	$rand    = mt_rand();
 
	echo "<div id='contextual$rand'>";
		echo '<div id="adddialog_contextual"></div>';
		echo '<div id="dialog_contextual"></div>';		


	 PluginContextualContextual::displayStandardTab($item, -1, 0, $options = []);	
	
	//$tabs->displayStandardTab($item, -1, 0, $options = []);
	
	$itemtype = $item->getType();
	$instID   = ((isset($item->fields['id'])) ? $item->fields['id'] : 0 );	
	$canedit  = Session::haveRight("plugin_contextual",READ);
	
	//$ruta=$_SESSION['glpiroot']."/plugins/contextual/lib/searchform/js/search_form.js";
	//echo  "<script type='text/javascript' src='$ruta'></script>";

      echo "<script>

	  $('#contextual$rand').contextual({
						   itemtype: '$itemtype',
						   canedit:   $canedit,
						   iframe:   'contextual$rand'
       });
	   
	   </script>
	   
	   </div>";   


      return true;
   }  
	  
   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
		//if ($item->getType()=="Ticket") {
			//Si el pedido de catálogo es "Peticiones de otros departamentos/organizaciones" 
            
				$number  = self::countForContextual($item);
				return self::createTabEntry(self::getTypeName($number), $number);
			
		//} 
	}

   static function countForContextual($item) {

		$criteria = ["itemtype" => $item->getType()];	   
	
		return countElementsInTable(getTableForItemType(__CLASS__), $criteria);
	   
   }

   static function install(Migration $migration) {
      global $DB, $CFG_GLPI;
      
      $table = getTableForItemType(__CLASS__);

			Session::addMessageAfterRedirect(__('			
			<table>
	<tr>
	<td align="left"><img style="vertical-align:middle;" alt="" src="'.$CFG_GLPI['root_doc'].'/plugins/contextual/img/install.png">&nbsp;&nbsp;</td>
	<td class="center">&nbsp;
	<FONT color="#4f35a2"><strong>Instalación</strong> realizada con <strong></font><font color="green">Éxito</font></strong> <br>- - - - - - - - - - - - - - - - - - <br>
	<font color="green"><strong>Plugin PluginContextualContextual</strong></font><FONT color="#4f35a2"> versión </font><strong><font color="green">'. PLUGIN_CONTEXTUAL_VERSION .'</font></strong>		
	</td>
	
	</tr>
</table><FONT color="#4f35a2"><br>Instalando Tablas.....</FONT><table>','plugin_contextual'),true, INFO);

      if (!$DB->TableExists($table)) {
         $query = "CREATE TABLE IF NOT EXISTS `$table` (                  				  
				  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
				  `itemtype` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
				  `field` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,				  
				  `content` LONGTEXT COLLATE utf8mb4_unicode_ci DEFAULT NULL,				 				  
				  `date_creation` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
				  `date_mod` TIMESTAMP NULL DEFAULT NULL,	
				  `entities_id` BIGINT UNSIGNED NOT NULL DEFAULT '0',
				  `is_recursive` tinyint(1) NOT NULL DEFAULT '0',				  
				  PRIMARY KEY (`itemtype`, `field`),				  
				  KEY `id` (`id`),
				  KEY `entities_id` (`entities_id`),
				  KEY `is_recursive` (`is_recursive`)
               ) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
         $DB->doQuery($query) or die ("Error adding table $table");		 
         		 
		$tabla='
			  <tr>
				<td colspan="2" align="left">&nbsp;&nbsp;<img style="vertical-align:middle;" alt="" src="'.$CFG_GLPI['root_doc'].'/plugins/contextual/img/check.png">&nbsp;
				&nbsp;<strong><FONT color="#3a9b26">'.$table.'</FONT>.</strong>				
				</td>
			  </tr>
			  </table>';
			  			  
				 
		 Session::addMessageAfterRedirect($tabla);			 
		 
      }

		if (!$DB->fieldExists($table,"title")){ 

			$query = "ALTER TABLE $table 
			ADD COLUMN `title` VARCHAR(255) NULL AFTER `field`;";
        
			$DB->doQuery($query) or die ("Error adding table $table");				$tabla='<FONT color="#4f35a2"><br>Campo <strong>`title`</strong> añadido en la tabla:</FONT>
				<table>
				 <tr>
				 <td colspan="2" align="left">&nbsp;&nbsp;<img style="vertical-align:middle;" alt="" src="'.$CFG_GLPI['root_doc'].'/plugins/contextual/img/check.png">&nbsp;
				 &nbsp;<strong><FONT color="#3a9b26">'.$table.'</FONT>.</strong>				
				 </td>
				 </tr>
				 </table>';
								 
					
				Session::addMessageAfterRedirect($tabla);	

			}

   }

 function rawSearchOptions() {

      $tab = [];

      $tab[] = [
         'id'   => 'common',
         'name' => self::getTypeName(2)
      ];
	  
      $tab[] = [
         'id'       => '1',
         'table'    => $this->getTable(),
         'field'    => 'content',
         'name'     => __('Contenido'),
         'datatype' => 'text'
      ];	  

      return $tab;
   } 

   function showForm($ID, $options = []) {

      global $CFG_GLPI;

	  if ($ID==0 and $this->canCreateItem()==0){

			echo "<div class='center'><br><br>";
			echo "<img src='" . $CFG_GLPI["root_doc"] . "/pics/warning.png' alt='".__s('Warning')."'>";
			echo "<br><br><span class='b'>" . __('Item not found') . "</span></div>";
			Html::nullFooter();
			exit ();
			 
	  }

      $this->initForm($ID, $options);
      $this->showFormHeader($options);
	  $this->getFromDB($ID); 
	  //Si ID = 0 y el pedido de catálogo es "Peticiones de otros departamentos/organizaciones", Recoge el contenido del campo "Comunicación interior de referencia" 


	echo '<tr><td colspan="4" class=""></td></tr>';
			  	 
      // General information : name
      echo '<table class="tab_cadre_fixe">';
		$itemtype = (isset($this->fields["itemtype"]) ? $this->fields["itemtype"] : $options["itemtype"] );
		$field = (isset($this->fields["field"]) ? $this->fields["field"] : $options["field"] );
		echo "<input type='hidden' name='itemtype' value='".$itemtype."'>"; 	
		echo "<input type='hidden' name='field' value='".$field."'>"; 	
		$options["mark"] =((isset($options["mark"])) ? $options["mark"] : "");
		echo "<input type='hidden' name='mark'  value='".$options["mark"]."'>"; 	
		

    echo "<tr class='tab_bg_1'>";
				echo "<td style='width:100px'>".__('Tipo de objeto')."<br>&nbsp</td><td>";
				
				          echo "<div class='fa-label'>
            <i class='fas fa-calendar-plus fa-fw'
               title='".__('Tipo de objeto')."'></i><strong>";
			  Html::autocompletionTextField($this, "itemtype", ['value' => $itemtype,  'option' => 'style="width:85%" disabled']);				  
			   
            echo "</strong></div>";
				echo "</td>";

				echo "<td style='width:100px'>".__('Objeto seleccionado')."<br>&nbsp</td><td>";				
				          echo "<div class='fa-label'>
            <i class='fas fa-vector-square fa-fw'
               title='".__('Objeto seleccionado')."'></i>";
				 
				 Html::autocompletionTextField($this, "field", ['value' => $field,  'option' => 'style="width:85%" disabled']);				 
			   
            echo "</div>";
				echo "</td>";					
	echo "</tr>";

	echo "<tr><td style='width:100px'>".__('Title')."<br>&nbsp</td>";
	echo '<td colspan="3" class="">';
	echo "<div class='fa-label'>
	<i class='fas fa-flag fa-fw'
		 title='".__('Title')."'></i><strong>";
			Html::autocompletionTextField($this, "title", ['value' => $this->fields["title"],  'option' => 'style="width:94%"']);				  

	echo "</strong></div></td></tr>";

	echo '<th colspan="4" class="">'.self::getTypeName(0).'</th>';
	echo '<tr><td colspan="4" class=""></td></tr>';			

	  $rand  = mt_rand();
      $rand_text  = mt_rand();
      $content_id = "comment$rand_text";
      $cols       = 90;
      $rows       = 15;	  

      $content = $this->fields['content'];
      if (isset($options['content'])
          && $options['content']) {
         $content = $options['content'];
      }	 
	  
				
      echo "<tr class='tab_bg_1'>";
				echo "<td colspan='4'>";

	  $content = Html::setRichTextContent($content_id, $content, $rand);
		
      Html::textarea(['name'            	=> 'content',
                      'value'           	=> $content,
                      'rand'            	=> $rand_text,
                      'editor_id'       	=> $content_id,
                      'enable_richtext' 	=> true,
											'enable_fileupload' => true,
                      'cols'            	=> $cols,
                      'rows'            	=> $rows]);
				
				echo "</td>";
						
	echo "</tr>";	
	  
      $this->showFormButtons($options);
	  
	      echo '</table>';		
      return true;
   }

	static function pre_show_tab($params) {
		//var_dump($_REQUEST);
		$rand    = mt_rand();
		$div_name = str_ireplace('$','',$_REQUEST['_glpi_tab'].$rand);
		$div_name = str_ireplace('main','',$div_name);
		//echo $div_name;
		
		$_REQUEST['div_name'] = $div_name;

		if ((isset($_REQUEST["_glpi_tab"]))
			and ($_REQUEST["_glpi_tab"] != "PluginContextualContextual$1")
			and ($_REQUEST["_glpi_tab"] != "Change$5") // Seguimientos
			and ($_REQUEST["_glpi_tab"] != "Ticket$1") // Seguimientos
			and ($_REQUEST["_glpi_tab"] != "Ticket$4")   // ESTADISTICAS
			//and ($key != "TicketValidation$1")
			and ($_REQUEST["_glpi_tab"] != "Log$1")
			and ($_REQUEST["_glpi_tab"] != "-2")
			and ($_REQUEST["_glpi_tab"] != "Profile_User$1")
			and ($_REQUEST["_glpi_tab"] != "CronTaskLog$2")
			) {

			echo "<div id='$div_name' class='tabcontextual'>";
			echo '<div id="showdialog_contextual_'.$div_name.'"></div>';		
		} else {
			echo "<div id='$div_name'>";	
		}
	
	}	 

	public function post_updateItem($history = 1) {

		//	Toolbox::logInFile("procedimientos", " input: " . print_r($this->input, TRUE) . "\r\n\r\n");

		 parent::addFiles($this->input, ['force_update' 	=> true,
		 																 'content_field' => 'content']);

		//	Toolbox::logInFile("procedimientos", " prepareInputForUpdate: " . print_r($this->input, TRUE) . "\r\n\r\n");
	
	}


	public function post_addItem() {
	
		parent::addFiles($this->input, ['force_update' 	=> true,
																		'content_field' => 'content']);

		//Toolbox::logInFile("procedimientos", " prepareInputForUpdate: " . print_r($this, TRUE) . "\r\n\r\n");
	
	}

	static function post_show_tab($params) {
		
		echo "</div>";

		if (isset($_REQUEST["_glpi_tab"])) {
			//var_dump($_REQUEST);

			if ((isset($_REQUEST["_glpi_tab"]))
			and ($_REQUEST["_glpi_tab"] != "PluginContextualContextual$1")
			and ($_REQUEST["_glpi_tab"] != "Change$5") // Seguimientos
			and ($_REQUEST["_glpi_tab"] != "Ticket$1") // Seguimientos
			and ($_REQUEST["_glpi_tab"] != "Ticket$4")   // ESTADISTICAS
			//and ($key != "TicketValidation$1")
			and ($_REQUEST["_glpi_tab"] != "Log$1")
			and ($_REQUEST["_glpi_tab"] != "-2")
			and ($_REQUEST["_glpi_tab"] != "Profile_User$1")
			and ($_REQUEST["_glpi_tab"] != "CronTaskLog$2")
			) {

				$itemtype=explode('$',$_REQUEST["_glpi_tab"])[0];
              //echo $itemtype;		  
				$contextual = new PluginContextualContextual();
				$params = ["itemtype" => $itemtype,];
				  
				if (count($contextual->find($params))>0){
						
				  echo "<script>

				  $('#".$_REQUEST["div_name"]."').contextual({
									   itemtype: '$itemtype',
									   canedit:   0,
									   iframe:   '".$_REQUEST["div_name"]."'
				   });
				   
				   </script>";			
						
				}
			}
		}	
	}

    static function displayStandardTab(CommonGLPI $item, $tab, $withtemplate = 0, $options = []) {

	$tabs= new CommonGLPI();
	 
      switch ($tab) {
         // All tab
         case -1 :
            // get tabs and loop over
            $ong = $item->defineAllTabs(['withtemplate' => $withtemplate]);

            if (method_exists($tabs, 'isLayoutExcludedPage') && 
                method_exists($tabs, 'isLayoutWithMain') &&
                !$tabs->isLayoutExcludedPage() && $tabs->isLayoutWithMain()) {
               //on classical and vertical split; the main tab is always displayed
               array_shift($ong);
            }

            if (count($ong)) {
               foreach ($ong as $key => $val) {
                  if ($key != 'empty') {
					  

	 // $item->getType()
		if (($key != "PluginContextualContextual$1")
			and ($key != "Change$5") // Seguimientos
			and ($key != "Ticket$1") // Seguimientos
			and ($key != "Ticket$4")   // ESTADISTICAS
			//and ($key != "TicketValidation$1")
			and ($key != "Log$1")
			and ($key != "-2")
			and ($key != "Profile_User$1")
			and ($key != "CronTaskLog$2")
			) { 

      					 // encapsular el form en un div con el itemtype al que pertenece		 
						 echo "<div id='".explode('$',$key)[0]."' class='tabcontextual'>";

						 echo "<div id='".$key."' class='alltab'>$val</div>";	
						 //[INICIO] [CRI] JMZ18G AYUDA CONTEXTUAL PARA LOS TABS DE UN OBJETO						 
						//[FINAL] [CRI] JMZ18G AYUDA CONTEXTUAL PARA LOS TABS DE UN OBJETO
						 switch ($key) {

							case 'PluginKanbanPile$main':								
								$options ["projects_id"] = $_REQUEST["projects_id"];
								$options ["modal"] = "dialog_pile";
								break;

							case 'PluginKanbanSprint$main':
								$options ["projects_id"] = $_REQUEST["projects_id"];
								break;

							case 'PluginFieldsField$main':								
								$options ["parent_id"] = $_REQUEST["parent_id"];
								break;								
							 
						 }

						 $tabs->displayStandardTab($item, $key, $withtemplate, $options);	
						 echo "</div>";
				}			
                  }
               }
            }
            return true;

         default :
           
            break;
      }
      return false;

   }
   
		   /**
    * Show a tooltip on an item
    *
    * @param $content   string   data to put in the tooltip
    * @param $options   array    of possible options:
    *   - applyto : string / id of the item to apply tooltip (default empty).
    *                  If not set display an icon
    *   - title : string / title to display (default empty)
    *   - contentid : string / id for the content html container (default auto generated) (used for ajax)
    *   - link : string / link to put on displayed image if contentid is empty
    *   - linkid : string / html id to put to the link link (used for ajax)
    *   - linktarget : string / target for the link
    *   - popup : string / popup action : link not needed to use it
    *   - img : string / url of a specific img to use
    *   - display : boolean / display the item : false return the datas
    *   - autoclose : boolean / autoclose the item : default true (false permit to scroll)
    *
    * @return nothing (print out an HTML div)
   **/
	static function showToolTip($content, $options = []) {
		global $CFG_GLPI;

		$param['applyto']    = '';
		$param['title']      = '';
		$param['contentid']  = '';
		$param['link']       = '';
		$param['linkid']     = '';
		$param['linktarget'] = '';
		$param['awesome-class'] = 'fa-info';
		$param['popup']      = '';
		$param['ajax']       = '';
		$param['display']    = true;
		$param['autoclose']  = true;
		$param['onclick']    = false;
		$param['mode']    	 = "contextual";
		
	//	Toolbox::logInFile("procedimientos", " content: " . print_r($content, TRUE) . "\r\n\r\n");

		if (is_array($options) && count($options)) {
			 foreach ($options as $key => $val) {
					$param[$key] = $val;
			 }
		}

		// No empty content to have a clean display
		if (empty($content)) {
			 $content = "&nbsp;";
		}
		$rand = mt_rand();
		$out  = '';

		// Force link for popup
		if (!empty($param['popup'])) {
			 $param['link'] = '#';
		}

		if (empty($param['applyto'])) {
			 if (!empty($param['link'])) {
					$out .= "<a id='".(!empty($param['linkid'])?$param['linkid']:"tooltiplink$rand")."'";

					if (!empty($param['linktarget'])) {
						 $out .= " target='".$param['linktarget']."' ";
					}
					$out .= " href='".$param['link']."'";

					if (!empty($param['popup'])) {
						 $out .= " onClick=\"".Html::jsGetElementbyID('tooltippopup'.$rand).".dialog('open'); return false;\" ";
					}
					$out .= '>';
			 }
			 if (isset($param['img'])) {
					//for compatibility. Use fontawesome instead.
					$out .= "<img id='tooltip$rand' src='".$param['img']."' class='pointer'>";
			 } else {

					if ($param['mode'] == "contextual") {

						$out .= "<span id='tooltip$rand' class='fas {$param['awesome-class']} pointer active'></span>";

					} else {

						$out .= "<span id='tooltip$rand' class='fas {$param['awesome-class']} pointer message_active'></span>";
					}
			 }

			 if (!empty($param['link'])) {
					$out .= "</a>";
			 }

			 $param['applyto'] = "tooltip$rand";
		}

		if (empty($param['contentid'])) {
			 $param['contentid'] = "content".$param['applyto'];
		}

		$out .= "<div id='".$param['contentid']."' class='invisible'>$content</div>";
		if (!empty($param['popup'])) {
			 $out .= Ajax::createIframeModalWindow('tooltippopup'.$rand,
																						 $param['popup'],
																						 ['display' => false,
																									 'width'   => 600,
																									 'height'  => 300]);
		}
		$js = "$(function(){";
		$js .= Html::jsGetElementbyID($param['applyto']).".qtip({
			 position: { viewport: $(window) },
			 content: {text: ".Html::jsGetElementbyID($param['contentid']);
		if (!$param['autoclose']) {
			 $js .=", title: {text: '".$param['title']." ',button: true}";
		}

		$js .= "}, style: { classes: 'qtip-rounded qtip-bootstrap'}";
	
		if ($param['onclick']) {
			 $js .= ",show: 'click', hide: false,";
		} else if (!$param['autoclose']) {

			if ($param['mode'] == "contextual") {

					$js .= ",show: {
						event: 'mouseover',
						solo: true, // ...and hide all other tooltips...
									}, 
										hide: {								
										//	fixed: true,
										//	delay: 500
										//leave: false
										event: 'unfocus', 
									}";
			} else {

					$js .= ",show: {
						event: 'mouseover',
						solo: true, // ...and hide all other tooltips...
									}, 
										hide: {								
										fixed: true,
										delay: 500,
										leave: false,
										//event: 'unfocus', 
									}";

			}


		}
		
		$js .= "});";
		$js .= "});";
		$out .= Html::scriptBlock($js);

		if ($param['display']) {
			 echo $out;
		} else {
			 return $out;
		}
 }

}