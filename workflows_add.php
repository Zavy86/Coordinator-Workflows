<?php
/* -------------------------------------------------------------------------- *\
|* -[ Workflows - Add ]------------------------------------------------------ *|
\* -------------------------------------------------------------------------- */
$checkPermission="workflows_add";
require_once("template.inc.php");
function content(){
 // acquire variables
 $g_category=$_GET['idCategory'];
 if(!$g_category){$g_category=0;}
 // get flow object
 $flow=api_workflows_flow($_GET['idFlow'],TRUE);
 // show workflow informations
 if($flow->id>0){
  echo "<h4>".stripslashes($flow->subject);
  if(strlen($flow->description)>0){echo " &rarr; <small class='muted'>".stripslashes(nl2br($flow->description))."</small>";}
  echo "</h4>\n";
  if(strlen($flow->advice)>0){echo "<p>".stripslashes(nl2br($flow->advice))."</p>\n";}
 }
 echo "<br>\n";
 // build form
 $form=new str_form("submit.php?act=workflow_save&idFlow=".$flow->id,"post","workflows_add");
 if(!$flow->id){
  // flow fields
  $form->addField("select","idCategory",api_text("add-ff-category"));
  $categories=$GLOBALS['db']->query("SELECT * FROM workflows_categories WHERE idCategory='0' ORDER BY name ASC");
  while($category=$GLOBALS['db']->fetchNextObject($categories)){
   $form->addFieldOption($category->id,stripslashes($category->name),($category->id==$g_category)?TRUE:FALSE);
   $subcategories=$GLOBALS['db']->query("SELECT * FROM workflows_categories WHERE idCategory='".$category->id."' ORDER BY name ASC");
   while($subcategory=$GLOBALS['db']->fetchNextObject($subcategories)){
    $form->addFieldOption($subcategory->id,"&minus; ".stripslashes($subcategory->name),($subcategory->id==$g_category)?TRUE:FALSE);
    $subsubcategories=$GLOBALS['db']->query("SELECT * FROM workflows_categories WHERE idCategory='".$subcategory->id."' ORDER BY name ASC");
    while($subsubcategory=$GLOBALS['db']->fetchNextObject($subsubcategories)){
     $form->addFieldOption($subsubcategory->id,"&nbsp; &minus; ".stripslashes($subsubcategory->name),($subsubcategory->id==$g_category)?TRUE:FALSE);
    }
   }
  }
  $form->addField("radio","typology",api_text("add-ff-typology"));
  $form->addFieldOption(1,api_text("typology-request"),TRUE);
  $form->addFieldOption(2,api_text("typology-incident"));
  $form->addField("text","subject",api_text("add-ff-subject"),NULL,"input-xxlarge");
  $form->addField("radio","priority",api_text("add-ff-priority"));
  $form->addFieldOption(1,api_text("priority-highest"));
  $form->addFieldOption(2,api_text("priority-high"));
  $form->addFieldOption(3,api_text("priority-medium"),TRUE);
  $form->addFieldOption(4,api_text("priority-low"));
  $form->addFieldOption(5,api_text("priority-lowest"));
 }else{
  // hidden fields from
  $form->addField("hidden","idCategory",NULL,$flow->idCategory);
  $form->addField("hidden","typology",NULL,$flow->typology);
  $form->addField("hidden","subject",NULL,$flow->subject);
  $form->addField("hidden","priority",NULL,$flow->priority);
  // flow fields
  foreach($flow->fields as $field){
   // build filed
   $form->addField($field->typology,$field->name,stripslashes($field->label),api_workflows_replaceTagCodes($field->value),$field->class,$field->placeholder);
   $field_options=api_workflows_flowFieldOptions($field);
   if(is_array($field_options)){
    foreach($field_options as $option){
     $form->addFieldOption($option->value,$option->label,$option->selected);
    }
   }
  }
 }
 // defaults fields
 $form->addField("text","referent",api_text("add-ff-referent"),api_accountName(),"input-medium");
 $form->addField("text","phone",api_text("add-ff-phone"),NULL,"input-small");
 $form->addField("textarea","note",api_text("add-ff-note"),NULL,"input-xxlarge");
 // controls
 $form->addControl("submit",api_text("add-fc-submit"));
 $form->addControl("button",api_text("add-fc-cancel"),NULL,"workflows_search.php?idCategory=".$g_category);
 // show form
 $form->render();
?>
<script type="text/javascript">
 $(document).ready(function(){
  // validation
  $('form').validate({
   rules:{
<?php
    foreach($flow->fields as $field){
     if($field->required){echo "    ".$field->name.":{required:true},\n";}
    }
?>
    phone:{required:true}
   },
   submitHandler:function(form){form.submit();}
  });
 });
</script>
<?php } ?>