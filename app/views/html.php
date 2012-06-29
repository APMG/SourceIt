<?php
// setup vars
    if (!isset($html['head'])) {
        $html['head'] = array();
    }
    if (!isset($html['head']['title'])) {
        $html['head']['title'] = 'Internet Fact Database';
    }
    if (!isset($html['head']['js'])) {
        $html['head']['js'] = array();
    }
    if (!isset($html['head']['css'])) {
        $html['head']['css'] = array();
    }
    if (!isset($html['head']['misc'])) {
        $html['head']['misc'] = '';
    }
    if (!isset($html['body'])) {
        $html['body'] = '';
    }
    if (!isset($html['foot'])) {
        $html['foot'] = array();
    }
    if (!isset($html['foot']['show'])) {
        $html['foot']['show'] = true;
    }
    if (!isset($html['foot']['js'])) {
        $html['foot']['js'] = array();
    }
    if (!isset($html['foot']['inline_js'])) {
        $html['foot']['inline_js'] = '';
    }
?>
<!DOCTYPE html
	PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en-US" xml:lang="en-US">
 <head>
  <title><?php echo $html['head']['title'] ?></title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />


<?php
 // render external js tags
 foreach ($html['head']['js'] as $uri) {
 ?>
  <script type="text/javascript" src="<?php echo 'js/'.$uri ?>.js"></script>
<?php
 }
 // render link tags
 foreach ($html['head']['css'] as $uri) {
 ?>
  <link rel="stylesheet" href="<?php echo 'css/'.$uri ?>.css" />
<?php
 }
 
 // anything else
 echo $html['head']['misc'];
?>

 </head>
 <body>
 <?php echo $html['body'] ?>
<? foreach ( $html['foot']['js'] as $uri ) { ?>
    <script type="text/javascript" src="<?= 'js/'.$uri ?>"></script>
<? } ?>
<?= $html['foot']['inline_js'] ?>
<? if ( isset( $html['foot']['ready'] ) ) { ?>
    <script type="text/javascript">
     <? if ( is_array( $html['foot']['ready'] ) ) {
            foreach ($html['foot']['ready'] as $r) { ?>
                $(document).ready(<?= $r ?>);
         <? }
        }
        else { ?>
            $(document).ready( <?= $html['foot']['ready'] ?> );
     <? } ?>
    </script>
<? } ?>
 </body>
</html>
