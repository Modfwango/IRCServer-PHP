<?php
  class @@CLASSNAME@@ {
    public $depend = array("Client", "ChannelMessageEvent", "UserModeEvent",
      "Modes");
    public $name = "Deaf";
    private $channel = null;
    private $modes = null;

    public function receiveUserMode($name, $id, $data) {
      $source = $data[0];
      $channel = $data[1];
      $modes = $data[2];

      $has = $this->client->hasModes($source->getOption("id"),
        array("Deaf"));
      foreach ($modes as $key => $mode) {
        if ($mode["name"] == "Deaf") {
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

      $modes = $this->client->hasModes($source->getOption("id"),
        array("Deaf"));
      if ($modes != false) {
        return array(false);
      }
    }

    public function isInstantiated() {
      $this->client = ModuleManagement::getModuleByName("Client");
      $this->modes = ModuleManagement::getModuleByName("Modes");
      $this->modes->setMode(array("Deaf", "c", "1", "0"));
      EventHandling::registerAsEventPreprocessor("userModeEvent", $this,
        "receiveUserMode");
      EventHandling::registerAsEventPreprocessor("channelMessageEvent", $this,
        "receiveChannelMessage");
      return true;
    }
  }
?>
