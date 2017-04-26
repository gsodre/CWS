<!DOCTYPE html>
<html>
	<head>
 
		<!-- CSS -->
		<link href="src/style.css" rel="stylesheet"/>
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" type="text/css">
  		<link rel="stylesheet" href="https://pingendo.com/assets/bootstrap/bootstrap-4.0.0-alpha.6.css" type="text/css">
	</head>
	<body>

		<div id="master">
			<div class="tabs"><div class="tab aktip" data-dip="chat">Chat</div><div class="tab" data-dip="users">Users</div></div>
			<div class="chat">
			<?php

				//para cada usuário que entrar é setada uma cor aleatória para seu nick.
				$colours = array('8FC7FF','8F8FFF','C78FFF','FF8FFF','FF8F8F','FFC78F','C7FF8F');
				$user_colour = array_rand($colours);
				
				$username = (!empty($_GET['name']) ? $_GET['name'] : '');
				$userlogin = (!empty($_GET['name']) ? '<div class="user '.$_GET['name'].'">'.$_GET['name'].' 
					<a href="index.php?pergi='.$_GET['name'].'" class="btn sair">Sair</a></div>' : '');
	 
				//se o usuário já estiver logado vai direto para tela do chat.
				if(!empty($username)){
			?>

					<div id='message_box'>
						<!-- Exibe as mensagens -->
					</div>
					<form id="msg_form">
						<input id="message" type="" placeholder="Digite uma mensagem..." />
					</form>

			<?php 

				}
	 
				//se o usuário não estiver logado mostra a tela de login.
				else{
			?>

  <div class="py-5">
    <div class="container">
      <div class="row">
        <div class="col-md-12">
          <div class="card">
            <div class="card-block">
              <h4 class="card-title text-center">Chat com WebSockets</h4>
              <h6 class="card-subtitle text-muted text-center">Bota teu nome campo e clica em entrar pra ser feliz conosco nessa aulinha de Rede de Computadores!</h6>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

			<div class="py-5">
				<div class="container">
					<div class="row">
					<div class="col-md-3"></div>
						<div class="col-md-6"">
							<form class="text-right" action="index.php" method="GET">
      							<div class="form-group">
        
        							<input class="form-control" name="name" class="username" placeholder="Nick" required/>
      							</div>
      
      							<button type="submit" class="btn btn-success">Entrar</button>
   		 					</form>
   		 				</div>
   		 				<div class="col-md-3"></div>
   		 			</div>
   		 		</div>
   		 	</div>

   		<div class="bg-primary py-2 text-center">
			<div class="container-fluid">
				<div class="row">
			        <div class="col-4"> <a><i class="fa fa-fw fa-facebook text-white fa-2x"></i></a> </div>
			        <div class="col-4"> <a><i class="fa fa-fw fa-twitter text-white fa-2x"></i></a> </div>
			        <div class="col-4"> <a><i class="fa fa-fw fa-github text-white fa-2x"></i></a> </div>
      			</div>
   			</div>
  		</div>
			 
			<?php 
				}
			?>
			
			</div>
			<div class="users" style='display:none'>
				<?php echo $userlogin ?>
			</div>
		</div>
		
		<!-- data user hidden -->
		<input id="u_name" type="hidden" value="<?php echo $username ?>"/>
		<input id="u_date" type="hidden" value="<?php echo date("d/m/Y - h:i") ?>"/>
		<input id="u_pergi" type="hidden" value="<?php echo (empty($_GET['pergi']) ? '' : $_GET['pergi']) ?>"/>
		<input id="u_color" type="hidden" value="<?php echo $colours[$user_colour] ?>"/>
	
		<!-- jQuery -->
		<script src="//code.jquery.com/jquery-2.1.3.min.js"></script>
		<script src="src/chat.js"></script>
		<script>
			chatws(
				//host
				host = 'localhost', 
				//port
				port = '8403', 
				//socketpath
				socketpath = 'cws/src/server.php'
			);
		</script>  
	</body>
</html>
