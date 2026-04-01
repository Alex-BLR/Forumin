<?php

////////////////// Шаблон ОШИБКА
function show_error($message, $back_url = 'index.php?mode=reg')
{
	exit("
		<div style='display: flex; flex-direction: column; align-items: center; justify-content: center; margin-top: 100px; font-size: 13px; font-weight: bold; font-family: tahoma; min-height: 50vh;'>
			<fieldset style='width: 400px; border: 1px solid #333; padding: 20px;'>
				<legend style='text-align: center; color: red; font-weight: bold; font-size: 13px; padding: 0 10px;'>ОШИБКА</legend>
				<div style='text-align: center; font-weight: bold; font-size: 13px;'>$message</div>
			</fieldset>
			<a href='$back_url' style='text-decoration: none; margin-top: 30px;'>&#9668; назад</a>
		</div>
	");
}


////////////////// Определение IP
function getIP() {
	//По умолчанию берем самый надежный адрес
	$ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';

	//Если сервер за прокси, проверяем X-Forwarded-For
	if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		$parts = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']); //Берем первый IP из списка (клиентский)
		$tmp_ip = trim($parts[0]);

		//Валидация формата IP
		if (filter_var($tmp_ip, FILTER_VALIDATE_IP)) $ip = $tmp_ip;
	}
	return $ip;
}

////////////////// Генерация thumbnails
//$src - исходный файл
//$dest - генерируемый файл
//$width, $height - ширина и высота генерируемого изображения, пикселей
//$size - текущие размеры
//$quality - качество JPEG

