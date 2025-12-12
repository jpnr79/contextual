<?php
/*
 * @version $Id: HEADER 15930 2020-09-28 10:47:55Z jmd $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2011 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

 LICENSE

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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginContextualMessage_Profile extends CommonDBRelation {

   // From CommonDBRelation
   static public $itemtype_1 = 'PluginContextualMessage';
   static public $items_id_1 = 'plugin_contextual_messages_id'; 	//getForeignKeyFieldForItemType('PluginContextualMessage');
    
      static public $itemtype_2 = 'Profile';
      static public $items_id_2 = 'profiles_id';
   static function getTypeName($nb=0) {
     global $LANG;
	  return _n('Profile', 'Profiles', $nb);
   }   

   static function countForMessage($message) {
     
		$criteria = [PluginContextualMessage_Profile::$items_id_1 => $message->getID()];	   
      
		return countElementsInTable(getTableForItemType(__CLASS__), $criteria);
	   
   }

	function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      
      $number  = self::countForMessage($item);

		if (($item->getType()=='PluginContextualMessage')&& ($this->canView())) {
         $ong[1] = self::createTabEntry(self::getTypeName($number), $number);
			return $ong;
		} else {
         return '';
      }
		
	}
		 
   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

      switch ($item->getType()) {
         case 'PluginContextualMessage' :
		
            switch ($tabnum) {
               case 1 :
                  self::showProfiles($item);
                  break;      
            }
            
         break;
      }

      return true;
   }

   static function showProfiles(PluginContextualMessage $message) {
         global $DB, $CFG_GLPI;
	  
         $instID = $message->fields['id'];
      
         if (!$message->can($instID, READ)) {
            return false;
         }
         $canedit = $message->can($instID, UPDATE);
   
         $rand   = mt_rand();
            echo "<form name='messageform_form$rand' id='messageform_form$rand' method='post'
                  action='".Toolbox::getItemTypeFormURL("PluginContextualMessage_Profile")."'>";
         if ($canedit) { 
            echo "<div class='firstbloc'>";
   
            echo "<table class='tab_cadre_fixe'>";
                 
            echo "<tr class='tab_bg_1'><td class='center'>";
           
            echo "<table id='form_in' style='display:none;'  class='tab_cadrehov' width='100%'><thead><tr class='tab_bg_2'>";
   
            echo "<th>";
            
            echo __('Añadir perfiles a los que va dirigido este mensaje');
            
            echo "</th></tr></thead>";
            
            echo "<body><tr><td align='center'>";
            
            
                  echo "<div class='fa-label'>
                     <i class='fas fa-filter fa-fw'
                        title='".__('Pedidos de Catálogo')."'></i>";
                     
                        $rand = Profile::dropdown(
                           [
                              'name'         => 'profiles_id', 
                              'entity_sons'  => true,
                              'entity'       => intval($message->fields['entities_id']),
                              'width'        => '600px'
                           ]
                        );         	
                     
                     echo "</div>";				
            echo "</td>";
            echo "</tr>";
            echo "</body></table>";
   
            self::boton($rand);
            
         echo "</td><td class='center'>";
            echo "<input type='submit' name='addform' value=\""._sx('button', 'Add')."\" class='submit'>";
            echo "<input type='hidden' name='".PluginContextualMessage_Profile::$items_id_1."' value='$instID'>";
            echo "</td></tr>";
            echo "</table>";	 
            echo "</div>";
    
         }
          Html::closeForm();	
   
         $query     = "SELECT *, `id` AS IDD
                       FROM " . getTableForItemType(__CLASS__);
         $query .= " WHERE plugin_contextual_messages_id =".$instID;
                
         $result_linked = $DB->query($query);				 
                
         $numrows=$DB->numrows($result_linked);				 
   
         echo '<div class="spaced">';
         $massContainerId = 'mass' . __CLASS__ . $rand;
         if ($canedit && $numrows) {
            Html::openMassiveActionsForm($massContainerId);
            $massiveactionparams = [
               'num_displayed' => min($_SESSION['glpilist_limit'], $numrows),
               'container'     => $massContainerId,
            ];
            
            Html::showMassiveActions($massiveactionparams);
         }
   
         echo '<table class="tab_cadre_fixehov">';
         echo '<tr class="noHover">';
         echo '<th colspan="12"><h3>';
         echo "<div class='fa-label'>
         <i class='fas fa-user-circle fa-fw'
            title='".__('Se muestran los Perfiles que tienen acceso al mensaje.')."'></i>".__('Perfiles')."</div></h3></th>";         
         echo '</tr>';
         if ($numrows) {
            self::commonListHeader(Search::HTML_OUTPUT, $massContainerId);
            Session::initNavigateListItems(
               PluginContextualMessage::class,
               //TRANS : %1$s is the itemtype name,
               //        %2$s is the name of the item (used for headings of a list)
               sprintf(__('%1$s = %2$s'), $message::getTypeName(1), $message->fields['name'])
            );
   
            $i = 0;
         
                  if ($numrows>0) {
                     while ($data = $DB->fetch_assoc($result_linked)) {
                        Session::addToNavigateListItems(PluginContextualMessage_Profile::class, $data['id']);
                        self::showShort(
                           $data['id'],
                           [
                              'row_num'               => $i,
                              'type_for_massiveaction' => __CLASS__,
                              'id_for_massiveaction'   => $data['IDD']
                           ]
                        );
                  }
               }
        
               self::commonListHeader(Search::HTML_OUTPUT, $massContainerId);
         }
         echo '</table>';
   
         if ($canedit && $numrows) {
            $massiveactionparams['ontop'] = false;         
            Html::showMassiveActions($massiveactionparams);
            Html::closeForm();
         }
         echo '</div>';   
		
 	} 

        static function boton ($dropdown_id) {
         
         //Toolbox::logInFile("procedimientos", " input: " . $dropdown_id . "\r\n\r\n"); 
     
           echo Html::scriptBlock("
           
           $(function() {
           
           var x;
           x=$(document);
           x.ready(inicio);
           
           function inicio(){
           
            var dropdown;
            dropdown=$('#dropdown_profiles_id".$dropdown_id."');
            dropdown.change(animate);
   
            $('#form_in').fadeIn('slow'); 

            var boton = $('input[name=addform]');  

            boton.fadeTo( 500 , 0, function() {
               // Animation complete.
               boton.css({ 'display' : 'none', });
            });
           
            }
           
           function animate(){
            
           var value = $(this).val();
           
           var boton = $('input[name=addform]');
           if ((!value) || (value==0)) {
           
           boton.fadeTo( 500 , 0, function() {
           // Animation complete.
           boton.css({ 'display' : 'none', });
           });
           
           /* boton.fadeIn( 500, function() {
           // Animation complete
           });*/
           
           } else {
           
           boton.fadeTo( 500 , 1, function() {
           // Animation complete.
           boton.css({ 'display' : 'inline', });
           });
           
           }
           }
           });
           
           ");
           
           }        
			
		
   static function install(Migration $migration) {
      global $DB, $CFG_GLPI;
      $table = getTableForItemType(__CLASS__);
      if (!$DB->TableExists($table)) {	  
		  
         $query = "CREATE TABLE IF NOT EXISTS `$table` (
         `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
		   `".PluginContextualMessage_Profile::$items_id_1."` BIGINT UNSIGNED NOT NULL DEFAULT '0',
		   `".PluginContextualMessage_Profile::$items_id_2."` BIGINT UNSIGNED NOT NULL DEFAULT '0',         
		   PRIMARY KEY (`id`),
		   UNIQUE KEY `unicity` (`".PluginContextualMessage_Profile::$items_id_1."`,`".PluginContextualMessage_Profile::$items_id_2."`)
		   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
		   
         $DB->doQuery($query) or die ("Error adding table $table");
		 
		 		$tabla='
			  <tr>
         declare(strict_types=1);
				<td colspan="2" align="left">&nbsp;&nbsp;<img style="vertical-align:middle;" alt="" src="'.$CFG_GLPI['root_doc'].'/plugins/contextual/img/check.png">&nbsp;
				&nbsp;<strong><FONT color="#3a9b26">'.$table.'</FONT>.</strong>				
				</td>
			  </tr>';
				 
		 Session::addMessageAfterRedirect($tabla);	
		 
      }
   }      

   function addItem($values) {

      $this->add($values);
    
   }   

 /**
    * @param $output_type     (default 'Search::HTML_OUTPUT')
    * @param $mass_id         id of the form to check all (default '')
    */
    static function commonListHeader($output_type = Search::HTML_OUTPUT, $mass_id = '') {

      // New Line for Header Items Line
      echo Search::showNewLine($output_type);
      // $show_sort if
      $header_num                      = 1;

      $items                           = [];
      $items[(empty($mass_id) ? '&nbsp' : Html::getCheckAllAsCheckbox($mass_id))] = '';
      $items[__('ID')]            = "id";
      $items[__('Profile')]       = "plugin_formcreator_forms_id";

      foreach ($items as $key => $val) {
         $issort = 0;
         $link   = "";
         $issort = 0;
         $order  = "";
         $options = "";

         switch ($key) {

            case 'Perfil' :
               $options = 'width=80%';
            break;
            
         }
        
         echo Search::showHeaderItem($output_type, $key, $header_num, $link, $issort, $order, $options);
      
      }

      // End Line for column headers
      echo Search::showEndLine($output_type);
   }

   /**
    * Display a line for an object
    *
    * @since 0.85 (befor in each object with differents parameters)
    *
    * @param $id                 Integer  ID of the object
    * @param $options            array    of options
    *      output_type            : Default output type (see Search class / default Search::HTML_OUTPUT)
    *      row_num                : row num used for display
    *      type_for_massiveaction : itemtype for massive action
    *      id_for_massaction      : default 0 means no massive action
    *      followups              : only for Tickets : show followup columns
    */
    static function showShort($id, $options = []) {
      global $CFG_GLPI, $DB;

      $p['output_type']            = Search::HTML_OUTPUT;
      $p['row_num']                = 0;
      $p['type_for_massiveaction'] = 0;
      $p['id_for_massiveaction']   = 0;

      if (count($options)) {
         foreach ($options as $key => $val) {
            $p[$key] = $val;
         }
      }

      $rand = mt_rand();

      // Prints a job in short form
      // Should be called in a <table>-segment
      // Print links or not in case of user view
      // Make new job object and fill it from database, if success, print it
      $item        = new static();
      $profile     = new Profile();

      $candelete   = static::canDelete();
      $canupdate   = Session::haveRight(static::$rightname, UPDATE);
      $align       = "class='center";
      $align_desc  = "class='left";

      $align      .= "'";
      $align_desc .= "'";

      if ($item->getFromDB($id)) {
         $item_num = 1;
         
         echo Search::showNewLine($p['output_type'], $p['row_num']%2);

         $check_col = '';
         if (($candelete || $canupdate)
             && ($p['output_type'] == Search::HTML_OUTPUT)
             && $p['id_for_massiveaction']) {

            $check_col = Html::getMassiveActionCheckBox($p['type_for_massiveaction'],
                                                        $p['id_for_massiveaction']);
         }
         echo Search::showItem($p['output_type'], $check_col, $item_num, $p['row_num'], $align);

         $id_col = $item->fields["id"];
         echo Search::showItem($p['output_type'], $id_col, $item_num, $p['row_num'], $align);

         // First column
         $profile->getFromDB($item->fields["profiles_id"]);   
         $first_col = "<span class='b'>".$profile->getName();"</span>&nbsp;";

         // Add link
         if ($item->canViewItem()) {

            $first_col = "<a id='".$profile->getType().$item->fields["profiles_id"]."$rand' href=\"".
                              $profile->getLinkURL()."\">$first_col</a>";
         }

         if ($p['output_type'] == Search::HTML_OUTPUT) {
            $first_col = sprintf(__('%1$s %2$s'), $first_col,
                                    Html::showToolTip(Toolbox::unclean_cross_side_scripting_deep(
                                       html_entity_decode($profile->fields['comment'], ENT_QUOTES, "UTF-8")
                                       ),
                                       ['display' => false,
                                        'applyto' => $profile->getType().
                                         $profile->fields["id"].
                                         $rand]));
         }

         echo Search::showItem($p['output_type'], $first_col, $item_num, $p['row_num'],
                               $align_desc."width='400' ");							   

         // Finish Line
         echo Search::showEndLine($p['output_type']);
      } else {
         echo "<tr class='tab_bg_2'>";
         echo "<td colspan='4' ><i>".__('No item in progress.')."</i></td></tr>";
      }
   }      

}
?>