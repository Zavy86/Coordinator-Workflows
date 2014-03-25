<?php
/* -------------------------------------------------------------------------- *\
|* -[ Workflows - Widget ]--------------------------------------------------- *|
\* -------------------------------------------------------------------------- */
require_once("../core/api.inc.php");
// load module language file
api_loadLocaleFile("../workflows/");
// include module api
require_once("../workflows/api.inc.php");
// include registries api
require_once("../registries/api.inc.php");
// include materials api
require_once("../materials/api.inc.php");
// widget title and well
echo "<h4>".api_text("widget-title")."</h4>\n";
echo "<div class='well well-small well-white'>\n";

// -- pasted

// definitions
$details_modals_array=array();
// acquire workflows status filter
$workflows_query="(status='1' OR status='2' OR status='3')";
// check for personal workflows
if($GLOBALS['db']->countOf("workflows_workflows","addIdAccount='".$_SESSION['account']->id."' AND ".$workflows_query)>0){
 // build workflows table
 $workflows_table=new str_table(api_text("flows-tr-workflowsUnvalued"),TRUE);
 $workflows_table->addHeader("&nbsp;",NULL,"16");
 $workflows_table->addHeader(api_text("flows-th-idWorkflow"),"nowarp");
 $workflows_table->addHeader("&nbsp;",NULL,"16");
 $workflows_table->addHeader(api_text("flows-th-timestamp"),"nowarp");
 $workflows_table->addHeader("!","nowarp text-center");
 $workflows_table->addHeader(api_text("flows-th-category"),"nowarp");
 $workflows_table->addHeader(api_text("flows-th-subject"),NULL,"100%");
 // build workflow table rows
 $workflows=$GLOBALS['db']->query("SELECT * FROM workflows_workflows WHERE addIdAccount='".$_SESSION['account']->id."' AND ".$workflows_query." ORDER BY addDate DESC");
 while($workflow=$GLOBALS['db']->fetchNextObject($workflows)){
  $workflows_table->addRow();
  // build workflows table fields
  $workflows_table->addField("<a href='../workflows/workflows_view.php?id=".$workflow->id."'>".api_icon("icon-search")."</a>","nowarp");
  $workflows_table->addField(str_pad($workflow->id,5,"0",STR_PAD_LEFT),"nowarp");
  $workflows_table->addField(api_workflows_status($workflow->status,TRUE),"nowarp text-center");
  $workflows_table->addField(api_timestampFormat($workflow->addDate,api_text("datetime")),"nowarp");
  $workflows_table->addField($workflow->priority,"nowarp text-center");
  $workflows_table->addField(api_workflows_categoryName($workflow->idCategory,TRUE,TRUE,TRUE),"nowarp");
  $workflows_table->addField(stripslashes($workflow->subject));
 }
}
// check for assignables tickets
//if($GLOBALS['db']->countOf("workflows_tickets",$query_where)>0){
 // build tickets table
 $tickets_table=new str_table(api_text("flows-tr-ticketsUnvalued"),TRUE);
 $tickets_table->addHeader("&nbsp;",NULL,"16");
 $tickets_table->addHeader(api_text("flows-th-idTicket"),"nowarp");
 $tickets_table->addHeader("&nbsp;",NULL,"16");
 $tickets_table->addHeader(api_text("flows-th-timestamp"),"nowarp");
 $tickets_table->addHeader(api_text("flows-th-sla"),"nowarp text-center");
 $tickets_table->addHeader("!","nowarp text-center");
 $tickets_table->addHeader(api_text("flows-th-account"),"nowarp");
 $tickets_table->addHeader(api_text("flows-th-category"),"nowarp");
 $tickets_table->addHeader(api_text("flows-th-subject"),NULL,"100%");
 $tickets_table->addHeader(api_text("flows-th-assigned"),"nowarp");
 $tickets_table->addHeader(api_text("flows-th-group"),"nowarp text-center");
 $tickets_table->addHeader("&nbsp;",NULL,"16");
 // generate tickets query
 $query_where="(status='1' OR status='2' OR status='3')";
 // build tickets table rows
 $tickets=$GLOBALS['db']->query("SELECT * FROM workflows_tickets WHERE ".$query_where." ORDER BY addDate DESC");
 while($ticket=$GLOBALS['db']->fetchNextObject($tickets)){
  $tickets_table->addRow();
  // assigned id
  if(!$ticket->idAssigned){$ticket->idAssigned=0;}
  // details modal windows
  $details_modal=api_workflows_ticketDetailsModal($ticket);
  $details_modals_array[]=$details_modal;
  // build tickets table fields
  $tickets_table->addField("<a href='../workflows/workflows_view.php?id=".$ticket->idWorkflow."&idTicket=".$ticket->id."'>".api_icon("icon-search")."</a>","nowarp");
  $tickets_table->addField(str_pad($ticket->idWorkflow,5,"0",STR_PAD_LEFT)."-".str_pad($ticket->id,5,"0",STR_PAD_LEFT),"nowarp");
  $tickets_table->addField(api_workflows_status($ticket->status,TRUE),"nowarp text-center");
  $tickets_table->addField(api_timestampFormat($ticket->addDate,api_text("datetime")),"nowarp");
  $tickets_table->addField(api_workflows_ticketSLA($ticket),"nowarp text-center");
  $tickets_table->addField($ticket->priority,"nowarp text-center");
  $tickets_table->addField(api_accountFirstname($ticket->addIdAccount),"nowarp");
  $tickets_table->addField(api_workflows_categoryName($ticket->idCategory,TRUE,TRUE,TRUE),"nowarp");
  $tickets_table->addField(stripslashes($ticket->subject));
  $tickets_table->addField(api_accountFirstname($ticket->idAssigned),"nowarp text-right");
  $tickets_table->addField(api_groupName($ticket->idGroup,TRUE,TRUE),"nowarp text-center");
  $tickets_table->addField($details_modal->link(api_icon("icon-list")),"nowarp text-center");
 }
//}
// show workflows table
if(is_object($workflows_table)){
 echo "<h5>".api_text("flows-workflows")."</h5>\n";
 $workflows_table->render();
}
if(is_object($workflows_table) && is_object($tickets_table)){echo "<hr>\n";}
// show tickets table
if(is_object($tickets_table)){
 echo "<h5>".api_text("flows-tickets")."</h5>\n";
 $tickets_table->render();
}
// show the pagination
if(is_object($pagination)){$pagination->render();}
// show status modal windows
foreach($details_modals_array as $modal){$modal->render();}

// -- pasted

echo "<span class='pull-right'>\n";
echo "<a href='../workflows/index.php'>Visualizza tutti i ticket</a>\n";
echo "</span>\n";
echo "<br>\n</div>\n";
?>