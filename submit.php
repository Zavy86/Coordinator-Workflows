<?php
/* -------------------------------------------------------------------------- *\
|* -[ Workflows - Submit ]---------------------------------------------------- *|
\* -------------------------------------------------------------------------- */
include('../core/api.inc.php');
include('api.inc.php');
api_loadLocaleFile("./");
$act=$_GET['act'];
switch($act){




 // ticket
 case "ticket_solicit":ticket_solicit();break;


 // ok


 // workflows
 case "workflow_save":workflow_save();break;
 // tickets
 case "ticket_save":ticket_save();break;
 case "ticket_assign":ticket_assign();break;
 case "ticket_process":ticket_process();break;
 // categories
 case "category_save":category_save();break;
 // flows
 case "flow_save":flow_save();break;
 case "flow_field_save":flow_field_save();break;
 case "flow_field_move_up":flow_field_move("up");break;
 case "flow_field_move_down":flow_field_move("down");break;
 case "flow_field_delete":flow_field_delete();break;
 case "flow_action_save":flow_action_save();break;
 case "flow_action_delete":flow_action_delete();break;
 // attachments
 case "attachments_download":attachments_download();break;
 // default
 default:
  $alert="?alert=submitFunctionNotFound&alert_class=alert-warning&act=".$act;
  exit(header("location: index.php".$alert));
}

















/* -[ Ticket Solicit ]------------------------------------------------------- */
function ticket_solicit(){/*
 if(!api_checkPermission("workflows","workflows_user")){api_die("accessDenied");}
 // acquire variables
 $g_id=$_GET['id'];
 if(!$g_id){$g_id=0;}
 // check
 if($g_id>0){
  // execute query
  //$GLOBALS['db']->execute("UPDATE workflows_tickets_activities SET status='2',idAssigned='".$_SESSION['account']->id."',updDate='".date("Y-m-d H:i:s")."' WHERE id='".$g_id."'");
  // alert
  $alert="&alert=ticketActivityAssigned&alert_class=alert-success";
 }else{
  // alert
  $alert="&alert=ticketError&alert_class=alert-error";
 }
 // redirect
 exit(header("location: workflows_view.php?id=".$g_id.$alert));*/
}















// ok







/* -[ Workflow Save ]--------------------------------------------------------- */
function workflow_save(){
 if(!api_checkPermission("workflows","workflows_add")){api_die("accessDenied");}
 // get flow object
 $flow=api_workflows_flow($_GET['idFlow'],FALSE);
 // acquire variables
 $p_idCategory=$_POST['idCategory'];
 $p_typology=$_POST['typology'];
 $p_subject=addslashes($_POST['subject']);
 $p_priority=$_POST['priority'];
 // assign flow variables
 if($flow->id){
  $idFlow=$flow->id;
  $sla=$flow->sla;
 }else{
  $idFlow=0;
  $sla=240;
 }
 // build query
 $query="INSERT INTO workflows_workflows
  (idCategory,idFlow,typology,subject,priority,sla,status,addDate,addIdAccount) VALUES
  ('".$p_idCategory."','".$idFlow."','".$p_typology."','".$p_subject."','".$p_priority."',
   '".$sla."','1','".date("Y-m-d H:i:s")."','".$_SESSION['account']->id."')";
 // execute query
 $GLOBALS['db']->execute($query);
 // set id to last inserted id
 $g_id=$GLOBALS['db']->lastInsertedId();
 // alert
 $alert="?alert=workflowCreated&alert_class=alert-success";
 // get fields
 if(!workflow_get_fields($g_id,$flow->id)){$alert="?alert=workflowError&alert_class=alert-error";}
 // process actions
 if(!workflow_process_actions($g_id,$flow->id)){$alert="?alert=workflowError&alert_class=alert-error";}
 // redirect
 exit(header("location: workflows_list.php".$alert));
}

