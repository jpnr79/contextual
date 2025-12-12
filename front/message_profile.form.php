<?php
declare(strict_types=1);

/*
   ----------------------------------------------------------
   Plugin Procedimientos 2.2.1
   GLPI 0.85.5
  
   Autor: Elena Martínez Ballesta.
   Fecha: Septiembre 2016

   ----------------------------------------------------------
 */

include ("../../../inc/includes.php");
                       

if (!isset($_GET["id"])) $_GET["id"] = "";
if (!isset($_GET["withtemplate"])) $_GET["withtemplate"] = "";


$PluginContextualMessage_Profile = new PluginContextualMessage_Profile();

 if (isset($_POST["addform"])) {   
   if ($_POST['profiles_id']>0) {
       $PluginContextualMessage_Profile ->addItem($_POST);
   }
   Html::back();  
} else if (isset($_GET["import_form"])) {
     // Import form
      Session::checkRight("entity", UPDATE);

         Html::header(__('contextual', 'contextual'), $_SERVER['PHP_SELF'] ,"admin", "PluginContextualMessage", "contextual");

      if (version_compare(GLPI_VERSION, '9.2', 'ge')) {
         Html::requireJs('fileupload');
      }

      $PluginContextualMessage_Profile->showImportForm();
      Html::footer();
      
} else if (isset($_POST["import_send"])) {
      // Import form
      Session::checkRight("entity", UPDATE);
      $PluginContextualMessage_Profile->importJson($_REQUEST);
      Html::back();

} else if (isset($_POST["elimina"])){
	$query= "delete from glpi_plugin_contextual_messages_profiles where plugin_contextual_messages_id=".$_POST["plugin_procedimientos_procedimientos_id"]."
			 and profiles_id=".$_POST["elimina"];
    $DB->query($query);
	Html::back();

} else {
	  
	Html::header(__('contextual', 'contextual'), $_SERVER['PHP_SELF'] ,"admin", "PluginContextualMessage", "contextual");
			   
   $PluginContextualMessage_Profile->display($_GET["id"]);
   Html::footer();
   
}
?>
