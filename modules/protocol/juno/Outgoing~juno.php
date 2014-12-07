<?php
  class __CLASSNAME__ {
    public $depend = array("Channel", "Client", "Modes");
    public $name = "juno";
    private $channel = null;
    private $client = null;
    private $config = array();
    private $modes = null;

    public function acquaint($connection) {
      $connection->send("SERVER ".$this->config["sid"]." ".
        $this->self->getConfigFlag("serverdomain")." ".
        $this->config["protocolversion"]." ".
        $this->self->getConfigFlag("version")." :".
        $this->self->getConfigFlag("serverdescription"));
      $connection->setOption("acquainted", true);
      if ($connection->getOption("sid") != false) {
        $this->authenticate($connection);
      }
    }

    public function authenticate($connection) {
      $connection->send("PASS ".$connection->getOption("sendpass"));
      $connection->setOption("sentpass", true);
      if ($connection->getOption("authenticated") == true) {
        $connection->send("READY");
      }
    }

    public function burst($connection) {
      $connection->send(":".$this->config["sid"]." BURST ".time());
      // burst here
      $connection->send(":".$this->config["sid"]." ENDBURST ".time());
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
          )
        );
        StorageHandling::saveFile($this, "config.json", json_encode($config,
          JSON_PRETTY_PRINT));
      }
      $this->config = $config;
      $this->tryConnections();
    }

    public function receiveConnectionConnected($name, $connection) {
      if ($connection->getOption("server") == true &&
          $connection->getOption("protocol") == "juno") {
        $this->acquaint();
      }
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
      $this->loadConfig();
      $this->channel = ModuleManagement::getModuleByName("Channel");
      $this->client = ModuleManagement::getModuleByName("Client");
      $this->modes = ModuleManagement::getModuleByName("Modes");
      EventHandling::registerForEvent("connectionConnectedEvent", $this,
        "receiveConnectionConnected");
      EventHandling::registerForEvent("rehashEvent", $this, "loadConfig");
      return true;
    }
  }
?>
