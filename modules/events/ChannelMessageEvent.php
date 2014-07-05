<?php
  class @@CLASSNAME@@ {
    public $name = "ChannelMessageEvent";

    public function isInstantiated() {
      EventHandling::createEvent("channelMessageEvent", $this);
      return true;
    }
  }
?>
