<div id="main">
			
				<div id="rightside">
				</div>
				
				<div id="content">
				<h1>Přidejte se k DINO SPACE!</h1>
				{error}
				<form action="authenticate/register" method="post"> 
 
<label for="register_user">Uživatelské jméno</label><br /> 
<input type="text" id="register_user" name="register_user" value="{register_user}" /><br /> 
 
<label for="register_password">Heslo</label><br /> 
<input type="password" id="register_password" name="register_password" value="" /><br /> 
 
<label for="register_password_confirm">Potvrzení hesla</label><br /> 
<input type="password" id="register_password_confirm" name="register_password_confirm" value="" /><br /> 
 
<label for="register_email">E-mail</label><br /> 
<input type="text" id="register_email" name="register_email" value="{register_email}" /><br /> 

<label for="register_dino_name">Jméno dinosaura</label><br /> 
<input type="text" id="register_dino_name" name="register_dino_name" value="{register_dino_name}" /><br /> 

<label for="register_dino_breed">Druh dinosaura</label><br /> 
<input type="text" id="register_dino_breed" name="register_dino_breed" value="{register_dino_breed}" /><br /> 

<label for="register_dino_gender">Pohlaví dinosaura</label><br /> 
<select id="register_dino_gender" name="register_dino_gender">
<option value="mužské">mužské</option>
<option value="ženské">ženské</option>
</select><br />

<label for="register_dino_dob">Datum narození dinosaura (dd/mm/yy)</label><br /> 
<input type="text" id="register_dino_dob" name="register_dino_dob" value="{register_dino_dob}" /><br /> 
 
 
 
<label for="">Souhlasíte s podmínkami užití?</label><br /> 
<input type="checkbox" id="register_terms" name="register_terms" value="1" /> <br />

<input type="submit" id="process_registration" name="process_registration" value="Vytvořit účet" /> 
</form> 
				
				
				</div>
			
			</div>