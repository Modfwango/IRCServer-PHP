<?php
  class __CLASSNAME__ {
    public $depend = array("Channel", "Modes");
    public $name = "charybdis";
    private $channel = null;
    private $config = array();
    private $modes = null;
    private $modeMap = array(
      "ChannelBan" => "b",
      "ChannelBanExemption" => "e",
      "ChannelOperator" => "o",
      "ChannelVoice" => "v",
      "Deaf" => "D",
      "InviteException" => "I",
      "InviteOnly" => "i",
      "Moderated" => "m",
      "NoExternalMessages" => "n",
      "ProtectTopic" => "t",
      "SSLOnly" => "S",
      "StripColors" => "c",
      "UnrestrictedInvite" => "g"
    );
    private $prefixMap = array(
      "ChannelOperator" => "@",
      "ChannelVoice" => "+"
    );

    public function getClientUID($connection) {
      return $this->getClientUIDByID($connection->getOption("id"));
    }

    public function getClientUIDByID($id) {
      return $this->config["sid"].strtoupper(substr($id, 0, 6));
    }

    public function introduceClient($connection) {
      return ":".$this->config["sid"]." EUID ".$connection->getOption(
        "nick")." 0 ".$connection->getOption("nickts")." +".implode(array_map(
        array($this, "getModeCharForName"), array_shift(
        $this->modes->getModeStringComponents(
        $connection->getOption("modes")))))." ".
        $connection->getOption("ident")." ".$connection->getHost()." ".
        $connection->getIP()." ".$this->getClientUID($connection)." * * :".
        $connection->getOption("realname");
    }

    public function joinChannel($channel, $connection = null) {
      // [ STACK ] Entering module: CommandEvent::preprocessEvent
      // [ DEBUG ] Event 'commandEvent' has been triggered for 'EVAL'
      // [ STACK ] Entering module: EVAL::receiveCommand
      // [ DEBUG ] Channel::getChannelMemberPrefixModeByID returning:
      // [ DEBUG ] array (
      // [ DEBUG ]   1000 =>
      // [ DEBUG ]   array (
      // [ DEBUG ]     0 => 'ChannelOperator',
      // [ DEBUG ]   ),
      // [ DEBUG ] )
      $c = $this->channel->getChannelByName($channel);
      if (is_array($c)) {
        if (!is_object($connection)) {
          $modeString = $this->modes->getModeStringComponents($c["modes"],
            false, array_merge($this->modes->getModesByType("3"),
            $this->modes->getModesByType("4")));
          $modeString = trim("+".implode(array_map(array($this,
            "getModeCharForName"), $modeString[0]))." ".implode(" ",
            $modeString[1]));
          $noprefix = array();
          $prefix = array();
          foreach ($this->channel->getChannelMembers($c["name"]) as $id) {
            $prefixes = $this->channel->getChannelMemberPrefixModeByID(
              $c["name"], $id, false);
            if (count($prefixes) == 0) {
              $noprefix[] = $this->getClientUIDByID($id);
            }
            else {
              for ($i = 0; $i < count($prefixes); $i++) {
                $prefixes[$i] = implode(array_map(array($this,
                  "getModePrefixForName"), $prefixes[$i]));
              }
              reset($prefixes);
              $key = key($prefixes);
              if (!isset($prefix[$key])) {
                $prefix[$key] = array();
              }
              $prefix[$key][] = implode($prefixes).$this->getClientUIDByID($id);
            }
          }
          $userString = trim(implode(" ", array_map("implode", array_fill(0,
            count($prefix), " "), $prefix))." ".implode(" ", $noprefix));
          return ":".$this->config["sid"]." SJOIN ".$c["time"]." ".
            $c["name"]." ".$modeString." :".$userString;
        }
        else {
          return ":".$this->getClientUID($connection)." JOIN ".$c["time"]." ".
            $c["name"]." +";
        }
      }
      return false;
    }

    public function nick($connection) {
      return ":".$this->getClientUID($connection)." NICK ".
        $connection->getOption("nick").$connection->getOption("nickts");
    }

    private function getModeCharForName($name) {
      return (isset($this->modeMap[$name]) ? $this->modeMap[$name] : null);
    }

    private function getModeNameForChar($char) {
      $modeMap = array_flip($this->modeMap);
      return (isset($modeMap[$char]) ? $modeMap[$char] : null);
    }

    private function getModeNameForPrefix($char) {
      $modeMap = array_flip($this->prefixMap);
      return (isset($prefixMap[$char]) ? $prefixMap[$char] : null);
    }

    private function getModePrefixForName($name) {
      return (isset($this->prefixMap[$name]) ? $this->prefixMap[$name] : null);
    }

    private function loadConfig($name = null, $data = null) {
      $config = @json_decode(trim(StorageHandling::loadFile($this,
        "config.json")), true);
      if (!is_array($config)) {
        $config = array(
          "sid" => rand(0, 9).substr(str_shuffle(str_repeat(
            "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ", 2)), 0, 2),
        );
        StorageHandling::saveFile($this, "config.json", json_encode($config,
          JSON_PRETTY_PRINT));
      }
      $this->config = $config;
    }

    public function isInstantiated() {
      $this->loadConfig();
      $this->channel = ModuleManagement::getModuleByName("Channel");
      $this->modes = ModuleManagement::getModuleByName("Modes");
      return true;
    }
  }
?>