
<div id="main">
			
				<div id="rightside">
				</div>
				
				<div id="content">
				<h1>Vítejte na webu DINO SPACE!</h1>
				{error}
				<form action="authenticate/login" method="post">
				<label for="sn_auth_user">Uživatelské jméno</label><br />
				<input type="text" id="sn_auth_user" name="sn_auth_user" /><br />
				<label for="sn_auth_pass">Heslo</label><br />
				<input type="password" id="sn_auth_pass" name="sn_auth_pass" /><br />
				<input type="hidden" id="referer" name="referer" value="{referer}" />
				<input type="submit" id="login" name="login" value="Přihlásit" />
				</form>			
				</div>
			
			</div>