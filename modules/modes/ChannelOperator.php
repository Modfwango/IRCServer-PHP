<?php
  class @@CLASSNAME@@ {
    public $depend = array("Channel", "Client", "ChannelModeEvent", "Modes");
    public $name = "ChannelOperator";
    private $channel = null;
    private $client = null;
    private $modes = null;

    public function receiveChannelMode($name, $id, $data) {
      $source = $data[0];
      $channel = $data[1];
      $modes = $data[2];

      $has = $this->channel->hasModes($channel["name"],
        array("ChannelOperator"));
      Logger::info(var_export($has, true));
      foreach ($modes as $key => $mode) {
        $client = $this->client->getClientByNick($mode["param"]);
        if ($client != false) {
          $mode["param"] = $client->getOption("nick");
          if ($mode["name"] == "ChannelOperator") {
            if ($mode["operation"] == "+") {
              if ($has != false) {
                foreach ($has as $m) {
                  if ($mode["param"] == $m["param"]) {
                    unset($modes[$key]);
                  }
                }
              }
              else {
                $has = true;
              }
            }
            if ($mode["operation"] == "-") {
              if ($has == false) {
                foreach ($has as $m) {
                  if ($mode["param"] == $m["param"]) {
                    unset($modes[$key]);
                  }
                }
              }
              else {
                $has = false;
              }
            }
          }
        }
        else {
          unset($modes[$key]);
        }
      }
      $data[2] = $modes;
      return array(null, $data);
    }

    public function isInstantiated() {
      $this->channel = ModuleManagement::getModuleByName("Channel");
      $this->client = ModuleManagement::getModuleByName("Client");
      $this->modes = ModuleManagement::getModuleByName("Modes");
      $this->modes->setMode(array("ChannelOperator", "o", 0, 4));
      EventHandling::registerAsEventPreprocessor("channelModeEvent", $this,
        "receiveChannelMode");
      return true;
    }
  }
?>
