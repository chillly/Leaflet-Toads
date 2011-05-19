<?php
/*
 * ajxToad.php
 *      
 * Copyright (c) 2011, Chris Hill
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or 
 * without modification, are permitted provided that the following
 * conditions are met:
 *
 *  * Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 *  * Redistributions in binary form must reproduce the above
 *    copyright notice, this list of conditions and the following
 *    disclaimer in the documentation and/or other materials provided
 *    with the distribution.
 *  * Neither the name of raggedred.net nor the names of its 
 *    contributors may be used to endorse or promote products derived
 *    from this software without specific prior written permission.

 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

include "../include/db.inc";

// check what is passed
$bbox=$_GET['bbox'];

// split the bbox into it's parts
list($left,$bottom,$right,$top)=explode(",",$bbox);

$chconn = mysql_connect('localhost', $dbuser, $dbpass) or die(mysql_error());
mysql_select_db($dbname) or die(mysql_error());
$query=sprintf("SELECT * FROM toad WHERE lon>='%1.7f' AND lon<='%1.7f' AND lat>='%1.7f' AND lat<='%1.7f'",$left,$right,$bottom,$top);
$restoad = mysql_query($query);

// if there are no rows returned carry on, an empty returned array is OK.

$toads=array();

while ($row = mysql_fetch_array($restoad)) {
    $t=array();
    $t['name']=$row["toadname"];
    $t['lon']=$row["lon"];
    $t['lat']=$row["lat"];
    $t['artist']=$row["artist"];
    $t['designer']=$row["designer"];
    $t['sponsor']=$row["sponsor"];

    $picquery="SELECT photo FROM toadpiclist t JOIN toadpics p on t.picid=p.picid where t.toadid='{$row["toadid"]}'";
    $respic=mysql_query($picquery);

    if (mysql_num_rows($respic)<1) {
        $t['pic']="";
    }
    else {
        $picrow=mysql_fetch_array($respic);
        $t['pic']=$picrow["photo"];
    }
    
    $toads[]=$t;
}
// tidy up the DB 
mysql_free_result($respic);
mysql_free_result($restoad);
mysql_close($chconn);

// encode the toads array as json and return it. toads can be empty
$encoded = json_encode($toads,JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP);
exit($encoded);

?>
