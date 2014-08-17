<?php
  class @@CLASSNAME@@ {
    public $name = "ChannelJoinEvent";

    public function isUnloadable() {
      return false;
    }

    public function isInstantiated() {
      EventHandling::createEvent("channelJoinEvent", $this);
      return true;
    }
  }
?>
