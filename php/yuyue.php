<?php
require_once("../../include/common.inc.php");

$validate = $_REQUEST['validate'];
if(empty($validate)) $validate=='';
else $validate = strtolower($validate);
$svali = GetCkVdValue();

$ip = GetIP();
if(!isset($name) || $name==''){
	echo '<script language="JavaScript">window.alert("����������");location.href="index.html"</script>';
	exit();	
}
if(!isset($age) || !is_numeric($age)){
	echo '<script language="JavaScript">window.alert("��������ȷ������");location.href="index.html"</script>';
	exit();	
}

if(!isset($hometel) || $hometel==''){
	echo '<script language="JavaScript">window.alert("������绰����");location.href="index.html"</script>';
	exit();	
}

$inQuery = "INSERT INTO `#@__yuyue` (`name`,`age`,`hometel`,`ill`,`ip`) VALUES ('$name','$age','$hometel','$ill', '$ip');
;";

if(!$dsql->ExecuteNoneQuery($inQuery)){
	$gerr = $dsql->GetError();
	$dsql->Close();
	ShowMsg("��������ʱ��������ϵ����Ա��".$gerr,"-1");
	exit();
}
echo '<script language="JavaScript">window.alert("�����ɹ�,���ȷ������");location.href="index.html"</script>';
?>