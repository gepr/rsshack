<?php
/*
 * Code adapted from Kevin Yank's article on PHP and RSS 1.0 from 
 * Sitepoint.  See URL http://www.webmasterbase.com/article/560
 *
 * Primary local modifications were to create an array of all stories
 * rather than printing them out one as a time as they are encountered
 * [The original author] also made some changes in formatting and
 * created slightly prettier error report
 *
 * Script will not display any information from the channel tag.  It
 * only displays headlines from the item tags
 *
 * 2014-10-22 gepr@tempusdictum.com
 *  - stolen from http://www.utexas.edu/learn/rss/includes.html
 *
 */
// global variable initialization
$insideitem = false;  // set to true when the parser is inside an item tag
$tag = "";   // name of tag you are in

$title = "";  // title of item
$description = "";  // description of item
$link = "";  // url for item
$numitems = 0;  // number of items

/*
 * Function:       parseRssFile($xml_parser, $fp, $errorurl, $errortitle) 
 * Description:    parses an rss file and does some checking for & characters 
 * Arguments:      $xml_parser pointer to XML parser
 * $fp file pointer
 * $errorurl path to error url as string
 * $errortitle an error title as string
 * Returns:        nothing
 *
 */
function parseRssFile($xml_parser, $fp, $errorurl, $errortitle) {
  while ($data = fread($fp, 4096)) {
    $data = ereg_replace("&", "&amp;", $data);
    if (!xml_parse($xml_parser, $data, feof($fp))) {
      // print error string and clear variables
      print "<a href=\"$errorurl\">$errortitle</a>";
      $title = "";
      $link = "";
      $description = "";
    }
  }
  // close the file handle
  fclose($fp);
}


//  Change this to the URL of your RSS file 
$rssurl = "http://blog.tempusdictum.com/index.php/feed/";


// if there is a parse error display the following generic 
// title and link rather than a cryptic numeric XML error
$errortitle = "Web Log";
$errorurl = "http://blog.tempusdictum.com/";

/*
 * startElement is function called when the parser encounters a new
 * element. Ignores starting elements until it finds an ITEM element
 */
function startElement($parser, $name, $attrs) {
  global $insideitem, $numitems, $tag, $title, $description, $link;
  if ($insideitem) {
    $tag = $name;
  } elseif ($name == "ITEM") {
    // we are now inside an item tag so set $insideitem to true and increment $numitems
    $insideitem = true;
    $numitems++;
  }
}

/*
 * endElement is function called when the parser encounters an ending element 
 * endElement just needs to reset $insideitem variable when it sees 
 * </item> because the parser is no longer inside an item tag
 */
function endElement($parser, $name) {
  global $insideitem, $numitems, $tag, $title, $description, $link;
  if ($name == "ITEM") { $insideitem = false; }
}

/*
 * characterData is where the work is done 
 * when we are inside an ITEM element build an array of headlines 
 * with links and descriptions
 */
function characterData($parser, $data) {
  global $insideitem, $numitems, $tag, $title, $description, $link;

  // if we are inside an item tag build up the arrays of headline information
  if ($insideitem) {
    switch ($tag) {
    case "TITLE":
      $title[$numitems] .= $data;
      break;
    case "DESCRIPTION":
      $description[$numitems] .= $data;
      break;
    case "LINK":
      $link[$numitems] .= $data;
      break;
    }
  }
}


// Create parser and register functions
$xml_parser = xml_parser_create();
xml_set_element_handler($xml_parser, "startElement", "endElement");
xml_set_character_data_handler($xml_parser, "characterData");

// open rss file
$fp = fopen("$rssurl","r")
or die("Error reading RSS data.");

/*
 * do the parsing
 * note that if there is an error it will display the $errortitle set 
 * above with a link or $errorurl
 */
parseRssFile($xml_parser, $fp, $errorurl, $errortitle); xml_parser_free($xml_parser);

/*
 * If parsing was successful we have three arrays $headlines contains
 * the headline text $link contains the URLs for the headlines
 * $description contain the descriptions for each headline
 *
 * The following for loop will print elements from the array Several
 * modifications can be made to the loop The example below will only
 * print the first 6 headlines from the RSS file (6 elements from the
 * array)
 *
 * if you want to print all elements from the array change $j<=6 to
 * $j<=count($title)
 *
 * Also the for loop below does not print descriptions If your channel
 * also contains descriptions you might want to print them as well by
 * trying the following print statement in the loop instead of the one
 * that is there:
 *
 * print "<p><a href=\"$link[$j]\">$title[$j]</a><br>$description[$j]</p>\n";
 *
 * unroll array printing out links to headlines
 */

print("<table height=\"100%\" border=\"1\" bordercolorlight=\"#3a3635\" bordercolordark=\"#000000\">\n");
$mintitle = min(count($title), 15);
for ($j=1; $j<=$mintitle; $j++) {
  print "<tr><td><a href=\"$link[$j]\">$title[$j]</a></td></tr>\n";
}
print("</table>\n");
?>


