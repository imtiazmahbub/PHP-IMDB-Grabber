<?php

class imdbInfo {
	
	public function makeJson($imdb_id) {
		// cache files are created like cache-json/0123456.json
		$cacheFile = 'cache-json' . DIRECTORY_SEPARATOR . $imdb_id.'.json';
		
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
		
        if ( ! is_writable(dirname(__FILE__) . '/cache-json') && ! mkdir(dirname(__FILE__) . '/cache-json')) {
            throw new Exception('The directory “' . dirname(__FILE__) . '/cache-json” isn’t writable.');
        }

		$imdb_info = $this->getImdbFromScrapper($imdb_id);
		if ($imdb_info == false) return false;
		$json = json_encode($imdb_info);

		$fh = fopen($cacheFile, 'w');
		fwrite($fh, time() . "\n");
		fwrite($fh, $json);
		fclose($fh);

		return $json;
	}
	
	private function getImdbFromScrapper($imdb_id) {
		include_once './imdb.class.php';
		$oIMDB = new IMDB('http://www.imdb.com/title/tt'.$imdb_id.'/');
		if ($oIMDB->isReady){
			if ($oIMDB->getTitle() == 'n/A') return false;
			
			$imdb_info= [
				'url'		=> $oIMDB->getUrl(),
				'title'		=> $oIMDB->getTitle(),
				'year'		=> $oIMDB->getYear(),
				'runtime'	=> $oIMDB->getRuntime(),
				'runtime'	=> $oIMDB->getRuntime(),
				'genre'		=> $oIMDB->getGenre(),
				'releaseDate'	=> $oIMDB->getReleaseDate(),
				'rating'	=> $oIMDB->getRating(),
				'votes'		=> $oIMDB->getVotes(),
				'poster'	=> $oIMDB->getPoster('medium', true),
				'trailers'	=> $oIMDB->getTrailerAsUrl(),
				'plot'		=> $oIMDB->getPlot(),
				'directors'	=> $oIMDB->getDirectorAsUrl(),
				'writers'	=> $oIMDB->getWriterAsUrl(),
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
}
		
?>