<?php
  class @@CLASSNAME@@ {
    public $depend = array("PrivateMessageEvent");
    public $name = "User";

    public function receivePrivateMessage($name, $data) {
      $source = $data[0];
      $target = $data[1];
      $message = $data[2];
      $base = ":".$source->getOption("nick")."!".$source->getOption("ident").
        "@".$source->getHost()." PRIVMSG ".$target->getOption("nick")." :";

      if (strlen($base.$message) > 510) {
        $chunks = str_split($message, (510 - strlen($base)));
        foreach ($chunks as $chunk) {
          $target->send($base.$chunk);
        }
      }
      else {
        $target->send($base.$message);
      }
    }

    public function isInstantiated() {
      EventHandling::registerForEvent("privateMessageEvent", $this,
        "receivePrivateMessage");
      return true;
    }
  }
?>
