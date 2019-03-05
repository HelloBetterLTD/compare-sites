# Compare Sites


Compare Sites is a comparison tool which helps to match two sites together. It takes two site URLs and goes through the site which the second needs to be checked against, 
and crawl all the internal links and build a list. Then it connects to the second site and pings all the URLs and grab the output. 

Then it checks for the server's response code and takes screenshots of each of the items and compare them against each other for differences. 

# Installation

1. Just download the phar file and run
2. Clone the project and run the command `php bin/console run [SITE] [COMPARE_AGAINST] [PATH_TO_SAVE] [DEPTH]`

## Install `puppeteer` 

The tools uses puppeteer to generate screenshots for each of the page. Make sure you have installed it. 
[https://www.npmjs.com/package/puppeteer](https://www.npmjs.com/package/puppeteer)
 