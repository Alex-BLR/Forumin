<?php

//error_reporting (E_ALL);
error_reporting(0);

include dirname(__FILE__)."/config.php";


////////////////// Очистка кода
function replacer($text) {
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


////////////////// Функция проверки пользователя: если куки совпадают с данными в базе - пользователь
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



////////////////// Изменение репутации участником форума
if (isset($_GET['event']))
{
	$event=replacer($_GET['event']);

	if ($event=="repa")
	{ 
		$name=replacer($_GET['name']);

		$mname=replacer($_COOKIE['cname']);

		// Если куков нет - облом, если куки есть и равны имени юзера - облом.
		if (!$user) exit("<center><font face=tahoma size=2><br><br><br>Изменять репутацию могут только участник форума!<br><br>[<a href='' onClick='self.close()'>закрыть окно</a>]</font></center>");

		else {
			if ($_COOKIE['cname']===$name) print"<center><font face=tahoma size=2><b>Менять репутацию самому себе запрещено!</b><br><br>[<a href='' onClick='self.close()'>закрыть окно</a>]</font></center><br>";

			else {
				print"
<html><head><title>Изменить репутацию: $name</title>
<meta http-equiv='Content-Type' content='text/html; charset=windows-1251'>
<meta http-equiv='Content-Language' content='ru'>
</head><body><center><style>body,table{font-family:tahoma;font-size:12px;}</style>
<FORM action='admin.php?event=repasave' method=post>
<table cellpadding=0 cellspacing=0 border=0 width=100% style='background:#eee;border:1px solid #000;'>
<TR height=25><TD colspan=7 align=center>Изменение репутации пользователя <b>$name</b></font></TD></TR>
<TR height=25>
<TD width=60 align=center><big>–5</big><INPUT name=repa type=radio value='-5'></TD>
<TD width=60 align=center><big>–3</big><INPUT name=repa type=radio value='-3'></TD>
<TD width=60 align=center><big>–1</big><INPUT name=repa type=radio value='-1'></TD>
<TD width=60 align=center><INPUT name=repa checked type=radio value='0'>&nbsp;</TD>
<TD width=60 align=center><INPUT name=repa type=radio value='+1'><big>+1</big></TD>
<TD width=60 align=center><INPUT name=repa type=radio value='+3'><big>+3</big></TD>
<TD width=60 align=center><INPUT name=repa type=radio value='+5'><big>+5</big></TD></TR>
<INPUT type=hidden name=name value='$name'>
<TR height=30><TD colspan=7 bgColor=#eeeeee align=center>Причина <INPUT type=text name='pochemu' size=60 maxlength=250 value='' title='Причина не более 250 символов'> <INPUT type=submit value='ОK'></TD></TR>
</TABLE></FORM>
<div align=center><fieldset align=justify style='width:520px;border:#333 1px solid;'>
<legend align=center><b><font color=red>ПРАВИЛА И УСЛОВИЯ ИЗМЕНЕНИЯ РЕПУТАЦИИ</font></b></legend>
При изменении чьей-либо репутации обязательно указывайте обоснованную причину! Изменение репутации без особой на то причины может повлечь штраф от админа. Учтите, что если вы, например, ставите пользователю +5 или -5, то и от вашей репутации отминусуется 5 баллов вне зависимости прибавляете ли вы или отнимаете баллы! </fieldset></div><br>[<a href='' onClick='self.close()'>Закрыть это окно</a>]<br><br>";

			}

			 // Ищем в файле repa.dat инфу об этом юзере
			if (is_file("datan/repa.dat"))
			{
				$file="datan/repa.dat";
				$lines=file("$file");
				$i=count($lines);

				print"<style>table,th,td{border:1px solid black;border-collapse:collapse;font-family:tahoma;font-size:12px;}tr:nth-child(even){background-color:#f5f5f5}</style><table border=0 cellpadding=1 cellspacing=1 width=100%><TR><TD bgColor=#dddddd colspan=5 align=center>Кто менял репутацию пользователя <b>$name</b></td></tr><TR align=center><TD width=80>Дата</TD><TD>Менял</TD><TD width=30>Балл</TD><TD width=60%>Причина</TD></TR>";

				do {
					$i--;
					$dt=explode("|",$lines[$i]);

					if (strlen($dt[3])>1) $dt[3]="<a href='index.php?event=profile&pname=$dt[3]' rel='nofollow' target=_blank>$dt[3]</a>"; else $dt[3]="бот форума";

					if ($dt[1]>0) $dt[1]="<TD align=center bgcolor=#B7FFB7><b><small>$dt[1]"; else $dt[1]="<TD align=center bgcolor=#FF9F9F><b><small>$dt[1]";

					if ($dt[2]==$name)
					{
						$dt[0]=date("d.m.y в H:i",$dt[0]);

						print "<TR><TD align=center><small>$dt[5]</small></TD><TD align=center><small>$dt[3]</small></TD>$dt[1]</small></b></TD><TD><small>$dt[4]</small></TD></TR>";
					}

				} while($i>0);

				echo'</table>';

			}
			echo'</body></html>';
			exit;
		}
	}

	// РЕПУТАЦИЯ: СОХРАНЕНИЕ ШАГ 2
	if ($event=="repasave")
	{
		if (isset($_COOKIE['cname']) and isset($_COOKIE['cpassreg'])) $wrfname=htmlspecialchars(stripslashes($_COOKIE['cname']),ENT_COMPAT,"windows-1251"); else exit("Только участники форума могут менять репутацию!");

		if (!isset($_POST['name'])) exit("Нет данных переменной name");

		$name=replacer($_POST['name']);

		if (isset($_POST['repa'])) $repa=$_POST['repa']; else exit("Нет данных переменной repa");

		if (isset($_POST['pochemu'])) $pochemu=$_POST['pochemu']; else exit("<center><b>Укажите причину!</b><br><br><a href='javascript:history.back(1)'>&#9668; назад</a></center>");

		if (!is_numeric($repa)) exit("<br><br><center><b>Ошибка!</b></center>");

		if ($repa>5 or $repa<-5) exit("<br><br><center><b>Репутацию можно менять максимум на 5 баллов!</b><br><br><a href='javascript:history.back(1)'>&#9668; назад</a></center>");

		if (strlen($pochemu)<5 or strlen($pochemu)>250) exit("<br><br><center><font face=tahoma size=2><b>Вы не ввели причину изменения репутации (от 5 до 250 симв)!</b><br><br><a href='javascript:history.back(1)'>&#9668; назад</a></font></center>");

		$dater = gmdate('d.m.Y', time()+3600*($timezone+(date('I')==1?0:1)));
		$timer = gmdate('H:i', time()+3600*($timezone+(date('I')==1?0:1)));
		$repadate="$dater в $timer";

		$today=time();

		// БЛОК добавляет + к репутации ЮЗЕРА
		$ulines=file("datan/userstat.dat");
		$ui=count($ulines)-1;

		$ulinenew=""; $rlinenew="";

		$ip=$_SERVER['REMOTE_ADDR'];

		// Ищем юзера по имени в файле userstat.dat, если недавно голосовали за него, запрещаем
		for ($i=0;$i<=$ui;$i++)
		{
			$udt=explode("|",$ulines[$i]);

			if ($udt[0]==$name)
			{
				$udt[3]=$udt[3]+$repa;

				if (strlen($udt[5])>5)
				{
					$next=$today-$udt[5];
					sleep(1);

					if ($ip==$udt[6]) exit("<br><br><center><font face=tahoma size=2><b>Вы уже меняли репутацию этого участника!</b><br><br><a href='javascript:history.back(1)'>&#9668; назад</a></font></center>");

					if ($next<3600)
					{
						$last=3600-$next;
						exit("<br><br><center><font face=tahoma size=2><b>Репутация уже менялась! Ждите $last сек</b><br><br><a href='javascript:history.back(1)'>&#9668; назад</a></font></center>");
					}
				}
				$ulines[$i]="$udt[0]|$udt[1]|$udt[2]|$udt[3]|$udt[4]|$today|$ip|$udt[7]|$udt[8]|\r\n";
			}
			$ulinenew.="$ulines[$i]";
		}
		$fp=fopen("datan/userstat.dat","w");
		flock($fp,LOCK_EX);
		fputs($fp,"$ulinenew");
		fflush($fp);
		flock($fp,LOCK_UN);
		fclose($fp);


		/////////////////////////////
		// Ищем юзера который меняет репу и отнимаем от его репы выставляемый им балл
		for ($ir=0;$ir<=$ui;$ir++)
		{
			$udr=explode("|",$ulines[$ir]);

			if ($udr[0]==$user)
			{
				$urep=abs($repa);
				$udr[3]=$udr[3]-$urep;
				$ulines[$ir]="$udr[0]|$udr[1]|$udr[2]|$udr[3]|$udr[4]|$udr[5]|$udr[6]|$udt[7]|$udt[8]|\r\n";
			}
			$rlinenew.="$ulines[$ir]";
		}
		$fp=fopen("datan/userstat.dat","w");
		flock($fp,LOCK_EX);
		fputs($fp,"$rlinenew");
		fflush($fp);
		flock($fp,LOCK_UN);
		fclose($fp);
		/////////////////////////////

		//дата в UNIX|балл|кому меняли|кто менял|причина|дата|||
		$fp=fopen("datan/repa.dat","a+");
		flock($fp,LOCK_EX);
		fputs($fp,"$today|$repa|$name|$wrfname|$pochemu|$repadate|||\r\n");
		fflush($fp);
		flock($fp,LOCK_UN);
		fclose($fp);

		exit("<br><br><center><font face=tahoma size=2><b>Рейтинг успешно пересчитан!</b><br><br><br>[<a href='' onClick='self.close()'>Закрыть окно</a>]</font></center>");
	}
}



include dirname(__FILE__)."/$fskin/top.html";





if (isset($_COOKIE['cadmin']) && isset($_COOKIE['cpass']))
{
	if ($_COOKIE['cadmin']==$adminname && $_COOKIE['cpass']==$adminpass)
	{
		//////////////// Изменяем РЕПУТАЦИЮ юзера
		if (isset($_GET['newrepa']))
		{
			if (isset($_GET['page'])) $page=$_GET['page']; else $page=1;
			$text=$_POST['repa'];
			$usernum=$_POST['usernum']-1;
			$text=htmlspecialchars($text,ENT_COMPAT,"windows-1251");
			$text=stripslashes(str_replace("|"," ",$text));

			$repa=str_replace("\r\n","<br>",$text);
			$lines=file("datan/userstat.dat");
			$dt=explode("|", $lines[$usernum]);

			$txtdat="$dt[0]|$dt[1]|$dt[2]|$repa|$dt[4]|$dt[5]|$dt[6]|$dt[7]|||";

			$fp=fopen("datan/userstat.dat","a+");
			flock($fp,LOCK_EX);
			ftruncate($fp,0); // УДАЛЯЕМ СОДЕРЖИМОЕ ФАЙЛА

			for ($i=0;$i<=(sizeof($lines)-1);$i++)
			{
				if ($i==$usernum) fputs($fp,"$txtdat\r\n"); else fputs($fp,$lines[$i]);
			}
			fflush($fp);
			flock($fp,LOCK_UN);
			fclose($fp);

			//Header("Location: admin.php?event=userwho&page=$page");

			print "<meta charset='windows-1251'><script>function reload(){location=\"javascript:history.back(1)\"};setTimeout('reload()',300);</script><br><br><br><br><br><br><center><font size=2 face=tahoma><b>Данные успешно изменены!<br><br><a href='javascript:history.back(1)' style='text-decoration:none;'>Продолжить</a></b></font></center>";

			exit;
		}


		//////////////// Добавляем/снимаем ШТРАФЫ ЮЗЕРУ
		if (isset($_GET['userstatus']))
		{
			if (isset($_GET['page'])) $page=$_GET['page']; else $page=1;
			$text=$_POST['status'];
			$usernum=$_POST['usernum']-1;
			$text=htmlspecialchars($text,ENT_COMPAT,"windows-1251");
			$text=stripslashes(str_replace("|"," ",$text));

			$status=str_replace("\r\n","<br>",$text);
			$lines=file("datan/userstat.dat");
			$dt=explode("|", $lines[$usernum]);

			$txtdat="$dt[0]|$dt[1]|$dt[2]|$dt[3]|$status|$dt[5]|$dt[6]|$dt[7]|||";

			$fp=fopen("datan/userstat.dat","a+");
			flock($fp,LOCK_EX);
			ftruncate($fp,0); // УДАЛЯЕМ СОДЕРЖИМОЕ ФАЙЛА

			for ($i=0;$i<=(sizeof($lines)-1);$i++)
			{
				if ($i==$usernum) fputs($fp,"$txtdat\r\n"); else fputs($fp,$lines[$i]);
			}
			fflush($fp);
			flock($fp,LOCK_UN);
			fclose($fp);

			//Header("Location: admin.php?event=userwho&page=$page");

			print "<meta charset='windows-1251'><script>function reload(){location=\"javascript:history.back(1)\"};setTimeout('reload()',300);</script><br><br><br><br><br><br><center><font size=2 face=tahoma><b>Данные успешно изменены!<br><br><a href='javascript:history.back(1)' style='text-decoration:none;'>Продолжить</a></b></font></center>";

			exit;
		}


		//////////////// Блок изменения СТАТУСА участника
		if (isset($_GET['newstatus']))
		{
			if ($_GET['newstatus'] !="")
			{
				$newstatus=$_GET['newstatus']-1;
				$status=$_POST['status'];

				if (isset($_GET['page'])) $page=$_GET['page']; else $page=1;
				if (strlen($status)<3) exit("<br><br><br><center><font face=tahoma size=2><b>Статус участника должен быть больше 3-х символов</b><br><br><a href='javascript:history.back(1)' style='text-decoration:none;'>Назад</a></b></font></center>");
				$status=htmlspecialchars($status,ENT_COMPAT,"windows-1251");
				$status=stripslashes($status);
				$status=str_replace("|"," ",$status);
				$status=str_replace("\r\n"," ",$status);
				$lines=file("datan/usersdat.php");
				$i=count($lines);
				$dt=explode("|", $lines[$newstatus]);

				$txtdat="$dt[0]|$dt[1]|$dt[2]|$dt[3]|$dt[4]|$dt[5]|$dt[6]|$dt[7]|$dt[8]|$dt[9]|$dt[10]|$dt[11]|$dt[12]|$status|$dt[14]|";

				$fp=fopen("datan/usersdat.php","a+");
				flock ($fp,LOCK_EX); 
				ftruncate ($fp,0); // УДАЛЯЕМ СОДЕРЖИМОЕ ФАЙЛА

				for ($i=0;$i<=(sizeof($lines)-1);$i++) {
					if ($i==$newstatus) {fputs($fp,"$txtdat\r\n");} else {fputs($fp,$lines[$i]);}
				}
				fflush($fp);
				flock($fp,LOCK_UN);
				fclose($fp);

				//Header("Location: admin.php?event=userwho&page=$page");

				print "<meta charset='windows-1251'><script>function reload(){location=\"javascript:history.back(1)\"};setTimeout('reload()',300);</script><br><br><br><br><br><br><center><font size=2 face=tahoma><b>Данные успешно изменены!<br><br><a href='javascript:history.back(1)' style='text-decoration:none;'>Продолжить</a></b></font></center>";

				exit;
			}
		}

		//////////////// Блок изменения РЕЙТИНГА участника
		if (isset($_GET['newreiting']))
		{
			if ($_GET['newreiting'] !="")
			{
				$newreiting=$_GET['newreiting']-1;
				$reiting=$_POST['reiting'];

				if (isset($_GET['page'])) $page=$_GET['page']; else $page=1;
				$reiting=htmlspecialchars($reiting,ENT_COMPAT,"windows-1251");
				$reiting=stripslashes($reiting);
				$reiting=str_replace("|"," ",$reiting);
				$reiting=str_replace("\r\n"," ",$reiting);
				$lines=file("datan/usersdat.php");
				$i=count($lines);
				$dt=explode("|", $lines[$newreiting]);

				$txtdat="$dt[0]|$dt[1]|$reiting|$dt[3]|$dt[4]|$dt[5]|$dt[6]|$dt[7]|$dt[8]|$dt[9]|$dt[10]|$dt[11]|$dt[12]|$dt[13]|$dt[14]|";

				$fp=fopen("datan/usersdat.php","a+");
				flock ($fp,LOCK_EX); 
				ftruncate ($fp,0); // УДАЛЯЕМ СОДЕРЖИМОЕ ФАЙЛА

				for ($i=0;$i<=(sizeof($lines)-1);$i++) {
					if ($i==$newreiting) fputs($fp,"$txtdat\r\n"); else fputs($fp,$lines[$i]);
				}
				fflush($fp);
				flock($fp,LOCK_UN);
				fclose($fp);

				//Header("Location: admin.php?event=userwho&page=$page");

				print "<meta charset='windows-1251'><script>function reload(){location=\"javascript:history.back(1)\"};setTimeout('reload()',300);</script><br><br><br><br><br><br><center><font size=2 face=tahoma><b>Данные успешно изменены!<br><br><a href='javascript:history.back(1)' style='text-decoration:none;'>Продолжить</a></b></font></center>";

				exit;
			}
		}


		//////////////// Блок удаления УЧАСТНИКА ФОРУМА
		if (isset($_GET['usersdelete']))
		{
			$usersdelete=$_GET['usersdelete'];
			$first=$_POST['first'];
			$last=$_POST['last'];
			$page=$_GET['page'];
			$delnum=null;
			$i=0;

			// Сравнимаем кол-во строк в файле ЮЗЕРОВ и их СТАТИСТИКУ
			if (count(file("datan/usersdat.php")) != count(file("datan/userstat.dat"))) exit("<br><br><br><center><b><font size=2 face=tahoma>Статистика участников повреждена!</b><br><br>Количество строк в файле <b>usersdat.php</b> не совпадает с количеством строк в <b>userstat.dat</b><br><br><b><a href='javascript:history.back(1)' style='text-decoration:none;'>Вернуться</a></b>");

			do {
				$dd="del$first";
				if (isset($_POST["$dd"]))
				{
					$delnum[$i]=$first;
					$i++;
				}
				$first++;
			} while ($first<=$last);
			$itogodel=count($delnum);
			$newi=0;

			if ($delnum=="") exit("<meta charset='windows-1251'><script>function reload(){location=\"javascript:history.back(1)\"};setTimeout('reload()',3000);</script><br><br><br><br><br><br><center><font size=2 face=tahoma><b>Вы не выбрали пользователя, которого хотите удалить!<br><br><a href='javascript:history.back(1)' style='text-decoration:none;'>Вернуться</a></b></font></center>");

			$file=file("datan/usersdat.php");
			$itogo=sizeof($file);
			$lines=null;
			$delyes="0";
			for ($i=0; $i<$itogo; $i++)
			{
				for ($p=0; $p<$itogodel; $p++)
				{
					if ($i==$delnum[$p]) {$delyes=1;}
				}
				// если нет метки на удаление записи - формируем новую строку массива, иначе - нет
				if ($delyes!=1) {$lines[$newi]=$file[$i]; $newi++;} else {$delyes="0";}
			}
			// пишем новый массив в файл
			$newitogo=count($lines); 
			$fp=fopen("datan/usersdat.php","w");
			flock($fp,LOCK_EX);

			// если всех юзеров удаляем, тогда ничего туда ВПУТИТЬ
			if (isset($lines[0]))
			{
				for ($i=0; $i<$newitogo; $i++) {fputs($fp,$lines[$i]);}
			} else {
				fputs($fp,"");
			}
			flock($fp,LOCK_UN);
			fclose($fp);

			// Удаляем инфу о юзере из блока статистики
			$file=file("datan/userstat.dat");
			$itogo=sizeof($file);
			$lines=null;
			$delyes="0";
			$newi=0;
			for ($i=0; $i<$itogo; $i++)
			{
				for ($p=0; $p<$itogodel; $p++)
				{
					if ($i==$delnum[$p]) {$delyes=1;}
				}
				// если нет метки на удаление записи - формируем новую строку массива, иначе - нет
				if ($delyes!=1) {$lines[$newi]=$file[$i]; $newi++;} else {$delyes="0";}
			}
			// пишем новый массив в файл
			$newitogo=count($lines);
			$fp=fopen("datan/userstat.dat","w");
			flock($fp,LOCK_EX);

			// если статистику всех юзеров удаляем, тогда ничего туда ВПУТИТЬ
			if (isset($lines[0]))
			{
				for ($i=0; $i<$newitogo; $i++) {fputs($fp,$lines[$i]);}
			} else {
				fputs($fp,"");
			}
			flock($fp,LOCK_UN);
			fclose($fp);

			print "<meta charset='windows-1251'><script>function reload(){location=\"admin.php?event=userwho&page=$page\"};setTimeout('reload()',300);</script><br><br><br><br><br><br><center><font size=2 face=tahoma><b>Данные успешно изменены!<br><br><a href='javascript:history.back(1)' style='text-decoration:none;'>Продолжить</a></b></font></center>";

			exit;
		} 









		if (isset($_GET['event']))
		{


			//////////////// УЧАСТНИКИ форума
			if ($_GET['event']=="userwho")
			{
				$t1="row1";
				$t2="row2";
				$error=0;
				$userlines=file("datan/usersdat.php");
				$ui=count($userlines)-1;
				$first=0;
				$last=$ui+1;
				$statlines=file("datan/userstat.dat");
				$si=count($statlines)-1;
				$bada="<center><font color=red><B>В файле статистики имеются ошибки!</B></font></center><br>";

				if ($si!=$ui) print "$bada";

				if (isset($_GET['page'])) $page=$_GET['page']; else $page="1";
				if (!ctype_digit($page)) $page=1; // защита
				if ($page=="0") $page="1"; else $page=abs($page);

				$maxpage=ceil(($ui+1)/$uq);

				if ($page>$maxpage) $page=$maxpage;

				$i=1+$uq*($page-1);

				if ($i>$ui) $i=$ui-$uq; $lm=$i+$uq;
				if ($lm>$ui) $lm=$ui+1;

echo'
<table width=100% valign=top cellpadding=0 cellspacing=1 border=0>
<tr><td>
	<table valign=top width=100% cellpadding=0 cellspacing=0 border=0>
<tr>
<th width=20 nowrap=nowrap>№</th>
<th>Имя</th>
<th width=20>Пол</th>
<th width=80>Регистрация</th>
<th>Емайл</th>
<th width=30>Тем</th>
<th width=70>Сообщ</th>
<th width=100>Репутация</th>
<th width=80>Нарушения</th>
<th width=380>Статус и награды</th>
<th width=80>Звёзды</th></tr>';

				$delblok="<FORM action='admin.php?usersdelete=$last&page=$page' method=POST name=delform><td class=$t1><table align=center cellpadding=0 cellspacing=0 border=0><th>X</th>";

				do {
					$tdt=explode("|",$userlines[$i]);
					$i++;
					$npp=$i-1;

					if (isset($statlines[$i-1]))
					{
						$sdt=explode("|",$statlines[$i-1]);
					} else {
						$sdt[0]="";
						$sdt[1]="-";
						$sdt[2]="-";
						$sdt[3]="-";
						$sdt[4]="-";
					}

					// Проверяем, если файл статистики повреждён - пишем сообщение о необходимости восстановить его
					if ($sdt[0]!=$tdt[0])
					{
						$error++;
						$sdt[1]="-";
						$sdt[2]="-";
						$sdt[3]="-";
						$sdt[4]="-";
					}
					if ($tdt[6]=="мужчина") $tdt[6]="<font color=green><b>М</b></font>"; else $tdt[6]="<font color=red><b>Ж</b></font>";

					if (strlen($tdt[13])<2) $tdt[13]=$users;

					$delblok.="<tr><td><input  style='width:18px;height:18px' type=checkbox name='del$npp' value=''";

					if (isset($_GET['chekall'])) {$delblok.='CHECKED';} $delblok.="></td></tr>";

					print"<tr><td class=$t1 align=center><small>$npp</small></td><td class=$t1><b><a href=\"index.php?event=profile&pname=$tdt[0]\">$tdt[0]</a></b></td>";

print"
<td class=$t1><center>$tdt[6]</center></td>
<td class=$t1><center>$tdt[4]</center></td>
<td class=$t1><small><center><a href=\"mailto:$tdt[3]\">$tdt[3]</a></center></small></td>
<td class=$t1><center>$sdt[1]</center></td>
<td class=$t1><center>$sdt[2]</center></td>
<td class=$t1>

<form action='admin.php?newrepa&page=$page' method=post><center><input type=text name=repa value='$sdt[3]' size=5 maxlength=5 style='width:50px;height:22px'><input type=hidden name=usernum value='$i'> <input type=submit name=submit value='OK' class=button style='width:30px'></center></td></form>

<td class=$t1>

<form action='admin.php?userstatus&page=$page' method=post><center><input type=text name=status value='$sdt[4]' size=4 maxlength=3 style='width:40px;height:22px'><input type=hidden name=usernum value='$i'> <input type=submit name=submit value='OK' class=button style='width:30px'></center></td></form>

<td class=$t1 width=280>
<form action='admin.php?newstatus=$i&page=$page' method=post><center><input type=text class=post name=status value='$tdt[13]' size=20 maxlength=300 style='width:350px;height:22px'> <input type=submit name=submit value='OK' class=button style='width:30px'></center></td></form>

<td class=$t1>

<form action='admin.php?newreiting=$i&page=$page' method=post><center><input type=text class=post name=reiting value='$tdt[2]' size=2 maxlength=2 style='width:28px;height:22px'> <input type=submit name=submit value='OK' class=button style='width:30px'></center></td></form></tr>";

					$t3=$t2; $t2=$t1; $t1=$t3;
				} while($i<$lm);

print"</table></td><td>$delblok</table></tr></table>

<br>

<div align=right>
	<input type=hidden name=first value='$first'>
	<input type=hidden name=last value='$last'>
	<input type=submit class=button value='Удалить выбранных' style='width:150'></FORM></div>";

				// выводим СПИСОК СТРАНИЦ
				if ($page>$maxpage) {$page=$maxpage;}

				echo'<table width=100% border=0><TR><TD width="45%">Страницы:&nbsp; ';

				if ($page>=4 and $maxpage>5) print "<a href=admin.php?event=userwho&page=1>1</a> ... ";

				$f1=$page+2;
				$f2=$page-2;

				if ($page<=2) {$f1=5; $f2=1;}
				if ($page>=$maxpage-1) {$f1=$maxpage; $f2=$page-3;}
				if ($maxpage<=5) {$f1=$maxpage; $f2=1;}

				for($i=$f2; $i<=$f1; $i++)
				{
					if ($page==$i)
					{
						print "<B>$i</B> &nbsp;";
					} else {
						print "<a href=admin.php?event=userwho&page=$i>$i</a> &nbsp;";
					}
				}
				if ($page<=$maxpage-3 and $maxpage>5) print "... <a href=admin.php?event=userwho&page=$maxpage>$maxpage</a>";

				print "</td><td width='55%'>Зарегистрировано: <B>$ui</B></td></tr></TABLE><br>";

				if ($error>0) {print"<br><br> $bada <br><br>";}

				echo'<br><b>Статус и награды</b>. Например, если необходимо добавить награду пользователю со статусом <mark>рядовой</mark>, то добавляем ее через символ @, то есть так <mark>рядовой@Первая награда@Вторая награда</mark> и т.д.<br><br><br><center>[<a href="index.php" style="text-decoration:none;">Вернуться на форум</a>]</center>';
			}



			if ($_GET['event']=="config")
			{
				if ($readonly==TRUE) {$ro1="checked"; $ro2="";} else {$ro2="checked"; $ro1="";}
				if ($notopic==TRUE) {$notopic1="checked"; $notopic2="";} else {$notopic2="checked"; $notopic1="";}
				if ($editmsg==TRUE) {$edsmg1="checked"; $edsmg2="";} else {$edsmg2="checked"; $edsmg1="";}
				if ($antiham==TRUE) {$ah1="checked"; $ah2="";} else {$ah2="checked"; $ah1="";}
				if ($antimat==TRUE) {$am1="checked"; $am2="";} else {$am2="checked"; $am1="";}
				if ($antimatt==TRUE) {$aam1="checked"; $aam2="";} else {$aam2="checked"; $aam1="";}
				if ($ipinfodb==TRUE) {$ipi1="checked"; $ipi2="";} else {$ipi2="checked"; $ipi1="";}
				if ($gravatar==TRUE) {$grv1="checked"; $grv2="";} else {$grv2="checked"; $grv1="";}
				if ($avround==TRUE) {$avr1="checked"; $avr2="";} else {$avr2="checked"; $avr1="";}
				if ($captchamin==TRUE) {$cap1="checked"; $cap2="";} else {$cap2="checked"; $cap1="";}
				if ($captcha==TRUE) {$scap1="checked"; $scap2="";} else {$scap2="checked"; $scap1="";}
				if ($liteurl==TRUE) {$lu1="checked"; $lu2="";} else {$lu2="checked"; $lu1="";}

				if ($uploader==TRUE) {$up1="checked"; $up2="";} else {$up2="checked"; $up1="";}

				if ($canupfile==TRUE) {$cs1="checked"; $cs2="";} else {$cs2="checked"; $cs1="";}
				if ($lastmess==TRUE) {$lm1="checked"; $lm2="";} else {$lm2="checked"; $lm1="";}
				if ($rankline==TRUE) {$rankline1="checked"; $rankline2="";} else {$rankline2="checked"; $rankline1="";}
				if ($nagrada==TRUE) {$nagrada1="checked"; $nagrada2="";} else {$nagrada2="checked"; $nagrada1="";}
				if ($welcome==TRUE) {$wel1="checked"; $wel2="";} else {$wel2="checked"; $wel1="";}
				$stopwrd1='';
				$stopwrd2='';
				$stopwrd3='';
				$stopwrd4='';
				if ($stopwrd=="4") {$stopwrd4="checked";}
				if ($stopwrd=="3") {$stopwrd3="checked";}
				if ($stopwrd=="2") {$stopwrd2="checked";}
				if ($stopwrd=="1") {$stopwrd1="checked";}

print "
<style>table,textarea{border:1px solid #555;padding:0 2 0 2;margin:1;border-collapse:collapse;}small {color: #666}
td.row2,td.row1 {border:1px solid #222;padding: 3 3px;}
input.radio {width:15px; height:15px; text-align:bottom; padding:0 0 0 0;}
</style><center>[<a href='index.php'>вернуться назад</a>]<br><br>
<form action=\"admin.php?saveconfig\" method=POST>
<table width=600px cellpadding=2 cellspacing=1>
<tr><td align=center width=200px height='22px' bgcolor=green><font color='#ddd'><b>Параметр</b></font></td><td align=center width='400px' bgcolor=green><font color='#ddd'><b>Значение</b></font></td></tr>
<tr><td class=row1 align=right><b>Название форума</b><br><small>выводится в title и заголовке</small></td><td class=row2><input type=text value=\"$fname\" name=fname maxlength=50 size=50 style='width:399px;'></td></tr>
<tr><td class=row1 align=right><b>Описание форума</b><br><small>выводится в шапке форума<br>разрешены теги, запрещены кавычки (\" \")</small></td><td class=row2><textarea cols=50 rows=6 size=400 name=fdesription style='width:399px;height:60px'>$fdesription</textarea></td></tr>
<tr><td class=row1 align=right><b>Логин и Пароль админа</b><br><small>для смены пароля удалите *** и введите пароль</small></td><td class=row2><input type=text value=\"$adminname\" maxlength=20 name=\"adminname\" size=14 style='width:120px'> Пароль: <input name=\"adminpass\" type=hidden value=\"$adminpass\"><input type=text value='*****' name=\"newpassword\" size=14 style='width:120px'></td></tr>
<tr><td class=row1 align=right><b>Заблокировать форум</b><br><small>откл. возможность создавать темы и отвечать в них</small></td><td class=row2><input class=radio type=radio name=readonly value=\"0\" $ro2 /> нет &nbsp;&nbsp; <input class=radio type=radio name=readonly value=\"1\" $ro1 /> да</td></tr>
<tr><td class=row1 align=right><b>Запретить создавать темы</b><br><small>отключает возможность создавать темы</small></td><td class=row2><input class=radio type=radio name=notopic value=\"0\" $notopic2 /> нет &nbsp;&nbsp; <input class=radio type=radio name=notopic value=\"1\" $notopic1 /> да</td></tr>
<tr><td class=row1 align=right><b>Использовать граватар</b><br><small>www.gravatar.com размер аватара: 70 = 70 х 70 px</small></td><td class=row2><input class=radio type=radio name=gravatar value=\"0\" $grv2 /> нет &nbsp;&nbsp; <input class=radio type=radio name=gravatar value=\"1\" $grv1 /> да &nbsp; &nbsp; / &nbsp; &nbsp; размер: <input type=text value='$gravatarsize' name=\"gravatarsize\" maxlength=3 size=4 style='width:28px'></td></tr>
<tr><td class=row1 align=right><b>Круглый граватар</b><br><small>использовать круглый аватар</small></td><td class=row2><input class=radio type=radio name=avround value=\"0\" $avr2 /> нет &nbsp;&nbsp; <input class=radio type=radio name=\"avround\" value=\"1\" $avr1 /> да</td></tr>
<tr><td class=row1 align=right><b>Параметры аватара</b></td><td class=row2><input type=text value='$avatar_width' maxlength=3 name='avatar_width' size=4 style='width:28px'> х <input type=text value='$avatar_height' maxlength=3 name='avatar_height' size=4 style='width:28px'> px &nbsp; <input type=text value='$max_file_size' maxlength=6 style='width:55px' name='max_file_size' size=7> байт</td></tr>
<tr><td class=row1 align=right><b>Участников на страницу<b></td><td class=row2><input type=text value='$uq' maxlength=2 name=uq size=4 style='width:28px'></td></tr>
<tr><td class=row1 align=right><b>Флаг страны пользователя</b><br><small>если флаг и город, то зарегистрируйся<br>http://ipinfodb.com/register.php<br>и впиши ключ</small></td><td class=row2><input class=radio type=radio name=\"ipinfodb\" value=\"0\" $ipi2 /> флаг &nbsp;&nbsp; <input class=radio type=radio name=\"ipinfodb\" value=\"1\" $ipi1 /> флаг и город. Ключ:<br><input name=\"key\" type='text' value='$key' size=14 maxlength=64 style='margin-left:0px;width:399px'></td></tr>
<tr><td class=row1 align=right><b>Часовая поправка</b></td><td class=row2><input type=text value='$timezone' maxlength=3 name=timezone size=7 style='width:28px'> час. (значение от -12 до 12)</td></tr>
<tr><td class=row1 align=right><b>Делать ссылки кликабельными</b></td><td class=row2><input class=radio type=radio name=liteurl value=\"0\" $lu2 /> нет &nbsp;&nbsp; <input class=radio type=radio name=liteurl value=\"1\" $lu1 /> да </td></tr>
<tr><td class=row1 align=right><b>Макс. количество символов</b></td><td class=row2><input type=text value='$maxname' name=maxname maxlength=2 size=4 style='width:28px'> в имени, <input type=text value='$maxmail' name=maxmail maxlength=2 size=4 style='width:28px'> в email, <input type=text value='$maxtopic' maxlength=3 name=maxtopic size=4 style='width:28px'> в теме, <input type=text value='$maxmsg' maxlength=4 name=maxmsg size=4 style='width:50px'> в сообщении</td></tr>
<tr><td class=row1 align=right><b>Сохранять последние сообщения</b><br><small>например, их можно будет выводить на сайте</small></td><td class=row2><input class=radio type=radio name=lastmess value=\"0\" $lm2 /> нет &nbsp;&nbsp; <input class=radio type=radio name=lastmess value=\"1\" $lm1 /> да, сохранять <input type=text value='$lastlines' maxlength=3 name=lastlines size=5 style='width:28px'> сообщений</td></tr>
<tr><td class=row1 align=right><b>Версия форума</b></td><td class=row2><input type=text value=\"$ver\" name=ver maxlength=50 size=50 style='width:399px'></td></tr>
<tr><td class=row1 align=right><b>Скин форума</b></td><td class=row2><br>&nbsp;<select class=input name=fskin>";

				$path = '.'; // Путь до папки '.' - текущая папка
				if ($handle=opendir($path)) {
					while(($file=readdir($handle)) !== false)
					if (is_dir($file)) {
						$stroka=stristr($file, "images");
						if (strlen($stroka)>"6")
						{
							$tskin=str_replace("images", "Скин ", $file);
							if ($fskin==$file) {$marker="selected";} else {$marker="";}
							print"<option $marker value=\"$file\">$tskin</option>";
						}
					}
					closedir($handle);
				} else {echo'Ошибка!';}

print "</select><br><br></td></tr>
<tr><td align=center colspan=2 bgcolor=green><font color='#ddd'><b>Названия иконок тем</b></font></td></tr>
<tr><td class=row1 align=right><img src='datan/1.png'></td><td class=row2><input type=text value=\"$topic1\" style='width:399px' name=topic1 size=10></td></tr>
<tr><td class=row1 align=right><img src='datan/2.png'></td><td class=row2><input type=text value=\"$topic2\" style='width:399px' name=topic2 size=10></td></tr>
<tr><td class=row1 align=right><img src='datan/3.png'></td><td class=row2><input type=text value=\"$topic3\" style='width:399px' name=topic3 size=10></td></tr>
<tr><td class=row1 align=right><img src='datan/4.png'></td><td class=row2><input type=text value=\"$topic4\" style='width:399px' name=topic4 size=10></td></tr>

<tr><td align=center colspan=2 bgcolor=green><font color='#ddd'><b>Редактирование сообщений пользователями</b></font></td></tr>
<tr><td class=row1 align=right><b>Разрешить редакт. сообщения</b></td><td class=row2><input class=radio type=radio name=editmsg value=\"1\" $edsmg1/> да &nbsp;&nbsp; <input class=radio type=radio name=editmsg value=\"0\" $edsmg2/> нет </td></tr>
<tr><td class=row1 align=right><b>Редактировать нельзя по истечению</b></td><td class=row2><input type=text value='$timeoutedit' maxlength=5 name=timeoutedit size=6 style='width:28px'> часов</td></tr>
<tr><td class=row1 align=right><b>Подпись о редактировании</b></td><td class=row2><input type=text value='$redsig' name='redsig' size=50 style='width:399px'></td></tr>

<tr><td align=center colspan=2 bgcolor=green><font color='#ddd'><b>Репутация / Статуы / Награды</b></font></td></tr>
<tr><td class=row1 align=right><b>Сколько давать при добавлении</b></td><td class=row2>темы: <input type=text value=\"$repaaddtem\" style='width:28px' name=repaaddtem maxlength=2 size=3> сообщения: <input type=text value=\"$repaaddmsg\" style='width:28px' name=repaaddmsg maxlength=2 size=3></td></tr>
<tr><td class=row1 align=right><b>Статус пользователя при регистрации</b></td><td class=row2><input type=text value=\"$userstatus\" style='width:399px' name=userstatus maxlength=30 size=10></td></tr>
<tr><td class=row1 align=right><b>Показывать картинки-статусы</b></td><td class=row2><input class=radio type=radio name=rankline value=\"1\" $rankline1/> да &nbsp; <input class=radio type=radio name=rankline value=\"0\" $rankline2/> нет</td></tr>

<tr><td class=row1 align=right><b>Показывать картинки-награды</b></td><td class=row2><input class=radio type=radio name=nagrada value=\"1\" $nagrada1/> да &nbsp; <input class=radio type=radio name=nagrada value=\"0\" $nagrada2/> нет</td></tr>
<tr><td align=center colspan=2 class=row1><b>Настройки статусов (званий)</b></td></tr>
<tr><td class=row1 align=right><b>Картинки-статусы</b></td><td class=row2><br>&nbsp;<select class=input name=imgstatus>";

				$d=scandir("rank");
				foreach($d as $f)
				{
					if ($f=="."||$f=="..") continue;
					{
						//echo '<a href="'.$dir.'/'.$f.'">'.$f.'</a><br>';

						if ($imgstatus==$f) $mark="selected"; else $mark="";

						print"<option $mark value=\"$f\">$f</option>";
					}
				}

print "</select>&nbsp; сохраните настройки - картинки поменяются<br><br></td></tr>
<tr><td align=right class=row1>Репутация <input type=text value='$stu0' maxlength=5 name='stu0' size=7 style='width:50px'>&nbsp;</td><td class=row2>статус <input type=text value='$stn0' maxlength=32 name='stn0' size=40 style='width:200px'>&nbsp;<img src='rank/$imgstatus/00.png' border=0 align=absmiddle></td></tr>
<tr><td align=right class=row1>Репутация <input type=text value='$stu1' maxlength=5 name='stu1' size=7 style='width:50px'>&nbsp;</td><td class=row2>статус <input type=text value='$stn1' maxlength=32 name='stn1' size=40 style='width:200px'>&nbsp;<img src='rank/$imgstatus/01.png' border=0 align=absmiddle></td></tr>
<tr><td align=right class=row1>Репутация <input type=text value='$stu2' maxlength=5 name='stu2' size=7 style='width:50px'>&nbsp;</td><td class=row2>статус <input type=text value='$stn2' maxlength=32 name='stn2' size=40 style='width:200px'>&nbsp;<img src='rank/$imgstatus/02.png' border=0 align=absmiddle></td></tr>
<tr><td align=right class=row1>Репутация <input type=text value='$stu3' maxlength=5 name='stu3' size=7 style='width:50px'>&nbsp;</td><td class=row2>статус <input type=text value='$stn3' maxlength=32 name='stn3' size=40 style='width:200px'>&nbsp;<img src='rank/$imgstatus/03.png' border=0 align=absmiddle></td></tr>
<tr><td align=right class=row1>Репутация <input type=text value='$stu4' maxlength=5 name='stu4' size=7 style='width:50px'>&nbsp;</td><td class=row2>статус <input type=text value='$stn4' maxlength=32 name='stn4' size=40 style='width:200px'>&nbsp;<img src='rank/$imgstatus/04.png' border=0 align=absmiddle></td></tr>
<tr><td align=right class=row1>Репутация <input type=text value='$stu5' maxlength=5 name='stu5' size=7 style='width:50px'>&nbsp;</td><td class=row2>статус <input type=text value='$stn5' maxlength=32 name='stn5' size=40 style='width:200px'>&nbsp;<img src='rank/$imgstatus/05.png' border=0 align=absmiddle></td></tr>
<tr><td align=right class=row1>Репутация <input type=text value='$stu6' maxlength=5 name='stu6' size=7 style='width:50px'>&nbsp;</td><td class=row2>статус <input type=text value='$stn6' maxlength=32 name='stn6' size=40 style='width:200px'>&nbsp;<img src='rank/$imgstatus/06.png' border=0 align=absmiddle></td></tr>
<tr><td align=right class=row1>Репутация <input type=text value='$stu7' maxlength=5 name='stu7' size=7 style='width:50px'>&nbsp;</td><td class=row2>статус <input type=text value='$stn7' maxlength=32 name='stn7' size=40 style='width:200px'>&nbsp;<img src='rank/$imgstatus/07.png' border=0 align=absmiddle></td></tr>
<tr><td align=right class=row1>Репутация <input type=text value='$stu8' maxlength=5 name='stu8' size=7 style='width:50px'>&nbsp;</td><td class=row2>статус <input type=text value='$stn8' maxlength=32 name='stn8' size=40 style='width:200px'>&nbsp;<img src='rank/$imgstatus/08.png' border=0 align=absmiddle></td></tr>
<tr><td align=right class=row1>Репутация <input type=text value='$stu9' maxlength=5 name='stu9' size=7 style='width:50px'>&nbsp;</td><td class=row2>статус <input type=text value='$stn9' maxlength=32 name='stn9' size=40 style='width:200px'>&nbsp;<img src='rank/$imgstatus/09.png' border=0 align=absmiddle></td></tr>
<tr><td align=right class=row1>Модератор&nbsp;</td><td class=row2>&nbsp;<img src='rank/$imgstatus/moder.png' border=0 align=absmiddle></td></tr>
<tr><td align=right class=row1>Администратор&nbsp;</td><td class=row2>&nbsp;<img src='rank/$imgstatus/admin.png' border=0 align=absmiddle></td></tr>

<tr><td align=center colspan=2 bgcolor=green><font color='#ddd'><b>Загрузка файлов</b></font></td></tr>
<tr><td align=right class=row1><b>Разрешить загрузку файлов Uploader</b></td><td class=row2><input class=radio type=radio name=uploader value=\"1\" $up1/> да &nbsp; <input class=radio type=radio name=uploader value=\"0\" $up2/> нет &nbsp; &nbsp; / &nbsp; &nbsp; Форма для загрузки <input type=text value='$max_files' maxlength=3 name='max_files' size=3 style='width:30px'> файлов</td></tr>
<tr><td align=right class=row1><b>Разрешить прикреплять файлы</b></td><td class=row2><input class=radio type=radio name=canupfile value=\"1\" $cs1/> да &nbsp; <input class=radio type=radio name=canupfile value=\"0\" $cs2/> нет</td></tr>
<tr><td align=right class=row1><b>Максимальный размер файла</b></td><td class=row2><input type=text value='$max_upfile_size' maxlength=7 name='max_upfile_size' size=7 style='width:100px'> байт</td></tr>
<tr><td align=right class=row1><b>Папка куда загружаются файлы</b></td><td class=row2><input type=text value='$filedir' class=post maxlength=16 name='filedir' size=7 style='width:100px'> по умолчанию <B>./load</B></td></tr>
<tr><td align=right class=row1><b>Путь папки загружаемых файлов</b></td><td class=row2><input type=text value='$storagepath' maxlength=70 name='storagepath' size=50 style='width:399px'></td></tr>

<tr><td align=right class=row1><b>Размер мини картинки</b><br><small>Если прикрепляемая картинка больше этого размера,<br>то ее мини изображение получится с таким размером</small></td><td class=row2><input type=text value='$smwidth' maxlength=3 name='smwidth' size=7 style='width:50px'> х <input type=text value='$smheight' maxlength=3 name='smheight' size=7 style='width:50px'> пикселей</td></tr>
<tr><td align=right class=row1><b>Макс размер изображения</b><br><small>Если прикрепить картинку 2600х1200px, то после<br> закачки она сожмется до указанного вами размера</small></td><td class=row2><input type=text value='$maxwidth' maxlength=4 name='maxwidth' size=7 style='width:50px'> х <input type=text value='$maxheight' maxlength=4 name='maxheight' size=7 style='width:50px' title='Прикрепленная картинка при просмотре откроется с таким размером. Т.е. если прикрепить большую картинку, например 1600х1200, то при просмотре она сожмется до указанного вами размера'> пикселей</td></tr>

<tr><td align=center colspan=2 bgcolor=green><font color='#ddd'><b>Антихам - Бан - Антимат</b></font></td></tr>
<tr><td class=row1 align=right><b>Включить Антихам</b></td><td class=row2><input class=radio type=radio name=antiham value=\"1\" $ah1 /> да &nbsp; <input class=radio type=radio name=antiham value=\"0\" $ah2 /> нет</td></tr>
<tr><td class=row1 align=right><b>Антихам</b><br><small>если в сообщении будут стоп-слова</small></td><td class=row2><input class=radio type=radio name=stopwrd value=\"1\" $stopwrd1 /> ничего не делать<br><input class=radio type=radio name=stopwrd value=\"2\" $stopwrd2 /> не пропускать сообщение<br><input class=radio type=radio name=stopwrd value=\"3\" $stopwrd3 /> заменять на слово: <input name=cons type='text' value=\"$cons\" size=14 maxlength=60 style='width:150px'><br><input class=radio type=radio name=stopwrd value=\"4\" $stopwrd4 /> добавить IP в бан</td></tr>
<tr><td class=row1 align=right><b>Антихам: стоп-слова</b><br><small>по одному слову через пробел</small></td><td class=row2><textarea cols=50 rows=6 size=500 name=stopwords style='width:399px;height:60px'>".file_get_contents("datan/stopwords.dat")."</textarea></td></tr>
<tr><td class=row1 align=right><b>Бан-лист</b><br><small>по одному IP через пробел</small></td><td class=row2><textarea cols=50 rows=6 size=500 name=banip style='width:399px;height:60px'>".file_get_contents("datan/badip.dat")."</textarea></td></tr>

<tr><td class=row1 align=right><b>Антимат 1</b></td><td class=row2 rowspan=2>
<table width=100% class=row1><tr><td class=row2><input class=radio type=radio name=antimat value=\"0\" $am2 /> нет &nbsp; <input class=radio type=radio name=antimat value=\"1\" $am1 /> да</td>
<td rowspan=2 class=row2>заменять маты на <input name=cons type='text' value=\"$cons\" size=14 maxlength=60 style='margin-left:0px;width:150px'></td>
</tr><tr><td class=row2><input class=radio type=radio name=antimatt value=\"0\" $aam2 /> нет &nbsp; <input class=radio type=radio name=antimatt value=\"1\" $aam1 /> да</td>
</tr></table></td></tr><tr><td class=row1 align=right><b>Антимат 2</b></td></tr>

<tr><td align=center colspan=2 bgcolor=green><font color='#ddd'><b>Капча (антиспам)</b></font></td></tr>
<tr><td class=row1 align=right><b>Капча форума</b></td><td class=row2><input class=radio type=radio name=captchamin value=\"0\" $cap2 /> сложная &nbsp;&nbsp; <input class=radio type=radio name=captchamin value=\"1\" $cap1 /> простая</td></tr>
<tr><td class=row1 align=right><b>Настройка сложной капчи</b></td><td class=row2><input class=radio type=radio name=captcha value=\"0\" $scap2 /> буквы и цифры &nbsp; <input class=radio type=radio name=captcha value=\"1\" $scap1 /> только цифры<br><input type=text value='$width' name=width maxlength=3 size=4 style='width:35px'> ширина, &nbsp; <input type=text value='$height' name=height maxlength=3 size=4 style='width:35px'> высота, &nbsp; 
<input type=text value='$font_size' name=font_size maxlength=3 size=4 style='width:35px'> размер шрифта<br><input type=text value='$fon_let_amount' name=fon_let_amount maxlength=3 size=4 style='width:35px'> симв. на капче, &nbsp; <input type=text value='$let_amount' name=let_amount maxlength=3 size=4 style='width:35px'> надо вводить<br><input type=text value='$path_fonts' maxlength=16 name='path_fonts' size=7 style='width:70px'> папка со шрифтами капчи</td></tr>

<tr><td align=center colspan=2 bgcolor=green><font color='#ddd'><b>Всплывающее окно в правом верхнем углу</b></font></td></tr>
<tr><td class=row1 align=right><b>Показывать всплывающее окно</b></td><td class=row2><input class=radio type=radio name=welcome value=\"1\" $wel1 /> да &nbsp;&nbsp; <input class=radio type=radio name=welcome value=\"0\" $wel2 /> нет</td></tr>
<tr><td class=row1 align=right><b>Заголовок окна</b><br><small>можно использовать теги, двойные кавычки нельзя</small></td><td class=row2><input type=text value=\"$welcometitle\" name=welcometitle maxlength=100 size=30 style='width:399px'></td></tr>
<tr><td class=row1 align=right><b>Текст окна</b><br><small>можно использовать теги, двойные кавычки нельзя</small></td><td class=row2><textarea cols=50 rows=6 size=500 name=welcometext style='width:399px;height:60px'>$welcometext</textarea></td></tr>

<tr><td align=center colspan=2 bgcolor=green><font color='#ddd'><b>Информация в окне при нажатии кнопки FAQ</b></font></td></tr>
<tr><td class=row1 align=right><b>Заголовок окна</b><br><small>можно использовать теги, двойные кавычки нельзя</small></td><td class=row2><input type=text value=\"$infotitle\" name=infotitle maxlength=100 size=30 style='width:399px'></td></tr>
<tr><td class=row1 align=right><b>Текст окна</b><br><small>можно использовать теги, двойные кавычки нельзя</small></td><td class=row2><textarea cols=50 rows=6 size=500 name=infotext style='width:399px;height:60px'>$infotext</textarea></td></tr>";

print "<tr><td align=center colspan=2><input type=hidden name=saction value=sanswer><input type=submit class=button value='Сохранить' style='width:100px'></td></tr></table></form><br>[<a href='index.php'>вернуться назад</a>]</center><br><br>";

				exit;
			}
		}


		//////////////// Сохранение конфига
		if (isset($_GET['saveconfig'])) {
			if ($_POST['newpassword']!="*****") {
				$pass=trim($_POST['newpassword']);
				$_POST['adminpass']=md5($pass);
			}

			$fd=stripslashes($_POST['fdesription']);
			$fd=str_replace("\\", "/", $fd);
			$fd=str_replace("\"", '', $fd);
			$fdesription=str_replace("\r\n","<br>", $fd);

			$wt=stripslashes($_POST['welcometitle']);
			$wt=str_replace("\\", "/", $wt);
			$wt=str_replace("\"", '', $wt);
			$welcometitle=str_replace("\r\n","<br>", $wt);

			$wtxt=stripslashes($_POST['welcometext']);
			$wtxt=str_replace("\\", "/", $wtxt);
			$wtxt=str_replace("\"", '', $wtxt);
			$welcometext=str_replace("\r\n","<br>", $wtxt);

			$it=stripslashes($_POST['infotitle']);
			$it=str_replace("\\", "/", $it);
			$it=str_replace("\"", '', $it);
			$infotitle=str_replace("\r\n","<br>", $it);

			$itxt=stripslashes($_POST['infotext']);
			$itxt=str_replace("\\", "/", $itxt);
			$itxt=str_replace("\"", '', $itxt);
			$infotext=str_replace("\r\n","<br>", $itxt);

			$f=fopen("datan/stopwords.dat", "w+");
			flock($f, LOCK_EX);
			fwrite($f, $_POST['stopwords']);
			flock($f, LOCK_UN);
			fclose($f);

			$f=fopen("datan/badip.dat", "w+");
			flock($f, LOCK_EX);
			fwrite($f, $_POST['banip']);
			flock($f, LOCK_UN);
			fclose($f);

$configdata="<?\r\n".
"$"."fname=\"".$_POST['fname']."\"; // Название форума выводится в title и заголовке\r\n".
"$"."fdesription=\"".$fdesription."\"; // Название и описание выводится в шапке форума\r\n".
"$"."adminname=\"".$_POST['adminname']."\"; // Логин админа\r\n".
"$"."adminpass=\"".$_POST['adminpass']."\"; // Пароль админа зашифрован md5\r\n".
"$"."readonly=\"".$_POST['readonly']."\"; // Заблокировать возможность создавать темы и отвечать в них? (1-да, 0-нет)\r\n".
"$"."notopic=\"".$_POST['notopic']."\"; // Заблокировать возможность создавать темы? (1-да, 0-нет)\r\n".
"$"."editmsg=\"".$_POST['editmsg']."\"; // Разрешить редактировать свои сообщения? (1-да, 0-нет)\r\n".
"$"."timeoutedit=\"".$_POST['timeoutedit']."\"; // Редактировать сообщения нельзя по истечению часов\r\n".
"$"."redsig=\"".str_replace('"','', $_POST['redsig'])."\"; // Подпись о редактировании\r\n".
"$"."antiham=\"".$_POST['antiham']."\"; // Включить систему Антихам (1-да, 0-нет)\r\n".
"$"."stopwrd=\"".$_POST['stopwrd']."\"; // При нахождении стоп-слов: 1 - ничего не делать, 2 - не пропускать сообщение, 3 - заменять, 4 - IP в бан-лист\r\n".
"$"."antimat=\"".$_POST['antimat']."\"; // Включить антимат1 (1-да, 0-нет)\r\n".
"$"."antimatt=\"".$_POST['antimatt']."\"; // Включить антимат2 (1-да, 0-нет)\r\n".
"$"."badwords=array(\"хуй\", \"хуи\", \"xyй\", \"xyu\", \"х у й\", \"xyй\", \"x y й\", \"х у и\", \"x y й\", \"пизд\", \"nизд\", \"n и 3 д\", \"п и 3 д\", \"пи3д\", \"nи3д\", \"п и з д\", \"блять\", \"б л я т ь\", \"блядь\", \"б л я д ь\", \"ебать\", \"ябать\", \"ебал\", \"ябал\", \"ебан\", \"ёбан\", \"уебан\", \"уёбок\", \"уебок\", \"уёбак\"); // маты\r\n".
"$"."cons=\"".$_POST['cons']."\"; // Маты будут заменяться на это слово\r\n".
"$"."ipinfodb=\"".$_POST['ipinfodb']."\"; // Определять флаг страны и город через www.ipinfodb.com (1-да, 0-нет)\r\n".
"$"."key=\"".$_POST['key']."\"; // Зарегистрируйся www.ipinfodb.com/register.php и впиши сюда ключ\r\n".
"$"."gravatar=\"".$_POST['gravatar']."\"; // Включить использование грАватаров (1-да, 0-нет)\r\n".
"$"."avround=\"".$_POST['avround']."\"; // Делать грАватар круглым (1-да, 0-нет)\r\n".
"$"."gravatarsize=\"".$_POST['gravatarsize']."\"; // Размер грАватара (70=70х70px)\r\n".
"$"."max_file_size=\"".$_POST['max_file_size']."\"; // Максимальный размер аватара в байтах\r\n".
"$"."avatar_width=\"".$_POST['avatar_width']."\"; // Максимальная длина аватара в пикселях\r\n".
"$"."avatar_height=\"".$_POST['avatar_height']."\"; // Максимальная высота аватара в пикселях\r\n".
"$"."uq=\"".$_POST['uq']."\"; // По сколько человек выводить список участников\r\n".
"$"."captchamin=\"".$_POST['captchamin']."\"; // Показывать простую (1) капчу или сложную (0). Настройки сложной ниже\r\n".
"$"."captcha=\"".$_POST['captcha']."\"; // Капча показывает буквы и цифры (0). Капча показывает только цифры (1)\r\n".
"$"."width=\"".$_POST['width']."\"; // Ширина изображения капчи\r\n".
"$"."height=\"".$_POST['height']."\"; // Высота изображения капчи\r\n".
"$"."font_size=\"".$_POST['font_size']."\"; // Размер шрифта капчи\r\n".
"$"."let_amount=\"".$_POST['let_amount']."\"; // Количество символов капчи, которые нужно ввести\r\n".
"$"."fon_let_amount=\"".$_POST['fon_let_amount']."\"; // Количество символов на капче\r\n".
"$"."path_fonts=\"".$_POST['path_fonts']."\"; // Путь к шрифтам для капчи\r\n".
"$"."timezone=\"".$_POST['timezone']."\"; // Часовая поправка - значения от 12 до -12\r\n".
"$"."liteurl=\"".$_POST['liteurl']."\"; // Разрешить делать ссылки активными? (1-да, 0-нет)\r\n".
"$"."repaaddmsg=\"".$_POST['repaaddmsg']."\"; // Сколько репутации добавлять за добавление сообщения?\r\n".
"$"."repaaddtem=\"".$_POST['repaaddtem']."\"; // Сколько репутации добавлять за добавлении темы?\r\n".
"$"."userstatus=\"".$_POST['userstatus']."\"; // Какой статус давать пользователю при регистрации?\r\n".
"$"."maxname=\"".$_POST['maxname']."\"; // Максимальное кол-во символов в имени\r\n".
"$"."maxmail=\"".$_POST['maxmail']."\"; // Максимальное кол-во символов в почт.адресе\r\n".
"$"."maxtopic=\"".$_POST['maxtopic']."\"; // Максимальное кол-во символов в названии темы\r\n".
"$"."maxmsg=\"".$_POST['maxmsg']."\"; // Максимальное кол-во символов в сообщении\r\n".
"$"."topic1=\"".$_POST['topic1']."\"; // Названия иконок тем\r\n".
"$"."topic2=\"".$_POST['topic2']."\";\r\n".
"$"."topic3=\"".$_POST['topic3']."\";\r\n".
"$"."topic4=\"".$_POST['topic4']."\";\r\n".
"$"."lastmess=\"".$_POST['lastmess']."\"; // Включить сохранение последних сообщений (1-да, 0-нет)\r\n".
"$"."lastlines=\"".$_POST['lastlines']."\"; // Сколько последних сообщений сохранять\r\n".
"$"."rankline=\"".$_POST['rankline']."\"; // Показывать картинки-статусы под аватаром\r\n".
"$"."nagrada=\"".$_POST['nagrada']."\"; // Показывать картинки-награды под аватаром\r\n".
"$"."stu0=\"".$_POST['stu0']."\"; // Баллы репутации необходимые для смены статуса\r\n".
"$"."stu1=\"".$_POST['stu1']."\";\r\n".
"$"."stu2=\"".$_POST['stu2']."\";\r\n".
"$"."stu3=\"".$_POST['stu3']."\";\r\n".
"$"."stu4=\"".$_POST['stu4']."\";\r\n".
"$"."stu5=\"".$_POST['stu5']."\";\r\n".
"$"."stu6=\"".$_POST['stu6']."\";\r\n".
"$"."stu7=\"".$_POST['stu7']."\";\r\n".
"$"."stu8=\"".$_POST['stu8']."\";\r\n".
"$"."stu9=\"".$_POST['stu9']."\";\r\n".
"$"."stn0=\"".$_POST['stn0']."\"; // Статусы при накоплении баллов репутации\r\n".
"$"."stn1=\"".$_POST['stn1']."\";\r\n".
"$"."stn2=\"".$_POST['stn2']."\";\r\n".
"$"."stn3=\"".$_POST['stn3']."\";\r\n".
"$"."stn4=\"".$_POST['stn4']."\";\r\n".
"$"."stn5=\"".$_POST['stn5']."\";\r\n".
"$"."stn6=\"".$_POST['stn6']."\";\r\n".
"$"."stn7=\"".$_POST['stn7']."\";\r\n".
"$"."stn8=\"".$_POST['stn8']."\";\r\n".
"$"."stn9=\"".$_POST['stn9']."\";\r\n".
"$"."imgstatus=\"".$_POST['imgstatus']."\"; // Картинки статусов\r\n".
"$"."uploader=\"".$_POST['uploader']."\"; // Разрешить загрузку файлов Uploader (1-да, 0-нет)\r\n".
"$"."max_files=\"".$_POST['max_files']."\"; // Сколько файлов разрешить Uploader\r\n".
"$"."canupfile=\"".$_POST['canupfile']."\"; // Разрешить прикреплять файлы (1-да, 0-нет)\r\n".
"$"."filedir=\"".$_POST['filedir']."\"; // Папка куда загружаются файлы\r\n".
"$"."storagepath=\"".$_POST['storagepath']."\"; // Путь папки куда загружаются файлы\r\n".
"$"."max_upfile_size=\"".$_POST['max_upfile_size']."\"; // Максимальный размер файла в байтах\r\n".
"$"."smwidth=\"".$_POST['smwidth']."\"; // Ширина мини картинки\r\n".
"$"."smheight=\"".$_POST['smheight']."\"; // Высота мини картинки\r\n".
"$"."maxwidth=\"".$_POST['maxwidth']."\"; // Макс ширина картинки\r\n".
"$"."maxheight=\"".$_POST['maxheight']."\"; // Макс высота картинки\r\n".
"$"."fskin=\"".$_POST['fskin']."\"; // Текущий скин форума\r\n".
"$"."ver=\"".$_POST['ver']."\"; // Версия форума\r\n".
"$"."welcome=\"".$_POST['welcome']."\"; // Включить окно, которое появляется в правом верхнем углу (1-да, 0-нет)\r\n".
"$"."welcometitle=\"".$welcometitle."\"; // Заголовок (не используйте двойные кавычки, можно только одинарные)\r\n".
"$"."welcometext=\"".$welcometext."\"; // Текст блока\r\n".
"$"."infotitle=\"".$infotitle."\"; // Заголовок информационной панели, появляется при нажатии FAQ\r\n".
"$"."infotext=\"".$infotext."\"; // Текст блока\r\n?>";

			$file=file("config.php");
			$fp=fopen("config.php","a+");
			flock ($fp,LOCK_EX); 
			ftruncate ($fp,0);
			fputs($fp,$configdata);
			fflush ($fp);
			flock ($fp,LOCK_UN);
			fclose($fp);
			//header("Location: admin.php?event=config");
			echo "<meta http-equiv=refresh content='0; url=admin.php?event=config'>";
			exit;
		}
	}
}


?>