<?php
  class @@CLASSNAME@@ {
    public $depend = array("NSClient");
    public $name = "NSHELP";

    public function receivePrivateMessage($name, $data) {
      $source = $data[0];
      $target = $data[1];
      $message = $data[2];
      $ex = explode(" ", trim($message));

      if (strtolower($target->getOption("nick")) == "nickserv") {
        Logger::info($source->getOption("nick")." Requesting command \n".
          var_export($ex, true));
      }
    }

    public function isInstantiated() {
      EventHandling::registerForEvent("privateMessageEvent", $this,
        "receivePrivateMessage");
      return true;
    }
  }
?>
