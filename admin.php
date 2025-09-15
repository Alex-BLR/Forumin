<?php

error_reporting(0);
//error_reporting(E_ALL);

include dirname(__FILE__)."/config.php";

$date = gmdate('d.m.Y', time()+3600*($timezone+(date('I')==1?0:1)));
$time = gmdate('H:i', time()+3600*($timezone+(date('I')==1?0:1)));


////////////////// Очистка кода
function replacer($text) {
	//$text=str_replace("¦", '', $text);
	$text=str_replace("&#032;", ' ', $text);
	$text=str_replace(">", '&gt;', $text);
	$text=str_replace("<", '&lt;', $text);
	$text=str_replace("\"", '&quot;', $text);
	$text=preg_replace("/\\\$/", '&#036;', $text);
	$text=preg_replace("/\\\/", '&#092;', $text);
	if (get_magic_quotes_gpc()) {
		$text=str_replace("&#092;&quot;", '&quot;', $text);
		$text=str_replace("&#092;'", '\'', $text);
		$text=str_replace("&#092;&#092;", '&#092;', $text);
	}
	//$text=str_replace("  ", ' ', $text);
 	$text=str_replace("\r\n", '<br>', $text);
	$text=str_replace("\n", '<br>', $text);
	$text=str_replace("\r", '', $text);
	$text=str_replace("\t", ' ', $text);
	$text=preg_replace("/\s\s+/", ' ', $text);
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
				print"<html><head><title>Изменить репутацию: $name</title>
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
<TR height=30><TD colspan=7 align=center>Причина <INPUT type=text name='pochemu' size=60 maxlength=250 value='' title='Причина не более 250 символов'> <INPUT type=submit value='ОK'></TD></TR>
</TABLE></FORM>
<div align=center><fieldset align=justify style='width:520px;border:#333 1px solid;'>
<legend align=center><b><font color=red>ПРАВИЛА И УСЛОВИЯ ИЗМЕНЕНИЯ РЕПУТАЦИИ</font></b></legend>
При изменении чьей-либо репутации обязательно указывайте обоснованную причину! Изменение репутации без особой на то причины может повлечь штраф от админа. Учтите, что если вы, например, ставите пользователю +5 или -5, то и от вашей репутации отминусуется 5 баллов вне зависимости прибавляете ли вы или отнимаете баллы! </fieldset></div><br>[<a href='' onClick='self.close()'>закрыть окно</a>]<br><br>";

			}

			// Ищем в файле repa.dat инфу об этом юзере
			if (is_file("datan/repa.dat"))
			{
				$file="datan/repa.dat";
				$lines=file("$file");
				$i=count($lines);

				print"
					<style>table,th,td{border:1px solid black;border-collapse:collapse;font-family:tahoma;font-size:11px;}tr:nth-child(even){background-color:#f5f5f5}</style>
					<table border=0 cellpadding=1 cellspacing=0 width=100%>
					<tr><td bgcolor=#dddddd colspan=5 align=center>Кто менял репутацию пользователя <b>$name</b></td></tr>
					<tr align=center>
					<td width=53>Дата</td>
					<td width=150>Менял</td>
					<td width=30>Балл</td>
					<td width=65%>Причина</td>
					</tr>";
				do {
					$i--;
					$dt=explode("|",$lines[$i]);

					if (strlen($dt[3])>1) $dt[3]="<a href='index.php?event=profile&pname=$dt[3]' rel='nofollow' target=_blank>$dt[3]</a>"; else $dt[3]="бот форума";

					if ($dt[1]>0) $dt[1]="<td align=center bgcolor=#B7FFB7><b>$dt[1]</b></td>"; else $dt[1]="<td align=center bgcolor=#FF9F9F><b>$dt[1]</b></td>";

					if ($dt[2]==$name)
					{
						$dt[0]=date("d.m.y в H:i",$dt[0]);
						print "<tr><td align=center><small>$dt[5]</small></td><td align=center>$dt[3]</td>$dt[1]<td style='padding:3;'>$dt[4]</td></tr>";
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

			exit("<meta charset='windows-1251'><script>function reload(){location=\"javascript:history.back(1)\"};setTimeout('reload()',300);</script><br><br><br><br><br><br><center><font size=2 face=tahoma><b>Данные успешно изменены!<br><br><a href='javascript:history.back(1)' style='text-decoration:none;'>Продолжить</a></b></font></center>");
			
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

			exit("<meta charset='windows-1251'><script>function reload(){location=\"javascript:history.back(1)\"};setTimeout('reload()',300);</script><br><br><br><br><br><br><center><font size=2 face=tahoma><b>Данные успешно изменены!<br><br><a href='javascript:history.back(1)' style='text-decoration:none;'>Продолжить</a></b></font></center>");

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

				exit("<meta charset='windows-1251'><script>function reload(){location=\"javascript:history.back(1)\"};setTimeout('reload()',300);</script><br><br><br><br><br><br><center><font size=2 face=tahoma><b>Данные успешно изменены!<br><br><a href='javascript:history.back(1)' style='text-decoration:none;'>Продолжить</a></b></font></center>");

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

				exit("<meta charset='windows-1251'><script>function reload(){location=\"javascript:history.back(1)\"};setTimeout('reload()',300);</script><br><br><br><br><br><br><center><font size=2 face=tahoma><b>Данные успешно изменены!<br><br><a href='javascript:history.back(1)' style='text-decoration:none;'>Продолжить</a></b></font></center>");

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
					if ($i==$delnum[$p]) $delyes=1;
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
					if ($i==$delnum[$p]) $delyes=1;
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

			exit("<meta charset='windows-1251'><script>function reload(){location=\"admin.php?event=userwho&page=$page\"};setTimeout('reload()',300);</script><br><br><br><br><br><br><center><font size=2 face=tahoma><b>Данные успешно изменены!<br><br><a href='javascript:history.back(1)' style='text-decoration:none;'>Продолжить</a></b></font></center>");

		} 




		if (isset($_GET['event']))
		{

			//////////////// Добавляем разделы форума
			if ($_GET['event']=="addforum")
			{
				echo'<form action="admin.php?event=makeforum" method=post>
<div align="center"><table width=700 cellpadding=2 cellspacing=1 border=0>
<tr><td class=row1 height=28><b>Добавить раздел, например: Литература</b></td></tr>
<tr><td class=row2 height=28 align=center><input type=text value="" name=razd placeholder="Раздел форума"></td></tr>
<tr><td class=row2><textarea cols=50 rows=3 size=300 style="width:100%;height:50px" name=razdtxt placeholder="Описание раздела"></textarea></td></tr>
<tr><td class=row1><p align=center><input type=submit class=button style="width:100px" value="Добавить"></p></td></tr>
</table></div></form><br><p align=center>[<a href="javascript:history.back(1)" style="text-decoration:none">вернуться назад</a>]</p>';

			}

			if ($_GET['event']=="makeforum")
			{
				$razd=str_replace("|", '', $_POST['razd']);
				$razdtxt=str_replace("|", '', $_POST['razdtxt']);

				if ($razd=="") exit("<br><p align=center><font size=2 face=tahoma><b>Вы не ввели название раздела форума!</b><br><br>[<a href='javascript:history.back(1)' style='text-decoration:none;'>вернуться назад</a>]</font></p>");

				$nextnum="0";

				if (is_file("datan/forum.dat"))
				{
					$lines=file("datan/forum.dat");
					$imax=count($lines);
					$i=0;
					do {
						$dt=explode("|", $lines[$i]);
						if ($nextnum<$dt[0]) {$nextnum=$dt[0];}
						$i++;
					} while($i<$imax);
					$nextnum++;
				}

				$rdat="$nextnum|$razd|$razdtxt|$date|$time|||||||";

				$rdat=htmlspecialchars($rdat, ENT_COMPAT, "windows-1251");
				$rdat=stripslashes($rdat);
				$rdat=str_replace("\r\n", '', $rdat);

				$fp=fopen("datan/forum.dat","a+");
				flock($fp,LOCK_EX);
				fputs($fp,"$rdat\r\n");
				fflush($fp);
				flock($fp,LOCK_UN);
				fclose($fp);
				echo "<meta http-equiv=refresh content='0; url=index.php'>";
				exit;
			}


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

				if ($si!=$ui) print "<center><font color=red><B>В файле статистики имеются ошибки!</B></font></center><br>";

				if (isset($_GET['page'])) $page=$_GET['page']; else $page="1";

				if (!ctype_digit($page)) $page=1; //защита

				if ($page=="0") $page="1"; else $page=abs($page);

				$maxpage=ceil(($ui+1)/$uq);

				if ($page>$maxpage) $page=$maxpage;

				$i=1+$uq*($page-1);

				if ($i>$ui) $i=$ui-$uq; $lm=$i+$uq;
				if ($lm>$ui) $lm=$ui+1;

				echo'<table width=100% cellpadding=0 cellspacing=1 border=0><tr><td>
<table width=100% cellpadding=1 cellspacing=0 border=0><tr>
<th width=10>№</th>
<th width=120>Имя</th>
<th width=20>Пол</th>
<th width=70>Регистрация</th>
<th width=150>Емайл</th>
<th width=30>Тем</th>
<th width=30>Сообщ</th>
<th width=70>Репутация</th>
<th width=70>Нарушения</th>
<th width=400>Статус и награды</th>
<th width=70>Звёзды</th>
</tr>';

				$delblok="<FORM action='admin.php?usersdelete=$last&page=$page' method=POST name=delform><td class=$t1><table align=center cellpadding='0' cellspacing='0' border=0><th>X</th>";

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

					$delblok.="<tr><td><input style='width:18px;height:18px' type=checkbox name='del$npp' value=''></td></tr>";

					print"<tr><td class=$t1 align=center><small>$npp</small></td><td class=$t1><b><a href=\"index.php?event=profile&pname=$tdt[0]\">$tdt[0]</a></b></td>";

					print"<td class=$t1><center>$tdt[6]</center></td>
<td class=$t1><center>$tdt[4]</center></td>
<td class=$t1><small><center><a href=\"mailto:$tdt[3]\">$tdt[3]</a></center></small></td>
<td class=$t1><center>$sdt[1]</center></td>
<td class=$t1><center>$sdt[2]</center></td>
<td class=$t1><form action='admin.php?newrepa&page=$page' method=post><center><input type=text name=repa value='$sdt[3]' size=5 maxlength=5 style='width:50px;height:22px'><input type=hidden name=usernum value='$i'> <input type=submit name=submit value='OK' class=button style='width:30px'></center></form></td>
<td class=$t1><form action='admin.php?userstatus&page=$page' method=post><center><input type=text name=status value='$sdt[4]' size=4 maxlength=3 style='width:40px;height:22px'><input type=hidden name=usernum value='$i'> <input type=submit name=submit value='OK' class=button style='width:30px'></center></form></td>
<td class=$t1><form action='admin.php?newstatus=$i&page=$page' method=post><center><input type=text class=post name=status value='$tdt[13]' size=20 maxlength=500 style='width:90%;height:22px'> <input type=submit name=submit value='OK' class=button style='width:30px'></center></form></td>
<td class=$t1><form action='admin.php?newreiting=$i&page=$page' method=post><center><input type=text class=post name=reiting value='$tdt[2]' size=2 maxlength=2 style='width:28px;height:22px'> <input type=submit name=submit value='OK' class=button style='width:30px'></center></form></td></tr>";

					$t3=$t2;
					$t2=$t1;
					$t1=$t3;

				} while($i<$lm);

				print"</table></td>$delblok</table></td></tr></table><br>
					<div align=right>
					<input type=hidden name=first value='$first'>
					<input type=hidden name=last value='$last'>
					<input type=submit class=button value='Удалить выбранных' style='width:150'></FORM>
					</div>";

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

				if ($error>0) {print"<br><br><center><font color=red><B>В файле статистики имеются ошибки!</B></font></center><br><br>";}

				echo'<br><b>Статус и награды</b>. Награды пользователю, например, со статусом <mark>рядовой</mark> добавляем через символ @, то есть <mark>рядовой@Награда за честность@Награда за отвагу</mark> и т.д.<br><br><br><center>[<a href="index.php" style="text-decoration:none;">Вернуться на форум</a>]</center>
</body></html>';
			}


			///////////////////// Делаем копию БД
			if ($_GET['event']=="copytopic")
			{
				if (copy("datan/topic.dat", "datan/topic-copy.dat")) exit("<br><br><center>Копия файла <b>topic.dat</b> создана под именем <b>topic-copy.dat</b><br><br>[<a href='javascript:history.back(1)'>вернуться назад</a>]</center>"); else exit("<br><br><center>Ошибка создания копии!<br><br>[<a href='javascript:history.back(1)'>вернуться назад</a>]</center>");
			}
			if ($_GET['event']=="copyrepa")
			{
				if (copy("datan/repa.dat", "datan/repa-copy.dat")) exit("<br><br><center>Копия файла <b>repa.dat</b> создана под именем <b>repa-copy.dat</b><br><br>[<a href='javascript:history.back(1)'>вернуться назад</a>]</center>"); else exit("<br><br><center>Ошибка создания копии!<br><br>[<a href='javascript:history.back(1)'>вернуться назад</a>]</center>");
			}
			if ($_GET['event']=="copyuserstat")
			{
				if (copy("datan/userstat.dat", "datan/userstat-copy.dat")) exit("<br><br><center>Копия файла <b>userstat.dat</b> создана под именем <b>userstat-copy.dat</b><br><br>[<a href='javascript:history.back(1)'>вернуться назад</a>]</center>"); else exit("<br><br><center>Ошибка создания копии!<br><br>[<a href='javascript:history.back(1)'>вернуться назад</a>]</center>");
			}
			if ($_GET['event']=="copyusersdat")
			{
				if (copy("datan/usersdat.php", "datan/usersdat-copy.php")) exit("<br><br><center>Копия файла <b>usersdat.php</b> создана под именем <b>usersdat-copy.php</b><br><br>[<a href='javascript:history.back(1)'>вернуться назад</a>]</center>"); else exit("<br><br><center>Ошибка создания копии!<br><br>[<a href='javascript:history.back(1)'>вернуться назад</a>]</center>");
			}

			///////////////////// Восстановить из копии БД
			if ($_GET['event']=="restoretopic")
			{
				if (copy("datan/topic-copy.dat", "datan/topic.dat")) exit("<br><br><center>Файл <b>topic.dat</b> восстановлен из копии!<br><br>[<a href='javascript:history.back(1)'>вернуться назад</a>]</center>"); else exit("<br><br><center>Ошибка восстановления из копии!<br><br>[<a href='javascript:history.back(1)'>вернуться назад</a>]</center>");
			}
			if ($_GET['event']=="restorerepa")
			{
				if (copy("datan/repa-copy.dat", "datan/repa.dat")) exit("<br><br><center>Файл <b>repa.dat</b> восстановлен из копии!<br><br>[<a href='javascript:history.back(1)'>вернуться назад</a>]</center>"); else exit("<br><br><center>Ошибка восстановления из копии!<br><br>[<a href='javascript:history.back(1)'>вернуться назад</a>]</center>");
			}
			if ($_GET['event']=="restoreuserstat")
			{
				if (copy("datan/userstat-copy.dat", "datan/userstat.dat")) exit("<br><br><center>Файл <b>userstat.dat</b> восстановлен из копии!<br><br>[<a href='javascript:history.back(1)'>вернуться назад</a>]</center>"); else exit("<br><br><center>Ошибка восстановления из копии!<br><br>[<a href='javascript:history.back(1)'>вернуться назад</a>]</center>");
			}
			if ($_GET['event']=="restoreusersdat")
			{
				if (copy("datan/usersdat-copy.php", "datan/usersdat.php")) exit("<br><br><center>Файл <b>usersdat.php</b> восстановлен из копии!<br><br>[<a href='javascript:history.back(1)'>вернуться назад</a>]</center>"); else exit("<br><br><center>Ошибка восстановления из копии!<br><br>[<a href='javascript:history.back(1)'>вернуться назад</a>]</center>");
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
				if ($topicmail==TRUE) {$tm1="checked"; $tm2="";} else {$tm2="checked"; $tm1="";}
				if ($uploader==TRUE) {$up1="checked"; $up2="";} else {$up2="checked"; $up1="";}
				if ($canupfile==TRUE) {$cs1="checked"; $cs2="";} else {$cs2="checked"; $cs1="";}
				if ($lastmess==TRUE) {$lm1="checked"; $lm2="";} else {$lm2="checked"; $lm1="";}
				if ($rankline==TRUE) {$rankline1="checked"; $rankline2="";} else {$rankline2="checked"; $rankline1="";}
				if ($nagrada==TRUE) {$nagrada1="checked"; $nagrada2="";} else {$nagrada2="checked"; $nagrada1="";}
				if ($welcome==TRUE) {$wel1="checked"; $wel2="";} else {$wel2="checked"; $wel1="";}
				if ($telegramsend==TRUE) {$tel1="checked"; $tel2="";} else {$tel2="checked"; $tel1="";}

				$stopwrd1='';
				$stopwrd2='';
				$stopwrd3='';
				$stopwrd4='';
				if ($stopwrd=="4") {$stopwrd4="checked";}
				if ($stopwrd=="3") {$stopwrd3="checked";}
				if ($stopwrd=="2") {$stopwrd2="checked";}
				if ($stopwrd=="1") {$stopwrd1="checked";}

print "<style>
* {margin:0; padding:0;}
#options {width:690px; margin:10px auto; text-align:right;}
#options a {text-decoration:none;}
#options a:hover {color:red}
#acc {font: 12px tahoma; width:690px; list-style:none; margin:0 auto 10px}
#acc h3 {width:676px; border:1px solid #555; padding:6px 6px 7px; margin-top:10px; cursor:pointer}
#acc h3:hover {background: green}
#acc .acc-section {overflow: hidden}
#acc .acc-content {width:658px; padding:10px; border:1px solid #555; border-top: none}

table, textarea {border: 1px solid #555; padding:0 2 0 2; margin: 1px; border-collapse:collapse;}
td.row2,td.row1 {font: 12px tahoma;border: 1px solid #222; padding: 3 3px;}
input.radio {width: 15px; height: 15px; text-align: bottom; padding: 0 0 0 0;}
</style>

<center>[<a href='index.php'>вернуться назад</a>]<br>

<div id='options'>[ <a href='javascript:parentAccordion.pr(1)'>Развернуть</a> | <a href='javascript:parentAccordion.pr(-1)'>Свернуть</a> ]</div>

<ul class='acc' id='acc'>
	<li>
		<h3>Сделать копию или восстановить файлы</h3>
		<div class='acc-section'>
			<div class='acc-content'>
<b>topic.dat</b> (<a href='admin.php?event=copytopic' style='color:red'>копия</a> / <a href='admin.php?event=restoretopic' style='color:red' onclick=\"return confirm('Вы уверены, что хотите восстановить из копии этот файл?')\">восст</a>) • <b>repa.dat</b> (<a href='admin.php?event=copyrepa' style='color:red'>копия</a> / <a href='admin.php?event=restorerepa' style='color:red' onclick=\"return confirm('Вы уверены, что хотите восстановить из копии этот файл?')\">восст</a>) • <b>userstat.dat</b> (<a href='admin.php?event=copyuserstat' style='color:red'>копия</a> / <a href='admin.php?event=restoreuserstat' style='color:red' onclick=\"return confirm('Вы уверены, что хотите восстановить из копии этот файл?')\">восст</a>) • <b>usersdat.php</b> (<a href='admin.php?event=copyusersdat' style='color:red'>копия</a> / <a href='admin.php?event=restoreusersdat' style='color:red' onclick=\"return confirm('Вы уверены, что хотите восстановить из копии этот файл?')\">восст</a>)

			</div>
		</div>
	</li>

	<li>
		<h3>Общие настройки</h3>
		<div class='acc-section'>
			<div class='acc-content'>

<form action=\"admin.php?saveconfig\" method=POST>
<table width=600px cellpadding=2 cellspacing=1>
<tr><td class=row1 align=right><b>Название форума</b><br>выводится в title и заголовке</td><td class=row2><input type=text value=\"$fname\" name=fname maxlength=50 size=50 style='width:399px;'></td></tr>
<tr><td class=row1 align=right><b>Описание форума</b><br>выводится в шапке форума<br>теги можно, дв. кавычки (\" \") нет</td><td class=row2><textarea cols=50 rows=6 size=400 name=fdesription style='width:399px;height:60px'>$fdesription</textarea></td></tr>
<tr><td class=row1 align=right><b>Логин и Пароль админа</b><br>удалите *** и введите пароль</td><td class=row2><input type=text value=\"$adminname\" maxlength=20 name=\"adminname\" size=14 style='width:120px'> Пароль: <input name=\"adminpass\" type=hidden value=\"$adminpass\"><input type=text value='*****' name=\"newpassword\" size=14 style='width:120px'></td></tr>
<tr><td class=row1 align=right><b>Заблокировать форум</b><br>запрет создавать темы и отвечать</td><td class=row2><input class=radio type=radio name=readonly value=\"0\" $ro2 /> нет &nbsp;&nbsp; <input class=radio type=radio name=readonly value=\"1\" $ro1 /> да</td></tr>
<tr><td class=row1 align=right><b>Запретить создавать темы</b><br>запрет создавать темы</td><td class=row2><input class=radio type=radio name=notopic value=\"0\" $notopic2 /> нет &nbsp;&nbsp; <input class=radio type=radio name=notopic value=\"1\" $notopic1 /> да</td></tr>
<tr><td class=row1 align=right><b>Использовать граватар</b><br>размер аватара: 70 = 70 х 70 px</td><td class=row2><input class=radio type=radio name=gravatar value=\"0\" $grv2 /> нет &nbsp;&nbsp; <input class=radio type=radio name=gravatar value=\"1\" $grv1 /> да &nbsp; &nbsp; / &nbsp; &nbsp; размер: <input type=text value='$gravatarsize' name=\"gravatarsize\" maxlength=3 size=4 style='width:28px'></td></tr>
<tr><td class=row1 align=right><b>Круглый граватар</b><br>использовать круглый аватар</td><td class=row2><input class=radio type=radio name=avround value=\"0\" $avr2 /> нет &nbsp;&nbsp; <input class=radio type=radio name=\"avround\" value=\"1\" $avr1 /> да</td></tr>
<tr><td class=row1 align=right><b>Параметры аватара</b></td><td class=row2><input type=text value='$avatar_width' maxlength=3 name='avatar_width' size=4 style='width:28px'> х <input type=text value='$avatar_height' maxlength=3 name='avatar_height' size=4 style='width:28px'> px &nbsp; <input type=text value='$max_file_size' maxlength=6 style='width:55px' name='max_file_size' size=7> байт</td></tr>
<tr><td class=row1 align=right><b>Участников на страницу<b></td><td class=row2><input type=text value='$uq' maxlength=2 name=uq size=4 style='width:28px'></td></tr>
<tr><td class=row1 align=right><b>Флаг страны пользователя</b><br>если #2, то получи ключ<br>https://www.ipinfodb.com</td><td class=row2><input class=radio type=radio name='ipinfodb' value='0' $ipi2 /> Вариант #1 &nbsp;&nbsp; <input class=radio type=radio name='ipinfodb' value='1' $ipi1 /> Вариант #2 &nbsp; &nbsp; <b>Ключ:</b><br><input name='key' type='text' value='$key' size=14 maxlength=64 style='margin-left:0px;width:399px'></td></tr>
<tr><td class=row1 align=right><b>Часовая поправка</b></td><td class=row2><input type=text value='$timezone' maxlength=3 name=timezone size=7 style='width:28px'> час. (значение от -12 до 12)</td></tr>
<tr><td class=row1 align=right><b>Делать ссылки кликабельными</b></td><td class=row2><input class=radio type=radio name=liteurl value='0' $lu2 /> нет &nbsp;&nbsp; <input class=radio type=radio name=liteurl value=\"1\" $lu1 /> да </td></tr>
<tr><td class=row1 align=right><b>Макс. количество символов</b></td><td class=row2><input type=text value='$maxname' name=maxname maxlength=2 size=4 style='width:28px'> в имени, <input type=text value='$maxmail' name=maxmail maxlength=2 size=4 style='width:28px'> в email, <input type=text value='$maxtopic' maxlength=3 name=maxtopic size=4 style='width:28px'> в теме, <input type=text value='$maxmsg' maxlength=4 name=maxmsg size=4 style='width:50px'> в сообщении</td></tr>
<tr><td class=row1 align=right><b>Сохранять последние сообщения</b><br>можно выводить на сайте</td><td class=row2><input class=radio type=radio name=lastmess value=\"0\" $lm2 /> нет &nbsp;&nbsp; <input class=radio type=radio name=lastmess value=\"1\" $lm1 /> да, сохранять <input type=text value='$lastlines' maxlength=3 name=lastlines size=5 style='width:28px'> сообщений</td></tr>

<tr><td class=row1 align=right><b>Мылить админу о новой теме</b><br></td><td class=row2><input class=radio type=radio name=topicmail value=\"0\" $tm2 /> нет &nbsp;&nbsp; <input class=radio type=radio name=topicmail value=\"1\" $tm1 /> да</td></tr>

<tr><td class=row1 align=right><b>E-mail админа</b></td><td class=row2><input type=text value=\"$adminmail\" name=ver maxlength=50 size=50 style='width:399px'></td></tr>
<tr><td class=row1 align=right><b>От кого E-mail</b></td><td class=row2><input type=text value=\"$frommail\" name=ver maxlength=50 size=50 style='width:399px'></td></tr>

<tr><td class=row1 align=right><b>Версия форума</b></td><td class=row2><input type=text value=\"$ver\" name=ver maxlength=50 size=50 style='width:399px'></td></tr>
<tr><td class=row1 align=right><b>Скин форума</b></td><td class=row2 height='38px'><select class=input name='fskin'>";

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

print "</select></td></tr></table>

			</div>
		</div>
	</li>

	<li>
		<h3>Названия иконок тем</h3>
		<div class='acc-section'>
			<div class='acc-content'>

<table width=600px cellpadding=2 cellspacing=1>
<tr><td class=row1 align=right>Иконка: <b>datan/1.png</b> &nbsp; <img src='datan/1.png'></td><td class=row2><input type=text value=\"$topic1\" style='width:399px' name=topic1 size=10></td></tr>
<tr><td class=row1 align=right>Иконка: <b>datan/2.png</b> &nbsp; <img src='datan/2.png'></td><td class=row2><input type=text value=\"$topic2\" style='width:399px' name=topic2 size=10></td></tr>
<tr><td class=row1 align=right>Иконка: <b>datan/3.png</b> &nbsp; <img src='datan/3.png'></td><td class=row2><input type=text value=\"$topic3\" style='width:399px' name=topic3 size=10></td></tr>
<tr><td class=row1 align=right>Иконка: <b>datan/4.png</b> &nbsp; <img src='datan/4.png'></td><td class=row2><input type=text value=\"$topic4\" style='width:399px' name=topic4 size=10></td></tr>
<tr><td class=row1 align=right>Иконка: <b>datan/5.png</b> &nbsp; <img src='datan/5.png'></td><td class=row2><input type=text value=\"$topic5\" style='width:399px' name=topic5 size=10></td></tr>
<tr><td class=row1 align=right>Иконка: <b>datan/6.png</b> &nbsp; <img src='datan/6.png'></td><td class=row2><input type=text value=\"$topic6\" style='width:399px' name=topic6 size=10></td></tr>
<tr><td class=row1 align=right>Иконка: <b>datan/7.png</b> &nbsp; <img src='datan/7.png'></td><td class=row2><input type=text value=\"$topic7\" style='width:399px' name=topic7 size=10></td></tr>
<tr><td class=row1 align=right>Иконка: <b>datan/8.png</b> &nbsp; <img src='datan/8.png'></td><td class=row2><input type=text value=\"$topic8\" style='width:399px' name=topic8 size=10></td></tr>
<tr><td class=row1 align=right>Иконка: <b>datan/9.png</b> &nbsp; <img src='datan/9.png'></td><td class=row2><input type=text value=\"$topic9\" style='width:399px' name=topic9 size=10></td></tr>
<tr><td class=row1 align=right>Иконка: <b>datan/10.png</b> &nbsp; <img src='datan/10.png'></td><td class=row2><input type=text value=\"$topic10\" style='width:399px' name=topic10 size=10></td></tr>
<tr><td class=row1 align=right>Иконка: <b>datan/11.png</b> &nbsp; <img src='datan/11.png'></td><td class=row2><input type=text value=\"$topic11\" style='width:399px' name=topic11 size=10></td></tr>
<tr><td class=row1 align=right>Иконка: <b>datan/12.png</b> &nbsp; <img src='datan/12.png'></td><td class=row2><input type=text value=\"$topic12\" style='width:399px' name=topic12 size=10></td></tr>
<tr><td class=row1 align=right>Иконка: <b>datan/13.png</b> &nbsp; <img src='datan/13.png'></td><td class=row2><input type=text value=\"$topic13\" style='width:399px' name=topic13 size=10></td></tr>
<tr><td class=row1 align=right>Иконка: <b>datan/14.png</b> &nbsp; <img src='datan/14.png'></td><td class=row2><input type=text value=\"$topic14\" style='width:399px' name=topic14 size=10></td></tr>
<tr><td class=row1 align=right>Иконка: <b>datan/15.png</b> &nbsp; <img src='datan/15.png'></td><td class=row2><input type=text value=\"$topic15\" style='width:399px' name=topic15 size=10></td></tr>
</table>
			</div>
		</div>
	</li>

	<li>
		<h3>Редактирование сообщений</h3>
		<div class='acc-section'>
			<div class='acc-content'>

<table width=600px cellpadding=2 cellspacing=1>
<tr><td class=row1 align=right><b>Разрешить редакт. сообщения</b></td><td class=row2><input class=radio type=radio name=editmsg value=\"1\" $edsmg1/> да &nbsp;&nbsp; <input class=radio type=radio name=editmsg value=\"0\" $edsmg2/> нет </td></tr>
<tr><td class=row1 align=right><b>Редактировать нельзя по истечению</b></td><td class=row2><input type=text value='$timeoutedit' maxlength=5 name=timeoutedit size=6 style='width:28px'> часов</td></tr>
<tr><td class=row1 align=right><b>Подпись о редактировании</b></td><td class=row2><input type=text value='$redsig' name='redsig' size=50 style='width:399px'></td></tr>
</table>

			</div>
		</div>
	</li>

	<li>
		<h3>Репутация - Статуы - Награды</h3>
		<div class='acc-section'>
			<div class='acc-content'>

<table width=600px cellpadding=2 cellspacing=1>
<tr><td class=row1 align=right><b>Сколько репы давать при</b></td><td class=row2>добавлении темы: <input type=text value=\"$repaaddtem\" style='width:28px' name=repaaddtem maxlength=2 size=3> сообщения: <input type=text value=\"$repaaddmsg\" style='width:28px' name=repaaddmsg maxlength=2 size=3></td></tr>
<tr><td class=row1 align=right><b>Показывать шильдик на аватаре</b></td><td class=row2><input type=text value=\"$repatimeday\" style='width:28px' name=repatimeday maxlength=2 size=3> дней с информацией кто менял репу юзеру</td></tr>
<tr><td class=row1 align=right><b>Статус юзера при регистрации</b></td><td class=row2><input type=text value=\"$userstatus\" style='width:200px' name=userstatus maxlength=30 size=10></td></tr>
<tr><td class=row1 align=right><b>Показывать картинки-статусы</b></td><td class=row2><input class=radio type=radio name=rankline value=\"1\" $rankline1/> да &nbsp; <input class=radio type=radio name=rankline value=\"0\" $rankline2/> нет</td></tr>
<tr><td class=row1 align=right><b>Показывать картинки-награды</b></td><td class=row2><input class=radio type=radio name=nagrada value=\"1\" $nagrada1/> да &nbsp; <input class=radio type=radio name=nagrada value=\"0\" $nagrada2/> нет</td></tr>
<tr><td align=center colspan=2 class=row1><b>Настройки статусов (званий)</b></td></tr>
<tr><td class=row1 align=right><b>Картинки-статусы</b></td><td class=row2>&nbsp;<select class=input name=imgstatus>";

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

print "</select> сохраните настройки - картинки поменяются</td></tr>
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
</table>
			</div>
		</div>
	</li>

	<li>
		<h3>Загрузка файлов</h3>
		<div class='acc-section'>
			<div class='acc-content'>

<table width=600px cellpadding=2 cellspacing=1>
<tr><td align=right class=row1><b>Разрешить загрузку Uploader-ом</b></td><td class=row2><input class=radio type=radio name=uploader value=\"1\" $up1/> да &nbsp; <input class=radio type=radio name=uploader value=\"0\" $up2/> нет &nbsp; | &nbsp; Форма для загрузки <input type=text value='$max_files' maxlength=3 name='max_files' size=3 style='width:30px'> файлов</td></tr>
<tr><td align=right class=row1><b>Разрешить прикреплять файлы</b></td><td class=row2><input class=radio type=radio name=canupfile value=\"1\" $cs1/> да &nbsp; <input class=radio type=radio name=canupfile value=\"0\" $cs2/> нет</td></tr>
<tr><td align=right class=row1><b>Максимальный размер файла</b></td><td class=row2><input type=text value='$max_upfile_size' maxlength=7 name='max_upfile_size' size=7 style='width:100px'> байт</td></tr>
<tr><td align=right class=row1><b>Папка куда загружаются файлы</b></td><td class=row2><input type=text value='$filedir' class=post maxlength=16 name='filedir' size=7 style='width:100px'> по умолчанию <B>./load</B></td></tr>
<tr><td align=right class=row1><b>Путь папки загружаемых файлов</b></td><td class=row2><input type=text value='$storagepath' maxlength=70 name='storagepath' size=50 style='width:399px'></td></tr>
<tr><td align=right class=row1><b>Размер мини картинки</b><br>если прикрепляемая картинка больше<br>этого размера то её превьюшка будет</td><td class=row2><input type=text value='$smwidth' maxlength=3 name='smwidth' size=7 style='width:50px'> х <input type=text value='$smheight' maxlength=3 name='smheight' size=7 style='width:50px'> пикселей</td></tr>
<tr><td align=right class=row1><b>Макс размер картинки</b><br>прикрепляемая картинка будет<br>сжиматься до указанного размера</td><td class=row2><input type=text value='$maxwidth' maxlength=4 name='maxwidth' size=7 style='width:50px'> х <input type=text value='$maxheight' maxlength=4 name='maxheight' size=7 style='width:50px' title='Прикрепленная картинка при просмотре откроется с таким размером. Т.е. если прикрепить большую картинку, например 1600х1200, то при просмотре она сожмется до указанного вами размера'> пикселей</td></tr>
</table>

			</div>
		</div>
	</li>

	<li>
		<h3>Бан - Антимат - Капча</h3>
		<div class='acc-section'>
			<div class='acc-content'>

<table width=600px cellpadding=2 cellspacing=1>
<tr><td class=row1 align=right><b>Включить Антихам</b></td><td class=row2><input class=radio type=radio name=antiham value=\"1\" $ah1 /> да &nbsp; <input class=radio type=radio name=antiham value=\"0\" $ah2 /> нет</td></tr>
<tr><td class=row1 align=right><b>Антихам</b><br><small>если в сообщении будут стоп-слова</small></td><td class=row2><input class=radio type=radio name=stopwrd value=\"1\" $stopwrd1 /> ничего не делать<br><input class=radio type=radio name=stopwrd value=\"2\" $stopwrd2 /> не пропускать сообщение<br><input class=radio type=radio name=stopwrd value=\"3\" $stopwrd3 /> заменять на <input name=cons type='text' value=\"$cons\" size=14 maxlength=50 style='width:150px'><br><input class=radio type=radio name=stopwrd value=\"4\" $stopwrd4 /> добавить IP в бан</td></tr>
<tr><td class=row1 align=right><b>Стоп-слова</b><br><small>по одному слову через пробел</small></td><td class=row2><textarea cols=50 rows=6 size=500 name=stopwords style='width:399px;height:60px'>".file_get_contents("datan/stopwords.dat")."</textarea></td></tr>
<tr><td class=row1 align=right><b>Бан-лист</b><br><small>по одному IP через пробел</small></td><td class=row2><textarea cols=50 rows=6 size=500 name=banip style='width:399px;height:60px'>".file_get_contents("datan/badip.dat")."</textarea></td></tr>
<tr><td class=row1 align=right><b>Антимат 1</b></td><td class=row2 rowspan=2>
<table width=100% class=row1><tr><td class=row2><input class=radio type=radio name=antimat value='0' $am2 /> нет &nbsp; <input class=radio type=radio name=antimat value='1' $am1 /> да</td>
<td rowspan=2 class=row2>заменять маты на <input name=cons type='text' value=\"$cons\" size=14 maxlength=60 style='margin-left:0px;width:150px'></td>
</tr><tr><td class=row2><input class=radio type=radio name=antimatt value='0' $aam2 /> нет &nbsp; <input class=radio type=radio name=antimatt value='1' $aam1 /> да</td>
</tr></table></td></tr><tr><td class=row1 align=right><b>Антимат 2</b></td></tr>
<tr><td align=center colspan=2 bgcolor=green><font color='#ddd'><b>Капча (антиспам)</b></font></td></tr>
<tr><td class=row1 align=right><b>Капча</b></td><td class=row2><input class=radio type=radio name=captchamin value='0' $cap2 /> сложная &nbsp;&nbsp; <input class=radio type=radio name=captchamin value='1' $cap1 /> простая</td></tr>
<tr><td class=row1 align=right><b>Настройка сложной</b></td><td class=row2><input class=radio type=radio name=captcha value='0' $scap2 /> буквы и цифры &nbsp; <input class=radio type=radio name=captcha value='1' $scap1 /> только цифры<br><input type=text value='$width' name=width maxlength=3 size=4 style='width:35px'> ширина, &nbsp; <input type=text value='$height' name=height maxlength=3 size=4 style='width:35px'> высота, &nbsp; 
<input type=text value='$font_size' name=font_size maxlength=3 size=4 style='width:35px'> размер шрифта<br><input type=text value='$fon_let_amount' name=fon_let_amount maxlength=3 size=4 style='width:35px'> симв. на капче, &nbsp; <input type=text value='$let_amount' name=let_amount maxlength=3 size=4 style='width:35px'> надо вводить<br><input type=text value='$path_fonts' maxlength=16 name='path_fonts' size=7 style='width:70px'> папка со шрифтами капчи</td></tr>
</table>
			</div>
		</div>
	</li>

	<li>
		<h3>Настройки для Telegram</h3>
		<div class='acc-section'>
			<div class='acc-content'>

<table width=600px cellpadding=2 cellspacing=1>
<tr><td class=row1 align=right><b>Отправлять сообщения в телеграм</b></td><td class=row2><input class=radio type=radio name=telegramsend value='1' $tel1 /> да &nbsp;&nbsp; <input class=radio type=radio name=telega value='0' $tel2 /> нет &nbsp; &nbsp; | &nbsp; &nbsp; инструкция в readme.txt</td></tr>
<tr><td class=row1 align=right><b>Telegram API token</b></td><td class=row2><input name=\"telegramtoken\" type='text' value='$telegramtoken' size=14 maxlength=100 style='margin-left:0px;width:399px'></td></tr>
<tr><td class=row1 align=right><b>Telegram User ID</b></td><td class=row2><input name=\"telegramid\" type='text' value='$telegramid' size=14 maxlength=12 style='margin-left:0px;width:100px'></td></tr>
</table>
			</div>
		</div>
	</li>

	<li>
		<h3>Всплывающее окно в правом углу</h3>
		<div class='acc-section'>
			<div class='acc-content'>

<table width=600px cellpadding=2 cellspacing=1>
<tr><td class=row1 align=right><b>Показывать окно</b></td><td class=row2><input class=radio type=radio name=welcome value='1' $wel1 /> да &nbsp;&nbsp; <input class=radio type=radio name=welcome value='0' $wel2 /> нет</td></tr>
<tr><td class=row1 align=right><b>Заголовок окна</b><br>теги можно, дв. кавычки (\" \") нет</td><td class=row2><input type=text value=\"$welcometitle\" name=welcometitle maxlength=200 size=50 style='width:399px'></td></tr>
<tr><td class=row1 align=right><b>Текст окна</b><br>теги можно, дв. кавычки (\" \") нет</td><td class=row2><textarea cols=50 rows=6 size=500 name=welcometext style='width:399px;height:100px'>$welcometext</textarea></td></tr>
</table>
			</div>
		</div>
	</li>

	<li>
		<h3>Выпадающая информация кнопки FAQ</h3>
		<div class='acc-section'>
			<div class='acc-content'>

<table width=600px cellpadding=2 cellspacing=1>
<tr><td class=row1 align=right><b>Заголовок окна</b><br>теги можно, дв. кавычки (\" \") нет</td><td class=row2><input type=text value=\"$infotitle\" name=infotitle maxlength=200 size=50 style='width:399px'></td></tr>
<tr><td class=row1 align=right><b>Текст окна</b><br>теги можно, дв. кавычки (\" \") нет</td><td class=row2><textarea cols=50 rows=6 size=500 name=infotext style='width:399px;height:100px'>$infotext</textarea></td></tr>
</table>
			</div>
		</div>
	</li>
</ul>

<p align=center><input type=hidden name=saction value=sanswer><input type=submit class=button value='Сохранить' style='width:100px'></p>

</form>

<br><center>[<a href='index.php'>вернуться назад</a>]</center><br><br>

<script>var TINY={};function T$(i){return document.getElementById(i)}function T$$(e,p){return p.getElementsByTagName(e)}TINY.accordion=function(){function slider(n){this.n=n;this.a=[]}slider.prototype.init=function(t,e,m,o,k){var a=T$(t),i=s=0,n=a.childNodes,l=n.length;this.s=k||0;this.m=m||0;for(i;i<l;i++){var v=n[i];if(v.nodeType!=3){this.a[s]={};this.a[s].h=h=T$$(e,v)[0];this.a[s].c=c=T$$('div',v)[0];h.onclick=new Function(this.n+'.pr(0,'+s+')');if(o==s){h.className=this.s;c.style.height='auto';c.d=1}else{c.style.height=0;c.d=-1}s++}}this.l=s};slider.prototype.pr=function(f,d){for(var i=0;i<this.l;i++){var h=this.a[i].h,c=this.a[i].c,k=c.style.height;k=k=='auto'?1:parseInt(k);clearInterval(c.t);if((k!=1&&c.d==-1)&&(f==1||i==d)){c.style.height='';c.m=c.offsetHeight;c.style.height=k+'px';c.d=1;h.className=this.s;su(c,1)}else if(k>0&&(f==-1||this.m||i==d)){c.d=-1;h.className='';su(c,-1)}}};function su(c){c.t=setInterval(function(){sl(c)},20)};function sl(c){var h=c.offsetHeight,d=c.d==1?c.m-h:h;c.style.height=h+(Math.ceil(d/5)*c.d)+'px';c.style.opacity=h/c.m;c.style.filter='alpha(opacity='+h*100/c.m+')';if((c.d==1&&h>=c.m)||(c.d!=1&&h==1)){if(c.d==1){c.style.height='auto'}clearInterval(c.t)}};return{slider:slider}}();</script><script>var parentAccordion=new TINY.accordion.slider(\"parentAccordion\");parentAccordion.init(\"acc\",\"h3\",0,0);</script>

";
				exit;
			}
		}

		//////////////// Сохранение конфига
		if (isset($_GET['saveconfig'])) {
			if ($_POST['newpassword']!="*****") {
				$pass=trim($_POST['newpassword']);
				$_POST['adminpass']=md5($pass);
			}

			function replacercfg($text) {
				$text=stripslashes($text);
				$text=str_replace("\"", '', $text);
				$text=str_replace("\r\n",'<br>', $text);
				$text=str_replace("\n",'<br>', $text);
				$text=str_replace("\r",'', $text);
				$text=str_replace("\t",' ', $text);
				$text = preg_replace("/\s\s+/", ' ', $text);
				return $text;
			}

			$fdesription=replacercfg($_POST['fdesription']);
			$welcometitle=replacercfg($_POST['welcometitle']);
			$welcometext=replacercfg($_POST['welcometext']);
			$infotitle=replacercfg($_POST['infotitle']);
			$infotext=replacercfg($_POST['infotext']);

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
"$"."fname=\"".$_POST['fname']."\"; //Название форума выводится в title и заголовке\r\n".
"$"."fdesription=\"".$fdesription."\"; //Название и описание выводится в шапке форума\r\n".
"$"."adminname=\"".$_POST['adminname']."\"; //Логин админа\r\n".
"$"."adminpass=\"".$_POST['adminpass']."\"; //Пароль админа зашифрован md5\r\n".
"$"."readonly=\"".$_POST['readonly']."\"; //Заблокировать возможность создавать темы и отвечать в них (1-да, 0-нет)\r\n".
"$"."notopic=\"".$_POST['notopic']."\"; //Заблокировать возможность создавать темы (1-да, 0-нет)\r\n".
"$"."editmsg=\"".$_POST['editmsg']."\"; //Разрешить редактировать свои сообщения (1-да, 0-нет)\r\n".
"$"."timeoutedit=\"".$_POST['timeoutedit']."\"; //Редактировать сообщения нельзя по истечению часов\r\n".
"$"."redsig=\"".str_replace('"','', $_POST['redsig'])."\"; //Подпись о редактировании\r\n".
"$"."topicmail=\"".$_POST['topicmail']."\"; //Отправлять сообщение админу на мыло о новой теме (1-да, 0-нет)\r\n".
"$"."adminmail=\"".$_POST['adminmail']."\"; //E-mail админа, на который будут приходить сообщения\r\n".
"$"."frommail=\"".$_POST['frommail']."\"; //От кого E-mail\r\n".
"$"."antiham=\"".$_POST['antiham']."\"; //Включить систему Антихам (1-да, 0-нет)\r\n".
"$"."stopwrd=\"".$_POST['stopwrd']."\"; //При нахождении стоп-слов: (1) ничего не делать, (2) не пропускать сообщение, (3) заменять, (4) IP в бан-лист\r\n".
"$"."antimat=\"".$_POST['antimat']."\"; //Включить антимат №1 (1-да, 0-нет)\r\n".
"$"."antimatt=\"".$_POST['antimatt']."\"; //Включить антимат №2 (1-да, 0-нет)\r\n".
"$"."badwords=array(\"cy4ka\", \"c y k a\", \"cyka\", \"с у к а\", \"сучка\", \"сука\", \"х=у=й\", \"х_у_и\", \"х_у_й\", \"х-у-й\", \"хуй\", \"хуи\", \"xyй\", \"xyu\", \"x y u\", \"х у й\", \"xyй\", \"x y й\", \"х у и\", \"x y й\", \"пидаp\", \"пидоp\", \"n u д о р\", \"п u д о р\", \"nuдар\", \"nuдор\", \"пuдар\", \"пuд0р\", \"пuдор\", \"nидр\", \"п и д р\", \"пидр\", \"пидар\", \"пидор\", \"nuzde\", \"пизд\", \"nизд\", \"n и 3 д\", \"п и 3 д\", \"пи3д\", \"nи3д\", \"п и з д\", \"6 л я\", \"6лядь\", \"6лять\", \"бляд\", \"блять\", \"б л я т ь\", \"блядь\", \"б л я д ь\", \"ебать\", \"ябать\", \"ебал\", \"ябал\", \"е б л а н\", \"e6лaн\", \"eблан\", \"еблaн\", \"е6лан\", \"еблан\", \"ебан\", \"ёбан\", \"уебан\", \"уёбок\", \"уебок\", \"уёбак\"); //маты\r\n".
"$"."cons=\"".$_POST['cons']."\"; //Маты будут заменяться на это слово\r\n".
"$"."ipinfodb=\"".$_POST['ipinfodb']."\"; //Определять флаг страны и город через www.ipinfodb.com (1-да, 0-нет)\r\n".
"$"."key=\"".$_POST['key']."\"; //Зарегистрируйся www.ipinfodb.com/register.php и впиши сюда ключ\r\n".
"$"."telegramsend=\"".$_POST['telegramsend']."\"; //Включить отправку сообщений в телеграм (1-да, 0-нет)\r\n".
"$"."telegramtoken=\"".$_POST['telegramtoken']."\"; //Телеграм API token\r\n".
"$"."telegramid=\"".$_POST['telegramid']."\"; //Телеграм User ID\r\n".
"$"."gravatar=\"".$_POST['gravatar']."\"; //Включить использование грАватаров (1-да, 0-нет)\r\n".
"$"."avround=\"".$_POST['avround']."\"; //Делать грАватар круглым (1-да, 0-нет)\r\n".
"$"."gravatarsize=\"".$_POST['gravatarsize']."\"; //Размер грАватара (70=70х70px)\r\n".
"$"."max_file_size=\"".$_POST['max_file_size']."\"; //Максимальный размер аватара в байтах\r\n".
"$"."avatar_width=\"".$_POST['avatar_width']."\"; //Максимальная длина аватара в пикселях\r\n".
"$"."avatar_height=\"".$_POST['avatar_height']."\"; //Максимальная высота аватара в пикселях\r\n".
"$"."uq=\"".$_POST['uq']."\"; //По сколько человек выводить список участников\r\n".
"$"."captchamin=\"".$_POST['captchamin']."\"; //Показывать простую капчу (1), или сложную (0). Настройки сложной ниже\r\n".
"$"."captcha=\"".$_POST['captcha']."\"; //Капча показывает буквы и цифры (0), капча показывает только цифры (1)\r\n".
"$"."width=\"".$_POST['width']."\"; //Ширина изображения капчи\r\n".
"$"."height=\"".$_POST['height']."\"; //Высота изображения капчи\r\n".
"$"."font_size=\"".$_POST['font_size']."\"; //Размер шрифта капчи\r\n".
"$"."let_amount=\"".$_POST['let_amount']."\"; //Количество символов капчи, которые нужно ввести\r\n".
"$"."fon_let_amount=\"".$_POST['fon_let_amount']."\"; //Количество символов на капче\r\n".
"$"."path_fonts=\"".$_POST['path_fonts']."\"; //Путь к шрифтам для капчи\r\n".
"$"."timezone=\"".$_POST['timezone']."\"; //Часовая поправка, значение от 12 до -12\r\n".
"$"."liteurl=\"".$_POST['liteurl']."\"; //Разрешить делать ссылки активными (1-да, 0-нет)\r\n".
"$"."repaaddmsg=\"".$_POST['repaaddmsg']."\"; //Сколько репутации добавлять за добавление сообщения\r\n".
"$"."repaaddtem=\"".$_POST['repaaddtem']."\"; //Сколько репутации добавлять за добавлении темы\r\n".
"$"."repatimeday=\"".$_POST['repatimeday']."\"; //Сколько дней показывать шильдик с инфой кто менял репу\r\n".
"$"."userstatus=\"".$_POST['userstatus']."\"; //Какой статус давать пользователю при регистрации\r\n".
"$"."maxname=\"".$_POST['maxname']."\"; //Максимальное кол-во символов в имени\r\n".
"$"."maxmail=\"".$_POST['maxmail']."\"; //Максимальное кол-во символов в почт.адресе\r\n".
"$"."maxtopic=\"".$_POST['maxtopic']."\"; //Максимальное кол-во символов в названии темы\r\n".
"$"."maxmsg=\"".$_POST['maxmsg']."\"; //Максимальное кол-во символов в сообщении\r\n".
"$"."topic1=\"".$_POST['topic1']."\"; //Названия иконок тем\r\n".
"$"."topic2=\"".$_POST['topic2']."\";\r\n".
"$"."topic3=\"".$_POST['topic3']."\";\r\n".
"$"."topic4=\"".$_POST['topic4']."\";\r\n".
"$"."topic5=\"".$_POST['topic5']."\";\r\n".
"$"."topic6=\"".$_POST['topic6']."\";\r\n".
"$"."topic7=\"".$_POST['topic7']."\";\r\n".
"$"."topic8=\"".$_POST['topic8']."\";\r\n".
"$"."topic9=\"".$_POST['topic9']."\";\r\n".
"$"."topic10=\"".$_POST['topic10']."\";\r\n".
"$"."topic11=\"".$_POST['topic11']."\";\r\n".
"$"."topic12=\"".$_POST['topic12']."\";\r\n".
"$"."topic13=\"".$_POST['topic13']."\";\r\n".
"$"."topic14=\"".$_POST['topic14']."\";\r\n".
"$"."topic15=\"".$_POST['topic15']."\";\r\n".
"$"."lastmess=\"".$_POST['lastmess']."\"; //Включить сохранение последних сообщений (1-да, 0-нет)\r\n".
"$"."lastlines=\"".$_POST['lastlines']."\"; //Сколько последних сообщений сохранять\r\n".
"$"."rankline=\"".$_POST['rankline']."\"; //Показывать картинки-статусы под аватаром\r\n".
"$"."nagrada=\"".$_POST['nagrada']."\"; //Показывать картинки-награды под аватаром\r\n".
"$"."stu0=\"".$_POST['stu0']."\"; //Баллы репутации необходимые для смены статуса\r\n".
"$"."stu1=\"".$_POST['stu1']."\";\r\n".
"$"."stu2=\"".$_POST['stu2']."\";\r\n".
"$"."stu3=\"".$_POST['stu3']."\";\r\n".
"$"."stu4=\"".$_POST['stu4']."\";\r\n".
"$"."stu5=\"".$_POST['stu5']."\";\r\n".
"$"."stu6=\"".$_POST['stu6']."\";\r\n".
"$"."stu7=\"".$_POST['stu7']."\";\r\n".
"$"."stu8=\"".$_POST['stu8']."\";\r\n".
"$"."stu9=\"".$_POST['stu9']."\";\r\n".
"$"."stn0=\"".$_POST['stn0']."\"; //Статусы при накоплении баллов репутации\r\n".
"$"."stn1=\"".$_POST['stn1']."\";\r\n".
"$"."stn2=\"".$_POST['stn2']."\";\r\n".
"$"."stn3=\"".$_POST['stn3']."\";\r\n".
"$"."stn4=\"".$_POST['stn4']."\";\r\n".
"$"."stn5=\"".$_POST['stn5']."\";\r\n".
"$"."stn6=\"".$_POST['stn6']."\";\r\n".
"$"."stn7=\"".$_POST['stn7']."\";\r\n".
"$"."stn8=\"".$_POST['stn8']."\";\r\n".
"$"."stn9=\"".$_POST['stn9']."\";\r\n".
"$"."imgstatus=\"".$_POST['imgstatus']."\"; //Картинки статусов\r\n".
"$"."uploader=\"".$_POST['uploader']."\"; //Разрешить загрузку файлов Uploader (1-да, 0-нет)\r\n".
"$"."max_files=\"".$_POST['max_files']."\"; //Сколько файлов разрешить Uploader\r\n".
"$"."canupfile=\"".$_POST['canupfile']."\"; //Разрешить прикреплять файлы (1-да, 0-нет)\r\n".
"$"."filedir=\"".$_POST['filedir']."\"; //Папка куда загружаются файлы\r\n".
"$"."storagepath=\"".$_POST['storagepath']."\"; //Путь папки куда загружаются файлы\r\n".
"$"."max_upfile_size=\"".$_POST['max_upfile_size']."\"; //Максимальный размер файла в байтах\r\n".
"$"."smwidth=\"".$_POST['smwidth']."\"; //Ширина мини картинки\r\n".
"$"."smheight=\"".$_POST['smheight']."\"; //Высота мини картинки\r\n".
"$"."maxwidth=\"".$_POST['maxwidth']."\"; //Макс ширина картинки\r\n".
"$"."maxheight=\"".$_POST['maxheight']."\"; //Макс высота картинки\r\n".
"$"."fskin=\"".$_POST['fskin']."\"; //Текущий скин форума\r\n".
"$"."ver=\"".$_POST['ver']."\"; //Версия форума\r\n".
"$"."welcome=\"".$_POST['welcome']."\"; //Включить окно, которое появляется в правом верхнем углу (1-да, 0-нет)\r\n".
"$"."welcometitle=\"".str_replace('"','', $welcometitle)."\"; //Заголовок (не используйте двойные кавычки)\r\n".
"$"."welcometext=\"".str_replace('"','', $welcometext)."\"; //Текст блока (не используйте двойные кавычки)\r\n".
"$"."infotitle=\"".str_replace('"','', $infotitle)."\"; //Заголовок панели, появляется при нажатии FAQ (не используйте двойные кавычки)\r\n".
"$"."infotext=\"".str_replace('"','', $infotext)."\"; //Текст панели (не используйте двойные кавычки)\r\n?>";

			$file=file("config.php");
			$fp=fopen("config.php","a+");
			flock ($fp,LOCK_EX); 
			ftruncate ($fp,0);
			fputs($fp,$configdata);
			fflush ($fp);
			flock ($fp,LOCK_UN);
			fclose($fp);
			echo "<meta http-equiv=refresh content='0; url=admin.php?event=config'>";
			exit;
		}
	}
}
?>