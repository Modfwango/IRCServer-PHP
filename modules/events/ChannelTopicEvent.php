<?php
  class __CLASSNAME__ {
    public $name = "ChannelTopicEvent";

    public function isUnloadable() {
      return false;
    }

    public function isInstantiated() {
      EventHandling::createEvent("channelTopicEvent", $this);
      return true;
    }
  }
?>
