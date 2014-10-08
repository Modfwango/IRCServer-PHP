<?php
  class __CLASSNAME__ {
    public $name = "AutoOpOpers";
    public $depend = array("ChannelModeEvent");

    public function receiveChannelJoin($name, $id, $channel) {
      $source = $data[0];
      $channel = $this->channel->getChannelByName($data[1]);

      $event = EventHandling::getEventByName("channelModeEvent");
      if ($event != false && $source->getOption("operator") == true) {
        foreach ($event[2] as $id => $registration) {
          // Trigger the channelModeEvent event for each
          // registered module.
          EventHandling::triggerEvent("channelModeEvent", $id,
            array($source, $channel, array(array("ChannelOperator",
            $source->getOption("id")))));
        }
      }
    }

    public function isInstantiated() {
      EventHandling::registerForEvent("channelJoinEvent", $this,
        "receiveChannelJoin");
      return true;
    }
  }
?>
