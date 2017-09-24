
	<!--<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"> -->
	<style>
		.tinystarbar {
			margin-left: auto;
			margin-right: auto;
			line-height: 12px;
			height: 12px;
			width: 101px;
			background: url(./PHP-IMDB-Grabber/images/starstiny.png) no-repeat 0px -12px;
			text-align: left;
		}
		
		.tinystarbar div {
			margin: 0;
			height: 12px;
			width: 0px;
			background: url(./PHP-IMDB-Grabber/images/starstiny.png) no-repeat 0px 0px;
		}

		.tinystarbar div {
			margin: 0;
			height: 12px;
			width: 0px;
			background: url(./PHP-IMDB-Grabber/images/starstiny.png) no-repeat 0px 0px;
		}
		table.recs tr.poster {
			vertical-align: bottom;
		}
		table.recs td {
			width: 20%;
			text-align: center;
		}
		table.recs tr.poster a img {
			border-width: 1px;
			background-color: #c2c8da;
		}
		#imdb_block {width:667px;}
		#imdb_block .hero{
			background-color: #333;
			color: #fff;
			position: relative;
			padding: 48px 60px;
		}
		#imdb_block .hero .imdb_title{
			font: 36px Arial,sans-serif;
			font-weight: normal;
			margin: 0px;
			color: #fff;
			padding-bottom: 3px;
			max-width: 80%;
		}
		#imdb_block .hero .imdb_title a {
			color: #fff;
			text-decoration: initial;
		}
		#imdb_block .hero .titleYear {
			color: silver;
			font-family: Arial;
			font-size: 25px;
			line-height: 100%;
		}
		#imdb_block .hero .subtext {
			font-size: 11px;
			color: silver;
		}
		#imdb_block .ghost {
			margin: 0 .5em;
			color: #6b6b6b;
		}
		#imdb_block .imdbRating {
			background: url(./PHP-IMDB-Grabber/images/sprites.png) no-repeat;
			background-position: -10px -118px;
			font-size: 11px;
			height: 40px;
			line-height: 13px;
			padding: 2px 0 0 40px;
			width: 100px;
			position: absolute;
			top: 35%;
			right: 50px;
			border-left: 1px solid #6b6b6b;
		}
		#imdb_block .imdbRating strong {
			font-size: 24px;
			font-weight: normal;
			font-family: Arial;
			line-height: 24px;
		}
		#imdb_block .imdbRating .votes {
			color: silver;
		}
		.gray {
			color: #6b6b6b;
			font-size: 10px;
		}
		#imdb_block .imdbResources {
			background-color: #333;
		}
		#imdb_block .imdbResources img {
			height:268px;
			width: 182px;
			float: left;
			margin-right: 4px;
			margin-top: 2px;
			margin-left: 1px;
		}
		#imdb_block .imdbInfoBlock {
			padding: 18px 20px;
			font-size: 13px;
			font-family: Verdana, Arial, sans-serif;
			color: #333;
		}
		#imdb_block .bg-white {
			color: #000 !important;
			background-color: #eee;
			padding-top: 5px;
			padding-left: 200px;
		}
		#imdb_block h4{
			color: #666666;
			font-size: 13px;
			margin: 0.35em 0 0.25em;
			padding: 0;
			font-weight: bold;
			padding-right: 8px;
		}
		h4.inline {
			display: inline;
			padding: 0 0.5em 0 0;
		}
		
	</style>
	<?php
	if(!isset($_GET['imdb_id'])) die();
	
	$imdb_id = $_GET['imdb_id'];

	include_once './imdb.class.php';
	?>

	<?php
	$oIMDB = new IMDB('http://www.imdb.com/title/tt'.$imdb_id.'/');
	?>
	<?php if ($oIMDB->isReady): ?>

	<div id="imdb_block">
		<div class="hero">
			<h1 class="imdb_title"><a href="<?=$oIMDB->getUrl() ?>" title="IMDB Link"><?=$oIMDB->getTitle() ?></a><span class="titleYear"> (<?=$oIMDB->getYear() ?>)</span></h1>
			<div class="subtext">
				<?=$oIMDB->getRuntime() ?> <span class="ghost">|</span>
				<?=$oIMDB->getGenre() ?> <span class="ghost">|</span>
				<?=$oIMDB->getReleaseDate() ?>
			</div>
			<div class="imdbRating">
				<div class="ratingvalue">
					<strong><?=$oIMDB->getRating() ?></strong>
					<span class="gray">/</span>
					<span class="gray">10</span>
				</div>
				<div class="votes"><?=$oIMDB->getVotes() ?></div>
			</div>
		</div>
		<div class="imdbResources">
			<img src="./PHP-IMDB-GRABBER/<?=$oIMDB->getPoster('medium', true) ?>" alt="*">
			<?php $trailer = $oIMDB->getTrailerAsUrl(); if($trailer == 'n/A'): ?>
				<div class="bg-white imdbInfoBlock">
					<?php $plot = $oIMDB->getPlot(); if($plot != 'n/A'): ?> <p class="imdbPlot"><?= $plot ?></p> <?php endif; ?>
					<?php $directors = $oIMDB->getDirectorAsUrl(); if($directors != 'n/A'): ?> <p><h4 class="inline">Directors: </h4><?=$directors ?></p> <?php endif; ?>
					<?php $writers = $oIMDB->getWriterAsUrl(); if($writers != 'n/A'): ?> <p><h4 class="inline">Writers: </h4><?=$writers ?></p> <?php endif; ?>
					<?php $awards = $oIMDB->getAwards(); if($awards != 'n/A'): ?> <p><h4 class="inline">Awards: </h4><?=$awards ?></p> <?php endif; ?>
					<?php $genres = $oIMDB->getGenre(); if($genres != 'n/A'): ?> <p><h4 class="inline">Genres: </h4><?=$genres ?></p> <?php endif; ?>
					<?php $plotkeys = $oIMDB->getPlotKeywords(); if($plotkeys != 'n/A'): ?> <p><h4 class="inline">Plot Keywords: </h4><?=$plotkeys ?></p> <?php endif; ?>
					<?php $mpaa = $oIMDB->getMpaa(); if($mpaa != 'n/A'): ?> <p><h4 class="inline">MPAA: </h4><?=$mpaa ?></p> <?php endif; ?>
					<?php $akas = $oIMDB->getAkas(); if($akas != 'n/A'): ?> <p><h4 class="inline">Also Known As: </h4><?=$akas ?></p> <?php endif; ?>
					<?php $seasons = $oIMDB->getSeasonsAsUrl(); if($seasons != 'n/A'): ?> <p><h4 class="inline">Seasons: </h4>'<?=$seasons ?>'</p> <?php endif; ?>
				</div>
			<?php else: ?>
				<iframe src="<?=$trailer ?>imdb/embed?autoplay=false&amp;width=480" width="480" height="270" allowfullscreen="true" mozallowfullscreen="true" webkitallowfullscreen="true" frameborder="no" scrolling="no" __idm_id__="642839553"></iframe>
			<?php endif; ?>
		</div>
		<?php if($trailer != 'n/A'): ?>
		<div class="imdbInfoBlock">
			<?php $plot = $oIMDB->getPlot(); if($plot != 'n/A'): ?> <p class="imdbPlot"><?= $plot ?></p> <?php endif; ?>
			<?php $directors = $oIMDB->getDirectorAsUrl(); if($directors != 'n/A'): ?> <p><h4 class="inline">Directors: </h4><?=$directors ?></p> <?php endif; ?>
			<?php $writers = $oIMDB->getWriterAsUrl(); if($writers != 'n/A'): ?> <p><h4 class="inline">Writers: </h4><?=$writers ?></p> <?php endif; ?>
			<?php $awards = $oIMDB->getAwards(); if($awards != 'n/A'): ?> <p><h4 class="inline">Awards: </h4><?=$awards ?></p> <?php endif; ?>
			<?php $genres = $oIMDB->getGenre(); if($genres != 'n/A'): ?> <p><h4 class="inline">Genres: </h4><?=$genres ?></p> <?php endif; ?>
			<?php $plotkeys = $oIMDB->getPlotKeywords(); if($plotkeys != 'n/A'): ?> <p><h4 class="inline">Plot Keywords: </h4><?=$plotkeys ?></p> <?php endif; ?>
			<?php $mpaa = $oIMDB->getMpaa(); if($mpaa != 'n/A'): ?> <p><h4 class="inline">MPAA: </h4><?=$mpaa ?></p> <?php endif; ?>
			<?php $akas = $oIMDB->getAkas(); if($akas != 'n/A'): ?> <p><h4 class="inline">Also Known As: </h4><?=$akas ?></p> <?php endif; ?>
			<?php $seasons = $oIMDB->getSeasonsAsUrl(); if($seasons != 'n/A'): ?> <p><h4 class="inline">Seasons: </h4>'<?=$seasons ?>'</p> <?php endif; ?>
		</div>
		<?php endif; ?>
		<?php $recommendations = $oIMDB->getRecommendations(); if($recommendations != 'n/A'): ?>
		<div class="recommendations">
			<?=$recommendations ?>
		</div>
		<?php endif; ?>
	</div>
	<?php else: ?>
		<p>Movie not found!</p>
	<?php endif; ?>