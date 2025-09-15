<?php // WR-forum v2.2 / 10.01.2019 / WR-Script.ru / ЛС

error_reporting(0);
//error_reporting (E_ALL);
   
include "config.php";

$datapmdir="data-pm"; // Папка с личными сообщениями

$shapka='<html><head>
<title>Отправка / Просмотр ЛС</title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">
<meta http-equiv="Content-Language" content="ru">
<link rel="stylesheet" href="'.$fskin.'/style.css" type="text/css">
<script>function x(){return;}function FocusText(){document.REPLIER.msg.focus();document.REPLIER.msg.select();return true;}function DoPrompt(action){var revisedMessage; var currentMessage=document.REPLIER.msg.value;}</script>
</head>
<body bgcolor="#E5E5E5" text="#000000" link="#006699" vlink="#5493B4">
<center><BR><BR><BR>';

// Очищаем переменные
$name="";
$flag=FALSE;

if (isset($_COOKIE['cname']) and isset($_COOKIE['cpassreg']))
{
	//$wrfc=$_COOKIE['wrfcookies'];
	//$wrfc=stripslashes(htmlspecialchars($wrfc,ENT_COMPAT,"windows-1251"));
	//$wrfc=explode("|", $wrfc);
	//$wrfname = $wrfc[0];
	//$wrfpass = $wrfc[1];

	$name = urldecode(strtolower($_COOKIE['cname']));

	// Сверяем логин и пароль в базе с тем, что у нас хранится в КУКАХ
	$lines=file("datan/usersdat.php");
	$maxi=count($lines);
	$i="1";

	do {
		$dt=explode("|",$lines[$i]); $i++;
		$dt[0]=strtolower($dt[0]);

		if ($dt[0]===$name and md5($dt[1])===$_COOKIE['cpassreg']) {$flag=TRUE; $i=$maxi;}

	} while($i < $maxi);

} else echo "$shapka <br><br><br><font color=red><b>Личные сообщения только для зарегистрированных пользователей!</b></font>";



