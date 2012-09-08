			<div id="main">
			
				<div id="rightside">
				
				<ul>
					<li><a href="messages/create">Vytvořit novou zprávu</a></a>
				</ul>
				</div>
				
				<div id="content">
					<h1>Doručené zprávy</h1>
					<table>
						<tr>
							<th>Od</th>
							<th>Předmět</th>
							<th>Odeslána</th>
						</tr>
						<!-- START messages -->
						<tr class="{read_style}">
							<td>{sender_name}</td>
							<td><a href="messages/view/{ID}">{subject}</a></td>
							<td>{sent_friendly}</td>
						</tr>
						<!-- END messages -->
					</table>
				</div>
			
			</div>