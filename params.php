<?php
/*
 * B O X E S - params.php
 *
 * Copyright (c) 2016 Bob Stammers
 *
 */

function show_params()
{
    start_html("Parameters");

    $SQL  = "SELECT location FROM locations";
    $r = sql_query($SQL);

    echo("<p>The settings you choose here will be used to restrict searches until you reset them or until your session ends.</p>");
    echo("<p>When choosing from a list, use the [Ctrl] key to make multiple choices.</p>");

    echo("<form name=\"PARAMS\" action=\"index.php\">\n");
    echo("<input type=\"hidden\" name=\"CMD\" value=\"PARAMS\">\n");
    echo("<table border=\"1\" summary=\"Parameters\">\n");
    echo("<tr>\n");

if(TRUE)
{
    echo("<td>Locations : </td>");
    echo("<td>");
    echo("<input type=\"radio\" name=\"LOCNS\" id=\"LOCNSALL\" value=\"ALL\" checked=\"checked\"> <label for=\"LOCNSALL\">Include ALL</label><br />");
    echo("<input type=\"radio\" name=\"LOCNS\" id=\"LOCNSSOME\" value=\"SOME\"> <label for=\"LOCNSSOME\">SOME (see right)</label>");
    echo("</td>");
    echo("<td><select multiple=\"multiple\" name=\"locations[]\" onclick=\"document.PARAMS.LOCNS[1].checked=true;\">");
    while(TRUE)
    {
        $rr = mysqli_fetch_assoc($r);
        if ($rr==FALSE) break;
        echo("<option value=\"".$rr['location']."\">".$rr['location']."</option>\n");
    }
    echo("</select></td>");
    echo("</tr>\n");
}
    $SQL  = "SELECT DISTINCT owner FROM contents ";
    $SQL .= "GROUP BY owner ";
    $r = sql_query($SQL);


    echo("<tr>\n");
    echo("<td>Partners : </td>");
    echo("<td>");
    echo("<input type=\"radio\" name=\"PTNRS\" id=\"PTNRSALL\" value=\"ALL\" checked=\"checked\"> <label for=\"PTNRSALL\">Include ALL</label><br />");
    echo("<input type=\"radio\" name=\"PTNRS\" id=\"PTNRSSOME\" value=\"SOME\"> <label for=\"PTNRSSOME\">SOME (see right)</label>");
    echo("</td>");
    echo("<td><select multiple=\"multiple\" name=\"partners[]\" onclick=\"document.PARAMS.PTNRS[1].checked=true;\">");
    while(TRUE)
    {
        $rr = mysqli_fetch_assoc($r);
        if ($rr==FALSE) break;
        echo("<option value=\"".$rr['owner']."\">".$rr['owner']."</option>\n");
    }
    echo("</select></td>");
    echo("</tr>\n");

    echo("</table>");
    echo("<input type=\"submit\" value=\"Set params\">");
    echo("</form>");

    echo("</body></html>");
}

function set_params()
{
    global $QUERY_STRING;

    parse_str($QUERY_STRING);
    if ($_GET['locns'] <> '') $_SESSION['WHERE_LOCNS'] = $_GET['locations'];
    if ($_GET['ptnrs'] <> '') $_SESSION['WHERE_PTNRS'] = $_GET['partners'];
}



if ($_GET['locns'] <> '' or $_GET['ptnrs'] <> '')
{
    set_params();
    include("search.php");
    exit;
}

show_params();


?>
