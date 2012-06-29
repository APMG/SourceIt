<h1>Test Response</h1>
<pre>
<?php 
    print_r($_SERVER);
    print_r($test);
    if (isset($test['exception'])) {
        throw new Exception($test['exception']);
    }
?>
</pre>
