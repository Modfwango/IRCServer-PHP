<?php
  class @@CLASSNAME@@ {
    public $depend = array("Channel", "Client", "ChannelModeEvent", "Modes");
    public $name = "ChannelBan";
    private $channel = null;
    private $client = null;
    private $modes = null;

    public function receiveChannelMode($name, $id, $data) {
      $source = $data[0];
      $channel = $data[1];
      $modes = $data[2];

      $h = array();
      $has = $this->channel->hasModes($channel["name"],
        array("ChannelBan"));
      if (is_array($has) && count($has) > 0) {
        foreach ($has as $m) {
          $h[strtolower($m["param"])] = true;
        }
      }
      foreach ($modes as $key => &$mode) {
        if ($mode["name"] == "ChannelBan") {
          Logger::info(var_export($mode, true));
          $mode["param"] = $this->client->getPrettyMask($mode["param"]);
          Logger::info(var_export($mode, true));
          if (!isset($h[strtolower($mode["param"])])) {
            $h[strtolower($mode["param"])] = false;
          }
          if ($mode["operation"] == "+") {
            if ($h[strtolower($mode["param"])] != false) {
              unset($modes[$key]);
            }
            else {
              $h[strtolower($mode["param"])] = true;
            }
          }
          if ($mode["operation"] == "-") {
            if ($h[strtolower($mode["param"])] == false) {
              unset($modes[$key]);
            }
            else {
              $h[strtolower($mode["param"])] = false;
            }
          }
        }
      }
      Logger::info(var_export($modes, true));
      $data[2] = $modes;
      return array(null, $data);
    }

    public function isInstantiated() {
      $this->channel = ModuleManagement::getModuleByName("Channel");
      $this->client = ModuleManagement::getModuleByName("Client");
      $this->modes = ModuleManagement::getModuleByName("Modes");
      $this->modes->setMode(array("ChannelBan", "b", "0", "3"));
      EventHandling::registerAsEventPreprocessor("channelModeEvent", $this,
        "receiveChannelMode");
      return true;
    }
  }
?>
