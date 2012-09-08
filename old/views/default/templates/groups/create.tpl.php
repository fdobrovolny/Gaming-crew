			<div id="main">
			
				<div id="rightside">
				

				</div>
				
				<div id="content">
					<h1>Vytvořit novou skupinu</h1>
					<form action="groups/create" method="post">
					<label for="name">Název</label><br />
					<input type="text" id="name" name="name" value="" /><br />
					<label for="description">Popis</label><br />
					<textarea id="description" name="description"></textarea><br />
					<label for="type">Typ</label><br />
					
					<select id="type" name="type">
						<option value="public">Veřejná skupina</option>
						<option value="private">Soukromá skupina</option>
						<option value="private-member-invite">Soukromá skupina (pouze na pozvání)</option>
						<option value="private-self-invite">Soukromá skupina (pouze na vyžádání)</option>
					</select><br />
					
					<input type="submit" id="create" name="create" value="Vytvořit skupinu" />
					</form>
					
				</div>
			
			</div>