function img_resize($src, $dest, $width, $height, $size, $name, $quality=92)
{
	global $smwidth;
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

	if($width>$smwidth) // выводим надпись
	{
		function _Kiril_latin($path)
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

	if ($size = @getimagesize($bigimgsrc))
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
	global $smwidth;

	if (ini_get('allow_url_fopen') && ($size = @getimagesize($imlink)) !== FALSE)
	{
		if ($size[0] <= $smwidth)
		{
			$imgtag="<img src=\"$imlink\" border=\"0\"> ";
		} else {
			$imgtag="<img src=\"index.php?mode=link&img=$imlink\" border=\"0\" style=\"border: 1px solid #ddd; cursor:pointer\" onclick=\"TINY.box.show({image:'$imlink',boxid:'frameless',animate:true})\">";
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
	//$text=str_replace("¦", '', $text);
	$text=str_replace("&#032;", ' ', $text);
	$text=str_replace(">", '&gt;', $text);
	$text=str_replace("<", '&lt;', $text);
	$text=str_replace("\"", '&quot;', $text);
	$text=str_replace("'", '&apos;', $text);
	$text=preg_replace("/\\\$/", '&#036;', $text);
	$text=preg_replace("/\\\/", '&#092;', $text);
	if (get_magic_quotes_gpc()) {
		$text=str_replace("&#092;&quot;", '&quot;', $text);
		$text=str_replace("&#092;'", '\'', $text);
		$text=str_replace("&#092;&#092;", '&#092;', $text);
	}
 	$text=str_replace("\r\n", '<br>', $text);
	$text=str_replace("\n", '<br>', $text);
	$text=str_replace("\t", ' ', $text);
	$text=str_replace("\r", '', $text);
	$text = preg_replace("/\s\s+/", ' ', $text);
	return $text;
}

////////////////// Функция кнопки Hide
function hideguest($hide)
{
	global $user;
	if ($user)
	{
		$hide="<br><br><fieldset style='width:95%;border:dotted 1px #777;'><legend align=left class=med>Текст скрыт от гостей</legend>$hide</fieldset><br>";
		return $hide;
	} else {
		$hide="<br><br><fieldset style='width:95%;border:dotted 1px #777;'><legend align=left class=med>Скрыто от гостей</legend><i>Только зарегистрированные пользователи могут видеть этот текст!</i></div></fieldset><br>";
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
function is_user()
{
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

////////////////// <br> в \n
function br2n($text)
{
	$text=str_replace("<br>", '
', $text);
	$text=str_replace("<br />", '
', $text);
	return $text;
}

////////////////// \n в <br>
function n2br($text)
{
	$text=str_replace("\r", '', $text);
	$text=str_replace("\n", '<br>', $text);
	return $text;
}

/////////////// Функция для отображения аватаров
function get_dir($path = './', $mask = '*.php', $mode = GLOB_NOSORT)
{
	if (version_compare(phpversion(), '4.3.0', '>='))	{
		if (chdir($path)) {$temp=glob($mask,$mode); return $temp;}
	} return false;
}

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
function remBadWordsA($text) {
	global $badwords, $cons;
	$mat=count($badwords);
	for ($i=0; $i<$mat; $i++) {
		$text=preg_replace("/".$badwords[$i]."/is", $cons, $text);
	}
	return $text;
}

////////////////// Антимат 2
function remBadWordsB($text) {
	global $cons;
	$pattern=('/(
		(?:\s+|^)(?:[пПnрРp]?[3ЗзВBвПnпрРpPАaAаОoO0о]?[сСcCиИuUОoO0оАaAаыЫуУyтТT]?|\w*[оаАaAО0oO])[Ппn][иИuUeEеЕ][зЗ3][ДдDd]\w*[\?\,\.\!\;\-]*|
		(?:\s+|^)\w{0,4}[оОoO0иИuUаАaAcCсСзЗ3тТTуУy]?[XxХх][уУy][йЙеЕeёЁEeяЯ9юЮиИuU]\w*[\?\,\.\;\-\!]*|
		(?:\s+|^)[бпПnБ6][лЛ][яЯ9]+(?:[дтДТDT]\w*)?[\?\,\.\;\!\-]*|
		(?:\s+|^)\w*[бпПnБ6][лЛ][яЯ9][дтДТDT]\w+[\?\,\.\;\-\!]*|
		(?:\s+|^)(?:\w*[оОoO0ъЪьыЫЬаАaAзЗ3уУyеЕe])?[еЕeEиИuUёЁ][бБ6пП](?:[оОoO0ыЫаАaAнНHиИuUуУyлЛеЕeкКkKE]\w*)?[\?\,\!\.\;\-]*|
		(?:\s*|^)?[ШшЩщ][лЛ][юЮ][хХxX]?[шШщЩ]?[кКkK]?\w*[\?\,\!\.\;\-]*|
		(?:\s*|^)?[сСcC][цЦ]?[уyУ]+[чЧ]?[КkKк]\w*[\?\,\!\.\;\-]*|
		(?:\s*|^)?[пПn][uUИи][Дд][aAАаоОoO0][Рpр]\w*[\?\,\!\.\;\-]*|
		(?:\s*|^)?[гГ][ОoOоаАaA][НHн][Дд][oOО0о][нНH]\w*[\?\,\!\.\;\-]*|
		(?:\s*|^)?\w*[3Зз][аАaAоОoO0][лK][уyУ][пПn]\w*[\?\,\!\.\;\-]*)/x');
	$text = preg_replace("$pattern", "$cons", $text);
	return $text;
}

////////////////// 
function codemsg($text) {
	$text=str_replace(array("[b]", "[/b]", "[i]", "[/i]", "[u]", "[/u]", "[s]", "[/s]", "[big]", "[/big]", "[small]", "[/small]", "[red]", "[/red]", "[blue]", "[/blue]", "[green]", "[/green]", "[orange]", "[/orange]", "[yellow]", "[/yellow]", "[left]", "[/left]", "[center]", "[/center]", "[right]", "[/right]", "[img]", "[/img]", "[video]", "[/video]", "[audio]", "[/audio]", "[vimeo]", "[/vimeo]", "[dzen]", "[/dzen]", "[rutube]", "[/rutube]", "[youtube]", "[/youtube]", "[ok]", "[/ok]", "[telegram]", "[/telegram]", "[map]", "[/map]", "[quote]", "[/quote]", "[code]", "[/code]", "[hide]", "[/hide]"), "", $text);
 	$text=str_replace("\r\n", ' ', $text);
	$text=str_replace("\n", ' ', $text);
	$text=str_replace("\t", ' ', $text);
	$text=str_replace("\r", ' ', $text);
	$text = str_replace(array("+", "&", "№", "#", "\"", " ", "А", "а", "Б", "б", "В", "в", "Г", "г", "Д", "д", "Е", "е", "Ё", "ё", "Ж", "ж", "З", "з", "И", "и", "Й", "й", "К", "к", "Л", "л", "М", "м", "Н", "н", "О", "о","П", "п", "Р", "р", "С", "с", "Т", "т", "У", "у", "Ф", "ф", "Х", "х", "Ц", "ц", "Ч", "ч", "Ш", "ш", "Щ", "щ", "Ъ", "ъ", "Ы", "ы", "Ь", "ь", "Э","э", "Ю", "ю", "Я", "я"), array("%2B", "%26", "%23", "%23", "%22", "%20", "%D0%90", "%D0%B0", "%D0%91", "%D0%B1", "%D0%92", "%D0%B2", "%D0%93", "%D0%B3", "%D0%94", "%D0%B4", "%D0%95", "%D0%B5", "%D0%81", "%D1%91", "%D0%96", "%D0%B6", "%D0%97", "%D0%B7", "%D0%98", "%D0%B8", "%D0%99", "%D0%B9", "%D0%9A", "%D0%BA", "%D0%9B", "%D0%BB", "%D0%9C", "%D0%BC", "%D0%9D", "%D0%BD", "%D0%9E", "%D0%BE", "%D0%9F", "%D0%BF", "%D0%A0", "%D1%80", "%D0%A1", "%D1%81", "%D0%A2", "%D1%82", "%D0%A3", "%D1%83", "%D0%A4", "%D1%84", "%D0%A5", "%D1%85", "%D0%A6", "%D1%86", "%D0%A7", "%D1%87", "%D0%A8", "%D1%88", "%D0%A9", "%D1%89", "%D0%AA", "%D1%8A", "%D0%AB", "%D1%8B", "%D0%AC", "%D1%8C", "%D0%AD", "%D1%8D", "%D0%AE", "%D1%8E", "%D0%AF", "%D1%8F"), $text);
	return $text;
}

?>