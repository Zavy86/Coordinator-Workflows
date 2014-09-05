<?php
/* -------------------------------------------------------------------------- *\
|* -[ Workflows - API ]------------------------------------------------------ *|
\* -------------------------------------------------------------------------- */


/* -[ Clear field name ]----------------------------------------------------- */
// @param $field : Field name to clear
function api_workflows_clearFieldName($field){
 $field=str_replace(" ","-",$field);
 $return=strtolower(preg_replace("/[^A-Za-z0-9-._[]]/", "",$field));
 return $return;
}

/* -[ Workflow object by id ]------------------------------------------------ */
// @integer $idWorkflow : workflow id
// @boolean $subobjects : load also feasibility subobjects
function api_workflows_workflow($idWorkflow,$subobjects=TRUE){
 $workflow=$GLOBALS['db']->queryUniqueObject("SELECT * FROM workflows_workflows WHERE id='".$idWorkflow."'");
 if(!$workflow->id){return FALSE;}
 // build workflow number
 $workflow->number=str_pad($workflow->id,5,"0",STR_PAD_LEFT);
 // retrieve workflow hostname
 $workflow->hostname=$GLOBALS['db']->queryUniqueValue("SELECT DISTINCT(hostname) FROM workflows_tickets WHERE idWorkflow='".$workflow->id."'");
 if($subobjects){
  // get workflow tickets
  $workflow->tickets=array();
  $tickets=$GLOBALS['db']->query("SELECT * FROM workflows_tickets WHERE idWorkflow='".$workflow->id."' ORDER BY requiredTicket ASC,status DESC,id ASC");
  while($ticket=$GLOBALS['db']->fetchNextObject($tickets)){
   $ticket->notes=array();
   $notes=$GLOBALS['db']->query("SELECT * FROM workflows_tickets_notes WHERE idTicket='".$ticket->id."' ORDER BY addDate DESC");
   while($note=$GLOBALS['db']->fetchNextObject($notes)){$ticket->notes[]=$note;}
   $workflow->tickets[]=$ticket;
  }
 }
 return $workflow;
}

/* -[ Workflow SLA ]--------------------------------------------------------- */
// @object $workflow : workflow object
// @boolean $popup : show textual difference in popup
function api_workflows_workflowSLA($workflow,$popup=FALSE){
 if(!$workflow->id){return FALSE;}
 // get timestamp and sla
 $timestamp_from=$workflow->addDate;
 $sla=$workflow->sla;
 // check sla
 if($sla==0){return FALSE;}
 // choise timestamp to
 if($workflow->status==4){$timestamp_to=$workflow->endDate;}
 else{$timestamp_to=date("Y-m-d H:i:s");}
 // check
 $difference=api_timestampDifference($timestamp_from,$timestamp_to,"I");
 if($difference<($sla/2)){$class="text-success";}
 elseif($difference<$sla){$class="text-warning";}
 else{$class="text-error";}
 // format difference
 if($workflow->status==4){
  if($difference<$sla){$difference_txt=api_text("api-sla-completed",api_timestampDifferenceFormat($difference*60,FALSE));}
  else{$difference_txt=api_text("api-sla-completedExpired",api_timestampDifferenceFormat(($difference-$sla)*60,FALSE));}
 }else{
  if($difference<$sla){$difference_txt=api_text("api-sla-expire",api_timestampDifferenceFormat(($sla-$difference)*60,FALSE));}
  else{$difference_txt=api_text("api-sla-expired",api_timestampDifferenceFormat(($difference-$sla)*60,FALSE));}
 }
 // return
 if($popup){
  $return="<a data-toggle='popover' data-placement='top' data-content='".$difference_txt."'>";
  $return.="<span class='".$class."'>".$sla."</span></a>";
 }else{
  $return="<span class='".$class."'>".api_text("api-sla",$sla)."</span> - ".$difference_txt;
 }
 return $return;
}


