<!DOCTYPE html>
<html>
	<head>
	  <meta charset="utf-8" />
	  <meta http-equiv="X-UA-Compatible" content="IE=edge">
	  <title>Meet & run</title>
	  <meta name="description" content="">
	  <meta name="viewport" content="width=device-width, initial-scale=1">
	  <link rel="stylesheet" href="css/reset.css">
	  <link rel="stylesheet" href="font/stylesheet.css">
	  <link rel="stylesheet" href="css/style.css">
	  <link rel="stylesheet" href="css/ion.rangeSlider.skinNice.css">
	  <link rel="stylesheet" href="css/ionrangeSlider.css">
	  <script type="text/javascript" src="http://code.jquery.com/jquery-latest.min.js"></script>
	  <script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
	  <script src="js/ion.rangeSlider.min.js"></script>
	  <script type="text/javascript" src="js/script.js"></script>
	</head>
	<body id="recherche">
		<header class="active header_connected " id="2">
			<div class="contain">
				<div>
					<a href=""></a>
					<a href=""></a>
					<a href=""><span>33</span></a>
					<a href="" class="active"></a>
				</div>
				<a href="#">Déconnexion</a>
			</div>
		</header>
		<section class="">
				<h1 class="titleN2">Rechercher une sportive</h1>
				<form action="#"></form>
					<h2 class="item_title"><p class="contain">Votre recherche</p></h2>
					<div class="section ma_recherche contain">
						<div class="line clearfix">
							<div class="left"><p>SEXE :</p></div>
							<div class="right">
								<input type="radio" name="sex" id="homme" value="homme"><label for="homme">Homme</label>
								<input type="radio" name="sex" id="femme" value="femme"><label for="femme">Femme</label>
							</div>
						</div>
						<div class="line clearfix">
							<div class="left"><p>AGE :</p></div>
							<div class="right">
								<input type="text" class="torangeage" name="taille" value="18;99">
							</div>
						</div>
						<div class="line clearfix">
							<div class="left"><p>VILLE :</p></div>
							<div class="right">
								<input type="text" placeholder="Entrez une ville...">
							</div>
						</div>
						<div class="line clearfix">
							<div class="left"><p>RAYON :</p></div>
							<div class="right">
								<input type="text" class="torangekm" name="rayon" value="1;200">
							</div>
						</div>
					</div>
					<div id="accordion">

					  <h3 class="item_title"><p class="contain">Critères physiques</p></h3>
					  <div id="physique" class="section contain">
							<div class="line clearfix">
							<div class="left"><p>TAILLE :</p></div>
							<div class="right">
								<input type="text" class="torangetaille" name="taille" value="130;220">
							</div>
						</div>
						<div class="line clearfix">
							<div class="left"><p>POIDS :</p></div>
							<div class="right">
								<input type="text" class="torangepoids" name="poids" value="30;300">
							</div>
						</div>
						<div class="line clearfix">
							<div class="left"><p>APPARENCE :</p></div>
							<div class="right">
								<select name="apparence">
									<option value="filiforme">filiforme</option>
									<option value="normal">normal</option>
									<option value="gros">gros</option>
								</select>
							</div>
						</div>
						<div class="line clearfix">
							<div class="left"><p>CARACTERE :</p></div>
							<div class="right">
								<select name="caractere">
									<option value="enthousiaste">enthousiaste</option>
									<option value="joyeux">joyeux</option>
									<option value="dépressif">dépressif</option>
								</select>
							</div>
						</div>
					  </div>
					  <h3 class="item_title"><p class="contain">Critères sportifs</p></h3>
					  <div id="sportif" class="section contain">
					   	<div class="line clearfix sportchoice">
							<div class="left"><p>SPORT PRATIQUé :</p></div>
							<div class="right">
								<input type="checkbox" name="sport" id="football" value="football"><label for="football">Football</label>
								<input type="checkbox" name="sport" id="Handball" value="handball"><label for="handball">Handball</label><br>
								<input type="checkbox" name="sport" id="tennis" value="tennis"><label for="tennis">Tennis</label>
								<input type="checkbox" name="sport" id="course" value="course"><label for="course">Course à pied</label><br>
								<input type="checkbox" name="sport" id="rugby" value="rugby"><label for="rugby">Rugby</label>
							</div>
						</div>
						<div class="line clearfix">
							<div class="left"><p>KILOMèTRES :</p></div>
							<div class="right">
								<input type="text" class="torangekm" name="km" value="10;100">
							</div>
						</div>
						<div class="line clearfix">
							<div class="left"><p>CALORIES :</p></div>
							<div class="right">
								<input type="text" class="torangecal" name="cal" value="10;100">
							</div>
						</div>
					  </div>
					</div>
					<input type="submit" value="RECHERCHER">
				</form>
		</section>
		<footer>
			<div class="contain">
				<div>
					<div>
						<p>© 2014 Run & Meet - Tous droits réservés</p>
					</div>
					<div>
						<p class="footer-title">Nous suivre sur</p>
						<div>
							<a href=""></a>
							<a href=""></a>
							<a href=""></a>
						</div>
					</div>
					<div>
						<div>
							<p class="footer-title">A propos de Run & Meet</p>
							<ul>
								<li><a href="">A propos</a></li>
								<li><a href="">Conditions d'utilisation</a></li>
								<li><a href="">Protection des données</a></li>
							</ul>
						</div>
					</div>
				</div>
			</div>
		</footer>
	</body>
</html>