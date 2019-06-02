$("#psyreq").hide();
$("#answdiv").hide();

var conn = new WebSocket('ws://localhost:8080');
conn.onopen = function(e) {
    console.log("Connection established!");
	$("#psyreq").show();
	conn.send('R003@');
};

conn.onerror = function(e) {
	$("#psyreq").hide();
	$("#netmsg").text("Сетевая ошибка: " + e.data);
}

conn.onclose = function(e) {
	$("#psyreq").hide();
	$("#netmsg").text("Соединение с сервером разорвано!");
}

conn.onmessage = function(e) {
	console.log(e.data);
	
	if(e.data.length > 4)
	{
		var answ = e.data.substr(0,4);
		
		if(answ == "A001")
		{
			$("#answval").text(e.data.substr(5));
			$( "#answdiv" ).show();
		}
		else if(answ == "A002")
		{
			$("#answ_num").val("");
			$("#reqdiv").show();
			conn.send('R003@');
		}
		else if(answ == "A003")
		{
			$("#statval").text(e.data.substr(5));
		}
		else
		{
			alert("Неизвестный ответ! Перезагрузите страницу.");
		}
	}
	else alert("Непонятный ответ! Перезагрузите страницу.");
    
};

function psyAskBtn()
{
	$("#reqdiv").hide();
	conn.send('R001@');
}

function MyNumBtn()
{
	var mynum = $("#answ_num").val();
	
	if(mynum && !isNaN(mynum))
	{
		$( "#answdiv" ).hide();
		conn.send("R002" + parseInt(mynum,10));
	}
	else
		alert("Нужно ввести число!");
}
