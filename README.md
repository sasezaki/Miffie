Miffie - scraping cli tool powered by php phar
==============================================

## Installation
wget [https://raw.github.com/sasezaki/Miffie/master/miffie.phar](https://raw.github.com/sasezaki/Miffie/master/miffie.phar)


## Usage

    $php miffie.phar [ options ]
    --xpath|-x <string>    expression xpath or css selector
    --type|-v <string>     val type
    --referer|-e <string>  referer
    --agent|-a <string>    useragent
    --nextlink|-n <string> nextlink
    --depth|-d <integer>   depth "if not set nextlink, using wedata"
    --basic|-b <string>    basic auth "user/pass"
    --cache|-h             cache with Zend\Cache
    --noCache|-r           no-cache-force
    --wait|-w <string>     sleep() :default 1
    --filter|-f <string>   filter for Diggin\Scraper
    --out|-o <integer>     timeout
    --helper|-l <string>   helper

## License

Miffie is licensed under LGPL.

## THANKS
Miffie inspired by [exthtml](http://web.archive.org/web/20081030041338/http://fuba.jottit.com/exthtml)