/* -[ Workflows Status ]----------------------------------------------------- */
// @integer $status : Value of status
// @integer $onlyIcon : Show only icon
function api_workflows_status($status,$onlyIcon=FALSE,$solved=NULL){
 switch($status){
  //case 1:$return=api_icon("icon-map-marker",api_text("status-opened"));if(!$onlyIcon){$return.=" ".api_text("status-opened");}break;
  case 1:$return="<span title='".api_text("status-opened")."' style=\"background:url('../workflows/images/bullet-red.png') no-repeat top left;background-size:20px 20px;display: inline-block;height:16px;width:20px\"></span>";if(!$onlyIcon){$return.=" ".api_text("status-opened");}break;
  //case 2:$return=api_icon("icon-eye-open",api_text("status-assigned"));if(!$onlyIcon){$return.=" ".api_text("status-assigned");}break;
  case 2:$return="<span title='".api_text("status-assigned")."' style=\"background:url('../workflows/images/bullet-green.png') no-repeat top left;background-size:20px 20px;display: inline-block;height:16px;width:20px\"></span>";if(!$onlyIcon){$return.=" ".api_text("status-assigned");}break;
  //case 3:$return=api_icon("icon-tint",api_text("status-standby"));if(!$onlyIcon){$return.=" ".api_text("status-standby");}break;
  case 3:$return="<span title='".api_text("status-standby")."' style=\"background:url('../workflows/images/bullet-yellow.png') no-repeat top left;background-size:20px 20px;display: inline-block;height:16px;width:20px\"></span>";if(!$onlyIcon){$return.=" ".api_text("status-standby");}break;
  case 4:
   $statusIcon="icon-ok";
   if($solved!==NULL){
    switch($solved){
     case 0:$solvedText=" - ".api_text("solved-unexecuted");$statusIcon="icon-remove";break;
     case 1:$solvedText=" - ".api_text("solved-executed");break;
     case 2:$solvedText=" - ".api_text("solved-unnecessary");break;
    }
   }
   $return=api_icon($statusIcon,api_text("status-closed").$solvedText);
   if(!$onlyIcon){$return.=" ".api_text("status-closed").$solvedText;}
   break;
  case 5:$return=api_icon("icon-lock",api_text("status-locked"));if(!$onlyIcon){$return.=" ".api_text("status-locked");}break;
  default:$return="[Status not found]";
 }
 return $return;
}

/* -[ Workflows Typology ]--------------------------------------------------- */
// @integer $typology : Typology id
// @integer $onlyIcon : Show only icon
function api_workflows_typology($typology,$onlyIcon=FALSE){
 switch($typology){
  case 1:$return=api_icon("icon-tasks");if(!$onlyIcon){$return.=" ".api_text("typology-request");}break;
  case 2:$return=api_icon("icon-warning-sign");if(!$onlyIcon){$return.=" ".api_text("typology-incident");}break;
  default:$return="[Typology not found]";
 }
 return $return;
}

/* -[ Workflows Priority ]--------------------------------------------------- */
// @integer $priority : Prority id
function api_workflows_priority($priority){
 switch($priority){
  case 1:$return=api_text("priority-highest");break;
  case 2:$return=api_text("priority-high");break;
  case 3:$return=api_text("priority-medium");break;
  case 4:$return=api_text("priority-low");break;
  case 5:$return=api_text("priority-lowest");break;
  default:$return="[Priority not found]";
 }
 return $return;
}


/* -[ Ticket object by id ]-------------------------------------------------- */
// @integer $idTicket : ticket id
// @boolean $subobjects : load also feasibility subobjects
function api_workflows_ticket($idticket,$subobjects=TRUE){
 $ticket=$GLOBALS['db']->queryUniqueObject("SELECT * FROM workflows_tickets WHERE id='".$idticket."'");
 if(!$ticket->id){return FALSE;}
 // build ticket number
 $ticket->number=str_pad($ticket->idWorkflow,5,"0",STR_PAD_LEFT)."-".str_pad($ticket->id,5,"0",STR_PAD_LEFT);
 if($subobjects){
  $ticket->notes=array();
  $notes=$GLOBALS['db']->query("SELECT * FROM workflows_tickets_notes WHERE idTicket='".$ticket->id."' ORDER BY addDate DESC");
  while($note=$GLOBALS['db']->fetchNextObject($notes)){$ticket->notes[]=$note;}
 }
 return $ticket;
}

