<?php
/////////////////////////////////////////////////////////////////////////////////////
// XBTIT IMDB Grabber
//
// Copyright (C) 2017 - 2018 @ https://github.com/imtiazmahbub
//
//    This file is part of XBTIT IMDB Grabber Hack
//
// Redistribution and use in source and binary forms, with or without modification,
// are permitted provided that the following conditions are met:
//
//   1. Redistributions of source code must retain the above copyright notice,
//      this list of conditions and the following disclaimer.
//   2. Redistributions in binary form must reproduce the above copyright notice,
//      this list of conditions and the following disclaimer in the documentation
//      and/or other materials provided with the distribution.
//   3. The name of the author may not be used to endorse or promote products
//      derived from this software without specific prior written permission.
//   4. It's against IMDB's policy to scrap their website. It's an user's individual
//      responsibility if they use it. The author can't be blamed for any illegal
//      use of this software.
//
// THIS SOFTWARE IS PROVIDED BY THE AUTHOR ``AS IS'' AND ANY EXPRESS OR IMPLIED
// WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF
// MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
// IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
// SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED
// TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
// PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF
// LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
// NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE,
// EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
//
////////////////////////////////////////////////////////////////////////////////////
define("IMDB_FOLDER",'imdb_files');

if (!empty($_GET['imdb_id'])){
	$imdb_id = $_GET['imdb_id'];
	
	// check if imdb ID is 7 characters long or die!
	if (strlen($imdb_id) != 7){
		echo '<div class="alert alert-danger" role="alert"><span class=text-danger">IMDB ID must be an integer of length: 7</span></div>';
		die();
	}
	
	$imdbInfo = new imdbInfo;
	$info = $imdbInfo->makeJson($imdb_id);

	if(!empty($_GET['response'])) {
		$response = $_GET['response'];
		if($response == 'html') {
			if($info) {
				$imdbInfo->createView($info);
			} else echo '<p>Movie not found!</p>';
		} elseif ($response == 'json'){
			header('Content-Type: application/json');
			echo json_decode($info);
		}
	}
	
	
	if (defined('IN_BTIT')) {
		$imdbinfotpl = new bTemplate();
		$oIMDB = (array)json_decode($info);
		
		$imdbinfotpl -> set("oIMDB", $oIMDB);
		if ($oIMDB['trailers'] == "n/A")
		$imdbinfotpl -> set("noTrailer", 'noTrailer', TRUE);
		$imdbinfotpl -> set("hasRecom", $oIMDB['recommendations'] == "n/A", TRUE);
	}
}

class imdbInfo {
	
	public function makeJson($imdb_id) {
		// cache files are created like cache-json/0123456.json
		$cacheFolder = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'imdb-files' . DIRECTORY_SEPARATOR . 'cache-json';
		$cacheFile =  $cacheFolder . DIRECTORY_SEPARATOR . $imdb_id.'.json';
		
		if (file_exists($cacheFile)) {
			$fh = fopen($cacheFile, 'r');
			$cacheTime = trim(fgets($fh));

			// if data was cached recently, return cached data
			if ($cacheTime > strtotime('-24 hours')) {
				return fread($fh, filesize($cacheFile));
			}

			// else delete cache file
			fclose($fh);
			unlink($cacheFile);
		}
		
        if ( ! is_writable($cacheFolder) && ! mkdir($cacheFolder)) {
            throw new Exception('The directory “' . $cacheFolder. '” isn’t writable.');
        }

		$imdb_info = $this->getImdbFromScrapper($imdb_id);
		if($imdb_info) {
			$this->saveInDB ($imdb_info);
		}
		if ($imdb_info == false) return false;
		$json = json_encode($imdb_info);

		$fh = fopen($cacheFile, 'w');
		fwrite($fh, time() . "\n");
		fwrite($fh, $json);
		fclose($fh);

		return $json;
	}
	
