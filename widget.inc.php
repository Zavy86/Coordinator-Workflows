<?php
/* -------------------------------------------------------------------------- *\
|* -[ Workflows - Widget ]--------------------------------------------------- *|
\* -------------------------------------------------------------------------- */
require_once("../core/api.inc.php");
// load module language file
api_loadLocaleFile("../workflows/");
// include module api
require_once("../workflows/api.inc.php");
/*// include registries api
require_once("../registries/api.inc.php");
// include materials api
require_once("../materials/api.inc.php");*/
// widget title and well
echo "<h4>".api_text("widget-title")."</h4>\n";
echo "<div class='well well-small well-white'>\n";

// acquire variables
$span=$_GET['span'];


// if span < 6 build small widget
if($span<6){
 // check personal workflows
 $personal_workflows=NULL;
 // opened
 $personal_workflows_opened=$GLOBALS['db']->countOf("workflows_workflows","addIdAccount='".api_account()->id."' AND status='1'");
 if($personal_workflows_opened){$personal_workflows.="<p>".api_workflows_status(1,TRUE)." ".api_text("widget-workflows-opened").": ".number_format($personal_workflows_opened,0,",",".")."</p>\n";}
 // assigned
 $personal_workflows_assigned=$GLOBALS['db']->countOf("workflows_workflows","addIdAccount='".api_account()->id."' AND status='2'");
 if($personal_workflows_assigned){$personal_workflows.="<p>".api_workflows_status(2,TRUE)." ".api_text("widget-workflows-assigned").": ".number_format($personal_workflows_assigned,0,",",".")."</p>\n";}
 // check pocessable tickets
 $processable_tickets=NULL;
 // opened
 $processable_tickets_where.="idAssigned='".api_account()->id."'";
 foreach(api_account()->companies[api_company()->id]->groups as $group){$processable_tickets_where.=" OR idGroup='".$group->id."'";}
 if(api_accountGroupMember(1)){$processable_tickets_where.=" OR idGroup='0'";}
 $processable_tickets_opened=$GLOBALS['db']->countOf("workflows_tickets","status='1' AND ( ".$processable_tickets_where." )");
 if($processable_tickets_opened){$processable_tickets.="<p>".api_workflows_status(1,TRUE)." ".api_text("widget-tickets-opened").": ".number_format($processable_tickets_opened,0,",",".")."</p>\n";}
 // assigned
 $processable_tickets_assigned=$GLOBALS['db']->countOf("workflows_tickets","status='2' AND idAssigned='".api_account()->id."'");
 if($processable_tickets_assigned){$processable_tickets.="<p>".api_workflows_status(2,TRUE)." ".api_text("widget-tickets-assigned").": ".number_format($processable_tickets_assigned,0,",",".")."</p>\n";}
 // stanbdy
 $processable_tickets_standby=$GLOBALS['db']->countOf("workflows_tickets","status='3' AND idAssigned='".api_account()->id."'");
 if($processable_tickets_standby){$processable_tickets.="<p>".api_workflows_status(3,TRUE)." ".api_text("widget-tickets-standby").": ".number_format($processable_tickets_standby,0,",",".")."</p>\n";}
 // closed
 $processable_tickets_closed=$GLOBALS['db']->countOf("workflows_tickets","status='4' AND idAssigned='".api_account()->id."'");
 if($processable_tickets_closed){$processable_tickets.="<p>".api_workflows_status(4,TRUE)." ".api_text("widget-tickets-closed").": ".number_format($processable_tickets_closed,0,",",".")."</p>\n";}
 // check for null
 if($processable_tickets==NULL){$processable_tickets="<p>".api_text("widget-tickets-null")."</p>\n";}
 // show personal workflows counters
 if($personal_workflows){echo "<h5>".api_text("widget-workflows")."</h5>\n".$personal_workflows;}
 // show processable tickets counters
 echo "<h5>".api_text("widget-tickets")."</h5>\n".$processable_tickets;
}else{
 // if span > 6 build large widget

// definitions
$details_modals_array=array();
// acquire workflows status filter
$workflows_query="(status='1' OR status='2' OR status='3')";
// check for personal workflows
if($GLOBALS['db']->countOf("workflows_workflows","addIdAccount='".$_SESSION['account']->id."' AND ".$workflows_query)>0){
 // build workflows table
 $workflows_table=new str_table(api_text("flows-tr-workflowsUnvalued"),TRUE);
 $workflows_table->addHeader("&nbsp;",NULL,"16");
 $workflows_table->addHeader(api_text("flows-th-timestamp"),"nowarp");
 $workflows_table->addHeader("&nbsp;",NULL,"16");
 if($span>8){$workflows_table->addHeader("!","nowarp text-center");}
 if($span>8){$workflows_table->addHeader(api_text("flows-th-category"),"nowarp");}
 $workflows_table->addHeader(api_text("flows-th-subject"),NULL,"100%");
 // build workflow table rows
 $workflows=$GLOBALS['db']->query("SELECT * FROM workflows_workflows WHERE addIdAccount='".$_SESSION['account']->id."' AND ".$workflows_query." ORDER BY addDate DESC");
 while($workflow=$GLOBALS['db']->fetchNextObject($workflows)){
  $workflows_table->addRow();
  // build workflows table fields
  $workflows_table->addField("<a href='../workflows/workflows_view.php?id=".$workflow->id."'>".api_icon("icon-search")."</a>","nowarp");
  $workflows_table->addField(api_timestampFormat($workflow->addDate,api_text("date")),"nowarp");
  $workflows_table->addField(api_workflows_status($workflow->status,TRUE),"nowarp text-center");
  if($span>8){$workflows_table->addField($workflow->priority,"nowarp text-center");}
  if($span>8){$workflows_table->addField(api_workflows_categoryName($workflow->idCategory,TRUE,TRUE,TRUE),"nowarp");}
  $workflows_table->addField(stripslashes($workflow->subject));
 }
}
// generate tickets query
$query_where="(status='1' OR status='2' OR status='3')";
// only assignable tickets
$query_where.=" AND ( ";
$query_where.=" idAssigned='".$_SESSION['account']->id."'";
foreach(api_account()->companies[api_company()->id]->groups as $group){$query_where.=" OR idGroup='".$group->id."'";}
if(api_accountGroupMember(1)){$query_where.=" OR idGroup='0'";}
$query_where.=" )";
// build tickets table
$tickets_table=new str_table(api_text("flows-tr-ticketsUnvalued"),TRUE);
$tickets_table->addHeader("&nbsp;",NULL,"16");
$tickets_table->addHeader(api_text("flows-th-timestamp"),"nowarp");
$tickets_table->addHeader("&nbsp;",NULL,"16");
if($span>8){$tickets_table->addHeader(api_text("flows-th-sla"),"nowarp text-center");}
if($span>8){$tickets_table->addHeader("!","nowarp text-center");}
$tickets_table->addHeader(api_text("flows-th-account"),"nowarp");
if($span>8){$tickets_table->addHeader(api_text("flows-th-category"),"nowarp");}
$tickets_table->addHeader(api_text("flows-th-subject"),NULL,"100%");
if($span>8){$tickets_table->addHeader(api_text("flows-th-assigned"),"nowarp");}
$tickets_table->addHeader(api_text("flows-th-group"),"nowarp text-center");
$tickets_table->addHeader("&nbsp;",NULL,"16");
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
 if($span>8){$tickets_table->addField(api_timestampFormat($ticket->addDate,api_text("datetime")),"nowarp");}
  else{$tickets_table->addField(api_timestampFormat($ticket->addDate,api_text("datetime-noyear")),"nowarp");}
 $tickets_table->addField(api_workflows_status($ticket->status,TRUE,$ticket->solved),"nowarp text-center");
 if($span>8){$tickets_table->addField(api_workflows_ticketSLA($ticket),"nowarp text-center");}
 if($span>8){$tickets_table->addField($ticket->priority,"nowarp text-center");}
 $tickets_table->addField(api_account($ticket->addIdAccount)->firstname,"nowarp");
 if($span>8){$tickets_table->addField(api_workflows_categoryName($ticket->idCategory,TRUE,TRUE,TRUE),"nowarp");}
 $tickets_table->addField(stripslashes($ticket->subject));
 if($span>8){$tickets_table->addField(api_account($ticket->idAssigned)->firstname,"nowarp text-right");}
 $tickets_table->addField(api_groupName($ticket->idGroup,TRUE,TRUE),"nowarp text-center");
 $tickets_table->addField($details_modal->link(api_icon("icon-list")),"nowarp text-center");
}


 if($span<8){



 }else{



 }


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

}

echo "<span class='pull-right'>\n";
echo "<a href='../workflows/index.php'>".api_text("widget-showAll")."</a>\n";
echo "</span>\n";
echo "<br>\n</div>\n";
?>