/* -[ Ticket Typology ]--------------------------------------------------- */
// @integer $typology : ticket typology id
// @integer $onlyIcon : show only icon
function api_workflows_ticketTypology($typology,$onlyIcon=FALSE){
 switch($typology){
  case 1:$return=api_icon("icon-map-marker",api_text("ticket-standard"));if(!$onlyIcon){$return.=" ".api_text("ticket-standard");}break;
  case 2:$return=api_icon("icon-envelope",api_text("ticket-external"));if(!$onlyIcon){$return.=" ".api_text("ticket-external");}break;
  case 3:$return=api_icon("icon-check",api_text("ticket-authorization"));if(!$onlyIcon){$return.=" ".api_text("ticket-authorization");}break;
  default:$return="[Ticket typology not found]";
 }
 return $return;
}

/* -[ Ticket SLA ]----------------------------------------------------------- */
// @object $ticket : ticket object
// @boolean $popup : show textual difference in popup
function api_workflows_ticketSLA($ticket,$popup=TRUE){
 if(!$ticket->id){return FALSE;}
 // get timestamp from
 $timestamp_from=$ticket->addDate;
 // get timestamp to
 if($ticket->status==4){$timestamp_to=$ticket->endDate;}
 else{$timestamp_to=date("Y-m-d H:i:s");}
 // get sla
 if($ticket->status==1 && intval($ticket->slaAssignment)>0){$sla=intval($ticket->slaAssignment);}
 else{$sla=intval($ticket->slaClosure);}
 // check sla
 if($sla==0){return FALSE;}
 // check
 $difference=api_timestampDifference($timestamp_from,$timestamp_to,"I");
 if($difference<($sla/2)){$class="text-success";}
 elseif($difference<$sla){$class="text-warning";}
 else{$class="text-error";}
 // format difference
 if($ticket->status==4){
  if($difference<$sla){$difference_txt=api_text("api-sla-completed",api_timestampDifferenceFormat($difference*60,FALSE));}
  else{$difference_txt=api_text("api-sla-completedExpired",api_timestampDifferenceFormat(($difference-$sla)*60,FALSE));}
 }else{
  if($difference<$sla){$difference_txt=api_text("api-sla-expire",api_timestampDifferenceFormat(($sla-$difference)*60,FALSE));}
  else{$difference_txt=api_text("api-sla-expired",api_timestampDifferenceFormat(($difference-$sla)*60,FALSE));}
 }
 // return
 if($popup){
  $return="<a data-toggle='popover' data-placement='top' data-content='".$difference_txt."'>";
  $return.="<span class='".$class."'>".$sla."</span></a>";
 }else{
  $return="<span class='".$class."'>".$sla."</span> ".api_text("api-sla-minutes")." - ".$difference_txt;
 }
 return $return;
}

