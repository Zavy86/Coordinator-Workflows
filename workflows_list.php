<?php
/* -------------------------------------------------------------------------- *\
|* -[ Workflows - List ]----------------------------------------------------- *|
\* -------------------------------------------------------------------------- */
header("refresh:300;");
$checkPermission="workflows_view";
require_once("template.inc.php");
function content(){
 // definitions
 $details_modals_array=array();
 $tickets_array=array();
 // acquire variabled
 $g_page=$_GET['p'];
 if(!$g_page){$g_page=1;}
 // show filters
 echo $GLOBALS['navigation']->filtersText();

 // generate tickets query
 //$query_where=$GLOBALS['navigation']->filtersQuery("1");
 $query_where=$GLOBALS['navigation']->filtersParameterQuery("status","1");
 $query_where.=" AND ".$GLOBALS['navigation']->filtersParameterQuery("idCategory","1");
 $query_where.=" AND ".$GLOBALS['navigation']->filtersParameterQuery("addIdAccount","1");
 $query_where.=" AND ".$GLOBALS['navigation']->filtersParameterQuery("idAssigned","1");
 // if all ticket is checked
 if($GLOBALS['navigation']->filtersParameterQuery("show")=="show='1'"){
  // show all tickets
 }else{
  // only assignable tickets
  $query_where.=" AND ( ";
  $query_where.=" idAssigned='".$_SESSION['account']->id."'";
  foreach(api_accountGroups() as $group){
   if($group->grouprole>1){$query_where.=" OR idGroup='".$group->id."'";}
  }
  if(api_accountGroupMember(api_groupId("SIS"))){$query_where.=" OR idGroup='0' OR idGroup IS NULL";}
  $query_where.=" )";
 }
 // order tickets
 $query_order=api_queryOrder("status ASC,addDate DESC");

 // acquire tickets
 $tickets=$GLOBALS['db']->query("SELECT * FROM workflows_tickets WHERE ".$query_where.$query_order);
 while($ticket=$GLOBALS['db']->fetchNextObject($tickets)){$tickets_array[]=$ticket;}

 // build tickets table
 $tickets_table=new str_table(api_text("flows-tr-ticketsUnvalued"),TRUE);
 $tickets_table->addHeader("&nbsp;",NULL,"16");
 $tickets_table->addHeader(api_text("flows-th-idTicket"),"nowarp",NULL,"id");
 $tickets_table->addHeader("&nbsp;",NULL,"16");
 $tickets_table->addHeader(api_text("flows-th-timestamp"),"nowarp",NULL,"addDate");
 $tickets_table->addHeader(api_text("flows-th-sla"),"nowarp text-center",NULL);
 $tickets_table->addHeader("!","nowarp text-center",NULL,"priority");
 $tickets_table->addHeader(api_text("flows-th-account"),"nowarp");
 $tickets_table->addHeader(api_text("flows-th-category"),"nowarp");
 $tickets_table->addHeader(api_text("flows-th-subject"),NULL,"100%","subject");
 $tickets_table->addHeader(api_text("flows-th-assigned"),"nowarp");
 $tickets_table->addHeader(api_text("flows-th-group"),"nowarp text-center");
 $tickets_table->addHeader("&nbsp;",NULL,"16");
 // build tickets table rows
 foreach($tickets_array as $ticket){
  //
  $tickets_table->addRow();
  // assigned id
  if(!$ticket->idAssigned){$ticket->idAssigned=0;}
  // details modal windows
  $details_modal=api_workflows_ticketDetailsModal($ticket);
  $details_modals_array[]=$details_modal;
  // build tickets table fields
  $tickets_table->addField("<a href='workflows_view.php?id=".$ticket->idWorkflow."&idTicket=".$ticket->id."'>".api_icon("icon-search")."</a>","nowarp");
  $tickets_table->addField(str_pad($ticket->idWorkflow,5,"0",STR_PAD_LEFT)."-".str_pad($ticket->id,5,"0",STR_PAD_LEFT),"nowarp");
  $tickets_table->addField(api_workflows_status($ticket->status,TRUE,$ticket->solved),"nowarp text-center");
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

 /*// build tickets table
 $assigned_tickets_table=new str_table(api_text("flows-tr-ticketsUnvalued"),TRUE);
 $assigned_tickets_table->addHeader("&nbsp;",NULL,"16");
 $assigned_tickets_table->addHeader(api_text("flows-th-idTicket"),"nowarp",NULL,"id");
 $assigned_tickets_table->addHeader("&nbsp;",NULL,"16");
 $assigned_tickets_table->addHeader(api_text("flows-th-timestamp"),"nowarp",NULL,"addDate");
 $assigned_tickets_table->addHeader(api_text("flows-th-sla"),"nowarp text-center",NULL);
 $assigned_tickets_table->addHeader("!","nowarp text-center",NULL,"priority");
 $assigned_tickets_table->addHeader(api_text("flows-th-account"),"nowarp");
 $assigned_tickets_table->addHeader(api_text("flows-th-category"),"nowarp");
 $assigned_tickets_table->addHeader(api_text("flows-th-subject"),NULL,"100%","subject");
 $assigned_tickets_table->addHeader(api_text("flows-th-assigned"),"nowarp");
 $assigned_tickets_table->addHeader(api_text("flows-th-group"),"nowarp text-center");
 $assigned_tickets_table->addHeader("&nbsp;",NULL,"16");
 // build tickets table rows
 foreach($tickets_array as $ticket){
  //
  if($ticket->idAssigned<>api_accountId()){continue;}
  //
  $assigned_tickets_table->addRow();
  // assigned id
  if(!$ticket->idAssigned){$ticket->idAssigned=0;}
  // details modal windows
  $details_modal=api_workflows_ticketDetailsModal($ticket);
  $details_modals_array[]=$details_modal;
  // build tickets table fields
  $assigned_tickets_table->addField("<a href='workflows_view.php?id=".$ticket->idWorkflow."&idTicket=".$ticket->id."'>".api_icon("icon-search")."</a>","nowarp");
  $assigned_tickets_table->addField(str_pad($ticket->idWorkflow,5,"0",STR_PAD_LEFT)."-".str_pad($ticket->id,5,"0",STR_PAD_LEFT),"nowarp");
  $assigned_tickets_table->addField(api_workflows_status($ticket->status,TRUE,$ticket->solved),"nowarp text-center");
  $assigned_tickets_table->addField(api_timestampFormat($ticket->addDate,api_text("datetime")),"nowarp");
  $assigned_tickets_table->addField(api_workflows_ticketSLA($ticket),"nowarp text-center");
  $assigned_tickets_table->addField($ticket->priority,"nowarp text-center");
  $assigned_tickets_table->addField(api_accountFirstname($ticket->addIdAccount),"nowarp");
  $assigned_tickets_table->addField(api_workflows_categoryName($ticket->idCategory,TRUE,TRUE,TRUE),"nowarp");
  $assigned_tickets_table->addField(stripslashes($ticket->subject));
  $assigned_tickets_table->addField(api_accountFirstname($ticket->idAssigned),"nowarp text-right");
  $assigned_tickets_table->addField(api_groupName($ticket->idGroup,TRUE,TRUE),"nowarp text-center");
  $assigned_tickets_table->addField($details_modal->link(api_icon("icon-list")),"nowarp text-center");
 }


 // build tickets table
 $tickets_table=new str_table(api_text("flows-tr-ticketsUnvalued"),TRUE);
 $tickets_table->addHeader("&nbsp;",NULL,"16");
 $tickets_table->addHeader(api_text("flows-th-idTicket"),"nowarp",NULL,"id");
 $tickets_table->addHeader("&nbsp;",NULL,"16");
 $tickets_table->addHeader(api_text("flows-th-timestamp"),"nowarp",NULL,"addDate");
 $tickets_table->addHeader(api_text("flows-th-sla"),"nowarp text-center",NULL);
 $tickets_table->addHeader("!","nowarp text-center",NULL,"priority");
 $tickets_table->addHeader(api_text("flows-th-account"),"nowarp");
 $tickets_table->addHeader(api_text("flows-th-category"),"nowarp");
 $tickets_table->addHeader(api_text("flows-th-subject"),NULL,"100%","subject");
 $tickets_table->addHeader(api_text("flows-th-assigned"),"nowarp");
 $tickets_table->addHeader(api_text("flows-th-group"),"nowarp text-center");
 $tickets_table->addHeader("&nbsp;",NULL,"16");
 // build tickets table rows
 foreach($tickets_array as $ticket){
  //
  if($ticket->idAssigned==api_accountId()||$ticket->status<>1){continue;}
  //
  $tickets_table->addRow();
  // assigned id
  if(!$ticket->idAssigned){$ticket->idAssigned=0;}
  // details modal windows
  $details_modal=api_workflows_ticketDetailsModal($ticket);
  $details_modals_array[]=$details_modal;
  // build tickets table fields
  $tickets_table->addField("<a href='workflows_view.php?id=".$ticket->idWorkflow."&idTicket=".$ticket->id."'>".api_icon("icon-search")."</a>","nowarp");
  $tickets_table->addField(str_pad($ticket->idWorkflow,5,"0",STR_PAD_LEFT)."-".str_pad($ticket->id,5,"0",STR_PAD_LEFT),"nowarp");
  $tickets_table->addField(api_workflows_status($ticket->status,TRUE,$ticket->solved),"nowarp text-center");
  $tickets_table->addField(api_timestampFormat($ticket->addDate,api_text("datetime")),"nowarp");
  $tickets_table->addField(api_workflows_ticketSLA($ticket),"nowarp text-center");
  $tickets_table->addField($ticket->priority,"nowarp text-center");
  $tickets_table->addField(api_accountFirstname($ticket->addIdAccount),"nowarp");
  $tickets_table->addField(api_workflows_categoryName($ticket->idCategory,TRUE,TRUE,TRUE),"nowarp");
  $tickets_table->addField(stripslashes($ticket->subject));
  $tickets_table->addField(api_accountFirstname($ticket->idAssigned),"nowarp text-right");
  $tickets_table->addField(api_groupName($ticket->idGroup,TRUE,TRUE),"nowarp text-center");
  $tickets_table->addField($details_modal->link(api_icon("icon-list")),"nowarp text-center");
 }*/

 // acquire workflows status filter
 $workflows_query=$GLOBALS['navigation']->filtersParameterQuery("status","1");
 // check for personal workflows
 if($g_page==1 && $GLOBALS['db']->countOf("workflows_workflows","addIdAccount='".$_SESSION['account']->id."' AND ".$workflows_query)>0){
  // build workflows table
  $workflows_table=new str_table(api_text("flows-tr-workflowsUnvalued"),FALSE);
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
   $workflows_table->addField("<a href='workflows_view.php?id=".$workflow->id."'>".api_icon("icon-search")."</a>","nowarp");
   $workflows_table->addField(str_pad($workflow->id,5,"0",STR_PAD_LEFT),"nowarp");
   $workflows_table->addField(api_workflows_status($workflow->status,TRUE),"nowarp text-center'");
   $workflows_table->addField(api_timestampFormat($workflow->addDate,api_text("datetime")),"nowarp");
   $workflows_table->addField($workflow->priority,"nowarp text-center");
   $workflows_table->addField(api_workflows_categoryName($workflow->idCategory,TRUE,TRUE,TRUE),"nowarp");
   $workflows_table->addField(stripslashes($workflow->subject));
  }
 }

 // show tickets table
 if(is_object($assigned_tickets_table)){
  echo "<h5>".api_text("flows-assigned-tickets")."</h5>\n";
  $assigned_tickets_table->render();
 }

 if(is_object($assigned_tickets_table) && is_object($tickets_table)){echo "<hr>\n";}

 // show tickets table
 if(is_object($tickets_table)){
  echo "<h5>".api_text("flows-tickets")."</h5>\n";
  $tickets_table->render();
 }

 if((is_object($assigned_tickets_table) || is_object($tickets_table)) && is_object($workflows_table)){echo "<hr>\n";}

 // show workflows table
 if(is_object($workflows_table)){
  echo "<h5>".api_text("flows-workflows")."</h5>\n";
  $workflows_table->render();
 }


 // show the pagination
 //if(is_object($pagination)){$pagination->render();}
 // show status modal windows
 foreach($details_modals_array as $modal){$modal->render();}
?>
<script type="text/javascript">
 // refresh page every 5 minutes
 window.setTimeout(function(){document.location.reload(true);},300000);
</script>
<?php } ?>
