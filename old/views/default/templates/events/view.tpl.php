<div id="main"> 

  <div id="rightside"> 
  </div> 

  <div id="content"> 
    <h1>{event_name}</h1> 
    <p>{event_description}</p> 
    <p>{event_event_date}: {event_start_time} až {event_end_time}</p> 
    
    <h2>Vaše účast</h2> 
    <p>Této akce se:</p> 
    <form action="event/change-attendance/{event_ID}" method="post"> 
      <select name="status"> 
        <option value="" {unknown_select}>Vyberte prosím...</option> 
        <option value="going" {attending_select}>Zúčastním</option> 
        <option value="not going" {notattending_select}>Nezúčastním</option> 
        <option value="maybe" {maybeattending_select}>Možná se zúčastním</option> 
      </select> 
      <input type="submit" name="" value="Aktualizovat" /> 
    </form> 

    <h2>Účastní se</h2> 
    <ul> 
      <!-- START attending -->
      <li>{name}</li>
      <!-- END attending --> 
    </ul> 

    <h2>Pozvaní (zatím neodpověděli)</h2> 
    <ul> 
      <!-- START invited -->
      <li>{name}</li>
      <!-- END invited --> 
    </ul> 

    <h2>Možná se účastní</h2> 
    <ul> 
      <!-- START maybeattending -->
      <li>{name}</li>
      <!-- END maybeattending --> 
    </ul> 

    <h2>Neúčastní se</h2> 
    <ul> 
      <!-- START notattending -->
      <li>{name}</li>
      <!-- END notattending --> 
    </ul> 
  </div>
</div>