/* -[ Ticket details modal window ]------------------------------------------ */
// @object $ticket : ticket object
function api_workflows_ticketDetailsModal($ticket){
 if(!$ticket->id){return FALSE;}
 $return=new str_modal("ticket_details_".$ticket->id);
 $return->header(api_text("api-details-mh-ticket",str_pad($ticket->idWorkflow,5,"0",STR_PAD_LEFT)."-".str_pad($ticket->id,5,"0",STR_PAD_LEFT)));
 // build body dl
 $dl_body=new str_dl("br");
 $dl_body->addElement(api_text("api-details-dt-category"),api_workflows_categoryName($ticket->idCategory,TRUE,TRUE));
 $dl_body->addElement(api_text("api-details-dt-add"),api_text("api-details-dd-add",array(api_accountName($ticket->addIdAccount),api_timestampFormat($ticket->addDate,api_text("datetime")))));
 if($ticket->assDate<>NULL){$dl_body->addElement(api_text("api-details-dt-ass"),api_text("api-details-dd-ass",array(api_accountName($ticket->idAssigned),api_timestampFormat($ticket->assDate,api_text("datetime")))));}
 if($ticket->endDate<>NULL){$dl_body->addElement(api_text("api-details-dt-end"),api_timestampFormat($ticket->endDate,api_text("datetime")));}
 if($ticket->updIdAccount<>NULL){$dl_body->addElement(api_text("api-details-dt-upd"),api_text("api-details-dd-upd",array(api_accountName($ticket->updIdAccount),api_timestampFormat($ticket->updDate,api_text("datetime")))));}
 if(strlen($ticket->hostname)>0){$dl_body->addElement(api_text("api-details-dt-hostname"),stripslashes($ticket->hostname));}
 //if(strlen($ticket->note)>0){$dl_body->addElement(api_text("api-details-dt-note"),nl2br(stripslashes($ticket->note)));}
 $dl_body->addElement(api_text("status"),api_workflows_status($ticket->status),NULL);
 $return->body($dl_body->render(FALSE));
 return $return;
}

/* -[ Ticket check process permission ]-------------------------------------- */
// @object $ticket : ticket object or ticket id
// @integer $idAccount : account id
function api_workflows_ticketProcessPermission($ticket,$idAccount=NULL){
 if($idAccount===0 || $idAccount==="0"){$idAccount=api_accountId();}
 if(!$ticket->id){$ticket=api_workflows_ticket($ticket);}
 if(!$ticket->id){return FALSE;}
 if($ticket->idAssigned==$idAccount){return TRUE;}
 if(api_accountGrouprole($ticket->idGroup,$idAccount,TRUE)>0){return TRUE;}
 if($ticket->idGroup==0 && api_accountGroupMember(api_groupId("SIS"))){return TRUE;}
 return FALSE;
}


/* -[ Category object by id ]------------------------------------------------ */
// @param $idCategory : ID of the category
function api_workflows_category($idCategory){
 $category=$GLOBALS['db']->queryUniqueObject("SELECT * FROM workflows_categories WHERE id='".$idCategory."'");
 if(!$category->id){return FALSE;}
 return $category;
}

/* -[ Category name by id ]-------------------------------------------------- */
// @param $idCategory : category id
// @param $parents : Show category parents
// @param $full : Show full name of category parents
function api_workflows_categoryName($idCategory,$parents=FALSE,$full=FALSE,$popup=FALSE){
 $category=api_workflows_category($idCategory);
 if($category->name<>NULL){
  $return=stripslashes($category->name);
  if($parents && $category->idCategory>0){
   $parent=api_workflows_category($category->idCategory);
   if($parent->name<>NULL){
    if($full){$parentsText=stripslashes($parent->name);}
    else{$parentsText=strtoupper(substr(stripslashes($parent->name),0,3));}
   }
   if($parent->idCategory>0){
    $parent=api_workflows_category($parent->idCategory);
    if($parent->name<>NULL){
     if($full){$parentsText=stripslashes($parent->name)." &rarr; ".$parentsText;}
     else{$parentsText=strtoupper(substr(stripslashes($parent->name),0,3))." &rarr; ".$parentsText;}
    }
   }
  }
  // return
  if($popup){
   $return="<a data-toggle='popover' data-placement='top' data-content='".$parentsText."' style='color:#333333;'>".$return."</a>";
  }else{
   if(strlen($parentsText)>0){$parentsText.=" &rarr; ";}
   $return=$parentsText.$return;
  }
  return $return;
 }
 elseif($category->id){return "[ID ".$category->id."]";}
 else{return "[Not found]";}
}

/* -[ Category default group id by category id ]----------------------------- */
// @param $idCategory : category id
function api_workflows_categoryGroup($idCategory){
 $category=api_workflows_category($idCategory);
 if($category->idGroup>0){return $category->idGroup;}
 else{return FALSE;}
}


