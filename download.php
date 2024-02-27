<?php
/*******************************************************
LP Download Counter v1.0 release (v1.0.3)
© 2004 Leviathan Poductions Media http://www.lpmedia.tk
© 2004 Chris Archer http://www.gsinclair.nm.ru

Usage:
download.php?file=file.ext (Download file.ext)

download.php?filecnt=file.ext
HTML: <script src="http://site.ru/download.php?filecnt=file.zip"></script>
Genrate JavaScript file, that inserts number of downloads file.ext

License Agreement:
This program is free software;  you can redistribute it and/or modify
it  under the terms  of  the  GNU  Lesser  General  Public  License  as
published by the Free Software Foundation; either version 2.1 of the
License, or (at your option) any later version.
*******************************************************/
//error_reporting (E_ALL);

include "config.php";

$data="$filedir/download.dat"; // Файл статистики
$jscript="application/javascript"; // JavaScript MIME Type Name
$binary="application/octet-stream"; // Binary file MIME Type


////////////////// Дата, время
$date=gmdate('d.m.Y',time()+3600*($timezone+(date('I')==1?0:1)));
$time=gmdate('H:i:s',time()+3600*($timezone+(date('I')==1?0:1)));

////////////////// Чистка
function replacer($text) {
	$text=str_replace("|", '', $text);
	$text=str_replace(">", '', $text);
	$text=str_replace("<", '', $text);
	$text=str_replace("\"", '', $text);
	$text=str_replace("'", '', $text);
	$text=str_replace("`", '', $text);
	$text=str_replace("\n", '', $text);
	$text=str_replace("\r", '', $text);
	$text=str_replace("\t", '', $text);
	$text=str_replace("\r\n", '', $text);
	$text=preg_replace("/\n/", '', $text);
	$text=preg_replace("/\r/", '', $text);
	//$text=stripslashes($text);
	return $text;
}

////////////////// Определение IP
function getIpAddress() {
	$check = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR');
	$rip = '0.0.0.0';
	foreach ($check as $akey) {
		if (isset($_SERVER[$akey])) {
			list($rip) = explode(',', $_SERVER[$akey]);
			break;
		}
	}
	return $rip;
}
$realip = getIpAddress();
$host = replacer(gethostbyaddr("$realip"));

$hr = replacer($_SERVER['HTTP_REFERER']);
$hua = replacer($_SERVER['HTTP_USER_AGENT']);



//////////////////////////////// Downloader
if (isset($_GET['file']))
{
	$filename=replacer($_GET['file']);

	if (file_exists("$filedir/$filename"))
	{
		// Leave script if writing is impossible
		if (!is_file($data) || !is_readable($data) || !is_writeable($data)) {ErrMess("Can't write file <b>$data</b>");}

		// Increase counter
		$read=fopen($data,"r") or ErrMess("Can't open file $data");
		$file_change=file("$data");
		fclose($read);

		$i=0;
		$chdone=0;
		while(isset($file_change[$i]))
		{
			$tmpstr=$file_change[$i];
			$tmpfn=strtok($tmpstr,"|");
			$filestat=(int)strtok("|");

			if ($filename==$tmpfn)
			{
				$filestat++;
				$file_change[$i]="$filename|$filestat|$date в $time|$realip|$host|$hr|$hua\r\n";
				$chdone=1;
				break;
			}
			$i++;
		}
		if (!$chdone) {$file_change[$i]="$filename|1\r\n";}

		$write=fopen($data,"w") or ErrMess("Can't open file $data");
		flock($write,2);
		$i=0;
		while(isset($file_change[$i]))
		{
			fputs($write,$file_change[$i]);
			$i++;
		}
		flock($write,3);
		fclose($write);

		header("Content-type: $binary");
		header("Location: $storagepath/$filename");
		exit;
	}
}

//////////////////////////////// Counter
if (isset($_GET['filecnt']))
{
	$filename=replacer($_GET['filecnt']);

	header("Content-type: $jscript");

	// Leave script if reading is impossible
	if (!is_file($data) || !is_readable($data))
	{
		echo("document.write('{error reading counter data}');");
		exit;
	}

	// Reading data file
	$read=fopen($data,"r") or die("document.write('{error reading counter data}');");
	$file_change=file("$data");
	fclose($read);

	// Seeking file stat
	$i=0;
	$found=0;
	while(isset($file_change[$i]))
	{
		$tmpstr=$file_change[$i];
		$tmpfn=strtok($tmpstr,"|");
		$filestat = (int)strtok("|");

		if ($filename==$tmpfn)
		{
			$found=1;
			echo("document.write('$filestat');");
			break;
		}
		$i++;
	}
	if (!$found) {echo("document.write('0');");}
	exit;
}

ErrMess("No files to download!");

// Use $mess to place error message text
function ErrMess($mess) {die("<h3>Downloader Error</h3><p>$mess</p><i>LP Download Counter © 2004 <a href=\"http://lpmedia.tk\" target=\"_blank\">Leviathan Productions Media</a></i>");}

?>
