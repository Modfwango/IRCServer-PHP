<?php
  class @@CLASSNAME@@ {
    public $name = "ChannelCreatedEvent";

    public function isInstantiated() {
      EventHandling::createEvent("channelCreatedEvent", $this);
      return true;
    }
  }
?>