/* -[ Flow object by id ]---------------------------------------------------- */
// @integer $idFlow : Flow id
// @boolean $subobjects : Load also feasibility subobjects
function api_workflows_flow($idFlow,$subobjects=TRUE){
 $flow=$GLOBALS['db']->queryUniqueObject("SELECT * FROM workflows_flows WHERE id='".$idFlow."'");
 if(!$flow->id){return FALSE;}
 if($subobjects){
  // get flows fields
  $flow->fields=array();
  $fields=$GLOBALS['db']->query("SELECT * FROM workflows_fields WHERE idFlow='".$flow->id."' ORDER BY position ASC");
  while($field=$GLOBALS['db']->fetchNextObject($fields)){$flow->fields[]=$field;}
  // get flows actions
  $flow->actions=array();
  $actions=$GLOBALS['db']->query("SELECT * FROM workflows_actions WHERE idFlow='".$flow->id."' ORDER BY requiredAction ASC");
  while($action=$GLOBALS['db']->fetchNextObject($actions)){$flow->actions[]=$action;}
 }
 return $flow;
}

/* -[ Flow field object by id ]---------------------------------------------- */
// @integer $idField : field id
function api_workflows_flowField($idField){
 $field=$GLOBALS['db']->queryUniqueObject("SELECT * FROM workflows_fields WHERE id='".$idField."'");
 if(!$field->id){return FALSE;}
 return $field;
}

/* -[ Flow action object by id ]--------------------------------------------- */
// @integer $idAction : action id
function api_workflows_flowAction($idAction){
 $action=$GLOBALS['db']->queryUniqueObject("SELECT * FROM workflows_actions WHERE id='".$idAction."'");
 if(!$action->id){return FALSE;}
 return $action;
}

/* -[ Flow fields options by field object ]---------------------------------- */
// @object $field : field object
function api_workflows_flowFieldOptions($field){
 $return=array();
 // if no preset value
 if(!$field->value && $field->required){
  $option_obj=new stdClass();
  $option_obj->value="";
  $option_obj->label=api_text("api-option-undefined");
  $return[]=$option_obj;
 }
 // build field options
 switch($field->options_method){
  // populate options manually
  case "values":
   $options=explode("\n",$field->options_values);
   foreach($options as $option){
    $options_value=explode("=",$option);
    // build option object
    $option_obj=new stdClass();
    $option_obj->value=$options_value[0];
    $option_obj->label=preg_replace('/\r|\n/m','',stripslashes($options_value[1]));
    if($options_value[0]==$field->value){$option_obj->selected=TRUE;}
    else{$option_obj->selected=FALSE;}
    $return[$options_value[0]]=$option_obj;
   }
   break;
  // populate options from a database query
  case "query":
   $options=$GLOBALS['db']->query(stripslashes($field->options_query));
   while($option=$GLOBALS['db']->fetchNextArray($options)){
    // build option object
    $option_obj=new stdClass();
    $option_obj->value=$option[0];
    $option_obj->label=stripslashes($option[1]);
    if($option[0]==$field->value){$option_obj->selected=TRUE;}
    else{$option_obj->selected=FALSE;}
    $return[$option[0]]=$option_obj;
   }
   break;
 }
 return $return;
}