/* -[ Workflow Get Field ]----------------------------------------------------- */
function workflow_get_fields($idWorkflow,$idFlow=0){
 if(!api_checkPermission("workflows","workflows_add")){api_die("accessDenied");}
 if(!$idWorkflow){return FALSE;}
 // definitions
 $description=NULL;
 // acquire varaibles
 $p_phone=addslashes($_POST['phone']);
 $p_note=addslashes($_POST['note']);
 // get flow fields
 if($idFlow>0){
  $fields=$GLOBALS['db']->query("SELECT * FROM workflows_fields WHERE idFlow='".$idFlow."' ORDER BY position ASC");
  while($field=$GLOBALS['db']->fetchNextObject($fields)){
   // field name to show in description
   if(strlen($field->label)>0){$field->nameShow=$field->label;}
   else{$field->nameShow=$field->name;}
   // prepare options array
   $field->options=api_workflows_flowFieldOptions($field);
   // acquire field values by typology
   switch($field->typology){
    // multiselect have array values
    case "multiselect":
     $values=NULL;
     if(is_array($_POST[$field->name])){
      foreach($_POST[$field->name] as $g_option){
       $values.=", ".$field->options[$g_option]->label;
      }
     }
     $value=substr($values,2);
     break;
    // checkbox and radio have text value
    case "checkbox":
    case "radio":
     if($_POST[$field->name]<>NULL){$value=$field->options[$_POST[$field->name]]->label;}
     break;
    // select value is in array
    case "select":
     if($_POST[$field->name]<>NULL){$value=$field->options[$_POST[$field->name]]->label;}
     break;
    // range values
    case "range":
    case "daterange":
    case "datetimerange":
     if($_POST[$field->name."_from"]<>NULL){$value=api_text("form-range-from")." ".$_POST[$field->name."_from"]." ";}
     if($_POST[$field->name."_to"]<>NULL){$value.=api_text("form-range-to")." ".$_POST[$field->name."_to"];}
     break;
    case "file":
     $value=NULL;
     $file=api_file_upload($_FILES[$field->name],"workflows_attachments",NULL,NULL,NULL,NULL,FALSE,NULL,FALSE);
     if($file->id){
      $value=addslashes("<a href='submit.php?act=attachments_download&id=".$file->id."'>".$file->name."</a>");
     }
     break;
    default:
     $value=addslashes($_POST[$field->name]);
   }
   $description.=$field->nameShow.": ".$value."\n\n";
  }
 }
 // acquire variables
 $description.=api_text("add-ff-phone").": ".$p_phone;
 // execute query
 $GLOBALS['db']->execute("UPDATE workflows_workflows SET description='".$description."',note='".$p_note."' WHERE id='".$idWorkflow."'");
 return TRUE;
}

