<?php
/*
_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/
_/
_/   Forumin v1.5 [15-09-2025]
_/
_/   Alexand3r ~  alexand3r2@mail.ru ~ KICQ 2655740 ~ http://vox.dx.am
_/  
_/   https://dzen.ru/alexblr
_/   https://youtube.com/@alexblr
_/   https://t.me/www_vox_dx_am
_/
_/   ������ EI ����� Copyright (c) 2004 ����� 
_/
_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/
*/

error_reporting(0);
//error_reporting(E_ALL);

session_start();

include "config.php";

$knopki="0"; // ������ �� � �� ��� �������� ���������

$valid_types_load=array("z", "zip", "rar", "7z", "jpg", "jpeg", "gif", "png"); // ���������� ����������� ������

$valid_types=array("gif", "jpg", "png", "jpeg"); // ���������� ����������� ��������

$maxfsize=round($max_file_size/10.24)/100; // ���������� ��� ������� ��


$hst=$_SERVER["HTTP_HOST"];
$self=$_SERVER["PHP_SELF"];
$furl=str_replace('index.php', '', "http://$hst$self");



////////////////// ����� ��������� ������� (������)
$time_gen = microtime();
$time_gen = explode(' ', $time_gen);
$start_gen = $time_gen[1] + $time_gen[0];


////////////////// ��������� thumbnails
//$src - �������� ����
//$dest - ������������ ����
//$width, $height - ������ � ������ ������������� �����������, ��������
//$size - ������� �������
//$quality - �������� JPEG

function img_resize($src, $dest, $width, $height, $size, $name, $quality=92)
{
	global $smwidth;
	if (!file_exists($src)) return false;
	if ($size==false) return false;

	//���������� �������� ������ �� MIME-���������� �������� getimagesize � �������� ��������������� ������� imagecreatefrom-�������
	$format=substr(strstr($size['mime'], '/'), 1);
	$icfunc="imagecreatefrom".$format;

	if (!function_exists($icfunc)) return false;

	//���������� ������ ������ ��� ����� ������ 3000�2000
	if ($size[0]>3000 || $size[1]>2000) {ini_set("memory_limit", "128M");}

	$isrc=$icfunc($src);
	$idest=imagecreatetruecolor($width, $height);

	imagecopyresampled($idest, $isrc, 0, 0, 0, 0, $width, $height, $size[0], $size[1]);

	if($width>$smwidth) // ������� �������
	{
		function _Kiril_latin($path)
		{
			return strtr($path,array("�"=>"a", "�"=>"b", "�"=>"v", "�"=>"g", "�"=>"d", "�"=>"e", "�"=>"jo", "�"=>"zh", "�"=>"z", "�"=>"i", "�"=>"j", "�"=>"k", "�"=>"l", "�"=>"m", "�"=>"n", "�"=>"o", "�"=>"p", "�"=>"r", "�"=>"s", "�"=>"t", "�"=>"u", "�"=>"f", "�"=>"x", "�"=>"c", "�"=>"ch", "�"=>"sh", "�"=>"shh", "�"=>"''", "�"=>"y", "�"=>"'", "�"=>"je", "�"=>"ju", "�"=>"ya", "��"=>"j/o", "��"=>"j/e", "�"=>"A", "�"=>"B", "�"=>"V", "�"=>"G", "�"=>"D", "�"=>"E", "�"=>"JO", "�"=>"ZH", "�"=>"Z", "�"=>"I", "�"=>"J", "�"=>"K", "�"=>"L", "�"=>"M", "�"=>"N", "�"=>"O", "�"=>"P", "�"=>"R", "�"=>"S", "�"=>"T", "�"=>"U", "�"=>"F", "�"=>"X", "�"=>"C", "�"=>"CH", "�"=>"SH", "�"=>"SHH", "�"=>"''", "�"=>"Y", "�"=>"'", "�"=>"JE", "�"=>"JU", "�"=>"YA", "��"=>"J/O", "��"=>"J/E"));
		}

		$copyrite=_Kiril_latin($name);
		$host=$_SERVER["HTTP_HOST"];
		//$host=_Kiril_latin($host);

		$textcolor=imagecolorallocate($idest, 255, 255, 255); //���� ������
		$backcolor=imagecolorallocate($idest, 0, 0, 0); //���� ����� ������

		$texthx=$width-strlen($host)*7.25; //X ��������������� ������
		$texthy=$height-15; //Y ��������������� ������
		$textvx=$width-16; //X ������������� ������
		$textvy=$height-20; //Y ������������� ������

		//����� ����� �������������
		imagestring($idest, 3, $texthx-1, $texthy, $host, $backcolor);
		imagestring($idest, 3, $texthx+1, $texthy, $host, $backcolor);
		imagestring($idest, 3, $texthx, $texthy-1, $host, $backcolor);
		imagestring($idest, 3, $texthx, $texthy+1, $host, $backcolor);
		imagestring($idest, 3, $texthx, $texthy, $host, $textcolor);

		//����� ����� �����������
		imagestringup($idest, 3, $textvx-1, $textvy, $copyrite, $backcolor);
		imagestringup($idest, 3, $textvx+1, $textvy, $copyrite, $backcolor);
		imagestringup($idest, 3, $textvx, $textvy-1, $copyrite, $backcolor);
		imagestringup($idest, 3, $textvx, $textvy+1, $copyrite, $backcolor);
		imagestringup($idest, 3, $textvx, $textvy, $copyrite, $textcolor);
	}
	imagejpeg($idest, $dest, $quality);
	imagedestroy($isrc);
	imagedestroy($idest);
	return true;
}
//////////////////

switch ($_GET['mode']) {
	case 'link': create_tmb($_GET['img']); break;
	case 'error': create_error(); break;
	case 'board': create_tmb('$filedir/'.$_GET['img']); break;
}

function create_error() {
	header("Content-type: image/png");
	$imsrc='$fskin/closed.gif';
	$img=imagecreatefrompng($imsrc);
	imagepng($img);
	imagedestroy($img);
	return;
}

function create_tmb($bigimgsrc) {
	global $smwidth;
	$rgb=0xFFFFFF;
	$quality=90;
	$width=$smwidth;

	if ($size = @getimagesize($bigimgsrc))
	{
		if ($size===false) return false;

		$format=strtolower(substr($size['mime'], strpos($size['mime'], '/')+1));
		$icfunc="imagecreatefrom" . $format;

		if (!function_exists($icfunc)) return false;

		$x_ratio=$size[0]/$width;
		$height=floor($size[1]/$x_ratio);
		header("Content-type: image/jpg");
		$bigimg=$icfunc($bigimgsrc);
		$trumbalis=imagecreatetruecolor($width, $height);
		imagefill($trumbalis, 0, 0, $rgb);
		imagecopyresampled($trumbalis, $bigimg, 0, 0, 0, 0, $width, $height, $size[0], $size[1]);
		imagejpeg($trumbalis);
		flush();
		imagedestroy($bigimg);
		imagedestroy($trumbalis);
	} else {
		create_error();
	}
	return;
}


////////////////// ��������� ��������
function replace_img_link($imlink)
{
	global $smwidth;

	if (ini_get('allow_url_fopen') && ($size = @getimagesize($imlink)) !== FALSE)
	{
		if ($size[0] <= $smwidth)
		{
			$imgtag="<img src=\"$imlink\" border=\"0\"> ";
		} else {
			$imgtag="<img src=\"index.php?mode=link&img=$imlink\" border=\"0\" style=\"border: 1px solid #ddd; cursor:pointer\" onclick=\"TINY.box.show({image:'$imlink',boxid:'frameless',animate:true})\">";
		}
	} else {
		$imgtag="<img src=\"$imlink\" border=\"0\"> ";
	}
	return $imgtag;
}

function ikoncode($post)
{ 
	if (preg_match_all("#\[img\](.*?)\[\/img\]#is", $post, $matches, PREG_SET_ORDER))
	{
		for($a=0;$a<count($matches);$a++)
		{
			if (preg_match("#\[img\](.*?)(script:|\'|\`|\?|\&|;|mailto:|\"| |=)(.*?)\[/img\]#is", $matches[$a][0],$out))
			{
				$patern[$a]= addcslashes($out[0],"\*\#\%\/\.\?\[\]\&\"\'\:\+\(\)\\\n");
				$patern[$a]= "#".$patern[$a]."#si";
				$replace[$a]="<span class=small>[<font color=red>������ ".$matches[$a][1]."</font>]</span>";
			} else {
				$patern[$a]= addcslashes($matches[$a][0],"\*\#\%\/\.\?\[\]\&\"\'\:\+\(\)\\\n");
				$patern[$a]= "#".$patern[$a]."#si";
				$replace[$a]=($imgpreview = 1) ?replace_img_link($matches[$a][1]):"<img src=".$matches[$a][1].">";
			}
		}
		$post=preg_replace($patern,$replace,$post);
	}
	$post=str_replace(' <br> ','<br>',$post);
	$post=stripslashes($post);
	return $post;
}



////////////////// ������� ����
function replacer($text) {
	//$text=stripslashes($text);
	//$text=str_replace("�", '', $text);
	$text=str_replace("&#032;", ' ', $text);
	$text=str_replace(">", '&gt;', $text);
	$text=str_replace("<", '&lt;', $text);
	$text=str_replace("\"", '&quot;', $text);
	$text=str_replace("'", '&apos;', $text);
	$text=preg_replace("/\\\$/", '&#036;', $text);
	$text=preg_replace("/\\\/", '&#092;', $text);
	if (get_magic_quotes_gpc()) {
		$text=str_replace("&#092;&quot;", '&quot;', $text);
		$text=str_replace("&#092;'", '\'', $text);
		$text=str_replace("&#092;&#092;", '&#092;', $text);
	}
 	$text=str_replace("\r\n", '<br>', $text);
	$text=str_replace("\n", '<br>', $text);
	$text=str_replace("\t", ' ', $text);
	$text=str_replace("\r", '', $text);
	$text = preg_replace("/\s\s+/", ' ', $text);
	return $text;
}



////////////////// ������� ������ Hide
function hideguest($hide)
{
	global $user;
	if ($user)
	{
		$hide="<br><br><fieldset style='width:95%;border:dotted 1px #777;'><legend align=left class=med>����� ����� �� ������</legend>$hide</fieldset><br>";
		return $hide;
	} else {
		$hide="<br><br><fieldset style='width:95%;border:dotted 1px #777;'><legend align=left class=med>������ �� ������</legend><i>������ ������������������ ������������ ����� ������ ���� �����!</i></div></fieldset><br>";
		return $hide;
	}
}

////////////////// ������� ������ Hide ��� �������������
function hideuser($hidename, $hidetext)
{
	global $user, $name;
	if ($_COOKIE['cname']==$hidename && $user || $user===$name) //$_COOKIE['cadmin']==$adminname & $_COOKIE['cpass']==$adminpass && strstr($puuu[13], '�������������'))
	{
		$hidename=" <span style='background-color:#555;font-style:italic;color:#ddd'>&nbsp;����� ��� <b>$hidename</b>: $hidetext</span> ";
		return $hidename;
	} else {
		$hidename=" <span style='background-color:#555;font-style:italic;color:#ddd'>������ <b>$hidename</b> � <b>�����</b> ����� ���� �����!</span> ";
		return $hidename;
	}
}


////////////////// ������� �������� ������������
function is_user()
{
	$uf=file("datan/usersdat.php");
	for($i=1;$i<sizeof($uf);$i++)
	{
		if ($uf[$i]) {
			$pu=explode('|',$uf[$i]);
			if ($pu[0]==$_COOKIE['cname'] && md5($pu[1])==$_COOKIE['cpassreg']) return $pu[0];
		}
	}
	return 0;
}

// ��������� ������, ���������� ��� �����
$_ = array();

// �������� ������������
$_['user'] = 0;

// ����� �������� ��� ��������� �� ������������ ����� ������ ��� �� �������� �������
$_['user'] = $user = is_user();


