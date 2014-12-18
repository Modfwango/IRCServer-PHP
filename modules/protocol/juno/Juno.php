<?php
  class __CLASSNAME__ {
    public $depend = array("Channel", "Client", "Modes", "Self");
    public $name = "Juno";
    private $alphabet = null;
    private $channel = null;
    private $client = null;
    private $config = array();
    private $modes = null;
    private $self = null;

    public function burst($connection) {
      $connection->send(":".$this->getSID()." BURST ".time());

      $acm = array();
      $aum = array();
      $modes = $this->modes->getModes();
      foreach ($modes as $mode) {
        if ($mode[2] == "0") {
          $name = $this->convertOutgoingCModeName($mode[0]);
          $acm[] = ($name != false ? $name : $mode[0]).":".$mode[1].":".
            $mode[3];
        }
        if ($mode[2] == "1") {
          $name = $this->convertOutgoingUModeName($mode[0]);
          $aum[] = ($name != false ? $name : $mode[0]).":".$mode[1];
        }
      }
      if (count($acm) > 0) {
        $connection->send(":".$this->getSID()." ACM ".implode(" ", $acm));
      }
      if (count($aum) > 0) {
        $connection->send(":".$this->getSID()." AUM ".implode(" ", $aum));
      }

      $connection->send(":".$this->getSID()." ENDBURST ".time());
      $connection->setOption("sentburst", true);
    }

    public function convertIncomingCModeName($name) {
      $tmp = array_flip($this->config["cmodemap"]);
      return (isset($tmp[$name]) ? $tmp[$name] : false);
    }

    public function convertIncomingUModeName($name) {
      $tmp = array_flip($this->config["umodemap"]);
      return (isset($tmp[$name]) ? $tmp[$name] : false);
    }

    public function convertOutgoingCModeName($name) {
      return (isset($this->config["cmodemap"][$name]) ?
        $this->config["cmodemap"][$name] : false);
    }

    public function convertOutgoingUModeName($name) {
      return (isset($this->config["umodemap"][$name]) ?
        $this->config["umodemap"][$name] : false);
    }

    public function getAlphabet() {
      return $this->alphabet;
    }

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
              "autoconn" => true
            )
          ),
          "cmodemap" => array(
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
          "umodemap" => array()
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
          $connection->connect();
          ConnectionManagement::newConnection($connection);
        }
      }
    }

    public function isInstantiated() {
      $this->channel = ModuleManagement::getModuleByName("Channel");
      $this->client = ModuleManagement::getModuleByName("Client");
      $this->modes = ModuleManagement::getModuleByName("Modes");
      $this->alphabet = $this->modes->createAlphabet();
      $this->self = ModuleManagement::getModuleByName("Self");
      EventHandling::registerForEvent("rehashEvent", $this, "loadConfig");
      $this->loadConfig();
      return true;
    }
  }
?>