/* -[ Workflow Process Actions ]----------------------------------------------- */
function workflow_process_actions($idWorkflow,$idFlow=0){
 if(!api_checkPermission("workflows","workflows_add")){api_die("accessDenied");}
 if(!$idWorkflow){return FALSE;}
 // check flow
 if($idFlow>0){
  // get flow actions
  $actions=$GLOBALS['db']->query("SELECT * FROM workflows_actions WHERE idFlow='".$idFlow."' ORDER BY requiredAction ASC,subject ASC");
  while($action=$GLOBALS['db']->fetchNextObject($actions)){
   $execute=TRUE;
   if($action->conditionedField>0){
    $execute=FALSE;
    $value=NULL;
    // check condition
    $field=$GLOBALS['db']->queryUniqueObject("SELECT * FROM workflows_fields WHERE id='".$action->conditionedField."'");
    if($field->id>0){
     $value=addslashes($_POST[$field->name]);
     if($value==$action->conditionedValue){$execute=TRUE;}
    }
   }
   // execute
   if($execute){
    // assign variables values
    $p_idCategory=$_POST['idCategory'];
    $requiredAction=$action->id;
    $typology=$action->typology;
    if($typology<>1){$hash=md5(api_randomString(32));}else{$hash=NULL;}
    $mail=api_workflows_replaceTagCodes($action->mail);
    $subject=$action->subject;
    $idGroup=$action->idGroup;
    $idAssigned=$action->idAssigned;
    $difficulty=$action->difficulty;
    $priority=$action->priority;
    $slaAssignment=$action->slaAssignment;
    $slaClosure=$action->slaClosure;
    $hostname=api_hostName();
    if($action->requiredAction>0){
     $status=5;
     $requiredTicket=$GLOBALS['db']->queryUniqueValue("SELECT id FROM workflows_tickets WHERE idWorkflow='".$idWorkflow."' AND requiredAction='".$action->requiredAction."'");
    }else{
     $status=1;
     $requiredTicket=0;
    }
    // build query
    $query="INSERT INTO workflows_tickets
     (idWorkflow,idCategory,requiredTicket,requiredAction,typology,hash,mail,subject,idGroup,idAssigned,
      difficulty,priority,slaAssignment,slaClosure,status,solved,approved,hostname,addDate,addIdAccount) VALUES
     ('".$idWorkflow."','".$p_idCategory."','".$requiredTicket."','".$requiredAction."','".$typology."','".$hash."',
      '".$mail."','".$subject."','".$idGroup."','".$idAssigned."','".$difficulty."','".$priority."',
      '".$slaAssignment."','".$slaClosure."','".$status."','0','0','".$hostname."','".date("Y-m-d H:i:s")."',
      '".$_SESSION['account']->id."')";
    // execute query
    $GLOBALS['db']->execute($query);
    // set id to last inserted id
    $q_idTicket=$GLOBALS['db']->lastInsertedId();
    // if ticket is not locked
    if($status<>5){
     // send notification
     api_workflows_notifications($q_idTicket);
    }
   }
  }
  // return
  return TRUE;
 }/*else{
  // open standard ticket
  $p_idCategory=$_POST['idCategory'];
  $hash=md5(api_randomString(32));
  $p_subject=addslashes($_POST['name']);
  $p_priority=$_POST['priority'];
  $hostname=api_hostName();
  // get group id by selected category
  $idGroup=api_workflows_categoryGroup($p_idCategory);
  // build query
  $query="INSERT INTO workflows_tickets
   (idWorkflow,idCategory,typology,hash,subject,idGroup,difficulty,priority,
    slaAssignment,slaClosure,status,solved,approved,hostname,addDate,addIdAccount) VALUES
   ('".$idWorkflow."','".$p_idCategory."','1','".$hash."','".$p_subject."',
    '".$idGroup."','2','".$p_priority."','0','240','1','0','0','".$hostname."',
    '".date("Y-m-d H:i:s")."','".$_SESSION['account']->id."')";
  // execute query
  $GLOBALS['db']->execute($query);
  // set id to last inserted id
  $q_idTicket=$GLOBALS['db']->lastInsertedId();
  // send notification
  api_workflows_notifications($q_idTicket);
  // return
  return TRUE;
 }*/
 return FALSE;
}


/* -[ Ticket Save ]---------------------------------------------------------- */
function ticket_save(){
 if(!api_checkPermission("workflows","workflows_add")){api_die("accessDenied");}
 // get workflow object
 $workflow=api_workflows_workflow($_GET['idWorkflow']);
 if(!$workflow->id){
  // redirect
  $alert="?alert=workflowError&alert_class=alert-error";
  exit(header("location: workflows_list.php".$alert));
 }
 // build and acquire variables
 $p_idCategory=$workflow->idCategory;
 $p_typology=$_POST['typology'];
 if($p_typology<>1){$hash=md5(api_randomString(32));}else{$hash=NULL;}
 $p_mail=addslashes($_POST['mail']);
 $p_subject=addslashes($_POST['subject']);
 $p_note=addslashes($_POST['note']);
 $p_idGroup=$_POST['idGroup'];
 $p_idAssigned=$_POST['idAssigned'];
 $p_difficulty=$_POST['difficulty'];
 $p_priority=$_POST['priority'];
 $slaAssignment=$_POST['slaAssignment'];
 $slaClosure=$_POST['slaClosure'];
 $hostname=api_hostName();
 // build query
 $query="INSERT INTO workflows_tickets
  (idWorkflow,idCategory,typology,hash,mail,subject,note,idGroup,idAssigned,difficulty,priority,
   slaAssignment,slaClosure,status,solved,approved,hostname,addDate,addIdAccount) VALUES
  ('".$workflow->id."','".$p_idCategory."','".$p_typology."','".$hash."','".$p_mail."',
   '".$p_subject."','".$p_note."','".$p_idGroup."','".$p_idAssigned."','".$p_difficulty."',
   '".$p_priority."','".$slaAssignment."','".$slaClosure."','1','0','0','".$hostname."',
   '".date("Y-m-d H:i:s")."','".$_SESSION['account']->id."')";
 // execute query
 $GLOBALS['db']->execute($query);
 // set id to last inserted id
 $q_idTicket=$GLOBALS['db']->lastInsertedId();
 // send notification
 api_workflows_notifications($q_idTicket);
 // redirect
 $alert="&alert=ticketCreated&alert_class=alert-success";
 exit(header("location: workflows_view.php?id=".$workflow->id.$alert));
}

