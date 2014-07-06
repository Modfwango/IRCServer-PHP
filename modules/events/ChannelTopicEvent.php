<?php
  class @@CLASSNAME@@ {
    public $name = "ChannelTopicEvent";

    public function isInstantiated() {
      EventHandling::createEvent("channelTopicEvent", $this);
      return true;
    }
  }
?>