/* -[ Replace Tag Codes in string ]------------------------------------------ */
// @param $string : String with Tag Codes to be replaced
function api_workflows_replaceTagCodes($string){
 // defined tag values
 $tagcodes_array=array(
  "[account-id]"=>$_SESSION['account']->id,
  "[account-mail]"=>api_accountMail(),
  "[account-name]"=>api_accountName(),
  "[account-firstname]"=>api_accountFirstname(),
  "[account-ldap]"=>$_SESSION['account']->ldapUsername,
 );
 // replace tags
 $string=str_replace(array_keys($tagcodes_array),array_values($tagcodes_array),$string);

 // --- da rifare in modo che veda anche più del primo field

 // acquire Flow
 $idFlow=$_GET['idFlow'];

 // dynamic tag field values
 while(($posStart=strpos($string,"[field-"))>0){
  //$tagcodes_array=array();
  $tag=substr($string,$posStart,strpos($string,"]",$posStart)-$posStart+1);
  $field=substr($string,$posStart+7,strpos($string,"]",$posStart)-$posStart-7);
  $field_obj=$GLOBALS['db']->queryUniqueObject("SELECT * FROM workflows_fields WHERE idFlow='".$idFlow."' AND name='".$field."' ORDER BY position ASC");
  $field_obj->options=api_workflows_flowFieldOptions($field_obj);
  // acquire field values by typology
  switch($field_obj->typology){
   // multiselect have array values
   case "multiselect":
    $values=NULL;
    if(is_array($_POST[$field_obj->name])){
     foreach($_POST[$field_obj->name] as $g_option){
      $values.=", ".$field_obj->options[$g_option]->label;
     }
    }
    $value=substr($values,2);
    break;
   // checkbox and radio have text value
   case "checkbox":
   case "radio":
    if($_POST[$field_obj->name]<>NULL){$value=$field_obj->options[$_POST[$field_obj->name]]->label;}
    break;
   // select value is in array
   case "select":
    if($_POST[$field_obj->name]<>NULL){$value=$field_obj->options[$_POST[$field_obj->name]]->label;}
    break;
   // range values
   case "range":
   case "daterange":
   case "datetimerange":
    if($_POST[$field_obj->name."_from"]<>NULL){$value=api_text("form-range-from")." ".$_POST[$field_obj->name."_from"]." ";}
    if($_POST[$field_obj->name."_to"]<>NULL){$value.=api_text("form-range-to")." ".$_POST[$field_obj->name."_to"];}
    break;
   case "file":
    $value=NULL;
    $file=api_file_upload($_FILES[$field_obj->name],"workflows_attachments",NULL,NULL,NULL,NULL,FALSE,NULL,FALSE);
    if($file->id){
     $value=addslashes("<a href='submit.php?act=attachments_download&id=".$file->id."'>".$file->name."</a>");
    }
    break;
   default:
    $value=addslashes($_POST[$field_obj->name]);
  }
  //if(!$value){$value="[not-found-field-".$field."]";}
  //$tagcodes_array[$tag]=$value;

  // replace tags
  $string=str_replace($tag,$value,$string);
 }

 // ---

 // replace tags
 //$string=str_replace(array_keys($tagcodes_array),array_values($tagcodes_array),$string);
 return $string;
}