/* -[ Ticket Assign ]-------------------------------------------------------- */
function ticket_assign(){
 // che permission to assign
 //if(!api_checkPermission("workflows","workflows_supporter")){api_die("accessDenied");}
 // acquire variables
 $g_idWorkflow=$_GET['idWorkflow'];
 if(!$g_idWorkflow){$g_idWorkflow=0;}
 $g_idTicket=$_GET['idTicket'];
 if(!$g_idTicket){$g_idTicket=0;}
 // check id
 if($g_idWorkflow>0 && $g_idTicket>0){
  // execute query
  $GLOBALS['db']->execute("UPDATE workflows_tickets SET status='2',idAssigned='".$_SESSION['account']->id."',assDate='".date("Y-m-d H:i:s")."',updDate='".date("Y-m-d H:i:s")."' WHERE id='".$g_idTicket."'");
  // alert
  $alert="&alert=ticketAssigned&alert_class=alert-success";
 }else{
  // alert
  $alert="&alert=ticketError&alert_class=alert-error";
 }
 // redirect
 exit(header("location: workflows_view.php?id=".$g_idWorkflow."&idTicket=".$g_idTicket.$alert));
}

/* -[ Ticket Process ]------------------------------------------------------- */
function ticket_process(){
 // check permission to process
 //if(!api_checkPermission("workflows","workflows_supporter")){api_die("accessDenied");}
 // acquire variables
 $g_idWorkflow=$_GET['idWorkflow'];
 if(!$g_idWorkflow){$g_idWorkflow=0;}
 $g_idTicket=$_GET['idTicket'];
 if(!$g_idTicket){$g_idTicket=0;}
 $p_status=$_POST['status'];
 $p_idGroup=$_POST['idGroup'];
 $p_idAssigned=$_POST['idAssigned'];
 $p_priority=$_POST['priority'];
 $p_difficulty=$_POST['difficulty'];
 $p_note=addslashes($_POST['note']);
 // switch status
 switch($p_status){
  case 1: // opened
   $update_date=",updDate=NULL,endDate=NULL";
   $solved=0;
   break;
  case 40: // unsolved
   $p_status=4;
   $solved=0;
   break;
  case 41: // solved
   $p_status=4;
   $solved=1;
   break;
  case 42: // unnecessary
   $p_status=4;
   $solved=2;
   break;
  default:
   $solved=0;
 }
 // if closed set endDate
 if($p_status==4){$update_date=",endDate='".date("Y-m-d H:i:s")."'";}
 // if change assigned account reset status
 if($p_status==2 && $p_idAssigned<>$_SESSION['account']->id){$p_status=1;}
 // check
 if($g_idWorkflow>0 && $g_idTicket>0){
  $query="UPDATE workflows_tickets SET
   status='".$p_status."',
   idGroup='".$p_idGroup."',
   idAssigned='".$p_idAssigned."',
   priority='".$p_priority."',
   difficulty='".$p_difficulty."',
   note='".$p_note."',
   solved='".$solved."'
   ".$update_date."
   WHERE id='".$g_idTicket."'";
  // execute query
  $GLOBALS['db']->execute($query);
  // unlock locked tickets
  if($p_status==4){
   $locked_tickets=$GLOBALS['db']->query("SELECT * FROM workflows_tickets WHERE requiredTicket='".$g_idTicket."' AND status='5'");
   while($locked_ticket=$GLOBALS['db']->fetchNextObject($locked_tickets)){
    // send notification
    api_workflows_notifications($locked_ticket);
   }
   $GLOBALS['db']->execute("UPDATE workflows_tickets SET status='1',addDate='".date("Y-m-d H:i:s")."' WHERE requiredTicket='".$g_idTicket."' AND status='5'");
  }
  // alert
  if($p_status==4){$alert="&alert=ticketClosed&alert_class=alert-success";}
  else{$alert="&alert=ticketUpdated&alert_class=alert-success";}
  // check if all activities are completed
  if($GLOBALS['db']->countOf("workflows_tickets","idWorkflow='".$g_idWorkflow."' AND (status<'4' OR status='5')")==0){
   // close workflow
   $GLOBALS['db']->execute("UPDATE workflows_workflows SET status='4',endDate='".date("Y-m-d H:i:s")."' WHERE id='".$g_idWorkflow."'");
   // notification

   // -----!!!----- notifica che il workflow è chiuso

   // alert
   $alert="&alert=workflowClosed&alert_class=alert-success";
  }
 }else{
  // alert
  $alert="&alert=ticketError&alert_class=alert-error";
 }
 // redirect
 exit(header("location: workflows_view.php?id=".$g_idWorkflow."&idTicket=".$g_idTicket.$alert));
}


