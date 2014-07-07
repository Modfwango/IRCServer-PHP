<?php
  class @@CLASSNAME@@ {
    public $depend = array("Channel");
    public $name = "ProtectTopic";
    private $channel = null;

    public function receiveChannelTopic($name, $id, $data) {
      $source = $data[0];
      $channel = $data[1];
      $message = $data[2];

      $modes = $this->channel->hasModes($channel["name"],
        array("ProtectTopic"));
      if ($modes != false) {
        $modes = $this->channel->hasModes($channel["name"], array("Operator"));
        if ($modes != false) {
          foreach ($modes as $mode) {
            if ($mode["param"] == $source->getOption("nick")) {
              return array(true);
            }
          }
        }
        $source->send(":".__SERVERDOMAIN__." 482 ".$source->getOption("nick").
          " ".$channel["name"]." :You're not a channel operator");
        return array(false);
      }
    }

    public function isInstantiated() {
      $this->channel = ModuleManagement::getModuleByName("Channel");
      EventHandling::registerAsEventPreprocessor("channelTopicEvent", $this,
        "receiveChannelTopic");
      return true;
    }
  }
?>