/* -[ Notifications ]------------------------------------------- */
// @object $ticket : ticket object to notificate
function api_workflows_notifications($ticket){
 if(!is_object($ticket) && is_int($ticket)){$ticket=api_workflows_ticket($ticket);}
 if(!is_object($ticket)){return FALSE;}
 // get workflow object
 $workflow=api_workflows_workflow($ticket->idWorkflow,FALSE);
 // switch ticket typology
 switch($ticket->typology){
  case 1: // ticket standard
   // --
   break;
  case 2: // external ticket
   $subject="Workflows - Ticket ".str_pad($ticket->idWorkflow,5,"0",STR_PAD_LEFT)."-".str_pad($ticket->id,5,"0",STR_PAD_LEFT);
   $subject.=" - ".stripslashes($ticket->subject);
   $message="Salve, con la presente si richiede il vostro intervento per il seguente ticket:\n\n";
   $message.="<strong>Priorità:</strong> ".api_workflows_priority($ticket->priority)."\n\n";
   $message.="<strong>Richiedente:</strong> ".api_accountName($ticket->addIdAccount)."\n\n";
   $message.="<strong>Categoria:</strong> ".api_workflows_categoryName($ticket->idCategory,TRUE,TRUE)."\n\n";
   $message.="<strong>Oggetto:</strong> ".stripslashes($ticket->subject)."\n\n";
   /*if(strlen($ticket->note)>0){
    $message.="<strong>Note</strong>:\n\n";
    $message.=stripslashes($ticket->note)."\n\n\n";
   }*/
   $message.="<strong>Dettagli</strong>:\n\n";
   $message.=stripslashes($workflow->description)."\n\n\n";
   if(strlen($workflow->note)>0){
    $message.="<strong>Note</strong>:\n\n";
    $message.=stripslashes($workflow->note)."\n\n\n";
   }
   $message.="<strong>Responso</strong>:\n\n";
   $message.="<a href='http://".$_SERVER['SERVER_NAME'].$GLOBALS['dir']."workflows/external_submit.php?act=ticket_external&idTicket=".$ticket->id."&idWorkflow=".$ticket->idWorkflow."&solved=1&hash=".$ticket->hash."'>Premi qui per segnalare l'attività come <u>ESEGUITA</u></a>\n";
   $message.="<a href='http://".$_SERVER['SERVER_NAME'].$GLOBALS['dir']."workflows/external_submit.php?act=ticket_external&idTicket=".$ticket->id."&idWorkflow=".$ticket->idWorkflow."&solved=0&hash=".$ticket->hash."'>Premi qui per segnalare l'attività come <u>NON ESEGUIBILE</u></a>\n";
   $message.="<a href='http://".$_SERVER['SERVER_NAME'].$GLOBALS['dir']."workflows/external_submit.php?act=ticket_external&idTicket=".$ticket->id."&idWorkflow=".$ticket->idWorkflow."&solved=2&hash=".$ticket->hash."'>Premi qui per segnalare l'attività come <u>NON NECESSARIA</u></a>\n";
   // sendmail
   api_sendmail($ticket->mail,$message,$subject,TRUE);
   break;
  case 3: // authorization
   $subject="Workflows - Ticket ".str_pad($ticket->idWorkflow,5,"0",STR_PAD_LEFT)."-".str_pad($ticket->id,5,"0",STR_PAD_LEFT);
   $subject.=" - ".stripslashes($ticket->subject);
   $message="Salve, con la presente si richiede la vostra autorizzazione per il seguente ticket:\n\n";
   $message.="<strong>Priorità:</strong> ".api_workflows_priority($ticket->priority)."\n\n";
   $message.="<strong>Richiedente:</strong> ".api_accountName($ticket->addIdAccount)."\n\n";
   $message.="<strong>Categoria:</strong> ".api_workflows_categoryName($ticket->idCategory,TRUE,TRUE)."\n\n";
   $message.="<strong>Oggetto:</strong> ".stripslashes($ticket->subject)."\n\n";
   if(strlen($ticket->note)>0){
    $message.="<strong>Note</strong>:\n\n";
    $message.=stripslashes($ticket->note)."\n\n\n";
   }
   $message.="<strong>Dettagli</strong>:\n\n";
   $message.=stripslashes($workflow->description)."\n\n\n";
   if(strlen($workflow->note)>0){
    $message.="<strong>Note</strong>:\n\n";
    $message.=stripslashes($workflow->note)."\n\n\n";
   }
   $message.="<strong>Autorizzazione</strong>:\n\n";
   $message.="<a href='http://".$_SERVER['SERVER_NAME'].$GLOBALS['dir']."workflows/external_submit.php?act=ticket_authorize&idTicket=".$ticket->id."&idWorkflow=".$ticket->idWorkflow."&authorization=1&hash=".$ticket->hash."'>Premi qui per <u>AUTORIZZARE</u> la richiesta</a>\n";
   $message.="<a href='http://".$_SERVER['SERVER_NAME'].$GLOBALS['dir']."workflows/external_submit.php?act=ticket_authorize&idTicket=".$ticket->id."&idWorkflow=".$ticket->idWorkflow."&authorization=0&hash=".$ticket->hash."'>Premi qui per <u>NON AUTORIZZARE</u> la richiesta</a>\n";
   // sendmail
   api_sendmail($ticket->mail,$message,$subject,TRUE);
   break;
  // if unknown typology
  default:return FALSE;
 }
 return TRUE;
}

?>