<?php
/*******************************************************
LP Download Counter v1.0 release (v1.0.3)
© 2004 Leviathan Poductions Media http://www.lpmedia.tk
© 2004 Chris Archer http://www.gsinclair.nm.ru

Usage:
download.php?file=filename.ext   (Downloads file filename.ext)

download.php?filecnt=filename.ext
HTML: <script src="http://site.ru/download.php?filecnt=file.zip"></script>
Genrate JavaScript file, that inserts number of downloads of file filename.ext

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

if (isset($_GET['file']))
{
	$filename=$_GET['file'];

	if (!is_file($data) || !is_readable($data) || !is_writeable($data)) {ErrMess("Can't write to data file<br><b>$data</b>");}

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
			$file_change[$i]="$filename|$filestat\r\n";
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

if (isset($_GET['filecnt']))
{
	$filename=$_GET['filecnt'];
	header("Content-type: $jscript");

	if (!is_file($data) || !is_readable($data))
	{
		echo("document.write('{error reading counter data}');");
		exit;
	}
	$read=fopen($data,"r") or die("document.write('{error reading counter data}');");
	$file_change=file("$data");
	fclose($read);

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

// if variables are not set
ErrMess("No files to download!");

// Use $mess to place error message text
function ErrMess($mess) {die("<h3>Downloader Error</h3><p>$mess</p><i>LP Download Counter © 2004 <a href=\"http://www.lpmedia.tk\" target=\"_blank\">Leviathan Productions Media</a></i>");}

?>