			<div id="main">
			
				<div id="rightside">
				
					<ul>
						<li><a href="group/{group_id}">{group_name}</a></li>
						<li><a href="group/{group_id}/create-topic">Vytvořit téma</a></li>
					</ul>
				</div>
				
				<div id="content">
					<h1>{topic_name}</h1>
					<!-- START posts -->
					<p>{post}</p>
					<p><em>Přidal {creator_friendly_post}, {friendly_created_post}</em></p>
					<hr />
					<!-- END posts -->
					<h2>Vložit příspěvek</h2>
					<form action="group/{group_id}/reply-to-topic/{topic_id}" method="post">
					<textarea id="post" name="post">
					
					</textarea>
					<input type="submit" id="np" name="np" value="Vložit" />
					
					</form>
				</div>
			
			</div>