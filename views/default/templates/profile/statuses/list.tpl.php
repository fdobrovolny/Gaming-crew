			<div id="main">
			
				<div id="rightside">
					<div style="text-align:center; padding-top: 5px;">
						<img src="uploads/profile/{profile_photo}" />
					</div>
					<div style="padding: 5px;">
					<h2>{profile_name}</h2>
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
				
				<div id="content"><h1>Nedávné aktualizace</h1>
				  {status_update}
				  <!-- {status_update_message} -->
					<!-- START updates -->
					{update-{ID}}
					<!-- END updates -->
				</div>
			</div>