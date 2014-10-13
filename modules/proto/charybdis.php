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
      return $this->config["sid"].strtoupper(substr($connection->getOption(
        "id"), 0, 6));
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

    public function joinChannel($connection, $channel, $burst = false) {
      $c = $this->channel->getChannelByName($channel);
      if ($burst == true || count($c["members"]) <= 1) {
        $modeString = $this->modes->getModeStringComponents($c["modes"], false,
          array_merge($this->modes->getModesByType("3"),
          $this->modes->getModesByType("4")));
        $modeString = trim("+".implode(array_map(array($this,
          "getModeCharForName"), $modeString[0]))." ".implode(" ",
          $modeString[1]));
        return ":".$this->config["sid"]." SJOIN ".$c["time"]." ".$c["name"]." ".
          $modeString." :".$this->getModePrefixForName(
          $this->channel->getChannelMemberPrefixModeByID($channel,
          $connection->getOption("id"))).$this->getClientUID($connection);
      }
      else {
        return ":".$this->config["sid"]." JOIN ".$c["time"]." ".$c["name"]." +";
      }
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
