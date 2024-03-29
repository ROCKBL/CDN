<?php
session_start();
require_once("../../include/common.php");
$id = (isset($gid) && is_numeric($gid)) ? $gid : 0;
$page = (isset($page) && is_numeric($page)) ? $page : 1;
$type = (isset($type) && is_numeric($type)) ? $type : 1;
$pCount = 0;
$jsoncachefile = sea_DATA."/cache/review/$type/$id.js";
//缓存第一页的评论
if($page<2)
{
	if(file_exists($jsoncachefile))
	{
		$json=LoadFile($jsoncachefile);
		die($json);
	}
}
$h = ReadData($id,$page);
$rlist = array();
if($page<2)
{
	createTextFile($h,$jsoncachefile);
}
die($h);	


function ReadData($id,$page)
{
	global $type,$pCount,$rlist;
	$ret = array("","",$page,0,10,$type,$id);
	if($id>0)
	{
		$ret[0] = Readmlist($id,$page,$ret[4]);
		$ret[3] = $pCount;
		$x = implode(',',$rlist);
		if(!empty($x))
		{
		$ret[1] = Readrlist($x,1,10000);
		}
	}	
	$readData = FormatJson($ret);
	return $readData;
}

function Readmlist($id,$page,$size)
{
	global $dsql,$type,$pCount,$rlist;
	$rlist = str_ireplace('@', "", $rlist);	
	$rlist = str_ireplace('/*', "", $rlist);
	$rlist = str_ireplace('*/', "", $rlist);
	$rlist = str_ireplace('*!', "", $rlist);
	$rlist = str_ireplace('%00', "", $rlist);
	$rlist = str_ireplace('0x', "", $rlist);
	$rlist = str_ireplace('%0b', "", $rlist);
	$rlist = str_ireplace('%23', "", $rlist);
	$rlist = str_ireplace('hex', "", $rlist);	
	$rlist = str_ireplace('updatexml', "update", $rlist);
	$rlist = str_ireplace('extractvalue', "extract", $rlist);
	$rlist = str_ireplace('union', "unio", $rlist);
	$rlist = str_ireplace('benchmark', "bench", $rlist);
	$rlist = str_ireplace('sleep', "slee", $rlist);
	$rlist = str_ireplace('load_file', "", $rlist);
	$rlist = str_ireplace('outfile', "out", $rlist);
	$rlist = str_ireplace('ascii', "asc", $rlist);	
	$rlist = str_ireplace('char(', "cha", $rlist);	
	$rlist = str_ireplace('substr', "sub", $rlist);
	$rlist = str_ireplace('substring', "sub", $rlist);
	$rlist = str_ireplace('script', "scri", $rlist);
	$rlist = str_ireplace('frame', "", $rlist);
	$rlist = str_ireplace('%26', "", $rlist);
	$rlist = str_ireplace('%7c', "", $rlist);
	$rlist = str_ireplace('file_', "fil", $rlist);
	$rlist = str_ireplace('information_schema', "infor", $rlist);
	$rlist = str_ireplace('exp', "ex", $rlist);
	$rlist = str_ireplace('information_schema', "infor", $rlist);
	$rlist = str_ireplace('GeometryCollection', "Geomet", $rlist);
	$rlist = str_ireplace('polygon', "poly", $rlist);
	$rlist = str_ireplace('multipoint', "multi", $rlist);
	$rlist = str_ireplace('multilinestring', "multi", $rlist);
	$rlist = str_ireplace('linestring', "lines", $rlist);
	$rlist = str_ireplace('multipolygon', "multi", $rlist);
	$ml=array();
	if($id>0)
	{
		$sqlCount = "SELECT count(*) as dd FROM sea_comment WHERE m_type=$type AND v_id=$id ORDER BY id DESC";
		$rs = $dsql ->GetOne($sqlCount);
		$pCount = ceil($rs['dd']/$size);
		$sql = "SELECT id,uid,username,dtime,reply,msg,agree,anti,pic,vote,ischeck FROM sea_comment WHERE m_type=$type AND v_id=$id ORDER BY id DESC limit ".($page-1)*$size.",$size ";
		$dsql->setQuery($sql);
		$dsql->Execute('commentmlist');
		while($row=$dsql->GetArray('commentmlist'))
		{
			$row['reply'].=ReadReplyID($id,$row['reply'],$rlist);
			$ml[]="{\"cmid\":".$row['id'].",\"uid\":".$row['uid'].",\"tmp\":\"\",\"nick\":\"".$row['username']."\",\"face\":\"\",\"star\":\"\",\"anony\":".(empty($row['username'])?1:0).",\"from\":\"".$row['username']."\",\"time\":\"".date("Y/n/j H:i:s",$row['dtime'])."\",\"reply\":\"".$row['reply']."\",\"content\":\"".$row['msg']."\",\"agree\":".$row['agree'].",\"aginst\":".$row['anti'].",\"pic\":\"".$row['pic']."\",\"vote\":\"".$row['vote']."\",\"allow\":\"".(empty($row['anti'])?0:1)."\",\"check\":\"".$row['ischeck']."\"}";
		}
	}
	$readmlist=join($ml,",");
	return $readmlist;
}