    /**
     * Scrapper. gets information from IMDB base class
     *
     * @return mixed array
     */
	private function getImdbFromScrapper($imdb_id) {
		require_once './imdb-files/imdb.class.php';
		$oIMDB = new IMDB('http://www.imdb.com/title/tt'.$imdb_id.'/');
		if ($oIMDB->isReady){
			if ($oIMDB->getTitle() == 'n/A') return false;
			
			$imdb_info= [
				'id'		=> $imdb_id,
				'url'		=> $oIMDB->getUrl(),
				'title'		=> $oIMDB->getTitle(),
				'year'		=> $oIMDB->getYear(),
				'runtime'	=> $oIMDB->getRuntime(),
				'genre'		=> $oIMDB->getGenre(),
				'releaseDate'	=> $oIMDB->getReleaseDate(),
				'rating'	=> $oIMDB->getRating(),
				'votes'		=> $oIMDB->getVotes(),
				'poster'	=> $oIMDB->getPoster('medium', true),
				'trailers'	=> $oIMDB->getTrailerAsUrl(),
				'plot'		=> $oIMDB->getPlot(),
				'director'	=> $oIMDB->getDirector(),
				'directors'	=> $oIMDB->getDirectorAsUrl(),
				'writer'	=> $oIMDB->getWriter(),
				'writers'	=> $oIMDB->getWriterAsUrl(),
				'cast'		=> $oIMDB->getCast(),
				'awards'	=> $oIMDB->getAwards(),
				'plotKeywords'	=> $oIMDB->getPlotKeywords(),
				'mpaa'		=> $oIMDB->getMpaa(),
				'akas'		=> $oIMDB->getAkas(),
				'seasons'	=> $oIMDB->getSeasonsAsUrl(),
				'recommendations'	=> $oIMDB->getRecommendations(),
			];
			return $imdb_info;
		}
		return false;
	}
	
    /**
     * Database handler
     *
     * @return mixed array
     */
	private function saveInDB ($imdb_info) {
		$THIS_BASEPATH=dirname(__FILE__);
		require_once ($THIS_BASEPATH ."/include/conextra.php");
		$con = getConnection();
		$TABLE_PREFIX = getPrefix();
		
		$exists = $this->getResults($con, "SELECT * FROM {$TABLE_PREFIX}imdb_info WHERE id={$imdb_info['id']}");
		// if entry doesn't exist
		
		$id 			= $imdb_info['id'];
		$url 			= $this->htmlSafe( $con, $imdb_info['url']);
		$title 			= $this->htmlSafe( $con, $imdb_info['title']);
		$year 			= $this->htmlSafe( $con, $imdb_info['year']);
		$runtime 		= $this->htmlSafe( $con, $imdb_info['runtime']);
		$genre			= $this->htmlSafe( $con, $imdb_info['genre']);
		$releaseDate 	= $this->htmlSafe( $con, $imdb_info['releaseDate']);
		$rating 		= $this->htmlSafe( $con, $imdb_info['rating']);
		$votes 			= (int) filter_var($imdb_info['votes'], FILTER_SANITIZE_NUMBER_INT);
		$poster 		= $this->htmlSafe( $con, $imdb_info['poster']);
		$plot 			= $this->htmlSafe( $con, $imdb_info['plot']);
		$plot_keywords 	= $this->htmlSafe( $con, $imdb_info['plotKeywords']);
		$mpaa 			= $this->htmlSafe( $con, $imdb_info['mpaa']);
		$aka 			= $this->htmlSafe( $con, $imdb_info['akas']);
		$seasons 		= $this->htmlSafe( $con, $imdb_info['seasons']);
		$directors 		= $this->htmlSafe( $con, $imdb_info['director']);
		$writers 		= $this->htmlSafe( $con, $imdb_info['writer']);
		$cast 			= $this->htmlSafe( $con, $imdb_info['cast']);
		
		if (count($exists)<1) {
			
			$query = "INSERT INTO {$TABLE_PREFIX}imdb_info
				(id, url, title, year, runtime, genre, releaseDate, rating, votes, poster, plot, plot_keywords, mpaa, aka, seasons, directors, writers, cast)
				VALUES
				(\"$id\", \"$url\", \"$title\", \"$year\", \"$runtime\", \"$genre\", \"releaseDate\", \"$rating\", \"$votes\", \"$poster\", \"$plot\", \"$plot_keywords\", \"$mpaa\", \"$aka\", \"$seasons\", \"$directors\", \"$writers\", \"$cast\")";
			$result = $this->executeSql($con, $query);
			return $result;
		} else {
			$query = "UPDATE {$TABLE_PREFIX}imdb_info SET
				url='$url', title='$title', year='$year', runtime='$runtime', genre='$genre', releaseDate='$releaseDate', rating='$rating', votes='$votes', poster='$poster', plot='$plot', plot_keywords='$plot_keywords', mpaa='$mpaa', aka='$aka', seasons='$seasons', directors='$directors', writers='$writers', cast='$cast'
				WHERE id=$id";
			return $this->executeSql($con, $query);
		}
	}
	
	

    /**
     * Database Function
     *
     * @return mixed array
     */
	private static function getResults ($con, $query) {
		$mr=mysqli_query($con, $query);
		
		$return = array();
		if (mysqli_errno($con)!=0) {
			throw new Exception ("MySQL query error! \nError: ".mysqli_error($con)." \nQuery: $query \n");
			unset($mz);
			((mysqli_free_result($mr) || (is_object($mr) && (get_class($mr) == "mysqli_result"))) ? true : false);
			return;
		}
		while ($mz=mysqli_fetch_assoc($mr))
			$return[]=$mz;
		return $return;
	}
	
    /**
     * Database Function
     *
     * @return boolean
     */
	private static function executeSql($con, $query) {
		$return = mysqli_query($con, $query);
		if (mysqli_errno($con)!=0)
			throw new Exception ("MySQL query error! Error: ".mysqli_error($con)." Query: $query ");
		return $return;
	}
	
    /**
     * Sanitizing input for database
     *
     * @return string
     */
	private static function htmlSafe($con, $html) {
		while (1) {
			$html2 = $html;
			$html = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $html); // strip script tags
			
			if ($html2 == $html)
				break;
		}
		$html = trim($html);
		$html = mysqli_real_escape_string($con, $html);
		return $html;
	}
	
