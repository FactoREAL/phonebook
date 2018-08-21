<?php 
	$accept_host = array('polezhaev', 'medvedev');
	$host = $_SERVER['REMOTE_HOST'];
	$allow_call = in_array($host, $accept_host);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="UTF-8">
	<title>Телефонный справочник</title>
	<link rel="shortcut icon" href="favicon.ico">

	<script src="webix/webix.js"></script>

	<link rel="stylesheet" href="webix/webix.css">
	<style>
	@media screen {
		body {
			margin: 0;
			background-color: rgb(235, 247, 255);
		}
		header {
			margin:  0 0 10px 0;
		}
		main {
			display: flex;
			flex-wrap: nowrap;
			justify-content: space-between;
			height: 860px;
		}
		#mode {
			/*background-color: rgb(52, 152, 219);*/
			background-color: rgb(155, 215, 255);
			border-radius: 5px;
			margin: 5px;
			display: block;
			position: absolute;
			top: 0;
			right: 0;
		}
		.webix_img_btn {
			text-align: center;
		}
		.call_btn .webix_img_btn_abs {
			background-color: #33c170;
			border-color: #38b16c;
		}
		.call_btn .webix_img_btn_abs:hover{
			background-color: #27ae60;
		}
		#info, #book {
			flex-basis: 37%;
		}
		.phones {
			display: flex;
			align-items: center;
		}
		.phones .name {
			flex-basis: 85%;
		}
		#book .number {
			flex-basis: 15%;
			font-weight: bold;
			text-align: center;
			border-radius: 5px;
		}
		<?php if ($allow_call) { ?>
		#book .number:hover {
			background-color: #209150;
			color: white;
		}
		#book .number.active {
			background-color: white;
			border: 2px solid orange;
			color: #666;		
		}
		<?php } ?>
		#info .sudName {
			display: none;
		}
		#info .info {
			display: flex;
			min-height: 40px;
		}
		#info .label {
			padding: 5px 10px;
			flex-basis: 20%;
		}
		#info .value {
			padding: 5px 10px;
			font-weight: bold;
		}
		.webix_list_item.custom {
			line-height: 20px;
			border-bottom: 1px solid #888;
			padding-top: 3px;
			padding-bottom: 3px;
		}

	}

	@media print {
		* {
			color: #222;
		}
		body {
			background-color: white;
			color: black;
		}
		#info .info {
			height: 35px;
			font-size: 16px;
		}
		#info .value {
			font-weight: bold;
			margin: 0 10px;
		}
		.phones {
			display: flex;
			align-items: center;
		}
		.phones .name {
			flex-basis: 85%;
		}
		#info .sudName {
			text-align: center;
			font-size: 20px;
			font-weight: bold;
			margin: 5px 0;
		}
		#book .number {
			flex-basis: 15%;
			font-weight: bold;
			text-align: center;
		}
		button, #mode {
			display: none;
		}
		.webix_list .webix_unit_header {
			background-color: #aaa;
		}
		.webix_list_item.custom {
			line-height: 20px;
			border-bottom: 1px solid #888;
			padding-top: 3px;
			padding-bottom: 3px;
		}
	}
	</style>
</head>
<body>
	<header id="header"></header>
	<div id="mode"></div>
	<main id="main">
		<div id="list"></div>
		<div id="info"></div>
		<div id="book"></div>
	</main>
<script>
webix.ui({
	view: 'toolbar', container: 'header', elements: [
		{view: 'label', label: 'Телефонный справочник аппарата мировых судей Белгородской области'},
		{view: 'label', label: '(15.05.2018)'}
	]
});

webix.ui({
	container: 'mode',
	cols: [
		{view: 'button', type: 'iconButton', width: 40, css:"call_btn", icon: 'phone', tooltip: 'Набрать номер', click: function(){ 
			$$('dialer').show();
			$$('number').focus();
		}},
		{view: 'icon', width: 40},
		{view: 'button', type: 'iconButton', width: 40, icon: 'eye', click: 'viewVersion()', tooltip: 'Версия для просмотра'},
		{view: 'button', type: 'iconButton', width: 40, icon: 'print', click: 'printVersion()', tooltip: 'Версия для печати'}, 
	]
});

webix.ui({
	id: 'sudlist', view: 'list', container: 'list', width: 300, type: {height: 33}, scroll: true, select: true, template: '#data1#', datatype: 'csv', url: 'суды.csv', ready: function(){
		this.filter(function(item){
		    return item.data0 > 0;
		});
		this.sort("#data0#", "asc", "int");
		this.select(this.getFirstId());
	}
});

webix.ui({
	id: 'sudinfo', container: 'info', template: '<div class="sudName">#data1#</div><div class="info"><span class="label">Индекс:</span><span class="value">#data2#</span></div><div class="info"><span class="label">Адрес:</span><span class="value">#data3#</span></div><div class="info"><span class="label">Код города:</span><span id="citycode" class="value">#data4#</span></div><div class="info"><span class="label">Эл.почта:</span><span class="value">#data5#</span></div>'
});

