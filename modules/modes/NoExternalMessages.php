<?php
  class @@CLASSNAME@@ {
    public $depend = array("Channel", "ChannelMessageEvent", "ChannelModeEvent",
      "Modes");
    public $name = "NoExternalMessages";
    private $channel = null;
    private $modes = null;

    public function receiveChannelMode($name, $id, $data) {
      $source = $data[0];
      $channel = $data[1];
      $modes = $data[2];

      $has = $this->channel->hasModes($channel["name"],
        array("NoExternalMessages"));
      Logger::info(var_export($has, true));
      foreach ($modes as $key => $mode) {
        if ($mode["name"] == "NoExternalMessages") {
          if ($mode["operation"] == "+") {
            if ($has != false) {
              unset($modes[$key]);
            }
            else {
              $has = true;
            }
          }
          if ($mode["operation"] == "-") {
            if ($has == false) {
              unset($modes[$key]);
            }
            else {
              $has = false;
            }
          }
        }
      }
      $data[2] = $modes;
      return array(null, $data);
    }

    public function receiveChannelMessage($name, $id, $data) {
      $source = $data[0];
      $channel = $data[1];
      $message = $data[2];

      $modes = $this->channel->hasModes($channel["name"],
        array("NoExternalMessages"));
      if ($modes != false &&
          !$this->channel->clientIsOnChannel($source->getOption("id"),
          $channel["name"])) {
        $source->send(":".__SERVERDOMAIN__." 404 ".$source->getOption("nick").
          " ".$channel["name"]." :Cannot send to channel");
        return array(false);
      }
    }

    public function isInstantiated() {
      $this->channel = ModuleManagement::getModuleByName("Channel");
      $this->modes = ModuleManagement::getModuleByName("Modes");
      $this->modes->setMode(array("NoExternalMessages", "n", 0, 0));
      EventHandling::registerAsEventPreprocessor("channelModeEvent", $this,
        "receiveChannelMode");
      EventHandling::registerAsEventPreprocessor("channelMessageEvent", $this,
        "receiveChannelMessage");
      return true;
    }
  }
?>