    /**
     * Generate View
     *
     * @return full formatted html output
     */
	public function createView($info) {

		$oIMDB = json_decode($info);
	
		echo '<div class="row" id="imdb_block">
			<div class="hero">
				<h1 class="imdb_title"><a href="'.$oIMDB->url .'" title="IMDB Link">'.$oIMDB->title .'</a><span class="titleYear"> ('. $oIMDB->year .')</span></h1>
				<div class="subtext">
					'.$oIMDB->runtime .' <span class="ghost">|</span>
					'. $oIMDB->genre.'  <span class="ghost">|</span>
					'. $oIMDB->releaseDate.' 
				</div>
				<div class="imdbRating">
					<div class="ratingvalue">
						<strong>'. $oIMDB->rating.' </strong>
						<span class="gray">/</span>
						<span class="gray">10</span>
					</div>
					<div class="votes">'. $oIMDB->votes.' </div>
				</div>
			</div>
			<div class="imdbResources">
				<div class="imdbPoster '. ( $oIMDB->trailers != "n/A" ? '' : "noTrailer" ). '">
					<img src="./imdb-files/'. $oIMDB->poster.' " alt="*">
				</div>';
				if($oIMDB->trailers != "n/A") {
					echo '<iframe src="'. $oIMDB->trailers.'imdb/embed?autoplay=false&amp;width=480" width="480" height="270" allowfullscreen="true" mozallowfullscreen="true" webkitallowfullscreen="true" frameborder="no" scrolling="no" __idm_id__="642839553"></iframe>';
				}
			echo '</div>
			<div class="imdbInfoBlock">
				<p class="imdbPlot">'.  $oIMDB->plot.' </p>
				<p><h4 class="inline">Directors: </h4>'. $oIMDB->directors.' </p>
				<p><h4 class="inline">Writers: </h4>'. $oIMDB->writers.' </p>
				<p><h4 class="inline">Awards: </h4>'. $oIMDB->awards.' </p>
				<p><h4 class="inline">Genres: </h4>'. $oIMDB->genre.' </p>
				<p><h4 class="inline">Plot Keywords: </h4>'. $oIMDB->plotKeywords.' </p>
				<p><h4 class="inline">MPAA: </h4>'. $oIMDB->mpaa.' </p>
				<p><h4 class="inline">Also Known As: </h4>'. $oIMDB->akas.' </p>
				<p><h4 class="inline">Seasons: </h4>'. $oIMDB->seasons.' </p>
			</div>';
			if($oIMDB->recommendations != "n/A") {
			echo '<div class="recommendations">
					'. $oIMDB->recommendations.' 
				</div>';
			}
		echo '</div>';
		echo '

	<style>
		.tinystarbar {
			margin-left: auto;
			margin-right: auto;
			line-height: 12px;
			height: 12px;
			width: 101px;
			background: url(./imdb-files/images/starstiny.png) no-repeat 0px -12px;
			text-align: left;
		}
		
		.tinystarbar div {
			margin: 0;
			height: 12px;
			width: 0px;
			background: url(./imdb-files/images/starstiny.png) no-repeat 0px 0px;
		}

		.tinystarbar div {
			margin: 0;
			height: 12px;
			width: 0px;
			background: url(./imdb-files/images/starstiny.png) no-repeat 0px 0px;
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
			background: url(./imdb-files/images/sprites.png) no-repeat;
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
			margin-right: 4px;
			margin-top: 2px;
			margin-left: 1px;
		}
		#imdb_block .imdbPoster {
			float: left;
		}
		#imdb_block .imdbPoster.noTrailer {
			margin-right: 20px;
		}
		#imdb_block .imdbInfoBlock {
			padding: 18px 20px;
			font-size: 13px;
			font-family: Verdana, Arial, sans-serif;
			color: #333;
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
	</style>';
	}
}
		
?>