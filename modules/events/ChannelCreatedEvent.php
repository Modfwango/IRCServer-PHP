<?php
  class __CLASSNAME__ {
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
