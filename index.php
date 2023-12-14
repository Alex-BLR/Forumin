<?php
/***********************************************************
 *  Forumin v1.2 [31-05-2023]
 *  Alexand3r ~ http://vox.dx.am ~ alexand3r2@mail.ru ~ youtube.com/@alexblr
 *  Движок от EI форум Copyright (c) 2004 Эдюха 
 *
 ***********************************************************/

//error_reporting (E_ALL);
error_reporting(0);

session_start();

include "config.php";

$knopki="0"; //кнопки ЛС и ПП (под аватаром) выключены

$valid_types_load=array("z", "zip", "rar", "7z", "jpg", "jpeg", "gif", "png"); //Расширения загружаемых файлов

$valid_types=array("gif", "jpg", "png", "jpeg"); //Расширения загружаемых аватаров

$maxfsize=round($max_file_size/10.24)/100; //Допустимый вес аватара Кб


$hst=$_SERVER["HTTP_HOST"];
$self=$_SERVER["PHP_SELF"];
$furl=str_replace('index.php', '', "http://$hst$self");


////////////////// Время генерации скрипта (начало)
$time_gen = microtime();
$time_gen = explode(' ', $time_gen);
$start_gen = $time_gen[1] + $time_gen[0];


////////////////// Генерация thumbnails
//$src - исходный файл
//$dest - генерируемый файл
//$width, $height - ширина и высота генерируемого изображения, пикселей
//$size - текущие размеры
//$quality - качество JPEG

