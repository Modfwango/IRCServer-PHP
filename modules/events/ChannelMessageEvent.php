<?php
  class __CLASSNAME__ {
    public $name = "ChannelMessageEvent";

    public function isUnloadable() {
      return false;
    }

    public function isInstantiated() {
      EventHandling::createEvent("channelMessageEvent", $this);
      return true;
    }
  }
?>
