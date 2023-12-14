// ==UserScript==
// @name         crosshair
// @namespace    https://cassandra.lol
// @version      0.1
// @description  cubeshot crosshair
// @author       cassandra
// @match        *cubeshot.io/*
// @icon         https://www.google.com/s2/favicons?sz=64&domain=cubeshot.io
// @grant        none
// ==/UserScript==

var newHTML         = document.createElement ('div');
newHTML.innerHTML   = '             \            \
        <img id="crosshair" src="https://cdn.discordapp.com/attachments/516664193668218912/918429080955797514/asdasdasdasdgwewefa.png" </img>       \                        \
';
function addGlobalStyle(css) {
    var head, style;
    head = document.getElementsByTagName('head')[0];
    if (!head) { return; }
    style = document.createElement('style');
    style.type = 'text/css';
    style.innerHTML = css;
    head.appendChild(style);
}

addGlobalStyle('#crosshair { position:absolute;display:block;left: calc(50% - 10px);;top: calc(50% - 10px); }');

addGlobalStyle('body { color: white; background-color: black; }');
document.body.appendChild (newHTML);