function Readrlist($ids,$page,$size)
{
	global $dsql,$type;
	$rl=array();
	$sql = "SELECT id,uid,username,dtime,reply,msg,agree,anti,pic,vote,ischeck FROM sea_comment WHERE m_type=$type AND id in ($ids) ORDER BY id DESC";
	$dsql->setQuery($sql);
	$dsql->Execute('commentrlist');
	while($row=$dsql->GetArray('commentrlist'))
	{
		$rl[]="\"".$row['id']."\":{\"uid\":".$row['uid'].",\"tmp\":\"\",\"nick\":\"".$row['username']."\",\"face\":\"\",\"star\":\"\",\"anony\":".(empty($row['username'])?1:0).",\"from\":\"".$row['username']."\",\"time\":\"".$row['dtime']."\",\"reply\":\"".$row['reply']."\",\"content\":\"".$row['msg']."\",\"agree\":".$row['agree'].",\"aginst\":".$row['anti'].",\"pic\":\"".$row['pic']."\",\"vote\":\"".$row['vote']."\",\"allow\":\"".(empty($row['anti'])?0:1)."\",\"check\":\"".$row['ischeck']."\"}";
	}
	$readrlist=join($rl,",");
	return $readrlist;
}

function ReadReplyID($gid,$cmid,&$rlist)
{
	global $dsql;
	$rlist = str_ireplace('@', "", $rlist);	
	$rlist = str_ireplace('/*', "", $rlist);
	$rlist = str_ireplace('*/', "", $rlist);
	$rlist = str_ireplace('*!', "", $rlist);
	$rlist = str_ireplace('%00', "", $rlist);
	$rlist = str_ireplace('0x', "", $rlist);
	$rlist = str_ireplace('%0b', "", $rlist);
	$rlist = str_ireplace('%23', "", $rlist);
	$rlist = str_ireplace('hex', "", $rlist);	
	$rlist = str_ireplace('updatexml', "update", $rlist);
	$rlist = str_ireplace('extractvalue', "extract", $rlist);
	$rlist = str_ireplace('union', "unio", $rlist);
	$rlist = str_ireplace('benchmark', "bench", $rlist);
	$rlist = str_ireplace('sleep', "slee", $rlist);
	$rlist = str_ireplace('load_file', "", $rlist);
	$rlist = str_ireplace('outfile', "out", $rlist);
	$rlist = str_ireplace('ascii', "asc", $rlist);	
	$rlist = str_ireplace('char(', "cha", $rlist);	
	$rlist = str_ireplace('substr', "sub", $rlist);
	$rlist = str_ireplace('substring', "sub", $rlist);
	$rlist = str_ireplace('script', "scri", $rlist);
	$rlist = str_ireplace('frame', "fra", $rlist);
	$rlist = str_ireplace('information_schema', "infor", $rlist);
	$rlist = str_ireplace('exp', "ex", $rlist);
	$rlist = str_ireplace('information_schema', "infor", $rlist);
	$rlist = str_ireplace('GeometryCollection', "Geomet", $rlist);
	$rlist = str_ireplace('polygon', "poly", $rlist);
	$rlist = str_ireplace('multipoint', "multi", $rlist);
	$rlist = str_ireplace('multilinestring', "multi", $rlist);
	$rlist = str_ireplace('linestring', "lines", $rlist);
	$rlist = str_ireplace('multipolygon', "multi", $rlist);
	if($cmid>0)
	{
		if(!in_array($cmid,$rlist))$rlist[]=$cmid;
		$row = $dsql->GetOne("SELECT reply FROM sea_comment WHERE id=$cmid limit 0,1");
		if(is_array($row))
		{
			$ReplyID = ",".$row['reply'].ReadReplyID($gid,$row['reply'],$rlist);
		}else
		{
			$ReplyID = "";
		}
	}else
	{
		$ReplyID = "";
	}
	return $ReplyID;
}

function FormatJson($json)
{
	$x = "{\"mlist\":[%0%],\"rlist\":{%1%},\"page\":{\"page\":%2%,\"count\":%3%,\"size\":%4%,\"type\":%5%,\"id\":%6%}}";
	for($i=6;$i>=0;$i--)
	{
		$x=str_replace("%".$i."%",$json[$i],$x);
	}
	$formatJson = jsonescape($x);
	return $formatJson;
}

function jsonescape($txt)
{
	$jsonescape=str_replace(chr(13),"",str_replace(chr(10),"",json_decode(str_replace("%u","\u",json_encode("".$txt)))));
	return $jsonescape;
}