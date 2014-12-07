<?php
  class __CLASSNAME__ {
    public $depend = array("Client", "CommandEvent", "Numeric", "RehashEvent",
      "Self");
    public $name = "OPER";
    private $client = null;
    private $config = array();
    private $numeric = null;
    private $self = null;

    public function receiveCommand($name, $data) {
      $connection = $data[0];
      $command = $data[1];

      foreach ($command as $key => $param) {
        if (trim($param) == null) {
          unset($command[$key]);
        }
      }
      $command = array_values($command);

      if ($connection->getOption("registered") == true) {
        if (count($command) > 1) {
          if ($connection->getOption("operator") == false) {
            foreach ($this->config as $oname => $oper) {
              if (strtolower($oname) == strtolower($command[0])) {
                if (isset($oper["mask"])) {
                  if (!is_array($oper["mask"])) {
                    $oper["mask"] = array($oper["mask"]);
                  }
                  $matches = false;
                  foreach ($oper["mask"] as $mask) {
                    if ($this->client->clientMatchesMask($connection, $mask)) {
                      $matches = true;
                      break;
                    }
                  }
                  if ($matches == true) {
                    if (password_verify($command[1], $oper["hash"])) {
                      $connection->setOption("operator", $oname);
                      $connection->send($this->numeric->get("RPL_YOUREOPER",
                        array(
                          $this->self->getConfigFlag("serverdomain"),
                          $connection->getOption("nick")
                        )
                      ));
                      return;
                    }
                    else {
                      $connection->send($this->numeric->get(
                        "ERR_PASSWDMISMATCH",
                        array(
                          $this->self->getConfigFlag("serverdomain"),
                          $connection->getOption("nick")
                        )
                      ));
                      return;
                    }
                  }
                }
              }
            }
            $connection->send($this->numeric->get("ERR_NOOPERHOST", array(
              $this->self->getConfigFlag("serverdomain"),
              $connection->getOption("nick")
            )));
          }
          else {
            $connection->send($this->numeric->get("RPL_YOUREOPER", array(
              $this->self->getConfigFlag("serverdomain"),
              $connection->getOption("nick")
            )));
          }
        }
        else {
          $connection->send(":".$this->numeric->get("ERR_NEEDMOREPARAMS", array(
            $this->self->getConfigFlag("serverdomain"),
            $connection->getOption("nick"),
            $this->name
          )));
        }
      }
      else {
        $connection->send($this->numeric->get("ERR_NOTREGISTERED", array(
          $this->self->getConfigFlag("serverdomain"),
          ($connection->getOption("nick") ?
          $connection->getOption("nick") : "*")
        )));
      }
    }

    public function loadConfig($name = null, $data = null) {
      $config = @json_decode(trim(StorageHandling::loadFile($this,
        "config.json")), true);
      if (!is_array($config)) {
        $config = array(
          "clay" => array(
            "mask" => array(
              "clayfreeman!*@clayfreeman.com",
              "clay@*.clayfreeman.com",
              "192.168.1.*"
            ),
            "hash" => "Run '/mkpasswd <password>' on the IRCd to get a hash"
          ),
          "matthew" => array(
            "mask" => "*@mattwb65.com",
            "hash" => "$2y$10$.vGn1O9wmRbobbyXD98HNOgsNpDczlqm3Jq7KnEd1rVAGv3F".
              "ykk1a"
          )
        );
        StorageHandling::saveFile($this, "config.json", json_encode($config,
          JSON_PRETTY_PRINT));
      }
      $this->config = $config;
    }

    public function isInstantiated() {
      $this->loadConfig();
      $this->client = ModuleManagement::getModuleByName("Client");
      $this->numeric = ModuleManagement::getModuleByName("Numeric");
      $this->self = ModuleManagement::getModuleByName("Self");
      EventHandling::registerForEvent("commandEvent", $this, "receiveCommand",
        array("oper", false));
      EventHandling::registerForEvent("rehashEvent", $this, "loadConfig");
      return true;
    }
  }
?>
