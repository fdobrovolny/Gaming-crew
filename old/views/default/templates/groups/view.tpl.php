			<div id="main">
			
				<div id="rightside">
					<ul>
						<li><a href="group/{group_id}/create-topic">Vytvořit téma</a></li>
					</ul>

				</div>
				
				<div id="content">
					<h1>{group_name}</h1>
					<p>{group_description}</p>
					<h2>Témata</h2>
					<table>
						<tr>
							<th>Téma</th><th>Autor</th><th>Vytvořeno</th><th>Příspěvků</th>
						</tr>
						<!-- START topics -->
						<tr>
							<td><a href="group/{group_id}/view-topic/{ID}">{name}</a></td><td>{creator_name}</td><td>{created_friendly}</td><td>{posts}</td>
						</tr>
						<!-- END topics -->
					</table>
				</div>
			
			</div>