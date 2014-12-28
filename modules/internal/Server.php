<?php
  class __CLASSNAME__ {
    public $depend = array();
    public $name = "Server";
    private $servers = array("byhost" => array(), "byprotocol" => array(),
      "bysid" => array());

    public function getServerByHost($host) {
      // Retrieve the requested server if it exists, otherwise return false.
      return $this->getServerBySID($this->getServerSIDByHost($host));
    }

    public function getServerBySID($id) {
      // Retrieve the requested server if it exists, otherwise return false.
      return (isset($this->servers["bysid"][$id]) ?
        $this->servers["bysid"][$id] : false);
    }

    public function getServersByProtocol($protocol) {
      $servers = array();
      foreach ($this->getServerSIDsByProtocol($protocol) as $sid) {
        $servers[] = $this->getServerBySID($sid);
      }
      return $servers;
    }

    public function getServerSIDByHost($host) {
      // Retrieve the requested server if it exists, otherwise return false.
      return (isset($this->servers["byhost"][strtolower($host)]) ?
        $this->servers["byhost"][strtolower($host)] : false);
    }

    public function getServerSIDsByProtocol($protocol) {
      // Retrieve the requested servers if they exist, otherwise return false.
      return (isset($this->servers["byprotocol"][$protocol]) ?
        $this->servers["byprotocol"][$protocol] : false);
    }

    public function setServer($server) {
      // Remove any pre-existing server indexes.
      $this->unsetServer($server);
      // Set a server.
      if ($server->getOption("sid") != false) {
        $this->servers["bysid"][$server->getOption("sid")] = $server;
        if ($server->getOption("servhost") != false) {
          $this->servers["byhost"][strtolower($server->getOption("servhost"))] =
            $server->getOption("sid");
        }

        if ($server->getHost() != false) {
          if (!isset($this->servers["byprotocol"][$server->getOption(
              "protocol")])) {
            $this->clients["byprotocol"][$server->getOption("protocol")] =
              array();
          }
          $this->clients["byprotocol"][$server->getOption("protocol")][] =
            $server->getOption("sid");
        }

        Logger::devel("New server:");
        Logger::devel(var_export($server, true));
        return true;
      }
      return false;
    }

    public function unsetServer($server) {
      if (isset($this->servers["bysid"][$server->getOption("sid")])) {
        unset($this->servers["bysid"][$server->getOption("sid")]);
        foreach ($this->servers["byhost"] as $key => $byhost) {
          if ($byhost == $server->getOption("sid")) {
            unset($this->servers["bysid"][$key]);
          }
        }
        foreach ($this->servers["byprotocol"] as &$byprotocol) {
          if (in_array($server->getOption("sid"), $byprotocol)) {
            $byprotocol = array_diff($byprotocol, array(
              $server->getOption("sid")));
          }
        }
      }
    }

    public function isUnloadable() {
      return false;
    }

    public function isInstantiated() {
      return true;
    }
  }
?>
