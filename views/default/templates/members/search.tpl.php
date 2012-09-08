			<div id="main">
			
				<div id="rightside">
				</div>
				
				<div id="content">
					<h1>Seznam členů webu Dino Space</h1>
					<p>Výsledky vyhledávání "{public_name}"</p>
					<!-- START members -->
					<p><strong>{name}</strong></p>
					<p>Chová dinosaura <strong>{dino_breed}</strong> {dino_gender}ho pohlaví se jménem <strong>{dino_name}</strong></p>
					<hr />
					<!-- END members -->
					<p>Zobrazená stránka {page_number} z {num_pages}</p>
					<p>{first} {previous} {next} {last}</p>
					
					<form action="members/search" method="post">
					<h2>Vyhledat člena</h2>
					<label for="name">Jméno</label><br />
					<input type="text" id="name" name="name" value="" /><br />
					<input type="submit" id="search" name="search" value="Vyhledat" />
					</form>
				</div>
			
			</div>