/* -[ Category Save ]-------------------------------------------------------- */
function category_save(){
 if(!api_checkPermission("workflows","workflows_admin")){api_die("accessDenied");}
 // acquire variables
 $g_id=$_GET['id'];
 if(!$g_id){$g_id=0;}
 $p_idCategory=$_POST['idCategory'];
 $p_name=addslashes($_POST['name']);
 $p_description=addslashes($_POST['description']);
 $p_idGroup=addslashes($_POST['idGroup']);
 // check
 if($p_idGroup>0){
  // build query
  if($g_id>0 && $p_idGroup>0){
   $query="UPDATE workflows_categories SET
    idCategory='".$p_idCategory."',
    name='".$p_name."',
    description='".$p_description."',
    idGroup='".$p_idGroup."',
    updDate='".date("Y-m-d H:i:s")."',
    updIdAccount='".$_SESSION['account']->id."'
    WHERE id='".$g_id."'";
   // execute query
   $GLOBALS['db']->execute($query);
   // redirect
   $alert="?alert=categoryUpdated&alert_class=alert-success";
   exit(header("location: workflows_categories.php".$alert));
  }else{
   $query="INSERT INTO workflows_categories
    (idCategory,name,description,idGroup,addDate,addIdAccount) VALUES
    ('".$p_idCategory."','".$p_name."','".$p_description."','".$p_idGroup."',
     '".date("Y-m-d H:i:s")."','".$_SESSION['account']->id."')";
   // execute query
   $GLOBALS['db']->execute($query);
   // set id to last inserted id
   $g_id=$GLOBALS['db']->lastInsertedId();
   // redirect
   $alert="?alert=categoryCreated&alert_class=alert-success";
   exit(header("location: workflows_categories.php".$alert));
  }
 }else{
  // redirect
  $alert="?alert=categoryError&alert_class=alert-error";
  exit(header("location: workflows_categories.php".$alert));
 }
}


/* -[ Flow Save ]------------------------------------------------------------ */
function flow_save(){
 if(!api_checkPermission("workflows","workflows_admin")){api_die("accessDenied");}
 // acquire variables
 $g_idFlow=$_GET['idFlow'];
 if(!$g_idFlow){$g_idFlow=0;}
 $p_idCategory=$_POST['idCategory'];
 $p_typology=$_POST['typology'];
 $p_pinned=$_POST['pinned'];
 $p_subject=addslashes($_POST['subject']);
 $p_description=addslashes($_POST['description']);
 $p_advice=addslashes($_POST['advice']);
 $p_priority=$_POST['priority'];
 $p_sla=$_POST['sla'];
 //$p_procedure=$_POST['procedure']; <- da implementare
 // build query
 if($g_idFlow>0){
  $query="UPDATE workflows_flows SET
   idCategory='".$p_idCategory."',
   typology='".$p_typology."',
   pinned='".$p_pinned."',
   subject='".$p_subject."',
   description='".$p_description."',
   advice='".$p_advice."',
   priority='".$p_priority."',
   sla='".$p_sla."',
   updDate='".date("Y-m-d H:i:s")."',
   updIdAccount='".$_SESSION['account']->id."'
   WHERE id='".$g_idFlow."'";
  // execute query
  $GLOBALS['db']->execute($query);
  // redirect
  $alert="&alert=flowUpdated&alert_class=alert-success";
  exit(header("location: workflows_flows_view.php?idFlow=".$g_idFlow.$alert));
 }else{
  $query="INSERT INTO workflows_flows
   (idCategory,typology,pinned,subject,description,advice,priority,sla,addDate,addIdAccount) VALUES
   ('".$p_idCategory."','".$p_typology."','".$p_pinned."','".$p_subject."','".$p_description."',
    '".$p_advice."','".$p_priority."','".$p_sla."','".date("Y-m-d H:i:s")."','".$_SESSION['account']->id."')";
  // execute query
  $GLOBALS['db']->execute($query);
  // set id to last inserted id
  $q_idFlow=$GLOBALS['db']->lastInsertedId();
  // redirect
  $alert="&alert=flowCreated&alert_class=alert-success";
  exit(header("location: workflows_flows_view.php?idFlow=".$q_idFlow.$alert));
 }
}

