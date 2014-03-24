<?php
/* -------------------------------------------------------------------------- *\
|* -[ Workflows - Flow View ]------------------------------------------------ *|
\* -------------------------------------------------------------------------- */
$checkPermission="workflows_admin";
include("template.inc.php");
function content(){
 // acquire variables
 $g_idField=$_GET['idField'];
 if(!$g_idField){$g_idField=0;}
 $g_idAction=$_GET['idAction'];
 if(!$g_idAction){$g_idAction=0;}
 $g_act=$_GET['act'];
 if(!$g_act){$g_act=NULL;}
 // get flow object
 $flow=api_workflows_flow($_GET['idFlow']);
 // get selected field object
 $selected_field=api_workflows_flowField($_GET['idField']);
 // get selected action object
 $selected_action=api_workflows_flowAction($_GET['idAction']);
 // build workflow dynamic list
 $flow_dl=new str_dl("br","dl-horizontal");
 $flow_dl->addElement(api_text("flows_view-dt-category"),api_workflows_categoryName($flow->idCategory,TRUE,TRUE));
 $flow_dl->addElement(api_text("flows_view-dt-subject"),"<strong>".stripslashes($flow->subject)."</strong>");
 $flow_dl->addElement(api_text("flows_view-dt-typology"),api_workflows_typology($flow->typology));
 $flow_dl->addElement(api_text("flows_view-dt-priority"),api_workflows_priority($flow->priority));
 if($flow->pinned){$flow_dl->addElement("&nbsp;",api_text("flows_view-dd-pinned"));}
 $flow_dl->addElement(api_text("flows_view-dt-sla"),$flow->sla." ".api_text("minutes"),NULL);
 // build details dynamic list
 $details_dl=new str_dl("br","dl-horizontal");
 $details_dl->addElement(api_text("flows_view-dt-description"),nl2br(stripslashes($flow->description)));
 $details_dl->addElement(api_text("flows_view-dt-advice"),nl2br(stripslashes($flow->advice)),NULL);
 // build fields table
 $fields_table=new str_table(api_text("flows_view-fields-tr-unvalued"));
 // build fields table headers
 $fields_table->addHeader("&nbsp;",NULL,16);
 $fields_table->addHeader("&nbsp;",NULL,16);
 $fields_table->addHeader(api_text("flows_view-fields-th-label"),"nowarp");
 $fields_table->addHeader(api_text("flows_view-fields-th-typology"),"nowarp");
 $fields_table->addHeader(api_text("flows_view-fields-th-name"),"nowarp");
 $fields_table->addHeader(api_text("flows_view-fields-th-value"),"nowarp");
 $fields_table->addHeader(api_text("flows_view-fields-th-options"),NULL,"100%",NULL,2);
 $fields_table->addHeader("&nbsp;",NULL,16);
 // build fields table rows
 foreach($flow->fields as $field){
  $fields_table->addRow();
  // position
  if($field->position>1){$position="<a href='submit.php?act=flow_field_move_up&idFlow=".$flow->id."&idField=".$field->id."'>".api_icon("icon-arrow-up")."</a>";}
  if($field->position<count($flow->fields)){$position.="<a href='submit.php?act=flow_field_move_down&idFlow=".$flow->id."&idField=".$field->id."'>".api_icon("icon-arrow-down")."</a>";}
  // build fields table fields
  $fields_table->addField($position);
  $fields_table->addField(($field->required)?api_icon("icon-ok-circle"):"&nbsp;");
  $fields_table->addField(stripslashes($field->label),"nowarp");
  $fields_table->addField(stripslashes($field->typology),"nowarp");
  $fields_table->addField(stripslashes($field->name),"nowarp");
  $fields_table->addField(stripslashes($field->value),"nowarp");
  $fields_table->addField(stripslashes($field->options_method));
  if($field->options_method=="values"){
   $fields_table->addField("<small>".nl2br(stripslashes($field->options_values))."</small>");
  }elseif($field->options_method=="query"){
   $fields_table->addField("<small>".stripslashes($field->options_query)."</small>");
  }else{
   $fields_table->addField("&nbsp;");
  }
  $fields_table->addField("<a href='workflows_flows_view.php?idFlow=".$flow->id."&idField=".$field->id."&act=editField'>".api_icon("icon-edit")."</a>");
 }
 // build actions table
 $actions_table=new str_table(api_text("flows_view-actions-tr-unvalued"));
 // build actions table headers
 $actions_table->addHeader("&nbsp;",NULL,16);
 $actions_table->addHeader(api_text("flows_view-actions-th-condition"),"nowarp");
 $actions_table->addHeader("!","text-center",16);
 $actions_table->addHeader(api_text("flows_view-actions-th-subject"),NULL,"100%");
 $actions_table->addHeader(api_text("flows_view-actions-th-assigned"),"nowarp text-right");
 $actions_table->addHeader(api_text("flows_view-actions-th-group"),"nowarp text-center");
 $actions_table->addHeader("&nbsp;",NULL,16);
 // build fields table rows
 foreach($flow->actions as $action){
  $actions_table->addRow();
  // require idAction
  $require=NULL;
  if($action->idAction>0){
   $required_action_name=$GLOBALS['db']->queryUniqueValue("SELECT subject FROM workflows_actions WHERE id='".$action->idAction."'");
   $require="<a data-toggle='popover' data-placement='top' data-content=\"[".$action->idAction."] ".stripslashes(str_replace("\n","| ",$required_action_name))."\">".api_icon("icon-repeat")."</i></a> ";
  }
  // condition
  $condition="*";
  if($action->conditioned){
   $conditioned_field=$GLOBALS['db']->queryUniqueObject("SELECT * FROM workflows_fields WHERE id='".$action->idField."'");
   $condition=stripslashes($conditioned_field->name)."=".$action->value;
  }
  // build actions table fields
  $actions_table->addField(api_workflows_ticketTypology($action->typology,TRUE),"nowarp");
  $actions_table->addField(stripslashes($condition),"nowarp");
  $actions_table->addField(stripslashes($action->priority),"text-center");
  $actions_table->addField($require."[".$action->id."] ".stripslashes($action->subject));
  $actions_table->addField(api_accountName($action->idAssigned),"nowarp text-right");
  $actions_table->addField(api_groupName($action->idGroup,TRUE,TRUE),"nowarp text-center");
  $actions_table->addField("<a href='workflows_flows_view.php?idFlow=".$flow->id."&idAction=".$action->id."&act=editAction'>".api_icon("icon-edit")."</a>");
 }



 // build fields modal window
 $field_modal=new str_modal("field_edit");
 if($selected_field->id){
  if(strlen($selected_field->label)>0){$label=stripslashes($selected_field->label);}
  else{$label=stripslashes($selected_field->name);}
  $field_modal->header(api_text("flows_view-fields-mh-field",$label));
 }else{
  $field_modal->header(api_text("flows_view-fields-mh-fieldAdd"));
 }
 // build fields modal form
 $form_body=new str_form("submit.php?act=flow_field_save&idFlow=".$flow->id."&idField=".$selected_field->id,"post","field_edit");
 $form_body->addField("text","label",api_text("flows_view-fields-ff-label"),$selected_field->label,"input-large",api_text("flows_view-fields-ff-label-placeholder"));
 $form_body->addField("select","typology",api_text("flows_view-fields-ff-typology"),NULL,"input-medium");
 $typologies=array("hidden","text","password","checkbox","radio","select","multiselect","textarea","file","slider","range","date","datetime","daterange","datetimerange");
 foreach($typologies as $typology){$form_body->addFieldOption($typology,ucwords($typology),($typology==$selected_field->typology)?TRUE:FALSE);}
 $form_body->addField("text","name",api_text("flows_view-fields-ff-name"),$selected_field->name,"input-large",api_text("flows_view-fields-ff-name-placeholder"));
 $form_body->addField("text","value",api_text("flows_view-fields-ff-value"),$selected_field->value,"input-large",api_text("flows_view-fields-ff-value-placeholder"));
 $form_body->addField("select","class",api_text("flows_view-fields-ff-class"),NULL,"input-medium");
 $form_body->addFieldOption("",api_text("flows_view-fields-ff-classNull"));
 $classes=array("input-mini","input-small","input-medium","input-large","input-xlarge","input-xxlarge");
 foreach($classes as $class){$form_body->addFieldOption($class,$class,($class==$selected_field->class)?TRUE:FALSE);}
 $form_body->addField("text","placeholder",api_text("flows_view-fields-ff-placeholder"),$selected_field->placeholder,"input-xlarge",api_text("flows_view-fields-ff-placeholder-placeholder"));
 $form_body->addField("select","options_method",api_text("flows_view-fields-ff-optionsMethod"),NULL,"input-medium");
 $methods=array("none","values","query");
 foreach($methods as $method){$form_body->addFieldOption($method,ucwords($method),($selected_field->options_method==$method)?TRUE:FALSE);}
 $form_body->addField("textarea","options_values",api_text("flows_view-fields-ff-optionsValues"),stripslashes($selected_field->options_values),"input-xlarge",api_text("flows_view-fields-ff-optionsValues-placeholder"));
 $form_body->addField("text","options_query",api_text("flows_view-fields-ff-optionsQuery"),stripslashes($selected_field->options_query),"input-xlarge",api_text("flows_view-fields-ff-optionsQuery-placeholder"));
 $form_body->addField("checkbox","required","&nbsp;");
 $form_body->addFieldOption(1,api_text("flows_view-fields-ff-required"),($selected_field->required)?TRUE:FALSE);
 $form_body->addControl("submit",api_text("flows_view-fields-fc-submit"));
 if($selected_field->id){$form_body->addControl("button",api_text("flows_view-fields-fc-delete"),"btn-danger","submit.php?act=flow_field_delete&idFlow=".$flow->id."&idField=".$selected_field->id,api_text("flows_view-fields-fc-delete-confirm"));}
 $field_modal->body($form_body->render(FALSE));





 




 // show flow
 echo "<h5>".api_text("flows_view-flow")." - <a href='workflows_flows_edit.php?idFlow=".$flow->id."'>".api_text("flows_view-flow-edit")."</a></h5>\n";
 // open split
 $GLOBALS['html']->split_open();
 $GLOBALS['html']->split_span(6);
 // show workflow dynamic list
 $flow_dl->render();
 // split page
 $GLOBALS['html']->split_span(6);
 // show details dynamic list
 $details_dl->render();
 // close split
 $GLOBALS['html']->split_close();
 // show fields table
 echo "<h5>".api_text("flows_view-fields")." - <a href='workflows_flows_view.php?idFlow=".$flow->id."&act=addField'>".api_text("flows_view-fields-add")."</a></h5>\n";
 $fields_table->render();
 // show actions table
 echo "<h5>".api_text("flows_view-actions")." - <a href='workflows_flows_view.php?idFlow=".$flow->id."&act=addField'>".api_text("flows_view-actions-add")."</a></h5>\n";
 $actions_table->render();
 // show fields modal windows
 $field_modal->render();
 // show actions modal windows
 //$action_modal->render();



 /*
?>



<!-- ModalPopup for modalAction -->
<div id='modalAction' class='modal hide fade' role='dialog' aria-hidden='true'>
<div class='modal-header'>
<button type='button' class='close' data-dismiss='modal' aria-hidden='true'>&times;</button>
<?php
 if($selected_action->id>0){
  echo "<h4>Azione: ".stripslashes($selected_action->name)."</h4>\n";
 }else{
  echo "<h4>Nuova azione</h4>\n";
 }
?>
</div>
<div class='modal-body'>

<form class="form-horizontal" action="<?php echo "submit.php?act=workflow_action_save&idWorkflow=".$workflow->id."&id=".$selected_action->id;?>" method="post">

 <div class="control-group">
  <label class="control-label">Tipologia</label>
  <div class="controls">
   <select id="typology" name="typology">
    <option value="1"<?php if($selected_action->typology==1){echo " selected";} ?>>Ticket standard</option>
    <option value="2"<?php if($selected_action->typology==2){echo " selected";} ?>>Ticket gestito esternamente</option>
    <option value="3"<?php if($selected_action->typology==3){echo " selected";} ?>>Richiesta di autorizzazione</option>
   </select>
  </div>
 </div>

 <div id="toggleMail">
  <div class="control-group">
   <label class="control-label" for="mail">Indirizzo mail</label>
   <div class="controls"><input type="text" id="mail" class="input-large" name="mail" placeholder="Mail per la gestione del ticket" value="<?php echo stripslashes($selected_action->mail);?>"></div>
  </div>
 </div>

 <div class="control-group">
  <label class="control-label">Azione richiesta</label>
  <div class="controls">
   <select name="idAction">
    <option value="0">Nessuna</option>
    <?php
     $actions=$GLOBALS['db']->query("SELECT * FROM workflows_actions WHERE idWorkflow='".$workflow->id."' ORDER BY idAction ASC,name ASC");
     while($action=$GLOBALS['db']->fetchNextObject($actions)){
      if($action->id<>$selected_action->id){
       echo "<option value='".$action->id."'";
       if($action->id==$selected_action->idAction){echo " selected";}
       echo ">[".$action->id."] ".stripslashes($action->name);
       echo "</option>\n";
      }
     }
    ?>
   </select>
  </div>
 </div>

 <div class="control-group">
  <label class="control-label">Condizionata da</label>
  <div class="controls">
   <select id="idField" name="idField">
    <option value="0">Nessuna condizione</option>
    <?php
     $fields=$GLOBALS['db']->query("SELECT * FROM workflows_fields WHERE idWorkflow='".$workflow->id."'");
     while($field=$GLOBALS['db']->fetchNextObject($fields)){
      echo "<option value='".$field->id."'";
      if($field->id==$selected_action->idField){echo " selected";}
      echo ">".stripslashes($field->name);
      echo "</option>\n";
     }
    ?>
   </select>
  </div>
 </div>

 <div id="toggleConditioned">
  <div class="control-group">
   <label class="control-label" for="value">Valore</label>
   <div class="controls"><input type="text" id="value" class="input-large" name="value" placeholder="Valore condizionale del campo" value="<?php echo stripslashes($selected_action->value);?>"></div>
  </div>
 </div>

 <div class="control-group">
  <label class="control-label" for="value">Nome attivit&agrave;</label>
  <div class="controls"><input type="text" id="name" class="input-xlarge" name="name" placeholder="Titolo dell'attivit&agrave;" value="<?php echo stripslashes($selected_action->name);?>"></div>
 </div>

 <div id="dGroup" class="control-group">
  <label class="control-label" for="idGroup">Gruppo</label>
  <div class="controls">
   <input type="hidden" id="idGroup" name="idGroup" class="input-large" value="<?php echo stripslashes($selected_action->idGroup);?>">
  </div>
 </div>

 <div id="dSingle" class="control-group">
  <label class="control-label" for="idAssigned">Utente</label>
  <div class="controls">
   <input type="hidden" id="idAssigned" name="idAssigned" class="input-large" value="<?php echo stripslashes($selected_action->idAssigned);?>">
  </div>
 </div>

 <div class="control-group">
  <label class="control-label">Priorit&agrave;</label>
  <div class="controls">
   <select name="priority">
    <option value="1"<?php if($selected_action->priority==1){echo " selected";} ?>>Bassa</option>
    <option value="2"<?php if($selected_action->priority==2 || $selected_action->id==0){echo " selected";} ?>>Media</option>
    <option value="3"<?php if($selected_action->priority==3){echo " selected";} ?>>Alta</option>
   </select>
  </div>
 </div>

 <div class="control-group">
  <label class="control-label">Difficolt&agrave;</label>
  <div class="controls">
   <select name="difficulty">
    <option value="1"<?php if($selected_action->difficulty==1){echo " selected";} ?>>Bassa</option>
    <option value="2"<?php if($selected_action->difficulty==2 || $selected_action->id==0){echo " selected";} ?>>Media</option>
    <option value="3"<?php if($selected_action->difficulty==3){echo " selected";} ?>>Alta</option>
   </select>
  </div>
 </div>

 <div class="control-group">
  <div class="controls">
   <input type='submit' class='btn btn-primary' name='submit' value='Salva'>
   <?php if($selected_action->id>0){echo "<a class='btn btn-danger' href='submit.php?act=workflow_action_delete&idWorkflow=".$workflow->id."&id=".$selected_action->id."' onclick='return confirm(\"Sei sicuro di voler eliminare questa azione?\")'>Elimina</a>";} ?>
  </div>
 </div>

</form>

</div>
</div>


*/?>





<script type="text/javascript">
 $(document).ready(function(){

  // call typology change event on page load
  $("#field_typology").trigger("change");
  // call options method change event
  $("#field_options_method").trigger("change");

  <?php
   // modals
   switch($g_act){
    case "addField":
    case "editField":
     echo "$('#modal_field_edit').modal('show');\n";
     break;
    case "addAction":
    case "editAction":
     echo "$('#modal_action_edit').modal('show');\n";
     break;
   }
  ?>


/*


   //if(!$selected_action->id>0 || $selected_action->typology==1){echo "$('#toggleMail').hide();\n";}
   //if($selected_action->idField==0){echo "$('#toggleConditioned').hide();\n";}

  // toggle mail
  $("#typology").on('change',function(){
   if($("#typology option:selected").val()==1){
    $("#toggleMail").hide();
   }else{
    $("#toggleMail").show();
   }
  });
  // toggle conditioned
  $("#idField").on('change',function(){
   if($("#idField option:selected").val()>0){
    $("#toggleConditioned").show();
   }else{
    $("#toggleConditioned").hide();
   }
  });
  // select2 idGroup
  $("#idGroup").select2({
   placeholder:"Seleziona un gruppo",
   allowClear:true,
   ajax:{
    url:"../accounts/groups_json.inc.php",
    dataType:'json',
    data:function(term,page){return{q:term};},
    results:function(data,page){return{results:data};}
   },
   initSelection:function(element,callback){
    var id=$(element).val();
    if(id!==""){
     $.ajax("../accounts/groups_json.inc.php?q="+id,{
      dataType:"json"
     }).done(function(data){callback(data[0]);});
    }
   }
  });
  // select2 idAccountTo
  $("#idAssigned").select2({
   placeholder:"Seleziona un utente",
   minimumInputLength:2,
   allowClear:true,
   ajax:{
    url:"../accounts/accounts_json.inc.php",
    dataType:'json',
    data:function(term,page){return{q:term};},
    results:function(data,page){return{results:data};}
   },
   initSelection:function(element,callback){
    var id=$(element).val();
    if(id!==""){
     $.ajax("../accounts/accounts_json.inc.php?q="+id,{
      dataType:"json"
     }).done(function(data){callback(data[0]);});
    }
   }
  });
*/

  // validation
  $('form[name=field_edit]').validate({
   rules:{
    label:{required:true},
    name:{required:true}
   },
   submitHandler:function(form){form.submit();}
  });

 });

 // toggle typology options
 $("#field_typology").change(function(){
  switch($(this).find("option:selected").val()){
   case "checkbox":
   case "radio":
   case "select":
   case "multiselect":
    $("#field_options_method").show();
    break;
   default:
    $("#field_options_method option[value=none]").attr("selected","selected");
    $("#field_options_method").hide();
    // call options method change event
    $("#field_options_method").trigger("change");
  }
 });

 // toggle options method
 $("#field_options_method").change(function(){
  if($(this).find("option:selected").val()==="values"){
   $("#field_options_values").show();
   $("#field_options_query").hide();
  }else if($(this).find("option:selected").val()==="query"){
   $("#field_options_values").hide();
   $("#field_options_query").show();
  }else{
   $("#field_options_values").hide();
   $("#field_options_query").hide();
  }
 });

</script>
<?php } ?>