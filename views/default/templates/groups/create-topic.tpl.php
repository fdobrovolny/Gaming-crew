			<div id="main">
			
				<div id="rightside">
				
					<ul>
						<li><a href="group/{group_id}">{group_name}</a></li>
					</ul>
				</div>
				
				<div id="content">
					<h1>Vytvoření tématu</h1>
					<form action="group/{group_id}/create-topic" method="post">
					<label for="name">Název tématu</label><br />
					<input type="text" id="name" name="name" value="" /><br />
					<label for="post">První příspěvek</label><br />
					<textarea id="post" name="post"></textarea><br />
					
					
					<input type="submit" id="create" name="create" value="Vytvořit téma" />
					</form>
					
				</div>
			
			</div>