<?php
  class @@CLASSNAME@@ {
    public $name = "ChannelCreatedEvent";

    public function isUnloadable() {
      return false;
    }

    public function isInstantiated() {
      EventHandling::createEvent("channelCreatedEvent", $this);
      return true;
    }
  }
?>
