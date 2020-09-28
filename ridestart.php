<?php
/*
 * I B A U K - ridestart.php
 *
 * This is the SQLITE version
 * 
 * 
 * Copyright (c) 2020 Bob Stammers
 *
 */

 function showRiderlist()
 {
	 $MAX_RIDERS = 50;
	 
	$OK = ($_SESSION['ACCESSLEVEL'] >= $GLOBALS['ACCESSLEVEL_UPDATE']);
	
	if (!$OK) safe_default_action();
	
	 $nameornum = safesql($_REQUEST['nameornum']);
	 
	 if ($nameornum=='') {
		 $nameornum = $_REQUEST['f'];
		 if ($nameornum=='')
			return FALSE;
	 }
	 $sql = "SELECT riders.riderid AS RiderID,Rider_Name,IBA_Number,IsPillion,bikeid,Bike,Registration FROM riders  JOIN bikes ON riders.riderid=bikes.riderid WHERE ";
	 $sql .= " Rider_Name LIKE '%".$nameornum."%' OR IBA_Number='".$nameornum."' LIMIT $MAX_RIDERS";
	 if ($_REQUEST['debug']=='sql') echo($sql."<hr>");
	 $riderlist = sql_query($sql);
	 $totrows = countrecs($riderlist);
	 start_html("New ride: Choose rider");
	 echo("<div class=\"maindata\">");
	 echo("<table><caption>New ride: Choose rider</caption><tr><th></th><th>Name</th><th>IBA</th><thPillion?</th><th>Bike</th><th>Reg</th></tr>\n");
	 echo("<form action=\"index.php\" method=\"get\"><input type=\"hidden\" name=\"c\" value=\"newride\">\n");
	 $ix = 0;
	 $checked = 'checked';
	 $lriderid = '';
	 while(TRUE)
	 {
		 $rd = $riderlist->fetchArray();
		 if ($rd == FALSE) break;
		 $xx = ($ix % 2 == 0 ? '1' : '2'); 
		 echo("<tr onclick=\"document.getElementById('ix$ix').checked=true;\" class=\" goto row-");
		 echo($xx);
		 echo("\">");
		 $riderid = $rd['RiderID'];
		 if ($lriderid=='') $lriderid = $riderid;
		 $bikeid = $rd['bikeid'];
		 $ridername = $rd['Rider_Name'];
		 $ibanumber = $rd['IBA_Number'];
		 $bike = $rd['Bike'];
		 $registration = $rd['Registration'];
		 echo("<td><input type=\"radio\" $checked name=\"ix\" id=\"ix$ix\" value=\"$ix\"><input type=\"hidden\" name=\"rid[]\" value=\"".$riderid."\"><input type=\"hidden\" name=\"bik[]\" value=\"".$bikeid."\"></td>");
		 $checked = '';
		 $ix++;
		 echo("<td>".$ridername."</td>");
		 echo("<td>".$ibanumber."</td>");
		 echo("<td>".$bike."</td>");
		 echo("<td>".$registration."</td>");
		 echo("</tr>\n");
	 }
	 //echo ("TR=$totrows; LR=$lriderid; RI=$riderid<hr>");
	 if ($totrows > 0 && $lriderid==$riderid) // Exactly one rider found
	 {
		// Same rider, new bike
		$xx = ($ix % 2 == 0 ? '1' : '2'); 
		echo("<tr onclick=\"document.getElementById('ix$ix').checked=true;\" class=\"goto row-");
		echo($xx);
		echo("\">");
		echo("<td><input type=\"radio\" name=\"ix\" id=\"ix$ix\" value=\"$ix\"><input type=\"hidden\" name=\"rid[]\" value=\"".$riderid."\"><input type=\"hidden\" name=\"bik[]\" value=\"new\"></td>");
		$ix++;
		echo("<td>".$ridername."</td>");
		echo("<td>".$ibanumber."</td>");
		echo("<td> &lt;new bike&gt; </td>");
		echo("<td></td>");
		echo("</tr>\n");
	 }
	 // New rider, new bike
	 $xx = ($ix % 2 == 0 ? '1' : '2'); 
	 echo("<tr onclick=\"document.getElementById('ix$ix').checked=true;\" class=\"goto row-");
	 echo($xx);
	 echo("\">");
	 echo("<td><input type=\"radio\" $checked name=\"ix\" id=\"ix$ix\"  value=\"$ix\"><input type=\"hidden\" name=\"key\" value=\"$nameornum\"><input type=\"hidden\" name=\"rid[]\" value=\"new\"><input type=\"hidden\" name=\"bik[]\" value=\"new\"></td>");
	 $checked = '';
	 $ix++;
	 echo("<td> &lt;new rider&gt; </td>");
	 echo("<td></td>");
	 echo("<td> &lt;new bike&gt; </td>");
	 echo("<td></td>");
	 echo("</tr>\n");
	 echo("<tr><td colspan=\"6\"><input type=\"submit\" value=\"Enter ride details\"></td></tr></form></table>");
	 echo("</div></body></html>");
	 return TRUE;
 }
 
?>
