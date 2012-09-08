<p>Řekněte ostatním, co právě děláte</p>
<form action="profile/statuses/{profile_user_id}" method="post" enctype="multipart/form-data">
<textarea id="status" name="status"></textarea>
<br />
<input type="radio" name="status_type" id="status_checker_update" class="status_checker" value="update"  />Aktualizace
<input type="radio" name="status_type" id="status_checker_video" class="status_checker" value="video"  />Video
<input type="radio" name="status_type" id="status_checker_image" class="status_checker" value="image"  />Obrázek
<input type="radio" name="status_type" id="status_checker_link" class="status_checker" value="link"  />Odkaz
<br />
<div class="video_input  extra_field">
<label for="video_url" class="">Adresa na serveru YouTube</label>
<input type="text" id="" name="video_url" class="" /><br />
</div>
<div class="image_input  extra_field">
<label for="image_file" class="">Nahrát obrázek</label>
<input type="file" id="" name="image_file" class="" /><br />
</div>
<div class="link_input  extra_field">
<label for="link_url" class="">Odkaz</label>
<input type="text" id="" name="link_url" class="" /><br />
<label for="link_description" class="">Popis</label>
<input type="text" id="" name="link_description" class="" /><br />
</div>
<input type="submit" id="updatestatus" name="updatestatus" value="Aktualizovat" />
</form>
<script type="text/javascript"> 
$(function() {
	$('.extra_field').hide();
	$("input[name='status_type']").change(function(){
		$('.extra_field').hide();
	    $('.'+ $("input[name='status_type']:checked").val() + '_input').show();
	});
});
</script>