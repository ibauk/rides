<?php

/*
 * I B A U K - update.php
 *
 *
 * Copyright (c) 2016 Bob Stammers
 * 
 *
 */



function putsql($SQL)
{
    if (TRUE)
        sql_query($SQL);
    else
        echo("<p>$SQL</p>");
}


if (!$_SESSION['UPDATING'])
    include("login.php");


include("search.php");
?>
