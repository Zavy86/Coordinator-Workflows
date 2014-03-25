<?php
/* -------------------------------------------------------------------------- *\
|* -[ Workflows - List ]----------------------------------------------------- *|
\* -------------------------------------------------------------------------- */
$checkPermission="workflows_view";
include("template.inc.php");
function content(){
 // definitions
 $details_modals_array=array();
 // acquire variabled
 $g_page=$_GET['p'];
 if(!$g_page){$g_page=1;}
 // show filters
 echo $GLOBALS['navigation']->filtersText();
 // acquire workflows status filter
 $workflows_query=$GLOBALS['navigation']->filtersParameterQuery("status","1");
 // check for personal workflows
 if($g_page==1 && $GLOBALS['db']->countOf("workflows_workflows","addIdAccount='".$_SESSION['account']->id."' AND ".$workflows_query)>0){
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
   $workflows_table->addField("<a href='workflows_view.php?id=".$workflow->id."'>".api_icon("icon-search")."</a>","nowarp");
   $workflows_table->addField(str_pad($workflow->id,5,"0",STR_PAD_LEFT),"nowarp");
   $workflows_table->addField(api_workflows_status($workflow->status,TRUE),"nowarp text-center");
   $workflows_table->addField(api_timestampFormat($workflow->addDate,api_text("datetime")),"nowarp");
   $workflows_table->addField($workflow->priority,"nowarp text-center");
   $workflows_table->addField(api_workflows_categoryName($workflow->idCategory,TRUE,TRUE,TRUE),"nowarp");
   $workflows_table->addField(stripslashes($workflow->subject));
  }
 }
 // generate tickets query
 $query_where=$GLOBALS['navigation']->filtersQuery("1");
 // only assignable tickets
 $query_where.=" AND ( ";
 $query_where.=" idAssigned='".$_SESSION['account']->id."'";
 foreach(api_accountGroups() as $group){
  if($group->grouprole>2){$query_where.=" OR idGroup='".$group->id."'";}
 }
 if(api_accountGroupMember(api_groupId("SIS"))){$query_where.=" OR idGroup='0'";}
 $query_where.=" )";
 // order tickets
 $query_order=api_queryOrder("addDate DESC");
 // pagination
 $pagination=new str_pagination("workflows_tickets",$query_where,$GLOBALS['navigation']->filtersGet());
 $query_limit=$pagination->queryLimit();
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
 // build tickets table rows
 $tickets=$GLOBALS['db']->query("SELECT * FROM workflows_tickets WHERE ".$query_where.$query_order.$query_limit);
 while($ticket=$GLOBALS['db']->fetchNextObject($tickets)){
  $tickets_table->addRow();
  // assigned id
  if(!$ticket->idAssigned){$ticket->idAssigned=0;}
  // details modal windows
  $details_modal=api_workflows_ticketDetailsModal($ticket);
  $details_modals_array[]=$details_modal;
  // build tickets table fields
  $tickets_table->addField("<a href='workflows_view.php?id=".$ticket->idWorkflow."&idTicket=".$ticket->id."'>".api_icon("icon-search")."</a>","nowarp");
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
?>
<script type="text/javascript">
 // refresh page every 5 minutes
 window.setTimeout(function(){document.location.reload(true);},300000);
</script>
<?php } ?>
