<?php
  class @@CLASSNAME@@ {
    public $name = "ChannelJoinEvent";

    public function isInstantiated() {
      EventHandling::createEvent("channelJoinEvent", $this);
      return true;
    }
  }
?>
