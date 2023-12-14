function answerGuest() {
	obj_form=document.forms.Guest;
	obj_pole_name=obj_form.name;
	obj_pole_email=obj_form.email;
	obj_pole_msg=obj_form.msg;

	if (obj_pole_name.value=='') {alert('Введите свое имя!'); return;}

	if (obj_pole_email.value=='') {alert('Введите свой E-mail адрес!'); return;}
	if (obj_pole_email.value.indexOf('@')==-1) {alert('Введите корректный E-mail адрес!'); return;}
	if (obj_pole_email.value.indexOf('.')==-1) {alert('Повторяю, введите корректный E-mail адрес!'); return;}

	textMsg=obj_pole_msg.value;
	if (textMsg=='') {alert('Введите свое сообщение!'); return;}
	if (textMsg.length<2) {alert('Длина сообщения должна быть не менее 2-х символов'); return;}

	obj_form.submit();
}

function regGuest() {
	obj_form=document.forms.Guest;
	obj_pole_name=obj_form.name;
	obj_pole_mail=obj_form.mail;
	obj_pole_topic=obj_form.topic;

	if (obj_pole_name.value=='') {alert('Введите свое имя!'); return;}

	txt=obj_pole_mail.value;

	if (txt=='') {alert('Введите свой E-mail адрес!'); return;}
	if (txt.indexOf('@')==-1) {alert('Введите корректный E-mail адрес!'); return;}
	if (txt.indexOf('.')==-1) {alert('Повторяю, введите корректный E-mail адрес!'); return;}

	textTopic=obj_pole_topic.value;
	if (textTopic=='') {alert('Введите название темы!'); return;}
	if (textTopic.length<2) {alert('Длина темы должна быть не менее 2-х символов'); return;}

	obj_form.submit();
}


function ins(name) {
	var input=document.forms[0].msg;input.value=input.value+"[b]"+name+"[/b]"+" ";
}

function q(text) {
	var input=document.forms[0].msg;input.value=input.value+"[quote]"+text+"[/quote]"+"\n";
}

function insbb(openb, closeb) {
	if (document.selection) {
		var tmp;
		document.REPLIER.msg.focus();
		tmp = document.REPLIER.document.selection.createRange().text;
		document.REPLIER.document.selection.createRange().text = openb + tmp + closeb;
	} else {
		var messageField = document.REPLIER.msg;
		var selStart = messageField.selectionStart;
		var selEnd = messageField.selectionEnd;
		var MsgVal = messageField.value;
		var text = new String();
		var step = new Number();
		if (selStart || messageField.selectionStart == '0') {
			step = openb.length + closeb.length;
			text = MsgVal.substring(0, selStart) + openb;
			text += MsgVal.substring(selStart, selEnd) + closeb;
			text += MsgVal.substring(selEnd, MsgVal.length);
			messageField.value = text;
			messageField.selectionStart = selStart;
			messageField.selectionEnd = selEnd + step;
		} else
		document.REPLIER.msg.value += openb + closeb;
	}
	document.REPLIER.msg.focus();
}

function instxt(txt) {
	/* if (document.selection) {
		document.sf.document.selection.createRange().text = txt;
	} else
	document.sf.msg.value += txt;*/
	insbb("", txt);
}

function hTextarea(area) {
	if (document.getElementById(area).style.height=='') {
		document.getElementById(area).style.height='300px';
	} else {
		var pattern = new RegExp("\\d+",'ig');
		var currHeight = document.getElementById(area).style.height.match(pattern);
		var newHeight = +currHeight + 100;
		document.getElementById(area).style.height = newHeight + 'px';
	}
}

function wTextarea(area) {
	if (document.getElementById(area).style.width=='') {
		document.getElementById(area).style.width='200px';
	} else {
		var pattern = new RegExp("\\d+",'ig');
		var currWidth = document.getElementById(area).style.width.match(pattern);
		var newWidth = +currWidth + 100;
		document.getElementById(area).style.width = newWidth + 'px';
	}
}

function wTextareaB(area) {
	if (document.getElementById(area).style.width=='') {
		document.getElementById(area).style.width='200px';
	} else {
		var pattern = new RegExp("\\d+",'ig');
		var currWidth = document.getElementById(area).style.width.match(pattern);
		var newWidth = +currWidth - 100;
		document.getElementById(area).style.width = newWidth + 'px';
	}
}



function welcome() {
	var welcome = document.getElementById('welcome');
	welcome.style.display = 'block';
	var welcome_time = 6000;
	setTimeout(unwelcome, welcome_time);
}
var welcome_opacity = 0.9;

