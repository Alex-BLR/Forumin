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
_/   https://t.me/voxlive
_/
_/   Движок EI форум Copyright (c) 2004 Эдюха 
_/
_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/
*/

error_reporting(0);
//error_reporting(E_ALL);

session_start();

require "config.php";
require "functions.php";

$valid_types_load = array("z", "zip", "rar", "7z", "jpg", "jpeg", "gif", "png"); //Расширения загружаемых файлов
$valid_types = array("gif", "jpg", "png", "jpeg"); //Расширения загружаемых аватаров
$maxfsize = round($max_file_size/10.24)/100; //Допустимый вес аватара Кб

$hst = $_SERVER["HTTP_HOST"];
$self = $_SERVER["PHP_SELF"];
$furl = str_replace('index.php', '', "http://$hst$self");

$ip = replacer(getIP());

////////////////// Время генерации скрипта (начало)
$time_gen = microtime();
$time_gen = explode(' ', $time_gen);
$start_gen = $time_gen[1] + $time_gen[0];


// Системный массив, используем как буфер
$_ = array();

// После проверки кук проверяем на пользователя чтобы лишний раз не вызывать функции
$_['user'] = $user = is_user();


////////////////// Часовая поправка
$timezone=floor($timezone);
if ($timezone<-12 || $timezone>12) $timezone = 0;


////////////////// Счетчик посещений
$num = 8;
$counter_file = "datan/counter.dat";
$cfile = (int)file_get_contents($counter_file);

if (empty($_COOKIE['countplus'])) {
	$cfile++;
	file_put_contents($counter_file, (string)$cfile, LOCK_EX);
	setcookie('countplus', '1', time() + 86400, '/');
}
$cnum = str_pad((string)$cfile, $num, '0', STR_PAD_LEFT);



////////////////// Капча сложная
if (isset($_REQUEST['add'])) {
	if (strtolower($_REQUEST['secpic']) !=$_SESSION['secpic']) {
		@header("Content-type: text/html; charset=windows-1251");
		show_error("Неверно введен защитный код!", "javascript:history.back()");
	}
}
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
	for($i = 0; $i < $let_amount; $i++) {
		$color = imagecolorallocatealpha($src, $colors[rand(0, count($colors)-1)], $colors[rand(0, count($colors)-1)], $colors[rand(0, count($colors)-1)], rand(20, 40));
		$font = $path_fonts . $fonts[rand(0, count($fonts)-1)];
		$letter = $letters[rand(0, count($letters)-1)];
		$cod[] = $letter;
		$size = rand($font_size * 1.5, $font_size * 1.8); 
		$x = ($i * ($width / $let_amount)) + ($width / $let_amount / 4);
		$y = ($height / 2) + ($size / 3); 
		imagettftext($src, $size, rand(0,15), $x, $y, $color, $font, $letter);
	}
	$_SESSION['secpic'] = implode('', $cod);
	header("Cache-Control: no-cache, must-revalidate");
	header("Content-type: image/png");
	imagepng($src);
	imagedestroy($src);
}

////////////////// Бан по IP
if ($antiham == 1) {
	$bad_ips_raw = file_get_contents("datan/badip.dat");
	if ($bad_ips_raw) {
		$bad_ips = explode(' ', trim($bad_ips_raw));
		if (in_array($ip, $bad_ips)) show_error("Администратор запретил вам пользоваться форумом!", "javascript:history.back()");
	}
}


////////////////// Бан по IP перенесён ниже. Если код будет здесь, то юзер не сможет читать тему 


////////////// Просмотры (клики)
if (isset($_GET['forumid'])) {
	$raw_id = $_GET['forumid'];

	//Проверяем ID (стандарт MD5) и длину 32 символа
	if (preg_match('/^[a-f0-9]{32}$/', $raw_id)) {
		$forumid = $raw_id;
		$clickfile = "data/" . $forumid . ".dat";

		if (file_exists($clickfile)) {
			$cookie_val = isset($_COOKIE['last_forum']) ? $_COOKIE['last_forum'] : '';
			if ($cookie_val !== $forumid) {
				setcookie('last_forum', $forumid, time() + 3600, '/', $_SERVER['HTTP_HOST']);
				$count = (int)@file_get_contents($clickfile);
				$count++;
				file_put_contents($clickfile, $count, LOCK_EX);
			}
		}
	}
}
//$countcl = file_get_contents($clickfile);


////////////////// Юзер - Админ - Выход очищаем куки
if (isset($_GET['event'])) {
	$event = $_GET['event'];
	$past = time() - 3600; // Время в прошлом для удаления

	if ($event == "clearuser") {
		setcookie("cname", "", $past, "/");
		setcookie("cmail", "", $past, "/");
		setcookie("cpassreg", "", $past, "/");
		header("Location: index.php");
		exit;
	} 
	elseif ($event == "clearadmin") {
		setcookie("cadmin", "", $past, "/");
		setcookie("cpass", "", $past, "/");
		header("Location: index.php");
		exit;
	}
}


////////////////// Регистрация
if (isset($_GET['mode']) and $_GET['mode']=="reg")
{
	if (isset($_POST['name']) && isset($_POST['mail']) && isset($_POST['passreg']))
	{
		$name = trim(str_replace("|", '', $_POST['name']));
		$mail = trim(str_replace("|", '', $_POST['mail']));
		$passreg = trim(str_replace("|", '', $_POST['passreg']));
		$userstatus = str_replace("|", '', $userstatus);
		$datee = gmdate('d.m.Y', time() + 3600*($timezone+(date('I')==1?0:1)));

		if (preg_match("/[^(\\w)|(\\x7F-\\xFF)|(\\-)]/", $name)) show_error("Разрешены русские и англ. буквы, цифры, подч, тире");
		if ($name == "" or strlen($name)>$maxname or strlen($name)<3) show_error("Имя пользователя должно быть от 3 до $maxname симв.");
		if ($passreg == "" or strlen($passreg)<3 or strlen($passreg)>10) show_error("Пароль должен быть от 3 до 10 символов!");

		//В PHP 5.2+ встроенная функция проверки Email
		//if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) exit("Email неверный");

		if (!preg_match("/^[a-z0-9\.\-_]+@[a-z0-9\-_]+\.([a-z0-9\-_]+\.)*?[a-z]+$/is", $mail) or $mail=="" or strlen($mail)>$maxmail) show_error("Ваш Email некорректный либо больше $maxmail символов!");

		if (isset($_POST['pol'])) $pol = trim($_POST['pol']); else $pol = "";

		if ($pol != "мужчина") $pol = "женщина";

		/////////////// Ищем юзера с таким логином или емайлом
		$loginsm = strtolower($name);
		$lines = file("datan/usersdat.php");
		$i = count($lines);
		if ($i>"1") {
			do {
				$i--;
				$rdt = explode("|",$lines[$i]); 
				$rdt[0] = strtolower($rdt[0]);
				if ($rdt[0] === $loginsm) {$bad="1"; $er="именем";}
				if ($rdt[3] === $mail) {$bad="1"; $er="почтовым адресом";}
			} while($i > 1);

			if (isset($bad)) show_error("Участник с таким $er уже зарегистрирован!");
		}
		$text="$name|$passreg|0|$mail|$datee||$pol||||||noavatar.gif|$userstatus||||";
		$text=replacer($text);

		setcookie("cname", $name, time() + 86400 * 365, "/");
		setcookie("cmail", $mail, time() + 86400 * 365, "/");
		setcookie("cpassreg", md5($passreg), time() + 86400 * 365, "/");

		/////////////// Записываем файл с юзерами
		$fp=fopen("datan/usersdat.php","a+");
		flock($fp,LOCK_EX);
		fputs($fp,"$text\r\n");
		fflush($fp);
		flock($fp,LOCK_UN);
		fclose($fp);

		/////////////// Записываем файл со статистикой
		$fp=fopen("datan/userstat.dat","a+");
		flock($fp,LOCK_EX);
		fputs($fp,"$name|0|0|0|0|||||\r\n");
		fflush($fp);
		flock($fp,LOCK_UN);
		fclose($fp);

		$riuser="<meta http-equiv='pragma' content='no-cache'><br><br><br><div align=center><fieldset align=center style='width:300px;border:#333 1px solid;'>
			<legend align=center style='border:#333 1px solid;background-color:#999;color:green;padding:2px 2px;'><b>Регистрация прошла успешно!</b></legend>
			<table align=center cellpadding=4 cellspacing=4 border=0><tr><td align=right><b>Логин:</b></td><td>".htmlspecialchars($name)."</td></tr>
			<tr><td align=right><b>Пароль:</b></td><td>".htmlspecialchars($passreg)."</td></tr><tr><td align=right><b>E-mail:</b></td><td>".htmlspecialchars($mail)."</td></tr>
			</table></fieldset></div>";
	}
}


////////////////// Админка
if (isset($_GET['mode']) && $_GET['mode'] == "admin")
{
	if (isset($_POST['admin']) && isset($_POST['pass']))
	{
		$admin = $_POST['admin'];
		$pass = $_POST['pass'];

		if ($admin === $adminname && md5($pass) === $adminpass)
		{
			setcookie("cadmin", $admin, time()+86400*365, "/");
			setcookie("cpass", md5($pass), time()+86400*365, "/");

			$riadmin="<meta http-equiv='pragma' content='no-cache'><br><br><br>
				<div align=center>	<fieldset align=center style='width:300px; border: #333 1px solid;'>
				<legend align=center><b><font color=red>Вы в режиме администратора!</font></b></legend>
				<table align=center cellpadding=4 cellspacing=4 border=0>
				<tr><td align=right><b>Логин:</b></td><td>".htmlspecialchars($admin)."</td></tr>
				<tr><td align=right><b>Пароль:</b></td><td>".htmlspecialchars($pass)."</td></tr>
				</table></fieldset></div>";
		}
	}
}


/////////////// Вход на форум проверка имени/пароля
if (isset($_GET['event']) && $_GET['event'] == "regenter")
{
	if (empty($_POST['name']) || empty($_POST['passreg'])) show_error("Введите имя и пароль!", "index.php?event=login");

	$name = str_replace("|", '', trim($_POST['name']));
	$pass = str_replace("|", '', trim($_POST['passreg']));
	$text = trim(replacer("$name|$pass|"));

	$exd = explode("|", $text);
	$name = $exd[0];
	$pass = $exd[1];

	$lines = file("datan/usersdat.php");

	for ($i = count($lines) - 1; $i >= 1; $i--)
	{
		$rdt = explode("|", trim($lines[$i]));
		if (isset($rdt[1]))
		{
			if ($name === $rdt[0] && $pass === $rdt[1])
			{
				$regenter = true;
				$cmail = $rdt[3];
				setcookie("cname", $name, time() + 86400 * 365, "/");
				setcookie("cmail", $cmail, time()  + 86400 * 365, "/");
				setcookie("cpassreg", md5($pass), time() + 86400 * 365, "/");
				break;
			}
		}
	}
	if (!$regenter) show_error("Введите имя и пароль!", "index.php?event=login");
	header("Location: index.php");
	exit;
}


