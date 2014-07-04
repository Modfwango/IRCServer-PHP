<?php
  define("__PROJECTROOT__", dirname(__FILE__));

  chdir(__PROJECTROOT__);
  define("__CHANNELMODES__", "z");
  define("__CHANNELMODESWITHPARAMS__", "l");
  define("__PROJECTVERSION__", "IRCServer-PHP-".substr(shell_exec(
    "git rev-parse HEAD"), 0, 7));
  define("__NETNAME__", "PHPNet");
  define("__MOTD__", "Hello!\nThis is a test.");
  define("__SERVERDOMAIN__", "php.clayfreeman.com");
  define("__USERMODES__", "z");
  require_once(__PROJECTROOT__."/.modfwango/main.php");
?>