if ($flag===TRUE)
{
	function replacer($text) {
		$text=str_replace("&#032;",' ',$text);
		$text=str_replace(">",'&gt;',$text);
		$text=str_replace("<",'&lt;',$text);
		$text=str_replace("\"",'&quot;',$text);
		$text=preg_replace("/\n\n/",'<p>',$text);
		$text=preg_replace("/\n/",'<br>',$text);
		$text=preg_replace("/\\\$/",'&#036;',$text);
		$text=preg_replace("/\r/",'',$text);
		$text=preg_replace("/\\\/",'&#092;',$text);
		// если magic_quotes включена - чистим везде СЛЭШи в этих случаях: одиночные (') и двойные кавычки ("), обратный слеш (\)
		if (get_magic_quotes_gpc()) {$text=str_replace("&#092;&quot;",'&quot;',$text); $text=str_replace("&#092;'",'\'',$text); $text=str_replace("&#092;&#092;",'&#092;',$text);}
		$text=str_replace("\r\n","<br> ",$text);
		$text=str_replace("\n\n",'<p> ',$text);
		$text=str_replace("\n",'<br> ',$text);
		$text=str_replace("\t",'',$text);
		$text=str_replace("\r",'',$text);
		$text=str_replace('   ',' ',$text);
		return $text;
	}

	if (isset($_GET['id']) or isset($_POST['id']))
	{
		if (isset($_GET['id'])) $id=replacer($_GET['id']);
		if (isset($_POST['id'])) $id=replacer($_POST['id']);

		$id=urldecode($id);
		$id=strtolower($id);

		if (is_file("$datapmdir/$id.dat"))
		{
			$linesn=file("$datapmdir/$id.dat");
			$in=count($linesn);

			if ($in > 500) exit("<br><br><center><b>Максимальное количество сообщений достигнуто!</b><br><br>Удалите у себя ненужные сообщения или скажите об этом получателю!</center><br><br>[<a href='javascript:history.back(1)'>&#9668; назад</a>]");

		}

		////////////// Блок УДАЛЕНИЯ выбранного СООБЩЕНИЯ
		if (isset($_GET['delmsg']))
		{ 
			$num=replacer($_GET['delmsg']);
			if ($num=="" or strlen($num)<5) exit("$shapka ОШИБКА! Не выбрано удаляемое сообщение!");

			if (is_file("$datapmdir/$id.dat"))
			{
				$file=file("$datapmdir/$id.dat");
				$fp=fopen("$datapmdir/$id.dat","w");
				flock ($fp,LOCK_EX);
				for ($i=0;$i< sizeof($file);$i++) {
					$dt=explode("|",$file[$i]);
					if ($dt[1]==$num) unset($file[$i]);
				}
				fputs($fp, implode("",$file));
				flock ($fp,LOCK_UN);
				fclose($fp);
			}
			Header("Location: pm.php?readpm&id=$id");
			exit;
		}

		///////////// ОЧИСТКА ЯЩИКА
		if (isset($_GET['alldel']))
		{ 
			if ($id==$name & is_file("$datapmdir/$id.dat"))
			{
				unlink ("$datapmdir/$id.dat");
				print "$shapka <p align=center><b>Личные сообщения удалены!</b><br><br>Вы можете перейти на <a href='index.php'>главную страницу форума</a></p>";
			} else
				exit("$shapka У вас нет сообщений!<br><br>[<a href='javascript:history.back(1)'>&#9668; назад</a>]");
		}

		////////////// ОТПРАВКА СООБЩЕНИЯ
		if (isset($_GET['sendpm']))
		{ 
			exit("$shapka <center><br><br><B>Сообщение отправлено!</B><br><br><br>Вы можете закрыть это окно, либо перейти в папку с вашими ЛС <br><br><b>[<a href='pm.php?readpm&id=$name' target='_new'>Перейти к моим ЛС</a>]</b>");
		}

		/////////////// ПРОСМОТР СООБЩЕНИЙ
		if (isset($_GET['readpm']))
		{
			touch("$datapmdir/$id.dat"); // если нет файла с ЛС, то создаём файл юзеру

			if (is_file("$datapmdir/$id.dat") & $id===strtolower($name))
			{
				$rlines=file("$datapmdir/$id.dat");
				$ri=count($rlines);
				$key="0";

				//////////// Если файл не пуст
				if ($ri>0)
				{
					$type=0;

					print "$shapka";

					echo'<center><font style="font-size:14px;font-family:tahoma">Личные сообщения <b>'.$id.'</b> [Всего: <b>'.$ri.'</b>]</font></center><br><table cellpadding=0 cellspacing=1 border=1 width=100%><tr><th width=170 height=22 nowrap=nowrap>Отправитель</th><th nowrap=nowrap><p align=center>Сообщение</p></th></tr>';

					do {
						$ri--;
						$edt=explode("|",$rlines[$ri]);
						$number=$ri+1;

						//$data=date("d.m.y H:i",$edt[1]);
						$data=$edt[1];

						$edt[7]=replacer($edt[7]);
						$edt[5]=replacer($edt[5]);
						$edt[8]=replacer($edt[8]);
						$edt[8]=str_replace("&lt;br&gt;",'<BR>',$edt[8]);

						if ($key==0) {$cvet="#E2F1FC"; $key=1;} else {$cvet="#F1F9FE"; $key=0;}

						print"<tr height=120><td class=row2 valign=top rowspan=2><br><center><b>$edt[5]</b><br><br><small><i>$data</i>&nbsp; [#<b>$number</b>]</small><br><br><br><br>";

						print"[<a href='pm.php?id=$id&delmsg=$edt[1]' onclick=\"return confirm('Удалить это сообщение?')\" style=\"text-decoration:none;font-size:11px;\">удалить</a>] &nbsp;";

						//href='pm.php?id=$edt[5]'
						if ($type==0) print"[<a href='#' name=citata onclick=\"window.open('pm.php?id=$edt[5]','citata','width=800,height=500,left=100,top=100,toolbar=0,status=0,border=0,scrollbars=1');return false;\" style='text-decoration:none;font-size:11px'>ответить</a>]</td><td class=row1 width=100% height=100px valign=top><table border=0 width=100% height=100%><tr valign=center><td><p align=justify>Тема: <font color=navy><u>$edt[7]</u></font><br>$edt[8]</p></td></tr></table></td></tr>";


						////////////// БЛОК быстрого ОТВЕТА

						print "<tr><td class=row2>
<FORM action='pm.php?savepm&id=$edt[5]' method=post name=REPLIER><center>
<font class=norm><details style='cursor:pointer'><summary>Быстрый ответ</summary><br>
<table cellpadding=2 cellspacing=0 width=800 border=0>
<tr><td align=center><font class=norm>Ответить пользователю <b>$edt[5]</b></font></td></tr>
<tr><td>Тема: <input type=text name=tema value='RE: $edt[7]' style='width:100%' title='Не более $maxtopic симв.'></td></tr>
<tr><td>Сообщение <small>(не более $maxmsg симв)</small><br><textarea name=msg cols=92 rows=11 style='width:100%;height:150px'></textarea></td></tr>
<tr><td align=center><input type=submit class=button style='width:100px;height:25px' value='Отправить'><br></td></tr>
</table></details></font></form>";

					} while($ri>0);

					echo"</TD></tr></table><p align=right><b>[<a href='pm.php?alldel&id=$name' onclick=\"return confirm('Удалить все сообщения?')\">Удалить сообщения</a>] &nbsp; [<a href='index.php'>вернуться на форум</a>]</b>&nbsp;</p>";

				} else {
					print "$shapka";

					echo'<br><center><font style="font-size:14px;font-family:tahoma">Личные сообщения <b>'.$id.'</b><br></font><br><table class=forumline width=80% cellspacing=1 cellpadding=0><tr><th class=thLeft width=170 height=22></th><th>&nbsp;<p align=center></p></th></tr>';

					print "<tr height=150><td class=row2 valign=top colspan=2><span class=name><br><center><center><h2>У вас нет сообщений!<br></h2>";

					if (isset($_GET['user'])) $useremail=$_GET['user']; else $useremail="";

					print"<center><TABLE cellPadding=2 cellSpacing=1 width=775 border=0><br><FORM action='pm.php?savepm' method=post><TBODY><TR><TD align=middle colSpan=2></TD></TR><TR><TD align=center>Отправитель: <B>$id</B> &nbsp; | &nbsp; Получатель: ";

					echo'<SELECT name=id class=maxiinput><option value=""> Выбрать </option>\r\n';

					// Блок считывает всех пользователей из файла
					if (is_file("datan/usersdat.php")) $lines=file("datan/usersdat.php");

					if (!isset($lines)) $datasize=0; else $datasize=sizeof($lines)-1;

					if ($datasize<=0) exit("Проблемы с базой пользователей!<br><br>[<a href='javascript:history.back(1)'>&#9668; назад</a>]");

					$imax=count($lines); $i="1";

					do {
						$dt=explode("|", $lines[$i]);
						print "<OPTION value=\"$dt[0]\">$dt[0] [$i]</OPTION>\r\n";
						$i++;
					} while($i < $imax);

					echo'</SELECT></TD></TR><tr><td><b>Тема:</b><input type=text name=tema value="Сообщение" style="width:100%"></td></tr>
<TR><TD><b>Сообщение</b> <small>(не более '.$maxmsg.' симв)</small><br><TEXTAREA name=msg style="FONT-SIZE: 14px; HEIGHT: 180px; WIDTH: 800px"></TEXTAREA></TD></TR>
<TR><TD colspan=2><center><INPUT type=submit value="ОТПРАВИТЬ" style="height:25;width:100px" class=button></form></TD></TR></TBODY></TABLE><br></center>';

					print "<h3>[<a href='index.php'>вернуться на форум</a>]</h3></center></td></TR></TABLE>";

					echo'';
				}
			}
		}

		///////////// СОХРАНЕНИЕ сообщения
		if (isset($_GET['savepm']))
		{
			$msg=replacer($_POST['msg']);
			$msg=str_replace("|","",$msg);
			$tema=replacer($_POST['tema']);
			$tema=str_replace("|","",$tema);

			if ($msg=="" || strlen($msg)>$maxmsg) exit("$shapka <center><B>Ваше сообщение пустое или превышает $maxmsg симв.</B><br><br>[<a href='javascript:history.back(1)'>&#9668; назад</a>]</center>");

			if (strlen($tema)>$maxtopic) exit("$shapka <center><B>Тема превышает $maxtopic симв.</B><br><br>[<a href='javascript:history.back(1)'>&#9668; назад</a>]</center>");

			// Считываем всех пользователей, ищем того, которому адресовано сообщение
			$i="0";
			$from_rn="";

			$tektime=time();

			$datee=gmdate('d.m.Y', time() + 3600*($timezone+(date('I')==1?0:1)));
			$timee=gmdate('H:i', time() + 3600*($timezone+(date('I')==1?0:1)));

			$to_rn=FALSE;
			$rn=mt_rand(10000,99999);

			$lines=file("datan/userstat.dat");
			$maxi=count($lines);
			do {
				$dt=explode("|",$lines[$i]);
				$i++;
				$dt[0]=strtolower($dt[0]);

				if ($dt[0]===$name) $from_rn="$dt[0]"; // Ищем RN-ключ юзера отправившего сообщение

				// Этот ключ НЕЛЬЗЯ передавать через форму! ОН нужен для безопасности скрипта
				if ($dt[0]===$id) $to_rn=$dt[0]; // ЕСЛИ нашли юзера, которому адресовано сообщение, то выставляем ФЛАГ

			} while($i < $maxi);

			//////////////////// Структура файла: rn|time|status|from_rn|to_rn|from_name|to_name|tema|msg|rezerved|
			if ($to_rn!=FALSE)
			{
				$text="$rn|$datee $timee|0|$from_rn|$to_rn|$name|$id|$tema|$msg||\r\n";
				$fp=fopen("$datapmdir/$id.dat","a+");
				flock ($fp,LOCK_EX);
				fputs($fp,$text);
				fflush ($fp);
				flock ($fp,LOCK_UN);
				fclose($fp);
			}
			exit("<script>function reload(){location=\"pm.php?sendpm&id=$id\"}; setTimeout('reload()',1000);</script>");
		}

		/////////////// Если нет никакого вывода и ЧТОБЫ самому себе письма не отправлять
		if ($name!=$id)
		{
			print "$shapka <FORM action='pm.php?savepm&id=$id' method=post name=REPLIER><center>
<table cellpadding=1 cellspacing=0 width=750 align=center border=1>
<tr><td height=25 class=row2><center><span class=norm>Личное сообщение для <b>$id</b></span></center></td></tr>
<tr><td height=40 class=row1><b>Тема:</b> <input type=text name=tema style='width:95%;height:25px' title='Не более $maxtopic симв'></td></tr>
<tr><td class=row1><b>Сообщение</b> <small>(не более $maxmsg симв)</small><br><textarea name=msg cols=92 rows=11 style='width:100%;height:200px'></textarea></td></tr>
<tr><td height=35 class=row1><center><input type=submit tabindex=5 style='width:100px;height:25;cursor:pointer' value='Отправить'></center></td></tr>
</table><br><br><center>[<a href='' onClick='self.close()'>закрыть окно</a>]</center><br></form>";

		}

	} else echo'Отсутствует ключ id';

} else exit("<br><br><p align=center>Идентификатор пользователя неверный!<br><br><br>[<a href='' onClick='self.close()'>закрыть</a>] &nbsp; [<a href='index.php'>на форум</a>]</p>");

?>
</body>
</html>