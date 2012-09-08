			<div id="main">
			
				<div id="rightside">
				
				<ul>
					<li><a href="messages/">Doručené zprávy</a></a>
				</ul>
				</div>
				
				<div id="content">
					<h1>Vytvořit zprávu</h1>
					<form action="messages/create" method="post">
					<label for="recipient">Příjemce:</label><br />
					<select id="recipient" name="recipient">
						<!-- START recipients -->
						<option value="{ID}" {opt}>{users_name}</option>
						<!-- END recipients -->
					</select><br />
					<label for="subject">Předmět:</label><br />
					<input type="text" id="subject" name="subject" value="{subject}" /><br />
					<label for="message">Zpráva:</label><br />
					<textarea id="message" name="message"></textarea><br />
					
					<input type="submit" id="create" name="create" value="Odeslat zprávu" />
					</form>
					
				</div>
			
			</div>