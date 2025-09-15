<?php // Последние сообщения Forumin

if (is_file("forum/datan/lastmes.dat"))
{
	$lastmessfile="forum/datan/lastmes.dat";
	$lines=file($lastmessfile);
	$i=count($lines);
	if ($i>=1) {
		$a1=$i-1; $u="-1"; // выводим данные по возрастанию или убыванию
		do {
			$dt=explode("¦", $lines[$a1]);
			$a1--;
			if (isset($dt[3]))
			{
				//$dt[6] = str_replace(array("&gt;", "&lt;", "&quot;", "&amp;"), array(">", "<", "\"", "&"), $dt[6]);
				//$dt[6]=str_replace("\"", "&quot;", $dt[6]);
				$dt[6]=htmlspecialchars($dt[6], ENT_COMPAT, 'cp1251');
				$dt[6]=str_replace(array("[video]", "[/video]"), array("", ""), $dt[6]);
				$dt[6]=str_replace(array("[audio]", "[/audio]"), array("", ""), $dt[6]);
				$dt[6]=str_replace(array("[vimeo]", "[/vimeo]"), array("", ""), $dt[6]);
				$dt[6]=str_replace(array("[dzen]", "[/dzen]"), array("", ""), $dt[6]);
				$dt[6]=str_replace(array("[rutube]", "[/rutube]"), array("", ""), $dt[6]);
				$dt[6]=str_replace(array("[youtube]", "[/youtube]"), array("", ""), $dt[6]);
				$dt[6]=str_replace(array("[ok]", "[/ok]"), array("", ""), $dt[6]);
				$dt[6]=str_replace(array("[telegram]", "[/telegram]"), array("", ""), $dt[6]);
				$dt[6]=str_replace(array("[quote]", "[/quote]"), array("", ""), $dt[6]);
				$dt[6]=str_replace(array("[code]", "[/code]"), array("", ""), $dt[6]);
				$dt[6]=str_replace(array("[hide]", "[/hide]"), array("", ""), $dt[6]);
				$dt[6]=str_replace("[b]","",$dt[6]);
				$dt[6]=str_replace("[/b]","",$dt[6]);
				$dt[6]=str_replace("[i]","",$dt[6]);
				$dt[6]=str_replace("[/i]","",$dt[6]);
				$dt[6]=str_replace("[u]","",$dt[6]);
				$dt[6]=str_replace("[/u]","",$dt[6]);
				$dt[6]=str_replace("[s]","",$dt[6]);
				$dt[6]=str_replace("[/s]","",$dt[6]);
				$dt[6]=str_replace("[big]","",$dt[6]);
				$dt[6]=str_replace("[/big]","",$dt[6]);
				$dt[6]=str_replace("[small]","",$dt[6]);
				$dt[6]=str_replace("[/small]","",$dt[6]);
				$dt[6]=str_replace("[red]","",$dt[6]);
				$dt[6]=str_replace("[/red]","",$dt[6]);
				$dt[6]=str_replace("[blue]","",$dt[6]);
				$dt[6]=str_replace("[/blue]","",$dt[6]);
				$dt[6]=str_replace("[green]","",$dt[6]);
				$dt[6]=str_replace("[/green]","",$dt[6]);
				$dt[6]=str_replace("[orange]","",$dt[6]);
				$dt[6]=str_replace("[/orange]","",$dt[6]);
				$dt[6]=str_replace("[yellow]","",$dt[6]);
				$dt[6]=str_replace("[/yellow]","",$dt[6]);
				$dt[6]=str_replace("[left]","",$dt[6]);
				$dt[6]=str_replace("[/left]","",$dt[6]);
				$dt[6]=str_replace("[center]","",$dt[6]);
				$dt[6]=str_replace("[/center]","",$dt[6]);
				$dt[6]=str_replace("[right]","",$dt[6]);
				$dt[6]=str_replace("[/right]","",$dt[6]);
				$dt[6]=str_replace("[img]","",$dt[6]);
				$dt[6]=str_replace("[/img]","",$dt[6]);

				$dt[6]=str_replace("&amp;lt;", '&lt;', $dt[6]);
				$dt[6]=str_replace("&amp;gt;", '&gt;', $dt[6]);
				$dt[6]=str_replace("&amp;quot;", '&quot;', $dt[6]);
				$dt[6]=str_replace("'", '&apos;', $dt[6]);
				$dt[6]=str_replace("&amp;#092;n", '&#092;n', $dt[6]);
				$dt[6]=str_replace("&amp;#036;", '&#036;', $dt[6]);
				$dt[6]=str_replace("&amp;#124;", '|', $dt[6]);
				$dt[6]=str_replace("&lt;br&gt;", '\r\n', $dt[6]);

				print "&bull; <span class=small>$dt[2]</span> <b><a href=\"forum/index.php?forumid=$dt[3]&page=$dt[5]\" title=\"$dt[6]\r\n\r\nНаписано: $dt[2]\">$dt[4]</a></b> » <!--img src=\"forum/flags/$dt[7]\" align=middle title=\"$dt[8]\"--> <span class=small  title=\"$dt[8]\">$dt[0]</span><br><div style='font-size:6px;line-height:1em;'>&nbsp;</div>";
			}
			$a11=$u; $u11=$a1;
		}
		while($a11 < $u11);
	}
}

?>