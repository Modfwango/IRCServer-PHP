<?php
  class __CLASSNAME__ {
    public $name = "AutoOpOpers";
    public $depend = array("Channel", "ChannelModeEvent");
    private $channel = null;

    public function receiveChannelJoin($name, $data) {
      $source = $data[0];
      $channel = $this->channel->getChannelByName($data[1]);

      $event = EventHandling::getEventByName("channelModeEvent");
      if ($event != false && $source->getOption("operator") != false) {
        foreach ($event[2] as $id => $registration) {
          // Trigger the channelModeEvent event for each
          // registered module.
          EventHandling::triggerEvent("channelModeEvent", $id, array($source,
            $channel, array(array(
              "operation" => "+",
              "name" => "ChannelOperator",
              "param" => $source->getOption("nick")
            ))));
        }
      }
    }

    public function isInstantiated() {
      $this->channel = ModuleManagement::getModuleByName("Channel");
      EventHandling::registerForEvent("channelJoinEvent", $this,
        "receiveChannelJoin");
      return true;
    }
  }
?>
