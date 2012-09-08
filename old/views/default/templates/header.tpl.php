<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"> 
<head> 
	<base href="{siteurl}" />
	<title>DINO SPACE! Sociální síť pro chovatele dinosaurů</title> 
	<meta http-equiv="content-type" content="text/html; charset=utf-8" /> 
	<meta name="description" content="Sociální síť pro chovatele dinosaurů" /> 
	<meta name="keywords" content="dinosaurus, sociální, síť, dino, space" /> 
	<link type="text/css" href="external/ui-lightness/jquery-ui-1.7.1.custom.css" rel="stylesheet" />	
	<script type="text/javascript" src="external/jquery-1.3.2.min.js"></script> 
	<script type="text/javascript" src="external/jquery-ui-1.7.2.custom.min.js"></script> 
	<script type="text/javascript" src="external/jquery.ui.datepicker-cs.js"></script>
	<script type="text/javascript"> 
		$(function() {
			$('.selectdate').datepicker({
				numberOfMonths: 1,
				showButtonPanel: false,
				dateFormat: 'dd/mm/yy'
			});
		});
		</script> 
	<link rel="stylesheet" type="text/css" href="views/default/style.css" /> 	
	<style type="text/css"> 
	/*.menu{menuselected} a{ background: #FFF !important; color: #3D70A3 !important;}*/
	</style> 
</head> 
<body> 
	<div id="wrapper">
		<div id="sidepane">
			<img src="views/default/images/logo.jpg" />
			<ul>
				<li><a href="home">Domů</a></li>
				<li><a href="members">Členové</a></li>
				<li class="active"><a href="relationships">Přátelé</a></li>
				<li><a href="profile">Profily</a></li>
				<li><a href="messages">Zprávy</a></li>
			</ul>
		</div>
		<div id="contentwrapper">
			
			<div id="headerbar">
			{userbar}
			<!-- <p>Uživatel: {username} | <a href="profile">zobrazit profil</a> | <a href="account">spravovat účet</a> | <a href="authenticate/logout">odhlásit</a></p> -->
			</div>