function unwelcome() {
	welcome_opacity -= 0.03;
	var welcome = document.getElementById('welcome');
	if (welcome_opacity > 0) {
		welcome.style.opacity = '' + welcome_opacity.toFixed(2);
		setTimeout(unwelcome, 60);
	} else {
		welcome.style.display = 'none';
	}
}


function toggleStats() {
	if (document.getElementById("stats").style.display == "block") {
		document.getElementById("stats").style.display = "none";
	} else {
		document.getElementById("stats").style.display = "block";
	}
}






function seeTextArea(form) {
	myWin = open("#", "displayWindow", "width=800,height=600,status=1,toolbar=1,menubar=1,resizable=1,border=0,scrollbars=1");
	myWin.document.open();
	myWin.document.write("<html><head><title>Предварительный просмотр</title>");
	myWin.document.write("<meta http-equiv='Content-Type' content='text/html; charset=windows-1251'>");
	myWin.document.write("<link type='text/css' rel='stylesheet' href='images-amBase/style5.css'></head>");
	myWin.document.write("<body><table width=100% height=100% border=0><tr valign=top><td>");
	myWin.document.write(FilterScript(form.msg.value));
	myWin.document.write("</td></tr><tr><td height=20 align=center><b><a href='#' onClick='self.close()' style='font-family:tahoma;font-size:14px;'>Закрыть окно</a></b></td></tr></body></html>");
	myWin.document.close();
}

