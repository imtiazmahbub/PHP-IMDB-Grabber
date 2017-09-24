# PHP IMDb.com Grabber for XBTIT

**This PHP library enables you to scrap data from IMDB.com.**

*Compatible with the current version of XBTIT (2.5.4 beta)*

*The script is a proof of concept. It’s mostly working, but you shouldn’t use it. IMDb doesn’t allow this method of data fetching. I personally do not use or promote this script, It's your own responsibility if you’re using it.*

The technique used is called “[web scraping](http://en.wikipedia.org/wiki/Web_scraping "Web scraping at Wikipedia")”. This means, if IMDb changes anything within their HTML source, the script is most likely going to fail. I won’t update this regularly, so don’t count on it to be working all the time.

## License

[The MIT License (MIT)](http://imtiazmahbub.mit-license.org/ "The MIT License")

## Preview

_Basic Style without loading from XBTIT_

![Screenshot Preview](https://preview.ibb.co/i56o8k/localhost_xbtit_master_php_imdb_grabber_get_Info_php_imdb_id_0073486.png)

How it looks in torrent details:

![Screenshot in torrent details page](https://preview.ibb.co/j9cNZQ/XBTIT_Index_Torrent_Details.png)

## Usage

See all methods in the original repository it was forked from:
[FabianBeiner/PHP-IMDB-Grabber](https://github.com/FabianBeiner/PHP-IMDB-Grabber)
I've added a new method to grab recommended Movies/Items.
Here's how to use it:

**Recommendations**

`$getRecommendations()`
```
include_once './imdb.class.php';

$oIMDB = new IMDB('http://www.imdb.com/title/tt'.$imdb_id.'/');
if ($oIMDB->isReady) {
    echo $oIMDB->getRecommendations();
}
```
## Usage with XBTIT

I'll add a modification.xml file to help you install it using XBTIT's hack installer soon

## Bugs?

If you run into any malfunctions, feel free to submit an issue. Make sure to enable debugging: `const IMDB_DEBUG = true;` in `imdb.class.php`.