/* -[ Flow Field Save ]------------------------------------------------------ */
function flow_field_save(){
 if(!api_checkPermission("workflows","workflows_admin")){api_die("accessDenied");}
 // acquire variables
 $g_idFlow=$_GET['idFlow'];
 if(!$g_idFlow){$g_idFlow=0;}
 $g_idField=$_GET['idField'];
 if(!$g_idField){$g_idField=0;}
 $p_typology=$_POST['typology'];
 $p_name=api_clearFileName(addslashes($_POST['name']));
 $p_label=addslashes($_POST['label']);
 $p_value=addslashes($_POST['value']);
 $p_class=addslashes($_POST['class']);
 $p_placeholder=addslashes($_POST['placeholder']);
 $p_required=$_POST['required'];
 $p_options_method=addslashes($_POST['options_method']);
 $p_options_values=addslashes($_POST['options_values']);
 $p_options_query=addslashes($_POST['options_query']);
 // convert fields
 if($p_options_method=="none"){$p_options_method="";$p_options_values="";$p_options_query="";}
 if($p_options_method=="values"){$p_options_query="";}
 if($p_options_method=="query"){$p_options_values="";}
 // check flow
 if(!$g_idFlow>0){
  $alert="?alert=flowError&alert_class=alert-error";
  exit(header("location: workflows_flow_list.php".$alert));
 }
 // check for insert or update
 if($g_idField>0){
  // build query
  $query="UPDATE workflows_fields SET
   idFlow='".$g_idFlow."',
   typology='".$p_typology."',
   name='".$p_name."',
   label='".$p_label."',
   value='".$p_value."',
   class='".$p_class."',
   placeholder='".$p_placeholder."',
   options_method='".$p_options_method."',
   options_values='".$p_options_values."',
   options_query='".$p_options_query."',
   required='".$p_required."'
   WHERE id='".$g_idField."'";
  // execute query
  $GLOBALS['db']->execute($query);
  // redirect
  $alert="&alert=fieldUpdated&alert_class=alert-success";
  exit(header("location: workflows_flows_view.php?idFlow=".$g_idFlow.$alert));
 }else{
  // get number of fields for position
  $position=$GLOBALS['db']->countOf("workflows_fields","idFlow='".$g_idFlow."'");
  $position++;
  // build query
  $query="INSERT INTO workflows_fields
   (idFlow,typology,name,label,value,class,placeholder,options_method,options_values,
    options_query,required,position) VALUES
   ('".$g_idFlow."','".$p_typology."','".$p_name."','".$p_label."','".$p_value."',
    '".$p_class."','".$p_placeholder."','".$p_options_method."','".$p_options_values."',
    '".$p_options_query."','".$p_required."','".$position."')";
  // execute query
  $GLOBALS['db']->execute($query);
  // set id to last inserted id
  $q_idField=$GLOBALS['db']->lastInsertedId();
  // redirect
  $alert="&alert=fieldCreated&alert_class=alert-success";
  exit(header("location: workflows_flows_view.php?idFlow=".$g_idFlow.$alert));
 }
}

