<?php
/*
 * I B A U K - peventsedit.php
 *
 * This is the SQLITE version
 * 
 * 
 * Copyright (c) 2025 Bob Stammers
 *
 */

require_once("general.conf.php");
require_once("db.conf.php");

$FULL_SQL = "SELECT rowid AS eventid,startdate,eventdesc,eventtype,coalesce(finishdate,'') AS finishdate FROM events ORDER BY startdate DESC";
$EVENT_TYPES = [1=>"RTE",2=>"Ride",3=>"Rally",4=>"Event"];
?>
<script>
    function showid(tr) {
        //alert('Event #'+tr.id);
        window.location.href = "/index.php?c=events&eid="+tr.id;
    }
</script>
<style>
    .row {
  display: flex;
  flex-direction: row;
  flex-wrap: wrap;
  width: 100%;
}

.column {
  display: flex;
  flex-direction: column;
  flex-basis: 100%;
  flex: 1;
}

    #eventlist tr:hover     { background-color: lightblue; color: black; }
    #singlevent             { max-width: 300px; }
    label.column            { text-align: right; padding-right: 1em; }
    #singlevent input       { flex-grow: 1; }
</style>
<?php

function delete_event() {

    $eid = $_REQUEST['eid'];
    if ($eid < 1) return;
    $sql = "DELETE FROM events WHERE rowid=$eid AND startdate='".$_REQUEST['startdate']."'";
    sql_query($sql);
}

function save_event() {
    global $EVENT_TYPES;
    $eid = 0;
    if (isset($_REQUEST['eid'])) {
        $eid = $_REQUEST['eid'];
    }
    if ($eid > 0) {
        $sql = "UPDATE events SET startdate='".$_REQUEST['startdate']."'";
        $sql .= ",eventdesc='".safesql($_REQUEST['eventdesc'])."'";
        $sql .= ",forumurl='".safesql($_REQUEST['forumurl'])."'";
        $sql .= ",urltarget='".safesql($_REQUEST['urltarget'])."'";
        $sql .= ",eventtype=".intval($_REQUEST['eventtype']);
        $sql .= ",finishdate='".$_REQUEST['finishdate']."'";
        $sql .= " WHERE rowid=".$eid;
        sql_query($sql);
    } else {
        $ed = $_REQUEST['eventdesc'];
        $et = $EVENT_TYPES[$_REQUEST['eventtype']];
        if (!str_contains(strtolower($ed),strtolower($et))) {
            $ed .= ' '.$et;
        }
        $sql = "SELECT forumurl FROM events WHERE startdate='".$_REQUEST['startdate']."' AND eventdesc='".safesql($ed)."'";
        $rs = sql_query($sql);
        $rd = $rs->fetchArray();
        if (!$rd) {
            $sql = "INSERT INTO events (startdate,eventdesc,forumurl,urltarget,eventtype,finishdate) VALUES(";
            $sql .= "'".$_REQUEST['startdate']."'";
            $sql .= ",'".safesql($ed)."'";
            $sql .= ",'".safesql($_REQUEST['forumurl'])."'";
            $sql .= ",'".safesql($_REQUEST['urltarget'])."'";
            $sql .= ",".intval($_REQUEST['eventtype']);
            $sql .= ",'".$_REQUEST['finishdate']."'";
            $sql .= ")";
            sql_query($sql);
        }
    }
}
function show_event() {
    global $EVENT_TYPES;
    $eid = 0;
    if (isset($_REQUEST['eid'])) {
        $eid = $_REQUEST['eid'];
    }
    if ($eid > 0) {
        $sql = "SELECT * FROM events WHERE rowid=".$eid;
        $rs = sql_query($sql);
        $rd = $rs->fetchArray();
        if ("".$rd['finishdate'] == "") {
            $rd['finishdate'] = $rd['startdate'];
        }
    } else {
        $rd = defaultRecord("events");
    }
    echo('<form id="singleevent" method="post">');
    echo('<input type="hidden" name="c" value="events">');
    echo('<input type="hidden" name="eid" value="'.$eid.'">');
    echo('<div class="row"><label class="column" for="eventtype">Event type</label>');
    echo('<select id="eventtype" class="column" name="eventtype">');
    foreach($EVENT_TYPES as $n=>$x) {
        echo('<option value="'.$n.'"');
        if ($n == $rd['eventtype']) {
            echo(' selected');
        }
        echo('>'.$x.'</option>');
    }
    echo('</select></div>');
    echo('<div  class="row"><label class="column" for="startdate">Event date (start)</label>');
    echo('<input type="date" autofocus class="column" id="startdate" name="startdate" value="'.$rd['startdate'].'"');
    echo(' oninput="'."document.querySelector('#finishdate').value=this.value;".'"');
    echo('></div>');
    echo('<div  class="row"><label class="column" for="finishdate">Until date (finish)</label>');
    echo('<input type="date" class="column" id="finishdate" name="finishdate" value="'.$rd['finishdate'].'"></div>');
    echo('<div class="row"><label class="column" for="eventdesc">Description (RTE/ride/etc)</label>');
    echo('<input type="text" class="column" id="eventdesc" name="eventdesc" value="'.$rd['eventdesc'].'"></div>');
    echo('<div class="row"><label class="column" for="forumurl">URL to event details (forum?)</label>');
    echo('<input type="text" class="column" id="forumurl" name="forumurl" value="'.$rd['forumurl'].'"></div>');
    echo('<div class="row" style="display:none;"><label class="column" for="urltarget">Target window name or blank</label>');
    echo('<input type="text" class="column" id="urltarget" name="urltarget" value="'.$rd['urltarget'].'"></div>');
    echo('<div class="row"><span class="column"></span>');
    if ($eid > 0) {
        echo('<input type="submit" name="killme" value="Delete this record"> ');
    }
    echo('<input class="column" type="submit" name="saveme" value="Save changes"></div>');
    echo('</form>');
}
function show_event_table() {

    global $FULL_SQL;
    
    echo('<table id="eventlist"><thead></thead><tbody>');
    $rs = sql_query($FULL_SQL);
    
    echo('<tr id="0" onclick="showid(this)" style="cursor:crosshair;"><td>+</td><td></td></tr>');
    while(true) {
        $rd = $rs->fetchArray();
		if ($rd == false) break;
        echo('<tr id="'.$rd['eventid'].'" onclick="showid(this)" style="cursor:pointer;">');
        echo('<td>'.colFmtDate($rd['startdate']).'</td>');
        echo('<td>'.$rd['eventdesc'].'</td>');
        echo('</tr>');

    }
    echo('</tbody></table>');
}

//var_dump($_REQUEST);
start_html("Events");
if (isset($_REQUEST['killme']) && isset($_REQUEST['eid'])) {
    delete_event();
    show_event_table();
} else if (isset($_REQUEST['saveme']) && isset($_REQUEST['eid'])) {
    save_event();
    show_event_table();
} else if (isset($_REQUEST['eid'])) {
    show_event();
} else {
    show_event_table();
}
?>
