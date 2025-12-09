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

class PluginContextualMessage extends CommonDBTM {
   public $dohistory=true; // EN LA CABECERA
   
   static $rightname = "plugin_contextual_message";

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
      return __('Message');
   }

   /**
    * @see CommonGLPI::getMenuName()
   **/
   static function getMenuName() {
      return __('Mensajes');
   }

   function defineTabs($options = []) {

      $ong = [];
      $this->addDefaultFormTab($ong);	 
			$this->addStandardTab('PluginContextualMessage_Profile', $ong, $options);
			$this->addStandardTab('PluginContextualMessage_User', $ong, $options);
      $this->addStandardTab('Log', $ong, $options);
      return $ong;
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
	  
   static function install(Migration $migration) {
      global $DB, $CFG_GLPI;
      
      $table = getTableForItemType(__CLASS__);

      if (!$DB->TableExists($table)) {
				$query ="CREATE TABLE IF NOT EXISTS `$table` (
									`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
									`name` text COLLATE utf8mb4_unicode_ci,
									`content` longtext COLLATE utf8mb4_unicode_ci,
									`users_id` BIGINT UNSIGNED NOT NULL DEFAULT '0',
									`view` BIGINT UNSIGNED NOT NULL DEFAULT '0',
									`date_creation` TIMESTAMP NULL DEFAULT NULL,
									`date_mod` TIMESTAMP NULL DEFAULT NULL,
									`begin_date` TIMESTAMP NULL DEFAULT NULL,
									`end_date` TIMESTAMP NULL DEFAULT NULL,
									`entities_id` BIGINT UNSIGNED NOT NULL DEFAULT '0',
  									`is_recursive` tinyint(1) NOT NULL DEFAULT '0',									
									PRIMARY KEY (`id`),
									KEY `users_id` (`users_id`),									
									KEY `date_creation` (`date_creation`),
									KEY `date_mod` (`date_mod`),
									KEY `begin_date` (`begin_date`),
									KEY `end_date` (`end_date`),
									KEY `entities_id` (`entities_id`),
 			 							KEY `is_recursive` (`is_recursive`),									
									FULLTEXT KEY `fulltext` (`name`,`content`),
									FULLTEXT KEY `name` (`name`),
									FULLTEXT KEY `content` (`content`)
								)  ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $DB->doQuery($query) or die ("Error adding table $table");		 
         		 
			$tabla='
					<tr>
					<td colspan="2" align="left">&nbsp;&nbsp;<img style="vertical-align:middle;" alt="" src="'.$CFG_GLPI['root_doc'].'/plugins/contextual/img/check.png">&nbsp;
					&nbsp;<strong><FONT color="#3a9b26">'.$table.'</FONT>.</strong>				
					</td>
					</tr>
					</table>';				 
				Session::addMessageAfterRedirect($tabla);	
				
				$migration->updateDisplayPrefs([
						__CLASS__ => [4, 5, 6, 3, 9, 10]
				]);		 
		 
      }
   }

	 function rawSearchOptions() {
		if (Session::haveRight('plugin_contextual_message',READ)) {
				$tab = [];  
	
				$tab[] = [
					 'id'   					=> 'common',
					 'name' 					=> self::getTypeName(2)
				];
			
				$tab[] = [
					 'id'       			=> '1',
					 'table'    			=> $this->getTable(),
					 'field'    			=> 'name',
					 'name'     			=> __('Name'),
					 'datatype' 			=> 'itemlink'
				];	  
	
				$tab[] = [
					'id'       				=> '2',
					'table'    				=> $this->getTable(),
					'field'    				=> 'content',
					'name'     				=> __('Content'),
					'datatype' 				=> 'text',
					'htmltext' 				=> true,
				 ];
	
				 $tab[] = [
					'id'       				=> '3',
					'table'    				=> $this->getTable(),
					'field'    				=> 'view',
					'name'     				=> _n('View', 'Views', Session::getPluralNumber()),
					'datatype' 				=> 'integer',
					'massiveaction'  	=> false
				 ];
	
				$tab[] = [
					'id'              => '4',
					'table'           => $this->getTable(),
					'field'           => 'begin_date',
					'name'            => __('Visibility start date'),
					'datatype'        => 'datetime'
					];
	
				$tab[] = [
					'id'              => '5',
					'table'           => $this->getTable(),
					'field'           => 'end_date',
					'name'            => __('Visibility end date'),
					'datatype'        => 'datetime'
				];	 
	
				$tab[] = [
					'id'              => '6',
					'table'           => 'glpi_users',
					'field'           => 'name',
					'name'            => __('User'),
					'massiveaction'   => false,
					'datatype'        => 'dropdown',
					'right'           => 'all'
			 ];
	
				$tab[] = [
					'id'              => '7',
					'table'           => 'glpi_entities',
					'field'           => 'completename',
					'name'            => __('Entity'),
					'massiveaction'   => false,
					'datatype'        => 'dropdown'
				];
	
				$tab[] = [
						'id'            => '8',
						'table'         => 'glpi_knowbaseitemcategories',		 
						'field'         => 'is_recursive',
						'name'          => __('Child entities'),
						'datatype'      => 'bool'
				];		 

			 $tab[] = [
						'id' 							=> '9',
						'table' 					=> 'glpi_plugin_contextual_messages_users',
						'field' 					=> 'view',
						'name' 						=> __('Leído', 'Leído'),				
						'massiveaction' 	=> false,	
					//'nosearch'				=> true,
						'transcomment'	  => __('Leído', 'Leído'),
						'searchtype' 			=> 'equals',
						'datatype' 				=> 'bool',	
						'joinparams' 			=> [
																'jointype' => 'child',				
																'condition' => "AND `NEWTABLE`.`users_id`= ".$_SESSION['glpiID'],
						],
						'computation'        => 'if (IsNull(TABLE.`view`), 0 ,1)'
				]; 		

				$tab[] = [
					'id' 						=> '10',
					'table' 				=> 'glpi_profiles',
					'field' 				=> 'name',
					'datatype'    	=> 'dropdown', 
					'name' 					=> __('Profile'),
					'forcegroupby'  => true,
					'joinparams'    => [
						 'beforejoin'    => [
								'table'   	    => 'glpi_plugin_contextual_messages_profiles',
								'joinparams'    => [
									 'jointype' 		  => 'child',
									 //'condition'    => 'AND NEWTABLE.`profiles_id` = '.CommonITILActor::REQUESTER
																	 ]
						 										]
														]
			 	];				

			} else {
				$tab = [];
	
				$tab[] = [
					 'id'   					=> 'common',
					 'name' 					=> self::getTypeName(2)
				];
				
				$condition = array();

				array_push($condition,'id in (select plugin_contextual_messages_id from glpi_plugin_contextual_messages_profiles where profiles_id = '.$_SESSION['glpiactiveprofile']["id"].')');
				
				$tab[] = [
					 'id'       			=> '1',
					 'table'    			=> $this->getTable(),
					 'field'    			=> 'name',
					 'name'     			=> __('Name'),
					 'datatype' 			=> 'itemlink',
					 'condition'      => $condition
				];		

				$tab[] = [
					'id' 							=> '9',
					'table' 					=> 'glpi_plugin_contextual_messages_users',
					'field' 					=> 'view',
					'name' 						=> __('Leído', 'Leído'),				
					'massiveaction' 	=> false,	
				//'nosearch'				=> true,
					'searchtype' 			=> 'equals',
					'datatype' 				=> 'bool',	
					'joinparams' 			=> [
															'jointype' => 'child',				
															'condition' => "AND `NEWTABLE`.`users_id`= ".$_SESSION['glpiID'],
					],
					'computation'        => 'if (IsNull(TABLE.`view`), 0 ,1)'
			]; 	
					
					$tab[] = [
						'id' 							=> '10',
						'table' 					=> 'glpi_profiles',
						'field' 					=> 'name',
						'datatype'    		=> 'dropdown', 
						'name' 						=> __('Profile'),
						'forcegroupby'    => true,
						'nosearch'				=> true,	
						'joinparams'      => [
							 'beforejoin'      => [
									'table'            => 'glpi_plugin_contextual_messages_profiles',
									'joinparams'       => [
										 'jointype'         => 'child',
										 'condition'        => 'AND NEWTABLE.`profiles_id` = '.$_SESSION['glpiactiveprofile']["id"]
																				]
							 ]
						]
				 ];
					
			}
			
			//$PluginContextualMessage_Profile = new PluginContextualMessage_Profile();
			//$tab = array_merge($tab, $PluginContextualMessage_Profile->rawSearchOptions());	
			
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

			$canedit = $this->can($ID, UPDATE);
      $this->initForm($ID, $options);
      $this->showFormHeader($options);
	  	$this->getFromDB($ID); 	
			  	 
      // General information : name
      echo '<table class="tab_cadre_fixe">';
			echo "<input type='hidden' name='users_id' value=\"".Session::getLoginUserID()."\">";
		

    	echo "<tr class='tab_bg_1'>";
			echo "<td style='width:100px'>".__('Visible since')."<br>&nbsp</td><td>";
				
			echo "<div class='fa-label'>
            <i class='fas fa-play fa-fw'
               title='".__('Visible since')."'></i><strong>";

							 Html::showDateTimeField("begin_date", ['value' => $this->fields["begin_date"],
							 'timestep'    => 1,
							 'maybeempty' => true,
							 'canedit'    => $canedit]);							 
			   
      echo "</strong></div>";
			echo "</td>";

			echo "<td style='width:100px'>".__('Visible until')."<br>&nbsp</td><td>";				
				          echo "<div class='fa-label'>
            <i class='fas fa-stop fa-fw'
               title='".__('Visible until')."'></i>";
				 
							 Html::showDateTimeField("end_date", ['value' => $this->fields["end_date"],
							 'timestep'    => 1,
							 'maybeempty' => true,
							 'canedit'    => $canedit]);			 
			   
       echo "</div>";
				echo "</td>";					
				echo "</tr>";

				echo "<tr><td style='width:100px'>".__('Name')."<br>&nbsp</td>";
				echo '<td colspan="3" class="">';
				echo "<div class='fa-label'>
				<i class='fas fa-flag fa-fw'
					title='".__('Name')."'></i><strong>";
						Html::autocompletionTextField($this, "name", ['value' => $this->fields["name"],  'option' => 'style="width:94%"']);				  

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

				$linkusers_id = true;
				// show item : question and answer
				if (((Session::getLoginUserID() === false) && $CFG_GLPI["use_public_faq"])
						|| (Session::getCurrentInterface() == "helpdesk")
						|| !User::canView()) {
					$linkusers_id = false;
				}

				echo "<tr><th class='tdkb'  colspan='2'>";
				if ($this->fields["users_id"]) {
					// Integer because true may be 2 and getUserName return array
					if ($linkusers_id) {
							$linkusers_id = 1;
					} else {
							$linkusers_id = 0;
					}

					echo sprintf(__('%1$s: %2$s'), __('Writer'), getUserName($this->fields["users_id"],
									$linkusers_id));
					echo "<br>";
				}

				echo "</th>";
				echo "<th class='tdkb' colspan='2'>";
				/*if ($this->countVisibilities() == 0) {
					echo "<span class='red'>".__('Unpublished')."</span><br>";
				}*/

				echo sprintf(_n('%d view', '%d views', $this->fields["view"]), $this->fields["view"]);


				echo "</th></tr>";	
			
				$this->showFormButtons($options);
			
				echo '</table>';		

				return true;
   }

   function updateCounter($id) {
			global $DB;

			//update counter view
			$DB->update(
				getTableForItemType(__CLASS__), [
						'view'   => new \QueryExpression($DB->quoteName('view') . ' + 1')
				], [
						'id' => $id
				]
			);
 		}	 

}