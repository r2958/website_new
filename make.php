<?php
 
ini_set('error_reporting','E_ALL & ~E_NOTICE');
ini_set('display_errors',1);

ob_start();
  readfile('/usr/local/Zend/etc/php.ini');
  // use @ to hide 'nothing to flush' errors
  @ob_flush();

  $content = ob_get_contents();
 ob_end_clean();
?>