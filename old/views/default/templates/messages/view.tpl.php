			<div id="main">
			
				<div id="rightside">
				
				<ul>
					<li><a href="messages/">Doručené zprávy</a></a>
					<li><a href="messages/create/{inbox_id}">Odpovědět na tuto zprávu</a></a>
					<li><a href="messages/delete/{inbox_id}">Odstranit tuto zprávu</a></a>
					<li><a href="messages/create">Vytvořit novou zprávu</a></a>
				</ul>
				</div>
				
				<div id="content">
					<h1>Zobrazení zprávy</h1>
					<table>
						<tr>
							<th>Předmět</th>
							<td>{inbox_subject}</td>
						</tr>
						<tr>
							<th>Od</th>
							<td>{inbox_senderName}</td>
						</tr>
						<tr>
							<th>Odeslána</th>
							<td>{inbox_sentFriendlyTime}</td>
						</tr>
						<tr>
							<th>Zpráva</th>
							<td>{inbox_message}</td>
						</tr>
					</table>
				</div>
			
			</div>