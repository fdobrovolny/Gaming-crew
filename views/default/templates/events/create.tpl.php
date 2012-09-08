<div id="main">

  <div id="rightside">
  </div>

  <div id="content">
    <h1>Vytvořit událost</h1>
    <form action="event/create" method="post">
      <label for="">Název</label><br />
      <input type="text" name="name" /><br />
    
      <label for="">Typ události</label><br />
      <select name="type">
        <option value="public">Veřejná</option>
        <option value="private">Soukromá</option>      
      </select><br />
      
      <label for="">Datum</label><br />
      <input type="text" class="selectdate" name="date"/><br />
      
      <label for="">Čas začátku</label><br />
      <input type="text" class="selecttime" name="start_time" /><br />
      
      <label for="">Čas konce</label><br />
      <input type="text" class="selecttime" name="end_time" /><br />
      
      <label for="">Popis</label><br />
      <textarea name="description" cols="45" rows="6"></textarea><br />
      
      <h2>Pozvat přátele?</h2>
      <p>Vyberte přátele, které chcete na událost pozvat.</p>
      <!-- START all -->
      <input type="checkbox" name="invitees[]" value="{ID}" />{users_name}
      <!-- END all --><br />
      <input type="submit" name="" value="Vytvořit událost" />
    </form>
  </div>
</div>