function img_resize($src, $dest, $width, $height, $size, $name, $quality=92)
{
	if (!file_exists($src)) return false;
	if ($size==false) return false;

	//Определяем исходный формат по MIME-информации функцией getimagesize и выбираем соответствующую формату imagecreatefrom-функцию
	$format=substr(strstr($size['mime'], '/'), 1);
	$icfunc="imagecreatefrom".$format;

	if (!function_exists($icfunc)) return false;

	//Увеличение лимита памяти для фоток больше 3000х2000
	if ($size[0]>3000 || $size[1]>2000) {ini_set("memory_limit", "128M");}

	$isrc=$icfunc($src);
	$idest=imagecreatetruecolor($width, $height);

	imagecopyresampled($idest, $isrc, 0, 0, 0, 0, $width, $height, $size[0], $size[1]);

	if($width>260) // выводим надпись
	{
		function _Kiril_latin ($path)
		{
			return strtr($path,array("а"=>"a", "б"=>"b", "в"=>"v", "г"=>"g", "д"=>"d", "е"=>"e", "ё"=>"jo", "ж"=>"zh", "з"=>"z", "и"=>"i", "й"=>"j", "к"=>"k", "л"=>"l", "м"=>"m", "н"=>"n", "о"=>"o", "п"=>"p", "р"=>"r", "с"=>"s", "т"=>"t", "у"=>"u", "ф"=>"f", "х"=>"x", "ц"=>"c", "ч"=>"ch", "ш"=>"sh", "щ"=>"shh", "ъ"=>"''", "ы"=>"y", "ь"=>"'", "э"=>"je", "ю"=>"ju", "я"=>"ya", "йо"=>"j/o", "йе"=>"j/e", "А"=>"A", "Б"=>"B", "В"=>"V", "Г"=>"G", "Д"=>"D", "Е"=>"E", "Ё"=>"JO", "Ж"=>"ZH", "З"=>"Z", "И"=>"I", "Й"=>"J", "К"=>"K", "Л"=>"L", "М"=>"M", "Н"=>"N", "О"=>"O", "П"=>"P", "Р"=>"R", "С"=>"S", "Т"=>"T", "У"=>"U", "Ф"=>"F", "Х"=>"X", "Ц"=>"C", "Ч"=>"CH", "Ш"=>"SH", "Щ"=>"SHH", "Ъ"=>"''", "Ы"=>"Y", "Ь"=>"'", "Э"=>"JE", "Ю"=>"JU", "Я"=>"YA", "ЙО"=>"J/O", "ЙЕ"=>"J/E"));
		}

		$copyrite=_Kiril_latin($name);
		$host=$_SERVER["HTTP_HOST"];
		//$host=_Kiril_latin($host);

		$textcolor=imagecolorallocate($idest, 255, 255, 255); //Цвет текста
		$backcolor=imagecolorallocate($idest, 0, 0, 0); //Цвет каймы текста

		$texthx=$width-strlen($host)*7.25; //X горизонтального текста
		$texthy=$height-15; //Y горизонтального текста
		$textvx=$width-16; //X вертикального текста
		$textvy=$height-20; //Y вертикального текста

		//кайма текст горизонтально
		imagestring($idest, 3, $texthx-1, $texthy, $host, $backcolor);
		imagestring($idest, 3, $texthx+1, $texthy, $host, $backcolor);
		imagestring($idest, 3, $texthx, $texthy-1, $host, $backcolor);
		imagestring($idest, 3, $texthx, $texthy+1, $host, $backcolor);
		imagestring($idest, 3, $texthx, $texthy, $host, $textcolor);

		//кайма текст вертикально
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

	if ($size=@getimagesize($bigimgsrc))
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


////////////////// Вставляем картинки
function replace_img_link($imlink)
{
	if (ini_get('allow_url_fopen') && ($size = @getimagesize($imlink)) !== FALSE)
	{
		if ($size[0] <= 260)
		{
			$imgtag="<img src=\"$imlink\" border=\"0\"> ";
		} else {
			$imgtag="<a href=\"$imlink\" target=\"_blank\"><img src=\"index.php?mode=link&img=$imlink\" border=\"0\" style=\"border: 1px outset #DCDCDC;\"></a>";
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
				$replace[$a]="<span class=small>[<font color=red>Ошибка ".$matches[$a][1]."</font>]</span>";
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



////////////////// Очистка кода
function replacer($text) {
	//$text=stripslashes($text);
	//$text=str_replace("¦", "", $text);
	$text=str_replace("&#032;", " ", $text);
	$text=str_replace(">", "&gt;", $text);
	$text=str_replace("<", "&lt;", $text);
	$text=str_replace("\"", "&quot;", $text);
	$text=str_replace('\\\\', '\\', $text);
	$text=preg_replace("/\\\$/", "&#036;", $text);
	$text=preg_replace("/\\\/", "&#092;", $text);
	if (get_magic_quotes_gpc()) {
		$text=str_replace("&#092;&quot;", '&quot;', $text);
		$text=str_replace("&#092;'", '\'', $text);
		$text=str_replace("&#092;&#092;", '&#092;', $text);
	}
	$text=str_replace("  ", " ", $text);
 	$text=str_replace("\r\n", "<br>", $text);
	$text=str_replace("\n", "<br>", $text);
	$text=str_replace("\t", "", $text);
	$text=str_replace("\r", "", $text);
	return $text;
}



////////////////// Функция кнопки Hide
function hideguest($hide)
{
	global $user;
	if ($user)
	{
		$hide="<br><br><fieldset style='width:95%;border:dotted 1px #777777;'><legend align=left class=med>Текст скрыт от гостей</legend>$hide</fieldset><br>";
		return $hide;
	} else {
		$hide="<br><br><fieldset style='width:95%;border:dotted 1px #777777;'><legend align=left class=med>Скрыто от гостей</legend><i>Только зарегистрированные пользователи могут видеть этот текст!</i></div></fieldset><br>";
		return $hide;
	}
}

////////////////// Функция кнопки Hide для пользователей
function hideuser($hidename, $hidetext)
{
	global $user, $name;
	if ($_COOKIE['cname']==$hidename && $user || $user===$name) //$_COOKIE['cadmin']==$adminname & $_COOKIE['cpass']==$adminpass && strstr($puuu[13], 'администратор'))
	{
		$hidename=" <span style='background-color:#555;font-style:italic;color:#ddd'>&nbsp;Лично для <b>$hidename</b>: $hidetext</span> ";
		return $hidename;
	} else {
		$hidename=" <span style='background-color:#555;font-style:italic;color:#ddd'>Только <b>$hidename</b> и <b>Админ</b> видят этот текст!</span> ";
		return $hidename;
	}
}


////////////////// Функция проверки пользователя
function is_user() {
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

// Системный массив, используем как буфер
$_ = array();

// Обнуляем пользователя
$_['user'] = 0;

// После проверки кук проверяем на пользователя чтобы лишний раз не вызывать функции
$_['user'] = $user = is_user();


////////////////// <br> в \n
function br2n($text)
{
	$text=str_replace("<br>", "\n", $text);
	$text=str_replace("<br />", "\n", $text);
	return $text;
}


////////////////// \n в <br>
function n2br($text)
{
	$text=str_replace("\r", "", $text);
	$text=str_replace("\n", "<br>", $text);
	return $text;
}


/////////////// Функция для отображения аватаров
function get_dir($path = './', $mask = '*.php', $mode = GLOB_NOSORT)
{
	if (version_compare(phpversion(), '4.3.0', '>='))	{
		if (chdir($path)) {$temp=glob($mask,$mode); return $temp;}
	} return false;
}


////////////////// Счетчик посещений
$num = 6;
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



////////////////// Автолинкование ссылок
function autolink($str, $attributes=array()) {
	$attrs = '';
	foreach ($attributes as $attribute => $value) {$attrs .= " {$attribute}=\"{$value}\"";}
	$str = ' ' . $str;
	$str = preg_replace('`([^"=\'>])((http|https|ftp)://[^\s<]+[^\s<\.)])`i', '$1<a href="$2"'.$attrs.' target="_new">$2</a>', $str);
	$str = substr($str, 1);
	return $str;
}


////////////////// Антимат 1
function removeBadWords($text) {
	global $badwords, $cons;
	$mat=count($badwords);
	for ($i=0; $i<$mat; $i++)
	$text=preg_replace("/".$badwords[$i]."/si", $cons, $text);
	return $text;
}

////////////////// Антимат 2
function removeBadWordss($text) {
	global $cons;
	$pattern = ('/(
		(?:\s+|^)(?:[пПnрРp]?[3ЗзВBвПnпрРpPАaAаОoO0о]?[сСcCиИuUОoO0оАaAаыЫуУyтТT]?|\w*[оаАaAО0oO])[Ппn][иИuUeEеЕ][зЗ3][ДдDd]\w*[\?\,\.\!\;\-]*|
		(?:\s+|^)\w{0,4}[оОoO0иИuUаАaAcCсСзЗ3тТTуУy]?[XxХх][уУy][йЙеЕeёЁEeяЯ9юЮиИuU]\w*[\?\,\.\;\-\!]*|
		(?:\s+|^)[бпПnБ6][лЛ][яЯ9]+(?:[дтДТDT]\w*)?[\?\,\.\;\!\-]*|
		(?:\s+|^)\w*[бпПnБ6][лЛ][яЯ9][дтДТDT]\w+[\?\,\.\;\-\!]*|
		(?:\s+|^)(?:\w*[оОoO0ъЪьыЫЬаАaAзЗ3уУyеЕe])?[еЕeEиИuUёЁ][бБ6пП](?:[оОoO0ыЫаАaAнНHиИuUуУyлЛеЕeкКkKE]\w*)?[\?\,\!\.\;\-]*|
		(?:\s*|^)?[ШшЩщ][лЛ][юЮ][хХxX]?[шШщЩ]?[кКkK]?\w*[\?\,\!\.\;\-]*|
		(?:\s*|^)?[сСcC][цЦ]?[уyУ]+[чЧ]?[КkKк]*\w*[\?\,\!\.\;\-]*|
		(?:\s*|^)?[пПn][uUИи][Дд][aAАаоОoO0][Рpр]\w*[\?\,\!\.\;\-]*|
		(?:\s*|^)?[гГ][ОoOоаАaA][НHн][Дд][oOО0о][нНH]\w*[\?\,\!\.\;\-]*|
		(?:\s*|^)?\w*[3Зз][аАaAоОoO0][лK][уyУ][пПn]\w*[\?\,\!\.\;\-]*)/x');
	$text = preg_replace("$pattern", "$cons", $text);
	return $text;
}

////////////////// Капча сложная
if (isset($_REQUEST['add'])) {
	if (strtolower($_REQUEST['secpic']) !=$_SESSION['secpic']) {
		@header("Content-type: text/html; charset=windows-1251");
		echo "<html><head><meta http-equiv='Content-Type' content='text/html; charset=windows-1251'></head><body><center><br><br><br><br><font face=tahoma size=2><b>Неверно введен защитный код!</b><br><br><a href=\"javascript:history.back()\">&#9668; назад</a></font></center>"; exit();
	}
}

////////////////// Капча сложная
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


////////////////// Определение IP вариант 1 (отключен) 
/*
function getIpAddress() {
	$check = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR');
	$ip = '0.0.0.0';
	foreach ($check as $akey) {
		if (isset($_SERVER[$akey])) {list($ip) = explode(',', $_SERVER[$akey]); break;}
	}
	return $ip;
}
$ip = getIpAddress();
*/

////////////////// Определение IP вариант 2
function getUserIP() {
	$ip = $_SERVER['REMOTE_ADDR'];
	if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)) {
		$ip = array_pop(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']));
	}
	return $ip;
}
$ip = getUserIP();


////////////////// Бан по IP
if ($antiham==1) {
	$_b=0;
	$e=explode(' ', file_get_contents("datan/badip.dat"));
	foreach($e as $v)
	if (@strstr($ip, $v)) exit("<div align=center><br><br><br><br><br><fieldset style='width:400px;border: #333 1px solid;'><legend align='center'><font size=2 face=tahoma color=red><b>Вы заблокированы!</b></font></legend><br><center><font size=2 face=tahoma><b>Администратор запретил вам пользоваться форумом!</b></font></center><br></fieldset></div>");
}

////////////////// Бан по IP
if (is_file("datan/banip.dat")) {
	$lines=file("datan/banip.dat");
	$i=count($lines);
	if ($i>0) {
		do {
			$i--;
			$idt=explode("|", $lines[$i]);
			if ($idt[0]===$ip || $idt[1]===$user) exit("<div align=center><br><br><br><br><br><fieldset style='width:400px;border: #333 1px solid;'><legend align='center'><font size=2 face=tahoma color=red><b>Бан по причине</b></font></legend><br><center><font size=2 face=tahoma><b>$idt[2]</b></font></center><br></fieldset></div>");
		}
		while($i>"0");
	}
	unset($lines);
}



////////////// Просмотры (клики)
if (isset($_GET['forumid']))
{
	$fc=file("datan/topic.dat");
	for ($i=0; $i<sizeof($fc); $i++)
	{
		$dtc=explode('¦',$fc[$i]);
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




////////////////// Юзер - Выход - очищаем куки
if (isset($_GET['event'])) {
	if ($_GET['event']=="clearuser") {
		@setcookie("cname","",time(),"/");
		@setcookie("cmail","",time(),"/");
		@setcookie("cpassreg","",time(),"/");
		//@setcookie("wrfcookies","",time(),"/");
		@header("Location: index.php");
		exit;
	}
}


////////////////// Админ - Выход - очищаем куки
if (isset($_GET['event'])) {
	if ($_GET['event']=="clearadmin") {
		@setcookie("cadmin","",time(),"/");
		@setcookie("cpass","",time(),"/");
		@header("Location: index.php");
	}
}


////////////////// Часовая поправка
$timezone=floor($timezone);
if ($timezone<-12 || $timezone>12) $timezone = 0;



////////////////// Регистрация
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
				<div align=center><br><br><br><br><br><fieldset style='width:400px; border: #333 1px solid;'><legend align='center'>
				<font size=2 face=tahoma color=red><b>ОШИБКА</b></font></legend>
				<br><center><font size=2 face=tahoma><b>Ваше имя содержит недопустимые символы!<br>Разрешены: русские и англ. буквы и цифры.</b></font></center>
				<br></fieldset></div><br><p align=center><a href='index.php?mode=reg' style='text-decoration:none;'>&#9668; назад</a></p>"); 

		if ($name=="" or strlen($name)>$maxname)
			exit("
				<div align=center><br><br><br><br><br><fieldset style='width:400px; border: #333 1px solid;'><legend align='center'><font size=2 face=tahoma color=red><b>ОШИБКА</b></font></legend>
				<br><center><font size=2 face=tahoma><b>Ваше имя пустое или превышает $maxname символов!</b></font></center>
				<br></fieldset></div><br><p align=center><a href='index.php?mode=reg' style='text-decoration:none;'>&#9668; назад</a></p>");

		if ($passreg=="" or strlen($passreg)<3 or strlen($passreg)>10)
			exit("
				<div align=center><br><br><br><br><br><fieldset style='width:400px; border: #333 1px solid;'><legend align='center'><font size=2 face=tahoma color=red><b>ОШИБКА</b></font></legend>
				<br><center><font size=2 face=tahoma><b>Пароль не должен быть пустым и коротким!<br>Допускается длина пароля от 3 до 10 симв.</b></font></center>
				<br></fieldset></div><br><p align=center><a href='index.php?mode=reg' style='text-decoration:none;'>&#9668; назад</a></p>");

		if (!preg_match("/^[a-z0-9\.\-_]+@[a-z0-9\-_]+\.([a-z0-9\-_]+\.)*?[a-z]+$/is", $mail) or $mail=="" or strlen($mail)>$maxmail)
			exit("
				<div align=center><br><br><br><br><br><fieldset style='width:400px; border: #333 1px solid;'><legend align='center'><font size=2 face=tahoma color=red><b>ОШИБКА</b></font></legend>
				<br><center><font size=2 face=tahoma><b>Введенный Email адрес некорректный,<br>либо превышает $maxmail символов!</b></font></center>
				<br></fieldset></div><br><p align=center><a href='index.php?mode=reg' style='text-decoration:none;'>&#9668; назад</a></p>");

		if (isset($_POST['pol'])) $pol=$_POST['pol']; else $pol="";

		if ($pol!="мужчина") $pol="женщина";

		/////////////// КОД активации
		$z=1;
		do {
			$userkey=mt_rand(1000000,9999999);
			if (strlen($userkey)==7) $z++;
		} while ($z<1);

		$userstatus=replacer($userstatus);

		/////////////// Ищем юзера с таким логином или емайлом
		$loginsm=strtolower($name);
		$lines=file("datan/usersdat.php");
		$i=count($lines);
		if ($i>"1") {
			do {
				$i--;
				$rdt=explode("|",$lines[$i]); 
				$rdt[0]=strtolower($rdt[0]);
				if ($rdt[0]===$loginsm) {$bad="1"; $er="именем";}
				if ($rdt[3]===$mail) {$bad="1"; $er="емайлом";}
			} while($i > 1);

			if (isset($bad))
				exit("
					<div align=center><br><br><br><br><br>
					<fieldset style='width:350px; border: #333 1px solid;'><legend align='center'><font size=2 face=tahoma color=red><b>ОШИБКА</b></legend>
					<br><center><b>Участник с таким $er уже зарегистрирован!</b></font></center><br>
					</fieldset></div><br><br><p align=center><a href='index.php?mode=reg' style='text-decoration:none;'>&#9668; назад</a></p>");
		}

		$text="$name|$passreg|0|$mail|$datee||$pol||||||noavatar.gif|$userstatus||";
		$text=replacer($text);

		setcookie("cname",$name,(time()+300000000),"/");
		setcookie("cmail",$mail,(time()+300000000),"/");
		setcookie("cpassreg",md5($passreg),(time()+300000000),"/");

		/////////////// Записываем файл с юзерами
		$fp=fopen("datan/usersdat.php","a+");
		flock($fp,LOCK_EX);
		fputs($fp,"$text\r\n");
		fflush($fp);
		flock($fp,LOCK_UN);
		fclose($fp);

		/////////////// Записываем строчку с именем в файл со статистикой
		$fp=fopen("datan/userstat.dat","a+");
		flock($fp,LOCK_EX);
		fputs($fp,"$name|0|0|0|0|||||\r\n");
		fflush($fp);
		flock($fp,LOCK_UN);
		fclose($fp);

		$riuser="<meta http-equiv='pragma' content='no-cache'><br><br><br><div align=center><fieldset align=center style='width:300px;border:#333 1px solid;'>
			<legend align=center style='border:#333 1px solid;background-color:#999;color:green;padding:2px 2px;'><b>Регистрация прошла успешно!</b></legend>
			<table align=center cellpadding=4 cellspacing=4 border=0><tr><td align=right><b>Логин:</b></td><td>$name</td></tr>
			<tr><td align=right><b>Пароль:</b></td><td>$passreg</td></tr><tr><td align=right><b>E-mail:</b></td><td>$mail</td></tr>
			</table></fieldset></div>";
	}
}



////////////////// Админка
if (isset($_GET['mode'])) {
	if ($_GET['mode']=="admin") {
		if (isset($_POST['admin']) && isset($_POST['pass'])) {
			$admin=$_POST['admin'];
			$pass=$_POST['pass'];

			if ($admin==$adminname && md5("$pass")==$adminpass) {
				@setcookie("cadmin",$admin,(time()+30000000),"/");
				@setcookie("cpass",md5($pass),(time()+30000000),"/");

				$riadmin="
					<meta http-equiv='pragma' content='no-cache'><br><br><br>
					<div align=center>	<fieldset align=center style='width:300px; border: #333 1px solid;'>
					<legend align=center><b><font color=red>Вы в режиме администратора!</font></b></legend>
					<table align=center cellpadding=4 cellspacing=4 border=0>
					<tr><td align=right><b>Логин:</b></td><td>$admin</td></tr>
					<tr><td align=right><b>Пароль:</b></td><td>$pass</td></tr>
					</table></fieldset></div>";
			}
		}
	}
}


/////////////// Вход на форум, проверка имени/пароля
if (isset($_GET['event']))
{
	if ($_GET['event']=="regenter")
	{
		if (!isset($_POST['name']) & !isset($_POST['passreg'])) exit("<br><br><br><center><font size=2 face=tahoma><b>Введите имя и пароль!</b><br><p align=center>[<a href=\"index.php?event=login\">вернуться назад</a>]</p>");

		$name=str_replace("|", '', $_POST['name']);
		$pass=str_replace("|", '', $_POST['passreg']);
		$text=trim(replacer("$name|$pass|"));

		if (strlen($text)<3) 	exit("<br><br><br><center><font size=2 face=tahoma><b>Вы не ввели имя или пароль!</b><br><br><br><p align=center>[<a href=\"index.php?event=login\">вернуться назад</a>]</p>");

		$exd=explode("|",$text);
		//$name=strtolower($exd[0]);
		$name=$exd[0];
		$pass=$exd[1];

		// проходим по всем пользователям и сверяем данные
		$lines=file("datan/usersdat.php");
		$i=count($lines);
		do {
			$i--;
			$rdt=explode("|",$lines[$i]);
			if (isset($rdt[1])) // Если строчка НЕ ПУСТА
			{
				if ($name===$rdt[0] && $pass===$rdt[1])
				{
					$regenter="$i";
					$cmail = $rdt[3];
					setcookie("cname", $name, (time()+300000000),"/");
					setcookie("cmail", $cmail, (time()+300000000),"/");
					setcookie("cpassreg", md5($pass), (time()+300000000),"/");

					$tektime=time();

					//$wrfcookies="$name|".md5($pass)."|$cmail|$tektime|";
					//setcookie("wrfcookies", $wrfcookies, (time()+300000000),"/");
				}
			}
		} while($i > "1");

		if (!isset($regenter)) exit("<br><br><br><br><center><font size=2 face=tahoma><B>Ваши данные не верны!</B><br><br><br><p align=center>[<a href=\"index.php?event=login\">вернуться назад</a>]</p>");

		header("Location: index.php");
	}
}



/////////////// РЕДАКТИРОВАНИЕ ПРОФИЛЯ - сохранение данных
if (isset($_GET['event']))
{
	if ($_GET['event']=="reregist")
	{
		if (!isset($_POST['name']))
			exit("
				<div align=center><br><br><br><br><br><fieldset style='width:350px; border: #333 1px solid;'><legend align='center'><font size=2 face=tahoma color=red><b>ОШИБКА</b></font></legend>
				<br><center><font size=2 face=tahoma><b>Вы не ввели свое имя!</b></font></center>
				<br></fieldset></div><br><br><p align=center><a href='javascript:history.back(1)' style='text-decoration:none;'>&#9668; назад</a></p>");

		$name=trim(str_replace("|", '', $_POST['name']));

		if ($name=="" or strlen($name)>$maxname)
			exit("
				<div align=center><br><br><br><br><br><fieldset style='width:350px; border: #333 1px solid;'><legend align='center'><font size=2 face=tahoma color=red><b>ОШИБКА</b></font></legend>
				<br><center><font size=2 face=tahoma><b>Ваше имя пустое или превышает $maxname символов!</b></font></center>
				<br></fieldset></div><br><br><p align=center><a href='javascript:history.back(1)' style='text-decoration:none;'>&#9668; назад</a></p>");

		if (preg_match("/[^(\\w)|(\\x7F-\\xFF)|(\\-)]/", $name))
			exit("
				<div align=center><br><br><br><br><br><fieldset style='width:350px; border: #333 1px solid;'><legend align='center'><font size=2 face=tahoma color=red><b>ОШИБКА</b></font></legend>
				<br><center><font size=2 face=tahoma><b>Ваше имя содержит недопустимые символы!<br>Разрешены: русские и англ. буквы и цифры.</b></font></center>
				<br></fieldset></div><br><br><p align=center><a href='javascript:history.back(1)' style='text-decoration:none;'>&#9668; назад</a></p>");


		if (!isset($_POST['pass']))
			exit("
				<div align=center><br><br><br><br><br><fieldset style='width:350px; border: #333 1px solid;'><legend align='center'><font size=2 face=tahoma color=red><b>ОШИБКА</b></font></legend>
				<br><center><font size=2 face=tahoma><b>Пароль не должен быть пустым и коротким!<br>Допускается длина пароля от 3 до 10 симв.</b></font></center>
				<br></fieldset></div><br><br><p align=center><a href='javascript:history.back(1)' style='text-decoration:none;'>&#9668; назад</a></p>");

		$pass=replacer(str_replace("|", '', $_POST['pass']));
		$oldpass=$_POST['oldpass'];

		if (strlen($pass)<3 or strlen($pass)>10)
			exit("
				<div align=center><br><br><br><br><br><fieldset style='width:350px; border: #333 1px solid;'><legend align='center'><font size=2 face=tahoma color=red><b>ОШИБКА</b></font></legend>
				<br><center><font size=2 face=tahoma><b>Пароль не должен быть пустым и коротким!<br>Допускается длина пароля от 3 до 10 симв.</b></font></center>
				<br></fieldset></div><br><br><p align=center><a href='javascript:history.back(1)' style='text-decoration:none;'>&#9668; назад</a></p>");

		if (isset($_POST['email'])) $email=strtolower($_POST['email']); else $email="";

		if (!preg_match("/^[a-z0-9\.\-_]+@[a-z0-9\-_]+\.([a-z0-9\-_]+\.)*?[a-z]+$/is", $email) or $email=="" or strlen($email)>$maxmail)
			exit("
				<div align=center><br><br><br><br><br><fieldset style='width:350px; border: #333 1px solid;'><legend align='center'><font size=2 face=tahoma color=red><b>ОШИБКА</b></font></legend>
				<br><center><font size=2 face=tahoma><b>Введенный Email адрес некорректный,<br>либо превышает $maxmail символов!</b>	</font></center>
				<br></fieldset></div><br><br><p align=center><a href='javascript:history.back(1)' style='text-decoration:none;'>&#9668; назад</a></p>");

		if (isset($_POST['dayx'])) $dayx=$_POST['dayx']; else $dayx="";
		if (isset($_POST['pol'])) $pol=$_POST['pol']; else $pol="";
		if ($pol!="мужчина") $pol="женщина";
		if (isset($_POST['icq'])) $icq=$_POST['icq']; else $icq="";
		if (isset($_POST['www'])) $www=$_POST['www']; else $www="";
		if (isset($_POST['about'])) $about=$_POST['about']; else $about="";
		if (isset($_POST['work'])) $work=$_POST['work']; else $work="";
		if (isset($_POST['write'])) $write=$_POST['write']; else $write="";
		if (isset($_POST['avatar'])) $avatar=$_POST['avatar']; else $avatar="";
		if (isset($_POST['cflag'])) $cflag=$_POST['cflag']; else $cflag="";

		$notgood="<br><br><br><center><font size=2 face=tahoma><b>Введено слишком много данных поля ";

		if (strlen($dayx)>10) {$notgood.="<font color=red>день рождения</font></b><br><p align=center><a href='javascript:history.back(1)'>&#9668; назад</a></p>"; exit("$notgood");}
		if (strlen($icq)>12) {$notgood.="<font color=red>ICQ</font></b><br><p align=center><a href='javascript:history.back(1)'>&#9668; назад</a></p>"; exit("$notgood");}
		if (strlen($www)>70) {$notgood.="<font color=red>сайт</font></b><br><p align=center><a href='javascript:history.back(1)'>&#9668; назад</a></p>"; exit("$notgood");}
		if (strlen($about)>70) {$notgood.="<font color=red>откуда</font></b><br><p align=center><a href='javascript:history.back(1)'>&#9668; назад</a></p>"; exit("$notgood");}
		if (strlen($work)>70) {$notgood.="<font color=red>интересы</font></b><br><p align=center><a href='javascript:history.back(1)'>&#9668; назад</a></p>"; exit("$notgood");}
		if (strlen($write)>70) {$notgood.="<font color=red>подпись</font></b><br><p align=center><a href='javascript:history.back(1)'>&#9668; назад</a></p>"; exit("$notgood");}

		if ($antimatt==1) $dayx=removeBadWordss($dayx);
		if ($antimatt==1) $icq=removeBadWordss($icq);
		if ($antimatt==1) $www=removeBadWordss($www);
		if ($antimatt==1) $about=removeBadWordss($about);
		if ($antimatt==1) $work=removeBadWordss($work);
		if ($antimatt==1) $write=removeBadWordss($write);

		$email=str_replace("|","",$email);
		$dayx=str_replace("|","",$dayx);
		$icq=str_replace("|","",$icq);
		$www=str_replace("|","",$www);
		$about=str_replace("|","",$about);
		$work=str_replace("|","",$work);
		$write=str_replace("|","",$write);
		$avatar=str_replace("|","",$avatar);
		$cflag=str_replace("|","",$cflag);

		// проверка Логина/Старого пароля
		$ok=null;
		$lines=file("datan/usersdat.php");
		$i=count($lines);
		unset($ok);
		do {
			$i--;
			$rdt=explode("|", $lines[$i]);

			if (strtolower($name)===strtolower($rdt[0]) & $oldpass===$rdt[1]) $ok="$i"; // Ищем юзера логин/пароль

			else {
				if ($email===$rdt[3]) $bademail="1"; // Вдруг у когото уже есть такой емайл?
			}
		} while($i > "1");

		if (isset($bademail)) exit("
				<div align=center><br><br><br><br><br><fieldset style='width:350px; border: #333 1px solid;'><legend align='center'><font size=2 face=tahoma color=red><b>ОШИБКА</b></font></legend>
				<br><center><font size=2 face=tahoma><b>Есть совпадение! Пользователь с емейлом<br><font color=red>$email</font><br>уже зарегистрирован на форуме!</b>
				</font></center><br></fieldset></div><br><br><p align=center><a href='javascript:history.back(1)' style='text-decoration:none;'>&#9668; назад</a></p>");

		if (!isset($ok))
		{
			setcookie("cname","",time(),"/");
			setcookie("cmail","",time(),"/");
			setcookie("cpassreg","",time(),"/");

			exit("
				<div align=center><br><br><br><br><br><fieldset style='width:400px;border: #333 1px solid;'><legend align='center'><font size=2 face=tahoma color=red><b>ОШИБКА</b></font></legend>
				<br><center><font size=2 face=tahoma><b>Новый логин-пароль-емайл не совпадает ни с одним из БД<br>Смена электронного адреса запрещена!</b>
				</font></center><br></fieldset></div><br><br><p align=center><a href='javascript:history.back(1)' style='text-decoration:none;'>&#9668; назад</a></p>");
		}

		$udt=explode("|",$lines[$ok]);
		$dayreg=$udt[4];
		$kolvomsg=$udt[2];
		$status=$udt[13];

		// блок загрузки АВАТАРА
		if ($_FILES['file']['name']!="")
		{
			$fotoname=$_FILES['file']['name']; // определяем имя файла
			$avatar=$fotoname;
			$fotosize=$_FILES['file']['size']; // Запоминаем размер файла

			// проверяем расширение
			$ext=strtolower(substr($fotoname, 1 + strrpos($fotoname, ".")));

			if (!in_array($ext, $valid_types)) exit("<div align=center><br><br><br><br><br><fieldset style='width:400px;border: #333 1px solid;'><legend align='center'><font size=2 face=tahoma color=red><b>Файл не загружен!</b></font></legend><br><center><font size=2 face=tahoma><b>Возможные причины - вы пытаетесь загрузить не графический файл, неверно введён адрес или выбран файл.</b></font></center><br></fieldset></div><br><br><p align=center><a href='javascript:history.back(1)' style='text-decoration:none;'>&#9668; назад</a></p>");

		}

		$text="$name|$pass|$kolvomsg|$email|$dayreg|$dayx|$pol|$icq|$www|$about|$work|$write|$avatar|$status|$cflag|";
		$text=replacer($text);
		$exd=explode("|",$text);
		$name=$exd[0];
		$pass=$exd[1];
		$email=$exd[3];

		// Ставим куку юзеру
		$tektime=time();

		//$wrfcookies="$name|".md5($pass)."|$email|$tektime|";
		//setcookie("wrfcookies", $wrfcookies, (time()+300000000),"/");

		setcookie("cname", $name, (time()+300000000),"/");
		setcookie("cmail", $email, (time()+300000000),"/");
		setcookie("cpassreg", md5($pass), (time()+300000000),"/");

		if ($_FILES['file']['name']!="")
		{
			// 1. считаем кол-во точек в выражении - если большей одной - СВОБОДЕН!
			$findtchka=substr_count($fotoname, ".");
			if ($findtchka>1)
				exit("
					<div align=center><br><br><br><br><br><fieldset style='width:400px;border: #333 1px solid;'><legend align='center'><font size=2 face=tahoma color=red><b>ОШИБКА</b></font></legend>
					<br><center><font size=2 face=tahoma><b>В имени файла есть точки $findtchka раз(а).<br>Это ЗАПРЕЩЕНО!</b>
					</font></center><br></fieldset></div><br><br><p align=center><a href='javascript:history.back(1)' style='text-decoration:none;'>&#9668; назад</a></p>");

			// 2. если в имени есть .php, .html, .htm - свободен! 
			$bag="В имени файла <B>запрещено</B> использовать .php, .html, .htm<br><p align=center><a href=\"javascript:history.back(1)\">&#9668; назад</a></p>";
			if (preg_match("/\.php/i",$fotoname)) exit("<br><br><br><center><font size=2 face=tahoma>Вхождение <b>.php</b> найдено.<br><br>$bag");
			if (preg_match("/\.html/i",$fotoname)) exit("<br><br><br><center><font size=2 face=tahoma>Вхождение <b>.html</b> найдено.<br><br>$bag");
			if (preg_match("/\.htm/i",$fotoname)) exit("<br><br><br><center><font size=2 face=tahoma>Вхождение <b>.htm</b> найдено.<br><br>$bag");

			// 3. защищаем от РУССКИХ букв в имени файла и проверяем расширение файла 
			if (!preg_match("/^[a-z0-9\.\-_]+\.(jpg|gif|png|jpeg)+$/is",$fotoname))
				exit("
					<div align=center><br><br><br><br><br><fieldset style='width:400px;border: #333 1px solid;'><legend align='center'><font size=2 face=tahoma color=red><b>ОШИБКА</b></font></legend>
					<br><center><font size=2 face=tahoma><b>Запрещено использовать русские буквы в имени файла!</b>
					</font></center><br></fieldset></div><br><br><p align=center><a href='javascript:history.back(1)' style='text-decoration:none;'>&#9668; назад</a></p>");

			// 4. Проверяем, может быть файл с таким именем уже есть на сервере
			if (file_exists("./avatars/$fotoname"))
				exit("
					<div align=center><br><br><br><br><br><fieldset style='width:400px;border: #333 1px solid;'><legend align='center'><font size=2 face=tahoma color=red><b>ОШИБКА</b></font></legend>
					<br><center><font size=2 face=tahoma><b>Файл с таким именем уже существует на сервере!<br>Измените имя на другое!</b>
					</font></center><br></fieldset></div><br><br><p align=center><a href='javascript:history.back(1)' style='text-decoration:none;'>&#9668; назад</a></p>");

			// 5. Размер в Кб. < допустимого
			$fotoksize=round($fotosize/10.24)/100; // размер ЗАГРУЖАЕМОГО ФОТО Кб
			$fotomax=round($max_file_size/10.24)/100; // макс размер фото Кб

			if ($fotoksize>$fotomax)
				exit("
					<div align=center><br><br><br><br><br><fieldset style='width:400px;border: #333 1px solid;'><legend align='center'><font size=2 face=tahoma color=red><b>ОШИБКА</b></font></legend>
					<br><center><font size=2 face=tahoma>Вы превысили допустимый размер!<br>Максимальный размер:<b>$fotomax</b> Кб<br>Ваша картинка: <b>$fotoksize</b> Кб
					</font></center><br></fieldset></div><br><br><p align=center><a href='javascript:history.back(1)' style='text-decoration:none;'>&#9668; назад</a></p>");

			// 6. Габариты аватара
			$size=getimagesize($_FILES['file']['tmp_name']);

			if ($size[0]>$avatar_width or $size[1]>$avatar_height)
				exit("
					<div align=center><br><br><br><br><br><fieldset style='width:400px;border: #333 1px solid;'><legend align='center'><font size=2 face=tahoma color=red><b>ОШИБКА</b></font></legend>
					<br><center><font size=2 face=tahoma><b>Размеры аватара не должны превышать<br>$avatar_width х $avatar_height px</b>
					</font></center><br></fieldset></div><br><br><p align=center><a href='javascript:history.back(1)' style='text-decoration:none;'>&#9668; назад</a></p>");

			if ($fotosize>"0" and $fotosize<$max_file_size)
			{
				copy($_FILES['file']['tmp_name'], avatars."/".$fotoname);

				print "<br><br><br><center><font size=2 face=tahoma><b>Фото успешно загружено: $fotoname ($fotosize байт)</b><br><p align=center><a href='javascript:history.back(1)' style='text-decoration:none;'>&#9668; назад</a></p>";
			} else {
				exit("
					<div align=center><br><br><br><br><br><fieldset style='width:400px;border: #333 1px solid;'><legend align='center'><font size=2 face=tahoma color=red><b>ФАЙЛ НЕ ЗАГРУЖЕН</b></font></legend>
					<br><center><font size=2 face=tahoma>Если вы видите сообщение:<br><b>[function.getimagesize]: Filename cannot be empty</b><br>
					значит библиотека GD отсутствует, либо старой версии. Возможно, доступ на папку для загрузки выставлен ошибочно, либо хостер запретил загрузку файлов через http.</b>
					</font></center><br></fieldset></div><br><br><p align=center><a href='javascript:history.back(1)' style='text-decoration:none;'>&#9668; назад</a></p>");
			}
		}

		$file=file("datan/usersdat.php");
		$fp=fopen("datan/usersdat.php","a+");
		flock($fp,LOCK_EX);
		ftruncate($fp,0);
		for ($i=0;$i<sizeof($file);$i++) {
			if ($ok!=$i) fputs($fp,$file[$i]); else fputs($fp,"$text\r\n");
		}
		fflush($fp); //очищение файлового буфера
		flock($fp,LOCK_UN);
		fclose($fp);



		if ($_COOKIE['cadmin']==$adminname & $_COOKIE['cpass']==$adminpass)
		{
			 exit ("<meta charset='windows-1251'><script>function reload(){location=\"index.php?event=clearuser\"};setTimeout('reload()',5000);</script>
<table width=100% height=80%><tr><td><table border=1 cellpadding=5 cellspacing=0 bordercolor=#224488 align=center valign=center width=450 height=90>
<tr><td><center><font size=2 face=tahoma><B>Регистрационные данные <font color=red>$name</font> успешно изменены!<BR><BR>
<a href='index.php?event=clearuser' style='text-decoration:none;'>Продолжить</a></B></font><BR></td></tr></table></td></tr></table></center>");

		} else {
			 exit ("<meta charset='windows-1251'><script>function reload(){location=\"javascript:history.back(1)\"};setTimeout('reload()',1000);</script>
<table width=100% height=80%><tr><td><table border=1 cellpadding=5 cellspacing=0 bordercolor=#224488 align=center valign=center width=450 height=90>
<tr><td><center><font size=2 face=tahoma><B>Регистрационные данные <font color=red>$name</font> успешно изменены!<BR><BR>
<a href='javascript:history.back(1)' style='text-decoration:none;'>Продолжить</a></B></font><BR></td></tr></table></td></tr></table></center>");

		}
	}
}




////////////////// Начало

$forumid=$_GET['forumid'];
$action=$_GET['action'];
$page=$_GET['page'];
$name=$_POST['name'];
$mail=$_POST['mail'];
$topic=$_POST['topic'];
$msg=$_POST['msg'];
$email=$_POST['email'];
$cname=$_COOKIE['cname'];
$cmail=$_COOKIE['cmail'];
$cpassreg=$_COOKIE['cpassreg'];

$tt=1;

if (isset($_POST['tt'])) $tt = $_POST['tt'];

if (($tt>4) || ($tt<1)) $tt=1;

$zvezdmax=0;
$repamax=0;

if (isset($_POST['zvezdmax'])) $zvezdmax = $_POST['zvezdmax'];
if (isset($_POST['repamax'])) $repamax = $_POST['repamax'];

if (isset($name) && isset($msg) && isset($email) && isset($_POST['forumid']) && isset($_POST['action']) && $_POST['action']=="answer")
{
	$forumid=$_POST['forumid'];
	$linesm=file("data/$forumid");
	$nm=count($linesm);
	$gpg=ceil($nm/10);

	header("location: index.php?forumid=$forumid&page=$gpg#m$nm");
}

if ($welcome==1) $on=" onload='welcome()'"; else $on="";





////////////////// Шапка форума
include "$fskin/top.html";




////////////////// Админка - действия
if (isset($_COOKIE['cadmin']) && isset($_COOKIE['cpass']))
{
	if ($_COOKIE['cadmin']==$adminname && $_COOKIE['cpass']==$adminpass)
	{
		if (isset($_GET['forumid']))
		{
			if (isset($_GET['mode']))
			{
				/////////// Закрыть тему
				if ($_GET['mode']=="closetopic" or $_GET['mode']=="opentopic" )
				{
					$fid="datan/topic.dat";
					$tlines=file("$fid");
					$ut=count($tlines);
					$tlinenew="";

					for ($i=0; $i<=$ut; $i++)
					{
						$pu=explode('¦',$tlines[$i]);

						if ($_GET['mode']=="closetopic") 
						{
							if ($pu[2]==$forumid) {
								$tlines[$i]="$pu[0]¦$pu[1]¦$pu[2]¦$pu[3]¦$pu[4]¦$pu[5]¦$pu[6]¦$pu[7]¦$pu[8]¦$pu[9]¦$pu[10]¦0¦$pu[12]¦$pu[13]¦$pu[14]¦$pu[15]¦$pu[16]¦$pu[17]¦$pu[18]¦$pu[19]¦$pu[20]¦$pu[21]¦$pu[22]¦$pu[23]¦$pu[24]¦";
							}
							$tlinenew.="$tlines[$i]";
						}
						if ($_GET['mode']=="opentopic")
						{
							if ($pu[2]==$forumid) {
								$tlines[$i]="$pu[0]¦$pu[1]¦$pu[2]¦$pu[3]¦$pu[4]¦$pu[5]¦$pu[6]¦$pu[7]¦$pu[8]¦$pu[9]¦$pu[10]¦1¦$pu[12]¦$pu[13]¦$pu[14]¦$pu[15]¦$pu[16]¦$pu[17]¦$pu[18]¦$pu[19]¦$pu[20]¦$pu[21]¦$pu[22]¦$pu[23]¦$pu[24]¦";
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

				/////////// VIP тема
				if ($_GET['mode']=="viptopic" or $_GET['mode']=="unviptopic")
				{
					$fid="datan/topic.dat";
					$tlines=file("$fid");
					$ut=count($tlines);
					$tlinenew="";

					for ($i=0; $i<=$ut; $i++)
					{
						$pu=explode('¦',$tlines[$i]);

						if ($_GET['mode']=="viptopic")
						{
							if ($pu[2]==$forumid) {
								$tlines[$i]="$pu[0]¦$pu[1]¦$pu[2]¦$pu[3]¦$pu[4]¦$pu[5]¦$pu[6]¦$pu[7]¦$pu[8]¦$pu[9]¦$pu[10]¦vip¦$pu[12]¦$pu[13]¦$pu[14]¦$pu[15]¦$pu[16]¦$pu[17]¦$pu[18]¦$pu[19]¦$pu[20]¦$pu[21]¦$pu[22]¦$pu[23]¦$pu[24]¦";
							}
							$tlinenew.="$tlines[$i]";
						}
						if ($_GET['mode']=="unviptopic")
						{
							if ($pu[2]==$forumid) {
								$tlines[$i]="$pu[0]¦$pu[1]¦$pu[2]¦$pu[3]¦$pu[4]¦$pu[5]¦$pu[6]¦$pu[7]¦$pu[8]¦$pu[9]¦$pu[10]¦1¦$pu[12]¦$pu[13]¦$pu[14]¦$pu[15]¦$pu[16]¦$pu[17]¦$pu[18]¦$pu[19]¦$pu[20]¦$pu[21]¦$pu[22]¦$pu[23]¦$pu[24]¦";
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
						$pu=explode('¦',$fid[$i]);
						if ($pu[2]==$forumid) unset($fid[$i]);
					}
					fputs($fp, implode("",$fid));
					fflush($fp);
					flock($fp,LOCK_UN);
					fclose($fp);

					unlink("data/$forumid");
					unlink("data/$forumid.dat");

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
			$text=$_POST['text'];
			$text=trim($text);
			$text=str_replace("¦","",$text);
			$text=str_replace("\n","<br>",$text);
			$text=str_replace("\r","",$text);

			$edit=file("data/$forumid");
			$fp=fopen("data/$forumid","w");
			flock($fp,LOCK_EX);
			$edit[$msg]=explode("¦",$edit[$msg]);
			$edit[$msg][3]=$text;
			$edit[$msg]=implode("¦",$edit[$msg]);
			fwrite($fp, implode("",$edit));
			fflush($fp);
			flock($fp,LOCK_UN);
			fclose($fp);

			$gpg=ceil($msg/10);
			if ($gpg=="0") {$gpg="1";}
			echo "<meta http-equiv=refresh content='0; url=index.php?forumid=$forumid&page=$gpg'>";
		}

		//////////////// Бан
		if (isset($_GET['event']))
		{
			if ($_GET['event']=="ban")
			{
				if (is_file("datan/banip.dat"))
				{
					$linesb=file("datan/banip.dat");
					$ib=count($linesb);
					$itogo=$ib;
					if ($ib>0) {
						print"
							<br><center><style>table,th,td {border:#222 1px solid; border-collapse:collapse;}</style>
							<table width=800 align=center><tr><td>
							<table width=100% cellpadding=2>
							<tr bgcolor='#777777'><td width=14 align=center><b>X</b></td><td align=center><b>IP</b></td><td align=center><b>Nickname</b></td><td align=center><b>Причина</b></td></tr>";
						do {
							$ib--;
							$idt=explode("|", $linesb[$ib]);
							print"<tr><td align=center><a href='index.php?delip=$ib' title='Удалить'><font color=red><b>X</b></font></a></td><td width=120 align=center>$idt[0]</td><td width=120>&nbsp;<b>$idt[1]</b></td><td>$idt[2]</td></tr>";
						} while($ib>"0");
					} else {
						print"<br><br><center><b>Заблокированные IP-адреса отсутствуют</b></center><br>";
					}
				}
				exit("</table><br><center><form action='index.php?badip' method=POST>
<input type=text placeholder='IP' style='width:120px' maxlength=15 name='ip'> <input type=text placeholder='Nickname' style='width:120px' name='nickban'> <input type=text style='width:450px' maxlength=250 name='text' placeholder='Причина'> <input type=submit value='Добавить' class='fbutton'></form><br>Забанено: <b>$itogo</b></td></tr></table><br><br><a href='index.php'>&#9668; назад</a></center></body></html>");

			}
		}

		//////////////// Добавление IP в БАН
		if (isset($_GET['badip']))
		{
			if (isset($_POST['ip']))
			{
				$ip=$_POST['ip'];
				$nickban=$_POST['nickban'];
				$badtext=$_POST['text'];
			}

			if (isset($_GET['ip_get']))
			{
				$ip=$_GET['ip_get'];
				$nickban=$_GET['nickban'];
				$badtext="За добавление нежелательных сообщений";
			}

			if (strlen($ip)<8) exit("<br><br><br><center><b>Вы неправильно ввели IP-адрес!</b><br><br><a href='index.php'>&#9668; назад</a>");

			$badtext=str_replace("|", "", $badtext);
			$nickban=str_replace("|", "", $nickban);

			//$text=htmlspecialchars($text, ENT_QUOTES, 'cp1251');
			$text="$ip|$nickban|$badtext|";
			$text=str_replace("\r\n", "<br>", $text);
			$text=stripslashes($text);

			$fp=fopen("datan/banip.dat","a+");
			flock($fp,LOCK_EX);
			fputs($fp,"$text\r\n");
			fflush($fp);
			flock($fp,LOCK_UN);
			fclose($fp);
			echo "<meta http-equiv=refresh content='0; url=index.php?event=ban'>";
		}

		//////////////// Удаление IP из БАНА
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







/////////////// Вход на форум
if (isset($_GET['event']))
{
	if ($_GET['event']=="login")
	{
		print "
			<br><br><form method=post action=\"index.php?event=regenter\" name='Guest' onSubmit='regGuest(); return(false);'> 
			<table align=center style='border: #000 1px solid;' cellpadding=4 cellspacing=5>
			<tr><td><input name=\"name\" size=30 placeholder='Name' type=text maxlength=$maxname></td></tr>
			<tr><td><input name=\"passreg\" size=30 type=password placeholder='Password' maxlength=20></td></tr>";

		if ($captchamin==1)
		{
			exit ("<tr><td><table cellpadding=0 cellspacing=0 border=0><tr><td>
<script>function checkedBox(f){if(f.check1.checked) document.getElementById('other').innerHTML='<br><center><input type=submit class=fbutton value=\'Отправить\'></center>';
else document.getElementById('other').innerHTML='<br><center><input type=submit class=fbutton value=\'Отправить\' disabled=\'disabled\'></center>';}</script>
<input type=checkbox name=check1 onClick=\"checkedBox(this.form)\" style='height:20px;width:20px' title='Если не отправляет данные, то повторно ставьте галочку капчи' ></td>
<td width='100%'>&nbsp; я не бот</td></tr></table></td></tr>
<tr><td><div align=center></div><div id=other align=center><br><input type=submit class=fbutton value='Отправить' disabled='disabled'></div>
</td></tr></table></form><p align=center><a href='index.php?id=forum'>&#9668; назад</a></p>");

		} else {
			exit ("<tr><td><img src=\"index.php?secpic\" id='secpic_img' style='border: #000 1px solid;' align='top' title='Для смены картинки щелкните по ней' onclick=\"document.getElementById('secpic_img').src='index.php?secpic&' + Math.random(); return false\">&nbsp; <input type='text' name='secpic' id='secpic' style='width:60px' title='Введите $let_amount жирных симв. изображенных на картинке' maxlength='10'></td></tr>
<tr><td><input type=hidden name=add value=''><br><center><input type=submit class=fbutton value='Отправить'></center>
</td></tr></table></form><p align=center><a href='index.php?id=forum'>&#9668; назад</a></p>");

		}
	}
}







/////////////// Редактирование сообщения
if ($_GET['event']=="edit_post" && $_GET['m'] && $_GET['forumid'])
{
	$page=$_GET['page'];
	$fi=$_GET['forumid'];
	$mg=$_GET['m'];
	$mf=file("data/".$fi);
	$pm=explode('¦',$mf[$mg]);

	// все проверки пройдены
	//if ($_['user'] && $pm[0]==$_['user'] && $pm[9] > (time()-3600*$timeoutedit) || $_['adm'] || $_['moder'] && !strstr($status,"администратор"))

	// все проверки пройдены
	if ($_['user'] && $pm[0]==$_['user'] && $editmsg && $pm[10] > (time()-3600*$timeoutedit))
	{
		// записываем измененное сообщение
		if (trim($_POST['msg']))
		{
			//if ($_['adm'] || $_['moder'] && !strstr($status,"администратор"))
			//$mssg=n2br($_POST['msg']); else $mssg=replacer($_POST['msg']); // админу можно с тегами, не админу теги вырезаем

			$mssg=replacer($_POST['msg']);
			$mssg=str_replace("¦",'', $mssg);

			$date_e=gmdate('d.m.Y', time() + 3600*($timezone+(date('I')==1?0:1)));
			$time_e=gmdate('H:i', time() + 3600*($timezone+(date('I')==1?0:1)));
			$editdate="$date_e в $time_e";
			$rsg='';
			$rsg=str_replace('%date%',$editdate,$redsig);

			$mf[$mg]=$pm[0].'¦'.$pm[1].'¦'.$pm[2].'¦'.$mssg.$rsg.'¦'.$pm[4].'¦'.$pm[5].'¦'.$pm[6].'¦'.$pm[7].'¦'.$pm[8].'¦'.$pm[9].'¦'.$pm[10].'¦'.$pm[11].'¦'.$pm[12];

			$f=fopen("data/".$fi, "w+");
			flock($f, LOCK_EX);
			fwrite($f, implode("", $mf));
			fflush($f);			
			flock($f, LOCK_UN);
			fclose($f);

			echo "<meta http-equiv=refresh content='0; url=index.php?forumid=".$fi."&page=".$page."#m".$mg."'>";
		} else {
			print "
<form action=\"index.php?event=edit_post&forumid=$fi&m=".$_GET['m']."&page=".$_GET['page']."\" method=post name=REPLIER>
<br><table class=f align=center cellspacing=0 cellpadding=2 border=0><tr><td>
<input type=button class=button value='B' title='Жирный шрифт' style='font-weight:bold;' onclick=\"insbb('[b]','[/b]');\">
<input type=button class=button value='i' title='Наклонный шрифт' style='font-style:italic;' onclick=\"insbb('[i]','[/i]');\">
<input type=button class=button value='U' title='Подчеркнутый шрифт' style='text-decoration:underline;' onclick=\"insbb('[u]','[/u]');\">
<input type=button class=button value='S' title='Зачеркнутый шрифт' style='text-decoration:line-through;' onclick=\"insbb('[s]','[/s]');\">
<input type=button class=button value='R' title='Красный шрифт' style='font-weight:bold;color:red;' onclick=\"insbb('[red]','[/red]');\">
<input type=button class=button value='B' title='Синий шрифт' style='font-weight:bold;color:blue;' onclick=\"insbb('[blue]','[/blue]');\"> 
<input type=button class=button value='G' title='Зеленый шрифт' style='font-weight:bold;color:green;' onclick=\"insbb('[green]','[/green]');\">
<input type=button class=button value='O' title='Оранжевый шрифт' style='font-weight:bold;color:orange;' onclick=\"insbb('[orange]','[/orange]');\">
<input type=button class=button value='Big' title='Большой шрифт' onclick=\"insbb('[big]','[/big]');\">
<input type=button class=button value='Min' title='Маленький шрифт' onclick=\"insbb('[small]','[/small]');\">
<input type=button class=button value='=--' title='Выровнять текс влево' onclick=\"insbb('[left]','[/left]');\">
<input type=button class=button value='-=-' title='Центрировать текст' onclick=\"insbb('[center]','[/center]');\">
<input type=button class=button value='--=' title='Выровнять текст вправо' onclick=\"insbb('[right]','[/right]');\">
<input type=button class=button value='IMG' title='Вставить картинку\n[img]http://site.ru/foto.jpg[/img]' style='width:38px' onclick=\"insbb('[img]','[/img]');\">
<input type=button class=button value='Code' title='Код' style='width:38px' onclick=\"insbb('[code]','[/code]');\">
<input type=button class=button value='« »' title='Цитата\nВыделите текст, который хотите процитировать и нажмите эту кнопку' style='width:38px' onclick='REPLIER.msg.value += \" [quote]\"+(window.getSelection?window.getSelection():document.selection.createRange().text)+\"[/quote] \"'>
<input type=button class=button value='PM' title='Личное сообщение\n[hide]скрыть текст от гостей форума[/hide]\n[hide=DDD]текст увидит юзер DDD и админ[/hide]' style='width:38px' onclick=\"insbb('[hide]','[/hide]');\">
<input type=button class=button value='Spoiler' title='Скрытый текст\n[spoiler]Текст[/spoiler]\n[spoiler=Название]Текст[/spoiler]' style='width:60px' onclick=\"insbb('[spoiler]','[/spoiler]');\">
<input type=button class=button value='Video' title='Вставить flv, mp4, wmv, avi, mpg\nПример:\n[video]http://site.ru/video.flv[/video]\n[video=640,480]http://site.ru/video.flv[/video]' style='width:60px' onclick=\"insbb('[video]','[/video]');\">
<input type=button class=button value='Music' title='Вставить mid, midi, wav, wma, mp3, ogg\nПример:\n[audio]http://site.ru/audio.mp3[/audio]' style='width:60px' onclick=\"insbb('[audio]','[/audio]');\">
<input type=button class=button value='Youtube' title='Вставить видео с YouTube\n[youtube]https://youtu.be/cEnHQYFP2tw[/youtube]\n[youtube]https://www.youtube.com/watch?v=cEnHQYFP2tw[/youtube]' style='width:60px' onclick=\"insbb('[youtube]','[/youtube]');\">
<a href='#' onclick='toggleStats(); return false;' style='cursor:pointer;'>FAQ</a>
<textarea name=msg cols=70 style='height:170px;font-size:9pt' id='expand'>".br2n($pm[3])."</textarea>
<br><div style='font-size:1px'>&nbsp;</div>
<center><input type=button value='&#9660;&#9660;&#9660;' title='Растянуть' style='height:15px;width:100%;font-size:10px;' onclick=\"hTextarea('expand'); return false;\"></center><div style='font-size:2px'>&nbsp;</div>
<input type=hidden name=forumid value=\"$forumid\">
<input type=hidden name=name value=\"$cname\">
<input type=hidden name=email value=\"$cmail\">
</td></tr><tr><td class=row1 align=center height='50px'><input type=submit tabindex=5 class=fbutton value='Отправить' style='width:110px'></td></tr></table></form>
<br><p align=center><a href=\"javascript:history.back(1)\">&#9668; назад</a></p></body></html>";

		}

	} else echo "<div align=center><br><br><br><br><br><fieldset style='border: #555 1px solid; width:400px'><legend align='center'><font size=2 face=tahoma color=red><b>Редактирование запрещено!</b></font></legend>
			<br><center><font size=2 face=tahoma><b>Вы не можете редактировать сообщение по причине:<br>это запрещено, либо время редактирования истекло!</b></font></center>
			<br></fieldset></div><br><br><p align=center><a href=\"javascript:history.back(1)\">&#9668; назад</a></p>";
}




////////////////// Кто был на форуме за ХХ часов
if (isset($_COOKIE['cname']))
{
	$st_userday=$_COOKIE['cname'];
} else {
	$st_userday="Гость";
}
$st_time=time();
$st_lineday="$st_userday|$st_time|$ip|\r\n";
$st_guestsday=0; //число гостей
$st_infoday=' '; //строка со списком пользователей
$list_ip=' '; //строка со списком айпишников гостей
$st_fileday="datan/userlistday.dat";
$timeouthours="24"; //Время активности на форуме (часов) 
$st_intervalday=$timeouthours*3600; //Бездействие (сек)
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
			if ($st_arrday[0]<>"Гость")
			{
				if ($st_userday=="Гость")
				{
					$findstrday=" <a href='index.php?event=profile&pname=$st_arrday[0]'>$st_arrday[0]</a>, ";
				} else {
					$findstrday=" <a href='index.php?event=profile&pname=$st_arrday[0]'>$st_arrday[0]</a>, ";
				}

				if (!strstr($st_infoday,(" ".$findstrday))) $st_infoday.=$findstrday;
			} else {
				$findstrday=$st_arrday[2].", "; //подсчет гостей

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

	if ($st_userday=="Гость") ++$st_guestsday; else $st_infoday="$st_userday, ";
}


////////////////// Когда последний раз был на форуме
$date=gmdate('d.m.Y',time()+3600*($timezone+(date('I')==1?0:1)));
$time=gmdate('H:i',time()+3600*($timezone+(date('I')==1?0:1)));

if ($_['user']) 
{
	$ulines=file("datan/usersdat.php");
	$ui=count($ulines)-1;
	$ulinenew="";

	// Ищем юзера по имени
	for ($u=0; $u<=$ui; $u++)
	{
		$udt=explode("|",$ulines[$u]);
		if ($udt[0]==$_COOKIE['cname'])
		{
			$ulines[$u]="$udt[0]|$udt[1]|$udt[2]|$udt[3]|$udt[4]|$udt[5]|$udt[6]|$udt[7]|$udt[8]|$udt[9]|$udt[10]|$udt[11]|$udt[12]|$udt[13]|$udt[14]|$date $time\r\n";
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




/////////////// ПРОСМОТР УЧАСТНИКОВ
if (isset($_GET['event']))
{
	if ($_GET['event']=="who")
	{
		if (!isset($_COOKIE['cname']) and !isset($_COOKIE['cpassreg']) || !$_COOKIE['cadmin']==$adminname and !$_COOKIE['cpass']==$adminpass)

			exit("<br><br><br><br><br><table align=center style='border: #333 1px solid;' width=380><tr><th style='height:30px'><p style='color:red'>Доступ ограничен!</p></th></tr><tr><td><center><span style='FONT-SIZE:12px'><br><B>Для просмотра данных пользователей вам необходимо<br><br>::: <a href=\"index.php?mode=reg\">зарегистрироваться</a> :::</B></span><br><br><br>[<a href=\"javascript:history.back(1)\">вернуться назад</a>]<br><br></center></td></table>");

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

				// Если есть совпадение в строке - присваиваем флагу значение 1
				if ($dt[6]!="" and $pol!="") {if (stristr($dt[6],$pol)) $flag=1;}
				if ($dt[10]!="" and $interes!="") {if (stristr($dt[10],$interes)) $flag=1;}
				if ($dt[8]!="" and $url!="") {if (stristr($dt[8],$url)) $flag=1;}
				if ($dt[9]!="" and $from!="") {if (stristr($dt[9],$from)) $flag=1;}

				// если было хоть одно соврадение, включаем участника в массив участников
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

		print "
<p align=center>[<a href='index.php'>вернуться на форум</a>]</p><center><form action=\"index.php?event=who\" method=GET>
<input type=hidden name=event value='who'>
<table style='border: #000 1px solid;' width=90% height=50 cellpadding=1 cellspacing=0><tr>
<td><input type=text name=pol value='$pol' size=20 placeholder='Пол (введи: муж или жен)'></td>
<td><input type=text name=interes value='$interes' class=post maxlength=50 size=20 placeholder='Интересы'></td>
<td><input type=text name=url value='$url' class=post maxlength=50 size=20 placeholder='Сайт'></td>
<td><input type=text name=from value='$from' class=post maxlength=50 size=20 placeholder='Откуда'></td>
</tr><tr><td colspan=4><p align='center'><input type=submit class=fbutton style='width:100%' value='Фильтр'></p></td></tr>
</table></form><br><br>
<table style='border: #000 1px solid;' width=90% cellpadding=1 cellspacing=0><tr>
<th width=25>№</th><th width=120>Имя</th><th width=120>Статус</th><th width=100>Награды</th><th>ЛС</th><th>Зареган</th><th>Когда был</th><th>ДР</th><th>Интересы</th><th>Сайт</th><th>Откуда</th></tr>";


		// Исключаем ошибку вызова несуществующей страницы
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

				if (isset($dt[1])) // Если строчка потерялась в скрипте (пустая строка) - то просто её НЕ выводим
				{
					$codename=urlencode($dt[0]); // Кодируем имя в СПЕЦФОРМАТ для корректной передачи через GET-запрос

					if (isset($_COOKIE['cname']) and isset($_COOKIE['cpassreg']))
					{
						$wfn="<a href=\"index.php?event=profile&pname=$codename\">$dt[0]</a>";

						$mls="<form action='pm.php?id=$codename' method=POST name=citata onclick=\"window.open('pm.php?id=$codename','citata','width=800,height=500,left=100,top=100,toolbar=0,status=0,border=0,scrollbars=1');return false;\"><input type='button' value='ЛС' class=button></form>";


					} else {
						$wfn="$dt[0]"; $mls=" ";
					}
					if (strlen($dt[13])=="7" and ctype_digit($dt[13])) $dt[13]="<!--font color=red><small>ждёт статуса</small></font-->";
					if (strlen($dt[13])<2) $dt[13]=$users;
					if ($dt[6]=="мужчина") $add="polm.gif"; else $add="polg.gif";
					if (is_file("flags/$dt[14]")) {$flagpr="$dt[14]";} else {$flagpr="noflag.gif";}

					print "<tr><td class=$t1 height=22><center><small>$numm</small></center></td><td class=$t1><img src='$fskin/$add' border=0> <span align=absmiddle>$wfn</span> ";

					if ($dt[7] != "")
					{
						print " <a href=\"https://icq.im/$dt[7]\" target=_blank><img src=\"https://status.icq.com/5/online1.gif?icq=$dt[7]\" border=0 align=absmiddle width=13 height=13 title=\"icq $dt[7]\"></a>";
					}

					$newstatus=explode("@", $dt[13]);

					print "</td><td class=$t1 align=center><span>$newstatus[0]</span></td><td class=$t1 align=center>&nbsp;";

					for ($i=1; $i<count($newstatus); $i++) {
						print "<img src=\"$fskin/medal.gif\" style='cursor:help' border=0 title=\"$newstatus[$i]\"> ";
					}

					print "</td>
<td class=$t1 align=center width=40><small>$mls</small></td>
<td class=$t1 align=center width=68><small>$dt[4]</small></td>
<td class=$t1 align=center width=68><small>$dt[15]</small></td>
<td class=$t1 align=center width=68><small>$dt[5]</small></td>
<td class=$t1><font style='font-family:tahoma;font-size:10px;'>$dt[10]</font></td>
<td class=$t1><small><a href=\"$dt[8]\" target='_blank'>$dt[8]</a></small></td>
<td class=$t1><img src='flags/$flagpr' border=0 align=center> <small>$dt[9]</small></td></tr>";

					if ($t1=="row1") $t1="row2"; else $t1="row1";
				}

			} while($fm < $lm+1); 
		}

		echo'</table><BR><table width=90%><TR><TD width=30%>Страницы:&nbsp; ';

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

		print "</TD><TD align=center width=30%><span>[<a href='index.php'>вернуться на форум</a>]</span></TD><TD align=right width=30%><span>Зарегистрировано: $allmaxi</span></TD></TR></TABLE><BR>";
	}


	/////////////// РЕДАКТИРОВАНИЕ ПРОФИЛЯ
	if ($_GET['event']=="profile")
	{
		if (!isset($_GET['pname'])) exit("<br><br><br><br><br><p align=center><b>ОШИБКА ЗАПРОСА!</b><br><br><br>[<a href='javascript:history.back(1)'>&#9668; назад</a></p>");

		$pname=urldecode($_GET['pname']); // Раскодируем имя
		$lines=file("datan/usersdat.php");
		$i=count($lines);
		$use="0";
		do {
			$i--;
			$rdt=explode("|", $lines[$i]);

			if (isset($rdt[1])) // Если пустая строка - то НЕ выводим
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
							print "<center><br><form action=\"index.php?event=reregist\" name=creator method=post enctype=multipart/form-data>
<table cellpadding=2 cellspacing=0 width=480 style='border: #000 1px solid;'>
<tr><th colspan=2 height=26 valign=middle>Регистрационная информация</th></tr>
<tr><td class=row1 height=26 width=120 align=right><span class=gen><b>Ваше имя</b>:</span>&nbsp;</td><td class=row2>&nbsp;<span class=gen>$rdt[0]</span></td></tr>
<tr><td class=row1 height=26 align=right><span class=gen align=right><b>Ваш пароль</b>:</span>&nbsp;</td><td class=row2>&nbsp;<input type=password class=post style='width:200px;height:23px' value=\"$rdt[1]\" name=pass size=25 maxlength=12></td></tr>
<tr><td class=row1 height=28 align=right><span class=gen align=right><b>Ваш e-mail</b>:</span>&nbsp;</td><td class=row2>&nbsp;<input type=text class=post style='width:200px;height:23px' value=\"$rdt[3]\" name=email size=25 maxlength=50></td></tr>
<tr><td class=row1 height=28 align=right><span class=gen align=right><b>ЛС</b>:</span>&nbsp;</td><td class=row2>&nbsp;";

							$wrfname=strtolower($wrfname);

							if (is_file("data-pm/$wrfname.dat"))
							{
								$linespm=file("data-pm/$wrfname.dat");
								$pmi=count($linespm);

								print "[<a href='pm.php?readpm&id=$wrfname'><font color=red><b>$pmi сообщ.</b></font></a>]";

							} else echo'сообщений нет';

							print"
</td></tr><tr><td colspan=2></td></tr>
<tr><th colspan=2 height=26 valign=middle>Дополнительная информация</th></tr>
<tr><td class=row1 height=26 align=right><span class=gen><b>Зарегистрирован</b>:</span>&nbsp;</td><td class=row2>&nbsp;<span class=genmed>$rdt[4]</span></td></tr>
<tr><td class=row1 height=26 align=right><span class=gen><b>Ваш пол</b>:</span>&nbsp;</td><td class=row2>&nbsp;<span class=gen>$rdt[6]</span><input type=hidden value=\"$rdt[6]\" name=pol></td></tr>
<tr><td class=row1 height=26 align=right><span class=gen><b>День рождения</b>:</span>&nbsp;</td><td class=row2>&nbsp;<input type=text name=dayx value=\"$rdt[5]\" class=post style='width:100px;height:23px' size=10 maxlength=10>&nbsp;<small>пример: 21.12.2012</small></td></tr>
<tr><td class=row1 height=26 align=right><span class=gen><b>Номер ICQ</b>:</span>&nbsp;</td><td class=row2>&nbsp;<input type=text value=\"$rdt[7]\" name=icq class=post style='width:100px;height:23px'' size=10 maxlength=12></td></tr>
<tr><td class=row1 height=26 align=right><span class=gen><b>Ваш сайт</b>:</span>&nbsp;</td><td class=row2>&nbsp;<input type=text value=\"$rdt[8]\" class=post style='width:345px;height:23px' name=www size=25 maxlength=50></td></tr>
<tr><td class=row1 height=26 align=right><span class=gen><b>Откуда</b>:</span>&nbsp;</td><td class=row2>&nbsp;<input type=text class=post style='width:345px;height:23px' value=\"$rdt[9]\" name=about size=25 maxlength=60></td></tr>
<tr><td class=row1 height=26 align=right><span class=gen><b>Интересы</b>:</span>&nbsp;</td><td class=row2>&nbsp;<input type=text class=post style='width:345px;height:23px' value=\"$rdt[10]\" name=work size=35 maxlength=60></td></tr>
<tr><td class=row1 height=26 align=right><span class=gen><b>Подпись</b>:</span>&nbsp;</span></td><td class=row2>&nbsp;<input type=text class=post style='width:345px;height:23px' value=\"$rdt[11]\" name=write size=35 maxlength=70></td></tr><tr><td class=row1 height=26 align=right><span class=gen><b>Флаг</b>:</span>&nbsp;</td><td class=row2>";

							/////////// Блок выбора ФЛАГА
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

							print "<table><tr><td><script>function showimageflag(){document.images.cflag.src='./flags/'+document.creator.cflag.options[document.creator.cflag.selectedIndex].value;}</script><select name='cflag' size=6 onChange='showimageflag()'>$selecthtm</select></td><td><img src='./flags/$currentflag' name=cflag border=0 hspace=15></td></tr></table></td></tr><tr><td class=row1 height=25 align=right><span class=gen><b>Аватар</b>:</span>&nbsp;</td><td class=row2 height=120>";

							/////////// Блок выбора АВАТАРА
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
<td class=row1 align=right><span class=gen><br><b>Загрузить аватар</b>:</span>&nbsp;<div align=right><small><i>не более <B>$avatar_width</B>х<B>$avatar_height</B>px ".$maxfsize."Kb &nbsp;<br><br></i></small></div></td>
<td class=row2>&nbsp;<input type=file name=file class=post style='width:340px;height:23px' size=35 maxlength=150></td></tr>
<tr><td colspan=2 align=center><input type=hidden name=name value=\"$rdt[0]\"><input type=hidden name=oldpass value=\"$rdt[1]\">
<input type=submit name=submit value='Сохранить' class='fbutton'></td></tr></table></form><p align=center>[<a href='javascript:history.back(1)'>вернуться назад</a>]</p>";

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
							$tekdt=time();

							if (strlen($rdt[13])<2) $rdt[13]=$users;
							if (is_file("avatars/$rdt[12]")) $avpr="$rdt[12]"; else $avpr="noavatar.gif";
							if (is_file("flags/$rdt[14]")) $flagpr="$rdt[14]"; else $flagpr="noflag.gif";

							print "<br><br><center><table cellpadding=2 cellspacing=0 width=520 class=forumline>
<tr><th class=thHead colspan=2>Профиль участника</th></tr>
<tr><td class=row1 width=120 height=26 align=right><span class=gen><b>Имя</b>:</span>&nbsp;</td><td class=row2>&nbsp;<span class=nav>$rdt[0]</span></td></tr>
<tr><td class=row1 height=26 align=right><span class=gen><b>Регистрация</b>:</span>&nbsp;</td><td class=row2>&nbsp;<span class=genmed>$rdt[4]</span></td></tr>
<tr><td class=row1 height=26 align=right><span class=gen><b>Был на форуме</b>:</span>&nbsp;</td><td class=row2>&nbsp;<span class=genmed>$rdt[15]</span></td></tr>
<tr><td class=row1 height=26 align=right><span class=gen><b>Пол</b>:</span>&nbsp;</td><td class=row2>&nbsp;<span class=gen>$rdt[6]</span></td></tr>
<tr><td class=row1 height=26 align=right><span class=gen><b>Статус</b>:</span>&nbsp;</td><td class=row2>&nbsp;<span class=gen>";

							$newstatus=explode("@", $rdt[13]);

							print "$newstatus[0]</span></td></tr><tr><td class=row1 height=25 align=right><span class=gen><b>Награды</b>:</span>&nbsp;</td><td class=row2><span class=gen>&nbsp;";

							if (count($newstatus)>1) {print " ";}

							for($i=1; $i<count($newstatus); $i++) {print"<img src='$fskin/medal.gif' style='cursor:help' border=0 title='$newstatus[$i]'> ";}

							print"</span></td></tr>
<tr><td class=row1 height=26 align=right><span class=gen><b>ЛС</b>:</span>&nbsp;</td><td class=row2><form action='pm.php?id=$rdt[0]' method=POST name=citata onclick=\"window.open('pm.php?id=$rdt[0]','citata','width=800,height=500,left=100,top=100,toolbar=0,status=0,border=0,scrollbars=1');return false;\"><input type='button' value='ЛС' class=button></form></td></tr>
<tr><td class=row1 height=26 align=right><span class=gen><b>Сообщений</b>:</span>&nbsp;</td><td class=row2>&nbsp;<span class=gen><b>$msguser</b> (<b>$msgaktiv</b>%) <progress title='% сообщений от общего числа' max='100' value='$msgaktiv'></progress></span></td></tr>
<tr><td class=row1 height=26 align=right><span class=gen><b>Родился</b>:</span>&nbsp;</td><td class=row2>&nbsp;<span class=genmed>$rdt[5]</span></td></tr>
<tr><td class=row1 height=26 align=right><span class=gen><b>ICQ</b>:</span>&nbsp;</td><td class=row2>&nbsp;<span class=gen>$rdt[7]</span> ";

							if ($rdt[7] !="")
							{
								print " <a href=\"https://icq.im/$rdt[7]\" target=blank><img src=\"https://status.icq.com/5/online1.gif?icq=$dt[7]\" border=0 align=top width=15 height=15 title=\"icq $rdt[7]\"></a>";
							}

							print"</td></tr>
<tr><td class=row1 height=26 align=right><span class=gen><b>Сайт</b>:</span>&nbsp;</td><td class=row2>&nbsp;<span class=gen><a href='$rdt[8]' target='_blank'>$rdt[8]</a></span></td></tr>
<tr><td class=row1 height=26 align=right><span class=gen><b>Откуда</b>:</span>&nbsp;</td><td class=row2>&nbsp;<img src='./flags/$flagpr' border=0 align=center>&nbsp; <span class=gen>$rdt[9]</span></td></tr>
<tr><td class=row1 height=26 align=right><span class=gen><b>Интересы</b>:</span>&nbsp;</td><td class=row2>&nbsp;<span class=gen>$rdt[10]</span></td></tr>
<tr><td class=row1 height=26 align=right><span class=gen><b>Подпись</b>:</span>&nbsp;</td><td class=row2>&nbsp;<span class=gen>$rdt[11]</span></td></tr>
<tr><td class=row1 height=26 align=right><span class=gen><b>Аватар</b>:</span>&nbsp;</td><td class=row2>&nbsp;<img src='./avatars/$avpr' border=0><br></td></tr></td></tr></table>
<br><p align=center>[<a href=\"javascript:history.back(1)\">вернуться назад</a>]</p>";

							$use="1";
						}
					}
				}
			}
		} while($i>"1");

		if (!isset($wrfname)) exit("<div align=center><br><br><br><br><br><fieldset style='width:350px'><legend align='center'><font size=2 face=tahoma color=red><b>Доступ закрыт!</b></font></legend>
					<br><center><font size=2 face=tahoma><b>Только зарегистрированные пользователи<br>могут пpосматpивать профиль участников!</b></font></center>
					<br></fieldset></div><br><br><p align=center><a href='javascript:history.back(1)'>&#9668; назад</a></p>");

		// в БД такого ЮЗЕРА НЕТ
		if ($use!="1") echo'<div align=center><br><br><br><br><br><fieldset style="width:400px"><legend align="center"><font size=2 face=tahoma color=red><b>Пользователь не найден!</b></font></legend>
				<br><center><font size=2 face=tahoma><b>Пользователь с таким именем не найден!<BR>Вероятнее всего он был удалён админом!</b></font></center>
				<br></fieldset></div><br><br><p align=center><a href="javascript:history.back(1)">&#9668; назад</a></p>';
	}
	exit;
}


////////////////// Получаем количество участников форума
if (is_file("datan/usersdat.php")) // считываем имя последнего зарегистрировавшегося
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




////////////////// Создаем тему
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
			$date = "$datee в $timee";

			$name = replacer(substr($name,0,$maxname));
			$mail = replacer(substr($mail,0,$maxmail));
			$topic = replacer(substr($topic,0,$maxtopic));

			$name = str_replace("¦","",$name);
			$mail = str_replace("¦","",$mail);
			$topic = str_replace("¦","",$topic);

			$tt = replacer(str_replace("¦","",$tt));

			if (preg_match('/^\d+$/', $zvezdmax)) $zvezdmax = replacer(substr(str_replace("¦","",$zvezdmax),0,2)); else $zvezdmax = "0";
			if (preg_match('/^\d+$/', $repamax)) $repamax = replacer(substr(str_replace("¦","",$repamax),0,5)); else $repamax = "0";

			if ($antimat==1) {
				$name = removeBadWords($name);
				$topic = removeBadWords($topic);
				$mail = removeBadWords($mail);
			}
			if ($antimatt==1) {
				$name = removeBadWordss($name);
				$topic = removeBadWordss($topic);
				$mail = removeBadWordss($mail);
			}

			//getCountry();

			if ($ipinfodb==1) {
				$url = "http://api.ipinfodb.com/v3/ip-city/?key=$key&ip=$ip&format=json";
				$data = json_decode(file_get_contents($url));
				$country_code = $data->countryCode;
				$country_city = ucwords(strtolower($data->cityName.', '.$data->countryName));
				$country_city = str_replace("-, -", "", $country_city);
				$country_city = str_replace(", ", "", $country_city);

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

			$value=$name."¦".$mail."¦".$date."¦".$topic."¦".$country_img."¦".$country_name."¦".$ip."¦".$country."¦".$latitude."¦".$longitude."¦".$tt."¦".$zvezdmax."¦".$repamax."¦\n";

			$fp=fopen("data/$gen","w");
			flock($fp,LOCK_EX);
			fwrite($fp, $value);
			fflush($fp);
			flock($fp,LOCK_UN);
			fclose($fp);

			$valuetopic=$name."¦".$mail."¦".$gen."¦".$date."¦".$topic."¦".$country_img."¦".$country_name."¦".$ip."¦".$country."¦".$latitude."¦".$longitude."¦".$tt."¦".$zvezdmax."¦".$repamax."¦\n";

			$fp=fopen("datan/topic.dat","a+");
			flock($fp,LOCK_EX);
			fwrite($fp, $valuetopic);
			fflush($fp);
			flock($fp,LOCK_UN);
			fclose($fp);

			////////////////// Просмотры (клики), создаем файл 

			$clickfile = 'data/'.$gen.'.dat';
			if (!file_exists($clickfile)) file_put_contents($clickfile, 0, LOCK_EX);


			////////////////// БЛОК добавляет +1 к кол-ву тем (Репа)
			if ($_['user']) 
			{
				$ulines=file("datan/userstat.dat");
				$ui=count($ulines)-1;
				$ulinenew="";

				// Ищем юзера по имени в userstat.dat
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
	print"<div align=center><font color=red><b>Добавление тем и сообщений запрещено!</b></font></div><br>";
}







////////////////// Ответ в теме
if (isset($forumid) || isset($_POST['action']) && $_POST['action']=="answer" && isset($_POST['forumid']))
{
	if ($action=="answer" || isset($_POST['action']))
	{
		if (isset($_POST['name']) && isset($_POST['email']) && isset($_POST['msg']) && isset($_POST['forumid']) && isset($_POST['action']) && $_POST['action']=="answer" && $readonly==0)
		{
			////////////////// Антимат 2 (stopwords.dat)
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
			$mm=str_replace("01","янв",$mm);
			$mm=str_replace("02","фев",$mm);
			$mm=str_replace("03","марта",$mm);
			$mm=str_replace("04","апр",$mm);
			$mm=str_replace("05","мая",$mm);
			$mm=str_replace("06","июня",$mm);
			$mm=str_replace("07","июля",$mm);
			$mm=str_replace("08","авг",$mm);
			$mm=str_replace("09","сент",$mm);
			$mm=str_replace("10","окт",$mm);
			$mm=str_replace("11","нояб",$mm);
			$mm=str_replace("12","дек",$mm);

			$date=gmdate('d ', time()+3600*($timezone+(date('I')==1?0:1))).$mm.gmdate(' Y \в H:i', time()+3600*($timezone+(date('I')==1?0:1)));

			$name=substr($name,0,$maxname);
			$email=substr($email,0,$maxmail);
			$msg=substr($msg,0,$maxmsg);

			$name=str_replace("¦","",$name);
			$email=str_replace("¦","",$email);
			$msg=str_replace("¦","",$msg);

			$tt=trim(replacer(str_replace("¦","",$tt)));

			$name=trim(replacer($name));
			$email=trim(replacer($email));
			$msg=trim(replacer($msg));

			$name=str_replace("\r","",$name);
			$name=str_replace("\n","",$name);
			$name=str_replace("\t","",$name);

			$email=str_replace("\r","",$email);
			$email=str_replace("\n","",$email);
			$email=str_replace("\t","",$email);

			$msg=str_replace("\n","<br>",$msg);

			$tektime=time();

			//getCountry();

			if ($ipinfodb==1) {
				$url = "http://api.ipinfodb.com/v3/ip-city/?key=$key&ip=$ip&format=json";
				$data = json_decode(file_get_contents($url));
				$country_code = $data->countryCode;
				$country_city = ucwords(strtolower($data->cityName.', '.$data->countryName));
				$country_city = str_replace("-, -", "", $country_city);
				$country_city = str_replace(", ", "", $country_city);

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





			if (isset($_FILES['file']['name'])) // ЕСЛИ ДОБАВЛЯЕМ ФАЙЛ
			{
				$fotoname=replacer($_FILES['file']['name']);

			if (strlen($fotoname)>1)
			{
				$fotosize=$_FILES['file']['size'];

				// Проверяем РАСШИРЕНИЕ
				$ext=strtolower(substr($fotoname, 1 + strrpos($fotoname, ".")));
				if (!in_array($ext, $valid_types_load)) exit;

				// Считаем КОЛ-ВО ТОЧЕК не большей одной
				$findtchka=substr_count($fotoname, ".");
				if ($findtchka>1) exit;

				// Если в имени есть .php, .html, .htm
				if (preg_match("/\.php/i",$fotoname)) exit;
				if (preg_match("/\.html/i",$fotoname)) exit;
				if (preg_match("/\.htm/i",$fotoname)) exit;

				// Защищаем от РУССКИХ букв и проверка РАСШИРЕНИЯ 
				$patern="";
				foreach($valid_types_load as $v)
				$patern.="$v|";
				if (!preg_match("/^[a-z0-9\.\-_]+\.(".$patern.")+$/is",$fotoname)) exit;

				// Проверяем, может быть файл с таким именем уже есть на сервере
				if (file_exists("$filedir/$fotoname")) exit;

				// Размер файла
				$fotoksize=round($fotosize/10.24)/100; // размер ЗАГРУЖАЕМОГО файла в Кб
				$fotomax=round($max_upfile_size/10.24)/100; // максимальный размер файла в Кб

				if ($fotoksize>$fotomax) exit;

				// ЕСЛИ включен порядок присвоения файлу случайного имени при загрузке - генерируем случайное имя
				//if ($random_name==TRUE) {do $key=mt_rand(100000,999999); while (file_exists("$filedir/$key.$ext")); $fotoname="$key.$ext";}

				copy($_FILES['file']['tmp_name'], $filedir."/".$fotoname);

				print "<br><br><br><center><font size=3 face=arial>Файл <b>$fotoname</b> ($fotosize байт) успешно загружен!</center>";

				$size = getimagesize("$filedir/$fotoname");

				/////// Если габариты меньше заданных в настройках 260х220 то ничего с ним не делаем. Блок делает маленькое изображение - превьюшки
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

				//От размера
				if ($size[0]>$maxwidth || $size[1]>$maxheight) {

				//От веса. Если больше 100 Кб жать. Кроме гифов.
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


			$value=$name."¦".$email."¦".$date."¦".$msg."¦".$country_img."¦".$country_name."¦".$ip."¦".$country."¦".$latitude."¦".$longitude."¦".$tektime."¦".$fotoname."¦".$fotosize;

			$fil="data/".$forumid;
			$fp=fopen($fil,"a");
			flock($fp,LOCK_EX);
			fwrite($fp, $value."\n");
			fflush($fp);
			flock($fp,LOCK_UN);
			fclose($fp);

			$msgg=preg_replace("/\[hide\](.+?)\[\/hide\]/is", " [Текст скрыт от гостей] ", $msg);
			$msgg=preg_replace("/\[hide=(.+?)\](.+?)\[\/hide\]/is", " [Текст для \\1] ", $msg);

			$msgg=substr($msg,0,100);

			$ccnt=(count(file($fil)))-1;

			$topiclines=file("datan/topic.dat");
			$counttopic=count($topiclines);
			$fp=fopen("datan/topic.dat","w");
			flock($fp,LOCK_EX);

			for($i=0; $i<$counttopic; $i++)
			{
				$tdt=explode("¦",$topiclines[$i]);

				$topicdat="$tdt[0]¦$tdt[1]¦$tdt[2]¦$tdt[3]¦$tdt[4]¦$tdt[5]¦$tdt[6]¦$tdt[7]¦$tdt[8]¦$tdt[9]¦$tdt[10]¦$tdt[11]¦$tdt[12]¦$tdt[13]¦".$ccnt."¦".$name."¦".$email."¦".$date."¦".$msgg."¦".$country_img."¦".$country_name."¦".$ip."¦".$country."¦".$latitude."¦".$longitude."¦";

				if ($forumid!=$tdt[2]) fwrite($fp,"$topiclines[$i]"); else fwrite($fp,$topicdat."\n");
			}
			fflush($fp);
			flock($fp,LOCK_UN);
			fclose($fp);



			////////////////// БЛОК добавляет +1 к сообщению (Репа)
			if ($_['user']) 
			{
				$ulines=file("datan/userstat.dat");
				$ui=count($ulines)-1;
				$ulinenew="";

				// Ищем юзера по имени в файле userstat.dat
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



			////////////////// Блок пересчёта участников
			if ($_['user'])
			{
				$lines=null;
				$ok=null;

				$ulines=file("datan/usersdat.php");
				$ui=count($ulines);

				$slines=file("datan/userstat.dat");
				$si=count($slines)-1;

				/////////// Обновляем статус пользователей (автор КОТ)
				for ($i=1;$i<$ui;$i++)
				{
					$udt=explode("|", $ulines[$i]);

					if ($i<=$si) $sdt=explode("|",$slines[$i]); else $sdt[0]="";

					if ($udt[0]==$sdt[0]) // если имя=имя - значит данные верны
					{
						$repuser=$sdt[3]; //репутация пользователя для повышения статуса
						$statuser=$udt[13]; //текущий статус пользователя

						//// Заглушка для статуса
						$stu_end = $stu9 + 10;
						$stn_end = "Pro";
						if ($repuser>$stu9) $stu_end =  $repuser + 10;

						if (!strstr($udt[13],"администратор") and !strstr($udt[13],"модератор"))
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
				ftruncate($fp,0); //Удаляем содержимое файла
				for ($i=0;$i<$ui;$i++)
				fputs($fp,$ulines[$i]);
				flock($fp,LOCK_UN);
				fclose($fp);

				///// Цикл по кол-ву юзеров в базе
				for ($i=1;$i<$ui;$i++)
				{
					$udt=explode("|", $ulines[$i]);

					if ($i<=$si) $sdt=explode("|",$slines[$i]); else $sdt[0]="";
					if ($udt[0]==$sdt[0])
					{
						$udt[0]=str_replace("\r\n","",$udt[0]);
						$ok=1;

						if (isset($sdt[1]) and isset($sdt[2]) and isset($sdt[3]) and isset($sdt[4]))
						{
							$lines[$i]="$slines[$i]";
						} else {
							$lines[$i]="$udt[0]|0|0|0|0|||||\r\n";
						}
					}
					// Цикл в файле статистики - поиск строку текущего юзера
					if ($ok!="1") {
						for ($j=1;$j<$si;$j++)
						{
							$sdt=explode("|", $slines[$j]);

							if ($udt[0]==$sdt[0]) {$ok=1; $lines[$i]=$slines[$j];} // если имя=имя - значит данные верны
						}
						if ($ok!="1") $lines[$i]="$udt[0]|0|0|0|0|||||\r\n"; // создаём юзера с нулевой статистикой
					}
					$ok=null;
					$ii=count($lines);
				}
				$fp=fopen("datan/userstat.dat","a+");
				flock($fp,LOCK_EX); 
				ftruncate ($fp,0);
				fputs($fp,"ЮЗЕР|Тем|Сообщ|Репа|Предупр|Когда меняли рейтинг|ip|||\r\n");
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
				list($n_name,$e_email,$d_date,$topic,$c_country_img,$c_country_name,$i_ip,$c_country,$l_latitude,$l_longitude,$tektime,$fileload,$fileloadsize)=explode("¦",$theme[0]);
				$topic=trim($topic);

				/////////////////// Запись последних сообщений
				if ($lastmess=="1")
				{
					$lastmessfile="datan/lastmes.dat";
					$newlines=file("$lastmessfile");
					$ni=count($newlines)-1;
					$i2=0;
					$newlineexit="";

					$msg=str_replace("¦", "", $msg);

					$msg=preg_replace("/\[hide\](.+?)\[\/hide\]/is", " [Текст скрыт от гостей] ", $msg);
					$msg=preg_replace("/\[hide=(.+?)\](.+?)\[\/hide\]/is", " [Текст для \\1] ", $msg);

					$valuelast=$name."¦".$email."¦".$date."¦".$forumid."¦".$topic."¦".$pages."¦".$msg."¦".$country_img."¦".$country_name."¦".$ip."¦".$country."¦".$latitude."¦".$longitude;

					$valuelast=str_replace("
", "<br>", $valuelast);

					for ($i=0;$i<=$ni;$i++) {
						$ndt=explode("¦",$newlines[$i]);
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

			list($name,$email,$date,$topic,$country_img,$country_name,$ip,$country,$latitude,$longitude,$tektime,$fileload,$fileloadsize)=explode("¦",$theme[0]);

			$topic=trim($topic);

			print "<script>document.title+=\": $topic\"</script>\n";
			print "<table align=center cellspacing=0 cellpadding=1 border=0><tr><td><div align=center class=med>";

			$prev=min($page-1,$pages);
			$next=max($page+1,1);

			if ($page>1) print "<a href=\"index.php?forumid=".$forumid."&page=".$prev."\" class=pagination>&#9668;</a>&nbsp; &nbsp;";

			if ($pages>1) {
				if ($page>=4 and $pages>5) $pageinfo.="<a href='index.php?forumid=$forumid&page=1' class=pagination>1</a> ... ";
				$f1=$page+2;
				$f2=$page-2;
				if ($page<=2) $f1=5; $f2=1;
				if ($page>=$pages-1) $f1=$pages; $f2=$page-3;
				if ($pages<=5) $f1=$pages; $f2=1;

				for($i=$f2; $i<=$f1; $i++)
				{
					if ($page==$i) $pageinfo.="<b class=currentpage>$i</b>&nbsp;"; else $pageinfo.="<a href='index.php?forumid=$forumid&page=$i' class=pagination>$i</a>&nbsp;";
				}
				if ($page<=$pages-3 and $pages>5) $pageinfo.="... <a href='index.php?forumid=$forumid&page=$pages' class=pagination>$pages</a>";
			}

			print $pageinfo;

			if ($page<$pages) print "&nbsp; <a href=\"index.php?forumid=".$forumid."&page=".$next."\" class=pagination>&#9658;</a>";

			print "</div></td></tr></table><table class=f align=center cellspacing=1 cellpadding=0 bgcolor=#000000 style='margin-top:3px' border=0><td><table width='100%' cellspacing=0>
				<td class=t><font color=red>Тема:</font>&nbsp;<a href='index.php' title='Вернуться к списку тем'>$topic</a> &nbsp;<span class=small>[Ответов: $ccnt, страница: $page]</span></td>
				<td class=t valign=top align=right>";

			if ($_COOKIE['cadmin']==$adminname && $_COOKIE['cpass']==$adminpass)
			{
				print "<a href='index.php?mode=unlink&forumid=$forumid' style='color:red;' onclick=\"return confirm('Удалить эту тему?')\">Удалить</a> •&nbsp;";
			}

			print "<a href='index.php'>Список тем</a> • <a href='index.php?action=newtopic'>Создать тему</a></td></table></td></table>";


			for ($i=$p; $i<min($mpp+$p, $cnt); $i++)
			{
				list($name,$email,$date,$msg,$country_img,$country_name,$ip,$country,$latitude,$longitude,$tektime,$fileload,$fileloadsize)=explode("¦",$theme[$i]);

				$msg_for_admin=$msg;

				$msg=preg_replace('#\[quote\](.+?)\[/quote\]#is', '<div class=q>$1</div>', $msg);
				$msg=str_replace(" [/quote]","[/quote]",$msg);
				$msg=str_replace("\n[/quote]","[/quote]",$msg);

				$msg=preg_replace("/\[hide\](.*?)\[\/hide\]/eis", "hideguest('\\1')", $msg);
				$msg=preg_replace("/\[hide=(.*?)\](.*?)\[\/hide\]/eis", "hideuser('\\1','\\2')", $msg);

				$msg=preg_replace("/(\[code\])(.+?)(\[\/code\])/is","<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b><u><small>Код:</small></u></b><div class=code style='margin-left:18px;margin-right:18px;padding:5px;margin-top:2px'>$2</div>", $msg);

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
				$msg=preg_replace('#\[spoiler\](.*?)\[/spoiler\]#i', '<div style="padding:0px;margin:5px;border:#999999 0px solid;"><a style="border-bottom: 1px dashed; text-decoration: none;" href="#" onclick="var container=this.parentNode.getElementsByTagName(\'div\')[0];if(container.style.display!=\'\'){container.style.display=\'\';} else {container.style.display=\'none\';}">Спойлер</a><div style="display:none;word-wrap:break-word;overflow:hidden;"><div class="spoiler">$1</div></div></div>', $msg);

				$msg=preg_replace("/(\[video\])(.+?)(\[\/video\])/is","<br><video width=640 height=480 controls><source src=\"$2?autoplay=false\" type=\"video/mp4\"></video><br>", $msg);
				$msg=preg_replace("/(\[video=)(\S+?)(\,)(.+?)(\])(.+?)(\.flv|\.mp4|\.wmv|\.avi|\.mpg|\.mpeg)(\[\/video\])/is", "<br><video width=\"$2\" height=\"$4\" controls><source src=\"$6$7\" autoplay=false type=\"video/mp4\"></video><br>", $msg);

				$msg=preg_replace("/(\[audio\])(.+?)(\[\/audio\])/is","<br><audio src=\"$2?autoplay=false\" type=\"audio/mp3\" controls></audio><br>", $msg);

				//$msg=preg_replace("/(\[youtube\])(.+?)(\[\/youtube\])/is","<br><object width=640px height=480px><param name=movie value=\"http://www.youtube.com/v/$2\"></param><param name=allowFullScreen value=true></param><param name=allowscriptaccess value=always></param><embed src=\"http://www.youtube.com/v/$2\" type=\"application/x-shockwave-flash\" allowscriptaccess=always allowfullscreen=true width=640px height=480px></embed></object><br>", $msg);

				$msg=preg_replace("/\[youtube\]https?:\/\/(?:[a-z\d-]+\.)?youtu(?:be(?:-nocookie)?\.com\/.*v=|\.be\/)([-\w]{11})(?:.*[\?&#](?:star)?t=([\dhms]+))?\[\/youtube\]/i","<br><object width=640px height=480px><param name=movie value=\"http://www.youtube.com/v/$1\"></param><param name=allowFullScreen value=true></param><param name=allowscriptaccess value=always></param><embed src=\"https://www.youtube.com/v/$1\" type=\"application/x-shockwave-flash\" allowscriptaccess=always allowfullscreen=true width=640px height=480px></embed></object><br>", $msg);

				if ($antimat==1) $msg=removeBadWords($msg);
				if ($antimatt==1) $msg=removeBadWordss($msg);

				//if ($liteurl==1) {$msg=preg_replace("#([^\[img\]])(http|https|ftp|goper):\/\/([a-zA-Z0-9\.\?&=\;\-\/_]+)([\W\s<\[]+)#i", "\\1<a href=\"\\2://\\3\" target=\"_blank\">\\2://\\3</a>\\4", $msg);}
				//$msg=preg_replace('#\[img\](.+?)\[/img\]#', '<a href="$1" target="_new" title="Открыть в новом окне"><img src="$1" border="0" width="auto"></a>', $msg);

				$msg=ikoncode($msg);

				//if ($liteurl==1) $msg=autolink($msg);

				if ($liteurl==1) {
					$msg=preg_replace("/([\s>\]]+)www\.([\w\-\.,@?^=%&:;\/~\+#]*[\w\-\@?^=%&:;\/~\+#])/i", "\\1http://www.\\2", $msg); 
					$msg=preg_replace("/([\s>\]]+)((http|ftp)+(s)?:(\/\/)([\w]+(.[\w]+))([\w\-\.,@?^=%&:;\/~\+#]*[\w\-\@?^=%&:;\/~\+#])?)/i", "\\1<a href=\"\\2\" target=\"_blank\">\\2</a>", $msg);
				}

				$latitude = str_replace(array(".","1","2","3","4","5","6","7","8","9","0"), array("-","i","z","e","y","s","b","f","x","g","o"), $latitude);
				$longitude = str_replace(array(".","1","2","3","4","5","6","7","8","9","0"), array("-","i","z","e","y","s","b","f","x","g","o"), $longitude);
				$user_id = "$latitude-$longitude";

				$you_status=$you_reiting=$you_avatar=$you_flag=$you_email=$you_icq=$you_site=$you_datareg=$you_from=$you_podpis="";

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
					$dttt=explode("¦", $lines[$a]);
					if ($forumid==$dttt[2])
					{
						if (empty($_['user']) & $dttt[12]>0 || empty($_['user']) & $dttt[13]>0) exit("<script>setTimeout(function(){window.location.href='index.php';},10000);</script><br><br><br><br><center><table align=center width=450><tr><th colspan=4 height=30>Доступ ограничен!</th></tr><tr class=row2><td class=row1><center><BR><BR><span style='FONT-SIZE:12px'><b>Для просмотра этой темы вы должны быть зарегистрированы!</b><br><br>[<a href=\"index.php\">вернуться назад</a>]</span></center><br><br></td></table><br>");

						if (isset($_COOKIE['cadmin']) && isset($_COOKIE['cpass']) && $_COOKIE['cadmin']==$adminname && $_COOKIE['cpass']==$adminpass)
						{
							continue;
						} else {
							if ($_['user'] && $you_zvezd<$dttt[12]) exit("<script>setTimeout(function(){window.location.href='index.php';},10000);</script><br><br><br><br><center><table align=center width=450><tr><th colspan=4 height=30>Доступ ограничен!</th></tr><tr class=row2><td class=row1><center><BR><BR><span style='FONT-SIZE:12px'><b>Для просмотра этой темы надо иметь $dttt[12] звезд.<br>У вас $you_zvezd</b><br><br>[<a href=\"index.php\">вернуться назад</a>]</span></center><br><br></td></table><br>");

							if ($_['user'] && $you_repa<$dttt[13]) exit("<script>setTimeout(function(){window.location.href='index.php';},10000);</script><br><br><br><br><center><table align=center width=450><tr><th colspan=4 height=30>Доступ ограничен!</th></tr><tr class=row2><td class=row1><center><BR><BR><span style='FONT-SIZE:12px'><b>Для просмотра этой темы надо иметь $dttt[13] баллов репутации.<br>У вас $you_repa</b><br><br>[<a href=\"index.php\">вернуться назад</a>]</span></center><br><br></td></table><br>");
						}
					}
				}




				$topicavtor = explode("¦", $theme[0]);

				if ($name == $topicavtor[0]) {$topicavtor="автор темы";} else {$topicavtor="";}

				$newstatus=explode("@", $you_status);

				print "
					<table class=f align=center cellspacing=1 cellpadding=0 border=0>	
					<tr><td rowspan=2 valign=top class=name>
					<table cellspacing=0 cellpadding=1 width='190px' border=0>
					<tr><td valign=top class=name><a href=\"javascript:ins('".$name."')\" class='name' title='Вставить имя в форму ответа'>".$name."</a>";

				if ($_['user'])
				{
					print " <span class=small><sup><a href='index.php?event=profile&pname=".$name."' style='text-decoration:none' title='Звёзды (выдаёт админ). Перейти к профилю пользователя'>$you_reiting</a></sup><div style='display:inline-block;margin:0 0 -5 5;' class='$country_img' title='$country, $country_name'></div><br><div class=small>$newstatus[0]<br>$topicavtor</div></td></tr><tr><td valign=top class=name><br>";

				} else {
					print " <span class=small><sup title='Количество звезд'>$you_reiting</sup><div style='display:inline-block;margin:0 0 -5 5;' class='$country_img' title='$country, $country_name'></div><br>$newstatus[0]<br>$topicavtor</span></td></tr><tr><td valign=top class=name><br>";
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
						$repatimeday=864000; //864000=10дней, 2592000=30дней
						$repatime=$dtr[0]+$repatimeday;

						if ($dtr[1]>0)
						{
							$dtr[1]="<div style='background:#B7FFB7;border-right:1px solid #B7FFB7;color:#000;font-family:tahoma;font-size:9px;font-weight:bold;'>$dtr[1]</div>";
						} else {
							$dtr[1]="<div style='background:#FF9F9F;border-right:1px solid #FF9F9F;color:#000;font-family:tahoma;font-size:9px;font-weight:bold;'>$dtr[1]</div>";
						}

						if ($dtr[2]===$name and $repatime>$tektime)
						{
							$dtr[0]=date("d.m.y в H:i", $dtr[0]);

							$ppp="<div class='text-block'><div class='tooltip'>$dtr[1]<span class='tooltiptext'><b><u>Последним менял репутацию</u><br>Юзер</b>: $dtr[3] ($dtr[5])<br><b>Поставил</b>: $point<br><b>Причина</b>: $dtr[4]</span></div></div>";
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
						print "<style>.cont{position:relative;}.text-block{position:absolute;top:0px;right:50px;}.holder{position:relative;}.holder:hover .block{display:block;}.block{position:absolute;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);padding:5px;display:none;}</style><div class='cont'><div class='gravatar' align='center'><div class='holder'><img src=\"avatars/$avpr\" style='border-radius:50%;-moz-border-radius:50%;-webkit-border-radius:50%'><div class='block'><small>[<a class='small' href='#' name=citata onclick=\"window.open('pm.php?id=".$name."','citata','width=800,height=500,left=100,top=100,toolbar=0,status=0,border=0,scrollbars=1');return false;\" title='Отправить Личное Сообщение'>Личное</a>] [<a class='small' href='index.php?event=profile&pname=".$name."' title='Профиль Пользователя'>Профиль</a>]</small></div></div></div>$ppp</div>";

					} else {
						print "<style>.cont{position:relative;}.text-block{position:absolute;top:0px;right:50px;}.holder{position:relative;}.holder:hover .block{display:block;}.block{position:absolute;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);padding:5px;display:none;}</style><div class='cont'><div class='gravatar' align='center'><div class='holder'><img src=\"avatars/$avpr\"><div class='block'><small>[<a class='small' href='#' name=citata onclick=\"window.open('pm.php?id=".$name."','citata','width=800,height=500,left=100,top=100,toolbar=0,status=0,border=0,scrollbars=1');return false;\" title='Отправить Личное Сообщение'>Личное</a>] [<a class='small' href='index.php?event=profile&pname=".$name."' title='Профиль Пользователя'>Профиль</a>]</small></div></div></div>$ppp</div>";

					}
				} else {
					if ($gravatar==1)
					{
						$gravatarimg=md5(strtolower(trim($email)));

						if ($avround==1)
						{
							print "<style>.cont{position:relative;}.text-block{position:absolute;top:0px;right:50px;}.holder{position:relative;}.holder:hover .block{display:block;}.block{position:absolute;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);padding:5px;display:none;}</style><div class='cont'><div align='center'><div class='holder'><img style='border-radius:50%;-moz-border-radius:50%;-webkit-border-radius:50%' src=\"http://www.gravatar.com/avatar/$gravatarimg?d=identicon&s=$gravatarsize\"><div class='block'><small>[<a class='small' href='#' name=citata onclick=\"window.open('pm.php?id=".$name."','citata','width=800,height=500,left=100,top=100,toolbar=0,status=0,border=0,scrollbars=1');return false;\" title='Отправить Личное Сообщение'>Личное</a>] [<a class='small' href='index.php?event=profile&pname=".$name."' title='Профиль Пользователя'>Профиль</a>]</small></div></div></div>$ppp</div>";

						} else {
							print "<style>.cont{position:relative;}.text-block{position:absolute;top:0px;right:50px;}.holder{position:relative;}.holder:hover .block{display:block;}.block{position:absolute;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);padding:5px;display:none;}</style><div class='cont'><div class='gravatar' align='center'><div class='holder'><img src=\"http://www.gravatar.com/avatar/$gravatarimg?d=identicon&s=$gravatarsize\"><div class='block'><small>[<a class='small' href='#' name=citata onclick=\"window.open('pm.php?id=".$name."','citata','width=800,height=500,left=100,top=100,toolbar=0,status=0,border=0,scrollbars=1');return false;\" title='Отправить Личное Сообщение'>Личное</a>] [<a class='small' href='index.php?event=profile&pname=".$name."' title='Профиль Пользователя'>Профиль</a>]</small></div></div></div>$ppp</div>";

						}
					} else {
						print "<div align='center'></div>";
					}
				}

				$rank_list = "
<span class='tooltiptext' style='width:250px'>
<b>Статус</b>: $newstatus[0]<br><br>
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
					if ($newstatus[0]=="модератор") print"<div class='tooltip'><img src=\"rank/$imgstatus/moder.png\" border=0>$rank_list";
					if ($newstatus[0]=="администратор") print"<div class='tooltip'><img src=\"rank/$imgstatus/admin.png\" border=0>$rank_list";
				}

				print "
					</td></tr><tr><td valign=top class=name><div align='center'>
					<!--div style='display:inline-block;' class='$country_img' title='$country, $country_name'></div><font style='font-size:9px;'>$user_id</font-->
					</div></td></tr><tr><td valign=top class=name>";

				if ($nagrada==1)
				{
					for ($ii=1; $ii<count($newstatus); $ii++)
					{
						print"<div class='tooltip'><img src='$fskin/medal.gif' border=0 style='cursor:help'><span class='tooltiptext'><b>Награда #$ii</b>: $newstatus[$ii]</span></div> ";
					}
					print "<br>";
				}


				if (isset($_COOKIE['cadmin']) && isset($_COOKIE['cpass']) && $_COOKIE['cadmin']==$adminname && $_COOKIE['cpass']==$adminpass)
				{
					print "<a href=\"mailto:".$email."\" style='font-family:tahoma;font-size:11px;font-weight:normal'>".$email."</a><br><span class=small><a href='https://ip-whois-lookup.com/lookup.php?ip=".$ip."' target='_blank'>".$ip."</a>&nbsp;[<a href=index.php?badip&ip_get=".$ip."&nickban=".$name.">БАН</a>]</span><br>";
				}

				if (is_file("flags/$you_flag")) $flagpr="$you_flag"; else $flagpr="noflag.gif";

				$array = explode('.',$you_flag);

				$you_flag_name = $array[0];

				$uslines=file("datan/userstat.dat");
				$usi=count($uslines)-1;

				// Ищем юзера по имени в userstat.dat
				for ($iu=0; $iu<=$usi; $iu++)
				{
					$udt=explode("|",$uslines[$iu]);

					if ($udt[0]==$name)
					{
						print "<div align=center title='[ тем / сообщений / репутация / нарушений ]' style='background-color:;font-family:tahoma;font-size:10px;font-weight:normal;border:0px solid #333;margin:5px'>[ $udt[1] / $udt[2] / <a href='#m$fm' style='text-decoration:none' onclick=\"window.open('admin.php?event=repa&name=$udt[0]&who=$userpn', 'repa', 'width=600,height=600,left=50,top=50,scrollbars=yes');\"><b>$udt[3]</b> &#177;</a> / $udt[4] ]";

						if ($knopki==1)
						{
							print "</div>";
						} else {
							if ($_['user'])
							{
print "<details title='Посмотреть кратко ПП' style='cursor:hand;display:inline-block;font-family:tahoma;font-size:12px;font-weight:normal;text-align:left;padding:0px 1px;margin:3 0 3;'><summary style='text-align:center;'></summary><div style='width:175px'><table border=0 style='border:1px solid #669900;width:175px'>
<tr><td><b style='font-family:tahoma;font-size:10px'>Статус:</b></small></td><td><i style='font-family:tahoma;font-size:10px'>$newstatus[0]</i></td></tr>
<tr><td><b style='font-family:tahoma;font-size:10px'>Зарег:</b></small></td><td><small><i style='font-family:tahoma;font-size:10px'>$you_datareg [#$userpn]</i></small></td></tr>
<tr><td><b style='font-family:tahoma;font-size:10px'>Откуда:</b></small></td><td><small><img src=\"flags/$flagpr\"/> <i style='font-family:tahoma;font-size:10px'>$you_flag_name $you_from</i></small></td></tr>
<tr><td><b style='font-family:tahoma;font-size:10px'>ICQ:</b></small></td><td><small><i style='font-family:tahoma;font-size:10px'>$you_icq</i></td></tr>
<tr><td><b style='font-family:tahoma;font-size:10px'>Web:</b></small></td><td><small><i style='font-family:tahoma;font-size:10px'><a href=\"$you_site\" target=_new>$you_site</a></i></small></td></tr></table></div></details></div>";

							}
							print "</div>";
						}

						if ($knopki==1)
						{
							if ($_['user'])
							{
print "<style>a.glf{font-family:tahoma;font-size:11px;font-weight:normal;color:#669900;border-radius:3px;box-shadow:1px 1px #111;padding:0px 2px;text-decoration:none;border:1px solid #669900;}a.glf:hover{background-color:#669900;color:#fff;}</style>
<a class='glf' href='#' name=citata onclick=\"window.open('pm.php?id=".$name."','citata','width=800,height=500,left=100,top=100,toolbar=0,status=0,border=0,scrollbars=1');return false;\" title='Отправить Личное Сообщение'>ЛС</a> <a class='glf' href='index.php?event=profile&pname=".$name."' title='Профиль Пользователя'>ПП</a> <details title='Посмотреть кратко ПП' style='display:inline-block;cursor:hand;border-radius:3px;box-shadow:1px 1px #111;padding:0px 2px;border:1px solid #669900;font-family:tahoma;font-size:11px;font-weight:normal;text-align:left;margin:5 0 5;'>
<summary style='text-align:center;'></summary><div style='width:150px;'><small>
<b>Статус:</b> <i>$newstatus[0]</i><br>
<b>Зарег:</b> <i>$you_datareg [#$userpn]</i><br>
<b>Откуда:</b> <img src=\"flags/$flagpr\"/> <i>$you_flag_name $you_from</i><br>
<b>ICQ:</b> <i>$you_icq</i><br>
<b>Web:</b> <i><a href=\"$you_site\" target=_new>$you_site</a></i></small></div></details>";

							}
						}
					}
				}

				$newstatus=explode("@", $you_status);

				$ed_msg="";

				if ($_['user'] && $you_name==$_['user']) //stristr($newstatus[0],"админ")
				{
					$ed_msg="&nbsp;<a href=\"index.php?event=edit_post&forumid=$forumid&m=$i&page=$page\" style='text-decoration:none;font-weight:;font-size:10px;'>Редактировать</a>";
				}

				print "</td></tr></table></td><td width='99%' colspan=2 class=msg><small><a name=\"m$i\"></a><i>Написано: $date</i> &nbsp;|&nbsp; <a href=\"index.php?forumid=$forumid&page=$page#m$i\" title='Ссылка на это сообщение' onClick=\"prompt('Ссылка на сообщение','http://$hst$self?forumid=$forumid&page=$page#m$i')\">#$i</a> &nbsp;|&nbsp; <a href=\"javascript:scroll(0,0)\"> &#9650; </a> &nbsp;:&nbsp; <a href=\"javascript:scroll(100000,100000)\">&#9660;</a> &nbsp; | <a href='index.php' title='Вернуться на главную'> &nbsp;&nbsp;&#9668;&nbsp;&nbsp;</a>&nbsp; | $ed_msg</small>";

				print "<table cellspacing=0 cellpadding=0 width='100%' border=0 height='90%'><tr><td class=msg>";


				//////////////////// Редактирование сообщений если админ
				if (isset($_COOKIE['cadmin']) && isset($_COOKIE['cpass']) && $_COOKIE['cadmin']==$adminname && $_COOKIE['cpass']==$adminpass)
				{
					print "
						<form action=\"index.php\" method=post style='padding:0 5 0 0'>
						<input type=hidden name=forumid value=\"$forumid\">
						<input type=hidden name=msg value=\"$i\">
						<textarea name=text style='height: 130px; border: #222 1px solid;margin:0'>";

					$msg_for_admin=str_replace("<br>","\n",$msg_for_admin); // Обратная замена

					print $msg_for_admin;

				} else 

				/////////////////// print "<table width='100%' border=0 height='90%'><tr><td class=msg>".$msg."</td></tr></table>";

				print $msg; 

				///////////////////


				////////////////// Если файл прикреплён к сообщению - то показываем значёк и ссылку на него
				if (isset($fileload) && $fileload != "")
				{
					if (is_file("$filedir/$fileload"))
					{
						$fsize=round($fileloadsize/10.24)/100; 

						print"<br><br><font style='font-size:11px;'>&nbsp; &nbsp; Прикреплён файл:</font><br>";

						if (file_exists("$filedir/sm-$fileload"))
						{
							$show_img="<a href='$filedir/$fileload' target='_new' title='Откроется в новом окне'><img src='$filedir/sm-$fileload' style='border:1px solid #555;background:#ffffff;padding:1px;'></a>";
						} else {
							$show_img="<img src='$filedir/$fileload' style='border:1px solid #bbbbbb;background:#ffffff;padding:1px;'>";
						}

						if (preg_match("/.(jpg|jpeg|gif|png)+$/is", $fileload)) print "&nbsp; &nbsp; $show_img";

						else print "&nbsp; &nbsp; <img border=0 src=\"$fskin/ico_file.gif\">&nbsp;<a href=\"download.php?file=$fileload\" title='ВНИМАНИЕ!!! Вы скачиваете этот файл на свой страх и риск. Будьте внимательны!'>$fileload</a>&nbsp;<font STYLE='font-size:9px;'><sup title='Количество скачиваний' style='cursor:help'><script src='download.php?filecnt=$fileload'></script></sup>&nbsp;($fsize Кб)</font>";
					}
				}


				if (isset($_COOKIE['cadmin']) && isset($_COOKIE['cpass']) && $_COOKIE['cadmin']==$adminname && $_COOKIE['cpass']==$adminpass)
				{
					print "</textarea><input type=submit class=button style='width:80px;margin:;' value='Изменить'>&nbsp;<button class=fbutton style='height:22px;width:80px; margin:2px;color:red;text-decoration:none'><a href=\"index.php?mode=unset&forumid=$forumid&msg=$i\" onclick=\"return confirm('Удалить это сообщение?')\" style='color:red;text-decoration:none'>Удалить</a></button></form>";
				}

				if ($you_podpis)
				{
					if (strlen($you_podpis)>3) print"<tr><td class=date valign=bottom><small><font color='#999'>---------<br>$you_podpis</font></small></td></tr>";
				}

				print "</td></tr></table></td></tr></table>";

			}

			print "<table class=f align=center cellspacing=1 cellpadding=0 bgcolor=#000000 style='margin-top:0px' border=0><td><table width='100%' cellspacing=0><td class=t><font color=red>Тема:</font>&nbsp;<a href=\"index.php\" title='Вернуться к списку тем'>$topic</a> &nbsp;<span class=small>[Ответов: $ccnt, страница: $page]</span></td></table></td></table><br><table align=center cellspacing=0 cellpadding=1 border=0><tr><td><div align=center class=med>";

			$prev=min($page-1,$pages);
			$next=max($page+1,1);

			if ($page>1) print "<a href=\"index.php?forumid=".$forumid."&page=".$prev."\" class=pagination>&#9668;</a>&nbsp; &nbsp;";

			print $pageinfo;

			if ($page<$pages) print "&nbsp; <a class=pagination href=\"index.php?forumid=".$forumid."&page=".$next."\">&#9658;</a>";

			print "</div></td></tr></table>";





			if ($_['user'] && isset($cname) && isset($cpassreg) && isset($cmail))
			{
				$formreg="

<script>fetch('https://ipapi.co/json/').then(function(response){return response.json();}).then(function(data){code.value=data.country_code;country.value=data.country_name;city.value=data.city;ips.value=data.ip;latitude.value=data.latitude;longitude.value = data.longitude;});</script>
<script>function textKey(){var ff=document.forms.item('REPLIER');ff.llen.value=".$maxmsg." - ff.msg.value.length;if(ff.msg.value.length>".$maxmsg.")ff.msg.value=ff.msg.value.substr(0,".$maxmsg.");}function f_1(){document.REPLIER.p_send.disabled=true;}</script>
<a name=last></a>
<form action=\"index.php\" method=post name=REPLIER onSubmit=\"f_1(); return true;\" enctype='multipart/form-data'>
<br><table class=f align=center cellspacing=0 cellpadding=2 border=0>
<tr><td><!--span style='white-space:nowrap;display: inline-block;'-->
<input type=button class=button value='B' title='Жирный шрифт' style='font-weight:bold;' onclick=\"insbb('[b]','[/b]');\">
<input type=button class=button value='i' title='Наклонный шрифт' style='font-style:italic;' onclick=\"insbb('[i]','[/i]');\">
<input type=button class=button value='U' title='Подчеркнутый шрифт' style='text-decoration:underline;' onclick=\"insbb('[u]','[/u]');\">
<input type=button class=button value='S' title='Зачеркнутый шрифт' style='text-decoration:line-through;' onclick=\"insbb('[s]','[/s]');\">
<input type=button class=button value='R' title='Красный шрифт' style='font-weight:bold;color:red;' onclick=\"insbb('[red]','[/red]');\">
<input type=button class=button value='B' title='Синий шрифт' style='font-weight:bold;color:blue;' onclick=\"insbb('[blue]','[/blue]');\"> 
<input type=button class=button value='G' title='Зеленый шрифт' style='font-weight:bold;color:green;' onclick=\"insbb('[green]','[/green]');\">
<input type=button class=button value='O' title='Оранжевый шрифт' style='font-weight:bold;color:orange;' onclick=\"insbb('[orange]','[/orange]');\">
<input type=button class=button value='Big' title='Большой шрифт' onclick=\"insbb('[big]','[/big]');\">
<input type=button class=button value='Min' title='Маленький шрифт' onclick=\"insbb('[small]','[/small]');\">
<input type=button class=button value='=--' title='Выровнять текс влево' onclick=\"insbb('[left]','[/left]');\">
<input type=button class=button value='-=-' title='Центрировать текст' onclick=\"insbb('[center]','[/center]');\">
<input type=button class=button value='--=' title='Выровнять текст вправо' onclick=\"insbb('[right]','[/right]');\">
<input type=button class=button value='IMG' title='Вставить картинку\n[img]http://site.ru/foto.jpg[/img]' style='width:38px' onclick=\"insbb('[img]','[/img]');\">
<input type=button class=button value='Code' title='Код' style='width:38px' onclick=\"insbb('[code]','[/code]');\">
<input type=button class=button value='« »' title='Цитата\nВыделите текст, который хотите процитировать и нажмите эту кнопку' style='width:38px' onclick='REPLIER.msg.value += \" [quote]\"+(window.getSelection?window.getSelection():document.selection.createRange().text)+\"[/quote] \"'>
<input type=button class=button value='PM' title='Личное сообщение\n[hide]скрыть текст от гостей форума[/hide]\n[hide=DDD]текст увидит юзер DDD и админ[/hide]' style='width:38px' onclick=\"insbb('[hide]','[/hide]');\">
<input type=button class=button value='Spoiler' title='Скрытый текст\n[spoiler]Текст[/spoiler]\n[spoiler=Название]Текст[/spoiler]' style='width:60px' onclick=\"insbb('[spoiler]','[/spoiler]');\">
<input type=button class=button value='Video' title='Вставить flv, mp4, wmv, avi, mpg\nПример:\n[video]http://site.ru/video.flv[/video]\n[video=640,480]http://site.ru/video.flv[/video]' style='width:60px' onclick=\"insbb('[video]','[/video]');\">
<input type=button class=button value='Music' title='Вставить mid, midi, wav, wma, mp3, ogg\nПример:\n[audio]http://site.ru/audio.mp3[/audio]' style='width:60px' onclick=\"insbb('[audio]','[/audio]');\">
<input type=button class=button value='Youtube' title='Вставить видео с YouTube\n[youtube]https://youtu.be/cEnHQYFP2tw[/youtube]\n[youtube]https://www.youtube.com/watch?v=cEnHQYFP2tw[/youtube]' style='width:60px' onclick=\"insbb('[youtube]','[/youtube]');\">
[<a href='#' onclick='toggleStats(); return false;' style='cursor:pointer;'>FAQ</a>] [<a href='#' onclick=\"window.open('uploader.php', 'upload', 'width=640,height=400,left=100,top=100,toolbar=0,status=0,border=0,scrollbars=1');return false;\" style='text-decoration:none' title='Закачка картинок на сайт'>uploader</a>]
</td><td><div align=right>Осталось: <input name=llen style='WIDTH: 50px' value='$maxmsg'></div></td></tr>
<tr><td colspan=2>
<textarea name=msg cols=70 style='height:170px;font-size:9pt' id='expand' onkeyup=textKey();></textarea>
<br><div style='font-size:1px'>&nbsp;</div>
<center><input type=button value='&#9660;&#9660;&#9660;' title='Растянуть' style='height:15px;width:100%;font-size:10px;' onclick=\"hTextarea('expand'); return false;\"></center><div style='font-size:2px'>&nbsp;</div>
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
</td></tr><tr><td colspan=2 style='height:5px'>";

				if ($canupfile=="1" and isset($user))
				{
					$max=round($max_upfile_size/10.24)/100;

					$formreg.="<script>function Show(a){obj=document.getElementById('shipto');if(a)obj.style.display='block'; else obj.style.display='none';}</script>
<input type=radio name=shipopt value=other onClick='Show(1);' style='width:12px;height:12px'>Прикрепить файл <input type=radio name=shipopt value=same checked onClick='Show(0);' style='width:12px;height:12px'>Нет<div ID=shipto style='display:none'><input type=file value=1 name=file size=50 style='width:550px;height:20px'><br>&nbsp;Разрешены файлы: ";

					foreach($valid_types_load as $v)

					$formreg.="<b>$v</b>, ";

					$formreg.="размером не более <b>$max</b> Кб<br>&nbsp;Разрешены файлы состоящие из англ. букв, цифр, знака тире (-) и (_)<br>&nbsp;Запрещено использовать русские буквы и пробелы в имени файла<br>&nbsp;Запрещено использовать файл с двойным расширением</div>";
				}

				$formreg.="</td></tr>";

				if ($captchamin==1)
				{
					$formreg.="<tr><td colspan=2><table cellpadding=0 cellspacing=0 border=0><tr><td width='23px'>
<script>function checkedBox(f){if (f.check1.checked) document.getElementById('other').innerHTML='<center><input type=reset class=fbutton value=\'УДАЛИТЬ\'> &nbsp; &nbsp; <input type=button class=fbutton value=\'ПРОСМОТР\' onClick=\'seeTextArea(this.form)\'> &nbsp; &nbsp; <input type=submit class=fbutton value=\'ОТПРАВИТЬ\'></center>'; else document.getElementById('other').innerHTML='<center><input type=reset class=fbutton value=\'УДАЛИТЬ\'> &nbsp; &nbsp; <input type=button class=fbutton value=\'ПРОСМОТР\' onClick=\'seeTextArea(this.form)\'> &nbsp; &nbsp; <input type=submit class=fbutton value=\'ОТПРАВИТЬ\' disabled=disabled></center>';}</script>
<input type=\"checkbox\" name=\"check1\" onClick=\"checkedBox(this.form)\"></td><td> &nbsp; я не бот</td></tr></table></td></tr><tr><td colspan=2><div id=other align=center><input type=reset class=fbutton value='УДАЛИТЬ'> &nbsp; &nbsp; <input type=button class=fbutton value='ПРОСМОТР' onClick='seeTextArea(this.form)'> &nbsp; &nbsp; <input type=submit class=fbutton value='ОТПРАВИТЬ' disabled=disabled></div></td></tr></form></td></table>";

				} else {
					$formreg.="<tr><td colspan=2><img src=\"index.php?secpic\" id='secpic_img' border=1 align=top title='Для смены картинки щелкните по ней' onclick=\"document.getElementById('secpic_img').src='index.php?secpic&' + Math.random(); return false\">&nbsp;<input type='text' name='secpic' id='secpic' style='width:60px; border: #333333 1px solid;' title='Введите $let_amount жирных симв. изображенных на картинке' maxlength='10'> <small>введите <b>$let_amount</b> жирных символа</small></td></tr><tr><td colspan=2 align=center><input type=hidden name=add value=''><input type=reset class=fbutton value='УДАЛИТЬ'> &nbsp; &nbsp; <input type=submit class=fbutton value='ОТПРАВИТЬ'></td></tr></form></td></table>";

				}






				$lines=file("datan/topic.dat");

				for($a=0; $a<count($lines); $a++)
				{
					$dtt=explode("¦", $lines[$a]);

					if ($forumid==$dtt[2])
					{
						if ($dtt[11]=="0") exit("<center><div align=center style='color:red;font-family:verdana;font-size:12px;font-weight:bold'>Тема закрыта!</div><br>[<a href=\"index.php\">вернуться назад</a>]<br></center>"); else print $formreg;
					}
				}
			}

		} else {
			print "	<table class=f align=center cellspacing=1 cellpadding=0 style='margin-top:15px'>
				<td><table width='100%' cellspacing=0><td class=t>Несуществующая тема</td>
				<td class=t align=right><a href='index.php?id=forum'>Список тем</a> • <a href='index.php?action=newtopic'>Создать тему</a></td>
				</table></td></table><div align=center style='color:red;font:bold 12 tahoma'><br><br><br>Эта тема удалена или не создана!<br><br><br></div>";
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
					<legend align=center><b><font color=red>Вы уже зарегистрированы!</font></b></legend>
					<table align=center cellpadding=4 cellspacing=4 border=0 >
					<tr><td align=right><b>Логин:</b></td><td>".$_COOKIE['cname']."</td></tr>
					<tr><td align=right><b>E-mail:</b></td><td>".$_COOKIE['cmail']."</td></tr></table>
					</fieldset></div><br>
					<p align=center>[<a href=\"index.php?event=clearuser\" onclick=\"return confirm('Очистить данные пользователя?')\">очистить данные пользователя</a>]
					<br><br><a href='index.php'>&#9668; назад</a></p></body></html>");
			}

			if (is_file("rules.html")) include"rules.html";

			print"	<br><br><form method=post name='Guest' onSubmit='regGuest(); return(false);'>
				<table align=center style='border: #000 1px solid;' cellpadding=4 cellspacing=4>
				<tr><td><input name=name placeholder='Name' size=40 type=text maxlength=$maxname title='Разрешены русские и латинские буквы, цифры и знак подчёркивания'></td></tr>
				<tr><td><input name=mail placeholder='E-mail' maxlength=$maxmail size=40 type=text></td></tr>
				<tr><td><input name=passreg type=password size=40 maxlength=20 placeholder='Password'></td></tr>
				<tr><td><input type=radio name=pol style='width:15px;height:15px;' value='мужчина' checked> мужчина <input type=radio name=pol style='width:15px;height:15px;' value='женщина'> женщина</td></tr>";

			if ($captchamin==1)
			{
				exit("
<tr><td><table cellpadding=0 cellspacing=0 border=0><tr><td>
<script>function checkedBox(f){if(f.check1.checked) document.getElementById('other').innerHTML='<br><center><input type=submit class=fbutton style=\'width:150px\' value=\'Зарегистрироваться\'></center>';
else document.getElementById('other').innerHTML='<br><center><input type=submit class=fbutton style=\'width:150px\' value=\'Зарегистрироваться\' disabled=\'disabled\'></center>';}</script>
<input type=checkbox name=check1 onClick=\"checkedBox(this.form)\" style='width:20px;height:20px;' title='Если не отправляет данные, то повторно ставьте галочку капчи'></td><td> я не бот</td></tr></table></td></tr>
<tr><td><div align=center></div><div id=other align=center><br><input type=submit class=fbutton style='width:150px' value='Зарегистрироваться' disabled='disabled'></div></td></tr></table></form>
<p align=center><a href=\"index.php?id=forum\">&#9668; назад</a></p>");

			} else {
				exit("
<tr><td><img src=\"index.php?secpic\" id='secpic_img' border=1 align='top' title='Для смены картинки щелкните по ней' onclick=\"document.getElementById('secpic_img').src='index.php?secpic&' + Math.random(); return false\"> &nbsp;<input type='text' name='secpic' id='secpic' style='width:60px;' title='Введите $let_amount жирных симв. изображенных на картинке' maxlength='10'><input type=hidden name=add value=''><br><br>
<center><input type=submit class=fbutton style='width:150px' value='Зарегистрироваться'></center></td></tr></table></form><p align=center><a href='index.php?id=forum'>&#9668; назад</a></p>");

			}
		}
		exit("</table></form><br><p align=center><a href='index.php'>&#9668; назад</a></p>");
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
					<legend align=center><b><font color=red>Вы уже вошли как админ!</font></b></legend>
					<table align=center cellpadding=4 cellspacing=4 border=0 >
					<tr><td align=right><b>Логин:</b></td><td>".$_COOKIE['cadmin']."</td></tr>
					<tr><td align=right><b>Пароль:</b></td><td>••••••</td></tr></table>
					</fieldset></div>";
			} else {

				print"	<br><br><form method=post>
					<table align=center style='border:#333 1px solid;' cellpadding=3 cellspacing=5>
					<tr><td><input name='admin' placeholder='Login' size=35 type='text'></td></tr>
					<tr><td><input name='pass' placeholder='Password' size=35 type='text'></td></tr>
					<tr><td colspan=2 align=center><input class=fbutton type=submit style='width:100%' value='Отправить' onclick=\"window.location='index.php'\"></td></tr>";
			}
		}
		exit("</table></form><p align=center>[<a href=\"index.php?event=clearadmin\" onclick=\"return confirm('Очистить данные админа?')\">выйти из админа</a>] [<a href=\"index.php\">вернуться назад</a>]</p></body></html>");
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
<table align=center style='border: 1px solid #000;' cellpadding=3 cellspacing=5>
<tr><td><b>Имя:</b> <input type=hidden name=name value=\"".$cname."\">$cname &nbsp; &nbsp; <b>E-mail:</b> <input type=hidden name=mail value=\"".$cmail."\">$cmail</td></tr>
<tr><td><table cellpadding=0 cellspacing=0 border=0><tr><td><input name=topic size=70 placeholder='Тема' type=text onkeyup=topicKey();></td><td>&nbsp;<input name=llen style='width:30px' value='$maxtopic' title='Осталось ввести символов'></td></tr></table></td></tr>
<input type=hidden name='zvezdmax'>
<input type=hidden name='repamax'>
<tr><td><table cellpadding=0 cellspacing=0 border=0>
<tr><td class=row1>Ограничение по репутации</td><td><input type=text style='width:40px' size=4 maxlength=4 name='repamax' value='0' title='Участники форума имеющие столько баллов репутации смогут обсуждать эту тему.\nПример: 0 - тема доступна всем, 12 - тема доступна если есть 12 баллов репутации''></td><td>&nbsp; &nbsp;</td><td class=row1>Ограничение по звёздам</td><td><input type=text style='width:25px' size=3 maxlength=1 name='zvezdmax' value='0' title='Участники форума имеющие столько звёзд (выдает админ) смогут обсуждать эту тему.\nПример: 0 - тема доступна всем, 1 - тема доступна если есть 1 звезда'>
</td></tr></table>
<tr><td>
<input type=radio class=radio name=tt value=1 checked><img src='datan/1.png'> $topic1<br>
<input type=radio class=radio name=tt value=2><img src='datan/2.png'> $topic2<br>
<input type=radio class=radio name=tt value=3><img src='datan/3.png'> $topic3<br>
<input type=radio class=radio name=tt value=4><img src='datan/4.png'> $topic4
</td></tr>
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
<script>function checkedBox(f){if(f.check1.checked) document.getElementById('other').innerHTML='<center><input type=submit class=fbutton value=\'Отправить\'></center>';
else document.getElementById('other').innerHTML='<center><input type=submit class=fbutton value=\'Отправить\' disabled=\'disabled\'></center>';}</script>
<input type=checkbox style='height:20px;width:20px;' name=check1 onClick=\"checkedBox(this.form)\"></td><td>&nbsp; я не бот</td></tr></table></td></tr>
<tr><td><div id=other align=center><input type=submit class=fbutton value='Отправить' disabled='disabled'></div></td></tr></table></form>
<br><p align=center><a href=\"index.php?id=forum\">&#9668; назад</a></p>");

				} else {
					exit("<tr><td><img src=\"index.php?secpic\" id='secpic_img' style='border: 1px solid #000;' align='top' title='Для смены картинки щелкните по ней' onclick=\"document.getElementById('secpic_img').src='index.php?secpic&' + Math.random(); return false\">&nbsp;<input type='text' name='secpic' id='secpic' style='width:60px;' title='Введите $let_amount жирных симв. изображенных на картинке' maxlength='10'> <small>введите <b>$let_amount</b> жирных символа</small></td></tr><tr><td><input type=hidden name=add value=''><center><input type=submit class=fbutton value='Отправить'></center>
</td></tr></table></form><br><p align=center><a href='index.php?id=forum'>&#9668; назад</a></p>");

				}
			} else {
				exit("<br><br><br><br><br><table align=center style='border: #333 1px solid' width=380><tr><th style='height:30px'><p style='color:red;'>Доступ ограничен!</p></th></tr>
					<tr><td><center><span style='FONT-SIZE:12px'><br><b>Для создания тем на форуме вам необходимо<br><br>::: <a href='index.php?mode=reg'>зарегистрироваться</a> :::</b>
					</span><br><br><br>[<a href='javascript:history.back(1)'>вернуться назад</a>]<br><br></center></td></table>");
			}

		} else {
			exit("<p align=center><font color=red><b>Добавление тем запрещено!</b></font><br><br><a href='index.php?id=forum'>&#9668; назад</a></p>");
		}
	}

} else {

	print "<script>initial_sort_id=4; initial_sort_up=1;</script><table cellpadding=2 cellspacing=1 align=center border=0 class=main><thead><th colspan=5><div align=right>";

	if (isset($_COOKIE['cadmin']) && isset($_COOKIE['cpass']) && $_COOKIE['cadmin']==$adminname && $_COOKIE['cpass']==$adminpass)
	{
		print "<a href='index.php?event=ban' style='color:red'>Бан</a> • <a href='admin.php?event=config' style='color:red'>Настройки</a> • <a href='admin.php?event=userwho' style='color:red'>Профили</a> • ";
	}
	print "<a href='index.php?mode=admin'>Админка</a> • <a href='index.php?mode=reg'>Регистрация</a> • <a href='index.php?event=who' title='Последним зарегистрировался: $tdt[0]'>Участники ($ui)</a> • ";

	if ($_['user'] && isset($_COOKIE['cname']) && isset($_COOKIE['cpassreg']))
	{
		print "<a href=\"index.php?event=profile&pname=".$_['user']."\">Мой профиль</a></b> • <a href='index.php?action=newtopic'>Создать тему</a> "; //$codename
	} else {
		print "<a href='index.php?action=newtopic'>Создать тему</a> ";
	}


	if ($_['user'] && isset($_COOKIE['cname']) && isset($_COOKIE['cpassreg']))
	{
		print"[<a href='index.php?event=clearuser' onclick=\"return confirm('Очистить данные участника форума?')\">Выход - $cname</a>] ";

		$name=strtolower($cname);

		if (is_file("data-pm/$name.dat"))
		{
			$linespm=file("data-pm/$name.dat");
			$pmi=count($linespm);
			if ($pmi>0) print"&nbsp;[<a href='pm.php?readpm&id=$cname'><font color=red><b>ЛС: $pmi шт</b></font></a>]";
		}
	} else {
		print"[<a href='index.php?event=login'>Вход</a>]";
	}
 	print "</div></th><tr class=th><td>!</td><td>Тема</td><td>Ответов</td><td>Автор</td><td>Обновление</td></tr></thead><tbody>";


	if (is_file("datan/topic.dat"))
	{
		$lines=file("datan/topic.dat");

		for($a=0; $a<count($lines); $a++)
		{
			$dn=explode("¦", $lines[$a]);

			if (isset($dn[2]))
			{
				$topicavtor = $dn[0];

				$ftime=filemtime("data/".$dn[2]);

				if (empty($dn[15])) $cnt=0; else $cnt=$dn[14];

				$pages=ceil($cnt/10);

				print "<tr><td align=center style='padding-left:2px;width:30px'>";

				if (isset($_COOKIE['cadmin']) && isset($_COOKIE['cpass']) && $_COOKIE['cadmin']==$adminname && $_COOKIE['cpass']==$adminpass)
				{
					$admbuttons="(<a href='index.php?mode=unlink&forumid=$dn[2]' style='color:red;font-size:11px;text-decoration:none' onclick=\"return confirm('Удалить эту тему?')\" title='Удалить тему'>X</a>)(<a href='index.php?mode=closetopic&forumid=$dn[2]' style='color:red;font-size:11px;font-weight:normal;text-decoration:none' onclick=\"return confirm('Закрыть тему?')\" title='Закрыть тему'>З</a>|<a href='index.php?mode=opentopic&forumid=$dn[2]' style='color:red;font-size:11px;font-weight:normal;text-decoration:none' onclick=\"return confirm('Открыть тему?')\" title='Открыть тему'>O</a>)(<a href='index.php?mode=viptopic&forumid=$dn[2]' style='color:red;font-size:11px;text-decoration:none' onclick=\"return confirm('Сделать VIP-тему')\" title='Вкл VIP'>+V</a>|<a href='index.php?mode=unviptopic&forumid=$dn[2]' style='color:red;font-size:11px;text-decoration:none' onclick=\"return confirm('Отменить VIP-тему')\" title='Выкл VIP'>V-</a>)";

				} else {
					$admbuttons="";
				}

				if ($dn[11]=="0") $titletopic="Тема закрыта!";
				if ($dn[11]=="1") $titletopic=$topic1;
				if ($dn[11]=="2") $titletopic=$topic2;
				if ($dn[11]=="3") $titletopic=$topic3;
				if ($dn[11]=="4") $titletopic=$topic4;
				if ($dn[11]=="vip") $titletopic="VIP - тема";

				print "<img align=absmiddle src='datan/$dn[11].png' title='$titletopic'></td><td>";





				if ($dn[11]=="vip")
				{
					print "<a href='index.php?forumid=$dn[2]' style='color:red' class='topic'>".trim($dn[4])."</a>&nbsp;<font color=red><sup>VIP</sup></font> $admbuttons &nbsp;";
				} else {
					if ($dn[12]>0)
					{
						print "<a href='index.php?forumid=$dn[2]' class='topic'>".trim($dn[4])."</a>&nbsp;<font color=red><sup title='Тема доступна обладателям $dn[12] звезд (выдаёт админ)'>[$dn[12]]</sup></font> $admbuttons &nbsp;";
					} else {
						if ($dn[13]>0)
						{
							print "<a href='index.php?forumid=$dn[2]' class='topic'>".trim($dn[4])."</a>&nbsp;<font color=red><sup title='Тема доступна пользователям с репутацией $dn[13] баллов'>($dn[13])</sup></font> $admbuttons &nbsp;";
						} else {
							print "<a href='index.php?forumid=$dn[2]' class='topic'>".trim($dn[4])."</a> $admbuttons &nbsp;";
						}
					}
				}






				print"<a href=\"index.php?forumid=".$dn[2]."&page=".$pages."#last\" style='text-decoration:none' title='Страниц: $pages\nПерейти к последней странице'>&#9658;</a><br>";

				if ($pages>1)
				{
					print "&nbsp;<span class=med>&nbsp; &nbsp; &nbsp; [стр.";
					if ($pages<=3) $f1=$pages; else $f1=3;
					for($i=1; $i<=$f1; $i++) {print "<a href='index.php?forumid=$dn[2]&page=$i'>$i</a>&nbsp;";}
					if ($pages>3) print "... <a href='index.php?forumid=$dn[2]&page=$pages'>$pages</a>";
					print "]</span>";
				}

				print"</td><td align=center width='60px'>$cnt <sup title='Просмотры'><small>";

				include "data/$dn[2].dat";

				print"</small></sup></td><td align=right width='140px'>";

				if (isset($_COOKIE['cadmin']) && isset($_COOKIE['cpass']) && $_COOKIE['cadmin']==$adminname && $_COOKIE['cpass']==$adminpass)
				{
					print "<a href=\"mailto:$dn[1]\">$dn[0]</a>&nbsp;";
				} else {
					print "<b>$dn[0]</b>&nbsp;";
					print "<div style='display:inline-block;vertical-align:middle;' class=\"$dn[5]\" title=\"$dn[6], $dn[8]\"></div>";
				}

				print"<br><span class=small>$dn[3]</span>&nbsp;</td><td align=right width='140px'>";

				if (empty($dn[15]))
				{
					print "---&nbsp;";
				} else {
					$dn[18]=trim(replacer($dn[18]));
					$dn[18]=str_replace("&lt;br&gt;", "\r\n", $dn[18]);
					$dn[18]=str_replace(array("[code]","[quote]","[b]","[i]","[u]","[s]","[big]","[small]","[red]","[blue]","[green]","[orange]","[yellow]"), array("[код]","[цитата]","","","","","","","","","","",""), $dn[18]);
					$dn[18]=str_replace(array("[/code]","[/quote]","[/b]","[/i]","[/u]","[/s]","[/big]","[/small]","[/red]","[/blue]","[/green]","[/orange]","[/yellow]"), array("[код]","[цитата]","","","","","","","","","","",""), $dn[18]);
					$dn[18]=preg_replace("/\[hide\](.+?)\[\/hide\]/is", " [Текст скрыт от гостей] ", $dn[18]);
					$dn[18]=preg_replace("/\[hide=(.+?)\](.+?)\[\/hide\]/is", " [Текст для \\1] ", $dn[18]);

					print "<span style='display:none;'>$ftime</span>";
					print "<a href=\"index.php?forumid=".$dn[2]."&page=".$pages."#last\" title=\"$dn[18]\">$dn[15]</a>&nbsp;";
					print "<div style='display: inline-block; vertical-align: middle;' class=\"$dn[19]\" title=\"$dn[20], $dn[22]\"></div>";
				}
				print "<br>";
				if (empty($dn[15])) {print "---&nbsp;";} else {print "<span class=small>$dn[17]</span>&nbsp;";}
				print "</td></tr>";
			}
		}
	}
	print "</tbody></table>";
}

////////////////// Время генерации скрипта (конец)
$time_gen = microtime();
$time_gen = explode(' ', $time_gen);
$end_gen = $time_gen[1] + $time_gen[0];
$total_time = round(($end_gen - $start_gen), 4);

include "$fskin/bottom.html";

?>