/* -[ Flow Field Move ]------------------------------------------------------ */
function flow_field_move($to){
 if(!api_checkPermission("workflows","workflows_admin")){api_die("accessDenied");}
 // acquire variables
 $g_idFlow=$_GET['idFlow'];
 if(!$g_idFlow){$g_idFlow=0;}
 $g_idField=$_GET['idField'];
 if(!$g_idField){$g_idField=0;}
 // check flow
 if(!$g_idFlow>0){
  $alert="?alert=flowError&alert_class=alert-error";
  exit(header("location: workflows_flow_list.php".$alert));
 }
 if($g_idField>0){
  $moved=FALSE;
  // get current position
  $position=$GLOBALS['db']->queryUniqueValue("SELECT position FROM workflows_fields WHERE id='".$g_idField."'");
  // move field
  switch($to){
   case "up":
    if($position>1){
     echo $GLOBALS['db']->execute("UPDATE workflows_fields SET position=".$position." WHERE position='".($position-1)."' AND idFlow='".$g_idFlow."'");
     echo $GLOBALS['db']->execute("UPDATE workflows_fields SET position=".($position-1)." WHERE id='".$g_idField."'");
     $moved=TRUE;
    }
    break;
   case "down":
    $max_position=$GLOBALS['db']->countOf("workflows_fields","idFlow='".$g_idFlow."'");
    if($position<$max_position){
     echo $GLOBALS['db']->execute("UPDATE workflows_fields SET position=".$position." WHERE position='".($position+1)."' AND idFlow='".$g_idFlow."'");
     echo $GLOBALS['db']->execute("UPDATE workflows_fields SET position=".($position+1)." WHERE id='".$g_idField."'");
     $moved=TRUE;
    }
    break;
  }
  // alert and redirect
  if($moved){$alert="&alert=fieldMoved&alert_class=alert-success";}
   else{$alert="&alert=flowError&alert_class=alert-error";}
  exit(header("location: workflows_flows_view.php?idFlow=".$g_idFlow.$alert));
 }else{
  // redirect
  $alert="&alert=flowError&alert_class=alert-error";
  exit(header("location: workflows_flows_view.php?idFlow=".$g_idFlow.$alert));
 }
}

/* -[ Flow Field Delete ]---------------------------------------------------- */
function flow_field_delete(){
 if(!api_checkPermission("workflows","workflows_admin")){api_die("accessDenied");}
 // acquire variables
 $g_idFlow=$_GET['idFlow'];
 if(!$g_idFlow){$g_idFlow=0;}
 $g_idField=$_GET['idField'];
 if(!$g_idField){$g_idField=0;}
 // check flow
 if(!$g_idFlow>0){
  $alert="?alert=flowError&alert_class=alert-error";
  exit(header("location: workflows_flow_list.php".$alert));
 }
 // check
 if($g_idField>0){
  // get field position
  $position=$GLOBALS['db']->queryUniqueValue("SELECT position FROM workflows_fields WHERE id='".$g_idField."'");
  // delete action
  echo $GLOBALS['db']->execute("DELETE FROM workflows_fields WHERE id='".$g_idField."'");
  // moves back fields located after
  echo $GLOBALS['db']->execute("UPDATE workflows_fields SET position=position-1 WHERE position>'".$position."' AND idFlow='".$g_idFlow."'");
  // redirect
  $alert="&alert=fieldDeleted&alert_class=alert-warning";
  exit(header("location: workflows_flows_view.php?idFlow=".$g_idFlow.$alert));
 }else{
  // redirect
  $alert="&alert=flowError&alert_class=alert-error";
  exit(header("location: workflows_flows_view.php?idFlow=".$g_idFlow.$alert));
 }
}

