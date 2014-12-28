<?php
  class __CLASSNAME__ {
    public $depend = array("RehashEvent");
    public $name = "Juno";
    private $config = array();

    public function getConnection($servhost) {
      return (isset($this->config["connections"][$servhost]) ?
        $this->config["connections"][$servhost] : false);
    }

    public function getSID() {
      return $this->config["sid"];
    }

    public function getVersion() {
      return $this->config["version"];
    }

    private function loadConfig($name = null, $data = null) {
      $config = @json_decode(trim(StorageHandling::loadFile($this,
        "config.json")), true);
      if (!is_array($config)) {
        $config = array(
          "sid" => rand(0, 9),
          "version" => "6.1",
          "connections" => array(
            "devserver1.example.com" => array(
              "ip" => "192.168.1.86",
              "port" => "7000",
              "sendpass" => "k",
              "recvpass" => "k",
              "autoconn" => true,
              "forwardmodemap" => array(
                "channel" => array(
                  "ban" => "ChannelBan",
                  "except" => "ChannelBanExemption",
                  "op" => "ChannelOperator",
                  "voice" => "ChannelVoice",
                  "invite_except" => "InviteException",
                  "invite_only" => "InviteOnly",
                  "moderated" => "Moderated",
                  "no_ext" => "NoExternalMessages",
                  "protect_topic" => "ProtectTopic",
                  "free_invite" => "UnrestrictedInvite"
                ),
                "user" => array()
              ),
              "reversemodemap" => array(
                "channel" => array(
                   "ChannelBan" => "ban",
                   "ChannelBanExemption" => "except",
                   "ChannelOperator" => "op",
                   "ChannelVoice" => "voice",
                   "InviteException" => "invite_except",
                   "InviteOnly" => "invite_only",
                   "Moderated" => "moderated",
                   "NoExternalMessages" => "no_ext",
                   "ProtectTopic" => "protect_topic",
                   "UnrestrictedInvite" => "free_invite"
                ),
                "user" => array()
              )
            )
          )
        );
        StorageHandling::saveFile($this, "config.json", json_encode($config,
          JSON_PRETTY_PRINT));
      }
      $this->config = $config;
      $this->tryConnections();
    }

    public function tryConnections() {
      foreach ($this->config["connections"] as $host => $c) {
        if ($c["autoconn"] == true) {
          $connection = new Connection("0", array(
            $c["ip"],
            $c["port"],
            false, // SSL isn't supported yet??
            array(
              "server" => true,
              "protocol" => "juno",
              "servhost" => $host,
              "sendpass" => $c["sendpass"],
              "recvpass" => $c["recvpass"]
            )
          ));
          ConnectionManagement::newConnection($connection);
        }
      }
    }

    public function isInstantiated() {
      EventHandling::registerForEvent("rehashEvent", $this, "loadConfig");
      $this->loadConfig();
      return true;
    }
  }
?>
