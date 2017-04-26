//já criado o socket, você pode acessa do browser, só que para acessa-lo de forma nativa, só com browsers modernos que suportem a API websocket, caso contrário, existem outras saídas, como flashsocket e javasocket, no qual você pode acessar do seu script.
var chatws = function(k, l, m) {
	
	//create a new WebSocket object.
    var n = 'ws://' + k + ':' + l + '/' + m + '',
        socket = new WebSocket(n);
	
	//user data
    var o = $('#u_name').val(),
        u_date = $('#u_date').val(),
        u_pergi = $('#u_pergi').val(),
        u_color = $('#u_color').val();
		
    if (o != '') {
		//connection is open 
        socket.onopen = function(a) {
            var b = {
                mode: 'userlgn',
                message: 'entrou na sala - ',
                name: o,
                udate: u_date,
                color: ''
            };
			//convert and send data to server
            socket.send(JSON.stringify(b))
        }
    } else if (o == '' && u_pergi != '') {
		//notification if any users are logged out
        socket.onopen = function(a) {
            var b = {
                mode: 'userlgn',
                message: 'saiu da sala - ',
                name: u_pergi,
                udate: u_date,
                color: ''
            };
			//convert and send data to server
            socket.send(JSON.stringify(b))
        }
    }
	//perform the process when sending message
    $("#msg_form").on("submit", function() {
		// get user data
        var a = $('#message').val();
        var b = o;
        var c = u_date;
        if (a == "") {
            alert("Digite alguma mensagem!");
            return
        }
		//prepare json data
        var d = {
            mode: 'usermsg',
            message: a,
            name: b,
            udate: c,
            color: u_color
        };
		//convert and send data to server
        socket.send(JSON.stringify(d));
		//reset text
        $('#message').val('');
        return false
    });
	
	//show messages received from server
    socket.onmessage = function(a) {
        //PHP sends Json data
        var b = JSON.parse(a.data), 
			c = b.type,
			d = b.message,
			e = b.name,
			f = b.date,
			g = b.color;
        if (c == 'usermsg' && e != null) {
            var h = (e == o ? "bubble-right" : "bubble-left");
            var i = (e == o ? "" : g);
            $('#message_box').append('<div class="' + h + '"><p><span class="name" style="color:#' + i + '">' + e + '</span><span class="msgc">' + htmlEntities(d) + '</span><span class="dat">' + f + '</span></p></div>')
			//if there is a new user login then automatically show in the users tab
            if ($('.' + e).html() == null || $('.' + e).html() == undefined) {
                $('.users').append('<div class="user ' + e + '">' + e + '</div>')
            }
		} else if (c == 'userlgn' && e != null) {
            if (d.match(/meninggalkan/g)) {
                if ($('.er' + b.name).html() == null || $('.er' + b.name).html() == undefined) {
                    $('#message_box').append('<div class="bubble-center disconnected er' + e + '">' + e + ' ' + d + ' ' + f + '</div>')
                }
				//when you exclude the user name from the users tab
                $('.' + b.name).remove()
            } else {
                if ($('.kon' + b.name).html() == null || $('.kon' + b.name).html() == undefined) {
                    $('#message_box').append('<div class="bubble-center connected kon' + e + '">' + e + ' ' + d + ' ' + f + '</div>')
                }
                //if there is a new user login then automatically show in the users tab
                if ($('.' + b.name).html() == null || $('.' + b.name).html() == undefined) {
                    $('.users').append('<div class="user ' + b.name + '">' + b.name + '</div>')
                }
            }
        }
		
		/* show status connection (delete if not needed) */
        if (c == 'system') {
            var j = (d.match(/disconnected/g) ? "disconnected" : "connected");
            $('#message_box').append('<div class="bubble-center ' + j + '">' + d + '</div>')
        }
		/*-------------------------------------------------------------*/
		
		//if there is a new message then germatis scroll down
        $("html, body").animate({
            scrollTop: $(document).height()
        }, 1000)
    };
    socket.onerror = function(a) {
        $('#message_box').append('<div class="bubble-center disconnected">Ocorreu um erro - ' + a.data + '</div>')
    };
    socket.onclose = function(a) {
        $('#message_box').append('<div class="bubble-center disconnected">Conexão interrompida...</div>')
    };

	//this function to encode messages that have html tags to be displayed
    function htmlEntities(a) {
        return String(a).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;')
    }	
	
	//settings tab button
    $('.tab').click(function() {
        $('.tab').removeClass('aktip');
        $(this).addClass('aktip');
        var a = $(this).data('dip');
        if (a == "chat") {
            $('.chat').css('display', 'block');
            $('.users').css('display', 'none')
        } else {
            $('.chat').css('display', 'none');
            $('.users').css('display', 'block')
        }
        return false
    });
	
}