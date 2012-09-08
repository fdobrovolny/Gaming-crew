			<div id="main">
			
				<div id="rightside">
					<div style="text-align:center; padding-top: 5px;">
						<img src="uploads/profile/{profile_photo}" />
					</div>
					<div style="padding: 5px;">
						<h2>Přátelé</h2>
						<ul>
							<!-- START profile_friends_sample -->
							<li><a href="profile/view/{ID}">{users_name}</a></li>
							<!-- END profile_friends_sample -->
							<li><a href="relationships/all/{profile_user_id}">Zobrazit všechny</a></li>
							<li><a href="relationships/mutual/{profile_user_id}">Zobrazit společné přátele</a></li>
						</ul>
						<h2>Zbytek mého profilu</h2>
						<ul>
							<li><a href="profile/statuses/{profile_user_id}">Stavové aktualizace</a></li>
						</ul>
					</div>
				</div>
				
				<div id="content"><h1>{profile_name}</h1>
					<p>{p_bio}</p>
					<h2>Můj dinosaurus</h2>
					<table>
						<tr>
							<th>Jméno</th>
							<td>{p_dino_name}</td>
						</tr>
						<tr>
							<th>Narozen</th>
							<td>{p_dino_dob}</td>
						</tr>
						<tr>
							<th>Druh</th>
							<td>{p_dino_breed}</td>
						</tr>
						<tr>
							<th>Pohlaví</th>
							<td>{p_dino_gender}</td>
						</tr>
						
					</table>
				</div>
			</div>