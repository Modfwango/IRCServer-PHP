<?php
  class @@CLASSNAME@@ {
    public $depend = array("NSClient");
    public $name = "NSLOGOUT";

    public function receiveNickServCommand($name, $data) {
      $source = $data[0];
      $target = $data[1];
      $params = $data[2];

      if (is_string($source->getOption("loggedin"))) {
        // Logged in.
        $source->setOption("loggedin", false);
        $source->send(":".$target->getOption("nick")."!".
          $target->getOption("ident")."@".$target->getHost()." ".
          "PRIVMSG ".$source->getOption("nick")." :You have been logged out.");
      }
      else {
        // Not logged in.
        $source->send(":".$target->getOption("nick")."!".
          $target->getOption("ident")."@".$target->getHost()." ".
          "PRIVMSG ".$source->getOption("nick")." :You're not logged in.");
      }
    }

    public function isInstantiated() {
      EventHandling::registerForEvent("nsCommandEvent", $this,
        "receiveNickServCommand", array("logout", "Allows you to logout of ".
          "your account.\nUsage: /msg NickServ LOGOUT", null));
      return true;
    }
  }
?>
