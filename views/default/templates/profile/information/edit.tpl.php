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
							<li><a href="relationships/all/{p_user_id}">Zobrazit všechny</a></li>
							<li><a href="relationships/mutual/{p_user_id}">Zobrazit společné přátele</a></li>
						</ul>
						<h2>Zbytek mého profilu</h2>
						<ul>
							<li><a href="profile/statuses/{p_user_id}">Stavové aktualizace</a></li>
						</ul>
					</div>
				</div>
				
				<div id="content"><h1>{profile_name}: Editace profilu</h1>
					<form action="profile/view/{p_user_id}/edit" method="post" enctype="multipart/form-data">
						<label for="name">Jméno</label><br />
						<input type="text" id="name" name="name" value="{p_name}" /><br />
						<label for="profile_picture">Fotografie</label> <br />
						<input type="file" id="profile_picture" name="profile_picture" />
						<br />
						<label for="bio">Biografie</label>
						<textarea id="bio" name="bio" cols="40" rows="6">{p_bio}</textarea>
						<label for="dino_name">Jméno dinosaura</label><br />
						<input type="text" id="dino_name" name="dino_name" value="{p_dino_name}" /><br />
						<label for="dino_breed">Druh dinosaura</label><br />
						<input type="text" id="dino_breed" name="dino_breed" value="{p_dino_breed}" /><br />
						<label for="dino_dob">Datum narození dinosaura</label><br />
						<input type="text" id="dino_dob" class="selectdate" name="dino_dob" value="{p_dino_dob}" /><br />
						<label for="dino_gender">Pohlaví dinosaura</label><br />
						<select id="dino_gender" name="dino_gender">
							<option value="">Vyberte prosím</option>
							<option value="mužské">mužské</option>
							<option value="ženské">ženské</option>
						</select>
						<br />
						<input type="submit" id="" name="" value="Uložit profil" />
					</form>
					
				</div>
			</div>