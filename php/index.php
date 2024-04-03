<?php
  $env = getenv('env');
  echo 'env: ' . ($env !== false ? $env : 'not set');
?>