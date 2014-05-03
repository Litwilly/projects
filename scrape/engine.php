<?php 
/************************************************
 engine.php - HTML Scrapper and DB Populator
************************************************/

 	// Open a MySQL connection
	include("db_connect_test.php");
	// Include the Mail package
	include("sendMail.php");
	// import simple_html_dom.php to give me various methods for website selection and scraping
	include("simple_html_dom.php");
	
/**************************************************************************
  makePage() function - HTML DOM Work horse
  	Scrapes, formats, populates, and calls sendMail() on new entries
***************************************************************************/
function makePage($html)
{	
	$link_number = -1;//will use this as a new array key number in a foreach
    $meta_array = array();//making sure there is an array when functions call upon it to eliminate those errors
    foreach($html->find('td.forum_listing') as $e) {
    
        foreach($e->find('p.meta') as $f){//find all meta id's
        $meta = $f->plaintext;
        //echo $meta;
        $explode_meta = explode(" ",$meta);//explode by spaces and make an array        
        
        if(!ctype_digit($explode_meta[0])){//only if array value content does not have just positive whole numbers
        $link_number++;//adds 1 to array key number, same as $link_number = $link_number + 1;
 
        //build a new array by key number value and name associations
        $meta_array[$link_number] = array(
        "date" => $explode_meta['0'],
        "time" => $explode_meta['1'],
        "timezone" => str_replace("by","",$explode_meta['2']),
        "username_first" => $explode_meta['3'],
        "username_last" => $explode_meta['4']
        );
        }
        }
        
    }

// find all td tags with class=forum_listing
$link_number = -1;//reset number
    foreach($html->find('td.forum_listing') as $tdTagExt){
        foreach($tdTagExt->find('a')as $aTagExt){
        $link_number++;//add by one again
            $title = $aTagExt->plaintext;
            $href = "http://www.backpackinglight.com".$aTagExt->href;
            //associating the link and title in a new array
            $meta_array[$link_number]['title'] = $title;
            $meta_array[$link_number]['href'] = $href;
        }
    }
    
//print_r($meta_array)."<br />";//to see how the array looks    
	
	//get the current number of rows before insert
    $result = mysql_query("select * from bp");
    $number_of_rows_before = mysql_num_rows($result);

    //access the new links array
    foreach($meta_array as $link){
	    echo "<p>";
	    echo "<a href='".$link['href']."' target='_blank'>".$link['title']."</a><br />";
	    echo " Date: ".$link['date']." Time: ".$link['time']." ".$link['timezone']."<br />"; //Posted by: ".$link['username_first']." ".$link['username_last']."<br />";
	    echo "</p>";
	    
		    //convert array to string for db
		    $link_href = $link['href'];
		    $link_title = $link['title'];
		    $link_date = $link['date'] . " " . $link['time'];
		   
		    //populate db
		    mysql_query("insert into `bp` (`url`,`title`,`date`) values ('$link_href','$link_title','$link_date')");
    } 
		    
		$result_after = mysql_query("select * from bp");
		$number_of_rows_after = mysql_num_rows($result_after);
		//Line below assists with testing if-statement condition
		//echo "Number of rows fetched : ". $number_of_rows_before . "<br />" . "Number of rows fetched are : ". $number_of_rows_after. "<br /><br />";
		
	  	
  	// send myself email after every new db entry
    if($number_of_rows_after>$number_of_rows_before) 
    	{
    	sendMail();
    	}
}

// get DOM from BPL URL
$html0 = file_get_html('http://www.backpackinglight.com/cgi-bin/backpackinglight/forums/display_forum.html?forum=19');
$html1 = file_get_html('http://www.backpackinglight.com/cgi-bin/backpackinglight/forums/display_forum.html?offset=25&forum=19');

// Run function for last two pages of listings
makePage($html0);
makePage($html1);

// Close the connection
mysql_close($link);

?>