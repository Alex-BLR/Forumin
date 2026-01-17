<?php //Последние сообщения Forumin

if (is_file("forum/datan/lastmes.dat")) {
	$lastmessfile="forum/datan/lastmes.dat";
	$lines=file($lastmessfile);
	$i=count($lines);
	if ($i>=1) {
		$a1=$i-1; $u="-1"; //Выводим данные по возрастанию или убыванию
		do {
			$dt=explode("¦", $lines[$a1]);
			$a1--;
			if (isset($dt[3])) {
				//$dt[6]=str_replace(array("&gt;", "&lt;", "&quot;", "&amp;"), array(">", "<", "\"", "&"), $dt[6]);
				//$dt[6]=str_replace("\"", "&quot;", $dt[6]);
				$dt[6]=htmlspecialchars($dt[6], ENT_COMPAT, 'cp1251');

				$dt[6]=str_replace(array("[video]", "[/video]"), "", $dt[6]);
				$dt[6]=str_replace(array("[audio]", "[/audio]"), "", $dt[6]);
				$dt[6]=str_replace(array("[vimeo]", "[/vimeo]"), "", $dt[6]);
				$dt[6]=str_replace(array("[dzen]", "[/dzen]"), "", $dt[6]);
				$dt[6]=str_replace(array("[rutube]", "[/rutube]"), "", $dt[6]);
				$dt[6]=str_replace(array("[youtube]", "[/youtube]"), "", $dt[6]);
				$dt[6]=str_replace(array("[ok]", "[/ok]"), "", $dt[6]);
				$dt[6]=str_replace(array("[telegram]", "[/telegram]"), "", $dt[6]);
				$dt[6]=str_replace(array("[map]", "[/map]"), "", $dt[6]);
				$dt[6]=str_replace(array("[quote]", "[/quote]"), "[цитата]", $dt[6]);
				$dt[6]=str_replace(array("[code]", "[/code]"), "[код]", $dt[6]);
				$dt[6]=str_replace(array("[hide]", "[/hide]"), "", $dt[6]);

				$dt[6]=str_replace(array("[b]", "[/b]"), "", $dt[6]);
				$dt[6]=str_replace(array("[i]", "[/i]"), "", $dt[6]);
				$dt[6]=str_replace(array("[u]", "[/u]"), "", $dt[6]);
				$dt[6]=str_replace(array("[s]", "[/s]"), "", $dt[6]);

				$dt[6]=str_replace(array("[big]", "[/big]"), "", $dt[6]);
				$dt[6]=str_replace(array("[small]", "[/small]"), "", $dt[6]);

				$dt[6]=str_replace(array("[red]", "[/red]"), "", $dt[6]);
				$dt[6]=str_replace(array("[blue]", "[/blue]"), "", $dt[6]);
				$dt[6]=str_replace(array("[green]", "[/green]"), "", $dt[6]);
				$dt[6]=str_replace(array("[orange]", "[/orange]"), "", $dt[6]);
				$dt[6]=str_replace(array("[yellow]", "[/yellow]"), "", $dt[6]);

				$dt[6]=str_replace(array("[left]", "[/left]"), "", $dt[6]);
				$dt[6]=str_replace(array("[center]", "[/center]"), "", $dt[6]);
				$dt[6]=str_replace(array("[right]", "[/right]"), "", $dt[6]);
				$dt[6]=str_replace(array("[img]", "[/img]"), "", $dt[6]);

				$dt[6]=str_replace("&amp;lt;","&lt;", $dt[6]);
				$dt[6]=str_replace("&amp;gt;","&gt;", $dt[6]);
				$dt[6]=str_replace("&amp;quot;","&quot;", $dt[6]);
				$dt[6]=str_replace("&amp;#092;n","&#092;n",$dt[6]);
				$dt[6]=str_replace("&amp;#036;","&#036;", $dt[6]);
				$dt[6]=str_replace("&amp;#124;","|",$dt[6]);
				$dt[6]=str_replace("&lt;br&gt;","\r\n", $dt[6]);

				print "<div style='margin: 6px 0px;'>&bull; <span class=small>$dt[2]</span> <b><a href=\"forum/index.php?forumid=$dt[3]&page=$dt[5]\" title=\"$dt[6]\n\nНаписано: $dt[2]\">$dt[4]</a></b> » <!--img src=\"forum/flags/$dt[7]\" align=middle title=\"$dt[8]\"--><span class=small title=\"$dt[8]\">$dt[0]</span></div>";
			}
			$a11=$u; $u11=$a1;
		}
		while($a11 < $u11);
	}
}
?>