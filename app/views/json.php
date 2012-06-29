<?php
if (!isset($json)) {
    throw new Exception("json var not defined");
}
echo json_encode($json);
?>