webix.ui({
	view: 'unitlist', container: 'book', id: 'phonebook',  scroll: true, uniteBy: '#data1#', template: '<span class="phones"><div class="name">#data2#<br><b>#data3#</b></div> <div class="number">#data4#</div></span>', select: false, type: {height: 'auto'}, datatype: 'csv', url: 'справочник.csv', ready: function(){
	    //code...
	}
	<?php if ($allow_call) { ?>
	,onClick: {
		number: function(e, id) { //клик по div.number
			var code = document.querySelector('#citycode').innerHTML;
			var number = e.target.innerHTML
			
			if (e.target.active) { // уже идет набор
				e.target.active = false;
				resetCall();
			} else { // телефон свободен
				e.target.active = true;
				code = code.split('(').join('').split(')').join('').split(' ').join('');
				code = code.replace('+7', '');
				webix.html.addCss(e.target, 'active');
				callTo(code, number, e.target);
			}
		}
	}
	<?php } ?>
});

webix.ui({
	view: 'popup',
	id: 'dialer',
	width: 300,
	position: 'center',
	modal: true,
	body: {
		view: 'form',
		borderless: true,
		elements: [
			{id: 'number', view: 'text', label: 'Номер (без "8")', labelPosition: 'top', name: 'n'},
			{view: 'layout',
			cols: [
				{view: 'button', type: 'form', value: 'НАБРАТЬ', click: function(){
					if (this.getParentView().getParentView().validate()) {
						var text = $$('number').getValue();
						text = text.split('-').join('').split(' ').join('');
						text = text.replace('+7', '8');
						var code = number = '';
						var isCode = false;
						// парсим из строки код и номер
						for (var i = 0; i < text.length; i++) {
							if (text[i] == '(') {
								isCode = true;
								number = '';
								continue;
							}
							if (text[i] == ')') {
								isCode = false;
								number = '';
								continue;
							}
							if (isCode) {
								code += text[i];
							} else number += text[i];
						}
						if (!code && text.indexOf('8') == 0) { // убираем +7 и 8 в начале
							text = text.substring(1);
						}
						$$('number').setValue('');
						$$('dialer').hide();
						callTo(code, number)
					}
				}},
				{view: 'button',type: 'danger', value: 'Отмена', click: () => {$$('dialer').hide(); }}
			]},
		],
		rules: {
			'n': webix.rules.isNotEmpty
		}
	}
});

$$('phonebook').bind($$('sudlist'), function(slave, master) {
	if (!master) return false;
	return master.data0 == slave.data0;
});

$$('sudinfo').bind($$('sudlist'), function(slave, master){
	if (!master) return false;
	return master.data0 == slave.data0;
});


function viewVersion() {
	var header = document.getElementById('header');
	var main = document.getElementById('main');
	var list = document.getElementById('list');
	var info = document.getElementById('info');
	var book = document.getElementById('book');

	var listItems = book.getElementsByClassName('webix_list_item');
	for (i=0; i<listItems.length; i++) {
		listItems[i].classList.remove('custom');
	}

	header.style = undefined;
	main.style = undefined;
	list.style.display = 'block';

	main.style.display = 'flex';

	info.style.width = '37%';
	info.style.height = '100%';

	book.style.width = '37%';
	
	$$('sudinfo').adjust();
	$$('phonebook').adjust();
	info.style = undefined;
	book.style = undefined;
}

function printVersion() {
	var header = document.getElementById('header');
	var main = document.getElementById('main');
	var list = document.getElementById('list');
	var info = document.getElementById('info');
	var book = document.getElementById('book');
	
	with (main.style) {
		display = 'inline-block';
		height = 'auto';
		width = '65%';
	}
	header.style.display = 'none';
	list.style.display = 'none';
	info.style.height = '170px';
	var listItems = book.getElementsByClassName('webix_list_item');
	for (i=0; i<listItems.length; i++) {
		listItems[i].classList.add('custom');
	}
	book.childNodes[0].style.height = 'auto';

	$$('sudinfo').adjust();
	$$('phonebook').adjust();
	window.print();
}

function callTo(code, num, target=undefined) {
	number = num.split('-').join('');
	console.log('звоним '+code+number);
	webix.ajax().post('/cgi-bin/dialer.py', {c:code, n:number}, {
		success: function(txt, data){
			response = data.json();
			switch (response.res) {
				case 'ready':
					if (target) target.active = false;
					webix.message("Номер набран. Поднимите трубку", type='debug');
					break;
				case 'busy':
					if (target) target.active = false;
					webix.message('Телефон занят!', type='error');
					break;
				case 'reset':
					if (target) target.active = false;
					break;
				default: console.log(response);
			}
			if (target) webix.html.removeCss(target, 'active');
		},
		error: function(txt, data){
			webix.message("Нет связи с сервером!", type='error');
			if (target) webix.html.removeCss(target, 'active');
		}
	});
}

function resetCall(target) {
	webix.ajax().post('/cgi-bin/dialer.py');
	console.log('сброс');
}
</script>
</body>
</html>