/////////////// РЕДАКТИРОВАНИЕ ПРОФИЛЯ - сохранение данных
if (isset($_GET['event']))
{
	if ($_GET['event']=="reregist")
	{
		if (!isset($_POST['name'])) show_error("Вы не ввели своё имя!", "javascript:history.back(1)");

		$name=trim(str_replace("|", '', $_POST['name']));

		if ($name=="" or strlen($name)>$maxname or strlen($name)<3) show_error("Имя должно быть от 3 до $maxname символов!", "javascript:history.back(1)");

		if (preg_match("/[^(\\w)|(\\x7F-\\xFF)|(\\-)]/", $name)) show_error("Разрешены русские и англ. буквы, цифры, подч, тире", "javascript:history.back(1)");

		if (!isset($_POST['pass'])) show_error("Допускается длина пароля от 3 до 10 симв.", "javascript:history.back(1)");

		$pass = replacer(str_replace("|", '', $_POST['pass']));
		$oldpass = $_POST['oldpass'];

		if (strlen($pass)<3 or strlen($pass)>10) show_error("Допускается длина пароля от 3 до 10 симв.", "javascript:history.back(1)");

		if (isset($_POST['email'])) $email = strtolower($_POST['email']); else $email = "";

		if (!preg_match("/^[a-z0-9\.\-_]+@[a-z0-9\-_]+\.([a-z0-9\-_]+\.)*?[a-z]+$/is", $email) or $email=="" or strlen($email)>$maxmail) show_error("Введенный Email некорректный или превышает $maxmail симв", "javascript:history.back(1)");

		if (isset($_POST['dayx'])) $dayx=replacer($_POST['dayx']); else $dayx="";
		if (isset($_POST['pol'])) $pol=replacer($_POST['pol']); else $pol="";
		if ($pol!="мужчина") $pol="женщина";
		if (isset($_POST['icq'])) $icq=replacer($_POST['icq']); else $icq="";
		if (isset($_POST['telegram'])) $telegram=replacer($_POST['telegram']); else $telegram="";
		if (isset($_POST['www'])) $www=replacer($_POST['www']); else $www="";
		if (isset($_POST['about'])) $about=replacer($_POST['about']); else $about="";
		if (isset($_POST['work'])) $work=replacer($_POST['work']); else $work="";
		if (isset($_POST['write'])) $write=replacer($_POST['write']); else $write="";
		if (isset($_POST['avatar'])) $avatar=replacer($_POST['avatar']); else $avatar="";
		if (isset($_POST['cflag'])) $cflag=replacer($_POST['cflag']); else $cflag="";

		if (strlen($dayx)>10) show_error("Введено много данных ДЕНЬ РОЖДЕНИЯ (макс 10 симв)", "javascript:history.back(1)");
		if (strlen($icq)>12) show_error("Введено много данных KICQ (макс 12 симв)", "javascript:history.back(1)");
		if (strlen($telegram)>40) show_error("Введено много данных Telegram (макс 40 симв)", "javascript:history.back(1)");
		if (strlen($www)>40) show_error("Введено много данных САЙТ (макс 40 симв)", "javascript:history.back(1)");
		if (strlen($about)>50) show_error("Введено много данных ОТКУДА (макс 50 симв)", "javascript:history.back(1)");
		if (strlen($work)>100) show_error("Введено много данных ИНТЕРЕСЫ (макс 100 симв)", "javascript:history.back(1)");
		if (strlen($write)>150) show_error("Введено много данных ПОДПИСЬ (макс 150 симв)", "javascript:history.back(1)");

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

		// проверка Логина/Старого пароля
		$ok=null;
		$lines=file("datan/usersdat.php");
		$i=count($lines);
		unset($ok);
		do {
			$i--;
			$rdt=explode("|", $lines[$i]);

			if (strtolower($name) === strtolower($rdt[0]) & $oldpass === $rdt[1])
			{
				$ok="$i";
			} else {
				if ($email === $rdt[3]) $bademail="1"; // Если уже есть такой емайл?
			}
		} while($i > "1");

		if (isset($bademail)) show_error("Пользователь с таким емейлом уже зарегистрирован", "javascript:history.back(1)");

		if (!isset($ok))
		{
			setcookie("cname", "", time(), "/");
			setcookie("cmail", "", time(), "/");
			setcookie("cpassreg", "", time(), "/");

			show_error("Смена электронного адреса запрещена!", "javascript:history.back(1)");
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

			if (!in_array($ext, $valid_types)) show_error("Файл не загружен. Неверно введён адрес или выбран файл!", "javascript:history.back(1)");
		}

		$text="$name|$pass|$kolvomsg|$email|$dayreg|$dayx|$pol|$icq|$www|$about|$work|$write|$avatar|$status|$cflag||$telegram|";
		$text=replacer($text);
		$exd=explode("|",$text);
		$name=$exd[0];
		$pass=$exd[1];
		$email=$exd[3];

		// Ставим куку юзеру
		$tektime=time();

		@setcookie("cname", $name, (time() + 86400 * 365), "/");
		@setcookie("cmail", $email, (time() + 86400 * 365), "/");
		@setcookie("cpassreg", md5($pass), (time() + 86400 * 365), "/");

		if ($_FILES['file']['name']!="")
		{
			// 1. считаем кол-во точек
			$findtchka = substr_count($fotoname, ".");

			if ($findtchka>1) show_error("В имени файла есть точки $findtchka раза", "javascript:history.back(1)");

			// 2. если в имени есть .php и др.
			if (preg_match("/\.php|\.htm|\.html|\.mht|\.mhtml|\.hta|\.vb|\.vbs|\.vbe|\b\.js\b|\b\.jse\b|\b\.jar\b/i", $fotoname)) show_error("Ваш файл имеет запрещённое расширение!", "javascript:history.back(1)");

			// 3. защищаем от РУССКИХ букв в имени файла и проверяем расширение файла 
			if (!preg_match("/^[a-z0-9\.\-_]+\.(jpg|gif|png|jpeg)+$/is",$fotoname)) show_error("Запрещено использовать русские буквы в имени файла!", "javascript:history.back(1)");

			// 4. Проверяем, может быть файл с таким именем уже есть
			if (file_exists("./avatars/$fotoname")) show_error("Файл с таким именем уже существует на сервере!", "javascript:history.back(1)");

			// 5. Размер в Кб < допустимого
			$fotoksize=round($fotosize/10.24)/100; //Размер ЗАГРУЖАЕМОГО ФОТО Кб
			$fotomax=round($max_file_size/10.24)/100; //Макс размер фото Кб

			if ($fotoksize>$fotomax) show_error("Вы превысили допустимый размер: $fotomax Кб. Ваша картинка: $fotoksize Кб", "javascript:history.back(1)");

			// 6. Габариты аватара
			$size=getimagesize($_FILES['file']['tmp_name']);

			if ($size[0]>$avatar_width or $size[1]>$avatar_height) show_error("Превышены допустимые размеры аватара $avatar_width х $avatar_height px", "javascript:history.back(1)");

			if ($fotosize>"0" and $fotosize<$max_file_size)
			{
				copy($_FILES['file']['tmp_name'], avatars."/".$fotoname);

				print "<br><br><br><center><font size=2 face=tahoma><b>Фото успешно загружено: $fotoname ($fotosize байт)</b><br><p align=center><a href='javascript:history.back(1)' style='text-decoration:none;'>&#9668; назад</a></p>";
			} else {

				show_error("Если вы видите сообщение: Filename cannot be empty значит библиотека GD отсутствует либо старой версии. Возможно, доступ к папке для загрузки выставлен ошибочно или хостер запретил загрузку файлов через http", "javascript:history.back(1)");
			}
		}

		$file=file("datan/usersdat.php");
		$fp=fopen("datan/usersdat.php","a+");
		flock($fp,LOCK_EX);
		ftruncate($fp,0);
		for ($i=0;$i<sizeof($file); $i++) {
			if ($ok!=$i) fputs($fp,$file[$i]); else fputs($fp,"$text\r\n");
		}
		fflush($fp);
		flock($fp,LOCK_UN);
		fclose($fp);

		if ($_COOKIE['cadmin'] === $adminname & $_COOKIE['cpass'] === $adminpass)
		{
			 exit("<meta charset='windows-1251'><script>function reload(){location=\"index.php?event=clearuser\"};setTimeout('reload()',5000);</script>
<table width=100% height=80%><tr><td><table border=1 cellpadding=5 cellspacing=0 bordercolor=#224488 align=center valign=center width=450 height=90>
<tr><td><center><font size=2 face=tahoma><B>Регистрационные данные <font color=red>$name</font> успешно изменены!<BR><BR>
<a href='index.php?event=clearuser' style='text-decoration:none;'>Продолжить</a></B></font><BR></td></tr></table></td></tr></table></center>");

		} else {
			 exit("<meta charset='windows-1251'><script>function reload(){location=\"javascript:history.back(1)\"};setTimeout('reload()',1000);</script>
<table width=100% height=80%><tr><td><table border=1 cellpadding=5 cellspacing=0 bordercolor=#224488 align=center valign=center width=450 height=90>
<tr><td><center><font size=2 face=tahoma><B>Регистрационные данные <font color=red>$name</font> успешно изменены!<BR><BR>
<a href='javascript:history.back(1)' style='text-decoration:none;'>Продолжить</a></B></font><BR></td></tr></table></td></tr></table></center>");

		}
	}
}


////////////////// Начало 1

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


////////////////// Блок запрета/доступа к теме. Юзер не сможет читать тему форума
if (is_file("data/".$forumid.".user"))
{
	$tub = explode('|', file_get_contents("data/$forumid.user"));

	if (!empty($tub[0]) & $tub[2]=="1")
	{
		if (preg_match("/\b".$user."\b/i", $tub[0])) show_error("Автор темы установил запрет на чтение!", "index.php");
	}
	if (!empty($tub[1]) & $tub[3]=="1")
	{
		if (!preg_match("/\b".$user."\b/i", $tub[1])) show_error("Читать тему могут только пользователи: $tub[1]", "index.php");
	}
}


////////////////// Начало 2

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


/////////// Ответ в теме
if (isset($name) && isset($cname) && isset($msg) && isset($email) && isset($_POST['forumid']) && isset($_POST['action']) && $_POST['action']=="answer")
{
	if (strlen($msg)>2 && strlen($msg)<$maxmsg)
	{
		$forumid = trim($_POST['forumid']);
		$linesm = file("data/$forumid");
		$nm = count($linesm);
		$gpg = ceil($nm/10);

		if ($telegramsend == 1)
		{
			$telegram_msg = "<b>Topic</b>: http://$hst$self?forumid=$forumid%26page=$gpg%23m$nm %0A<b>Message</b>: " .codemsg($msg);
			echo '<object type="text/html" data="https://api.telegram.org/bot' .$telegramtoken. '/sendMessage?chat_id=' .$telegramid. '&parse_mode=html&text=' .$telegram_msg. '" width="1px" height="1px"></object>';
		}
		echo "<meta http-equiv=refresh content='0; url=index.php?forumid=$forumid&page=$gpg#m$nm'>";
	} else {
		show_error("Сообщение не должно быть коротким (лимит 2 - $maxmsg симв.)", "javascript:history.back(1)");
	}
}

if ($welcome == 1) $on=" onload='welcome()'"; else $on="";


////////////////// Шапка форума
include "$fskin/top.html";


////////////////// Админка - действия
if (isset($_COOKIE['cadmin']) && isset($_COOKIE['cpass']))
{
	if ($_COOKIE['cadmin'] === $adminname && $_COOKIE['cpass'] === $adminpass)
	{
		if (isset($_GET['forumid']))
		{
			if (isset($_GET['mode']))
			{
				/////////// Закрыть тему
				if ($_GET['mode'] == "closetopic" or $_GET['mode'] == "opentopic" )
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
			$text=str_replace("¦",'', $text);
			$text=str_replace("\n",'<br>', $text);
			$text=str_replace("\r",'', $text);

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
		if (isset($_GET['event']) && $_GET['event']=="ban")
		{
			if (is_file("datan/banip.dat"))
			{
				$linesb=file("datan/banip.dat");
				$ib=count($linesb);
				$itogoban=$ib;
				if ($ib>0) {
					print"	<br><center><style>table,td{border:#222 1px solid; border-collapse:collapse}</style>
						<table width='93%'>
						<tr><td>
							<table width='100%' cellpadding='5' cellspacing='0'>
							<tr class=row2>
							<td align=center><b>X</b></td>
							<td align=center><b>Дата от</b></td>
							<td align=center><b>Дата до</b></td>
							<td align=center><b>Дней</b></td>
							<td align=center><b>Наказание</b></td>
							<td align=center><b>Статус</b></td>
							<td align=center><b>IP</b></td>
							<td align=center><b>Nick</b></td>
							<td align=center><b>Причина</b></td>
							</tr>";
					do {
						$ib--;
						$idt=explode("|", $linesb[$ib]);
						if ($idt[4]>time()) $ban_status="<font color=red>Сидит</font>"; else $ban_status="<font color=green>Вышел</font>";
						if ($idt[6]==TRUE) $banorwarn_status="Бан"; else $banorwarn_status="Предупр";

						$idt[3]=date("d.m.Y_H:i",$idt[3]);
						$idt[4]=date("d.m.Y_H:i",$idt[4]);

						print"	<tr><td width=15 align=center class=row1><a href='index.php?delip=$ib' title='Удалить' onclick=\"return confirm('Удалить эту строку?')\"><font color=red><b>X</b></font></a></td>
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
					print"<br><br><center><b>Заблокированные отсутствуют</b></center><br>";
				}
			}
			$banorwarn=1;
			if ($banorwarn==TRUE) {$banorwarn1="checked"; $banorwarn2="";} else {$banorwarn2="checked"; $banorwarn1="";}

			print"</table><br><center><form action='index.php?badip' method=POST><input class=radio type=radio name=\"banorwarn\" value='1' $banorwarn1/>бан <input class=radio type=radio name=\"banorwarn\" value='0' $banorwarn2/>предупр, на <input type=text style='width:35px' maxlength=3 name=\"to_time\" value='5'> дней, <input type=text placeholder='IP *' style='width:105px' maxlength=15 name=\"ip\"> <!--input type=text placeholder='Nick' style='width:130px' name='nickban' maxlength='$maxname'--> <SELECT name=nickban style='height:28px;padding:2px 3px;border-radius:3px;'><option value=''>Выберите ник</option>";

			if (is_file("datan/usersdat.php")) $lines=file("datan/usersdat.php");
			$imax=count($lines); $i="1";
			do {
				$dt=explode("|", $lines[$i]);
				print "<OPTION $selectnext value=\"$dt[0]\">$dt[0]</OPTION>";
				if ($nickban==$dt[0]) $selectnext="selected"; else $selectnext="";
				$i++;
			} while($i < $imax);

			print"</SELECT> <input type=text style='width:50%' maxlength=500 name=\"text\" placeholder='Причина'> <input type=submit value='Добавить' class='fbutton'></form><br>Всего в списке: <b>$itogoban</b><br><br>* если блокируете пользователя по нику и не знаете его IP, то впишите, например 127.0.0.3</td></tr></table><br><br><a href='index.php'>&#9668; назад</a></center></body></html>";

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
			if (isset($_POST['to_time'])) $to_time=$_POST['to_time']; else $to_time="5";
			if (isset($_POST['banorwarn'])) $banorwarn=$_POST['banorwarn'];

			$from_time=time();
			$to_time_day=$to_time;
			$to_time=$from_time+86400*$to_time;

			if (isset($_GET['ip_get']))
			{
				$ip=$_GET['ip_get'];
				$nickban=$_GET['nickban'];
				$badtext="Нарушения правил!";
			}

			if (strlen($ip)<7) show_error("Вы неправильно ввели IP-адрес", "javascript:history.back(1)");

			$badtext=str_replace("|", '', $badtext);
			$nickban=str_replace("|", '', $nickban);

			$text="$ip|$nickban|$badtext|$from_time|$to_time|$to_time_day|$banorwarn|";

			//$text=htmlspecialchars($text,ENT_COMPAT,"windows-1251"); //WR
			//$text=htmlspecialchars($text, ENT_QUOTES, 'cp1251'); //Моё
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
if (isset($_GET['event']) && $_GET['event']=="login")
{
	print "
		<br><br><form method=post action=\"index.php?event=regenter\" name='Guest' onSubmit='regGuest(); return(false);'> 
		<table align=center style='border: #333 1px solid;' cellpadding=4 cellspacing=5>
		<tr><td><input name=\"name\" size=30 placeholder='Name' type=text maxlength=$maxname></td></tr>
		<tr><td><input name=\"passreg\" size=30 type=password placeholder='Password' maxlength=20></td></tr>";

	if ($captchamin==1)
	{
		exit("<tr><td><table cellpadding=0 cellspacing=0 border=0><tr><td>
<script>function checkedBox(f){if(f.check1.checked) document.getElementById('other').innerHTML='<br><center><input type=submit class=fbutton value=\'Отправить\'></center>';
else document.getElementById('other').innerHTML='<br><center><input type=submit class=fbutton value=\'Отправить\' disabled=\'disabled\'></center>';}</script>
<input type=checkbox name=check1 onClick=\"checkedBox(this.form)\" style='height:20px;width:20px' title='Если не отправляет данные, то повторно ставьте галочку капчи' ></td>
<td width='100%'>&nbsp; я не бот</td></tr></table></td></tr>
<tr><td><div align=center></div><div id=other align=center><br><input type=submit class=fbutton value='Отправить' disabled='disabled'></div>
</td></tr></table></form><p align=center><a href='index.php?id=forum'>&#9668; назад</a></p>");

	} else {
		exit("<tr><td><img src=\"index.php?secpic\" id='secpic_img' style='border: #000 1px solid;' align='top' title='Для смены картинки щелкните по ней' onclick=\"document.getElementById('secpic_img').src='index.php?secpic&' + Math.random(); return false\">&nbsp; <input type='text' name='secpic' id='secpic' style='width:60px' title='Введите $let_amount жирных симв. изображенных на картинке' maxlength='10'></td></tr>
<tr><td><input type=hidden name=add value=''><br><center><input type=submit class=fbutton value='Отправить'></center>
</td></tr></table></form><p align=center><a href='index.php?id=forum'>&#9668; назад</a></p>");

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
			print "<form action=\"index.php?event=edit_post&forumid=$fi&m=".$_GET['m']."&page=".$_GET['page']."\" method=post name=REPLIER>
<br><table class=f align=center cellspacing=0 cellpadding=2 border=0><tr><td>".get_bb_panel()."<div style='font-size:3px'>&nbsp;</div><textarea name=msg cols=70 style='height:170px;' id='expand'>".br2n($pm[3])."</textarea>
<center><input type=button value='&#9660;&#9660;&#9660;' title='Растянуть' style='height:15px;width:100%;font-size:10px;' onclick=\"hTextarea('expand');return false;\"></center><div style='font-size:2px'>&nbsp;</div>
<input type=hidden name=forumid value=\"$forumid\">
<input type=hidden name=name value=\"$cname\">
<input type=hidden name=email value=\"$cmail\">
</td></tr><tr><td class=row1 align=center height='50px'><input type=submit tabindex=5 class=fbutton value='Отправить' style='width:110px'></td></tr></table></form>
<br><p align=center><a href='javascript:history.back(1)'>&#9668; назад</a></p></body></html>";
		}
	} else show_error("Установленное на форуме время для редактирования сообщения истекло!", "javascript:history.back(1)");
}

////////////////// Кто был на форуме за ХХ часов  
if (isset($_COOKIE['cname'])) {$st_userday = $_COOKIE['cname'];} else {$st_userday = "Гость";}
$st_time=time();
$st_lineday="$st_userday|$st_time|$ip|\r\n";
$st_guestsday=0; //число гостей
$st_infoday=' '; //строка списка пользователей
$list_ip=' '; //строка списка IP гостей
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
				$findstrday=$st_arrday[2].", "; //Подсчет гостей

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

	if ($st_userday=="Гость") ++$st_guestsday; else $st_infoday = "$st_userday, ";
}


////////////////// Когда последний раз был на форуме
$date=gmdate('d.m.Y',time()+3600*($timezone+(date('I')==1?0:1)));
$time=gmdate('H:i',time()+3600*($timezone+(date('I')==1?0:1)));

if ($_['user']) 
{
	$ulines=file("datan/usersdat.php");
	$ui=count($ulines)-1;
	$ulinenew="";

	//Ищем юзера по имени
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


/////////////// ПРОСМОТР УЧАСТНИКОВ
if (isset($_GET['event']))
{
	if ($_GET['event']=="who")
	{
		if (!isset($_COOKIE['cname']) and !isset($_COOKIE['cpassreg']) || !$_COOKIE['cadmin']==$adminname and !$_COOKIE['cpass']==$adminpass) show_error("Для просмотра пользователей вам необходимо<br><br>[<a href='index.php?mode=reg'>зарегистрироваться</a>]", "javascript:history.back(1)");

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

				//Если есть совпадение в строке присваиваем флагу 1
				if ($dt[6]!="" and $pol!="") {if (stristr($dt[6],$pol)) $flag=1;}
				if ($dt[10]!="" and $interes!="") {if (stristr($dt[10],$interes)) $flag=1;}
				if ($dt[8]!="" and $url!="") {if (stristr($dt[8],$url)) $flag=1;}
				if ($dt[9]!="" and $from!="") {if (stristr($dt[9],$from)) $flag=1;}

				//Если было хоть одно соврадение, включаем участника в массив участников
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
			$fadd="";
			$lines=$alllines;
		}

		if (!isset($lines)) $maxi=0; else $maxi=count($lines)-1;

		print "<p align=center>[<a href='index.php'>вернуться на форум</a>]</p><center><form action=\"index.php?event=who\" method=GET>
<input type=hidden name=event value='who'>
<table style='border: #000 1px solid;' width='90%' height=50 cellpadding=1 cellspacing=0><tr>
<td><input type=text name=pol value='$pol' size=20 placeholder='Пол (введи: муж или жен)'></td>
<td><input type=text name=interes value='$interes' class=post maxlength=50 size=20 placeholder='Интересы'></td>
<td><input type=text name=url value='$url' class=post maxlength=50 size=20 placeholder='Сайт'></td>
<td><input type=text name=from value='$from' class=post maxlength=50 size=20 placeholder='Откуда'></td>
</tr><tr><td colspan=4><p align='center'><input type=submit class=fbutton style='width:100%' value='Фильтр'></p></td></tr>
</table></form><br><br>
<table style='border: #000 1px solid;' width=90% cellpadding=1 cellspacing=0><tr>
<th width=25>№</th><th width=120>Имя</th><th width=120>Статус</th><th width=100>Награды</th><th>ЛС</th><th>Зареган</th><th>Когда был</th><th>ДР</th><th>Интересы</th><th>Сайт</th><th>Откуда</th></tr>";

		//Исключаем ошибку вызова несуществующей страницы
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

					print "<tr><td class=$t1 height='22px'><center><small>$numm</small></center></td><td class=$t1><img src='$fskin/$add' border=0> <span align=absmiddle>$wfn</span> ";

					if ($dt[7] != "")
					{
						print " <a href='http://kicq.ru' target='_blank'><img src='https://status.icq.com/5/online1.gif' border=0 align=absmiddle width='13px' height='13px' title=\"KICQ: $dt[7]\"></a>";
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

		echo'</table><BR><table width="90%"><TR><TD width="30%">Страницы:&nbsp; ';

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

		print "</TD><TD align=center width='30%'><span>[<a href='index.php'>вернуться на форум</a>]</span></TD><TD align=right width='30%'><span>Зарегистрировано: $allmaxi</span></TD></TR></TABLE><BR>";
	}


	/////////////// РЕДАКТИРОВАНИЕ ПРОФИЛЯ
	if ($_GET['event']=="profile")
	{
		if (!isset($_GET['pname'])) show_error("ОШИБКА ЗАПРОСА", "javascript:history.back(1)");

		$pname=urldecode($_GET['pname']); //Раскодируем имя
		$lines=file("datan/usersdat.php");
		$i=count($lines);
		$use="0";
		do {
			$i--;
			$rdt=explode("|", $lines[$i]);

			if (isset($rdt[1])) //Если пустая строка - то НЕ выводим
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
							print "<center><p align=center>[<a href='index.php'>главная форума</a>] &nbsp; [<a href='javascript:history.back(1)'>вернуться назад</a>]</p><form action=\"index.php?event=reregist\" name=creator method=post enctype=multipart/form-data>
<table cellpadding=2 cellspacing=0 width='480px' style='border:1px #333 solid;'>
<tr><th colspan=2 height='26px' valign=middle>Регистрационная информация</th></tr>
<tr><td class=row1 height='26px' width='120px' align=right><b>Ваше имя</b>&nbsp;</td><td class=row2>&nbsp;$rdt[0]</td></tr>
<tr><td class=row1 height='26px' align=right><b>Ваш пароль</b>&nbsp;</td><td class=row2>&nbsp;<input type=password class=post style='width:200px;height:23px' value=\"$rdt[1]\" name=pass size=25 maxlength=12></td></tr>
<tr><td class=row1 height='26px' align=right><b>Ваш e-mail</b>&nbsp;</td><td class=row2>&nbsp;<input type=text class=post style='width:200px;height:23px' value=\"$rdt[3]\" name=email size=25 maxlength=50></td></tr>
<tr><td class=row1 height='26px' align=right><b>ЛС</b>&nbsp;</td><td class=row2>&nbsp;";

							$wrfname=strtolower($wrfname);

							if (is_file("data-pm/$wrfname.dat"))
							{
								$linespm=file("data-pm/$wrfname.dat");
								$pmi=count($linespm);

								print "[<a href='pm.php?readpm&id=$wrfname'><font color=red><b>$pmi сообщ.</b></font></a>]";

							} else echo'сообщений нет';

print"</td></tr><tr><td colspan=2></td></tr>
<tr><th colspan=2 height='26px' valign=middle>Дополнительная информация</th></tr>
<tr><td class=row1 height='26px' align=right><b>Регистрация</b>&nbsp;</td><td class=row2>&nbsp;$rdt[4]</td></tr>
<tr><td class=row1 height='26px' align=right><b>Пол</b>&nbsp;</td><td class=row2>&nbsp;$rdt[6]<input type=hidden value=\"$rdt[6]\" name=pol></td></tr>
<tr><td class=row1 height='26px' align=right><b>День рождения</b>&nbsp;</td><td class=row2>&nbsp;<input type=text name=dayx placeholder='пример: 21.12.2012' value=\"$rdt[5]\" class=post style='width:120px;height:23px' size=10 maxlength=10>&nbsp;</td></tr>
<tr><td class=row1 height='26px' align=right><b>KICQ</b>&nbsp;</td><td class=row2>&nbsp;<input type=text value=\"$rdt[7]\" name=icq class=post style='width:120px;height:23px' size=10 maxlength=12></td></tr>
<tr><td class=row1 height='26px' align=right><b>Телеграм</b>&nbsp;</td><td class=row2>&nbsp;<input type=text value=\"$rdt[16]\" name=telegram placeholder='пример: https://t.me/youtubequest' class=post style='width:345px;height:23px' size=10 maxlength=50></td></tr>
<tr><td class=row1 height='26px' align=right><b>Сайт</b>&nbsp;</td><td class=row2>&nbsp;<input type=text value=\"$rdt[8]\" class=post style='width:345px;height:23px' name=www  placeholder='пример: http://mysite.ru' size=25 maxlength=50></td></tr>
<tr><td class=row1 height='26px' align=right><b>Откуда</b>&nbsp;</td><td class=row2>&nbsp;<input type=text class=post style='width:345px;height:23px' value=\"$rdt[9]\" name=about size=25 maxlength=60></td></tr>
<tr><td class=row1 height='26px' align=right><b>Интересы</b>&nbsp;</td><td class=row2>&nbsp;<input type=text class=post style='width:345px;height:23px' value=\"$rdt[10]\" name=work size=35 maxlength=60></td></tr>
<tr><td class=row1 height='26px' align=right><b>Подпись</b>&nbsp;</td><td class=row2>&nbsp;<input type=text class=post style='width:345px;height:23px' value=\"$rdt[11]\" name=write size=35 maxlength=70></td></tr>
<tr><td class=row1 height='26px' align=right><b>Флаг</b>&nbsp;</td><td class=row2>";

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

							print "<table><tr><td><script>function showimageflag(){document.images.cflag.src='./flags/'+document.creator.cflag.options[document.creator.cflag.selectedIndex].value;}</script><select name='cflag' size=6 onChange='showimageflag()'>$selecthtm</select></td><td><img src='./flags/$currentflag' name=cflag border=0 hspace=15></td></tr></table></td></tr><tr><td class=row1 height='25px' align=right><b>Аватар</b>&nbsp;</td><td class=row2 height='120px'>";

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
<td class=row1 align=right><br><b>Загрузить аватар</b>&nbsp;<div align=right><small><i>не более <B>$avatar_width</B>х<B>$avatar_height</B>px ".$maxfsize."Kb &nbsp;<br><br></i></small></div></td>
<td class=row2>&nbsp;<input type=file name=file class=post style='width:340px;height:23px' size=35 maxlength=150></td></tr>
<tr><td colspan=2 align=center><input type=hidden name=name value=\"$rdt[0]\"><input type=hidden name=oldpass value=\"$rdt[1]\">
<input type=submit name=submit value='Сохранить' class='fbutton'></td></tr></table></form><p align=center>[<a href='index.php'>главная форума</a>] &nbsp; [<a href='javascript:history.back(1)'>вернуться назад</a>] </p>";

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
<tr><th class=thHead colspan=2>Профиль участника</th></tr>
<tr><td class=row1 width='120px' height='26px' align=right><b>Имя</b>&nbsp;</td><td class=row2>&nbsp;<span class=nav>$rdt[0]</span></td></tr>
<tr><td class=row1 height='26px' align=right><b>Регистрация</b>&nbsp;</td><td class=row2>&nbsp;$rdt[4]</td></tr>
<tr><td class=row1 height='26px' align=right><b>Был на форуме</b>&nbsp;</td><td class=row2>&nbsp;$rdt[15]</td></tr>
<tr><td class=row1 height='26px' align=right><b>Пол</b>&nbsp;</td><td class=row2>&nbsp;$rdt[6]</td></tr>
<tr><td class=row1 height='26px' align=right><b>Статус</b>&nbsp;</td><td class=row2>&nbsp;";

							$newstatus=explode("@", $rdt[13]);

							print "$newstatus[0]</td></tr><tr><td class=row1 height='25px' align=right><b>Награды</b>&nbsp;</td><td class=row2>&nbsp;";

							if (count($newstatus)>1) {print " ";}

							for($i=1; $i<count($newstatus); $i++) {print"<img src='$fskin/medal.gif' style='cursor:help' border=0 title='$newstatus[$i]'> ";}

							print"</td></tr>
<tr><td class=row1 height='26px' align=right><b>ЛС</b>&nbsp;</td><td class=row2><form action='pm.php?id=$rdt[0]' method=POST name=citata onclick=\"window.open('pm.php?id=$rdt[0]','citata','width=800,height=500,left=100,top=100,toolbar=0,status=0,border=0,scrollbars=1');return false;\"><input type='button' value='ЛС' class=button></form></td></tr>
<tr><td class=row1 height='26px' align=right><b>Сообщений</b>&nbsp;</td><td class=row2>&nbsp;<b>$msguser</b> (<b>$msgaktiv</b>%) <progress title='% сообщений от общего числа' max='100' value='$msgaktiv'></progress></td></tr>
<tr><td class=row1 height='26px' align=right><b>Родился</b>&nbsp;</td><td class=row2>&nbsp;$rdt[5]</td></tr>
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
<tr><td class=row1 height='26px' align=right><b>Сайт</b>&nbsp;</td><td class=row2>&nbsp;<a href='$rdt[8]' target='_blank'>$rdt[8]</a></td></tr>
<tr><td class=row1 height='26px' align=right><b>Откуда</b>&nbsp;</td><td class=row2>&nbsp;<img src='./flags/$flagpr' border=0 align=center>&nbsp; $rdt[9]</td></tr>
<tr><td class=row1 height='26px' align=right><b>Интересы</b>&nbsp;</td><td class=row2>&nbsp;$rdt[10]</td></tr>
<tr><td class=row1 height='26px' align=right><b>Подпись</b>&nbsp;</td><td class=row2>&nbsp;$rdt[11]</td></tr>
<tr><td class=row1 height='26px' align=right><b>Аватар</b>&nbsp;</td><td class=row2>&nbsp;<img src='./avatars/$avpr' border=0><br></td></tr></td></tr></table>
<br><p align=center>[<a href=\"javascript:history.back(1)\">вернуться назад</a>]</p>";

							$use="1";
						}
					}
				}
			}
		} while($i>"1");

		if (!isset($wrfname)) show_error("Только зарегистрированные пользователи могут пpосматpивать профиль участников!", "javascript:history.back(1)");

		// в БД такого ЮЗЕРА НЕТ
		if ($use!="1") show_error("Пользователь с таким именем не найден. Вероятнее всего он был удалён админом!", "javascript:history.back(1)");
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

			$name = str_replace("¦",'', $name);
			$mail = str_replace("¦",'', $mail);
			$topic = str_replace("¦",'', $topic);

			$tt = replacer(str_replace("¦",'', $tt));

			$stopuser = replacer(str_replace("|",'', $stopuser));
			$onlyuser = replacer(str_replace("|",'', $onlyuser));

			$sur = replacer(str_replace("|",'', $sur));
			$our = replacer(str_replace("|",'', $our));

			if (preg_match('/^\d+$/', $zvezdmax)) $zvezdmax = replacer(substr(str_replace("¦",'',$zvezdmax),0,2)); else $zvezdmax = "0";
			if (preg_match('/^\d+$/', $repamax)) $repamax = replacer(substr(str_replace("¦",'',$repamax),0,5)); else $repamax = "0";

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

			////////////////// Отправляем сообщение на мыло о новой теме
			if ($topicmail==TRUE)
			{
				$headers = 'From: $frommail' . "\r\n" . 'Reply-To: $frommail' . "\r\n" . 'X-Mailer: PHP/' . phpversion();
				$subject = 'Forum: New topic';
				$msg='Topic: ' . $topic;
				mail($adminmail, $subject, $msg, $headers);
			}

			////////////////// Создаем файл *.user
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

			$name=trim(replacer(str_replace("¦",'', $name)));
			$email=trim(replacer(str_replace("¦",'', $email)));
			$msg=trim(replacer(str_replace("¦",'', $msg)));

			$msg=str_replace("\n",'<br>', $msg);

			$tt=trim(replacer(str_replace("¦",'', $tt)));

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

			if (isset($_FILES['file']['name'])) // ЕСЛИ ДОБАВЛЯЕМ ФАЙЛ
			{
				$fotoname=replacer($_FILES['file']['name']);

				if (strlen($fotoname)>1)
				{
					$fotosize=$_FILES['file']['size'];

					// Проверяем РАСШИРЕНИЕ
					$ext=strtolower(substr($fotoname, 1 + strrpos($fotoname, ".")));

					if (!in_array($ext, $valid_types_load)) show_error("Файл не загружен. Причины: недопустимое расширение файла, двойное расширение, файл картинки испорчен.", "javascript:history.back(1)");

					// Считаем КОЛ-ВО ТОЧЕК
					$findtchka=substr_count($fotoname, ".");

					if ($findtchka>1) show_error("В имени загружаемого файла точек больше одной!", "javascript:history.back(1)");

					// Если в имени есть .php и т.д.
					if (preg_match("/\.php|\.htm|\.html|\.mht|\.mhtml|\.hta|\.vb|\.vbs|\.vbe|\b\.js\b|\b\.jse\b|\b\.jar\b/i", $fotoname)) show_error("У вашего файла недопустимое расширение!", "javascript:history.back(1)");

					// Защищаем от РУССКИХ букв и проверка РАСШИРЕНИЯ 
					$patern="";
					foreach($valid_types_load as $v)
					$patern.="$v|";

					if (!preg_match("/^[a-z0-9\.\-_]+\.(".$patern.")+$/is",$fotoname)) show_error("Запрещены русские буквы и пробелы в имени файла!", "javascript:history.back(1)");

					// Проверяем, может быть файл с таким именем уже есть на сервере
					if (file_exists("$filedir/$fotoname")) show_error("Файл с таким именем уже существует. Измените имя на другое!", "javascript:history.back(1)");

					// Размер файла
					$fotoksize=round($fotosize/10.24)/100; //Размер ЗАГРУЖАЕМОГО файла в Кб
					$fotomax=round($max_upfile_size/10.24)/100; //Максимальный размер файла в Кб

					if ($fotoksize>$fotomax) show_error("Вы превысили допустимый размер файла $fotomax Кб. Вы загружаете файл размером $fotoksize Кб", "javascript:history.back(1)");

					// ЕСЛИ включен порядок присвоения файлу случайного имени при загрузке - генерируем случайное имя
					//if ($random_name==TRUE) {do $key=mt_rand(100000,999999); while (file_exists("$filedir/$key.$ext")); $fotoname="$key.$ext";}

					@copy($_FILES['file']['tmp_name'], $filedir."/".$fotoname);

					print "<br><br><br><center><font size=3 face=arial>Файл <b>$fotoname</b> ($fotosize байт) успешно загружен!</center>";

					$size = getimagesize("$filedir/$fotoname");

					/////// Если габариты меньше заданных в настройках 260х220 то ничего с ним не делаем. Блок делает превьюшки
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

					//От веса. Если больше $max_upfile_size жать. Кроме гифов.
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
						$udt[0]=str_replace("\r\n",'', $udt[0]);
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

					$msg=str_replace("¦",'', $msg);

					$msg=preg_replace("/\[hide\](.+?)\[\/hide\]/is", " [Текст скрыт от гостей] ", $msg);
					$msg=preg_replace("/\[hide=(.+?)\](.+?)\[\/hide\]/is", " [Текст для \\1] ", $msg);

					$valuelast=$name."¦".$email."¦".$date."¦".$forumid."¦".$topic."¦".$pages."¦".$msg."¦".$country_img."¦".$country_name."¦".$ip."¦".$country."¦".$latitude."¦".$longitude;

					$valuelast=trim(str_replace("
", '<br>', $valuelast));
					$valuelast=str_replace("\r\n",'<br>', $valuelast);
					$valuelast=str_replace("\n",'<br>', $valuelast);
					$valuelast=str_replace("\r",'', $valuelast);
					$valuelast=str_replace("\t",' ', $valuelast);

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
				<td class=t><font color=red>Тема:</font>&nbsp;<a href='index.php' title='Вернуться к списку тем'>$topic</a> &nbsp;<span class=small>[ответов: $ccnt, страница: $page]</span></td>
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

				//$msg=preg_replace("/(\[youtube\])(.+?)(\[\/youtube\])/is","<br><object width=640px height=480px><param name=movie value=\"https://www.youtube.com/v/$2\"></param><param name=allowFullScreen value=true></param><param name=allowscriptaccess value=always></param><embed src=\"https://www.youtube.com/v/$2\" type=\"application/x-shockwave-flash\" allowscriptaccess=always allowfullscreen=true width=640px height=480px></embed></object><br>", $msg);

				$msg=preg_replace("/\[youtube\]https?:\/\/(?:[a-z\d-]+\.)?youtu(?:be(?:-nocookie)?\.com\/.*v=|\.be\/)([-\w]{11})(?:.*[\?&#](?:star)?t=([\dhms]+))?\[\/youtube\]/i","<br><object width=640px height=480px><param name=movie value=\"https://www.youtube.com/v/$1\"></param><param name=allowFullScreen value=true></param><param name=allowscriptaccess value=always></param><embed src=\"https://www.youtube.com/v/$1\" type=\"application/x-shockwave-flash\" allowscriptaccess=always allowfullscreen=true width=640px height=480px></embed></object><br>", $msg);

				$msg=preg_replace("/\[vimeo\](http|https)?:\/\/(www\.|player\.)?vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|video\/|)(\d+)(?:|\/\?)\[\/vimeo\]/i", "<br><embed src=\"https://player.vimeo.com/video/$4\" allowscriptaccess=always allowfullscreen=true width=640px height=480px></embed><br>", $msg);

				$msg=preg_replace("/\[dzen\](http|https)?:\/\/(www\.)?dzen\.ru\/embed\/(.+)\[\/dzen\]/i", "<br><embed src=\"https://dzen.ru/embed/$3\" allow=\"autoplay; fullscreen; accelerometer; gyroscope; picture-in-picture; encrypted-media\" frameborder=0 scrolling=no allowfullscreen width=640px height=480px></embed><br>", $msg);

				$msg=preg_replace("/\[rutube\](http|https)?:\/\/(www\.)?rutube\.ru\/video\/(\w+)\[\/rutube\]/i", "<br><embed src=\"https://rutube.ru/play/embed/$3\" frameBorder=0 allow=\"clipboard-write; autoplay\" webkitAllowFullScreen mozallowfullscreen allowFullScreen width=640px height=480px></embed><br>", $msg);

				// Новая регулярка
				$msg=preg_replace("/\[telegram\](http|https)?:\/\/(www\.)?t\.me\/(.+)\/(\d+)\[\/telegram\]/i", '<br><div style="margin: 15px 0;"><script async src="https://telegram.org/js/telegram-widget.js?22" data-telegram-post="$3/$4" data-comments-limit="5" data-width="500px"></script></div><br>', $msg);

				// Старые регулярки
				//$msg=preg_replace("/\[telegram\](http|https)?:\/\/(www\.)?t\.me\/(.+)\/(\d+)\[\/telegram\]/i", '<br><iframe id="tg-post-$3-$4" src="https://t.me/$3/$4?embed=1" frameborder="0" scrolling="yes" style="margin: 10px 0; min-width: 320px; max-width: 550px; width: 100%; height: 400px; min-height: 50vh; border: none;"></iframe><br>', $msg);

				//$msg=preg_replace("~\[telegram\](?:https?://)?(?:www\.)?t\.me/([\w\d_]+)/(\d+)\[/telegram\]~i", '<div class="tg-embed-wrapper" style="margin: 10px 0;"><iframe id="tg-post-$1-$2" src="https://t.me/$1/$2?embed=1" frameborder="0" scrolling="no" style="overflow: hidden; color-scheme: light dark; border: none; min-width: 320px; max-width: 550px; width: 100%; height: 400px;"></iframe></div>', $msg);

				$msg=preg_replace_callback('/\[map\](.*?)\[\/map\]/i', function($matches){$address = urlencode(trim(strip_tags($matches[1]))); return '<br><iframe frameborder="0" src="https://maps.google.com/maps?f=q&source=s_q&hl=en&geocode=&q='.$address.'&z=14&output=embed" style="border: 2px solid rgba(72, 133, 237,1); width: 640px; height: 480px; margin: 15px 0;"></iframe><br>';}, $msg);


				$msg=preg_replace("/\[ok\](http|https)?:\/\/(www\.)?ok\.ru\/video\/(\w+)\[\/ok\]/i", '<br><embed src="https://ok.ru/videoembed/$3" frameborder="0" allow="autoplay" allowfullscreen width=640px height=480px></embed><br>', $msg);

				if ($antimat==1) $msg = remBadWordsA($msg);
				if ($antimatt==1) $msg = remBadWordsB($msg);

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
					$dttt=explode("¦", $lines[$a]);
					if ($forumid==$dttt[2])
					{
						if (empty($_['user']) & $dttt[12]>0 || empty($_['user']) & $dttt[13]>0) exit("<script>setTimeout(function(){window.location.href='index.php';},10000);</script><br><br><br><br><center><fieldset style='width:400px;border:solid 1px #777;'><legend align=center><font color=red>Доступ ограничен!</font></legend><p>Для просмотра этой темы вы должны быть зарегистрированы!</p></fieldset><br><br><a href=\"index.php\">&#9668; назад</a></center>");

						if (isset($_COOKIE['cadmin']) && isset($_COOKIE['cpass']) && $_COOKIE['cadmin']==$adminname && $_COOKIE['cpass']==$adminpass)
						{
							continue;
						} else {
							if ($_['user'] && $you_zvezd<$dttt[12]) exit("<script>setTimeout(function(){window.location.href='index.php';},10000);</script><br><br><br><br><center><fieldset style='width:400px;border:solid 1px #777;'><legend align=center><font color=red>Доступ ограничен!</font></legend><p>Для просмотра вы должны иметь минимум <b>$dttt[12]</b> звёзд. У вас <b>$you_zvezd</b> звёзд.</p></fieldset><br><br><a href=\"index.php\">&#9668; назад</a></center>");

							if ($_['user'] && $you_repa<$dttt[13]) exit("<script>setTimeout(function(){window.location.href='index.php';},10000);</script><br><br><br><br><center><fieldset style='width:400px;border:solid 1px #777;'><legend align=center><font color=red><b>Доступ ограничен!</b></font></legend><p>Для просмотра вы должны иметь минимум <b>$dttt[13]</b> баллов репутации. У вас <b>$you_repa</b></p></fieldset><br><br><a href=\"index.php\">&#9668; назад</a></center>");
						}
					}
				}

				$topicavtor = explode("¦", $theme[0]);

				if ($name == $topicavtor[0]) {$topicavtor="автор темы";} else {$topicavtor="";}

				$newstatus=explode("@", $you_status);

				print "
					<table class=f align=center cellspacing=1 cellpadding=0 border=0>	
					<tr><td rowspan=2 valign=top class=name>
					<table cellspacing=0 cellpadding=1 width='225px' border=0>
					<tr><td valign=top class=name><a href=\"javascript:ins('".$name."')\" class='name' title='Вставить имя в форму ответа'>".$name."</a>";

				if ($_['user']) {
					print " <span class=small><sup><a href='index.php?event=profile&pname=".$name."' style='text-decoration:none' title='Звёзды (выдаёт админ). Перейти к профилю пользователя'>$you_reiting</a></sup><div style='display:inline-block;margin:0 0 -5 5;' class='$country_img' title='$country, $country_name'></div><br><div class=small>$newstatus[0]<br>$topicavtor</div></td></tr><tr><td valign=top class=name><br>";

				} else {
					print " <span class=small><sup title='Количество звезд'>$you_reiting</sup><div style='display:inline-block;margin:0 0 -5 5;' class='$country_img' title='$country, $country_name'></div><br>$newstatus[0]<br>$topicavtor</span></td></tr><tr><td valign=top class=name><br>";
				}

				////////////////// Бан по IP, шильдик БАН под аватаром
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

							$userban="<div><div class='tooltip'><div style='background:red;padding:0 2;border:1px solid black;color:#111;font-family:tahoma;font-size:9px;font-weight:bold;'>БАН</div><span style='width:210px;' class='tooltiptext'><b><u>Бан</u></b>: $dtb[3] до $dtb[4]<br><b><u>Осталось</u></b>: $userban_time дн. из $dtb[5]<br><b><u>Причина</u></b>: $dtb[2]</span></div></div>";
							break;
						}

						if ($dtb[1]===$name and $tektime<$dtb[4] and $dtb[6]==FALSE)
						{
							if ($dtb[4]>time()) $userban_time = ceil(($dtb[4] - $tektime)/86400);
							$dtb[3]=date("d.m.Y_H:i",$dtb[3]);
							$dtb[4]=date("d.m.Y_H:i",$dtb[4]);

							$userban="<div><div class='tooltip'><div style='background:yellow;padding:0 2;border:1px solid black;color:#111;font-family:tahoma;font-size:9px;font-weight:bold;'>Предупр!</div><span style='width:210px;' class='tooltiptext'><b><u>Предупр.</u></b>: $dtb[3] до $dtb[4]<br><b><u>Осталось</u></b>: $userban_time дн. из $dtb[5]<br><b><u>Причина</u></b>: $dtb[2]</span></div></div>";
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
						print "<div class='cont'><div class='gravatar' align='center'><div class='holder'><img src=\"avatars/$avpr\" style='border-radius:50%;-moz-border-radius:50%;-webkit-border-radius:50%'><div class='block'><small>[<a class='small' href='#' name=citata onclick=\"window.open('pm.php?id=".$name."','citata','width=800,height=500,left=100,top=100,toolbar=0,status=0,border=0,scrollbars=1');return false;\" title='Отправить Личное Сообщение'>Личное</a>] [<a class='small' href='index.php?event=profile&pname=".$name."' title='Профиль Пользователя'>Профиль</a>]</small></div></div></div>$ppp $userban</div>";

					} else {
						print "<div class='cont'><div class='gravatar' align='center'><div class='holder'><img src=\"avatars/$avpr\"><div class='block'><small>[<a class='small' href='#' name=citata onclick=\"window.open('pm.php?id=".$name."','citata','width=800,height=500,left=100,top=100,toolbar=0,status=0,border=0,scrollbars=1');return false;\" title='Отправить Личное Сообщение'>Личное</a>] [<a class='small' href='index.php?event=profile&pname=".$name."' title='Профиль Пользователя'>Профиль</a>]</small></div></div></div>$ppp $userban</div>";
					}
				} else {
					if ($gravatar==1)
					{
						$gravatarimg=md5(strtolower(trim($email)));

						if ($avround==1)
						{
							print "<div class='cont'><div align='center'><div class='holder'><img style='border-radius:50%;-moz-border-radius:50%;-webkit-border-radius:50%' src=\"http://www.gravatar.com/avatar/$gravatarimg?d=identicon&s=$gravatarsize\"><div class='block'><small>[<a class='small' href='#' name=citata onclick=\"window.open('pm.php?id=".$name."','citata','width=800,height=500,left=100,top=100,toolbar=0,status=0,border=0,scrollbars=1');return false;\" title='Отправить Личное Сообщение'>Личное</a>] [<a class='small' href='index.php?event=profile&pname=".$name."' title='Профиль Пользователя'>Профиль</a>]</small></div></div></div>$ppp $userban</div>";

						} else {
							print "<div class='cont'><div class='gravatar' align='center'><div class='holder'><img src=\"http://www.gravatar.com/avatar/$gravatarimg?d=identicon&s=$gravatarsize\"><div class='block'><small>[<a class='small' href='#' name=citata onclick=\"window.open('pm.php?id=".$name."','citata','width=800,height=500,left=100,top=100,toolbar=0,status=0,border=0,scrollbars=1');return false;\" title='Отправить Личное Сообщение'>Личное</a>] [<a class='small' href='index.php?event=profile&pname=".$name."' title='Профиль Пользователя'>Профиль</a>]</small></div></div></div>$ppp $userban</div>";

						}
					} else {
						print "<div align='center'></div>";
					}
				}

$rank_list = "<span class='tooltiptext' style='width:250px'>
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
						print"<div class='tooltip'><img src='$fskin/medal.gif' border=0 style='cursor:help'><span class='tooltiptext' style='width:200px'><b>Награда #$ii</b>: $newstatus[$ii]</span></div> ";
					}
					print "<br>";
				}

				if (isset($_COOKIE['cadmin']) && isset($_COOKIE['cpass']) && $_COOKIE['cadmin']==$adminname && $_COOKIE['cpass']==$adminpass)
				{
					print "<a href=\"mailto:".$email."\" style='font-family:tahoma;font-size:11px;font-weight:normal'>".$email."</a><br><span class=small><a href='https://ip-whois-lookup.com/lookup.php?ip=".$ip."' target='_blank' title='Посмотреть информацию о IP'>".$ip."</a>&nbsp;[<a href=index.php?badip&ip_get=".$ip."&nickban=".$name." title='Забанить на 3 дня (срок по умолчанию)'>БАН</a>]</span><br>";
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
						print "<div align=center title='тем : сообщений : репутация : нарушений' style='background-color:;font-family:tahoma;font-size:11px;font-weight:normal;border:0px solid #333;margin:5px'>$udt[1] : $udt[2] : <a href='#m$fm' style='text-decoration:none' onclick=\"window.open('admin.php?event=repa&name=$udt[0]&who=$userpn', 'repa', 'width=650,height=600,left=50,top=50,scrollbars=yes');\">&hearts; <b>$udt[3]</b></a> : $udt[4] &nbsp;";

						if ($_['user'])
						{
print "<details title='Посмотреть кратко профиль' style='cursor:hand;display:inline-block;font-family:tahoma;font-size:11px;font-weight:normal;text-align:left;padding:0px 1px;margin:3 0 3;'><summary style='text-align:center'></summary><div style='width:205px;'><table style='border:1px solid #669900;width:210px;'>
<tr><td width='33px'><small>Who:</small></td><td><small><i>$newstatus[0]</i></small></td></tr>
<tr><td><small>Reg:</small></td><td><small><i>$you_datareg [#$userpn]</i></small></td></tr>
<tr><td><small>Frm:</small></td><td><img src=\"flags/$flagpr\"/> <small><i>$you_flag_name $you_from</i></small></td></tr>
<tr><td><small>Kicq:</small></td><td><small><i>$you_icq</i></small></td></tr>
<tr><td><small>Tgm:</small></td><td><small><i><a href=\"$you_telegram\" target=_new>$you_telegram</a></i></small></td></tr>
<tr><td><small>Web:</small></td><td><small><i><a href=\"$you_site\" target=_new>$you_site</a></i></small></td></tr></table></div></details>";

						}
						print "</div>";
					}
				}

				$newstatus=explode("@", $you_status);

				$ed_msg="";

				if ($_['user'] && $you_name == $_['user']) //stristr($newstatus[0],"админ")
				{
					$ed_msg = " &nbsp; <i><a href=\"index.php?event=edit_post&forumid=$forumid&m=$i&page=$page\" style='text-decoration:none'>Редактировать</a></i>";
				}

				print "</td></tr></table></td><td width='99%' colspan=2 class=msg><small><a name=\"m$i\"></a>&bull; <i>$date</i> &nbsp; : &nbsp; <a href=\"index.php?forumid=$forumid&page=$page#m$i\" title='Ссылка на это сообщение' onClick=\"prompt('Ссылка на сообщение','http://$hst$self?forumid=$forumid&page=$page#m$i')\">#$i</a> &nbsp; : <a href=\"javascript:scroll(0,0)\"> &nbsp; &#9650; &nbsp; </a> : <a href=\"javascript:scroll(100000,100000)\"> &nbsp; &#9660; &nbsp; </a> : <a href='index.php' title='Вернуться на главную'> &nbsp; &#9668; &nbsp; </a> : $ed_msg</small>";

				print "<table cellspacing=0 cellpadding=0 width='100%' border=0 height='90%'><tr><td class=msg>";


				//////////////////// Редактирование сообщений если админ
				if (isset($_COOKIE['cadmin']) && isset($_COOKIE['cpass']) && $_COOKIE['cadmin']==$adminname && $_COOKIE['cpass']==$adminpass)
				{
					print "
						<form action=\"index.php\" method=post style='padding:0 5 0 0'>
						<input type=hidden name=forumid value=\"$forumid\">
						<input type=hidden name=msg value=\"$i\">
						<textarea name=text style='height: 130px; border: #222 1px solid;margin:0'>";

					$msg_for_admin=str_replace("<br>", "\n", $msg_for_admin); // Обратная замена

					print $msg_for_admin;

				} else 

				//print "<table width='100%' border=0 height='90%'><tr><td class=msg>".$msg."</td></tr></table>";

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
							$show_img="<img src=\"$filedir/sm-$fileload\" style=\"border:1px solid #ddd; padding:1px;cursor:pointer\" onclick=\"TINY.box.show({image:'$filedir/$fileload',boxid:'frameless',animate:true})\">";
						} else {
							$show_img="<img src=\"$filedir/$fileload\" style=\"border:1px solid #ddd; padding:1px;\"> ";
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

			print "<table class=f align=center cellspacing=1 cellpadding=0 bgcolor='#000000' style='margin-top:0px' border=0><td><table width='100%' cellspacing=0><td class=t><font color=red>Тема:</font>&nbsp;<a href=\"index.php\" title='Вернуться к списку тем'>$topic</a> &nbsp;<span class=small>[ответов: $ccnt, страница: $page]</span></td></table></td></table><br><table align=center cellspacing=0 cellpadding=1 border=0><tr><td><div align=center class=med>";

			$prev=min($page-1,$pages);
			$next=max($page+1,1);

			if ($page>1) print "<a href=\"index.php?forumid=".$forumid."&page=".$prev."\" class=pagination>&#9668;</a>&nbsp; &nbsp;";

			print $pageinfo;

			if ($page<$pages) print "&nbsp; <a class=pagination href=\"index.php?forumid=".$forumid."&page=".$next."\">&#9658;</a>";

			print "</div></td></tr></table>";


			////////////////// Блок запрета/доступа к теме
			if (is_file("data/".$forumid.".user"))
			{
				$tu = explode('|', file_get_contents("data/$forumid.user"));

				if (!empty($tu[0]))
				{
					if ((preg_match("/\b".$name."\b/i", $tu[0])) && !strstr($you_status,"администратор")) show_error("Автор темы запретил вам участвовать в обсуждении!", "index.php");
				}
				if (!empty($tu[1]))
				{
					if ((!preg_match("/\b".$name."\b/i", $tu[1])) && !strstr($you_status,"администратор")) show_error("Участвовать в теме может админ и пользователи: $tu[1]", "index.php");
				}
			}

			////////////////// Бан по IP (блок перенесен сюда)
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

							exit("<br><div align=center><fieldset style='width:400px;border: #333 1px solid;'><legend align='center'><font size=2 face=tahoma color=red><b>Вы забанены администрацией на $idtb[5] дн.</b></font></legend><br><center><font size=2 face=tahoma><i>$idtb[3]</i> &nbsp; <b>до</b> &nbsp; <i>$idtb[4]</i><br><br><b><u>Причина</u>:</b> $idtb[2]</font></center><br></fieldset></div><br>");
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
<br><table class=f align=center cellspacing=0 cellpadding=2 border=0><tr><td>".get_bb_panel()."</td><td><div align=right>Осталось: <input name=llen style='WIDTH: 50px' value='$maxmsg'></div></td></tr>
<tr><td colspan=2>
<textarea name=msg cols=70 style='height:170px;' id='expand' onkeyup=textKey();></textarea>
<div style='font-size:0px'>&nbsp;</div>
<center><input type=button value='&#9660;&#9660;&#9660;' title='Нажми чтобы растянуть' style='height:15px;width:100%;font-size:10px;' onclick=\"hTextarea('expand'); return false;\"></center><div style='font-size:2px'>&nbsp;</div>
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

					if ($forumid==$dtt[2]) {
						if ($dtt[11]=="0") exit("<div align=center style='color:red;font-family:verdana;font-size:13px;font-weight:bold'>Тема закрыта!<br><br><br><a href='index.php'>&#9668; назад</a></div>"); else print $formreg;
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
					<legend align=center><b><font color=red>Вы зарегистрированы!</font></b></legend>
					<table align=center cellpadding=4 cellspacing=4 border=0 >
					<tr><td align=right><b>Логин:</b></td><td>".$_COOKIE['cname']."</td></tr>
					<tr><td align=right><b>E-mail:</b></td><td>".$_COOKIE['cmail']."</td></tr></table></fieldset></div><br>
					<p align=center>[<a href=\"index.php?event=clearuser\" onclick=\"return confirm('Очистить данные пользователя?')\">очистить данные пользователя</a>]
					<br><br><a href='index.php'>&#9668; назад</a></p></body></html>");
			}

			if (is_file("rules.html")) include"rules.html";

			print"	<br><br><form method=post name='Guest' onSubmit='regGuest(); return(false);'>
				<table align=center style='border: #333 1px solid;' cellpadding=4 cellspacing=4>
				<tr><td><input name=name placeholder='Name' size=40 type=text maxlength=$maxname title='Разрешены русские и латинские буквы, цифры и знак подчёркивания'></td></tr>
				<tr><td><input name=mail placeholder='E-mail' maxlength=$maxmail size=40 type=text></td></tr>
				<tr><td><input name=passreg type=password size=40 maxlength=20 placeholder='Password'></td></tr>
				<tr><td><input type=radio name=pol style='width:15px;height:15px;' value='мужчина' checked> мужчина <input type=radio name=pol style='width:15px;height:15px;' value='женщина'> женщина</td></tr>";

			if ($captchamin==1)
			{
				exit("<tr><td><table cellpadding=0 cellspacing=0 border=0><tr><td>
<script>function checkedBox(f){if(f.check1.checked) document.getElementById('other').innerHTML='<br><center><input type=submit class=fbutton style=\'width:150px\' value=\'Зарегистрироваться\'></center>';
else document.getElementById('other').innerHTML='<br><center><input type=submit class=fbutton style=\'width:150px\' value=\'Зарегистрироваться\' disabled=\'disabled\'></center>';}</script>
<input type=checkbox name=check1 onClick=\"checkedBox(this.form)\" style='width:20px;height:20px;' title='Если не отправляет данные, то повторно ставьте галочку капчи'></td><td> я не бот</td></tr></table></td></tr>
<tr><td><div align=center></div><div id=other align=center><br><input type=submit class=fbutton style='width:150px' value='Зарегистрироваться' disabled='disabled'></div></td></tr></table></form>
<p align=center><a href=\"index.php?id=forum\">&#9668; назад</a></p>");

			} else {
				exit("<tr><td><img src=\"index.php?secpic\" id='secpic_img' border=1 align='top' title='Для смены картинки щелкните по ней' onclick=\"document.getElementById('secpic_img').src='index.php?secpic&' + Math.random(); return false\"> &nbsp;<input type='text' name='secpic' id='secpic' style='width:60px;' title='Введите $let_amount жирных симв. изображенных на картинке' maxlength='10'><input type=hidden name=add value=''><br><br>
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
		exit("</table></form><p align=center>[<a href=\"index.php?event=clearadmin\" onclick=\"return confirm('Очистить данные админа?')\">выйти из админа</a>] [<a href=\"index.php\">перейти на форум</a>]</p></body></html>");
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
<tr><td><b>Имя:</b> <input type=hidden name=name value=\"".$cname."\">$cname &nbsp; &nbsp; <b>E-mail:</b> <input type=hidden name=mail value=\"".$cmail."\">$cmail</td></tr>
<tr><td><table cellpadding=0 cellspacing=0 border=0><tr><td><input name=topic size=80 placeholder='Тема' type=text onkeyup=topicKey();></td><td>&nbsp;<input name=llen style='width:40px' value='$maxtopic' title='Осталось ввести символов'></td></tr></table></td></tr>
<input type=hidden name='zvezdmax'>
<input type=hidden name='repamax'>
<input type=hidden name='stopuser'>
<input type=hidden name='onlyuser'>
<input type=hidden name='sur'>
<input type=hidden name='our'>
<tr><td>
<details style='cursor:hand;border:0px solid #669900'>
<summary style='text-align:center;'>Дополнительные настройки</summary><br>
<table cellpadding=1 cellspacing=1 border=0>
<tr>
	<td class=row1 width='142px' align=right>Запретить пользователей</td>
	<td><input type=text style='width:400px' size=4 name='stopuser' value='' title='Ники пользователей (через пробел или запятую), которым запрещено участвовать в теме'><br><input type=radio class=radio name='sur' value=0 checked>Разрешить им читать тему<input type=radio class=radio name='sur' value=1>Запретить им читать тему</td>
</tr>
</table>
<br>
<table cellpadding=1 cellspacing=1 border=0>
<tr>
	<td class=row1 width='142px' align=right>Только для пользователей</td>
	<td><input type=text style='width:400px' size=4 name='onlyuser' value='' title='Ники пользователей (через пробел или запятую), которые могут участвовать в теме. Не забудьте вписать свой ник'><br><input type=radio class=radio name='our' value=0 checked>Другим можно читать тему<input type=radio class=radio name='our' value=1>Другим нельзя читать тему</td>
</tr>
</table>
<br>
<table cellpadding=1 cellspacing=1 border=0>
<tr>
	<td class=row1 align=right>Ограничение по репутации</td>
	<td><input type=text style='width:40px' size=4 maxlength=4 name='repamax' value='0' title='Участники форума имеющие столько баллов репутации смогут обсуждать эту тему.\nПример: 0 - тема доступна всем, 12 - тема доступна если есть 12 баллов репутации'></td>
</tr>
<tr>
	<td class=row1 align=right>Ограничение по звёздам</td>
	<td><input type=text style='width:40px' size=3 maxlength=1 name='zvezdmax' value='0' title='Участники форума имеющие столько звёзд (выдает админ) смогут обсуждать эту тему.\nПример: 0 - тема доступна всем, 1 - тема доступна если есть 1 звезда'></td>
</tr>
</table>
</details>
<br>
<table cellpadding=1 cellspacing=2 border=0 width=100%>
<tr>
<td width=270><input type=radio class=radio name=tt value=1 checked><img src='img/1.png'> $topic1</td>
<td><input type=radio class=radio name=tt value=2><img src='img/2.png'> $topic2</td>
</tr>
<tr>
<td><input type=radio class=radio name=tt value=3><img src='img/3.png'> $topic3</td>
<td><input type=radio class=radio name=tt value=4><img src='img/4.png'> $topic4</td>
</tr>
<tr>
<td><input type=radio class=radio name=tt value=5><img src='img/5.png'> $topic5</td>
<td><input type=radio class=radio name=tt value=6><img src='img/6.png'> $topic6</td>
</tr>
<tr>
<td><input type=radio class=radio name=tt value=7><img src='img/7.png'> $topic7</td>
<td><input type=radio class=radio name=tt value=8><img src='img/8.png'> $topic8</td>
</tr>
<tr>
<td><input type=radio class=radio name=tt value=9><img src='img/9.png'> $topic9</td>
<td><input type=radio class=radio name=tt value=10><img src='img/10.png'> $topic10</td>
</tr>
<tr>
<td><input type=radio class=radio name=tt value=11><img src='img/11.png'> $topic11</td>
<td><input type=radio class=radio name=tt value=12><img src='img/12.png'> $topic12</td>
</tr>
<tr>
<td><input type=radio class=radio name=tt value=13><img src='img/13.png'> $topic13</td>
<td><input type=radio class=radio name=tt value=14><img src='img/14.png'> $topic14</td>
</tr>
<tr>
<td><input type=radio class=radio name=tt value=15><img src='img/15.png'> $topic15</td>
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
<script>function checkedBox(f){if(f.check1.checked) document.getElementById('other').innerHTML='<center><input type=submit class=fbutton value=\'Отправить\'></center>';
else document.getElementById('other').innerHTML='<center><input type=submit class=fbutton value=\'Отправить\' disabled=\'disabled\'></center>';}</script>
<input type=checkbox style='height:20px;width:20px;' name=check1 onClick=\"checkedBox(this.form)\"></td><td>&nbsp; я не бот</td></tr></table></td></tr>
<tr><td><div id=other align=center><input type=submit class=fbutton value='Отправить' disabled='disabled'></div></td></tr></table></form>
<br><p align=center><a href=\"index.php?id=forum\">&#9668; назад</a></p>");

				} else {
					exit("<tr><td><img src=\"index.php?secpic\" id='secpic_img' style='border: 1px solid #000;' align='top' title='Для смены картинки щелкните по ней' onclick=\"document.getElementById('secpic_img').src='index.php?secpic&' + Math.random(); return false\">&nbsp;<input type='text' name='secpic' id='secpic' style='width:60px;' title='Введите $let_amount жирных симв. изображенных на картинке' maxlength='10'> <small>введите <b>$let_amount</b> жирных символа</small></td></tr><tr><td><input type=hidden name=add value=''><center><input type=submit class=fbutton value='Отправить'></center></td></tr></table></form><br><p align=center><a href='index.php?id=forum'>&#9668; назад</a></p>");

				}
			} else {
				show_error("Для создания тем на форуме вам необходимо<br><br>[<a href='index.php?mode=reg'>зарегистрироваться</a>]", "javascript:history.back(1)"); 
			}
		} else {
			exit("<p align=center><font color=red><b>Добавление тем запрещено!</b></font><br><br><a href='index.php?id=forum'>&#9668; назад</a></p>");
		}
	}
} else {
	print "<table cellpadding=2 cellspacing=1 align=center border=0 class=main><thead><th colspan=5><div align=right>";

	if (isset($_COOKIE['cadmin']) && isset($_COOKIE['cpass']) && $_COOKIE['cadmin']==$adminname && $_COOKIE['cpass']==$adminpass)
	{
		print " <a href='index.php?event=ban' style='color:red'>Бан</a> • <a href='admin.php?event=config' style='color:red'>Настройки</a> • <a href='admin.php?event=userwho' style='color:red'>Профили</a> • ";
	}
	print "<a href='index.php?mode=admin'>Админка</a> • <a href='index.php?mode=reg'>Регистрация</a> • <a href='index.php?event=who' title='Последним зарегистрировался: $tdt[0]'>Участники ($ui)</a> • ";

	if ($_['user'] && isset($_COOKIE['cname']) && isset($_COOKIE['cpassreg']))
	{
		print "<a href=\"index.php?event=profile&pname=".$_['user']."\">Мой профиль</a></b> • <a href='index.php?action=newtopic&fid=$fid'>Создать тему</a> "; //$codename
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
			if ($pmi>0) print"[<a href=\"pm.php?readpm&id=$cname\"><font color=red>ЛС: $pmi</font></a>]"; else print"[<a href=\"pm.php?readpm&id=$cname\">ЛС: 0</a>]";
		} else {
			print"[<a href=\"pm.php?readpm&id=$cname\">ЛС: 0</a>]";
		}			
	} else {
		print"[<a href='index.php?event=login'>Вход</a>]";
	}

 	print "</div></th><tr class=th><td>!</td><td>Тема</td><td>Ответы / Просм.</td><td>Обновление</td></tr></thead><tbody>";


	if (is_file("datan/topic.dat"))
	{
		$lines=file("datan/topic.dat");

		$i=count($lines);

		do {
			$i--;
			$dts=explode("¦", $lines[$i]);
			if (is_file("data/$dts[2]")) $stime=filemtime("data/$dts[2]"); else $stime="";
			if ($dts[11]=="vip") {$stime=268;}
			$newlines[$i]="<!--$stime--> $dts[0]¦$dts[1]¦$dts[2]¦$dts[3]¦$dts[4]¦$dts[5]¦$dts[6]¦$dts[7]¦$dts[8]¦$dts[9]¦$dts[10]¦$dts[11]¦$dts[12]¦$dts[13]¦$dts[14]¦$dts[15]¦$dts[16]¦$dts[17]¦$dts[18]¦$dts[19]¦$dts[20]¦$dts[21]¦$dts[22]¦$dts[23]¦$dts[24]¦";
		} while($i > 0);
		rsort($newlines);

		for($a=0; $a<count($lines); $a++)
		{
			//$dn=explode("¦", $lines[$a]);
			$dn=explode("¦", $newlines[$a]);

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
				if ($dn[11]=="vip") $titletopic="VIP - тема";

				print "<img align=absmiddle src='img/$dn[11].png' title='$titletopic'></td><td><table border=0 width=100%><tr><td>";

				if ($dn[11]=="vip")
				{
					print "<a href='index.php?forumid=$dn[2]' style='color:red' class='topic'>".trim($dn[4])."</a>&nbsp; <span style='background:red;color:#000;border-radius:4px;font-weight:bold'>&nbsp;VIP&nbsp;</span> $admbuttons &nbsp;";
				} else {
					if ($dn[12]>0)
					{
						print "<a href='index.php?forumid=$dn[2]' class='topic'>".trim($dn[4])."</a>&nbsp; <span style='background:red;color:#000;border-radius:4px;font-weight:bold;cursor:help;' title='Тема для пользователей имеющих $dn[12] звезд и больше (выдаёт админ)'>&nbsp;&#9733; $dn[12]&nbsp;</span> $admbuttons &nbsp;";
					} else {
						if ($dn[13]>0)
						{
							print "<a href='index.php?forumid=$dn[2]' class='topic'>".trim($dn[4])."</a>&nbsp; <span style='background:red;color:#000;border-radius:4px;border:red 0px solid;font-weight:bold;cursor:help' title='Тема для пользователей с репутацией $dn[13] баллов и больше'>&nbsp;&hearts; $dn[13]&nbsp;</span> $admbuttons &nbsp;";
						} else {
							print "<a href='index.php?forumid=$dn[2]' class='topic'>".trim($dn[4])."</a> $admbuttons &nbsp;";
						}
					}
				}

				print"<a href=\"index.php?forumid=".$dn[2]."&page=".$pages."#last\" style='text-decoration:none' title='Страниц: $pages\nПерейти к последней странице'>&#9658;</a><br>";

				if (isset($_COOKIE['cadmin']) && isset($_COOKIE['cpass']) && $_COOKIE['cadmin']==$adminname && $_COOKIE['cpass']==$adminpass)
				{
					print"&nbsp; <a href=\"mailto:$dn[1]\" title='Автор темы'>$dn[0]</a> <i class=small>($dn[3])</i>";
				} else {
					print"&nbsp; <i title='Автор темы'>$dn[0]</i> <div style='display:inline-block;vertical-align:middle;' class=\"$dn[5]\" title=\"$dn[6], $dn[8]\"></div> <i class=small>($dn[3])</i>";
				}


				if ($pages>1)
				{
					print "&nbsp;<span class=med> &nbsp; [стр. ";
					if ($pages<=3) $f1=$pages; else $f1=3;
					for($i=1; $i<=$f1; $i++) {print "<a href='index.php?forumid=$dn[2]&page=$i'>$i</a>&nbsp;";}
					if ($pages>3) print "... <a href='index.php?forumid=$dn[2]&page=$pages'>$pages</a>";
					print "]</span>";
				}

				print "</td><td align='right'>";

				if (is_file("data/$dn[2].user")) print "<font color=red title='Тема для определённых пользователей, либо имеет ограничения на доступ'><svg aria-hidden='true' focusable='false' viewBox='0 0 16 16' height='16' width='16' fill='currentColor' display='inline-block' overflow='visible' style='vertical-align: text-bottom;'><path d='M7.467.133a1.748 1.748 0 0 1 1.066 0l5.25 1.68A1.75 1.75 0 0 1 15 3.48V7c0 1.566-.32 3.182-1.303 4.682-.983 1.498-2.585 2.813-5.032 3.855a1.697 1.697 0 0 1-1.33 0c-2.447-1.042-4.049-2.357-5.032-3.855C1.32 10.182 1 8.566 1 7V3.48a1.75 1.75 0 0 1 1.217-1.667Zm.61 1.429a.25.25 0 0 0-.153 0l-5.25 1.68a.25.25 0 0 0-.174.238V7c0 1.358.275 2.666 1.057 3.86.784 1.194 2.121 2.34 4.366 3.297a.196.196 0 0 0 .154 0c2.245-.956 3.582-2.104 4.366-3.298C13.225 9.666 13.5 8.36 13.5 7V3.48a.251.251 0 0 0-.174-.237l-5.25-1.68ZM8.75 4.75v3a.75.75 0 0 1-1.5 0v-3a.75.75 0 0 1 1.5 0ZM9 10.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0Z'></path></svg></font>";

				print "</td></tr></table></td><td align=center width='150px'>
<div align=left>&nbsp; &nbsp;<svg aria-hidden='true' focusable='false' viewBox='0 0 16 16' width='16' height='16' fill='currentColor' display='inline-block' overflow='visible' style='vertical-align: text-bottom;'><path d='M1 2.75C1 1.784 1.784 1 2.75 1h10.5c.966 0 1.75.784 1.75 1.75v7.5A1.75 1.75 0 0 1 13.25 12H9.06l-2.573 2.573A1.458 1.458 0 0 1 4 13.543V12H2.75A1.75 1.75 0 0 1 1 10.25Zm1.75-.25a.25.25 0 0 0-.25.25v7.5c0 .138.112.25.25.25h2a.75.75 0 0 1 .75.75v2.19l2.72-2.72a.749.749 0 0 1 .53-.22h4.5a.25.25 0 0 0 .25-.25v-7.5a.25.25 0 0 0-.25-.25Z'></path></svg> $cnt</div>

<div align=right><svg aria-hidden='true' focusable='false' viewBox='0 0 16 16' width='16' height='16' fill='currentColor' display='inline-block' overflow='visible' style='vertical-align: text-bottom;'><path d='M8 2c1.981 0 3.671.992 4.933 2.078 1.27 1.091 2.187 2.345 2.637 3.023a1.62 1.62 0 0 1 0 1.798c-.45.678-1.367 1.932-2.637 3.023C11.67 13.008 9.981 14 8 14c-1.981 0-3.671-.992-4.933-2.078C1.797 10.83.88 9.576.43 8.898a1.62 1.62 0 0 1 0-1.798c.45-.677 1.367-1.931 2.637-3.022C4.33 2.992 6.019 2 8 2ZM1.679 7.932a.12.12 0 0 0 0 .136c.411.622 1.241 1.75 2.366 2.717C5.176 11.758 6.527 12.5 8 12.5c1.473 0 2.825-.742 3.955-1.715 1.124-.967 1.954-2.096 2.366-2.717a.12.12 0 0 0 0-.136c-.412-.621-1.242-1.75-2.366-2.717C10.824 4.242 9.473 3.5 8 3.5c-1.473 0-2.825.742-3.955 1.715-1.124.967-1.954 2.096-2.366 2.717ZM8 10a2 2 0 1 1-.001-3.999A2 2 0 0 1 8 10Z'></path></svg>&nbsp;";

				include "data/$dn[2].dat";

				print "&nbsp; &nbsp;</div></td><td align=right width='150px'>";

				if (empty($dn[15]))
				{
					print "---&nbsp;";
				} else {
					$dn[18]=trim(replacer($dn[18]));
					$dn[18]=str_replace("&lt;br&gt;", "\r\n", $dn[18]);
					$dn[18]=str_replace(array(
								"[code]", "[/code]",
								"[quote]", "[/quote]",
								"[video]", "[/video]",
								"[audio]", "[/audio]",
								"[vimeo]", "[/vimeo]",
								"[dzen]", "[/dzen]",
								"[rutube]", "[/rutube]",
								"[youtube]", "[/youtube]",
								"[ok]", "[/ok]",
								"[telegram]", "[/telegram]",
								"[map]", "[/map]",
								"[left]", "[/left]",
								"[center]", "[/center]",
								"[right]", "[/right]",
								"[img]", "[/img]",
								"[b]", "[/b]",
								"[i]", "[/i]",
								"[u]", "[/u]",
								"[s]", "[/s]",
								"[big]", "[/big]",
								"[small]", "[/small]",
								"[red]", "[/red]",
								"[blue]", "[/blue]",
								"[green]", "[/green]",
								"[orange]", "[/orange]",
								"[yellow]", "[/yellow]"), "", $dn[18]);

					$dn[18]=preg_replace("/\[hide\](.+?)\[\/hide\]/is", " [Текст скрыт от гостей] ", $dn[18]);
					$dn[18]=preg_replace("/\[hide=(.+?)\](.+?)\[\/hide\]/is", " [Текст для \\1] ", $dn[18]);

					print "<span style='display:none;'>$ftime</span>";
					print "<a href=\"index.php?forumid=".$dn[2]."&page=".$pages."#last\" title=\"Перейти к сообщению:\n\n$dn[18]\">$dn[15]</a>&nbsp;";
					print "<div style='display:inline-block;vertical-align:middle;' class=\"$dn[19]\" title=\"$dn[20], $dn[22]\"></div>";
				}
				print "<br>";
				if (empty($dn[15])) print "---&nbsp;"; else print "<i class=med>$dn[17]</i>&nbsp;";
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