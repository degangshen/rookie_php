<?php
/**
 * 后加flexigrid table插件
 */
$page = $_POST['page'];
$rp = $_POST['rp'];
$sortname = $_POST['sortname'];
$sortorder = $_POST['sortorder'];

$query = $_POST['query']; //查询的内容
$qtype = $_POST['qtype']; //查询的字段名

if ($qtype && $query)
{
	$where_query = "$qtype like '%$query%'";
}
else 
{
	$where_query = "1=1";
}

if (!$sortname) $sortname = 'name';
if (!$sortorder) $sortorder = 'desc';

if (!$page) $page = 1;
//$rp = 3; //每页显示的条数

$start = (($page-1) * $rp);

$icon = array('&nbsp;&nbsp;&nbsp;│ ','&nbsp;&nbsp;&nbsp;├─ ','&nbsp;&nbsp;&nbsp;└─ ');		

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT" ); 
header("Last-Modified: " . gmdate( "D, d M Y H:i:s" ) . "GMT" ); 
header("Cache-Control: no-cache, must-revalidate" ); 
header("Pragma: no-cache" );
header("Content-type: text/xml");
$xml = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
$xml .= "<rows>";
$xml .= "<page>$page</page>";
?>