/* -[ Workflow Action Save ]-------------------------------------------------- */
function flow_action_save(){
 if(!api_checkPermission("workflows","workflows_admin")){api_die("accessDenied");}
 // acquire variables
 $g_idFlow=$_GET['idFlow'];
 if(!$g_idFlow){$g_idFlow=0;}
 $g_idAction=$_GET['idAction'];
 if(!$g_idAction){$g_idAction=0;}
 $p_typology=$_POST['typology'];
 $p_requiredAction=$_POST['requiredAction'];
 $p_conditionedField=$_POST['conditionedField'];
 $p_conditionedValue=addslashes($_POST['conditionedValue']);
 $p_subject=addslashes($_POST['subject']);
 $p_idGroup=$_POST['idGroup'];
 $p_idAssigned=$_POST['idAssigned'];
 if(!$p_idAssigned){$p_idAssigned=0;}
 $p_mail=addslashes($_POST['mail']);
 $p_difficulty=$_POST['difficulty'];
 $p_priority=$_POST['priority'];
 $p_slaAssignment=$_POST['slaAssignment'];
 $p_slaClosure=$_POST['slaClosure'];
 // convert fields
 if($p_typology==1){$p_mail="";}
 if($p_conditionedField==0){$p_conditionedValue="";}
 // check flow
 if(!$g_idFlow>0){
  $alert="?alert=flowError&alert_class=alert-error";
  exit(header("location: workflows_flow_list.php".$alert));
 }
 // build query
 if($g_idAction>0){
  $query="UPDATE workflows_actions SET
   idFlow='".$g_idFlow."',
   typology='".$p_typology."',
   requiredAction='".$p_requiredAction."',
   conditionedField='".$p_conditionedField."',
   conditionedValue='".$p_conditionedValue."',
   subject='".$p_subject."',
   idGroup='".$p_idGroup."',
   idAssigned='".$p_idAssigned."',
   mail='".$p_mail."',
   difficulty='".$p_difficulty."',
   priority='".$p_priority."',
   slaAssignment='".$p_slaAssignment."',
   slaClosure='".$p_slaClosure."'
   WHERE id='".$g_idAction."'";
  // execute query
  $GLOBALS['db']->execute($query);
  // redirect
  $alert="&alert=actionUpdated&alert_class=alert-success";
  exit(header("location: workflows_flows_view.php?idFlow=".$g_idFlow.$alert));
 }else{
  $query="INSERT INTO workflows_actions
   (idFlow,typology,requiredAction,conditionedField,conditionedvalue,subject,
    idGroup,idAssigned,mail,difficulty,priority,slaAssignment,slaClosure) VALUES
   ('".$g_idFlow."','".$p_typology."','".$p_requiredAction."','".$p_conditionedField."',
    '".$p_conditionedValue."','".$p_subject."','".$p_idGroup."','".$p_idAssigned."',
    '".$p_mail."','".$p_difficulty."','".$p_priority."','".$p_slaAssignment."',
    '".$p_slaClosure."')";
  // execute query
  $GLOBALS['db']->execute($query);
  // set id to last inserted id
  $q_idAction=$GLOBALS['db']->lastInsertedId();
  // redirect
  $alert="&alert=actionCreated&alert_class=alert-success";
  exit(header("location: workflows_flows_view.php?idFlow=".$g_idFlow.$alert));
 }
}

/* -[ Flow Action Delete ]--------------------------------------------------- */
function flow_action_delete(){
 if(!api_checkPermission("workflows","workflows_admin")){api_die("accessDenied");}
 // acquire variables
 $g_idFlow=$_GET['idFlow'];
 if(!$g_idFlow){$g_idFlow=0;}
 $g_idAction=$_GET['idAction'];
 if(!$g_idAction){$g_idAction=0;}
 // check flow
 if(!$g_idFlow>0){
  $alert="?alert=flowError&alert_class=alert-error";
  exit(header("location: workflows_flow_list.php".$alert));
 }
 if($g_idAction>0){
  // check if action is required
  if($GLOBALS['db']->countOf("workflows_actions","requiredAction='".$g_idAction."'")>0){
   // redirect
   $alert="&alert=actionRequired&alert_class=alert-error";
   exit(header("location: workflows_flows_view.php?idFlow=".$g_idFlow.$alert));
  }else{
   // delete action
   $GLOBALS['db']->execute("DELETE FROM workflows_actions WHERE id='".$g_idAction."'");
   // redirect
   $alert="&alert=actionDeleted&alert_class=alert-warning";
   exit(header("location: workflows_flows_view.php?idFlow=".$g_idFlow.$alert));
  }
 }else{
  // redirect
  $alert="&alert=flowError&alert_class=alert-error";
  exit(header("location: workflows_flows_view.php?idFlow=".$g_idFlow.$alert));
 }
}

/* -[ Attachments Download ]-------------------------------------------- */
function attachments_download(){
 if(!api_checkPermission("workflows","workflows_view")){api_die("accessDenied");}
 // acquire variables
 $g_id=$_GET['id'];
 if(!$g_id){$g_id=0;}
 // download file from database
 api_file_download($g_id,"workflows_attachments");
}