////////////////// <br> � \n
function br2n($text)
{
	$text=str_replace("<br>", '
', $text);
	$text=str_replace("<br />", '
', $text);
	return $text;
}


////////////////// \n � <br>
function n2br($text)
{
	$text=str_replace("\r", '', $text);
	$text=str_replace("\n", '<br>', $text);
	return $text;
}


/////////////// ������� ��� ����������� ��������
function get_dir($path = './', $mask = '*.php', $mode = GLOB_NOSORT)
{
	if (version_compare(phpversion(), '4.3.0', '>='))	{
		if (chdir($path)) {$temp=glob($mask,$mode); return $temp;}
	} return false;
}


////////////////// ������� ���������
$num = 7;
$cfile = file_get_contents("datan/counter.dat");

if (filesize("datan/counter.dat") >= 1) {
	if (!isset($_COOKIE['countplus']) || $_COOKIE['countplus'] != 1) {
		++$cfile;
		file_put_contents("datan/counter.dat", $cfile, LOCK_EX);
		@setcookie('countplus', 1, time()+86400);
	}
}
$cnum = str_pad($cfile, $num, '0', STR_PAD_LEFT);
$numLength = strlen($cnum);

//for ($i = 0; $i < $numLength; ++$i) {print '<img src="./num/' . $cnum[$i] . '.gif">';}



////////////////// �������������� ������
function autolink($str, $attributes=array()) {
	$attrs = '';
	foreach ($attributes as $attribute => $value) {$attrs .= " {$attribute}=\"{$value}\"";}
	$str = ' ' . $str;
	$str = preg_replace('`([^"=\'>])((http|https|ftp)://[^\s<]+[^\s<\.)])`i', '$1<a href="$2"'.$attrs.' target="_new">$2</a>', $str);
	$str = substr($str, 1);
	return $str;
}


////////////////// ������� 1
function remBadWordsA($text) {
	global $badwords, $cons;
	$mat=count($badwords);
	for ($i=0; $i<$mat; $i++) {
		$text=preg_replace("/".$badwords[$i]."/is", $cons, $text);
	}
	return $text;
}

////////////////// ������� 2
function remBadWordsB($text) {
	global $cons;
	$pattern=('/(
		(?:\s+|^)(?:[��n��p]?[3���B��n���pP�aA��oO0�]?[��cC��uU�oO0��aA�����y��T]?|\w*[���aA�0oO])[��n][��uUeE��][��3][��Dd]\w*[\?\,\.\!\;\-]*|
		(?:\s+|^)\w{0,4}[��oO0��uU��aAcC����3��T��y]?[Xx��][��y][����e��Ee��9����uU]\w*[\?\,\.\;\-\!]*|
		(?:\s+|^)[���n�6][��][��9]+(?:[����DT]\w*)?[\?\,\.\;\!\-]*|
		(?:\s+|^)\w*[���n�6][��][��9][����DT]\w+[\?\,\.\;\-\!]*|
		(?:\s+|^)(?:\w*[��oO0��������aA��3��y��e])?[��eE��uU��][��6��](?:[��oO0����aA��H��uU��y����e��kKE]\w*)?[\?\,\!\.\;\-]*|
		(?:\s*|^)?[����][��][��][��xX]?[����]?[��kK]?\w*[\?\,\!\.\;\-]*|
		(?:\s*|^)?[��cC][��]?[�y�]+[��]?[�kK�]\w*[\?\,\!\.\;\-]*|
		(?:\s*|^)?[��n][uU��][��][aA����oO0][�p�]\w*[\?\,\!\.\;\-]*|
		(?:\s*|^)?[��][�oO���aA][�H�][��][oO�0�][��H]\w*[\?\,\!\.\;\-]*|
		(?:\s*|^)?\w*[3��][��aA��oO0][�K][�y�][��n]\w*[\?\,\!\.\;\-]*)/x');
	$text = preg_replace("$pattern", "$cons", $text);
	return $text;
}

////////////////// ����� �������
if (isset($_REQUEST['add'])) {
	if (strtolower($_REQUEST['secpic']) !=$_SESSION['secpic']) {
		@header("Content-type: text/html; charset=windows-1251");
		echo "<html><head><meta http-equiv='Content-Type' content='text/html; charset=windows-1251'></head><body><center><br><br><br><br><font face=tahoma size=2><b>������� ������ �������� ���!</b><br><br><a href=\"javascript:history.back()\">&#9668; �����</a></font></center>"; exit();
	}
}

////////////////// ����� �������
if (isset($_GET['secpic'])) {
	if ($captcha==1) {$letters=array('0','1','2','3','4','5','6','7','8','9');} else {$letters=array('a','b','c','d','e','f','g','h','j','k','m','n','p','q','r','s','t','u','v','w','x','y','z','2','3','4','5','6','7','9');}
	$colors=array('10','30','50','70','90','110','130','150','170','190','210');
	$src=imagecreatetruecolor($width,$height);
	$fon=imagecolorallocate($src,255,255,255);
	imagefill($src,0,0,$fon);
	$fonts=array();
	$dir=opendir($path_fonts);
	while($fontName=readdir($dir)) {
		if ($fontName != "." && $fontName !="..") $fonts[]=$fontName;
	}
	closedir($dir);
	for($i=0;$i<$fon_let_amount;$i++) {
		$color=imagecolorallocatealpha($src,rand(0,255),rand(0,255),rand(0,255),100);
		$font=$path_fonts.$fonts[rand(0,sizeof($fonts)-1)];
		$letter=$letters[rand(0,sizeof($letters)-1)];
		$size=rand($font_size-2,$font_size+2);
		imagettftext($src,$size,rand(0,45),rand($width*0.1,$width-$width*0.1),rand($height*0.2,$height),$color,$font,$letter);
	}
	for($i=0;$i<$let_amount;$i++) {
		$color=imagecolorallocatealpha($src,$colors[rand(0,sizeof($colors)-1)],$colors[rand(0,sizeof($colors)-1)],$colors[rand(0,sizeof($colors)-1)],rand(20,40));
		$font=$path_fonts.$fonts[rand(0,sizeof($fonts)-1)];
		$letter=$letters[rand(0,sizeof($letters)-1)];
		$size=rand($font_size*2.1-2,$font_size*2.1+2);
		$x=($i+1)*$font_size + rand(4,7);
		$y=(($height*2)/3) + rand(0,5);
		$cod[]=$letter;
		imagettftext($src,$size,rand(0,15),$x,$y,$color,$font,$letter);
	}
	$_SESSION['secpic']=implode('',$cod);
	header("Content-type: image/png");
	imagepng($src);
}


////////////////// ����������� IP
function getIP() {
	$check = array('HTTP_CLIENT_IP','HTTP_X_REAL_IP','HTTP_X_CLUSTER_CLIENT_IP','HTTP_X_FORWARDED_FOR','HTTP_X_FORWARDED','HTTP_FORWARDED_FOR','REMOTE_ADDR');
	$ip = '0.0.0.0';
	foreach ($check as $akey) {
		if (isset($_SERVER[$akey])) {list($ip)=explode(',', $_SERVER[$akey]); break;}
	}
	return $ip;
}
$ip = replacer(getIP());


////////////////// ��� �� IP
if ($antiham==1) {
	$_b=0;
	$e=explode(' ', file_get_contents("datan/badip.dat"));
	foreach($e as $v)
	if (@strstr($ip, $v)) exit("<div align=center><br><br><br><br><br><fieldset style='width:400px;border: #333 1px solid;'><legend align='center'><font size=2 face=tahoma color=red><b>�� �������������!</b></font></legend><br><center><font size=2 face=tahoma><b>������������� �������� ��� ������������ �������!</b></font></center><br></fieldset></div>");
}





////////////////// ��� �� IP �������� ����. ���� ��� ����� �����, �� ���� �� ������ ������ ���� 





////////////// ��������� (�����)
if (isset($_GET['forumid']))
{
	$fc=file("datan/topic.dat");
	for ($i=0; $i<sizeof($fc); $i++)
	{
		$dtc=explode('�',$fc[$i]);
		if ($dtc[2]==$_GET['forumid'])
		{
			$clickfile='data/'.$dtc[2].'.dat';
			$ccookies="$ip|$dtc[2]";
			$mfc=explode("|",replacer($_COOKIE['count_click']));

			if (!isset($_COOKIE['count_click']))
			{
				@setcookie('count_click',$ccookies,time()+3600,'/',$_SERVER['SERVER_NAME']);
				$count = file_get_contents($clickfile);
				$count++;
				file_put_contents($clickfile, $count, LOCK_EX);
			} else {
				if ($_COOKIE['count_click'] != $mfc[0]) @setcookie('count_click',$ccookies,time()+3600,'/',$_SERVER['SERVER_NAME']);
				if ($mfc[1] != $dtc[2])
				{
					$count = file_get_contents($clickfile);
					$count++;
					file_put_contents($clickfile, $count, LOCK_EX);
				}
			}
		}
	}
}
//$countcl = file_get_contents($clickfile);




////////////////// ���� - ����� - ������� ����
if (isset($_GET['event'])) {
	if ($_GET['event']=="clearuser") {
		@setcookie("cname","",time(),"/");
		@setcookie("cmail","",time(),"/");
		@setcookie("cpassreg","",time(),"/");
		@header("Location: index.php");
		exit;
	}
}


////////////////// ����� - ����� - ������� ����
if (isset($_GET['event'])) {
	if ($_GET['event']=="clearadmin") {
		@setcookie("cadmin","",time(),"/");
		@setcookie("cpass","",time(),"/");
		@header("Location: index.php");
	}
}


////////////////// ������� ��������
$timezone=floor($timezone);
if ($timezone<-12 || $timezone>12) $timezone = 0;



////////////////// �����������
if (isset($_GET['mode']) and $_GET['mode']=="reg")
{
	if (isset($_POST['name']) && isset($_POST['mail']) && isset($_POST['passreg']))
	{
		$name=trim(str_replace("|", '', $_POST['name']));
		$mail=trim(str_replace("|", '', $_POST['mail']));
		$passreg=trim(str_replace("|", '', $_POST['passreg']));
		$datee=gmdate('d.m.Y', time() + 3600*($timezone+(date('I')==1?0:1)));

		if (preg_match("/[^(\\w)|(\\x7F-\\xFF)|(\\-)]/", $name))
			exit("
				<div align=center><br><br><br><br><br><fieldset style='width:400px;border:#333 1px solid;'><legend align='center'><font size=2 face=tahoma color=red><b>������</b></font></legend>
				<br><center><font size=2 face=tahoma><b>��������� ������� � ����. �����, �����, ����. � ����</b></font></center>
				<br></fieldset></div><br><p align=center><a href='index.php?mode=reg' style='text-decoration:none;'>&#9668; �����</a></p>"); 

		if ($name=="" or strlen($name)>$maxname or strlen($name)<3)
			exit("
				<div align=center><br><br><br><br><br><fieldset style='width:400px;border:#333 1px solid;'><legend align='center'><font size=2 face=tahoma color=red><b>������</b></font></legend>
				<br><center><font size=2 face=tahoma><b>��� ������������ ������ ���� �� 3 �� $maxname ����.</b></font></center>
				<br></fieldset></div><br><p align=center><a href='index.php?mode=reg' style='text-decoration:none;'>&#9668; �����</a></p>");

		if ($passreg=="" or strlen($passreg)<3 or strlen($passreg)>10)
			exit("
				<div align=center><br><br><br><br><br><fieldset style='width:400px; border: #333 1px solid;'><legend align='center'><font size=2 face=tahoma color=red><b>������</b></font></legend>
				<br><center><font size=2 face=tahoma><b>������ ������ ���� �� 3 �� 10 ��������!</b></font></center>
				<br></fieldset></div><br><p align=center><a href='index.php?mode=reg' style='text-decoration:none;'>&#9668; �����</a></p>");

		if (!preg_match("/^[a-z0-9\.\-_]+@[a-z0-9\-_]+\.([a-z0-9\-_]+\.)*?[a-z]+$/is", $mail) or $mail=="" or strlen($mail)>$maxmail)
			exit("
				<div align=center><br><br><br><br><br><fieldset style='width:400px; border: #333 1px solid;'><legend align='center'><font size=2 face=tahoma color=red><b>������</b></font></legend>
				<br><center><font size=2 face=tahoma><b>��� Email ������������, ���� ������ $maxmail ��������!</b></font></center>
				<br></fieldset></div><br><p align=center><a href='index.php?mode=reg' style='text-decoration:none;'>&#9668; �����</a></p>");

		if (isset($_POST['pol'])) $pol=trim($_POST['pol']); else $pol="";

		if ($pol!="�������") $pol="�������";

		/////////////// ��� ���������
		$z=1;
		do {
			$userkey=mt_rand(1000000,9999999);
			if (strlen($userkey)==7) $z++;
		} while ($z<1);

		$userstatus=replacer($userstatus);

		/////////////// ���� ����� � ����� ������� ��� �������
		$loginsm=strtolower($name);
		$lines=file("datan/usersdat.php");
		$i=count($lines);
		if ($i>"1") {
			do {
				$i--;
				$rdt=explode("|",$lines[$i]); 
				$rdt[0]=strtolower($rdt[0]);
				if ($rdt[0]===$loginsm) {$bad="1"; $er="������";}
				if ($rdt[3]===$mail) {$bad="1"; $er="�������� �������";}
			} while($i > 1);

			if (isset($bad)) exit("
					<div align=center><br><br><br><br><br>
					<fieldset style='width:350px; border: #333 1px solid;'><legend align='center'><font size=2 face=tahoma color=red><b>������</b></legend>
					<br><center><b>�������� � ����� $er ��� ���������������!</b></font></center><br>
					</fieldset></div><br><br><p align=center><a href='index.php?mode=reg' style='text-decoration:none;'>&#9668; �����</a></p>");
		}

		$text="$name|$passreg|0|$mail|$datee||$pol||||||noavatar.gif|$userstatus||||";
		$text=replacer($text);

		@setcookie("cname",$name,(time()+300000000),"/");
		@setcookie("cmail",$mail,(time()+300000000),"/");
		@setcookie("cpassreg",md5($passreg),(time()+300000000),"/");

		/////////////// ���������� ���� � �������
		$fp=fopen("datan/usersdat.php","a+");
		flock($fp,LOCK_EX);
		fputs($fp,"$text\r\n");
		fflush($fp);
		flock($fp,LOCK_UN);
		fclose($fp);

		/////////////// ���������� ������� � ������ � ���� �� �����������
		$fp=fopen("datan/userstat.dat","a+");
		flock($fp,LOCK_EX);
		fputs($fp,"$name|0|0|0|0|||||\r\n");
		fflush($fp);
		flock($fp,LOCK_UN);
		fclose($fp);

		$riuser="<meta http-equiv='pragma' content='no-cache'><br><br><br><div align=center><fieldset align=center style='width:300px;border:#333 1px solid;'>
			<legend align=center style='border:#333 1px solid;background-color:#999;color:green;padding:2px 2px;'><b>����������� ������ �������!</b></legend>
			<table align=center cellpadding=4 cellspacing=4 border=0><tr><td align=right><b>�����:</b></td><td>$name</td></tr>
			<tr><td align=right><b>������:</b></td><td>$passreg</td></tr><tr><td align=right><b>E-mail:</b></td><td>$mail</td></tr>
			</table></fieldset></div>";
	}
}



////////////////// �������
if (isset($_GET['mode']))
{
	if ($_GET['mode']=="admin")
	{
		if (isset($_POST['admin']) && isset($_POST['pass']))
		{
			$admin=$_POST['admin'];
			$pass=$_POST['pass'];

			if ($admin==$adminname && md5("$pass")==$adminpass)
			{
				@setcookie("cadmin",$admin,(time()+30000000),"/");
				@setcookie("cpass",md5($pass),(time()+30000000),"/");

				$riadmin="
					<meta http-equiv='pragma' content='no-cache'><br><br><br>
					<div align=center>	<fieldset align=center style='width:300px; border: #333 1px solid;'>
					<legend align=center><b><font color=red>�� � ������ ��������������!</font></b></legend>
					<table align=center cellpadding=4 cellspacing=4 border=0>
					<tr><td align=right><b>�����:</b></td><td>$admin</td></tr>
					<tr><td align=right><b>������:</b></td><td>$pass</td></tr>
					</table></fieldset></div>";
			}
		}
	}
}


/////////////// ���� �� �����, �������� �����/������
if (isset($_GET['event']))
{
	if ($_GET['event']=="regenter")
	{
		if (!isset($_POST['name']) & !isset($_POST['passreg'])) exit("<br><br><br><center><font size=2 face=tahoma><b>������� ��� � ������!</b><br><p align=center>[<a href=\"index.php?event=login\">��������� �����</a>]</p>");

		$name=str_replace("|", '', $_POST['name']);
		$pass=str_replace("|", '', $_POST['passreg']);
		$text=trim(replacer("$name|$pass|"));

		if (strlen($text)<3) 	exit("<br><br><br><center><font size=2 face=tahoma><b>�� �� ����� ��� ��� ������!</b><br><br><br><p align=center>[<a href=\"index.php?event=login\">��������� �����</a>]</p>");

		$exd=explode("|",$text);
		//$name=strtolower($exd[0]);
		$name=$exd[0];
		$pass=$exd[1];

		// �������� �� ���� ������������� � ������� ������
		$lines=file("datan/usersdat.php");
		$i=count($lines);
		do {
			$i--;
			$rdt=explode("|",$lines[$i]);
			if (isset($rdt[1])) // ���� ������� �� �����
			{
				if ($name===$rdt[0] && $pass===$rdt[1])
				{
					$regenter="$i";
					$cmail = $rdt[3];
					@setcookie("cname", $name, (time()+300000000),"/");
					@setcookie("cmail", $cmail, (time()+300000000),"/");
					@setcookie("cpassreg", md5($pass), (time()+300000000),"/");

					$tektime=time();
				}
			}
		} while($i>"1");

		if (!isset($regenter)) exit("<br><br><br><br><center><font size=2 face=tahoma><B>���� ������ �� �����!</B><br><br><br><p align=center>[<a href=\"index.php?event=login\">��������� �����</a>]</p>");

		header("Location: index.php");
	}
}



/////////////// �������������� ������� - ���������� ������
if (isset($_GET['event']))
{
	if ($_GET['event']=="reregist")
	{
		if (!isset($_POST['name']))
			exit("
				<div align=center><br><br><br><br><br><fieldset style='width:350px; border: #333 1px solid;'><legend align='center'><font size=2 face=tahoma color=red><b>������</b></font></legend>
				<br><center><font size=2 face=tahoma><b>�� �� ����� ��� ���!</b></font></center>
				<br></fieldset></div><br><br><p align=center><a href='javascript:history.back(1)' style='text-decoration:none;'>&#9668; �����</a></p>");

		$name=trim(str_replace("|", '', $_POST['name']));

		if ($name=="" or strlen($name)>$maxname or strlen($name)<3)
			exit("
				<div align=center><br><br><br><br><br><fieldset style='width:400px; border: #333 1px solid;'><legend align='center'><font size=2 face=tahoma color=red><b>������</b></font></legend>
				<br><center><font size=2 face=tahoma><b>��� ������ ���� �� 3 �� $maxname ��������!</b></font></center>
				<br></fieldset></div><br><br><p align=center><a href='javascript:history.back(1)' style='text-decoration:none;'>&#9668; �����</a></p>");

		if (preg_match("/[^(\\w)|(\\x7F-\\xFF)|(\\-)]/", $name))
			exit("
				<div align=center><br><br><br><br><br><fieldset style='width:400px; border: #333 1px solid;'><legend align='center'><font size=2 face=tahoma color=red><b>������</b></font></legend>
				<br><center><font size=2 face=tahoma><b>��������� ������� � ����. �����, �����, �������, ����</b></font></center>
				<br></fieldset></div><br><br><p align=center><a href='javascript:history.back(1)' style='text-decoration:none;'>&#9668; �����</a></p>");

		if (!isset($_POST['pass']))
			exit("
				<div align=center><br><br><br><br><br><fieldset style='width:400px; border: #333 1px solid;'><legend align='center'><font size=2 face=tahoma color=red><b>������</b></font></legend>
				<br><center><font size=2 face=tahoma><b>����������� ����� ������ �� 3 �� 10 ����.</b></font></center>
				<br></fieldset></div><br><br><p align=center><a href='javascript:history.back(1)' style='text-decoration:none;'>&#9668; �����</a></p>");

		$pass=replacer(str_replace("|", '', $_POST['pass']));
		$oldpass=$_POST['oldpass'];

		if (strlen($pass)<3 or strlen($pass)>10)
			exit("
				<div align=center><br><br><br><br><br><fieldset style='width:400px; border: #333 1px solid;'><legend align='center'><font size=2 face=tahoma color=red><b>������</b></font></legend>
				<br><center><font size=2 face=tahoma><b>����������� ����� ������ �� 3 �� 10 ����.</b></font></center>
				<br></fieldset></div><br><br><p align=center><a href='javascript:history.back(1)' style='text-decoration:none;'>&#9668; �����</a></p>");

		if (isset($_POST['email'])) $email=strtolower($_POST['email']); else $email="";

		if (!preg_match("/^[a-z0-9\.\-_]+@[a-z0-9\-_]+\.([a-z0-9\-_]+\.)*?[a-z]+$/is", $email) or $email=="" or strlen($email)>$maxmail)
			exit("
				<div align=center><br><br><br><br><br><fieldset style='width:400px; border: #333 1px solid;'><legend align='center'><font size=2 face=tahoma color=red><b>������</b></font></legend>
				<br><center><font size=2 face=tahoma><b>��������� Email ������������, ���� ��������� $maxmail ����.</b></font></center>
				<br></fieldset></div><br><br><p align=center><a href='javascript:history.back(1)' style='text-decoration:none;'>&#9668; �����</a></p>");

		if (isset($_POST['dayx'])) $dayx=replacer($_POST['dayx']); else $dayx="";
		if (isset($_POST['pol'])) $pol=replacer($_POST['pol']); else $pol="";
		if ($pol!="�������") $pol="�������";
		if (isset($_POST['icq'])) $icq=replacer($_POST['icq']); else $icq="";
		if (isset($_POST['telegram'])) $telegram=replacer($_POST['telegram']); else $telegram="";
		if (isset($_POST['www'])) $www=replacer($_POST['www']); else $www="";
		if (isset($_POST['about'])) $about=replacer($_POST['about']); else $about="";
		if (isset($_POST['work'])) $work=replacer($_POST['work']); else $work="";
		if (isset($_POST['write'])) $write=replacer($_POST['write']); else $write="";
		if (isset($_POST['avatar'])) $avatar=replacer($_POST['avatar']); else $avatar="";
		if (isset($_POST['cflag'])) $cflag=replacer($_POST['cflag']); else $cflag="";

		$notgood="<br><br><a href='javascript:history.back(1)' style='text-decoration:none;'>&#9668; �����</a></b></font></p>";

		if (strlen($dayx)>10) exit("<br><br><br><p align=center><font size=2 face=tahoma><b>������� ����� ������ ���� �������� (���� 10 ����) $notgood");
		if (strlen($icq)>12) exit("<br><br><br><p align=center><font size=2 face=tahoma><b>������� ����� ������ KICQ (���� 12 ����) $notgood");
		if (strlen($telegram)>40) exit("<br><br><br><p align=center><font size=2 face=tahoma><b>������� ����� ������ Telegram (���� 40 ����) $notgood");
		if (strlen($www)>40) exit("<br><br><br><p align=center><font size=2 face=tahoma><b>������� ����� ������ ���� (���� 40 ����) $notgood");
		if (strlen($about)>50) exit("<br><br><br><p align=center><font size=2 face=tahoma><b>������� ����� ������ ������ (���� 50 ����) $notgood");
		if (strlen($work)>100) exit("<br><br><br><p align=center><font size=2 face=tahoma><b>������� ����� ������ �������� (���� 100 ����) $notgood");
		if (strlen($write)>150) exit("<br><br><br><p align=center><font size=2 face=tahoma><b>������� ����� ������ ������� (���� 150 ����) $notgood");

		if ($antimatt==1)
		{
			$dayx=remBadWordsB($dayx);
			$icq=remBadWordsB($icq);
			$telegram=remBadWordsB($telegram);
			$www=remBadWordsB($www);
			$about=remBadWordsB($about);
			$work=remBadWordsB($work);
			$write=remBadWordsB($write);
		}

		$email=str_replace("|",'', $email);
		$dayx=str_replace("|",'', $dayx);
		$icq=str_replace("|",'', $icq);
		$telegram=str_replace("|",'', $telegram);
		$www=str_replace("|",'', $www);
		$about=str_replace("|",'', $about);
		$work=str_replace("|",'', $work);
		$write=str_replace("|",'', $write);
		$avatar=str_replace("|",'', $avatar);
		$cflag=str_replace("|",'', $cflag);

		// �������� ������/������� ������
		$ok=null;
		$lines=file("datan/usersdat.php");
		$i=count($lines);
		unset($ok);
		do {
			$i--;
			$rdt=explode("|", $lines[$i]);

			if (strtolower($name)===strtolower($rdt[0]) & $oldpass===$rdt[1]) $ok="$i"; // ���� �����

			else {
				if ($email===$rdt[3]) $bademail="1"; // ����� � ������ ��� ���� ����� �����?
			}
		} while($i > "1");

		if (isset($bademail)) exit("
				<div align=center><br><br><br><br><br><fieldset style='width:350px; border: #333 1px solid;'><legend align='center'><font size=2 face=tahoma color=red><b>������</b></font></legend>
				<br><center><font size=2 face=tahoma><b>���� ����������! ������������ � �������<br><font color=red>$email</font><br>��� ��������������� �� ������!</b>
				</font></center><br></fieldset></div><br><br><p align=center><a href='javascript:history.back(1)' style='text-decoration:none;'>&#9668; �����</a></p>");

		if (!isset($ok))
		{
			@setcookie("cname","",time(),"/");
			@setcookie("cmail","",time(),"/");
			@setcookie("cpassreg","",time(),"/");

			exit("
				<div align=center><br><br><br><br><br><fieldset style='width:400px;border: #333 1px solid;'><legend align='center'><font size=2 face=tahoma color=red><b>������</b></font></legend>
				<br><center><font size=2 face=tahoma><b>����� �����-������-����� �� ��������� �� � ����� �� ��<br>����� ������������ ������ ���������!</b>
				</font></center><br></fieldset></div><br><br><p align=center><a href='javascript:history.back(1)' style='text-decoration:none;'>&#9668; �����</a></p>");
		}

		$udt=explode("|",$lines[$ok]);
		$dayreg=$udt[4];
		$kolvomsg=$udt[2];
		$status=$udt[13];

		// ���� �������� �������
		if ($_FILES['file']['name']!="")
		{
			$fotoname=$_FILES['file']['name']; // ���������� ��� �����
			$avatar=$fotoname;
			$fotosize=$_FILES['file']['size']; // ���������� ������ �����

			// ��������� ����������
			$ext=strtolower(substr($fotoname, 1 + strrpos($fotoname, ".")));

			if (!in_array($ext, $valid_types)) exit("<div align=center><br><br><br><br><br><fieldset style='width:400px;border: #333 1px solid;'><legend align='center'><font size=2 face=tahoma color=red><b>���� �� ��������!</b></font></legend><br><center><font size=2 face=tahoma><b>��������� ������� - �� ��������� ��������� �� ����������� ����, ������� ����� ����� ��� ������ ����.</b></font></center><br></fieldset></div><br><br><p align=center><a href='javascript:history.back(1)' style='text-decoration:none;'>&#9668; �����</a></p>");

		}

		$text="$name|$pass|$kolvomsg|$email|$dayreg|$dayx|$pol|$icq|$www|$about|$work|$write|$avatar|$status|$cflag||$telegram|";
		$text=replacer($text);
		$exd=explode("|",$text);
		$name=$exd[0];
		$pass=$exd[1];
		$email=$exd[3];

		// ������ ���� �����
		$tektime=time();

		@setcookie("cname", $name, (time()+300000000),"/");
		@setcookie("cmail", $email, (time()+300000000),"/");
		@setcookie("cpassreg", md5($pass), (time()+300000000),"/");

		if ($_FILES['file']['name']!="")
		{
			// 1. ������� ���-�� �����
			$findtchka=substr_count($fotoname, ".");
			if ($findtchka>1) exit("
					<div align=center><br><br><br><br><br><fieldset style='width:400px;border: #333 1px solid;'><legend align='center'><font size=2 face=tahoma color=red><b>������</b></font></legend>
					<br><center><font size=2 face=tahoma><b>� ����� ����� ���� ����� $findtchka ���(�).<br>��� ���������!</b>
					</font></center><br></fieldset></div><br><br><p align=center><a href='javascript:history.back(1)' style='text-decoration:none;'>&#9668; �����</a></p>");

			// 2. ���� � ����� ���� .php � ��.

			if (preg_match("/\.php|\.htm|\.html|\.mht|\.mhtml|\.hta|\.vb|\.vbs|\.vbe|\b\.js\b|\b\.jse\b|\b\.jar\b/i",$fotoname))
				exit("<br><br><br><p align=center><font size=2 face=tahoma><b>��� ���� ����� ����������� ����������!</b><br><br><a href='javascript:history.back(1)'>&#9668; �����</a></p>");

			// 3. �������� �� ������� ���� � ����� ����� � ��������� ���������� ����� 
			if (!preg_match("/^[a-z0-9\.\-_]+\.(jpg|gif|png|jpeg)+$/is",$fotoname)) 
				exit("
					<div align=center><br><br><br><br><br><fieldset style='width:400px;border: #333 1px solid;'><legend align='center'><font size=2 face=tahoma color=red><b>������</b></font></legend>
					<br><center><font size=2 face=tahoma><b>��������� ������������ ������� ����� � ����� �����!</b>
					</font></center><br></fieldset></div><br><br><p align=center><a href='javascript:history.back(1)' style='text-decoration:none;'>&#9668; �����</a></p>");

			// 4. ���������, ����� ���� ���� � ����� ������ ��� ����
			if (file_exists("./avatars/$fotoname"))
				exit("
					<div align=center><br><br><br><br><br><fieldset style='width:400px;border: #333 1px solid;'><legend align='center'><font size=2 face=tahoma color=red><b>������</b></font></legend>
					<br><center><font size=2 face=tahoma><b>���� � ����� ������ ��� ���������� �� �������!<br>�������� ��� �� ������!</b>
					</font></center><br></fieldset></div><br><br><p align=center><a href='javascript:history.back(1)' style='text-decoration:none;'>&#9668; �����</a></p>");

			// 5. ������ � �� < �����������
			$fotoksize=round($fotosize/10.24)/100; //������ ������������ ���� ��
			$fotomax=round($max_file_size/10.24)/100; //���� ������ ���� ��

			if ($fotoksize>$fotomax)
				exit("
					<div align=center><br><br><br><br><br><fieldset style='width:400px;border: #333 1px solid;'><legend align='center'><font size=2 face=tahoma color=red><b>������</b></font></legend>
					<br><center><font size=2 face=tahoma>�� ��������� ���������� ������!<br>������������ ������:<b>$fotomax</b> ��<br>���� ��������: <b>$fotoksize</b> ��
					</font></center><br></fieldset></div><br><br><p align=center><a href='javascript:history.back(1)' style='text-decoration:none;'>&#9668; �����</a></p>");

			// 6. �������� �������
			$size=getimagesize($_FILES['file']['tmp_name']);

			if ($size[0]>$avatar_width or $size[1]>$avatar_height)
				exit("
					<div align=center><br><br><br><br><br><fieldset style='width:400px;border: #333 1px solid;'><legend align='center'><font size=2 face=tahoma color=red><b>������</b></font></legend>
					<br><center><font size=2 face=tahoma><b>������� ������� �� ������ ���������<br>$avatar_width � $avatar_height px</b>
					</font></center><br></fieldset></div><br><br><p align=center><a href='javascript:history.back(1)' style='text-decoration:none;'>&#9668; �����</a></p>");

			if ($fotosize>"0" and $fotosize<$max_file_size)
			{
				copy($_FILES['file']['tmp_name'], avatars."/".$fotoname);

				print "<br><br><br><center><font size=2 face=tahoma><b>���� ������� ���������: $fotoname ($fotosize ����)</b><br><p align=center><a href='javascript:history.back(1)' style='text-decoration:none;'>&#9668; �����</a></p>";
			} else {
				exit("
					<div align=center><br><br><br><br><br><fieldset style='width:400px;border: #333 1px solid;'><legend align='center'><font size=2 face=tahoma color=red><b>���� �� ��������</b></font></legend>
					<br><center><font size=2 face=tahoma>���� �� ������ ���������:<br><b>[function.getimagesize]: Filename cannot be empty</b><br>
					������ ���������� GD �����������, ���� ������ ������. ��������, ������ �� ����� ��� �������� ��������� ��������, ���� ������ �������� �������� ������ ����� http.</b>
					</font></center><br></fieldset></div><br><br><p align=center><a href='javascript:history.back(1)' style='text-decoration:none;'>&#9668; �����</a></p>");
			}
		}

		$file=file("datan/usersdat.php");
		$fp=fopen("datan/usersdat.php","a+");
		flock($fp,LOCK_EX);
		ftruncate($fp,0);
		for ($i=0;$i<sizeof($file);$i++) {
			if ($ok!=$i) fputs($fp,$file[$i]); else fputs($fp,"$text\r\n");
		}
		fflush($fp);
		flock($fp,LOCK_UN);
		fclose($fp);


		if ($_COOKIE['cadmin']==$adminname & $_COOKIE['cpass']==$adminpass)
		{
			 exit("<meta charset='windows-1251'><script>function reload(){location=\"index.php?event=clearuser\"};setTimeout('reload()',5000);</script>
<table width=100% height=80%><tr><td><table border=1 cellpadding=5 cellspacing=0 bordercolor=#224488 align=center valign=center width=450 height=90>
<tr><td><center><font size=2 face=tahoma><B>��������������� ������ <font color=red>$name</font> ������� ��������!<BR><BR>
<a href='index.php?event=clearuser' style='text-decoration:none;'>����������</a></B></font><BR></td></tr></table></td></tr></table></center>");

		} else {
			 exit("<meta charset='windows-1251'><script>function reload(){location=\"javascript:history.back(1)\"};setTimeout('reload()',1000);</script>
<table width=100% height=80%><tr><td><table border=1 cellpadding=5 cellspacing=0 bordercolor=#224488 align=center valign=center width=450 height=90>
<tr><td><center><font size=2 face=tahoma><B>��������������� ������ <font color=red>$name</font> ������� ��������!<BR><BR>
<a href='javascript:history.back(1)' style='text-decoration:none;'>����������</a></B></font><BR></td></tr></table></td></tr></table></center>");

		}
	}
}


////////////////// ������ 1

if (isset($_GET['forumid'])) $forumid = trim($_GET['forumid']);
if (isset($_GET['action'])) $action = trim($_GET['action']);
if (isset($_GET['page'])) $page = trim($_GET['page']);
if (isset($_POST['name'])) $name = trim($_POST['name']);
if (isset($_POST['mail'])) $mail = trim($_POST['mail']);
if (isset($_POST['topic'])) $topic = trim($_POST['topic']);
if (isset($_POST['msg'])) $msg = $_POST['msg'];
if (isset($_POST['email'])) $email = trim($_POST['email']);
if (isset($_COOKIE['cname'])) $cname = trim($_COOKIE['cname']);
if (isset($_COOKIE['cmail'])) $cmail = trim($_COOKIE['cmail']);
if (isset($_COOKIE['cpassreg'])) $cpassreg = trim($_COOKIE['cpassreg']);


////////////////// ���� �������/������� � ����. ���� �� ������ ������ ���� ������
if (is_file("data/".$forumid.".user"))
{
	$tub = explode('|', file_get_contents("data/$forumid.user"));

	if (!empty($tub[0]) & $tub[2]=="1")
	{
		if (preg_match("/\b".$user."\b/i", $tub[0])) exit("<div align=center><br><br><br><br><br><fieldset style='width:400px;border: #333 1px solid;'><legend align=center><font size=2 face=tahoma color=red><b>��������!</b></font></legend><br><font size=2 face=tahoma>����� ���� ��������� ������ �� ������!</font><br></fieldset><br><br><font size=2 face=tahoma><a href=\"index.php\" style='text-decoration:none;'>[��������� �� �����]</a></font></div>");
	}
	if (!empty($tub[1]) & $tub[3]=="1")
	{
		if (!preg_match("/\b".$user."\b/i", $tub[1])) exit("<div align=center><br><br><br><br><br><fieldset style='width:400px;border: #333 1px solid;'><legend align=center><font size=2 face=tahoma color=red><b>��������!</b></font></legend><font size=2 face=tahoma>������ ���� ����� ������ ������������:<br><b>$tub[1]</b></font><br></fieldset><br><br><font size=2 face=tahoma><a href=\"index.php\" style='text-decoration:none;'>[��������� �� �����]</a></font></div>");
	}
}


////////////////// ������ 2

$tt=1;

if (isset($_POST['tt'])) $tt = $_POST['tt'];

$zvezdmax=0;
$repamax=0;

$stopuser='';
$onlyuser='';

$sur='';
$our='';

if (isset($_POST['zvezdmax'])) $zvezdmax = $_POST['zvezdmax'];
if (isset($_POST['repamax'])) $repamax = $_POST['repamax'];

if (isset($_POST['stopuser'])) $stopuser = $_POST['stopuser'];
if (isset($_POST['onlyuser'])) $onlyuser = $_POST['onlyuser'];

if (isset($_POST['sur'])) $sur = $_POST['sur'];
if (isset($_POST['our'])) $our = $_POST['our'];


function codemsg($text) {
	$text=str_replace(array("[video]", "[/video]", "[audio]", "[/audio]", "[vimeo]", "[/vimeo]", "[dzen]", "[/dzen]", "[rutube]", "[/rutube]", "[youtube]", "[/youtube]", "[ok]", "[/ok]", "[telegram]", "[/telegram]", "[quote]", "[/quote]", "[code]", "[/code]", "[hide]", "[/hide]"), array("", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", ""), $text);
	$text=str_replace(array("[b]", "[/b]", "[i]", "[/i]", "[u]", "[/u]", "[s]", "[/s]", "[big]", "[/big]", "[small]", "[/small]", "[red]", "[/red]", "[blue]", "[/blue]", "[green]", "[/green]", "[orange]", "[/orange]", "[yellow]", "[/yellow]", "[left]", "[/left]", "[center]", "[/center]", "[right]", "[/right]", "[img]", "[/img]"), array("", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", ""), $text);
 	$text=str_replace("\r\n", ' ', $text);
	$text=str_replace("\n", ' ', $text);
	$text=str_replace("\t", ' ', $text);
	$text=str_replace("\r", ' ', $text);

	$text = str_replace(array("+", "&", "�", "#", "\"", " ", "�", "�", "�", "�", "�", "�", "�", "�", "�", "�", "�", "�", "�", "�", "�", "�", "�", "�", "�", "�", "�", "�", "�", "�", "�", "�", "�", "�", "�", "�", "�", "�","�", "�", "�", "�", "�", "�", "�", "�", "�", "�", "�", "�", "�", "�", "�", "�", "�", "�", "�", "�", "�", "�", "�", "�", "�", "�", "�", "�", "�","�", "�", "�", "�", "�"), array("%2B", "%26", "%23", "%23", "%22", "%20", "%D0%90", "%D0%B0", "%D0%91", "%D0%B1", "%D0%92", "%D0%B2", "%D0%93", "%D0%B3", "%D0%94", "%D0%B4", "%D0%95", "%D0%B5", "%D0%81", "%D1%91", "%D0%96", "%D0%B6", "%D0%97", "%D0%B7", "%D0%98", "%D0%B8", "%D0%99", "%D0%B9", "%D0%9A", "%D0%BA", "%D0%9B", "%D0%BB", "%D0%9C", "%D0%BC", "%D0%9D", "%D0%BD", "%D0%9E", "%D0%BE", "%D0%9F", "%D0%BF", "%D0%A0", "%D1%80", "%D0%A1", "%D1%81", "%D0%A2", "%D1%82", "%D0%A3", "%D1%83", "%D0%A4", "%D1%84", "%D0%A5", "%D1%85", "%D0%A6", "%D1%86", "%D0%A7", "%D1%87", "%D0%A8", "%D1%88", "%D0%A9", "%D1%89", "%D0%AA", "%D1%8A", "%D0%AB", "%D1%8B", "%D0%AC", "%D1%8C", "%D0%AD", "%D1%8D", "%D0%AE", "%D1%8E", "%D0%AF", "%D1%8F"), $text);
	return $text;
}



/////////// ����� � ����
if (isset($name) && isset($msg) && isset($email) && isset($_POST['forumid']) && isset($_POST['action']) && $_POST['action']=="answer")
{
	if (strlen($msg)>2 && strlen($msg)<$maxmsg)
	{
		$forumid=trim($_POST['forumid']);
		$linesm=file("data/$forumid");
		$nm=count($linesm);
		$gpg=ceil($nm/10);

		if ($telegramsend==1)
		{
			$telegram_msg = "Topic: http://$hst$self?forumid=$forumid%26page=$gpg%23m$nm Message: " .codemsg($msg);
			echo '<object type="text/html" data="https://api.telegram.org/bot' .$telegramtoken. '/sendMessage?chat_id=' .$telegramid. '&text=' .$telegram_msg. '" width="1px" height="1px"></object>';
		}
		echo "<meta http-equiv=refresh content='0; url=index.php?forumid=$forumid&page=$gpg#m$nm'>";
	} else {
		exit("
			<div align=center><br><br><br><br><br><fieldset style='width:420px; border: #333 1px solid;'><legend align='center'><font size=2 face=tahoma color=red><b>������</b></font></legend>
			<br><center><font size=2 face=tahoma><b>��������� �� ������ ���� ������ ��� ����� 2-� ��������, � ����� �� ������ ��������� $maxmsg ��������!</b></font></center>
			<br></fieldset></div><br><br><p align=center><a href='javascript:history.back(1)' style='text-decoration:none;'>&#9668; �����</a></p>");
	}
}

if ($welcome==1) $on=" onload='welcome()'"; else $on="";


////////////////// ����� ������
include "$fskin/top.html";


////////////////// ������� - ��������
if (isset($_COOKIE['cadmin']) && isset($_COOKIE['cpass']))
{
	if ($_COOKIE['cadmin']==$adminname && $_COOKIE['cpass']==$adminpass)
	{
		if (isset($_GET['forumid']))
		{
			if (isset($_GET['mode']))
			{
				/////////// ������� ����
				if ($_GET['mode']=="closetopic" or $_GET['mode']=="opentopic" )
				{
					$fid="datan/topic.dat";
					$tlines=file("$fid");
					$ut=count($tlines);
					$tlinenew="";

					for ($i=0; $i<=$ut; $i++)
					{
						$pu=explode('�',$tlines[$i]);

						if ($_GET['mode']=="closetopic") 
						{
							if ($pu[2]==$forumid) {
								$tlines[$i]="$pu[0]�$pu[1]�$pu[2]�$pu[3]�$pu[4]�$pu[5]�$pu[6]�$pu[7]�$pu[8]�$pu[9]�$pu[10]�0�$pu[12]�$pu[13]�$pu[14]�$pu[15]�$pu[16]�$pu[17]�$pu[18]�$pu[19]�$pu[20]�$pu[21]�$pu[22]�$pu[23]�$pu[24]�";
							}
							$tlinenew.="$tlines[$i]";
						}
						if ($_GET['mode']=="opentopic")
						{
							if ($pu[2]==$forumid) {
								$tlines[$i]="$pu[0]�$pu[1]�$pu[2]�$pu[3]�$pu[4]�$pu[5]�$pu[6]�$pu[7]�$pu[8]�$pu[9]�$pu[10]�1�$pu[12]�$pu[13]�$pu[14]�$pu[15]�$pu[16]�$pu[17]�$pu[18]�$pu[19]�$pu[20]�$pu[21]�$pu[22]�$pu[23]�$pu[24]�";
							}
							$tlinenew.="$tlines[$i]";
						}
					}
					$fp=fopen("$fid","w");
					flock($fp,LOCK_EX);
					fputs($fp,"$tlinenew");
					fflush($fp);
					flock($fp,LOCK_UN);
					fclose($fp);

					echo "<meta http-equiv=refresh content='0; url=index.php'>";
				}

				/////////// VIP ����
				if ($_GET['mode']=="viptopic" or $_GET['mode']=="unviptopic")
				{
					$fid="datan/topic.dat";
					$tlines=file("$fid");
					$ut=count($tlines);
					$tlinenew="";

					for ($i=0; $i<=$ut; $i++)
					{
						$pu=explode('�',$tlines[$i]);

						if ($_GET['mode']=="viptopic")
						{
							if ($pu[2]==$forumid) {
								$tlines[$i]="$pu[0]�$pu[1]�$pu[2]�$pu[3]�$pu[4]�$pu[5]�$pu[6]�$pu[7]�$pu[8]�$pu[9]�$pu[10]�vip�$pu[12]�$pu[13]�$pu[14]�$pu[15]�$pu[16]�$pu[17]�$pu[18]�$pu[19]�$pu[20]�$pu[21]�$pu[22]�$pu[23]�$pu[24]�";
							}
							$tlinenew.="$tlines[$i]";
						}
						if ($_GET['mode']=="unviptopic")
						{
							if ($pu[2]==$forumid) {
								$tlines[$i]="$pu[0]�$pu[1]�$pu[2]�$pu[3]�$pu[4]�$pu[5]�$pu[6]�$pu[7]�$pu[8]�$pu[9]�$pu[10]�1�$pu[12]�$pu[13]�$pu[14]�$pu[15]�$pu[16]�$pu[17]�$pu[18]�$pu[19]�$pu[20]�$pu[21]�$pu[22]�$pu[23]�$pu[24]�";
							}
							$tlinenew.="$tlines[$i]";
						}
					}
					$fp=fopen("$fid","w");
					flock($fp,LOCK_EX);
					fputs($fp,"$tlinenew");
					fflush($fp);
					flock($fp,LOCK_UN);
					fclose($fp);

					echo "<meta http-equiv=refresh content='0; url=index.php'>";
				}

				if ($_GET['mode']=="unlink")
				{
					$fid=file("datan/topic.dat");
					$fp=fopen("datan/topic.dat","w");
					flock($fp,LOCK_EX);
					for ($i=0; $i<sizeof($fid); $i++)
					{
						$pu=explode('�',$fid[$i]);
						if ($pu[2]==$forumid) unset($fid[$i]);
					}
					fputs($fp, implode("",$fid));
					fflush($fp);
					flock($fp,LOCK_UN);
					fclose($fp);

					unlink("data/$forumid");
					unlink("data/$forumid.dat");
					unlink("data/$forumid.user");

					echo "<meta http-equiv=refresh content='0; url=index.php'>";
				}

				if ($_GET['mode']=="unset")
				{
					$fid=file("data/$forumid");
					$fp=fopen("data/$forumid","w");
					flock($fp,LOCK_EX);
					for ($i=0; $i<sizeof($fid); $i++) {
						$id=$_GET['msg'];
						if ($id==$i) unset($fid[$i]);
					}
					fputs($fp, implode("",$fid));
					fflush($fp);
					flock($fp,LOCK_UN);
					fclose($fp);

					$gpg=ceil($id/10);
					if ($gpg=="0") {$gpg="1";}

					echo "<meta http-equiv=refresh content='0; url=index.php?forumid=$forumid&page=$gpg'>";
				}
			}
		}

		if (isset($_POST['forumid']) && isset($_POST['msg']) && isset($_POST['text']))
		{
			$forumid=$_POST['forumid'];
			$msg=$_POST['msg'];
			$text=trim($_POST['text']);
			$text=str_replace("�",'', $text);
			$text=str_replace("\n",'<br>', $text);
			$text=str_replace("\r",'', $text);

			$edit=file("data/$forumid");
			$fp=fopen("data/$forumid","w");
			flock($fp,LOCK_EX);
			$edit[$msg]=explode("�",$edit[$msg]);
			$edit[$msg][3]=$text;
			$edit[$msg]=implode("�",$edit[$msg]);
			fwrite($fp, implode("",$edit));
			fflush($fp);
			flock($fp,LOCK_UN);
			fclose($fp);

			$gpg=ceil($msg/10);
			if ($gpg=="0") {$gpg="1";}
			echo "<meta http-equiv=refresh content='0; url=index.php?forumid=$forumid&page=$gpg'>";
		}

		//////////////// ���
		if (isset($_GET['event']))
		{
			if ($_GET['event']=="ban")
			{
				if (is_file("datan/banip.dat"))
				{
					$linesb=file("datan/banip.dat");
					$ib=count($linesb);
					$itogoban=$ib;
					if ($ib>0) {
						print"	<br><center><style>table,td {border:#222 1px solid; border-collapse:collapse}</style>
							<table width='93%'>
							<tr><td>
								<table width='100%' cellpadding='5' cellspacing='0'>
								<tr class=row2>
								<td align=center><b>X</b></td>
								<td align=center><b>���� ��</b></td>
								<td align=center><b>���� ��</b></td>
								<td align=center><b>����</b></td>
								<td align=center><b>���������</b></td>
								<td align=center><b>������</b></td>
								<td align=center><b>IP</b></td>
								<td align=center><b>Nick</b></td>
								<td align=center><b>�������</b></td>
								</tr>";
						do {
							$ib--;
							$idt=explode("|", $linesb[$ib]);
							if ($idt[4]>time()) $ban_status="<font color=red>�����</font>"; else $ban_status="<font color=green>�����</font>";

							if ($idt[6]==TRUE) $banorwarn_status="���"; else $banorwarn_status="�������";

							$idt[3]=date("d.m.Y_H:i",$idt[3]);
							$idt[4]=date("d.m.Y_H:i",$idt[4]);

							print"	<tr><td width=15 align=center class=row1><a href='index.php?delip=$ib' title='�������' onclick=\"return confirm('������� ��� ������?')\"><font color=red><b>X</b></font></a></td>
								<td width=90 align=center><small>$idt[3]</small></td>
								<td width=90 align=center><small>$idt[4]</small></td>
								<td width=40 align=center>$idt[5]</td>
								<td width=50 align=center>$banorwarn_status</td>
								<td width=50 align=center>$ban_status</td>
								<td width=100 align=center>$idt[0]</td>
								<td width=150>&nbsp;<b>$idt[1]</b></td>
								<td>$idt[2]</td></tr>";
						} while($ib>"0");
					} else {
						print"<br><br><center><b>��������������� �����������</b></center><br>";
					}
				}
				$banorwarn=1;
				if ($banorwarn==TRUE) {$banorwarn1="checked"; $banorwarn2="";} else {$banorwarn2="checked"; $banorwarn1="";}


				print"</table><br><center><form action='index.php?badip' method=POST><input class=radio type=radio name=\"banorwarn\" value='1' $banorwarn1/>��� <input class=radio type=radio name=\"banorwarn\" value='0' $banorwarn2/>�������, �� <input type=text style='width:35px' maxlength=3 name=\"to_time\" value='5'> ����, <input type=text placeholder='IP *' style='width:105px' maxlength=15 name=\"ip\"> <!--input type=text placeholder='Nick' style='width:130px' name='nickban' maxlength='$maxname'--> <SELECT name=nickban style='height:28px;padding:2px 3px;border-radius:3px;'><option value=''>�������� ���</option>";

				if (is_file("datan/usersdat.php")) $lines=file("datan/usersdat.php");
				$imax=count($lines); $i="1";
				do {
					$dt=explode("|", $lines[$i]);
					print "<OPTION $selectnext value=\"$dt[0]\">$dt[0]</OPTION>";
					if ($nickban==$dt[0]) $selectnext="selected"; else $selectnext="";
					$i++;
				} while($i < $imax);

				print"</SELECT> <input type=text style='width:50%' maxlength=500 name=\"text\" placeholder='�������'> <input type=submit value='��������' class='fbutton'></form><br>����� � ������: <b>$itogoban</b><br><br>* ���� ���������� ������������ �� ����, �� �� ������ ��� IP, �� �������, �������� IP: 127.0.0.3</td></tr></table><br><br><a href='index.php'>&#9668; �����</a></center></body></html>";

			}
		}

		//////////////// ���������� IP � ���
		if (isset($_GET['badip']))
		{
			if (isset($_POST['ip']))
			{
				$ip=$_POST['ip'];
				$nickban=$_POST['nickban'];
				$badtext=$_POST['text'];
			}
			if (isset($_POST['to_time'])) $to_time=$_POST['to_time']; else $to_time="5";
			if (isset($_POST['banorwarn'])) $banorwarn=$_POST['banorwarn'];

			$from_time=time();
			$to_time_day=$to_time;
			$to_time=$from_time+86400*$to_time;

			if (isset($_GET['ip_get']))
			{
				$ip=$_GET['ip_get'];
				$nickban=$_GET['nickban'];
				$badtext="��������� ������!";
			}

			if (strlen($ip)<7) exit("<br><br><br><center><b>�� ����������� ����� IP-�����!</b><br><br><a href='javascript:history.back(1)'>&#9668; �����</a>");

			$badtext=str_replace("|", '', $badtext);
			$nickban=str_replace("|", '', $nickban);

			$text="$ip|$nickban|$badtext|$from_time|$to_time|$to_time_day|$banorwarn|";

			//$text=htmlspecialchars($text,ENT_COMPAT,"windows-1251"); //WR
			//$text=htmlspecialchars($text, ENT_QUOTES, 'cp1251'); //��
			$text=stripslashes($text);
			$text=str_replace("\r\n", '<br>', $text);
			$text=str_replace("\n", '', $text);
			$text=str_replace("\r", '', $text);

			$fp=fopen("datan/banip.dat","a+");
			flock($fp,LOCK_EX);
			fputs($fp,"$text\r\n");
			fflush($fp);
			flock($fp,LOCK_UN);
			fclose($fp);
			echo "<meta http-equiv=refresh content='0; url=index.php?event=ban'>";
		}

		//////////////// �������� IP �� ����
		if (isset($_GET['delip'])) {
			$xd=$_GET['delip'];
			$file=file("datan/banip.dat");
			$dt=explode("|", $file[$xd]);
			$fp=fopen("datan/banip.dat","w");
			flock($fp,LOCK_EX);
			for ($i=0; $i<sizeof($file);$i++) {if ($i==$xd) unset($file[$i]);}
			fputs($fp, implode("", $file));
			flock($fp,LOCK_UN);
			fclose($fp);
			echo "<meta http-equiv=refresh content='0; url=index.php?event=ban'>";
		}
	}
}







/////////////// ���� �� �����
if (isset($_GET['event']))
{
	if ($_GET['event']=="login")
	{
		print "
			<br><br><form method=post action=\"index.php?event=regenter\" name='Guest' onSubmit='regGuest(); return(false);'> 
			<table align=center style='border: #333 1px solid;' cellpadding=4 cellspacing=5>
			<tr><td><input name=\"name\" size=30 placeholder='Name' type=text maxlength=$maxname></td></tr>
			<tr><td><input name=\"passreg\" size=30 type=password placeholder='Password' maxlength=20></td></tr>";

		if ($captchamin==1)
		{
			exit("<tr><td><table cellpadding=0 cellspacing=0 border=0><tr><td>
<script>function checkedBox(f){if(f.check1.checked) document.getElementById('other').innerHTML='<br><center><input type=submit class=fbutton value=\'���������\'></center>';
else document.getElementById('other').innerHTML='<br><center><input type=submit class=fbutton value=\'���������\' disabled=\'disabled\'></center>';}</script>
<input type=checkbox name=check1 onClick=\"checkedBox(this.form)\" style='height:20px;width:20px' title='���� �� ���������� ������, �� �������� ������� ������� �����' ></td>
<td width='100%'>&nbsp; � �� ���</td></tr></table></td></tr>
<tr><td><div align=center></div><div id=other align=center><br><input type=submit class=fbutton value='���������' disabled='disabled'></div>
</td></tr></table></form><p align=center><a href='index.php?id=forum'>&#9668; �����</a></p>");

		} else {
			exit("<tr><td><img src=\"index.php?secpic\" id='secpic_img' style='border: #000 1px solid;' align='top' title='��� ����� �������� �������� �� ���' onclick=\"document.getElementById('secpic_img').src='index.php?secpic&' + Math.random(); return false\">&nbsp; <input type='text' name='secpic' id='secpic' style='width:60px' title='������� $let_amount ������ ����. ������������ �� ��������' maxlength='10'></td></tr>
<tr><td><input type=hidden name=add value=''><br><center><input type=submit class=fbutton value='���������'></center>
</td></tr></table></form><p align=center><a href='index.php?id=forum'>&#9668; �����</a></p>");

		}
	}
}







/////////////// �������������� ���������
if ($_GET['event']=="edit_post" && $_GET['m'] && $_GET['forumid'])
{
	$page=$_GET['page'];
	$fi=$_GET['forumid'];
	$mg=$_GET['m'];
	$mf=file("data/".$fi);
	$pm=explode('�',$mf[$mg]);

	// ��� �������� ��������
	//if ($_['user'] && $pm[0]==$_['user'] && $pm[9] > (time()-3600*$timeoutedit) || $_['adm'] || $_['moder'] && !strstr($status,"�������������"))

	// ��� �������� ��������
	if ($_['user'] && $pm[0]==$_['user'] && $editmsg && $pm[10] > (time()-3600*$timeoutedit))
	{
		// ���������� ���������� ���������
		if (trim($_POST['msg']))
		{
			//if ($_['adm'] || $_['moder'] && !strstr($status,"�������������"))
			//$mssg=n2br($_POST['msg']); else $mssg=replacer($_POST['msg']); // ������ ����� � ������, �� ������ ���� ��������

			$mssg=replacer($_POST['msg']);
			$mssg=str_replace("�",'', $mssg);

			$date_e=gmdate('d.m.Y', time() + 3600*($timezone+(date('I')==1?0:1)));
			$time_e=gmdate('H:i', time() + 3600*($timezone+(date('I')==1?0:1)));
			$editdate="$date_e � $time_e";
			$rsg='';
			$rsg=str_replace('%date%',$editdate,$redsig);

			$mf[$mg]=$pm[0].'�'.$pm[1].'�'.$pm[2].'�'.$mssg.$rsg.'�'.$pm[4].'�'.$pm[5].'�'.$pm[6].'�'.$pm[7].'�'.$pm[8].'�'.$pm[9].'�'.$pm[10].'�'.$pm[11].'�'.$pm[12];

			$f=fopen("data/".$fi, "w+");
			flock($f, LOCK_EX);
			fwrite($f, implode("", $mf));
			fflush($f);			
			flock($f, LOCK_UN);
			fclose($f);

			echo "<meta http-equiv=refresh content='0; url=index.php?forumid=".$fi."&page=".$page."#m".$mg."'>";
		} else {
			print "<form action=\"index.php?event=edit_post&forumid=$fi&m=".$_GET['m']."&page=".$_GET['page']."\" method=post name=REPLIER>
<br><table class=f align=center cellspacing=0 cellpadding=2 border=0><tr><td>
<input type=button class=button value='B' title='������ �����' style='font-weight:bold;' onclick=\"insbb('[b]','[/b]')\">
<input type=button class=button value='i' title='��������� �����' style='font-style:italic;' onclick=\"insbb('[i]','[/i]')\">
<input type=button class=button value='U' title='������������ �����' style='text-decoration:underline;' onclick=\"insbb('[u]','[/u]')\">
<input type=button class=button value='S' title='����������� �����' style='text-decoration:line-through;' onclick=\"insbb('[s]','[/s]')\">
<div class=rgb><mark><input type=button class=button value='R' title='������� ���� ������' style='color:red;' onclick=\"insbb('[red]','[/red]')\"></mark>
<input type=button class=button value='B' title='����� ���� ������' style='font-weight:bold;color:blue' onclick=\"insbb('[blue]','[/blue]')\"> 
<input type=button class=button value='G' title='������� ���� ������' style='font-weight:bold;color:green' onclick=\"insbb('[green]','[/green]')\">
<input type=button class=button value='O' title='��������� ���� ������' style='font-weight:bold;color:orange' onclick=\"insbb('[orange]','[/orange]')\">
<input type=button class=button value='Y' title='������ ���� ������' style='font-weight:bold;color:yellow' onclick=\"insbb('[yellow]','[/yellow]')\"></div>
<input type=button class=button value='BIG' title='������� �����' onclick=\"insbb('[big]','[/big]')\">
<input type=button class=button value='sm' title='��������� �����' onclick=\"insbb('[small]','[/small]')\">
<div class=align><mark><input type=button class=button value='=--' title='��������� ���� �����' onclick=\"insbb('[left]','[/left]')\"></mark>
<input type=button class=button value='-=-' title='������������ �����' onclick=\"insbb('[center]','[/center]')\">
<input type=button class=button value='--=' title='��������� ����� ������' onclick=\"insbb('[right]','[/right]')\"></div>
<input type=button class=button value='img' title='�������� ��������\n[img]http://site.ru/foto.jpg[/img]' style='width:35px' onclick=\"insbb('[img]','[/img]')\">
<input type=button class=button value='Code' title='���' style='width:35px' onclick=\"insbb('[code]','[/code]')\">
<input type=button class=button value='� �' title='������\n�������� �����, ������� ������ ������������� � ������� ��� ������' style='width:35px' onclick='REPLIER.msg.value += \" [quote]\"+(window.getSelection?window.getSelection():document.selection.createRange().text)+\"[/quote] \"'>
<input type=button class=button value='PM' title='������ ���������\n[hide]������ ����� �� ������ ������[/hide]\n[hide=DDD]����� ������ ���� DDD � �����[/hide]' style='width:35px' onclick=\"insbb('[hide]','[/hide]')\">
<input type=button class=button value='Spoiler' title='������� �����\n[spoiler]�����[/spoiler]\n[spoiler=��������]�����[/spoiler]' style='width:50px' onclick=\"insbb('[spoiler]','[/spoiler]')\">
<div class=media><mark><input type=button class=button value='Media' title='�������� flv, mp4, wmv, avi, mpg\n������:\n[video]http://site.ru/video.flv[/video]\n[video=640,480]http://site.ru/video.flv[/video]' style='width:50px' onclick=\"insbb('[video]','[/video]')\"></mark>
<input type=button class=button value='Music' title='�������� mid, midi, wav, wma, mp3, ogg\n������:\n[audio]http://site.ru/audio.mp3[/audio]' style='width:50px' onclick=\"insbb('[audio]','[/audio]')\">
<input type=button class=button value='Youtube' title='�������� ����� � YouTube\n������:\n[youtube]https://youtu.be/cEnHQYFP2tw[/youtube]\n[youtube]https://www.youtube.com/watch?v=cEnHQYFP2tw[/youtube]' style='width:50px' onclick=\"insbb('[youtube]','[/youtube]')\">
<input type=button class=button value='Rutube' title='�������� ����� � Rutube\n������:\n[rutube]https://rutube.ru/video/ec0873a8b642ee89414dcc5583f23077[/rutube]' style='width:50px' onclick=\"insbb('[rutube]','[/rutube]')\">
<input type=button class=button value='Vimeo' title='�������� ����� � Vimeo\n������:\n[vimeo]https://vimeo.com/805495470[/vimeo]' style='width:50px' onclick=\"insbb('[vimeo]','[/vimeo]')\">
<input type=button class=button value='Dzen' title='�������� ����� � Dzen\n������:\n[dzen]https://dzen.ru/embed/vkqzwsXzF1hw[/dzen]' style='width:50px' onclick=\"insbb('[dzen]','[/dzen]')\">
<input type=button class=button value='ok.ru' title='�������� ����� � ��������������\n������:\n[ok]https://ok.ru/video/7364277307929[/ok]' style='width:50px' onclick=\"insbb('[ok]','[/ok]')\"></div>
<input type=button class=button value='telegram' title='�������� ��������� �� ��������\n���������� � ������ ������ �� ��������� � �������� �� ������ � ���\n������:\n[telegram]https://t.me/youtubequest/3[/telegram]' style='width:55px' onclick=\"insbb('[telegram]','[/telegram]')\">
[<a href='#' onclick='toggleStats(); return false;' style='cursor:pointer;'>FAQ</a>] [<a href='#' onclick=\"window.open('uploader.php', 'upload', 'width=640,height=420,left=100,top=100,toolbar=0,status=0,border=0,scrollbars=1');return false;\" style='text-decoration:none' title='������� �������� �� ����'>UPL</a>]

<textarea name=msg cols=70 style='height:170px;font-size:9pt' id='expand'>".br2n($pm[3])."</textarea>
<br><div style='font-size:1px'>&nbsp;</div>
<center><input type=button value='&#9660;&#9660;&#9660;' title='���������' style='height:15px;width:100%;font-size:10px;' onclick=\"hTextarea('expand'); return false;\"></center><div style='font-size:2px'>&nbsp;</div>
<input type=hidden name=forumid value=\"$forumid\">
<input type=hidden name=name value=\"$cname\">
<input type=hidden name=email value=\"$cmail\">
</td></tr><tr><td class=row1 align=center height='50px'><input type=submit tabindex=5 class=fbutton value='���������' style='width:110px'></td></tr></table></form>
<br><p align=center><a href=\"javascript:history.back(1)\">&#9668; �����</a></p></body></html>";

		}

	} else echo "<div align=center><br><br><br><br><br><fieldset style='border: #555 1px solid; width:400px'><legend align='center'><font size=2 face=tahoma color=red><b>��������!</b></font></legend>
			<br><center><font size=2 face=tahoma><b>�� �� ������ ������������� ��� ���������, ��� ��� ������������� ��� ����� ����� �������!</b></font></center>
			<br></fieldset></div><br><br><p align=center><a href=\"javascript:history.back(1)\">&#9668; �����</a></p>";
}

////////////////// ��� ��� �� ������ �� �� �����  
if (isset($_COOKIE['cname']))
{
	$st_userday=$_COOKIE['cname'];
} else {
	$st_userday="�����";
}
$st_time=time();
$st_lineday="$st_userday|$st_time|$ip|\r\n";
$st_guestsday=0; //����� ������
$st_infoday=' '; //������ �� ������� �������������
$list_ip=' '; //������ �� ������� ���������� ������
$st_fileday="datan/userlistday.dat";
$timeouthours="24"; //����� ���������� �� ������ (�����) 
$st_intervalday=$timeouthours*3600; //����������� (���)
$writestartday=0;

if (is_file($st_fileday))
{
	$st_allday=file($st_fileday);
	$st_allday[count($st_allday)]=$st_lineday;

	for ($i=0; $i<count($st_allday); $i++)
	{
		$st_arrday=explode("|",$st_allday[$i]);

		if (($st_time-$st_arrday[1])>$st_intervalday)
		{
			$writestartday=$i;
		} else {
			if ($st_arrday[0]<>"�����")
			{
				if ($st_userday=="�����")
				{
					$findstrday=" <a href='index.php?event=profile&pname=$st_arrday[0]'>$st_arrday[0]</a>, ";
				} else {
					$findstrday=" <a href='index.php?event=profile&pname=$st_arrday[0]'>$st_arrday[0]</a>, ";
				}

				if (!strstr($st_infoday,(" ".$findstrday))) $st_infoday.=$findstrday;
			} else {
				$findstrday=$st_arrday[2].", "; //������� ������

				if (!strstr($list_ip," ".$findstrday)) $list_ip.=$findstrday; ++$st_guestsday;
			}
		}
	}
	$fp=fopen($st_fileday, "w");
	flock($fp,LOCK_EX);
	for ($i=$writestartday; $i<count($st_allday); $i++)
	{
		fputs($fp,$st_allday[$i]);
	}
	flock($fp,LOCK_UN);
	fclose($fp);
} else {
	$fp=fopen($st_fileday, "w");
	flock($fp,LOCK_EX);
	fputs($fp,$st_lineday);
	flock($fp,LOCK_UN);
	fclose($fp);

	if ($st_userday=="�����") ++$st_guestsday; else $st_infoday="$st_userday, ";
}


////////////////// ����� ��������� ��� ��� �� ������
$date=gmdate('d.m.Y',time()+3600*($timezone+(date('I')==1?0:1)));
$time=gmdate('H:i',time()+3600*($timezone+(date('I')==1?0:1)));

if ($_['user']) 
{
	$ulines=file("datan/usersdat.php");
	$ui=count($ulines)-1;
	$ulinenew="";

	// ���� ����� �� �����
	for ($u=0; $u<=$ui; $u++)
	{
		$udt=explode("|",$ulines[$u]);
		if ($udt[0]==$_COOKIE['cname'])
		{
			$ulines[$u]="$udt[0]|$udt[1]|$udt[2]|$udt[3]|$udt[4]|$udt[5]|$udt[6]|$udt[7]|$udt[8]|$udt[9]|$udt[10]|$udt[11]|$udt[12]|$udt[13]|$udt[14]|$date $time|$udt[16]|\r\n";
		}
		$ulinenew.="$ulines[$u]";
	}
	$fp=fopen("datan/usersdat.php","w");
	flock($fp,LOCK_EX);
	fputs($fp,"$ulinenew");
	fflush($fp);
	flock($fp,LOCK_UN);
	fclose($fp);
}




/////////////// �������� ����������
if (isset($_GET['event']))
{
	if ($_GET['event']=="who")
	{
		if (!isset($_COOKIE['cname']) and !isset($_COOKIE['cpassreg']) || !$_COOKIE['cadmin']==$adminname and !$_COOKIE['cpass']==$adminpass)

			exit("<br><br><br><br><br><table align=center style='border: #333 1px solid;' width=380><tr><th style='height:25px'><p style='color:red'>������ ���������!</p></th></tr><tr><td><center><br><B>��� ��������� ������������� ��� ����������<br><br>::: <a href=\"index.php?mode=reg\">������������������</a> :::</B><br><br><br>[<a href=\"javascript:history.back(1)\">��������� �����</a>]<br><br></center></td></table>");

		$t1="row1";
		$alllines=file("datan/usersdat.php");
		$allmaxi=count($alllines)-1;
		$i=1; $j=1; $flag=0;

		if (isset($_GET['pol'])) $pol=replacer($_GET['pol']); else $pol="";
		if (isset($_GET['interes'])) $interes=replacer($_GET['interes']); else $interes="";
		if (isset($_GET['url'])) $url=replacer($_GET['url']); else $url="";
		if (isset($_GET['from'])) $from=replacer($_GET['from']); else $from="";

		if ($pol!="" or $interes!="" or $url!="" or $from!="")
		{
			do {
				$dt=explode("|", $alllines[$i]);

				// ���� ���� ���������� � ������ - ����������� ����� �������� 1
				if ($dt[6]!="" and $pol!="") {if (stristr($dt[6],$pol)) $flag=1;}
				if ($dt[10]!="" and $interes!="") {if (stristr($dt[10],$interes)) $flag=1;}
				if ($dt[8]!="" and $url!="") {if (stristr($dt[8],$url)) $flag=1;}
				if ($dt[9]!="" and $from!="") {if (stristr($dt[9],$from)) $flag=1;}

				// ���� ���� ���� ���� ����������, �������� ��������� � ������ ����������
				if ($flag==1)
				{
					$lines[$j]=$alllines[$i];
					$flag=0;
					$j++;
				}
				$i++; 
			} while($i<$allmaxi+1);

			$fadd="&pol=$pol&interes=$interes&url=$url&from=$from";

		} else {
			$fadd=""; $lines=$alllines;
		}

		if (!isset($lines)) $maxi=0; else $maxi=count($lines)-1;

		print "<p align=center>[<a href='index.php'>��������� �� �����</a>]</p><center><form action=\"index.php?event=who\" method=GET>
<input type=hidden name=event value='who'>
<table style='border: #000 1px solid;' width='90%' height=50 cellpadding=1 cellspacing=0><tr>
<td><input type=text name=pol value='$pol' size=20 placeholder='��� (�����: ��� ��� ���)'></td>
<td><input type=text name=interes value='$interes' class=post maxlength=50 size=20 placeholder='��������'></td>
<td><input type=text name=url value='$url' class=post maxlength=50 size=20 placeholder='����'></td>
<td><input type=text name=from value='$from' class=post maxlength=50 size=20 placeholder='������'></td>
</tr><tr><td colspan=4><p align='center'><input type=submit class=fbutton style='width:100%' value='������'></p></td></tr>
</table></form><br><br>
<table style='border: #000 1px solid;' width=90% cellpadding=1 cellspacing=0><tr>
<th width=25>�</th><th width=120>���</th><th width=120>������</th><th width=100>�������</th><th>��</th><th>�������</th><th>����� ���</th><th>��</th><th>��������</th><th>����</th><th>������</th></tr>";


		// ��������� ������ ������ �������������� ��������
		if (!isset($_GET['page']))
		{
			$page=1;
		} else {
			$page=$_GET['page'];
			if (!ctype_digit($page)) $page=1;
			if ($page<1) $page=1;
		}
		$maxpage=ceil(($maxi+1)/$uq);

		if ($page>$maxpage) $page=$maxpage; $fm=$uq*($page-1);

		if ($fm>$maxi) $fm=$maxi-$uq; $lm=$fm+$uq;

		if ($lm>$maxi) $lm=$maxi+1;

		if (isset($lines))
		{
			do {
				$dt=explode("|", $lines[$fm]);
				$fm++;
				$num=$fm-1;

				if ($num==0) $numm=$fm; else $numm=$fm-1;

				if (isset($dt[1])) // ���� ������� ���������� � ������� (������ ������) - �� ������ � �� �������
				{
					$codename=urlencode($dt[0]); // �������� ��� � ���������� ��� ���������� �������� ����� GET-������

					if (isset($_COOKIE['cname']) and isset($_COOKIE['cpassreg']))
					{
						$wfn="<a href=\"index.php?event=profile&pname=$codename\">$dt[0]</a>";

						$mls="<form action='pm.php?id=$codename' method=POST name=citata onclick=\"window.open('pm.php?id=$codename','citata','width=800,height=500,left=100,top=100,toolbar=0,status=0,border=0,scrollbars=1');return false;\"><input type='button' value='��' class=button></form>";


					} else {
						$wfn="$dt[0]"; $mls=" ";
					}
					if (strlen($dt[13])=="7" and ctype_digit($dt[13])) $dt[13]="<!--font color=red><small>��� �������</small></font-->";
					if (strlen($dt[13])<2) $dt[13]=$users;
					if ($dt[6]=="�������") $add="polm.gif"; else $add="polg.gif";
					if (is_file("flags/$dt[14]")) {$flagpr="$dt[14]";} else {$flagpr="noflag.gif";}

					print "<tr><td class=$t1 height='22px'><center><small>$numm</small></center></td><td class=$t1><img src='$fskin/$add' border=0> <span align=absmiddle>$wfn</span> ";

					if ($dt[7] != "")
					{
						print " <a href='http://kicq.ru' target=_blank><img src='https://status.icq.com/5/online1.gif' border=0 align=absmiddle width='13px' height='13px' title=\"KICQ: $dt[7]\"></a>";
					}

					if ($dt[16] !="")
					{
						print " <a href=\"$dt[16]\" target='_new'><img src=\"https://upload.wikimedia.org/wikipedia/commons/8/82/Telegram_logo.svg\" width='13px' height='13px' border=0 align=absmiddle title=\"$dt[16]\"></a>";
					}

					$newstatus=explode("@", $dt[13]);

					print "</td><td class=$t1 align=center><span>$newstatus[0]</span></td><td class=$t1 align=center>&nbsp;";

					for ($i=1; $i<count($newstatus); $i++) {
						print "<img src=\"$fskin/medal.gif\" style='cursor:help' border=0 title=\"$newstatus[$i]\"> ";
					}

					print "</td>
<td class=$t1 align=center width='40px'><small>$mls</small></td>
<td class=$t1 align=center width='68px'><small>$dt[4]</small></td>
<td class=$t1 align=center width='68px'><small>$dt[15]</small></td>
<td class=$t1 align=center width='68px'><small>$dt[5]</small></td>
<td class=$t1><font style='font-family:tahoma;font-size:10px;'>$dt[10]</font></td>
<td class=$t1><small><a href=\"$dt[8]\" target='_blank'>$dt[8]</a></small></td>
<td class=$t1><img src='flags/$flagpr' border=0 align=center> <small>$dt[9]</small></td></tr>";

					if ($t1=="row1") $t1="row2"; else $t1="row1";
				}

			} while($fm < $lm+1); 
		}

		echo'</table><BR><table width="90%"><TR><TD width="30%">��������:&nbsp; ';

		if ($page>=4 and $maxpage>5) print "<a href=\"index.php?event=who&page=1$fadd\">1</a> ... ";
		$f1=$page+2;
		$f2=$page-2;

		if ($page==1) {$f1=$page+4; $f2=$page;}
		if ($page==2) {$f1=$page+3; $f2=$page-1;}
		if ($page==$maxpage) {$f1=$page; $f2=$page-4;}
		if ($page==$maxpage-1) {$f1=$page+1; $f2=$page-3;}
		if ($maxpage<4) {$f1=$maxpage; $f2=1;}

		for($i=$f2; $i<=$f1; $i++)
		{
			if ($page==$i) print "<B>$i</B> &nbsp;"; else print "<a href=\"index.php?event=who&page=$i$fadd\">$i</a> &nbsp;";
		}
		if ($page<=$maxpage-3 and $maxpage>5) print "... <a href=\"index.php?event=who&page=$maxpage$fadd\">$maxpage</a>";

		print "</TD><TD align=center width='30%'><span>[<a href='index.php'>��������� �� �����</a>]</span></TD><TD align=right width='30%'><span>����������������: $allmaxi</span></TD></TR></TABLE><BR>";
	}


	/////////////// �������������� �������
	if ($_GET['event']=="profile")
	{
		if (!isset($_GET['pname'])) exit("<br><br><br><br><br><p align=center><b>������ �������!</b><br><br><br>[<a href='javascript:history.back(1)'>&#9668; �����</a></p>");

		$pname=urldecode($_GET['pname']); // ����������� ���
		$lines=file("datan/usersdat.php");
		$i=count($lines);
		$use="0";
		do {
			$i--;
			$rdt=explode("|", $lines[$i]);

			if (isset($rdt[1])) // ���� ������ ������ - �� �� �������
			{
				if ($pname===$rdt[0])
				{
					if (isset($_COOKIE['cname']) & isset($_COOKIE['cpassreg']) || isset($_COOKIE['cadmin']) & isset($_COOKIE['cpass']) )
					{
						$wrfname=replacer($_COOKIE['cname']);
						$wrfpass=replacer($_COOKIE['cpassreg']);

						$wrfnameadmin=replacer($_COOKIE['cadmin']);
						$wrfpassadmin=replacer($_COOKIE['cpass']);
						
						if ($wrfname===$rdt[0] & $wrfpass===md5($rdt[1]) || $_COOKIE['cadmin']==$adminname & $_COOKIE['cpass']==$adminpass)
						{
							print "<center><p align=center>[<a href='index.php'>������� ������</a>] &nbsp; [<a href='javascript:history.back(1)'>��������� �����</a>]</p><form action=\"index.php?event=reregist\" name=creator method=post enctype=multipart/form-data>
<table cellpadding=2 cellspacing=0 width='480px' style='border:1px #333 solid;'>
<tr><th colspan=2 height='26px' valign=middle>��������������� ����������</th></tr>
<tr><td class=row1 height='26px' width='120px' align=right><b>���� ���</b>&nbsp;</td><td class=row2>&nbsp;$rdt[0]</td></tr>
<tr><td class=row1 height='26px' align=right><b>��� ������</b>&nbsp;</td><td class=row2>&nbsp;<input type=password class=post style='width:200px;height:23px' value=\"$rdt[1]\" name=pass size=25 maxlength=12></td></tr>
<tr><td class=row1 height='26px' align=right><b>��� e-mail</b>&nbsp;</td><td class=row2>&nbsp;<input type=text class=post style='width:200px;height:23px' value=\"$rdt[3]\" name=email size=25 maxlength=50></td></tr>
<tr><td class=row1 height='26px' align=right><b>��</b>&nbsp;</td><td class=row2>&nbsp;";

							$wrfname=strtolower($wrfname);

							if (is_file("data-pm/$wrfname.dat"))
							{
								$linespm=file("data-pm/$wrfname.dat");
								$pmi=count($linespm);

								print "[<a href='pm.php?readpm&id=$wrfname'><font color=red><b>$pmi �����.</b></font></a>]";

							} else echo'��������� ���';

print"</td></tr><tr><td colspan=2></td></tr>
<tr><th colspan=2 height='26px' valign=middle>�������������� ����������</th></tr>
<tr><td class=row1 height='26px' align=right><b>�����������</b>&nbsp;</td><td class=row2>&nbsp;$rdt[4]</td></tr>
<tr><td class=row1 height='26px' align=right><b>���</b>&nbsp;</td><td class=row2>&nbsp;$rdt[6]<input type=hidden value=\"$rdt[6]\" name=pol></td></tr>
<tr><td class=row1 height='26px' align=right><b>���� ��������</b>&nbsp;</td><td class=row2>&nbsp;<input type=text name=dayx placeholder='������: 21.12.2012' value=\"$rdt[5]\" class=post style='width:120px;height:23px' size=10 maxlength=10>&nbsp;</td></tr>
<tr><td class=row1 height='26px' align=right><b>KICQ</b>&nbsp;</td><td class=row2>&nbsp;<input type=text value=\"$rdt[7]\" name=icq class=post style='width:120px;height:23px' size=10 maxlength=12></td></tr>
<tr><td class=row1 height='26px' align=right><b>��������</b>&nbsp;</td><td class=row2>&nbsp;<input type=text value=\"$rdt[16]\" name=telegram placeholder='������: https://t.me/youtubequest' class=post style='width:345px;height:23px' size=10 maxlength=50></td></tr>
<tr><td class=row1 height='26px' align=right><b>����</b>&nbsp;</td><td class=row2>&nbsp;<input type=text value=\"$rdt[8]\" class=post style='width:345px;height:23px' name=www  placeholder='������: http://mysite.ru' size=25 maxlength=50></td></tr>
<tr><td class=row1 height='26px' align=right><b>������</b>&nbsp;</td><td class=row2>&nbsp;<input type=text class=post style='width:345px;height:23px' value=\"$rdt[9]\" name=about size=25 maxlength=60></td></tr>
<tr><td class=row1 height='26px' align=right><b>��������</b>&nbsp;</td><td class=row2>&nbsp;<input type=text class=post style='width:345px;height:23px' value=\"$rdt[10]\" name=work size=35 maxlength=60></td></tr>
<tr><td class=row1 height='26px' align=right><b>�������</b>&nbsp;</td><td class=row2>&nbsp;<input type=text class=post style='width:345px;height:23px' value=\"$rdt[11]\" name=write size=35 maxlength=70></td></tr>
<tr><td class=row1 height='26px' align=right><b>����</b>&nbsp;</td><td class=row2>";

							/////////// ���� ������ �����
							$images=null;
							unset($images);

							if (!is_file("flags/$rdt[14]")) $rdt[14]="noflag.gif";
							$root=str_replace( '\\', '/', getcwd() ) . '/';
							$dirtoopen=$root.'flags';
							$images=array();
							$handle=opendir($dirtoopen);
							while (false !==($file=readdir($handle)))

							if (strstr($file,'.gif') || strstr($file,'.jpg')) $images[]=$file; closedir($handle);
							sort($images, SORT_STRING);
							$selecthtm="";
							foreach ($images as $file)
							{
								if ($file==$rdt[14])
								{
									$selecthtm .='<option value="'.$file.'" selected>'.$file."</option>\n";
									$currentflag=$rdt[14];
								} else {
									$selecthtm .='<option value="'.$file.'">'.$file."</option>\n";
								}
							}

							print "<table><tr><td><script>function showimageflag(){document.images.cflag.src='./flags/'+document.creator.cflag.options[document.creator.cflag.selectedIndex].value;}</script><select name='cflag' size=6 onChange='showimageflag()'>$selecthtm</select></td><td><img src='./flags/$currentflag' name=cflag border=0 hspace=15></td></tr></table></td></tr><tr><td class=row1 height='25px' align=right><b>������</b>&nbsp;</td><td class=row2 height='120px'>";

							/////////// ���� ������ �������
							$images=null;
							unset($images);

							if (!is_file("avatars/$rdt[12]")) $rdt[12]="noavatar.gif";
							$root=str_replace( '\\', '/', getcwd() ) . '/';
							$dirtoopen=$root.'avatars';
							$images=array();

							if ( !($images==get_dir($dirtoopen,'*.{gif,png,jpeg,jpg}',GLOB_BRACE)) )
							{
								$handle=opendir($dirtoopen);
								while (false !==($file=readdir($handle)))

								if (strstr($file,'.gif') || strstr($file,'.jpg')) $images[]=$file; closedir($handle);
							}
							sort($images, SORT_STRING);
							$selecthtml="";
							foreach ($images as $file)
							{
								if ($file==$rdt[12])
								{
									$selecthtml .='<option value="'.$file.'" selected>'.$file."</option>\n";
									$currentface=$rdt[12];
								} else {
									$selecthtml .='<option value="'.$file.'">'.$file."</option>\n";
								}
							}

							print "<table><tr><td><script>function showimage(){document.images.avatar.src='./avatars/'+document.creator.avatar.options[document.creator.avatar.selectedIndex].value;}</script><select name='avatar' size=7 onChange='showimage()'>$selecthtml</select></td><td><img src='./avatars/$currentface' name=avatar border=0 hspace=15></td></tr></table></td></tr>
<td class=row1 align=right><br><b>��������� ������</b>&nbsp;<div align=right><small><i>�� ����� <B>$avatar_width</B>�<B>$avatar_height</B>px ".$maxfsize."Kb &nbsp;<br><br></i></small></div></td>
<td class=row2>&nbsp;<input type=file name=file class=post style='width:340px;height:23px' size=35 maxlength=150></td></tr>
<tr><td colspan=2 align=center><input type=hidden name=name value=\"$rdt[0]\"><input type=hidden name=oldpass value=\"$rdt[1]\">
<input type=submit name=submit value='���������' class='fbutton'></td></tr></table></form><p align=center>[<a href='index.php'>������� ������</a>] &nbsp; [<a href='javascript:history.back(1)'>��������� �����</a>] </p>";

							$use="1";
						}

						if ($use!="1")
						{
							$ulines=file("datan/userstat.dat");
							$ui=count($ulines)-1;
							$msgitogo=0;
							for ($i=0;$i<=$ui;$i++)
							{
								$udt=explode("|",$ulines[$i]);
								$msgitogo=$msgitogo+$udt[2];
								if ($udt[0]==$rdt[0]) $msguser=$udt[2];
							}
							$msgaktiv=round(10000*$msguser/$msgitogo)/100;

							if (strlen($rdt[13])<2) $rdt[13]=$users;
							if (is_file("avatars/$rdt[12]")) $avpr="$rdt[12]"; else $avpr="noavatar.gif";
							if (is_file("flags/$rdt[14]")) $flagpr="$rdt[14]"; else $flagpr="noflag.gif";

							print "<br><br><center><table cellpadding=2 cellspacing=0 width='520px' class=forumline>
<tr><th class=thHead colspan=2>������� ���������</th></tr>
<tr><td class=row1 width='120px' height='26px' align=right><b>���</b>&nbsp;</td><td class=row2>&nbsp;<span class=nav>$rdt[0]</span></td></tr>
<tr><td class=row1 height='26px' align=right><b>�����������</b>&nbsp;</td><td class=row2>&nbsp;$rdt[4]</td></tr>
<tr><td class=row1 height='26px' align=right><b>��� �� ������</b>&nbsp;</td><td class=row2>&nbsp;$rdt[15]</td></tr>
<tr><td class=row1 height='26px' align=right><b>���</b>&nbsp;</td><td class=row2>&nbsp;$rdt[6]</td></tr>
<tr><td class=row1 height='26px' align=right><b>������</b>&nbsp;</td><td class=row2>&nbsp;";

							$newstatus=explode("@", $rdt[13]);

							print "$newstatus[0]</td></tr><tr><td class=row1 height='25px' align=right><b>�������</b>&nbsp;</td><td class=row2>&nbsp;";

							if (count($newstatus)>1) {print " ";}

							for($i=1; $i<count($newstatus); $i++) {print"<img src='$fskin/medal.gif' style='cursor:help' border=0 title='$newstatus[$i]'> ";}

							print"</td></tr>
<tr><td class=row1 height='26px' align=right><b>��</b>&nbsp;</td><td class=row2><form action='pm.php?id=$rdt[0]' method=POST name=citata onclick=\"window.open('pm.php?id=$rdt[0]','citata','width=800,height=500,left=100,top=100,toolbar=0,status=0,border=0,scrollbars=1');return false;\"><input type='button' value='��' class=button></form></td></tr>
<tr><td class=row1 height='26px' align=right><b>���������</b>&nbsp;</td><td class=row2>&nbsp;<b>$msguser</b> (<b>$msgaktiv</b>%) <progress title='% ��������� �� ������ �����' max='100' value='$msgaktiv'></progress></td></tr>
<tr><td class=row1 height='26px' align=right><b>�������</b>&nbsp;</td><td class=row2>&nbsp;$rdt[5]</td></tr>
<tr><td class=row1 height='26px' align=right><b>KICQ</b>&nbsp;</td><td class=row2>&nbsp;$rdt[7] ";

							if ($rdt[7] !="")
							{
								print " <a href=\"http://kicq.ru\" target='_new'><img src=\"https://status.icq.com/5/online1.gif\" border=0 align=top width='15px' height='15px' title=\"KICQ: $rdt[7]\"></a>";
							}

print"</td></tr><tr><td class=row1 height='26px' align=right><b>Telegram</b>&nbsp;</td><td class=row2>&nbsp;<a href=\"$rdt[16]\" target='_new'>$rdt[16]</a> ";

							if ($rdt[16] !="")
							{
								print " <a href=\"$rdt[16]\" target='_new'><img src=\"https://upload.wikimedia.org/wikipedia/commons/8/82/Telegram_logo.svg\" width='15px' height='15px' border=0 align=top title=\"$rdt[16]\"></a>";
							}

							print"</td></tr>
<tr><td class=row1 height='26px' align=right><b>����</b>&nbsp;</td><td class=row2>&nbsp;<a href='$rdt[8]' target='_blank'>$rdt[8]</a></td></tr>
<tr><td class=row1 height='26px' align=right><b>������</b>&nbsp;</td><td class=row2>&nbsp;<img src='./flags/$flagpr' border=0 align=center>&nbsp; $rdt[9]</td></tr>
<tr><td class=row1 height='26px' align=right><b>��������</b>&nbsp;</td><td class=row2>&nbsp;$rdt[10]</td></tr>
<tr><td class=row1 height='26px' align=right><b>�������</b>&nbsp;</td><td class=row2>&nbsp;$rdt[11]</td></tr>
<tr><td class=row1 height='26px' align=right><b>������</b>&nbsp;</td><td class=row2>&nbsp;<img src='./avatars/$avpr' border=0><br></td></tr></td></tr></table>
<br><p align=center>[<a href=\"javascript:history.back(1)\">��������� �����</a>]</p>";

							$use="1";
						}
					}
				}
			}
		} while($i>"1");

		if (!isset($wrfname)) exit("<div align=center><br><br><br><br><br><fieldset style='width:350px'><legend align='center'><font size=2 face=tahoma color=red><b>������ ������!</b></font></legend>
					<br><center><font size=2 face=tahoma><b>������ ������������������ ������������<br>����� �p�����p����� ������� ����������!</b></font></center>
					<br></fieldset></div><br><br><p align=center><a href='javascript:history.back(1)'>&#9668; �����</a></p>");

		// � �� ������ ����� ���
		if ($use!="1") echo'<div align=center><br><br><br><br><br><fieldset style="width:400px"><legend align="center"><font size=2 face=tahoma color=red><b>������������ �� ������!</b></font></legend>
				<br><center><font size=2 face=tahoma><b>������������ � ����� ������ �� ������!<BR>��������� ����� �� ��� ����� �������!</b></font></center>
				<br></fieldset></div><br><br><p align=center><a href="javascript:history.back(1)">&#9668; �����</a></p>';
	}
	exit;
}


////////////////// �������� ���������� ���������� ������
if (is_file("datan/usersdat.php")) // ��������� ��� ���������� ���������������������
{
	$userlines=file("datan/usersdat.php");
	$usercount=count($userlines);
	$ui=$usercount-1;
	$tdt=explode("|", $userlines[$ui]);
} else {
	$fp=fopen("datan/usersdat.php","a+");
	fputs($fp,"<?die;?>\r\n");
	fflush($fp);
	fclose($fp);
	$ui="";
	$tdt[0]="";
}




////////////////// ������� ����
if ($readonly==0)
{
	if (isset($name) && isset($topic) && isset($_GET['action']) && $_GET['action']=="newtopic")
	{
		if ($notopic==0)
		{
			$td = date("dmyGis");
			srand((double)microtime()*1000000*sin($td));
			$gen = md5(uniqid(rand()));

			$datee = gmdate('d.m.Y', time()+3600*($timezone+(date('I')==1?0:1)));
			$timee = gmdate('H:i', time()+3600*($timezone+(date('I')==1?0:1)));
			$date = "$datee � $timee";

			$name = replacer(substr($name,0,$maxname));
			$mail = replacer(substr($mail,0,$maxmail));
			$topic = replacer(substr($topic,0,$maxtopic));

			$name = str_replace("�",'', $name);
			$mail = str_replace("�",'', $mail);
			$topic = str_replace("�",'', $topic);

			$tt = replacer(str_replace("�",'', $tt));

			$stopuser = replacer(str_replace("|",'', $stopuser));
			$onlyuser = replacer(str_replace("|",'', $onlyuser));

			$sur = replacer(str_replace("|",'', $sur));
			$our = replacer(str_replace("|",'', $our));

			if (preg_match('/^\d+$/', $zvezdmax)) $zvezdmax = replacer(substr(str_replace("�",'',$zvezdmax),0,2)); else $zvezdmax = "0";
			if (preg_match('/^\d+$/', $repamax)) $repamax = replacer(substr(str_replace("�",'',$repamax),0,5)); else $repamax = "0";

			if ($antimat==1) {
				$name = remBadWordsA($name);
				$topic = remBadWordsA($topic);
				$mail = remBadWordsA($mail);
			}
			if ($antimatt==1) {
				$name = remBadWordsB($name);
				$topic = remBadWordsB($topic);
				$mail = remBadWordsB($mail);
			}

			//getCountry();

			if ($ipinfodb==1) {
				$url = "http://api.ipinfodb.com/v3/ip-city/?key=$key&ip=$ip&format=json";
				$data = json_decode(file_get_contents($url));
				$country_code = $data->countryCode;
				$country_city = ucwords(strtolower($data->cityName.', '.$data->countryName));
				$country_city = str_replace("-, -", '', $country_city);
				$country_city = str_replace(", ", '', $country_city);

				$country_img = strtoupper($country_code);
				$country_name = $country_city;
				$country = $data->countryName;
				$latitude = $data->latitude;
				$longitude = $data->longitude;

				//$image = strtolower($country_code) . ".png";
				//$country_img = "<div class='$image' title='$country_city'></div>";
			} else {
				$country = replacer($_POST["country"]);
				$country_img = replacer(strtoupper($_POST["code"]));
				$country_name = replacer(ucwords(strtolower($_POST["city"])));
				$latitude = replacer($_POST["latitude"]);
				$longitude = replacer($_POST["longitude"]);
			}

			$value=$name."�".$mail."�".$date."�".$topic."�".$country_img."�".$country_name."�".$ip."�".$country."�".$latitude."�".$longitude."�".$tt."�".$zvezdmax."�".$repamax."�\n";

			$fp=fopen("data/$gen","w");
			flock($fp,LOCK_EX);
			fwrite($fp, $value);
			fflush($fp);
			flock($fp,LOCK_UN);
			fclose($fp);

			$valuetopic=$name."�".$mail."�".$gen."�".$date."�".$topic."�".$country_img."�".$country_name."�".$ip."�".$country."�".$latitude."�".$longitude."�".$tt."�".$zvezdmax."�".$repamax."�\n";

			$fp=fopen("datan/topic.dat","a+");
			flock($fp,LOCK_EX);
			fwrite($fp, $valuetopic);
			fflush($fp);
			flock($fp,LOCK_UN);
			fclose($fp);


			////////////////// ���������� ��������� �� ���� � ����� ����
			if ($topicmail==TRUE)
			{
				$headers = 'From: $frommail' . "\r\n" . 'Reply-To: $frommail' . "\r\n" . 'X-Mailer: PHP/' . phpversion();
				$subject = 'Forum: New topic';
				$msg='Topic: ' . $topic;
				mail($adminmail, $subject, $msg, $headers);
			}

			////////////////// ������� ���� *.user
			if (strlen($stopuser)>2 or strlen($onlyuser)>2)
			{
				$topicuser=$stopuser."|".$onlyuser."|".$sur."|".$our."|\n";
				$userfile = 'data/'.$gen.'.user';
				$fp=fopen($userfile,"w");
				flock($fp,LOCK_EX);
				fwrite($fp, $topicuser);
				fflush($fp);
				flock($fp,LOCK_UN);
				fclose($fp);
			}

			////////////////// ��������� (�����), ������� ���� 
			$clickfile = 'data/'.$gen.'.dat';
			if (!file_exists($clickfile)) file_put_contents($clickfile, 0, LOCK_EX);


			////////////////// ���� ��������� +1 � ���-�� ��� (����)
			if ($_['user']) 
			{
				$ulines=file("datan/userstat.dat");
				$ui=count($ulines)-1;
				$ulinenew="";

				// ���� ����� �� ����� � userstat.dat
				for ($i=0; $i<=$ui; $i++)
				{
					$udt=explode("|",$ulines[$i]);

					if ($udt[0]==$name)
					{
						if ($_GET['action']=="newtopic") {$udt[1]++; $udt[3]=$udt[3]+$repaaddtem;}

						$ulines[$i]="$udt[0]|$udt[1]|$udt[2]|$udt[3]|$udt[4]|$udt[5]||||\r\n";
					}
					$ulinenew.="$ulines[$i]";
				}
				$fp=fopen("datan/userstat.dat","w");
				flock($fp,LOCK_EX);
				fputs($fp,"$ulinenew");
				fflush($fp);
				flock($fp,LOCK_UN);
				fclose($fp);
			}
			echo "<meta http-equiv=refresh content='0; url=index.php?forumid=$gen'>";
		}
	}
} else {
	print"<div align=center><font color=red><b>���������� ��� � ��������� ���������!</b></font></div><br>";
}










////////////////// ����� � ����
if (isset($forumid) || isset($_POST['action']) && $_POST['action']=="answer" && isset($_POST['forumid']))
{
	if ($action=="answer" || isset($_POST['action']))
	{
		if (isset($_POST['name']) && isset($_POST['email']) && isset($_POST['msg']) && isset($_POST['forumid']) && isset($_POST['action']) && $_POST['action']=="answer" && $readonly==0)
		{

			////////////////// ������� 2 (stopwords.dat)
			if ($antiham==1)
			{
				$_b=0;
				$e=explode(' ', file_get_contents("datan/stopwords.dat"));
				foreach($e as $v) {
					if (@stristr($msg, trim($v)) && !$_b) {
						if ($stopwrd==4) {
							$f=fopen("datan/badip.dat", "a");
							flock($f, LOCK_EX);
							fwrite($f, "$ip ");
							flock($f, LOCK_UN);
							fclose($f);
							@chmod("datan/badip.dat",0644);
							$_b=1;
						}
						if ($stopwrd==3) $msg=preg_replace("/".$v."/si", $cons, $msg);
						if ($stopwrd==2) $_b=1;
					}
				}
				if ($_b) exit;
			}

			$mm=gmdate('m', time()+3600*($timezone+(date('I')==1?0:1)));
			$mm=str_replace("01","���",$mm);
			$mm=str_replace("02","���",$mm);
			$mm=str_replace("03","�����",$mm);
			$mm=str_replace("04","���",$mm);
			$mm=str_replace("05","���",$mm);
			$mm=str_replace("06","����",$mm);
			$mm=str_replace("07","����",$mm);
			$mm=str_replace("08","���",$mm);
			$mm=str_replace("09","����",$mm);
			$mm=str_replace("10","���",$mm);
			$mm=str_replace("11","����",$mm);
			$mm=str_replace("12","���",$mm);

			$date=gmdate('d ', time()+3600*($timezone+(date('I')==1?0:1))).$mm.gmdate(' Y \� H:i', time()+3600*($timezone+(date('I')==1?0:1)));

			$name=substr($name,0,$maxname);
			$email=substr($email,0,$maxmail);
			$msg=substr($msg,0,$maxmsg);

			$name=trim(replacer(str_replace("�",'', $name)));
			$email=trim(replacer(str_replace("�",'', $email)));
			$msg=trim(replacer(str_replace("�",'', $msg)));

			$msg=str_replace("\n",'<br>', $msg);

			$tt=trim(replacer(str_replace("�",'', $tt)));

			$tektime=time();

			//getCountry();

			if ($ipinfodb==1) {
				$url = "http://api.ipinfodb.com/v3/ip-city/?key=$key&ip=$ip&format=json";
				$data = json_decode(file_get_contents($url));
				$country_code = $data->countryCode;
				$country_city = ucwords(strtolower($data->cityName.', '.$data->countryName));
				$country_city = str_replace("-, -", '', $country_city);
				$country_city = str_replace(", ", '', $country_city);

				$country_img = strtoupper($country_code);
				$country_name = $country_city;
				$country = $data->countryName;
				$latitude = $data->latitude;
				$longitude = $data->longitude;

				//$image = strtolower($country_code) . ".png";
				//$country_img = "<div class='$image' title='$country_city'></div>";
			} else {
				$country = replacer($_POST["country"]);
				$country_img = replacer(strtoupper($_POST["code"]));
				$country_name = replacer(ucwords(strtolower($_POST["city"])));
				$latitude = replacer($_POST["latitude"]);
				$longitude = replacer($_POST["longitude"]);
			}


			if (isset($_FILES['file']['name'])) // ���� ��������� ����
			{
				$fotoname=replacer($_FILES['file']['name']);

				if (strlen($fotoname)>1)
				{
					$fotosize=$_FILES['file']['size'];

					// ��������� ����������
					$ext=strtolower(substr($fotoname, 1 + strrpos($fotoname, ".")));

					if (!in_array($ext, $valid_types_load)) exit("<div align=center><br><br><br><br><br><fieldset style='width:350px'><legend align='center'><font size=2 face=tahoma color=red><b>���� �� ��������.  �������:</b></font></legend><br><center><font size=2 face=tahoma>1) ������������ ���������� �����<br>2) ���� � ������� �����������<br>3) ����������� ���� ��������</font></center><br></fieldset></div><br><br><p align=center><a href='javascript:history.back(1)'>�����</a></p>");

					// ������� ���-�� �����
					$findtchka=substr_count($fotoname, ".");

					if ($findtchka>1) exit("<br><br><br><center><font size=3 face=arial>� ����� ������������ ����� ����� ������ �����!<br><p align=center><a href='javascript:history.back(1)'>&#9668; �����</a></p></font></center>");

					// ���� � ����� ���� .php � �.�.
					if (preg_match("/\.php|\.htm|\.html|\.mht|\.mhtml|\.hta|\.vb|\.vbs|\.vbe|\b\.js\b|\b\.jse\b|\b\.jar\b/i", $fotoname)) exit("<br><br><br><center><font size=3 face=arial>� ������ ����� ������������ ����������!<br><p align=center><a href='javascript:history.back(1)'>�����</a></p></font></center>");

					// �������� �� ������� ���� � �������� ���������� 
					$patern="";
					foreach($valid_types_load as $v)
					$patern.="$v|";

					if (!preg_match("/^[a-z0-9\.\-_]+\.(".$patern.")+$/is",$fotoname)) exit("<br><br><br><center><font size=3 face=arial>��������� ������� ����� � ������� � ����� �����!<br><p align=center><a href='javascript:history.back(1)'>�����</a></p></font></center>");

					// ���������, ����� ���� ���� � ����� ������ ��� ���� �� �������
					if (file_exists("$filedir/$fotoname")) exit("<br><br><br><center><font size=3 face=arial>���� � ����� ������ ��� ����������. �������� ��� �� ������!<br><p align=center><a href='javascript:history.back(1)'>�����</a></p></font></center>");

					// ������ �����
					$fotoksize=round($fotosize/10.24)/100; //������ ������������ ����� � ��
					$fotomax=round($max_upfile_size/10.24)/100; //������������ ������ ����� � ��

					if ($fotoksize>$fotomax) exit("<br><br><br><center><font size=3 face=arial>�� ��������� ���������� ������ ����� <b>$fotomax</b>��<br><br>�� ���������� ���� �������� <b>$fotoksize</b>��<br><p align=center><a href='javascript:history.back(1)'>�����</a></p></font></center>");

					// ���� ������� ������� ���������� ����� ���������� ����� ��� �������� - ���������� ��������� ���
					//if ($random_name==TRUE) {do $key=mt_rand(100000,999999); while (file_exists("$filedir/$key.$ext")); $fotoname="$key.$ext";}

					@copy($_FILES['file']['tmp_name'], $filedir."/".$fotoname);

					print "<br><br><br><center><font size=3 face=arial>���� <b>$fotoname</b> ($fotosize ����) ������� ��������!</center>";

					$size = getimagesize("$filedir/$fotoname");

					/////// ���� �������� ������ �������� � ���������� 260�220 �� ������ � ��� �� ������. ���� ������ ���������
					if ($size[0]>=$smwidth || $size[1]>=$smheight)
					{
						$smallfoto="sm-$fotoname";
						$reswidth=$smheight*$size[0]/$size[1];

						if ($reswidth>$smwidth)
						{
							$reswidth=$smwidth;
							$resheight=$smwidth*$size[1]/$size[0];
						} else {
							$resheight=$smheight;
						}
						img_resize("$filedir/$fotoname", "$filedir/$smallfoto", $reswidth, $resheight, $size, $name,'92'); 
					} else {
						$smallfoto="$fotoname";
					}

					//�� �������
					if ($size[0]>$maxwidth || $size[1]>$maxheight) {

					//�� ����. ���� ������ $max_upfile_size ����. ����� �����.
					//if ($fotosize>$max_upfile_size && $size[1]>0 && $ext!="gif") {

						$reswidth=$maxheight*$size[0]/$size[1];

						if ($reswidth>$maxwidth)
						{
							$reswidth=$maxwidth;
							$resheight=$maxwidth*$size[1]/$size[0];
						} else {
							$resheight=$maxheight;
						}
						img_resize("$filedir/$fotoname", "$filedir/$fotoname", $reswidth, $resheight, $size, $name);
					}
					//else {if($ext!="gif") img_resize("$filedir/$fotoname", "$filedir/$fotoname", $size[0], $size[1], $size, $name);}

					filesize("$filedir/$smallfoto");
					$fotoksize=round(filesize("$filedir/$fotoname")/10.24)/100;
					$size=getimagesize("$filedir/$fotoname");
				}
			}


			$value=$name."�".$email."�".$date."�".$msg."�".$country_img."�".$country_name."�".$ip."�".$country."�".$latitude."�".$longitude."�".$tektime."�".$fotoname."�".$fotosize;

			$fil="data/".$forumid;
			$fp=fopen($fil,"a");
			flock($fp,LOCK_EX);
			fwrite($fp, $value."\n");
			fflush($fp);
			flock($fp,LOCK_UN);
			fclose($fp);

			$msgg=preg_replace("/\[hide\](.+?)\[\/hide\]/is", " [����� ����� �� ������] ", $msg);
			$msgg=preg_replace("/\[hide=(.+?)\](.+?)\[\/hide\]/is", " [����� ��� \\1] ", $msg);

			$msgg=substr($msg,0,100);

			$ccnt=(count(file($fil)))-1;

			$topiclines=file("datan/topic.dat");
			$counttopic=count($topiclines);
			$fp=fopen("datan/topic.dat","w");
			flock($fp,LOCK_EX);

			for($i=0; $i<$counttopic; $i++)
			{
				$tdt=explode("�",$topiclines[$i]);

				$topicdat="$tdt[0]�$tdt[1]�$tdt[2]�$tdt[3]�$tdt[4]�$tdt[5]�$tdt[6]�$tdt[7]�$tdt[8]�$tdt[9]�$tdt[10]�$tdt[11]�$tdt[12]�$tdt[13]�".$ccnt."�".$name."�".$email."�".$date."�".$msgg."�".$country_img."�".$country_name."�".$ip."�".$country."�".$latitude."�".$longitude."�";

				if ($forumid!=$tdt[2])
				{
					fwrite($fp,"$topiclines[$i]");
				} else {
					fwrite($fp,$topicdat."\n");
				}
			}
			fflush($fp);
			flock($fp,LOCK_UN);
			fclose($fp);



			////////////////// ���� ��������� +1 � ��������� (����)
			if ($_['user']) 
			{
				$ulines=file("datan/userstat.dat");
				$ui=count($ulines)-1;
				$ulinenew="";

				// ���� ����� �� ����� � ����� userstat.dat
				for ($i=0; $i<=$ui; $i++)
				{
					$udt=explode("|",$ulines[$i]);

					if ($udt[0]==$name)
					{
						$udt[2]++;
						$udt[3]=$udt[3]+$repaaddmsg;
						$ulines[$i]="$udt[0]|$udt[1]|$udt[2]|$udt[3]|$udt[4]|$udt[5]||||\r\n";
					}
					$ulinenew.="$ulines[$i]";
				}
				$fp=fopen("datan/userstat.dat","w");
				flock($fp,LOCK_EX);
				fputs($fp,"$ulinenew");
				fflush($fp);
				flock($fp,LOCK_UN);
				fclose($fp);
			}



			////////////////// ���� ��������� ����������
			if ($_['user'])
			{
				$lines=null;
				$ok=null;

				$ulines=file("datan/usersdat.php");
				$ui=count($ulines);

				$slines=file("datan/userstat.dat");
				$si=count($slines)-1;

				/////////// ��������� ������ ������������� (����� ���)
				for ($i=1;$i<$ui;$i++)
				{
					$udt=explode("|", $ulines[$i]);

					if ($i<=$si) $sdt=explode("|",$slines[$i]); else $sdt[0]="";

					if ($udt[0]==$sdt[0]) // ���� ���=��� - ������ ������ �����
					{
						$repuser=$sdt[3]; //��������� ������������ ��� ��������� �������
						$statuser=$udt[13]; //������� ������ ������������

						//// �������� ��� �������
						$stu_end = $stu9 + 10;
						$stn_end = "Pro";
						if ($repuser>$stu9) $stu_end =  $repuser + 10;

						if (!strstr($udt[13],"�������������") and !strstr($udt[13],"���������"))
						{
							$statindex=array($stu0,$stu1,$stu2,$stu3,$stu4,$stu5,$stu6,$stu7,$stu8,$stu9,$stu_end);
							$statname=array("$stn0","$stn1","$stn2","$stn3","$stn4","$stn5","$stn6","$stn7","$stn8","$stn9","$stn_end");
							$status=explode("@",$statuser);
							$stati=0;
							do {
								$status[0]=$statname[$stati];
								$stati++;
							}
							while($repuser>=$statindex[$stati]);
							$statuser=implode($status,"@");
						}
						$udt[13]=$statuser;
					}
					$ulines[$i]=implode($udt,"|");
				}
				$fp=fopen("datan/usersdat.php","a+");
				flock($fp,LOCK_EX);
				ftruncate($fp,0); //������� ���������� �����
				for ($i=0;$i<$ui;$i++)
				fputs($fp,$ulines[$i]);
				flock($fp,LOCK_UN);
				fclose($fp);

				///// ���� �� ���-�� ������ � ����
				for ($i=1;$i<$ui;$i++)
				{
					$udt=explode("|", $ulines[$i]);

					if ($i<=$si) $sdt=explode("|",$slines[$i]); else $sdt[0]="";
					if ($udt[0]==$sdt[0])
					{
						$udt[0]=str_replace("\r\n",'', $udt[0]);
						$ok=1;

						if (isset($sdt[1]) and isset($sdt[2]) and isset($sdt[3]) and isset($sdt[4]))
						{
							$lines[$i]="$slines[$i]";
						} else {
							$lines[$i]="$udt[0]|0|0|0|0|||||\r\n";
						}
					}
					// ���� � ����� ���������� - ����� ������ �������� �����
					if ($ok!="1") {
						for ($j=1;$j<$si;$j++)
						{
							$sdt=explode("|", $slines[$j]);

							if ($udt[0]==$sdt[0]) {$ok=1; $lines[$i]=$slines[$j];} // ���� ���=��� - ������ ������ �����
						}
						if ($ok!="1") $lines[$i]="$udt[0]|0|0|0|0|||||\r\n"; // ������ ����� � ������� �����������
					}
					$ok=null;
					$ii=count($lines);
				}
				$fp=fopen("datan/userstat.dat","a+");
				flock($fp,LOCK_EX); 
				ftruncate ($fp,0);
				fputs($fp,"����|���|�����|����|�������|����� ������ �������|ip|||\r\n");
				for ($i=1;$i<=$ii;$i++)
				fputs($fp,"$lines[$i]");
				fflush($fp);
				flock($fp,LOCK_UN);
				fclose($fp);
			}

			if (file_exists("data/".$forumid))
			{
				$theme=file("data/".$forumid);
				if (!$mpp) {$mpp="10";}
				$cnt=count($theme);
				$ccnt=$cnt-1;
				$pages=ceil($ccnt/$mpp);
				list($n_name,$e_email,$d_date,$topic,$c_country_img,$c_country_name,$i_ip,$c_country,$l_latitude,$l_longitude,$tektime,$fileload,$fileloadsize)=explode("�",$theme[0]);
				$topic=trim($topic);

				/////////////////// ������ ��������� ���������
				if ($lastmess=="1")
				{
					$lastmessfile="datan/lastmes.dat";
					$newlines=file("$lastmessfile");
					$ni=count($newlines)-1;
					$i2=0;
					$newlineexit="";

					$msg=str_replace("�",'', $msg);

					$msg=preg_replace("/\[hide\](.+?)\[\/hide\]/is", " [����� ����� �� ������] ", $msg);
					$msg=preg_replace("/\[hide=(.+?)\](.+?)\[\/hide\]/is", " [����� ��� \\1] ", $msg);

					$valuelast=$name."�".$email."�".$date."�".$forumid."�".$topic."�".$pages."�".$msg."�".$country_img."�".$country_name."�".$ip."�".$country."�".$latitude."�".$longitude;

					$valuelast=trim(str_replace("
", '<br>', $valuelast));
					$valuelast=str_replace("\r\n",'<br>', $valuelast);
					$valuelast=str_replace("\n",'<br>', $valuelast);
					$valuelast=str_replace("\r",'', $valuelast);
					$valuelast=str_replace("\t",' ', $valuelast);

					for ($i=0;$i<=$ni;$i++) {
						$ndt=explode("�",$newlines[$i]);
						if (isset($ndt[3])) {
							if ($forumid!=$ndt[3]) $newlineexit.="$newlines[$i]"; $i2++;
						}
					}
					if ($i2>0) {
						$newlineexit.=$valuelast;
						$fp=fopen("$lastmessfile","w");
						flock($fp,LOCK_EX);
						fputs($fp,"$newlineexit\r\n");
						fflush($fp);
						flock($fp,LOCK_UN);
						fclose($fp);
					} else {
						$fp=fopen("$lastmessfile","a+");
						flock($fp,LOCK_EX);
						fputs($fp,"$valuelast\r\n");
						fflush($fp);
						flock($fp,LOCK_UN);
						fclose($fp);
					}

					$file=file($lastmessfile);
					$i=count($file);
					if ($i>=$lastlines) {
						$fp=fopen("$lastmessfile","w");
						flock($fp,LOCK_EX);
						unset($file[0]);
						fputs($fp, implode("",$file));
						fflush($fp);
						flock($fp,LOCK_UN);
						fclose($fp);
					}
				}
			}
		}
	} else {
		if (file_exists("data/".$forumid))
		{
			$theme=file("data/".$forumid);
			if (!$page) $page="1";
			if (!$mpp) $mpp="10";
			$p=($page-1)*$mpp+1;
			$cnt=count($theme);
			$ccnt=$cnt-1;
			$pages=ceil($ccnt/$mpp);

			list($name,$email,$date,$topic,$country_img,$country_name,$ip,$country,$latitude,$longitude,$tektime,$fileload,$fileloadsize)=explode("�",$theme[0]);

			$topic=trim($topic);

			print "<script>document.title+=\": $topic\"</script>\n";
			print "<table align=center cellspacing=0 cellpadding=1 border=0><tr><td><div align=center class=med>";

			$prev=min($page-1,$pages);
			$next=max($page+1,1);

			$pageinfo="";

			if ($page>1) print "<a href=\"index.php?forumid=".$forumid."&page=".$prev."\" class=pagination>&#9668;</a>&nbsp; &nbsp;";

			if ($pages>1) {
				if ($page>=4 and $pages>5) $pageinfo.="<a href='index.php?forumid=$forumid&page=1' class=pagination>1</a> ... ";
				$f1=$page+2;
				$f2=$page-2;
				if ($page<=2) {$f1=5; $f2=1;}
				if ($page>=$pages-1) {$f1=$pages; $f2=$page-3;}
				if ($pages<=5) {$f1=$pages; $f2=1;}

				for($i=$f2; $i<=$f1; $i++)
				{
					if ($page==$i) $pageinfo.="<b class=currentpage>$i</b>&nbsp;"; else $pageinfo.="<a href='index.php?forumid=$forumid&page=$i' class=pagination>$i</a>&nbsp;";
				}
				if ($page<=$pages-3 and $pages>5) $pageinfo.="... <a href='index.php?forumid=$forumid&page=$pages' class=pagination>$pages</a>";
			}

			print $pageinfo;

			if ($page<$pages) print "&nbsp; <a href=\"index.php?forumid=".$forumid."&page=".$next."\" class=pagination>&#9658;</a>";

			print "</div></td></tr></table><table class=f align=center cellspacing=1 cellpadding=0 bgcolor='#000000' style='margin-top:3px' border=0><td><table width='100%' cellspacing=0>
				<td class=t><font color=red>����:</font>&nbsp;<a href='index.php' title='��������� � ������ ���'>$topic</a> &nbsp;<span class=small>[�������: $ccnt, ��������: $page]</span></td>
				<td class=t valign=top align=right>";

			if ($_COOKIE['cadmin']==$adminname && $_COOKIE['cpass']==$adminpass)
			{
				print "<a href='index.php?mode=unlink&forumid=$forumid' style='color:red;' onclick=\"return confirm('������� ��� ����?')\">�������</a> �&nbsp;";
			}

			print "<a href='index.php'>������ ���</a> � <a href='index.php?action=newtopic'>������� ����</a></td></table></td></table>";


			for ($i=$p; $i<min($mpp+$p, $cnt); $i++)
			{
				list($name,$email,$date,$msg,$country_img,$country_name,$ip,$country,$latitude,$longitude,$tektime,$fileload,$fileloadsize)=explode("�",$theme[$i]);

				$msg_for_admin=$msg;

				$msg=preg_replace('#\[quote\](.+?)\[/quote\]#is', '<div class=q>$1</div>', $msg);
				$msg=str_replace(" [/quote]","[/quote]",$msg);
				$msg=str_replace("\n[/quote]","[/quote]",$msg);

				$msg=preg_replace("/\[hide\](.*?)\[\/hide\]/eis", "hideguest('\\1')", $msg);
				$msg=preg_replace("/\[hide=(.*?)\](.*?)\[\/hide\]/eis", "hideuser('\\1','\\2')", $msg);

				$msg=preg_replace("/(\[code\])(.+?)(\[\/code\])/is","<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b><u><small>���:</small></u></b><div class=code style='margin-left:18px;margin-right:18px;padding:5px;margin-top:2px'>$2</div>", $msg);

				$msg=preg_replace('#\[b\](.+?)\[/b\]#is', '<b>$1</b>', $msg);
				$msg=preg_replace('#\[i\](.+?)\[/i\]#is', '<i>$1</i>', $msg);
				$msg=preg_replace('#\[u\](.+?)\[/u\]#is', '<u>$1</u>', $msg);
				$msg=preg_replace('#\[s\](.+?)\[/s\]#is', '<s>$1</s>', $msg);

				$msg=preg_replace('#\[big\](.+?)\[/big\]#is', '<big>$1</big>', $msg);
				$msg=preg_replace('#\[small\](.+?)\[/small\]#is', '<small>$1</small>', $msg);

				$msg=preg_replace('#\[red\](.+?)\[/red\]#is', '<font color=red>$1</font>', $msg);
				$msg=preg_replace('#\[blue\](.+?)\[/blue\]#is', '<font color=blue>$1</font>', $msg);
				$msg=preg_replace('#\[green\](.+?)\[/green\]#is', '<font color=green>$1</font>', $msg);
				$msg=preg_replace('#\[orange\](.+?)\[/orange\]#is', '<font color=orange>$1</font>', $msg);
				$msg=preg_replace('#\[yellow\](.+?)\[/yellow\]#is', '<font color=yellow>$1</font>', $msg);

				$msg=preg_replace('#\[left\](.+?)\[/left\]#is', '<div align=left>$1</div>', $msg);
				$msg=preg_replace('#\[center\](.+?)\[/center\]#is', '<div align=center>$1</div>', $msg);
				$msg=preg_replace('#\[right\](.+?)\[/right\]#is', '<div align=right>$1</div>', $msg);

				$msg=preg_replace('#\[spoiler=(.*?)\](.*?)\[/spoiler\]#i', '<div style="padding:0px;margin:5px;border:#999999 0px solid;"><a style="border-bottom: 1px dashed; text-decoration: none;" href="#" onclick="var container=this.parentNode.getElementsByTagName(\'div\')[0];if(container.style.display!=\'\'){container.style.display=\'\';} else {container.style.display=\'none\';}">$1</a><div style="display:none;word-wrap:break-word;overflow:hidden;"><div class="spoiler">$2</div></div></div>', $msg);
				$msg=preg_replace('#\[spoiler\](.*?)\[/spoiler\]#i', '<div style="padding:0px;margin:5px;border:#999999 0px solid;"><a style="border-bottom: 1px dashed; text-decoration: none;" href="#" onclick="var container=this.parentNode.getElementsByTagName(\'div\')[0];if(container.style.display!=\'\'){container.style.display=\'\';} else {container.style.display=\'none\';}">�������</a><div style="display:none;word-wrap:break-word;overflow:hidden;"><div class="spoiler">$1</div></div></div>', $msg);

				$msg=preg_replace("/(\[video\])(.+?)(\[\/video\])/is","<br><video width=640 height=480 controls><source src=\"$2?autoplay=false\" type=\"video/mp4\"></video><br>", $msg);
				$msg=preg_replace("/(\[video=)(\S+?)(\,)(.+?)(\])(.+?)(\.flv|\.mp4|\.wmv|\.avi|\.mpg|\.mpeg)(\[\/video\])/is", "<br><video width=\"$2\" height=\"$4\" controls><source src=\"$6$7\" autoplay=false type=\"video/mp4\"></video><br>", $msg);

				$msg=preg_replace("/(\[audio\])(.+?)(\[\/audio\])/is","<br><audio src=\"$2?autoplay=false\" type=\"audio/mp3\" controls></audio><br>", $msg);

				//$msg=preg_replace("/(\[youtube\])(.+?)(\[\/youtube\])/is","<br><object width=640px height=480px><param name=movie value=\"https://www.youtube.com/v/$2\"></param><param name=allowFullScreen value=true></param><param name=allowscriptaccess value=always></param><embed src=\"https://www.youtube.com/v/$2\" type=\"application/x-shockwave-flash\" allowscriptaccess=always allowfullscreen=true width=640px height=480px></embed></object><br>", $msg);

				$msg=preg_replace("/\[youtube\]https?:\/\/(?:[a-z\d-]+\.)?youtu(?:be(?:-nocookie)?\.com\/.*v=|\.be\/)([-\w]{11})(?:.*[\?&#](?:star)?t=([\dhms]+))?\[\/youtube\]/i","<br><object width=640px height=480px><param name=movie value=\"https://www.youtube.com/v/$1\"></param><param name=allowFullScreen value=true></param><param name=allowscriptaccess value=always></param><embed src=\"https://www.youtube.com/v/$1\" type=\"application/x-shockwave-flash\" allowscriptaccess=always allowfullscreen=true width=640px height=480px></embed></object><br>", $msg);

				$msg=preg_replace("/\[vimeo\](http|https)?:\/\/(www\.|player\.)?vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|video\/|)(\d+)(?:|\/\?)\[\/vimeo\]/i", "<br><embed src=\"https://player.vimeo.com/video/$4\" allowscriptaccess=always allowfullscreen=true width=640px height=480px></embed><br>", $msg);

				$msg=preg_replace("/\[dzen\](http|https)?:\/\/(www\.)?dzen\.ru\/embed\/(.+)\[\/dzen\]/i", "<br><embed src=\"https://dzen.ru/embed/$3\" allow=\"autoplay; fullscreen; accelerometer; gyroscope; picture-in-picture; encrypted-media\" frameborder=0 scrolling=no allowfullscreen width=640px height=480px></embed><br>", $msg);

				$msg=preg_replace("/\[rutube\](http|https)?:\/\/(www\.)?rutube\.ru\/video\/(\w+)\[\/rutube\]/i", "<br><embed src=\"https://rutube.ru/play/embed/$3\" frameBorder=0 allow=\"clipboard-write; autoplay\" webkitAllowFullScreen mozallowfullscreen allowFullScreen width=640px height=480px></embed><br>", $msg);

				$msg=preg_replace("/\[telegram\](http|https)?:\/\/(www\.)?t\.me\/(.+)\/(\d+)\[\/telegram\]/i", "<br><iframe id=\"telegram-post-youtubequest-$4\" src=\"https://t.me/$3/$4?embed=1\" frameborder=\"0\" scrolling=\"yes\" style=\"overflow: hidden; color-scheme: light dark; border: none; min-width:480px; min-height:400px;\"></iframe><br>", $msg);

				$msg=preg_replace("/\[ok\](http|https)?:\/\/(www\.)?ok\.ru\/video\/(\w+)\[\/ok\]/i", "<br><embed src=\"https://ok.ru/videoembed/$3\" frameborder=\"0\" allow=\"autoplay\" allowfullscreen width=640px height=480px></embed><br>", $msg);

				if ($antimat==1) $msg = remBadWordsA($msg);
				if ($antimatt==1) $msg = remBadWordsB($msg);

				//if ($liteurl==1) {$msg=preg_replace("#([^\[img\]])(http|https|ftp|goper):\/\/([a-zA-Z0-9\.\?&=\;\-\/_]+)([\W\s<\[]+)#i", "\\1<a href=\"\\2://\\3\" target=\"_blank\">\\2://\\3</a>\\4", $msg);}
				//$msg=preg_replace('#\[img\](.+?)\[/img\]#', '<a href="$1" target="_new" title="������� � ����� ����"><img src="$1" border="0" width="auto"></a>', $msg);

				$msg=ikoncode($msg);

				//if ($liteurl==1) $msg=autolink($msg);

				if ($liteurl==1) {
					$msg=preg_replace("/([\s>\]]+)www\.([\w\-\.,@?^=%&:;\/~\+#]*[\w\-\@?^=%&:;\/~\+#])/i", "\\1http://www.\\2", $msg); 
					$msg=preg_replace("/([\s>\]]+)((http|ftp)+(s)?:(\/\/)([\w]+(.[\w]+))([\w\-\.,@?^=%&:;\/~\+#]*[\w\-\@?^=%&:;\/~\+#])?)/i", "\\1<a href=\"\\2\" target=\"_blank\">\\2</a>", $msg);
				}

				$latitude = str_replace(array(".","1","2","3","4","5","6","7","8","9","0"), array("-","i","z","e","y","s","b","f","x","g","o"), $latitude);
				$longitude = str_replace(array(".","1","2","3","4","5","6","7","8","9","0"), array("-","i","z","e","y","s","b","f","x","g","o"), $longitude);
				$user_id = "$latitude-$longitude";

				$you_status="";
				$you_reiting="";
				$you_avatar="";
				$you_flag="";
				$you_email="";
				$you_icq="";
				$you_telegram="";
				$you_site="";
				$you_datareg="";
				$you_from="";
				$you_podpis="";

				$iu=$usercount;
				do {
					$iu--;
					$du=explode("|",$userlines[$iu]);

					if ($_['user']===$du[0]) $you_zvezd=$du[2];

					if ($du[0]===$name)
					{
						if (isset($du[12]))
						{
							$you_name=$du[0];
							$you_status=$du[13];
							$you_reiting=$du[2];
							$you_avatar=$du[12];
							$you_flag=$du[14];
							$you_email=$du[3];
							$you_icq=$du[7];
							$you_telegram=$du[16];
							$you_site=$du[8];
							$you_datareg=$du[4];
							$you_from=$du[9];
							$you_podpis=$du[11];
							$userpn=$iu;
						}
					}
				} while($iu>"0");




				$uslines=file("datan/userstat.dat");
				$usr=count($uslines)-1;

				for ($uu=0; $uu<=$usr; $uu++)
				{
					$udr=explode("|",$uslines[$uu]);
					if ($_['user']===$udr[0]) $you_repa=$udr[3];
				}


				$lines=file("datan/topic.dat");
				for($a=0; $a<count($lines); $a++)
				{
					$dttt=explode("�", $lines[$a]);
					if ($forumid==$dttt[2])
					{
						if (empty($_['user']) & $dttt[12]>0 || empty($_['user']) & $dttt[13]>0) exit("<script>setTimeout(function(){window.location.href='index.php';},10000);</script><br><br><br><br><center><fieldset style='width:500px;border:solid 1px #777;'><legend align=center><font color=red>������ ���������!</font></legend>��� ��������� ���� ���� �� ������ ���� ����������������!<br><br>[<a href=\"index.php\">��������� �����</a>]</fieldset></center>");

						if (isset($_COOKIE['cadmin']) && isset($_COOKIE['cpass']) && $_COOKIE['cadmin']==$adminname && $_COOKIE['cpass']==$adminpass)
						{
							continue;
						} else {
							if ($_['user'] && $you_zvezd<$dttt[12]) exit("<script>setTimeout(function(){window.location.href='index.php';},10000);</script><br><br><br><br><center><fieldset style='width:500px;border:solid 1px #777;'><legend align=center><font color=red>������ ���������!</font></legend>��� ��������� �� ������ ����� ������� <b>$dttt[12]</b> ����. � ��� <b>$you_zvezd</b> ����.<br><br>[<a href=\"index.php\">��������� �����</a>]</fieldset></center>");

							if ($_['user'] && $you_repa<$dttt[13]) exit("<script>setTimeout(function(){window.location.href='index.php';},10000);</script><br><br><br><br><center><fieldset style='width:500px;border:solid 1px #777;'><legend align=center><font color=red><b>������ ���������!</b></font></legend>��� ��������� �� ������ ����� ������� <b>$dttt[13]</b> ������ ���������. � ��� <b>$you_repa</b><br><br>[<a href=\"index.php\">��������� �����</a>]</fieldset></center>");
						}
					}
				}



				$topicavtor = explode("�", $theme[0]);

				if ($name == $topicavtor[0]) {$topicavtor="����� ����";} else {$topicavtor="";}

				$newstatus=explode("@", $you_status);

				print "
					<table class=f align=center cellspacing=1 cellpadding=0 border=0>	
					<tr><td rowspan=2 valign=top class=name>
					<table cellspacing=0 cellpadding=1 width='225px' border=0>
					<tr><td valign=top class=name><a href=\"javascript:ins('".$name."')\" class='name' title='�������� ��� � ����� ������'>".$name."</a>";


				if ($_['user'])
				{
					print " <span class=small><sup><a href='index.php?event=profile&pname=".$name."' style='text-decoration:none' title='����� (����� �����). ������� � ������� ������������'>$you_reiting</a></sup><div style='display:inline-block;margin:0 0 -5 5;' class='$country_img' title='$country, $country_name'></div><br><div class=small>$newstatus[0]<br>$topicavtor</div></td></tr><tr><td valign=top class=name><br>";

				} else {
					print " <span class=small><sup title='���������� �����'>$you_reiting</sup><div style='display:inline-block;margin:0 0 -5 5;' class='$country_img' title='$country, $country_name'></div><br>$newstatus[0]<br>$topicavtor</span></td></tr><tr><td valign=top class=name><br>";
				}




				////////////////// ��� �� IP, ������� ��� ��� ��������
				if (is_file("datan/banip.dat"))
				{
					$linesb=file("datan/banip.dat");
					$b=count($linesb);
					$tektime=time();
					do {
						$b--;
						$dtb=explode("|", $linesb[$b]);

						if ($dtb[1]===$name and $tektime<$dtb[4] and $dtb[6]==TRUE)
						{
							if ($dtb[4]>time()) $userban_time = ceil(($dtb[4] - $tektime)/86400);
							$dtb[3]=date("d.m.Y_H:i",$dtb[3]);
							$dtb[4]=date("d.m.Y_H:i",$dtb[4]);

							$userban="<div><div class='tooltip'><div style='background:red;padding:0 2;border:1px solid black;color:#111;font-family:tahoma;font-size:9px;font-weight:bold;'>���</div><span style='width:210px;' class='tooltiptext'><b><u>���</u></b>: $dtb[3] �� $dtb[4]<br><b><u>��������</u></b>: $userban_time ��. �� $dtb[5]<br><b><u>�������</u></b>: $dtb[2]</span></div></div>";
							break;
						}

						if ($dtb[1]===$name and $tektime<$dtb[4] and $dtb[6]==FALSE)
						{
							if ($dtb[4]>time()) $userban_time = ceil(($dtb[4] - $tektime)/86400);
							$dtb[3]=date("d.m.Y_H:i",$dtb[3]);
							$dtb[4]=date("d.m.Y_H:i",$dtb[4]);

							$userban="<div><div class='tooltip'><div style='background:yellow;padding:0 2;border:1px solid black;color:#111;font-family:tahoma;font-size:9px;font-weight:bold;'>�������!</div><span style='width:210px;' class='tooltiptext'><b><u>�������.</u></b>: $dtb[3] �� $dtb[4]<br><b><u>��������</u></b>: $userban_time ��. �� $dtb[5]<br><b><u>�������</u></b>: $dtb[2]</span></div></div>";
							break;
						}
						$userban="";

					} while($b>0);

					unset($linesb);
				}

				if (is_file("datan/repa.dat"))
				{
					$lines=file("datan/repa.dat");
					$r=count($lines);
					$tektime=time();
					do {
						$r--;
						$dtr=explode("|",$lines[$r]);
						$point=$dtr[1];
						$repatime=$dtr[0]+(86400*$repatimeday);

						if ($dtr[1]>0)
						{
							$dtr[1]="<div style='background:#B7FFB7;border-right:1px solid #B7FFB7;color:#000;font-family:tahoma;font-size:9px;font-weight:bold;'>$dtr[1]</div>";
						} else {
							$dtr[1]="<div style='background:#FF9F9F;border-right:1px solid #FF9F9F;color:#000;font-family:tahoma;font-size:9px;font-weight:bold;'>$dtr[1]</div>";
						}

						if ($dtr[2]===$name and $repatime>$tektime)
						{
							$dtr[0]=date("d.m.y � H:i", $dtr[0]);

							$ppp="<div class='text-block'><div class='tooltip'>$dtr[1]<span class='tooltiptext'><b><u>��������� ����� ���������</u><br>����</b>: $dtr[3] ($dtr[5])<br><b>��������</b>: $point<br><b>�������</b>: $dtr[4]</span></div></div>";
							break;
						}
						$ppp=" ";
						
					} while($r>0);
				}




				if ($you_avatar!="noavatar.gif" & $you_avatar!="")
				{
					if (is_file("avatars/$you_avatar")) $avpr="$you_avatar"; else $avpr="noavatar.gif";

					if ($avround==1)
					{
						print "<div class='cont'><div class='gravatar' align='center'><div class='holder'><img src=\"avatars/$avpr\" style='border-radius:50%;-moz-border-radius:50%;-webkit-border-radius:50%'><div class='block'><small>[<a class='small' href='#' name=citata onclick=\"window.open('pm.php?id=".$name."','citata','width=800,height=500,left=100,top=100,toolbar=0,status=0,border=0,scrollbars=1');return false;\" title='��������� ������ ���������'>������</a>] [<a class='small' href='index.php?event=profile&pname=".$name."' title='������� ������������'>�������</a>]</small></div></div></div>$ppp $userban</div>";

					} else {
						print "<div class='cont'><div class='gravatar' align='center'><div class='holder'><img src=\"avatars/$avpr\"><div class='block'><small>[<a class='small' href='#' name=citata onclick=\"window.open('pm.php?id=".$name."','citata','width=800,height=500,left=100,top=100,toolbar=0,status=0,border=0,scrollbars=1');return false;\" title='��������� ������ ���������'>������</a>] [<a class='small' href='index.php?event=profile&pname=".$name."' title='������� ������������'>�������</a>]</small></div></div></div>$ppp $userban</div>";

					}
				} else {
					if ($gravatar==1)
					{
						$gravatarimg=md5(strtolower(trim($email)));

						if ($avround==1)
						{
							print "<div class='cont'><div align='center'><div class='holder'><img style='border-radius:50%;-moz-border-radius:50%;-webkit-border-radius:50%' src=\"http://www.gravatar.com/avatar/$gravatarimg?d=identicon&s=$gravatarsize\"><div class='block'><small>[<a class='small' href='#' name=citata onclick=\"window.open('pm.php?id=".$name."','citata','width=800,height=500,left=100,top=100,toolbar=0,status=0,border=0,scrollbars=1');return false;\" title='��������� ������ ���������'>������</a>] [<a class='small' href='index.php?event=profile&pname=".$name."' title='������� ������������'>�������</a>]</small></div></div></div>$ppp $userban</div>";

						} else {
							print "<div class='cont'><div class='gravatar' align='center'><div class='holder'><img src=\"http://www.gravatar.com/avatar/$gravatarimg?d=identicon&s=$gravatarsize\"><div class='block'><small>[<a class='small' href='#' name=citata onclick=\"window.open('pm.php?id=".$name."','citata','width=800,height=500,left=100,top=100,toolbar=0,status=0,border=0,scrollbars=1');return false;\" title='��������� ������ ���������'>������</a>] [<a class='small' href='index.php?event=profile&pname=".$name."' title='������� ������������'>�������</a>]</small></div></div></div>$ppp $userban</div>";

						}
					} else {
						print "<div align='center'></div>";
					}
				}

$rank_list = "<span class='tooltiptext' style='width:250px'>
<b>������</b>: $newstatus[0]<br><br>
0) $stu0 - $stn0 <img src=\"rank/$imgstatus/00.png\" align='absmiddle'><br>
1) $stu1 - $stn1 <img src=\"rank/$imgstatus/01.png\" align='absmiddle'><br>
2) $stu2 - $stn2 <img src=\"rank/$imgstatus/02.png\" align='absmiddle'><br>
3) $stu3 - $stn3 <img src=\"rank/$imgstatus/03.png\" align='absmiddle'><br>
4) $stu4 - $stn4 <img src=\"rank/$imgstatus/04.png\" align='absmiddle'><br>
5) $stu5 - $stn5 <img src=\"rank/$imgstatus/05.png\" align='absmiddle'><br>
6) $stu6 - $stn6 <img src=\"rank/$imgstatus/06.png\" align='absmiddle'><br>
7) $stu7 - $stn7 <img src=\"rank/$imgstatus/07.png\" align='absmiddle'><br>
8) $stu8 - $stn8 <img src=\"rank/$imgstatus/08.png\" align='absmiddle'><br>
9) $stu9 - $stn9 <img src=\"rank/$imgstatus/09.png\" align='absmiddle'><br>
!) <img src=\"rank/$imgstatus/moder.png\" align='absmiddle'> Moder<br>
!) <img src=\"rank/$imgstatus/admin.png\" align='absmiddle'> Admin
</span></div>";

				if ($rankline==1)
				{
					print "<div align='center'>&nbsp;</div>";
					if ($newstatus[0]==$stn0) print"<div class='tooltip'><img src=\"rank/$imgstatus/00.png\" border=0>$rank_list";
					if ($newstatus[0]==$stn1) print"<div class='tooltip'><img src=\"rank/$imgstatus/01.png\" border=0>$rank_list";
					if ($newstatus[0]==$stn2) print"<div class='tooltip'><img src=\"rank/$imgstatus/02.png\" border=0>$rank_list";
					if ($newstatus[0]==$stn3) print"<div class='tooltip'><img src=\"rank/$imgstatus/03.png\" border=0>$rank_list";
					if ($newstatus[0]==$stn4) print"<div class='tooltip'><img src=\"rank/$imgstatus/04.png\" border=0>$rank_list";
					if ($newstatus[0]==$stn5) print"<div class='tooltip'><img src=\"rank/$imgstatus/05.png\" border=0>$rank_list";
					if ($newstatus[0]==$stn6) print"<div class='tooltip'><img src=\"rank/$imgstatus/06.png\" border=0>$rank_list";
					if ($newstatus[0]==$stn7) print"<div class='tooltip'><img src=\"rank/$imgstatus/07.png\" border=0>$rank_list";
					if ($newstatus[0]==$stn8) print"<div class='tooltip'><img src=\"rank/$imgstatus/08.png\" border=0>$rank_list";
					if ($newstatus[0]==$stn9) print"<div class='tooltip'><img src=\"rank/$imgstatus/09.png\" border=0>$rank_list";
					if ($newstatus[0]==$stn_end) print"<div class='tooltip'><img src=\"rank/$imgstatus/10.png\" border=0>$rank_list";
					if ($newstatus[0]=="���������") print"<div class='tooltip'><img src=\"rank/$imgstatus/moder.png\" border=0>$rank_list";
					if ($newstatus[0]=="�������������") print"<div class='tooltip'><img src=\"rank/$imgstatus/admin.png\" border=0>$rank_list";
				}

				print "
					</td></tr><tr><td valign=top class=name><div align='center'>
					<!--div style='display:inline-block;' class='$country_img' title='$country, $country_name'></div><font style='font-size:9px;'>$user_id</font-->
					</div></td></tr><tr><td valign=top class=name>";

				if ($nagrada==1)
				{
					for ($ii=1; $ii<count($newstatus); $ii++)
					{
						print"<div class='tooltip'><img src='$fskin/medal.gif' border=0 style='cursor:help'><span class='tooltiptext' style='width:200px'><b>������� #$ii</b>: $newstatus[$ii]</span></div> ";
					}
					print "<br>";
				}


				if (isset($_COOKIE['cadmin']) && isset($_COOKIE['cpass']) && $_COOKIE['cadmin']==$adminname && $_COOKIE['cpass']==$adminpass)
				{
					print "<a href=\"mailto:".$email."\" style='font-family:tahoma;font-size:11px;font-weight:normal'>".$email."</a><br><span class=small><a href='https://ip-whois-lookup.com/lookup.php?ip=".$ip."' target='_blank' title='���������� ���������� � IP'>".$ip."</a>&nbsp;[<a href=index.php?badip&ip_get=".$ip."&nickban=".$name." title='�������� �� 3 ��� (���� �� ���������)'>���</a>]</span><br>";
				}

				if (is_file("flags/$you_flag")) $flagpr="$you_flag"; else $flagpr="noflag.gif";

				$array = explode('.',$you_flag);

				$you_flag_name = $array[0];

				$uslines=file("datan/userstat.dat");
				$usi=count($uslines)-1;

				// ���� ����� �� ����� � userstat.dat
				for ($iu=0; $iu<=$usi; $iu++)
				{
					$udt=explode("|",$uslines[$iu]);

					if ($udt[0]==$name)
					{
						print "<div align=center title='[ ��� / ��������� / ��������� / ��������� ]' style='background-color:;font-family:tahoma;font-size:10px;font-weight:normal;border:0px solid #333;margin:5px'>[ $udt[1] / $udt[2] / <a href='#m$fm' style='text-decoration:none' onclick=\"window.open('admin.php?event=repa&name=$udt[0]&who=$userpn', 'repa', 'width=650,height=600,left=50,top=50,scrollbars=yes');\"><b>$udt[3]</b> &#177;</a> / $udt[4] ]";

						if ($knopki==1)
						{
							print "</div>";
						} else {
							if ($_['user'])
							{
print "<details title='���������� ������ �������' style='cursor:hand;display:inline-block;font-family:tahoma;font-size:12px;font-weight:normal;text-align:left;padding:0px 1px;margin:3 0 3;'><summary style='text-align:center;'></summary><div style='width:210px'><table border=0 style='border:1px solid #669900;width:210px'>
<tr><td><b style='font-family:tahoma;font-size:11px'>Who</b></td><td><i style='font-family:tahoma;font-size:11px'>$newstatus[0]</i></td></tr>
<tr><td><b style='font-family:tahoma;font-size:11px'>Reg</b></td><td><i style='font-family:tahoma;font-size:11px'>$you_datareg [#$userpn]</i></td></tr>
<tr><td><b style='font-family:tahoma;font-size:11px'>Frm</b></td><td><img src=\"flags/$flagpr\"/> <i style='font-family:tahoma;font-size:11px'>$you_flag_name $you_from</i></td></tr>
<tr><td><b style='font-family:tahoma;font-size:11px'>kicq</b></td><td><i style='font-family:tahoma;font-size:11px'>$you_icq</i></td></tr>
<tr><td><b style='font-family:tahoma;font-size:11px'>tg</b></td><td><i style='font-family:tahoma;font-size:11px'><a href=\"$you_telegram\" target=_new>$you_telegram</a></i></td></tr>
<tr><td><b style='font-family:tahoma;font-size:11px'>Web</b></td><td><i style='font-family:tahoma;font-size:11px'><a href=\"$you_site\" target=_new>$you_site</a></i></td></tr></table></div></details></div>";

							}
							print "</div>";
						}

						if ($knopki==1)
						{
							if ($_['user'])
							{
print "<style>a.glf{font-family:tahoma;font-size:11px;font-weight:normal;color:#669900;border-radius:3px;box-shadow:1px 1px #111;padding:0px 2px;text-decoration:none;border:1px solid #669900;}a.glf:hover{background-color:#669900;color:#fff;}</style>
<a class='glf' href='#' name=citata onclick=\"window.open('pm.php?id=".$name."','citata','width=800,height=500,left=100,top=100,toolbar=0,status=0,border=0,scrollbars=1');return false;\" title='��������� ������ ���������'>��</a> <a class='glf' href='index.php?event=profile&pname=".$name."' title='������� ������������'>��</a> <details title='���������� ������ ��' style='display:inline-block;cursor:hand;border-radius:3px;box-shadow:1px 1px #111;padding:0px 2px;border:1px solid #669900;font-family:tahoma;font-size:11px;font-weight:normal;text-align:left;margin:5 0 5;'>
<summary style='text-align:center;'></summary><div style='width:150px;'><small>
<b>������:</b> <i>$newstatus[0]</i><br>
<b>�����:</b> <i>$you_datareg [#$userpn]</i><br>
<b>������:</b> <img src=\"flags/$flagpr\"/> <i>$you_flag_name $you_from</i><br>
<b>Kicq:</b> <i>$you_icq</i><br>
<b>Tg:</b> <i>$you_telegram</i><br>
<b>����:</b> <i><a href=\"$you_site\" target=_new>$you_site</a></i></small></div></details>";

							}
						}
					}
				}

				$newstatus=explode("@", $you_status);

				$ed_msg="";

				if ($_['user'] && $you_name==$_['user']) //stristr($newstatus[0],"�����")
				{
					$ed_msg=" &nbsp; <i><a href=\"index.php?event=edit_post&forumid=$forumid&m=$i&page=$page\" style='text-decoration:none'>�������������</a></i>";
				}

				print "</td></tr></table></td><td width='99%' colspan=2 class=msg><small><a name=\"m$i\"></a><i>��������: $date</i> &nbsp; | &nbsp; <a href=\"index.php?forumid=$forumid&page=$page#m$i\" title='������ �� ��� ���������' onClick=\"prompt('������ �� ���������','http://$hst$self?forumid=$forumid&page=$page#m$i')\">#$i</a> &nbsp; | <a href=\"javascript:scroll(0,0)\"> &nbsp; &#9650; &nbsp; </a> : <a href=\"javascript:scroll(100000,100000)\"> &nbsp; &#9660; &nbsp; </a> : <a href='index.php' title='��������� �� �������'> &nbsp; &#9668; &nbsp; </a> | $ed_msg</small>";

				print "<table cellspacing=0 cellpadding=0 width='100%' border=0 height='90%'><tr><td class=msg>";


				//////////////////// �������������� ��������� ���� �����
				if (isset($_COOKIE['cadmin']) && isset($_COOKIE['cpass']) && $_COOKIE['cadmin']==$adminname && $_COOKIE['cpass']==$adminpass)
				{
					print "
						<form action=\"index.php\" method=post style='padding:0 5 0 0'>
						<input type=hidden name=forumid value=\"$forumid\">
						<input type=hidden name=msg value=\"$i\">
						<textarea name=text style='height: 130px; border: #222 1px solid;margin:0'>";

					$msg_for_admin=str_replace("<br>","\n",$msg_for_admin); // �������� ������

					print $msg_for_admin;

				} else 

				/////////////////// print "<table width='100%' border=0 height='90%'><tr><td class=msg>".$msg."</td></tr></table>";

				print $msg; 

				///////////////////






				////////////////// ���� ���� ��������� � ��������� - �� ���������� ������ � ������ �� ����
				if (isset($fileload) && $fileload != "")
				{
					if (is_file("$filedir/$fileload"))
					{
						$fsize=round($fileloadsize/10.24)/100; 

						print"<br><br><font style='font-size:11px;'>&nbsp; &nbsp; ��������� ����:</font><br>";

						if (file_exists("$filedir/sm-$fileload"))
						{
							$show_img="<img src=\"$filedir/sm-$fileload\" style=\"border:1px solid #ddd; padding:1px;cursor:pointer\" onclick=\"TINY.box.show({image:'$filedir/$fileload',boxid:'frameless',animate:true})\">";
						} else {
							$show_img="<img src=\"$filedir/$fileload\" style=\"border:1px solid #ddd; padding:1px;\"> ";
						}

						if (preg_match("/.(jpg|jpeg|gif|png)+$/is", $fileload)) print "&nbsp; &nbsp; $show_img";

						else print "&nbsp; &nbsp; <img border=0 src=\"$fskin/ico_file.gif\">&nbsp;<a href=\"download.php?file=$fileload\" title='��������!!! �� ���������� ���� ���� �� ���� ����� � ����. ������ �����������!'>$fileload</a>&nbsp;<font STYLE='font-size:9px;'><sup title='���������� ����������' style='cursor:help'><script src='download.php?filecnt=$fileload'></script></sup>&nbsp;($fsize ��)</font>";
					}
				}


				if (isset($_COOKIE['cadmin']) && isset($_COOKIE['cpass']) && $_COOKIE['cadmin']==$adminname && $_COOKIE['cpass']==$adminpass)
				{
					print "</textarea><input type=submit class=button style='width:80px;margin:;' value='��������'>&nbsp;<button class=fbutton style='height:22px;width:80px; margin:2px;color:red;text-decoration:none'><a href=\"index.php?mode=unset&forumid=$forumid&msg=$i\" onclick=\"return confirm('������� ��� ���������?')\" style='color:red;text-decoration:none'>�������</a></button></form>";
				}

				if ($you_podpis)
				{
					if (strlen($you_podpis)>3) print"<tr><td class=date valign=bottom><small><font color='#999'>---------<br>$you_podpis</font></small></td></tr>";
				}

				print "</td></tr></table></td></tr></table>";

			}

			print "<table class=f align=center cellspacing=1 cellpadding=0 bgcolor='#000000' style='margin-top:0px' border=0><td><table width='100%' cellspacing=0><td class=t><font color=red>����:</font>&nbsp;<a href=\"index.php\" title='��������� � ������ ���'>$topic</a> &nbsp;<span class=small>[�������: $ccnt, ��������: $page]</span></td></table></td></table><br><table align=center cellspacing=0 cellpadding=1 border=0><tr><td><div align=center class=med>";

			$prev=min($page-1,$pages);
			$next=max($page+1,1);

			if ($page>1) print "<a href=\"index.php?forumid=".$forumid."&page=".$prev."\" class=pagination>&#9668;</a>&nbsp; &nbsp;";

			print $pageinfo;

			if ($page<$pages) print "&nbsp; <a class=pagination href=\"index.php?forumid=".$forumid."&page=".$next."\">&#9658;</a>";

			print "</div></td></tr></table>";


			////////////////// ���� �������/������� � ����
			if (is_file("data/".$forumid.".user"))
			{
				$tu = explode('|', file_get_contents("data/$forumid.user"));

				if (!empty($tu[0]))
				{
					if ((preg_match("/\b".$name."\b/i", $tu[0])) && !strstr($you_status,"�������������")) exit("<div align=center><br><br><br><br><br><fieldset style='width:400px;border: #333 1px solid;'><legend align='center'><font size=2 face=tahoma color=red><b>��������!</b></font></legend><br><center><font size=2 face=tahoma><b>����� ���� �������� ��� ����������� � ����������!</b></font></center><br></fieldset><br><br><font size=2 face=tahoma><a href=\"index.php\" style='text-decoration:none;'>[��������� �����]</a></font></div>");
				}
				if (!empty($tu[1]))
				{
					if ((!preg_match("/\b".$name."\b/i", $tu[1])) && !strstr($you_status,"�������������")) exit("<div align=center><br><br><br><br><br><fieldset style='width:400px;border: #333 1px solid;'><legend align='center'><font size=2 face=tahoma color=red><b>��������!</b></font></legend><br><center><font size=2 face=tahoma><b>����������� � ���� ����� ����� � ������������:<br>$tu[1]</b></font></center><br></fieldset><br><br><font size=2 face=tahoma><a href=\"index.php\" style='text-decoration:none;'>[��������� �����]</a></font></div>");
				}
			}


			////////////////// ��� �� IP (���� ��������� ����)
			if (is_file("datan/banip.dat"))
			{
				$linesb=file("datan/banip.dat");
				$ib=count($linesb);
				$tektime=time();
				if ($ib>0) {
					do {
						$ib--;
						$idtb=explode("|", $linesb[$ib]);
						if ($idtb[0]===$ip and $tektime<$idtb[4] and $idtb[6]==TRUE or $idtb[1]===$user and $tektime<$idtb[4] and $idtb[6]==TRUE)
						{
							$idtb[3]=date("d.m.Y - H:i",$idtb[3]);
							$idtb[4]=date("d.m.Y - H:i",$idtb[4]);

							exit("<br><div align=center><fieldset style='width:400px;border: #333 1px solid;'><legend align='center'><font size=2 face=tahoma color=red><b>�� �������� �������������� �� $idtb[5] ��.</b></font></legend><br><center><font size=2 face=tahoma><i>$idtb[3]</i> &nbsp; <b>��</b> &nbsp;  <i>$idtb[4]</i><br><br><b><u>�������</u>:</b> $idtb[2]</font></center><br></fieldset></div><br>");
						}
					}
					while($ib>0);
				}
				unset($linesb);
			}



			if ($_['user'] && isset($cname) && isset($cpassreg) && isset($cmail))
			{
$formreg="
<script>fetch('https://ipapi.co/json/').then(function(response){return response.json();}).then(function(data){code.value=data.country_code;country.value=data.country_name;city.value=data.city;ips.value=data.ip;latitude.value=data.latitude;longitude.value = data.longitude;});</script>
<script>function textKey(){var ff=document.forms.item('REPLIER');ff.llen.value=".$maxmsg." - ff.msg.value.length;if(ff.msg.value.length>".$maxmsg.")ff.msg.value=ff.msg.value.substr(0,".$maxmsg.");}function f_1(){document.REPLIER.p_send.disabled=true;}</script>
<a name=last></a>
<form action=\"index.php\" method=post name=REPLIER onSubmit=\"f_1(); return true;\" enctype='multipart/form-data'>
<br><table class=f align=center cellspacing=0 cellpadding=2 border=0>
<tr><td>
<input type=button class=button value='B' title='������ �����' style='font-weight:bold;' onclick=\"insbb('[b]','[/b]')\">
<input type=button class=button value='i' title='��������� �����' style='font-style:italic;' onclick=\"insbb('[i]','[/i]')\">
<input type=button class=button value='U' title='������������ �����' style='text-decoration:underline;' onclick=\"insbb('[u]','[/u]')\">
<input type=button class=button value='S' title='����������� �����' style='text-decoration:line-through;' onclick=\"insbb('[s]','[/s]')\">
<div class=rgb><mark><input type=button class=button value='R' title='������� ���� ������' style='color:red;' onclick=\"insbb('[red]','[/red]')\"></mark>
<input type=button class=button value='B' title='����� ���� ������' style='font-weight:bold;color:blue' onclick=\"insbb('[blue]','[/blue]')\"> 
<input type=button class=button value='G' title='������� ���� ������' style='font-weight:bold;color:green' onclick=\"insbb('[green]','[/green]')\">
<input type=button class=button value='O' title='��������� ���� ������' style='font-weight:bold;color:orange' onclick=\"insbb('[orange]','[/orange]')\">
<input type=button class=button value='Y' title='������ ���� ������' style='font-weight:bold;color:yellow' onclick=\"insbb('[yellow]','[/yellow]')\"></div>
<input type=button class=button value='BIG' title='������� �����' onclick=\"insbb('[big]','[/big]')\">
<input type=button class=button value='sm' title='��������� �����' onclick=\"insbb('[small]','[/small]')\">
<div class=align><mark><input type=button class=button value='=--' title='��������� ���� �����' onclick=\"insbb('[left]','[/left]')\"></mark>
<input type=button class=button value='-=-' title='������������ �����' onclick=\"insbb('[center]','[/center]')\">
<input type=button class=button value='--=' title='��������� ����� ������' onclick=\"insbb('[right]','[/right]')\"></div>
<input type=button class=button value='img' title='�������� ��������\n[img]http://site.ru/foto.jpg[/img]' style='width:35px' onclick=\"insbb('[img]','[/img]')\">
<input type=button class=button value='Code' title='���' style='width:35px' onclick=\"insbb('[code]','[/code]')\">
<input type=button class=button value='� �' title='������\n�������� �����, ������� ������ ������������� � ������� ��� ������' style='width:35px' onclick='REPLIER.msg.value += \" [quote]\"+(window.getSelection?window.getSelection():document.selection.createRange().text)+\"[/quote] \"'>
<input type=button class=button value='PM' title='������ ���������\n[hide]������ ����� �� ������ ������[/hide]\n[hide=DDD]����� ������ ���� DDD � �����[/hide]' style='width:35px' onclick=\"insbb('[hide]','[/hide]')\">
<input type=button class=button value='Spoiler' title='������� �����\n[spoiler]�����[/spoiler]\n[spoiler=��������]�����[/spoiler]' style='width:50px' onclick=\"insbb('[spoiler]','[/spoiler]')\">
<div class=media><mark><input type=button class=button value='Media' title='�������� flv, mp4, wmv, avi, mpg\n������:\n[video]http://site.ru/video.flv[/video]\n[video=640,480]http://site.ru/video.flv[/video]' style='width:50px' onclick=\"insbb('[video]','[/video]')\"></mark>
<input type=button class=button value='Music' title='�������� mid, midi, wav, wma, mp3, ogg\n������:\n[audio]http://site.ru/audio.mp3[/audio]' style='width:50px' onclick=\"insbb('[audio]','[/audio]')\">
<input type=button class=button value='Youtube' title='�������� ����� � YouTube\n������:\n[youtube]https://youtu.be/cEnHQYFP2tw[/youtube]\n[youtube]https://www.youtube.com/watch?v=cEnHQYFP2tw[/youtube]' style='width:50px' onclick=\"insbb('[youtube]','[/youtube]')\">
<input type=button class=button value='Rutube' title='�������� ����� � Rutube\n������:\n[rutube]https://rutube.ru/video/ec0873a8b642ee89414dcc5583f23077[/rutube]' style='width:50px' onclick=\"insbb('[rutube]','[/rutube]')\">
<input type=button class=button value='Vimeo' title='�������� ����� � Vimeo\n������:\n[vimeo]https://vimeo.com/805495470[/vimeo]' style='width:50px' onclick=\"insbb('[vimeo]','[/vimeo]')\">
<input type=button class=button value='Dzen' title='�������� ����� � Dzen\n������:\n[dzen]https://dzen.ru/embed/vkqzwsXzF1hw[/dzen]' style='width:50px' onclick=\"insbb('[dzen]','[/dzen]')\">
<input type=button class=button value='ok.ru' title='�������� ����� � ��������������\n������:\n[ok]https://ok.ru/video/7364277307929[/ok]' style='width:50px' onclick=\"insbb('[ok]','[/ok]')\"></div>
<input type=button class=button value='telegram' title='�������� ��������� �� ��������\n���������� � ������ ������ �� ��������� � �������� �� ������ � ���\n������:\n[telegram]https://t.me/youtubequest/3[/telegram]' style='width:55px' onclick=\"insbb('[telegram]','[/telegram]');\">
[<a href='#' onclick='toggleStats(); return false;' style='cursor:pointer;'>FAQ</a>] [<a href='#' onclick=\"window.open('uploader.php', 'upload', 'width=640,height=420,left=100,top=100,toolbar=0,status=0,border=0,scrollbars=1');return false;\" style='text-decoration:none' title='������� �������� �� ����'>UPL</a>]

</td><td><div align=right>��������: <input name=llen style='WIDTH: 50px' value='$maxmsg'></div></td></tr>
<tr><td colspan=2>
<textarea name=msg cols=70 style='height:170px;font-size:9pt' id='expand' onkeyup=textKey();></textarea>
<br><div style='font-size:1px'>&nbsp;</div>
<center><input type=button value='&#9660;&#9660;&#9660;' title='���������' style='height:15px;width:100%;font-size:10px;' onclick=\"hTextarea('expand'); return false;\"></center><div style='font-size:2px'>&nbsp;</div>
<input type=hidden name=action value=answer>
<input type=hidden name=forumid value='$forumid'>
<input type=hidden name=name value='$cname'>
<input type=hidden name=email value='$cmail'>
<input type=hidden name='country' id='country'>
<input type=hidden name='city' id='city'>
<input type=hidden name='code' id='code'>
<input type=hidden name='ips' id='ips'>
<input type=hidden name='latitude' id='latitude'>
<input type=hidden name='longitude' id='longitude'>
</td></tr>
<tr><td colspan=2 style='height:5px'>";

				if ($canupfile=="1" and isset($user))
				{
					$max=round($max_upfile_size/10.24)/100;

					$formreg.="<script>function Show(a){obj=document.getElementById('shipto');if(a)obj.style.display='block'; else obj.style.display='none';}</script>
<input type=radio name=shipopt value=other onClick='Show(1);' style='width:12px;height:12px'>���������� ���� <input type=radio name=shipopt value=same checked onClick='Show(0);' style='width:12px;height:12px'>���<div ID=shipto style='display:none'><input type=file value=1 name=file size=50 style='width:550px;height:20px'><br>&nbsp;��������� �����: ";

					foreach($valid_types_load as $v)

					$formreg.="<b>$v</b>, ";

					$formreg.="�������� �� ����� <b>$max</b> ��<br>&nbsp;��������� ����� ��������� �� ����. ����, ����, ����� ���� (-) � (_)<br>&nbsp;��������� ������������ ������� ����� � ������� � ����� �����<br>&nbsp;��������� ������������ ���� � ������� �����������</div>";
				}

				$formreg.="</td></tr>";

				if ($captchamin==1)
				{
					$formreg.="<tr><td colspan=2><table cellpadding=0 cellspacing=0 border=0><tr><td width='23px'>
<script>function checkedBox(f){if (f.check1.checked) document.getElementById('other').innerHTML='<center><input type=reset class=fbutton value=\'�������\'> &nbsp; &nbsp; <input type=button class=fbutton value=\'��������\' onClick=\'seeTextArea(this.form)\'> &nbsp; &nbsp; <input type=submit class=fbutton value=\'���������\'></center>'; else document.getElementById('other').innerHTML='<center><input type=reset class=fbutton value=\'�������\'> &nbsp; &nbsp; <input type=button class=fbutton value=\'��������\' onClick=\'seeTextArea(this.form)\'> &nbsp; &nbsp; <input type=submit class=fbutton value=\'���������\' disabled=disabled></center>';}</script>
<input type=\"checkbox\" name=\"check1\" onClick=\"checkedBox(this.form)\"></td><td> &nbsp; � �� ���</td></tr></table></td></tr><tr><td colspan=2><div id=other align=center><input type=reset class=fbutton value='�������'> &nbsp; &nbsp; <input type=button class=fbutton value='��������' onClick='seeTextArea(this.form)'> &nbsp; &nbsp; <input type=submit class=fbutton value='���������' disabled=disabled></div></td></tr></form></td></table>";

				} else {
					$formreg.="<tr><td colspan=2><img src=\"index.php?secpic\" id='secpic_img' border=1 align=top title='��� ����� �������� �������� �� ���' onclick=\"document.getElementById('secpic_img').src='index.php?secpic&' + Math.random(); return false\">&nbsp;<input type='text' name='secpic' id='secpic' style='width:60px; border: #333333 1px solid;' title='������� $let_amount ������ ����. ������������ �� ��������' maxlength='10'> <small>������� <b>$let_amount</b> ������ �������</small></td></tr><tr><td colspan=2 align=center><input type=hidden name=add value=''><input type=reset class=fbutton value='�������'> &nbsp; &nbsp; <input type=submit class=fbutton value='���������'></td></tr></form></td></table>";

				}


				$lines=file("datan/topic.dat");

				for($a=0; $a<count($lines); $a++)
				{
					$dtt=explode("�", $lines[$a]);

					if ($forumid==$dtt[2])
					{
						if ($dtt[11]=="0") exit("<center><div align=center style='color:red;font-family:verdana;font-size:12px;font-weight:bold'>���� �������!</div><br>[<a href=\"index.php\">��������� �����</a>]<br></center>"); else print $formreg;
					}
				}
			}
		} else {
			print "	<table class=f align=center cellspacing=1 cellpadding=0 style='margin-top:15px'>
				<td><table width='100%' cellspacing=0><td class=t>�������������� ����</td>
				<td class=t align=right><a href='index.php?id=forum'>������ ���</a> � <a href='index.php?action=newtopic'>������� ����</a></td>
				</table></td></table><div align=center style='color:red;font:bold 12 tahoma'><br><br><br>��� ���� ������� ��� �� �������!<br><br><br></div>";
		}
	}


} elseif (isset($_GET['mode'])) {

	if ($_GET['mode']=="reg")
	{
		if ($riuser)
		{
			print $riuser;
		} else {
			if (isset($_COOKIE['cname']) & isset($_COOKIE['cpassreg']))
			{
				exit("	<br><br><br><div align=center><fieldset align=center style='width:300px;border:#333 1px solid;'>
					<legend align=center><b><font color=red>�� ����������������!</font></b></legend>
					<table align=center cellpadding=4 cellspacing=4 border=0 >
					<tr><td align=right><b>�����:</b></td><td>".$_COOKIE['cname']."</td></tr>
					<tr><td align=right><b>E-mail:</b></td><td>".$_COOKIE['cmail']."</td></tr></table></fieldset></div><br>
					<p align=center>[<a href=\"index.php?event=clearuser\" onclick=\"return confirm('�������� ������ ������������?')\">�������� ������ ������������</a>]
					<br><br><a href='index.php'>&#9668; �����</a></p></body></html>");
			}

			if (is_file("rules.html")) include"rules.html";

			print"	<br><br><form method=post name='Guest' onSubmit='regGuest(); return(false);'>
				<table align=center style='border: #333 1px solid;' cellpadding=4 cellspacing=4>
				<tr><td><input name=name placeholder='Name' size=40 type=text maxlength=$maxname title='��������� ������� � ��������� �����, ����� � ���� �������������'></td></tr>
				<tr><td><input name=mail placeholder='E-mail' maxlength=$maxmail size=40 type=text></td></tr>
				<tr><td><input name=passreg type=password size=40 maxlength=20 placeholder='Password'></td></tr>
				<tr><td><input type=radio name=pol style='width:15px;height:15px;' value='�������' checked> ������� <input type=radio name=pol style='width:15px;height:15px;' value='�������'> �������</td></tr>";

			if ($captchamin==1)
			{
				exit("<tr><td><table cellpadding=0 cellspacing=0 border=0><tr><td>
<script>function checkedBox(f){if(f.check1.checked) document.getElementById('other').innerHTML='<br><center><input type=submit class=fbutton style=\'width:150px\' value=\'������������������\'></center>';
else document.getElementById('other').innerHTML='<br><center><input type=submit class=fbutton style=\'width:150px\' value=\'������������������\' disabled=\'disabled\'></center>';}</script>
<input type=checkbox name=check1 onClick=\"checkedBox(this.form)\" style='width:20px;height:20px;' title='���� �� ���������� ������, �� �������� ������� ������� �����'></td><td> � �� ���</td></tr></table></td></tr>
<tr><td><div align=center></div><div id=other align=center><br><input type=submit class=fbutton style='width:150px' value='������������������' disabled='disabled'></div></td></tr></table></form>
<p align=center><a href=\"index.php?id=forum\">&#9668; �����</a></p>");

			} else {
				exit("<tr><td><img src=\"index.php?secpic\" id='secpic_img' border=1 align='top' title='��� ����� �������� �������� �� ���' onclick=\"document.getElementById('secpic_img').src='index.php?secpic&' + Math.random(); return false\"> &nbsp;<input type='text' name='secpic' id='secpic' style='width:60px;' title='������� $let_amount ������ ����. ������������ �� ��������' maxlength='10'><input type=hidden name=add value=''><br><br>
<center><input type=submit class=fbutton style='width:150px' value='������������������'></center></td></tr></table></form><p align=center><a href='index.php?id=forum'>&#9668; �����</a></p>");

			}
		}
		exit("</table></form><br><p align=center><a href='index.php'>&#9668; �����</a></p>");
	}


	if ($_GET['mode']=="admin")
	{
		if ($riadmin)
		{
			print $riadmin;
		} else {
			if (isset($_COOKIE['cadmin']) && isset($_COOKIE['cpass']))
			{
				print"	<br><br><br><div align=center>
					<fieldset align=center style='width:270px; border: #333 1px solid;'>
					<legend align=center><b><font color=red>�� ��� ����� ��� �����!</font></b></legend>
					<table align=center cellpadding=4 cellspacing=4 border=0 >
					<tr><td align=right><b>�����:</b></td><td>".$_COOKIE['cadmin']."</td></tr>
					<tr><td align=right><b>������:</b></td><td>������</td></tr></table>
					</fieldset></div>";
			} else {

				print"	<br><br><form method=post>
					<table align=center style='border:#333 1px solid;' cellpadding=3 cellspacing=5>
					<tr><td><input name='admin' placeholder='Login' size=35 type='text'></td></tr>
					<tr><td><input name='pass' placeholder='Password' size=35 type='text'></td></tr>
					<tr><td colspan=2 align=center><input class=fbutton type=submit style='width:100%' value='���������' onclick=\"window.location='index.php'\"></td></tr>";
			}
		}
		exit("</table></form><p align=center>[<a href=\"index.php?event=clearadmin\" onclick=\"return confirm('�������� ������ ������?')\">����� �� ������</a>] [<a href=\"index.php\">��������� �����</a>]</p></body></html>");
	}


} elseif (isset($_GET['action'])) {

	if ($_GET['action']=="newtopic")
	{
		if ($notopic==0)
		{
			if ($_['user'] && isset($_COOKIE['cname']) && isset($_COOKIE['cpassreg'])) 
			{
				print "<br><br><br><br>
<script>fetch('https://ipapi.co/json/').then(function(response){return response.json();}).then(function(data){code.value=data.country_code;country.value=data.country_name;city.value=data.city;ips.value=data.ip;latitude.value=data.latitude;longitude.value = data.longitude;});</script>
<script>function topicKey(){var ff=document.forms.item('Guest');ff.llen.value=".$maxtopic." - ff.topic.value.length;if(ff.topic.value.length>".$maxtopic.")ff.topic.value=ff.topic.value.substr(0,".$maxtopic.");}</script>
<form method=post name='Guest' onSubmit='regGuest(); return(false);'>
<table align=center style='border:1px solid #000;' cellpadding=3 cellspacing=5>
<tr><td><b>���:</b> <input type=hidden name=name value=\"".$cname."\">$cname &nbsp; &nbsp; <b>E-mail:</b> <input type=hidden name=mail value=\"".$cmail."\">$cmail</td></tr>
<tr><td><table cellpadding=0 cellspacing=0 border=0><tr><td><input name=topic size=80 placeholder='����' type=text onkeyup=topicKey();></td><td>&nbsp;<input name=llen style='width:40px' value='$maxtopic' title='�������� ������ ��������'></td></tr></table></td></tr>
<input type=hidden name='zvezdmax'>
<input type=hidden name='repamax'>
<input type=hidden name='stopuser'>
<input type=hidden name='onlyuser'>
<input type=hidden name='sur'>
<input type=hidden name='our'>
<tr><td>
<details style='cursor:hand;border:0px solid #669900'>
<summary style='text-align:center;'>�������������� ���������</summary><br>
<table cellpadding=1 cellspacing=1 border=0>
<tr>
	<td class=row1 width='142px' align=right>��������� �������������</td>
	<td><input type=text style='width:400px' size=4 name='stopuser' value='' title='���� ������������� (����� ������ ��� �������), ������� ��������� ����������� � ����'><br><input type=radio class=radio name='sur' value=0 checked>��������� �� ������ ����<input type=radio class=radio name='sur' value=1>��������� �� ������ ����</td>
</tr>
</table>
<br>
<table cellpadding=1 cellspacing=1 border=0>
<tr>
	<td class=row1 width='142px' align=right>������ ��� �������������</td>
	<td><input type=text style='width:400px' size=4 name='onlyuser' value='' title='���� ������������� (����� ������ ��� �������), ������� ����� ����������� � ����. �� �������� ������� ���� ���'><br><input type=radio class=radio name='our' value=0 checked>������ ����� ������ ����<input type=radio class=radio name='our' value=1>������ ������ ������ ����</td>
</tr>
</table>
<br>
<table cellpadding=1 cellspacing=1 border=0>
<tr>
	<td class=row1 align=right>����������� �� ���������</td>
	<td><input type=text style='width:40px' size=4 maxlength=4 name='repamax' value='0' title='��������� ������ ������� ������� ������ ��������� ������ ��������� ��� ����.\n������: 0 - ���� �������� ����, 12 - ���� �������� ���� ���� 12 ������ ���������'></td>
</tr>
<tr>
	<td class=row1 align=right>����������� �� ������</td>
	<td><input type=text style='width:40px' size=3 maxlength=1 name='zvezdmax' value='0' title='��������� ������ ������� ������� ���� (������ �����) ������ ��������� ��� ����.\n������: 0 - ���� �������� ����, 1 - ���� �������� ���� ���� 1 ������'></td>
</tr>
</table>
</details>
<br>
<table cellpadding=1 cellspacing=2 border=0 width=100%>
<tr>
<td width=270><input type=radio class=radio name=tt value=1 checked><img src='datan/1.png'> $topic1</td>
<td><input type=radio class=radio name=tt value=2><img src='datan/2.png'> $topic2</td>
</tr>
<tr>
<td><input type=radio class=radio name=tt value=3><img src='datan/3.png'> $topic3</td>
<td><input type=radio class=radio name=tt value=4><img src='datan/4.png'> $topic4</td>
</tr>
<tr>
<td><input type=radio class=radio name=tt value=5><img src='datan/5.png'> $topic5</td>
<td><input type=radio class=radio name=tt value=6><img src='datan/6.png'> $topic6</td>
</tr>
<tr>
<td><input type=radio class=radio name=tt value=7><img src='datan/7.png'> $topic7</td>
<td><input type=radio class=radio name=tt value=8><img src='datan/8.png'> $topic8</td>
</tr>
<tr>
<td><input type=radio class=radio name=tt value=9><img src='datan/9.png'> $topic9</td>
<td><input type=radio class=radio name=tt value=10><img src='datan/10.png'> $topic10</td>
</tr>
<tr>
<td><input type=radio class=radio name=tt value=11><img src='datan/11.png'> $topic11</td>
<td><input type=radio class=radio name=tt value=12><img src='datan/12.png'> $topic12</td>
</tr>
<tr>
<td><input type=radio class=radio name=tt value=13><img src='datan/13.png'> $topic13</td>
<td><input type=radio class=radio name=tt value=14><img src='datan/14.png'> $topic14</td>
</tr>
<tr>
<td><input type=radio class=radio name=tt value=15><img src='datan/15.png'> $topic15</td>
</tr>
</table>
<input type=hidden value=newtopic name=action>
<input type=hidden value=\"".$admin."\" name=admin>
<input type=hidden value=\"".$pass."\" name=pass>
<input type=hidden name='country' id='country'>
<input type=hidden name='city' id='city'>
<input type=hidden name='code' id='code'>
<input type=hidden name='ips' id='ips'>
<input type=hidden name='latitude' id='latitude'>
<input type=hidden name='longitude' id='longitude'>";

				if ($captchamin==1)
				{
					exit("<tr><td><table cellpadding=0 cellspacing=0 border=0><tr><td>
<script>function checkedBox(f){if(f.check1.checked) document.getElementById('other').innerHTML='<center><input type=submit class=fbutton value=\'���������\'></center>';
else document.getElementById('other').innerHTML='<center><input type=submit class=fbutton value=\'���������\' disabled=\'disabled\'></center>';}</script>
<input type=checkbox style='height:20px;width:20px;' name=check1 onClick=\"checkedBox(this.form)\"></td><td>&nbsp; � �� ���</td></tr></table></td></tr>
<tr><td><div id=other align=center><input type=submit class=fbutton value='���������' disabled='disabled'></div></td></tr></table></form>
<br><p align=center><a href=\"index.php?id=forum\">&#9668; �����</a></p>");

				} else {
					exit("<tr><td><img src=\"index.php?secpic\" id='secpic_img' style='border: 1px solid #000;' align='top' title='��� ����� �������� �������� �� ���' onclick=\"document.getElementById('secpic_img').src='index.php?secpic&' + Math.random(); return false\">&nbsp;<input type='text' name='secpic' id='secpic' style='width:60px;' title='������� $let_amount ������ ����. ������������ �� ��������' maxlength='10'> <small>������� <b>$let_amount</b> ������ �������</small></td></tr><tr><td><input type=hidden name=add value=''><center><input type=submit class=fbutton value='���������'></center>
</td></tr></table></form><br><p align=center><a href='index.php?id=forum'>&#9668; �����</a></p>");

				}
			} else {
				exit("<br><br><br><br><br><table align=center style='border:#333 1px solid' width='380px'><tr><th style='height:25px'><font color=red>������ ���������!</font></th></tr>
				<tr><td><p align=center><br><b>��� �������� ��� �� ������ ����������<br><br>[ <a href='index.php?mode=reg'>������������������</a> ]</b></p><br></tr></td></table>
				<br><p align=center>[<a href='javascript:history.back(1)'>��������� �����</a>]</p>");
			}

		} else {
			exit("<p align=center><font color=red><b>���������� ��� ���������!</b></font><br><br><a href='index.php?id=forum'>&#9668; �����</a></p>");
		}
	}

} else {

	print "<table cellpadding=2 cellspacing=1 align=center border=0 class=main><thead><th colspan=5><div align=right>";

	if (isset($_COOKIE['cadmin']) && isset($_COOKIE['cpass']) && $_COOKIE['cadmin']==$adminname && $_COOKIE['cpass']==$adminpass)
	{
		print " <a href='index.php?event=ban' style='color:red'>���</a> � <a href='admin.php?event=config' style='color:red'>���������</a> � <a href='admin.php?event=userwho' style='color:red'>�������</a> � ";
	}
	print "<a href='index.php?mode=admin'>�������</a> � <a href='index.php?mode=reg'>�����������</a> � <a href='index.php?event=who' title='��������� �����������������: $tdt[0]'>��������� ($ui)</a> � ";

	if ($_['user'] && isset($_COOKIE['cname']) && isset($_COOKIE['cpassreg']))
	{
		print "<a href=\"index.php?event=profile&pname=".$_['user']."\">��� �������</a></b> � <a href='index.php?action=newtopic&fid=$fid'>������� ����</a> "; //$codename
	} else {
		print "<a href='index.php?action=newtopic'>������� ����</a> ";
	}


	if ($_['user'] && isset($_COOKIE['cname']) && isset($_COOKIE['cpassreg']))
	{
		print"[<a href='index.php?event=clearuser' onclick=\"return confirm('�������� ������ ��������� ������?')\">����� - $cname</a>] ";

		$name=strtolower($cname);

		if (is_file("data-pm/$name.dat"))
		{
			$linespm=file("data-pm/$name.dat");
			$pmi=count($linespm);
			if ($pmi>0) print"[<a href=\"pm.php?readpm&id=$cname\"><font color=red>��: $pmi</font></a>]"; else print"[<a href=\"pm.php?readpm&id=$cname\">��: 0</a>]";
		} else {
			print"[<a href=\"pm.php?readpm&id=$cname\">��: 0</a>]";
		}			
	} else {
		print"[<a href='index.php?event=login'>����</a>]";
	}

 	print "</div></th><tr class=th><td>!</td><td>����</td><td>������ / �����.</td><td>�����</td><td>����������</td></tr></thead><tbody>";



	if (is_file("datan/topic.dat"))
	{
		$lines=file("datan/topic.dat");

		$i=count($lines);

		do {
			$i--;
			$dts=explode("�", $lines[$i]);
			if (is_file("data/$dts[2]")) $stime=filemtime("data/$dts[2]"); else $stime="";
			if ($dts[11]=="vip") {$stime=268;}
			$newlines[$i]="<!--$stime--> $dts[0]�$dts[1]�$dts[2]�$dts[3]�$dts[4]�$dts[5]�$dts[6]�$dts[7]�$dts[8]�$dts[9]�$dts[10]�$dts[11]�$dts[12]�$dts[13]�$dts[14]�$dts[15]�$dts[16]�$dts[17]�$dts[18]�$dts[19]�$dts[20]�$dts[21]�$dts[22]�$dts[23]�$dts[24]�";
		} while($i > 0);
		rsort($newlines);

		for($a=0; $a<count($lines); $a++)
		{
			//$dn=explode("�", $lines[$a]);
			$dn=explode("�", $newlines[$a]);

			if (isset($dn[2]))
			{
				$topicavtor = $dn[0];

				$ftime=filemtime("data/".$dn[2]);

				if (empty($dn[15])) $cnt=0; else $cnt=$dn[14];

				$pages=ceil($cnt/10);

				print "<tr><td align=center style='padding-left:2px;width:30px'>";

				if (isset($_COOKIE['cadmin']) && isset($_COOKIE['cpass']) && $_COOKIE['cadmin']==$adminname && $_COOKIE['cpass']==$adminpass)
				{
					$admbuttons="(<a href='index.php?mode=unlink&forumid=$dn[2]' style='color:red;font-size:11px;text-decoration:none' onclick=\"return confirm('������� ��� ����?')\" title='������� ����'>X</a>)(<a href='index.php?mode=closetopic&forumid=$dn[2]' style='color:red;font-size:11px;font-weight:normal;text-decoration:none' onclick=\"return confirm('������� ����?')\" title='������� ����'>�</a>|<a href='index.php?mode=opentopic&forumid=$dn[2]' style='color:red;font-size:11px;font-weight:normal;text-decoration:none' onclick=\"return confirm('������� ����?')\" title='������� ����'>O</a>)(<a href='index.php?mode=viptopic&forumid=$dn[2]' style='color:red;font-size:11px;text-decoration:none' onclick=\"return confirm('������� VIP-����')\" title='��� VIP'>+V</a>|<a href='index.php?mode=unviptopic&forumid=$dn[2]' style='color:red;font-size:11px;text-decoration:none' onclick=\"return confirm('�������� VIP-����')\" title='���� VIP'>V-</a>)";

				} else {
					$admbuttons="";
				}

				if ($dn[11]=="0") $titletopic="���� �������!";
				if ($dn[11]=="1") $titletopic=$topic1;
				if ($dn[11]=="2") $titletopic=$topic2;
				if ($dn[11]=="3") $titletopic=$topic3;
				if ($dn[11]=="4") $titletopic=$topic4;
				if ($dn[11]=="5") $titletopic=$topic5;
				if ($dn[11]=="6") $titletopic=$topic6;
				if ($dn[11]=="7") $titletopic=$topic7;
				if ($dn[11]=="8") $titletopic=$topic8;
				if ($dn[11]=="9") $titletopic=$topic9;
				if ($dn[11]=="10") $titletopic=$topic10;
				if ($dn[11]=="11") $titletopic=$topic11;
				if ($dn[11]=="12") $titletopic=$topic12;
				if ($dn[11]=="13") $titletopic=$topic13;
				if ($dn[11]=="14") $titletopic=$topic14;
				if ($dn[11]=="15") $titletopic=$topic15;
				if ($dn[11]=="vip") $titletopic="VIP - ����";

				print "<img align=absmiddle src='datan/$dn[11].png' title='$titletopic'></td><td><table border=0 width=100%><tr><td>";


				if ($dn[11]=="vip")
				{
					print "<a href='index.php?forumid=$dn[2]' style='color:red' class='topic'>".trim($dn[4])."</a>&nbsp;<font color=red><sup>VIP</sup></font> $admbuttons &nbsp;";
				} else {
					if ($dn[12]>0)
					{
						print "<a href='index.php?forumid=$dn[2]' class='topic'>".trim($dn[4])."</a>&nbsp;<font color=red><sup title='���� �������� ����������� $dn[12] ����� (����� �����)'>[$dn[12]]</sup></font> $admbuttons &nbsp;";
					} else {
						if ($dn[13]>0)
						{
							print "<a href='index.php?forumid=$dn[2]' class='topic'>".trim($dn[4])."</a>&nbsp;<font color=red><sup title='���� �������� ������������� � ���������� $dn[13] ������'>($dn[13])</sup></font> $admbuttons &nbsp;";
						} else {
							print "<a href='index.php?forumid=$dn[2]' class='topic'>".trim($dn[4])."</a> $admbuttons &nbsp;";
						}
					}
				}

				print"<a href=\"index.php?forumid=".$dn[2]."&page=".$pages."#last\" style='text-decoration:none' title='�������: $pages\n������� � ��������� ��������'>&#9658;</a><br>";

				if ($pages>1)
				{
					print "&nbsp;<span class=med> &nbsp; &nbsp; [���. ";
					if ($pages<=3) $f1=$pages; else $f1=3;
					for($i=1; $i<=$f1; $i++) {print "<a href='index.php?forumid=$dn[2]&page=$i'>$i</a>&nbsp;";}
					if ($pages>3) print "... <a href='index.php?forumid=$dn[2]&page=$pages'>$pages</a>";
					print "]</span>";
				}

				print "</td><td align='right'>";

				if (is_file("data/$dn[2].user")) print "<font color=red title='���� ��� ����������� �������������, ���� ����� ����������� �� ������'><svg aria-hidden='true' focusable='false' viewBox='0 0 16 16' height='16' width='16' fill='currentColor' display='inline-block' overflow='visible' style='vertical-align: text-bottom;'><path d='M7.467.133a1.748 1.748 0 0 1 1.066 0l5.25 1.68A1.75 1.75 0 0 1 15 3.48V7c0 1.566-.32 3.182-1.303 4.682-.983 1.498-2.585 2.813-5.032 3.855a1.697 1.697 0 0 1-1.33 0c-2.447-1.042-4.049-2.357-5.032-3.855C1.32 10.182 1 8.566 1 7V3.48a1.75 1.75 0 0 1 1.217-1.667Zm.61 1.429a.25.25 0 0 0-.153 0l-5.25 1.68a.25.25 0 0 0-.174.238V7c0 1.358.275 2.666 1.057 3.86.784 1.194 2.121 2.34 4.366 3.297a.196.196 0 0 0 .154 0c2.245-.956 3.582-2.104 4.366-3.298C13.225 9.666 13.5 8.36 13.5 7V3.48a.251.251 0 0 0-.174-.237l-5.25-1.68ZM8.75 4.75v3a.75.75 0 0 1-1.5 0v-3a.75.75 0 0 1 1.5 0ZM9 10.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0Z'></path></svg></font>";

				print "</td></tr></table></td><td align=center width='150px'><svg aria-hidden='true' focusable='false' viewBox='0 0 16 16' width='16' height='16' fill='currentColor' display='inline-block' overflow='visible' style='vertical-align: text-bottom;'><path d='M1 2.75C1 1.784 1.784 1 2.75 1h10.5c.966 0 1.75.784 1.75 1.75v7.5A1.75 1.75 0 0 1 13.25 12H9.06l-2.573 2.573A1.458 1.458 0 0 1 4 13.543V12H2.75A1.75 1.75 0 0 1 1 10.25Zm1.75-.25a.25.25 0 0 0-.25.25v7.5c0 .138.112.25.25.25h2a.75.75 0 0 1 .75.75v2.19l2.72-2.72a.749.749 0 0 1 .53-.22h4.5a.25.25 0 0 0 .25-.25v-7.5a.25.25 0 0 0-.25-.25Z'></path></svg> $cnt &nbsp;&nbsp;<svg aria-hidden='true' focusable='false' viewBox='0 0 16 16' width='16' height='16' fill='currentColor' display='inline-block' overflow='visible' style='vertical-align: text-bottom;'><path d='M8 2c1.981 0 3.671.992 4.933 2.078 1.27 1.091 2.187 2.345 2.637 3.023a1.62 1.62 0 0 1 0 1.798c-.45.678-1.367 1.932-2.637 3.023C11.67 13.008 9.981 14 8 14c-1.981 0-3.671-.992-4.933-2.078C1.797 10.83.88 9.576.43 8.898a1.62 1.62 0 0 1 0-1.798c.45-.677 1.367-1.931 2.637-3.022C4.33 2.992 6.019 2 8 2ZM1.679 7.932a.12.12 0 0 0 0 .136c.411.622 1.241 1.75 2.366 2.717C5.176 11.758 6.527 12.5 8 12.5c1.473 0 2.825-.742 3.955-1.715 1.124-.967 1.954-2.096 2.366-2.717a.12.12 0 0 0 0-.136c-.412-.621-1.242-1.75-2.366-2.717C10.824 4.242 9.473 3.5 8 3.5c-1.473 0-2.825.742-3.955 1.715-1.124.967-1.954 2.096-2.366 2.717ZM8 10a2 2 0 1 1-.001-3.999A2 2 0 0 1 8 10Z'></path></svg>&nbsp;";

				include "data/$dn[2].dat";

				print "</td><td align=right width='150px'>";

				if (isset($_COOKIE['cadmin']) && isset($_COOKIE['cpass']) && $_COOKIE['cadmin']==$adminname && $_COOKIE['cpass']==$adminpass)
				{
					print "<a href=\"mailto:$dn[1]\">$dn[0]</a>&nbsp;";
				} else {
					print "<b>$dn[0]</b>&nbsp;";
					print "<div style='display:inline-block;vertical-align:middle;' class=\"$dn[5]\" title=\"$dn[6], $dn[8]\"></div>";
				}

				print"<br><span class=small>$dn[3]</span>&nbsp;</td><td align=right width='150px'>";

				if (empty($dn[15]))
				{
					print "---&nbsp;";
				} else {
					$dn[18]=trim(replacer($dn[18]));
					$dn[18]=str_replace("&lt;br&gt;", "\r\n", $dn[18]);
					$dn[18]=str_replace(array("[code]","[quote]","[b]","[i]","[u]","[s]","[big]","[small]","[red]","[blue]","[green]","[orange]","[yellow]"), array("[���-]","[������-]","","","","","","","","","","",""), $dn[18]);
					$dn[18]=str_replace(array("[/code]","[/quote]","[/b]","[/i]","[/u]","[/s]","[/big]","[/small]","[/red]","[/blue]","[/green]","[/orange]","[/yellow]"), array("[-���]","[-������]","","","","","","","","","","",""), $dn[18]);
					$dn[18]=preg_replace("/\[hide\](.+?)\[\/hide\]/is", " [����� ����� �� ������] ", $dn[18]);
					$dn[18]=preg_replace("/\[hide=(.+?)\](.+?)\[\/hide\]/is", " [����� ��� \\1] ", $dn[18]);

					print "<span style='display:none;'>$ftime</span>";
					print "<a href=\"index.php?forumid=".$dn[2]."&page=".$pages."#last\" title=\"$dn[18]\">$dn[15]</a>&nbsp;";
					print "<div style='display: inline-block; vertical-align: middle;' class=\"$dn[19]\" title=\"$dn[20], $dn[22]\"></div>";
				}
				print "<br>";
				if (empty($dn[15])) print "---&nbsp;"; else print "<span class=small>$dn[17]</span>&nbsp;";
				print "</td></tr>";
			}
		}
	}




	print "</tbody></table>";
}

////////////////// ����� ��������� ������� (�����)
$time_gen = microtime();
$time_gen = explode(' ', $time_gen);
$end_gen = $time_gen[1] + $time_gen[0];
$total_time = round(($end_gen - $start_gen), 4);

include "$fskin/bottom.html";

?>