function FilterScript(str) {
	str=str.replace(/\[b\]/ig,"<b>");
	str=str.replace(/\[\/b\]/ig,"</b>");
	str=str.replace(/\[i\]/ig,"<i>");
	str=str.replace(/\[\/i\]/ig,"</i>");
	str=str.replace(/\[u\]/ig,"<u>");
	str=str.replace(/\[\/u\]/ig,"</u>");
	str=str.replace(/\[s\]/ig,"<s>");
	str=str.replace(/\[\/s\]/ig,"</s>");
	str=str.replace(/\[big\]/ig,"<big>");
	str=str.replace(/\[\/big\]/ig,"</big>");
	str=str.replace(/\[small\]/ig,"<small>");
	str=str.replace(/\[\/small\]/ig,"</small>");
	str=str.replace(/\[red\]/ig,"<font color=red><B>");
	str=str.replace(/\[\/red\]/ig,"</B></font>");
	str=str.replace(/\[blue\]/ig,"<font color=blue><B>");
	str=str.replace(/\[\/blue\]/ig,"</B></font>");
	str=str.replace(/\[green\]/ig,"<font color=green><B>");
	str=str.replace(/\[\/green\]/ig,"</B></font>");
	str=str.replace(/\[orange\]/ig,"<font color=#ff8000><B>");
	str=str.replace(/\[\/orange\]/ig,"</B></font>");
	str=str.replace(/\[yellow\]/ig,"<font color=yellow><B>");
	str=str.replace(/\[\/yellow\]/ig,"</B></font>");
	str=str.replace(/\[left\]/ig,"<div align=left>");
	str=str.replace(/\[\/left\]/ig,"</div>");
	str=str.replace(/\[center\]/ig,"<div align=center>");
	str=str.replace(/\[\/center\]/ig,"</div>");
	str=str.replace(/\[right\]/ig,"<div align=right>");
	str=str.replace(/\[\/right\]/ig,"</div>");

	str=str.replace(/\[hide=(.+?)\](.+?)\[\/hide\]/ig,"<span class=med style='background-color:#ddd;font-style:italic'><b>Шёпотом</b> для <b>$1</b>: $2&nbsp;</span>");
	str=str.replace(/\[hide\]/ig,"<fieldset align=center style='width:90%;border:solid 1px #777'><div style='PADDING-LEFT:6px;PADDING-BOTTOM:2px;' align=left><legend align=left class=med>Скрыто от гостей</legend>");
	str=str.replace(/\[\/hide\]/ig,"</div></fieldset>");

	str=str.replace(/\[spoiler\]/ig,"<div style='margin:10px;margin-top:5px'><div style='margin-bottom:2px'><small>Скрытый текст</small> <input type='button' class='button' value='Показать' style='width:60px;font-size:10px;margin:0px;padding:0px;BORDER:1px solid;cursor:hand' onClick=\"if(this.parentNode.parentNode.getElementsByTagName('div')[1].getElementsByTagName('div')[0].style.display !=''){this.parentNode.parentNode.getElementsByTagName('div')[1].getElementsByTagName('div')[0].style.display=''; this.innerText=''; this.value='Скрыть';} else {this.parentNode.parentNode.getElementsByTagName('div')[1].getElementsByTagName('div')[0].style.display='none'; this.innerText=''; this.value='Показать';}\"></div><div style='margin:0px;padding:3px;border:solid 0px #090909;background-color:#ddd;font-size:11px'><div style='display:none'>");
	str=str.replace(/\[\/spoiler\]/ig,"</div></div></div>");

	str=str.replace(/\[quote\]/ig,"<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<B><U><small>Цитата:</small></U></B><table width=95% border=0 cellpadding=3 cellspacing=1 style='margin-left:18px;padding:5px;margin-top:1px'><tr><td class=q>");
	str=str.replace(/\[\/quote\]/ig,"</td></tr></table>");

	str=str.replace(/\[code\]/ig,"<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<B><U><small>Код:</small></U></B><table width=95% border=0 cellpadding=3 cellspacing=1 style='margin-left:18px;padding:5px;margin-top:1px'><tr><td class=code>");
	str=str.replace(/\[\/code\]/ig,"</td></tr></table>");

	str=str.replace(/\[html\]/ig,"<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<B><U><small>Код:</small></U></B><br><textarea rows=6 wrap=on readonly style=\"width:97%;margin-left:18px;padding:5px;margin-top:1px\" class=code>");
	str=str.replace(/\[\/html\]/ig,"</textarea>");

	str=str.replace(/\[youtube\]https?:\/\/(?:[a-z\d-]+\.)?youtu(?:be(?:-nocookie)?\.com\/.*v=|\.be\/)([-\w]{11})(?:.*[\?&#](?:star)?t=([\dhms]+))?\[\/youtube\]/ig, "<br><object width=640px height=480px><param name=movie value=\"http://www.youtube.com/v/$1\"></param><param name=allowFullScreen value=true></param><param name=allowscriptaccess value=always></param><embed src=\"https://www.youtube.com/v/$1\" type=\"application/x-shockwave-flash\" allowscriptaccess=always allowfullscreen=true width=640px height=480px></embed></object><br>");

	str=str.replace(/(\[audio\])(.+?)(\[\/audio\])/ig, "<br><audio src=\"$2?autoplay=false\" type=\"audio/mp3\" controls></audio><br>");

	str=str.replace(/(\[video\])(.+?)(\[\/video\])/ig,"<br><video width=640 height=480 controls><source src=\"$2?autoplay=false\" type=\"video/mp4\"></video><br>");
	str=str.replace(/(\[video=)(\S+?)(\,)(.+?)(\])(.+?)(\.flv|\.mp4|\.wmv|\.avi|\.mpg|\.mpeg)(\[\/video\])/ig,"<br><video width=\"$2\" height=\"$4\" controls><source src=\"$6$7\" autoplay=false type=\"video/mp4\"></video><br>");

	str=str.replace(/(\[video\])(.+?)(\[\/video\])/ig, "<br><video width=640 height=480 controls><source src=\"$2?autoplay=false\" type=\"video/mp4\"></video><br>");
	str=str.replace(/(\[video=)(\S+?)(\,)(.+?)(\])(.+?)(\.flv|\.mp4|\.wmv|\.avi|\.mpg|\.mpeg)(\[\/video\])/ig, "<br><video width=\"$2\" height=\"$4\" controls><source src=\"$6$7\" autoplay=false type=\"video/mp4\"></video><br>");

	str=str.replace(/\[img\](.+?)\[\/img\]/ig,"<img src=\"$1\" border=0>");
	str=str.replace(/([\s>\]]+)www\.([\w\-\.,@?^=%&:;\/~\+#]*[\w\-\@?^=%&:;\/~\+#])/ig,"$1http://www.$2");
	str=str.replace(/([\s>\]]+)((http|ftp)+(s)?:(\/\/)([\w]+(.[\w]+))([\w\-\.,@?^=%&:;\/~\+#]*[\w\-\@?^=%&:;\/~\+#])?)/ig,"$1<a href=\"$2\" target=\"_blank\">$2</a>");

//	str=str.replace(/>/g, "&gt;");
//	str=str.replace(/</g, "&lt;");

	str=str.replace(/\n/g, "<br>");
	str=str.replace(/\r/g, "");
	str=str.replace(/&quot;/g, "\"");
//	str=str.replace(/&nbsp;/g, " ");
//	str=str.replace(/&amp;/g, "&");
//	str=str.replace(/<br>/ig, "\n");
//	str=str.replace(/<br\/>/ig, "\n");
	return str;
}
