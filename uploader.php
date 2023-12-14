<?php

include "config.php";

$dirload = "load/"; // Default directory to upload files
$file_ext = array(".gif", ".jpg", ".jpeg", ".png");

$hst=$_SERVER["HTTP_HOST"];
$self=$_SERVER["PHP_SELF"];
$furl=str_replace('uploader.php', '', "http://$hst$self");

if (!is_dir($dirload)) die ("Error: The directory <b>$dirload</b> doesn't exist!");
if (!is_writeable("$dirload")) die ("Error: The directory <b>$dirload</b> is NOT writable!");

// Функция проверки пользователя
function is_user() {
	$uf=file("datan/usersdat.php");
	for($i=1;$i<sizeof($uf);$i++)
	{
		if ($uf[$i]) {
			$pu=explode('|',$uf[$i]);
			if ($pu[0]==$_COOKIE['cname'] && md5($pu[1])==$_COOKIE['cpassreg'])
			return $pu[0];
		}
	} return 0;
}
$_ = array();
$_['user'] = 0;
$_['user'] = is_user();


if (isset($_POST['upload_form']) && $uploader==1 && $_['user'])
{
	echo "<font size=2 face=arial><h3>Результат загрузки</h3>";

	for ($i=1; $i<= $max_files; $i++) 
	{
		$new_file  = $_FILES['file'.$i];
		$file_name = $new_file['name'];

		// Replace spaces of file name with underscore
		$file_name = str_replace(' ', '_', $file_name);
		$file_tmp = $new_file['tmp_name'];
		$file_size = $new_file['size'];

		// Check file selection
		if (!is_uploaded_file($file_tmp))
		{
			echo "#$i: Не выбран<br><br>";
		} else {
			$ext = strrchr($file_name,'.');
			if (!in_array(strtolower($ext),$file_ext))
			{
				echo "#$i: Загружаемый файл <b>$file_name</b> не является картинкой<br><br>";
			} else {
				if ($file_size > $max_upfile_size)
				{
					echo "#$i: Файл <b>$file_name</b> превышает <b>". $max_upfile_size / 1024000 ."</b> Мб <br><br>";
				} else {
					// Check for existing file
					if (file_exists($dirload.$file_name))
					{
						echo "#$i: Файл <b>$file_name</b> уже есть<br><br>";
					} else {
						if (move_uploaded_file($file_tmp,$dirload.$file_name))
						{
							echo "#$i: <input type=text size=150 style='width:100%' value='[img]".$furl.$dirload.$file_name."[/img]' id='url$i'> <button onclick=\"var copyText=document.getElementById('url$i');copyText.select();document.execCommand('copy')\">Копировать в буфер</button><br><br>";
						} else {
							echo "#$i: Ошибка загрузки!<br><br>";
						}
					}
				}
			}
		}
	}
	echo "<br><br><center>[<a href='./uploader.php'>Загрузить ещё</a>] &nbsp; &nbsp; &nbsp; [<a href='' onClick='self.close()'>Закрыть окно</a>]</center><br>";

} else {

	if ($uploader==0) print "<center><br><br><br><br><br><font size=2 face=tahoma><b>Загрузка файлов отключена!</b><br><br><br>[<a href='' onClick='self.close()'>Закрыть окно</a>]</center></font>";

	if ($uploader==1 && $_['user'])
	{
		echo "<font size=2 face=arial>Макс. размер файла ". $max_upfile_size / 1024000 ." Мб";
		echo "<br><br><form method='post' action=\"$_SERVER[PHP_SELF]\" enctype='multipart/form-data'>";

		for ($i = 1; $i <= $max_files; $i++) {echo "#$i: <input type=\"file\" name=\"file". $i ."\"><br><br>";}

		echo "<input type='hidden' name='MAX_FILE_SIZE' value='$max_upfile_size'><input type='submit' name='upload_form' value='Загрузить'></form><br><center>[<a href='' onClick='self.close()'>Закрыть окно</a>]</center><br></font>";
	}
}
?>