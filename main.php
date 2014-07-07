<?php
  define("__TIMEZONE__", "America/Chicago");
  define("__PROJECTROOT__", dirname(__FILE__));

  chdir(__PROJECTROOT__);
  define("__PROJECTVERSION__", "IRCServer-PHP-".substr(shell_exec(
    "git rev-parse HEAD"), 0, 7));
  define("__NETNAME__", "PHPNet");
  define("__MOTD__", "Hello!\nThis is a test.");
  define("__PINGTIME__", 120);
  define("__SERVERDOMAIN__", "php.clayfreeman.com");
  require_once(__PROJECTROOT__."/.modfwango/main.php");
?>
