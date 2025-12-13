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
   }



<?php

class PluginContextualProfile extends Profile {

   static $rightname = "profile";

   static function getTypeName($nb = 0) {
      return 'Plugin Contextual';
   }

   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      if ($item->getType()=='Profile') {
            return self::getTypeName(2);
      }
      return '';
   }


   static function DisplayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      global $CFG_GLPI;

      if ($item->getType()=='Profile') {
         $ID = $item->getID();
         $prof = new self();

         self::addDefaultProfileInfos($ID, 
                                    array('plugin_contextual' => 0));
      }
      return true;
   }

   static function createFirstAccess($profiles_id) {
      //85
      self::addDefaultProfileInfos($profiles_id,
         ['plugin_contextual'         => (READ+UPDATE+CREATE+PURGE),
          'plugin_contextual_message' => (READ+UPDATE+CREATE+PURGE)], true);
   }
    /**
    * @param $profile
   **/
   static function addDefaultProfileInfos($profiles_id, $rights, $drop_existing = false) {
      global $DB;
      
      $profileRight = new ProfileRight();
      foreach ($rights as $right => $value) {
		  
		$criteria = [
		"profiles_id" => $profiles_id,
		"name" => $right,
		];		  
		  
         if (countElementsInTable('glpi_profilerights', $criteria) && $drop_existing) {
            $profileRight->deleteByCriteria(array('profiles_id' => $profiles_id, 'name' => $right));
         }
         if (!countElementsInTable('glpi_profilerights', $criteria)) {
            $myright['profiles_id'] = $profiles_id;
            $myright['name']        = $right;
            $myright['rights']      = $value;
            $profileRight->add($myright);

            //Add right to the current session
            $_SESSION['glpiactiveprofile'][$right] = $value;
         }
      }
   }


   /**
    * Show profile form
    *
    * @param $items_id integer id of the profile
    * @param $target value url of target
    *
   * @return void
    **/
   function showForm($profiles_id=0, $openform=TRUE, $closeform=TRUE) {

      echo "<div class='firstbloc'>";
      if (($canedit = Session::haveRightsOr(self::$rightname, array(CREATE, UPDATE, PURGE)))
          && $openform) {
         $profile = new Profile();
         echo "<form method='post' action='".$profile->getFormURL()."'>";
      }

      $profile = new Profile();
      $profile->getFromDB($profiles_id);
      if ($profile->getField('interface') == 'central') {
         $rights = $this->getAllRights();	 
         $profile->displayRightsChoiceMatrix($rights, array('canedit'       => $canedit,
                                                         'default_class' => 'tab_bg_2',
                                                         'title'         => __('General')));                                                         

   	  }
       
      if ($canedit
          && $closeform) {
         echo "<div class='center'>";
         echo Html::hidden('id', array('value' => $profiles_id));
         echo Html::submit(_sx('button', 'Save'), array('name' => 'update'));
         echo "</div>\n";
         Html::closeForm();
      }
      echo "</div>";
      return;
   }

   
   static function getAllRights($all = false) {
      $rights = array(
          array(
		        'itemtype'  => 'PluginContextualContextual',
                'label'     => _n('Ayuda Contextual', 'Ayuda Contextual', 2, 'contextual'),
                'field'     => 'plugin_contextual'
          ),
          array(
            'itemtype'  => 'PluginContextualMessage',
              'label'     => _n('Mensajes de Calidad', 'Mensajes de Calidad', 2, 'contextual'),
              'field'     => 'plugin_contextual_message'
        ),          
      );
      
      return $rights;
   }   


   /**
    * Init profiles
    *
    **/
    
   static function translateARight($old_right) {
	   
	   
	   
      switch ($old_right) {
         case '': 
            return 0;
         case 'r' :
            return READ;
         case 'w':
            return ALLSTANDARDRIGHT + READNOTE + UPDATENOTE;
         case '0':
         case '1':
            return $old_right;
            
         default :
            return 0;
      }
   }
   

   
   /**
   * Initialize profiles, and migrate it necessary
   */
   static function initProfile() {
      global $DB;
      $profile = new self();

      //Add new rights in glpi_profilerights table
      foreach ($profile->getAllRights(true) as $data) {
		  
		  $criteria = [			
			"name" => $data['field'],
		  ];
		  
         if (countElementsInTable("glpi_profilerights", $criteria) == 0) {
            ProfileRight::addProfileRights(array($data['field']));
         }
      }
   }

   
  static function removeRightsFromSession() {
      foreach (self::getAllRights(true) as $right) {
         if (isset($_SESSION['glpiactiveprofile'][$right['field']])) {
            unset($_SESSION['glpiactiveprofile'][$right['field']]);
         }
      }
   }
}

?>