<?php
/* -------------------------------------------------------------------------- *\
|* -[ Workflows - Template ]------------------------------------------------- *|
\* -------------------------------------------------------------------------- */
include("module.inc.php");
include("../core/api.inc.php");
api_loadModule(array("registries","materials"));
// print header
$html->header(api_text("module-title"),$module_name);

// acquire variables
$g_id=$_GET['id'];
if(!$g_id){$g_id=0;}
$g_idTicket=$_GET['idTicket'];
if(!$g_idTicket){$g_idTicket=0;}
$g_idWorkflow=$_GET['idWorkflow'];
if(!$g_idWorkflow){$g_idWorkflow=0;}
if($g_idWorkflow>0){$g_id=$g_idWorkflow;}

// get workflow object
$workflow=api_workflows_workflow($g_id,FALSE);

// get ticket object
$ticket=api_workflows_ticket($g_idTicket,TRUE);

// search box
if(api_baseName()=="workflows_list.php" ||
   api_baseName()=="workflows_search.php" ||
   api_baseName()=="workflows_flows_list.php"){
 $search=TRUE;
}else{
 $search=FALSE;
}

// build navigation
global $navigation;
$navigation=new str_navigation($search,"idCategory");

// workflows tickets list
$navigation->addTab(api_text("nav-workflows"),"workflows_list.php");

// operations
if($workflow->id){
 $navigation->addTab(api_text("nav-operations"),NULL,NULL,"active");
 $navigation->addSubTab(api_text("nav-solicit"),"#submit.php?act=");
 $navigation->addSubTab(api_text("nav-addTicket"),"workflows_view.php?id=".$workflow->id."&act=addTicket");
 if($ticket->id){
  //$navigation->addSubTab(api_text("nav-assign"),"#submit.php?act=");

 }
}

$navigation->addTab(api_text("nav-open"),"workflows_search.php");

// selected
if(api_baseName()=="workflows_flows_list.php" ||
   api_baseName()=="workflows_flows_view.php" ||
   api_baseName()=="workflows_flows_edit.php" ||
   api_baseName()=="workflows_categories.php"){
 $class="active";
}else{
 $class=NULL;
}

$navigation->addTab(api_text("nav-administration"),NULL,NULL,$class);
$navigation->addSubTab(api_text("nav-list"),"workflows_flows_list.php");
$navigation->addSubTab(api_text("nav-add"),"workflows_flows_edit.php");
$navigation->addSubTab(api_text("nav-categories"),"workflows_categories.php");

// filters
if(api_baseName()=="workflows_list.php"){
 $navigation->addFilter("multiselect","status",api_text("filter-status"),array(1=>api_text("filter-opened"),2=>api_text("filter-assigned"),3=>api_text("filter-standby"),4=>api_text("filter-closed"),5=>api_text("filter-locked")));
 // if not filtered load default filters
 if($_GET['filtered']<>1){$_GET['status']=array(1,2,3);}
}
if(api_baseName()=="workflows_list.php" || api_baseName()=="workflows_flows_list.php"){
 $categories_array=array();
 $categories=$GLOBALS['db']->query("SELECT * FROM workflows_categories WHERE idCategory='0' ORDER BY name ASC");
 while($category=$GLOBALS['db']->fetchNextObject($categories)){
  //$categories_array[$category->id]=$category->name;
  $categories_array[$category->id]=api_workflows_categoryName($category->id,TRUE);
  $subcategories=$GLOBALS['db']->query("SELECT * FROM workflows_categories WHERE idCategory='".$category->id."' ORDER BY name ASC");
  while($subcategory=$GLOBALS['db']->fetchNextObject($subcategories)){
   //$categories_array[$subcategory->id]="&minus; ".$subcategory->name;
   $categories_array[$subcategory->id]=api_workflows_categoryName($subcategory->id,TRUE);
   $subsubcategories=$GLOBALS['db']->query("SELECT * FROM workflows_categories WHERE idCategory='".$subcategory->id."' ORDER BY name ASC");
   while($subsubcategory=$GLOBALS['db']->fetchNextObject($subsubcategories)){
    //$categories_array[$subsubcategory->id]="&nbsp;&nbsp; &minus; ".$subsubcategory->name;
    $categories_array[$subsubcategory->id]=api_workflows_categoryName($subsubcategory->id,TRUE);
   }
  }
 }
 $navigation->addFilter("multiselect","idCategory",api_text("filter-category"),$categories_array,"input-xlarge");

 // if not filtered load default filters
 if($_GET['filtered']<>1){}
}
if(api_baseName()=="workflows_list.php" || api_baseName()=="workflows_flows_list.php"){
 $navigation->addFilter("multiselect","typology",api_text("filter-typology"),array(1=>api_text("typology-request"),2=>api_text("typology-incident")),"input-xlarge");
}
// show navigation
$navigation->render();
// check permissions before displaying module
if($checkPermission==NULL){content();}else{if(api_checkPermission($module_name,$checkPermission,TRUE)){content();}}
// print footer
$html->footer();
?>