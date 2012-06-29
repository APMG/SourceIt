<?php
if (!isset($jsonp)) {
    throw new Exception("jsonp var not defined");
}
echo $jsonp["callback"];
echo "(";
echo json_encode($jsonp);
echo ");";
