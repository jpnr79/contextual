<?php
/*
 * @version $Id: HEADER 15930 2020-09-28 10:47:55Z jmd $
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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginContextualMessage_User extends CommonDBRelation {

   // From CommonDBRelation
   static public $itemtype_1 = 'PluginContextualMessage';
   static public $items_id_1 = 'plugin_contextual_messages_id'; 	//getForeignKeyFieldForItemType('PluginContextualMessage');
    
   static public $itemtype_2 = 'User';
   static public $items_id_2 = 'users_id';
   
   static $rightname = "plugin_contextual_message";

   static function canView(): bool {

		return (Session::haveRight(self::$rightname, READ));
	
	 }
	 
   static function getTypeName($nb=0) {
      global $LANG;
	  return _n('Message', 'Mensajes', $nb);
   }   

	function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

			if (($item->getType()=='PluginContextualMessage')&& ($this->canView())) {
            $ong[1] = self::createTabEntry('Estadísticas');
            $ong[2] = __('Vista Preliminar');
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
                  self::showStatistics($item->getID(), $item);
                  break;
      
               case 2 :
                  plugin_show_content($item);
                  break;
      
            }
            
         break;
      }

      return true;
   }

   	static function showStatistics($ID, $item) {
         global $DB, $CFG_GLPI;

				$view_users = new PluginContextualMessage_User();

				$dbu = new DbUtils();

				$cond = ["plugin_contextual_messages_id" => $ID];
				$countViews = $dbu->countElementsInTable(self::getTable(), $cond);            

            $iterator = $DB->request([
               'SELECT DISTINCT' => 'glpi_users.name AS name',
               'FROM' => 'glpi_users',
               'INNER JOIN' => [
               'glpi_profiles_users' => [
               'ON' => [
               'glpi_users'          => 'id',
               'glpi_profiles_users' => 'users_id',
               
               ]
               ],
               'glpi_plugin_contextual_messages_profiles' => [
                  'ON' => [
                  'glpi_profiles_users'                      => 'profiles_id',
                  'glpi_plugin_contextual_messages_profiles' => 'profiles_id',
                  
                  ]
                  ]               
               ],              
               'WHERE' => [
                  'glpi_users.is_active'                                                   => 1,
                  'glpi_users.auths_id'                                                    => 1,   
                  'glpi_plugin_contextual_messages_profiles.plugin_contextual_messages_id' => $ID
                  ],
               ]);  
               

            $total_users = (count($iterator)>1 ? count($iterator) : $countViews );
                   
				//$condition = ['is_active' => 1, 'auths_id' => 1];
				//$total_users = $dbu->countElementsInTable(User::getTable(), $condition);				

            echo '<table class="tab_cadre_fixe">';
            echo '<tr><th>Visitas por total de Usuarios</th><th>Visitas Únicas Vs Totales</th></tr>';
            echo "<tr class='tab_bg_1'>";
            echo "<td width='50%'>";
               $porcent_total = round((($countViews * 100) / $total_users),3);
               $title         = "Usuarios";
               $series        = "'".$porcent_total."', '".(100-$porcent_total)."'";
               $labels        = "'".$porcent_total."%', '".(100-$porcent_total)."%'";
               $value_1       = $countViews;
               $value_total_1 = $total_users;  
               $options       = ['color'            => '#88b788',
                                 'label_color'      => '#000',
                                 'background_color' => '#a3b6da',
                                 'fore_color'       => '#6c0349'];  

               $view_users->displayPieGraph($title, $series, $labels, $value_1, $options, $display = true);
            echo "</td><td width='50%'>";
            $views = ($item->fields['view']<1 ? 1 : $item->fields['view']); // EVITAR ERROR *** PHP Warning(2): Division by zero
               $porcent_total = round((($countViews * 100) / ($views)),3);
               $title         = "Visitas";
               $series        = "'".$porcent_total."', '".(100-$porcent_total)."'";
               $labels        = "'".$porcent_total."%', '".(100-$porcent_total)."%'";
               $value_2       = $item->fields['view'];
               $value_total_2 = $item->fields['view']-$countViews;            
               $options       = ['color'            => '#eceebf',
                                 'label_color'      => '#6c0349',
                                 'background_color' => '#a06e8f',
                                 'fore_color'       => '#6c0349'];  
                                 
               $view_users->displayPieGraph($title, $series, $labels, $value_2, $options, $display = true);
            echo "</td></tr>";
            echo "<tr><th><font color='green'>$value_1</font> Usuarios de <font color='blue'>$value_total_1</font>.</th>";
            echo "<th><font color='green'>$countViews</font> Visitas únicas y <font color='blue'>$value_total_2</font> concurrentes.</th></tr></table>";


		/*	$iterator=$DB->request([
				'SELECT' => [
				PluginKanbanPile::getTable().'.id',
				PluginKanbanPile::getTable().'.name',
				],
				'FROM' => self::getTable(),
				'LEFT JOIN' => [
				PluginKanbanPile::getTable() => [
				'ON' => [
				PluginKanbanPile::getTable() => 'id',
				self::getTable() => getForeignKeyFieldForItemType('PluginKanbanPile')
				]
				]
				],
				'WHERE' => [
				PluginKanbanPile::getTable().'.projects_id' => $instID,
				self::getTable().".".getForeignKeyFieldForItemType('PluginKanbanSprint') => $sprint
				],
												 'GROUPBY'      => [
														PluginKanbanPile::getTable().'.id',
												 ],		
				
				'ORDER' => self::getTable().".".getForeignKeyFieldForItemType('PluginKanbanPile').' DESC',
				
				]);*/			
			
	
 	 	} 
			
  /**
    * Check and replace empty labels
    *
    * @param array $labels Labels
    *
    * @return void
    */

		private function displayPieGraph($title, $series, $labels, $value, $options = [], $display = true) {

         echo "<div id='$title' class='ct-chart'></div>";

         echo' <style type="text/css">
          .ct-chart-donut .ct-series-a .ct-slice-donut {
             ';

         if (isset($options['color'])){
            echo 'stroke: '.$options['color'].';';
         } else {
            echo 'stroke: #d70206;'; 
         }

         echo'}

          .ct-chart-donut .ct-series-b .ct-slice-donut {
              stroke: rgba(0,0,0,.4);
              opacity: 0.0;
          }';  

         echo '#'.$title.' svg.ct-chart-donut .ct-fill-donut .ct-slice-donut{'; 
            if (isset($options['background_color'])){
               echo 'stroke: '.$options['background_color'].';';
            } else {
               echo 'stroke: rgba(0,0,0,.4);';
            }            
            
         echo 'opacity: 1;
          }
          .ct-fill-donut-label h3{
              font-weight: bolder;
          }
          .ct-fill-donut-label .small {
              font-size: 0.6em;
          }

          #'.$title.' div.ct-fill-donut-label {';
            
            if (isset($options['fore_color'])){
               echo 'color: '.$options['fore_color'].';';
            } else {
               echo 'color: rgba(0,0,0);'; 
            }              
            echo 'font-size: .90rem;
            line-height: 1;
          }

          #'.$title.' svg g text.ct-label {
            color: rgba(0,0,0,.4);
            background-color: coral;';
            if (isset($options['label_color'])){
               echo 'fill: '.$options['label_color'].';';
            } else {
               echo 'fill: rgba(0,0,0,.4);'; 
            }              
            echo 'font-size: .75rem;
            line-height: 1;
          }

          .ct-fill-donut-label i { 
              font-size: 1.5em;';
              if (isset($options['label_color'])){
               echo 'color: '.$options['label_color'].';';
            } else {
               echo 'color: rgba(0,0,0,.4);'; 
            }              

              
          echo '}
      </style>';
          
          echo "<script type='text/javascript'>
          $(function() {

                   
             var chart = new Chartist.Pie('#".$title."', {
                series: [".$series."],
                labels: [".$labels."]
            }, {
                donut: true,
                donutWidth: 20,
                startAngle: 210,
                total: 100,
                showLabel: true,
                plugins: [
                    Chartist.plugins.fillDonut({
                        items: [{
                            content: '<i class=\"fa fa-tachometer text-muted\"></i>',
                            position: 'bottom',
                            offsetY : 10,
                            offsetX: -2
                        }, {
                            content: '<h3>".$value." <span class=\"small\">".$title."</span></h3>'
                        }]
                    })
                ],
            });

            chart.on('draw', function(data) {
                if(data.type === 'slice' && data.index == 0) {
                    // Get the total path length in order to use for dash array animation
                    var pathLength = data.element._node.getTotalLength();

                    // Set a dasharray that matches the path length as prerequisite to animate dashoffset
                    data.element.attr({
                        'stroke-dasharray': pathLength + 'px ' + pathLength + 'px'
                    });

                    // Create animation definition while also assigning an ID to the animation for later sync usage
                    var animationDefinition = {
                        'stroke-dashoffset': {
                            id: 'anim' + data.index,
                            dur: 1200,
                            from: -pathLength + 'px',
                            to:  '0px',
                            easing: Chartist.Svg.Easing.easeOutQuint,
                            // We need to use `fill: 'freeze'` otherwise our animation will fall back to initial (not visible)
                            fill: 'freeze'
                        }
                    };

                    // We need to set an initial value before the animation starts as we are not in guided mode which would do that for us
                    data.element.attr({
                        'stroke-dashoffset': -pathLength + 'px'
                    });

                    // We can't use guided mode as the animations need to rely on setting begin manually
                    // See gionkunz.github.io/chartist-js/api-documentation.html#chartistsvg-function-animate
                    data.element.animate(animationDefinition, true);
                }
            });                
              
          }); </script>";


   }

   /**
    * Generates te CSV file
    *
    * @param array  $labels  Labels
    * @param array  $series  Series
    * @param array  $options Options
    *
    * @return string filename
    */
  /* private function generateCsvFile($labels, $series, $options = []) {
      $uid = Session::getLoginUserID(false);
      $csvfilename = $uid.'_'.mt_rand().'.csv';

      // Render CSV
      if ($fp = fopen(GLPI_GRAPH_DIR.'/'.$csvfilename, 'w')) {
         // reformat datas
         $values  = [];
         $headers = [];
         $row_num = 0;
         foreach ($series as $serie) {
            $data = $serie['data'];
            //$labels[$row_num] = $label;
            if (is_array($data) && count($data)) {
               $headers[$row_num] = $serie['name'];
               foreach ($data as $key => $val) {
                  if (!isset($values[$key])) {
                     $values[$key] = [];
                  }
                  if (isset($options['datatype']) && $options['datatype'] == 'average') {
                     $val = round($val, 2);
                  }
                  $values[$key][$row_num] = $val;
               }
            } else {
               $values[$serie['name']][] = $data;
            }
            $row_num++;
         }
         ksort($values);

         if (!count($headers) && $options['title']) {
            $headers[] = $options['title'];
         }

         // Print labels
         fwrite($fp, $_SESSION["glpicsv_delimiter"]);
         foreach ($headers as $val) {
            fwrite($fp, $val.$_SESSION["glpicsv_delimiter"]);
         }
         fwrite($fp, "\n");

         //print values
         foreach ($values as $key => $data) {
            fwrite($fp, $key.$_SESSION["glpicsv_delimiter"]);
            foreach ($data as $value) {
               fwrite($fp, $value.$_SESSION["glpicsv_delimiter"]);
            }
            fwrite($fp, "\n");
         }

         fclose($fp);
         return $csvfilename;
      }
      return false;
   }	 
			
			/**
    * Display pie graph
    *
    * @param string   $title  Graph title
    * @param string[] $labels Labels to display
    * @param array    $series Series data. An array of the form:
    *                 [
    *                    ['name' => 'a name', 'data' => []],
    *                    ['name' => 'another name', 'data' => []]
    *                 ]
    * @param array    $options  Options
    * @param boolean  $display  Whether to display directly; defauts to true
    *
    * @return void
    */
	/*	public function displayPieGraph($title, $labels, $series, $options = [], $display = true) {
      global $CFG_GLPI;
			
      $param = [
         'csv'     => true
      ];

      if (is_array($options) && count($options)) {
         foreach ($options as $key => $val) {
            $param[$key] = $val;
         }
      }

      $slug = str_replace('-', '_', Toolbox::slugify($title));
      $this->checkEmptyLabels($labels);
      $out = "<h2 class='center'>$title";
      if ($param['csv']) {
         $options['title'] = $title;
         $csvfilename = $this->generateCsvFile($labels, $series, $options);
         $out .= " <a href='".$CFG_GLPI['root_doc'].
            "/front/graph.send.php?file=$csvfilename' title='".__s('CSV').
            "' class='pointer fa fa-file-alt'><span class='sr-only'>".__('CSV').
            "</span></a>";
      }
      $out .= "</h2>";
      $out .= "<div id='$slug' class='chart'></div>";
      $out .= "<script type='text/javascript'>
                  $(function() {
                     var $slug = new Chartist.Pie('#$slug', {
                        labels: ['" . implode('\', \'', Toolbox::addslashes_deep($labels))  . "'],
                        series: [";

      $first = true;
      foreach ($series as $serie) {
         if ($first === true) {
            $first = false;
         } else {
            $out .= ",\n";
         }

         $serieLabel = Toolbox::addslashes_deep($serie['name']);
         $serieData = $serie['data'];
         $out .= "{'meta': '$serieLabel', 'value': '$serieData'}";
      }
      Toolbox::logInFile("procedimientos", " zzzz: " . print_r($options, TRUE) . "\r\n\r\n");
      $out .= "
                        ]
                     }, {
                        donut: true,
                        showLabel: false,
                        height: 300,
                        width: 300,
                        plugins: [
                           Chartist.plugins.legend(),
                           Chartist.plugins.tooltip()
                        ]
                     });

                     $slug.on('draw', function(data) {
                        if(data.type === 'slice') {
                           // Get the total path length in order to use for dash array animation
                           var pathLength = data.element._node.getTotalLength();

                           // Set a dasharray that matches the path length as prerequisite to animate dashoffset
                           data.element.attr({
                              'stroke-dasharray': pathLength + 'px ' + pathLength + 'px'
                           });

                           // Create animation definition while also assigning an ID to the animation for later sync usage
                           var animationDefinition = {
                              'stroke-dashoffset': {
                                 id: 'anim' + data.index,
                                 dur: 300,
                                 from: -pathLength + 'px',
                                 to:  '0px',
                                 easing: Chartist.Svg.Easing.easeOutQuint,
                                 // We need to use `fill: 'freeze'` otherwise our animation will fall back to initial (not visible)
                                 fill: 'freeze'
                              }
                           };

                           // We need to set an initial value before the animation starts as we are not in guided mode which would do that for us
                           data.element.attr({
                              'stroke-dashoffset': -pathLength + 'px'
                           });

                           // We can't use guided mode as the animations need to rely on setting begin manually
                           // See http://gionkunz.github.io/chartist-js/api-documentation.html#chartistsvg-function-animate
                           data.element.animate(animationDefinition, false);
                        }
                     });
                  });
              </script>";

      if ($display) {
         echo $out;
         return;
      }
      return $out;
   }			*/
		
   static function install(Migration $migration) {
      global $DB, $CFG_GLPI;
      $table = getTableForItemType(__CLASS__);
      if (!$DB->TableExists($table)) {	  
		  
         $query = "CREATE TABLE IF NOT EXISTS `$table` (
         `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
		   `".PluginContextualMessage_User::$items_id_1."` BIGINT UNSIGNED NOT NULL DEFAULT '0',
		   `".PluginContextualMessage_User::$items_id_2."` BIGINT UNSIGNED NOT NULL DEFAULT '0',
         `view` BIGINT UNSIGNED NOT NULL DEFAULT '0',
         `date_creation` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,         
		   PRIMARY KEY (`id`),
		   UNIQUE KEY `unicity` (`".PluginContextualMessage_User::$items_id_1."`,`".PluginContextualMessage_User::$items_id_2."`)
		   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
		   
         $DB->doQuery($query) or die ("Error adding table $table");
		 
		 		$tabla='
			  <tr>
				<td colspan="2" align="left">&nbsp;&nbsp;<img style="vertical-align:middle;" alt="" src="'.$CFG_GLPI['root_doc'].'/plugins/contextual/img/check.png">&nbsp;
				&nbsp;<strong><FONT color="#3a9b26">'.$table.'</FONT>.</strong>				
				</td>
			  </tr>';
				 
		 Session::addMessageAfterRedirect($tabla);	
		 
      }
   }   

   function updateCounter($id) {
		global $DB;

		//update counter view
		$DB->update(
			getTableForItemType(__CLASS__), [
					'view'   => new \QueryExpression($DB->quoteName('view') . ' + 1')
			 ], [
               PluginContextualMessage_User::$items_id_1 => $id,
               PluginContextualMessage_User::$items_id_2 => $_SESSION['glpiID']
			 ]
		);
   }  
   

   function addItem($values) {

      $this->add($values);
    
   }   

// [INICIO] [CRI] JMZ18G FUNCIÓN NECESARIA PARA PODER FILTRAR POR EL CAMPO LEIDO.
   static function getNameField() {
      return 'id';
   }
// [FINAL] [CRI] JMZ18G FUNCIÓN NECESARIA PARA PODER FILTRAR POR EL CAMPO LEIDO.   

/*
// NO ES NECESARIO PORQUE EL CAMPO VIEW ESTA DEFINIDO COMO BOOLEANO Y YA A APRECE 
// EN EL LISTADO CUANDO SE FILTRA COMO SI / NO
   /**
	* @since version 0.84
	*
	* @param $field
	* @param $values
	* @param $options   array
 **/
/*
 static function getSpecificValueToDisplay($field, $values, array $options=array()) {
	
	 Toolbox::logInFile("procedimientos", " USERS: " . print_r($values, TRUE) . "\r\n\r\n");
		if (!is_array($values)) {
			 $values = array($field => $values);
		}

		switch ($field) {
			 case 'view' :
            Toolbox::logInFile("procedimientos", " values: " . print_r($values, TRUE) . "\r\n\r\n");
            IF ($values['view'] > 0) {
				   return "<font color='green'><span class='fa fa-eye'></span></font> <strong>SI</strong>"; 
            } else {
               return "<font color='red'><span class='fa fa-eye'></span></font> <strong>NO</strong>"; 
            }
			 break;
		}
		return parent::getSpecificValueToDisplay($field, $values, $options);
 }
*/

}
?>