<?php
  class __CLASSNAME__ {
    public $depend = array("Client", "CommandEvent", "Numeric", "QUIT", "Self");
    public $name = "KILL";
    private $client = null;
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
        if ($connection->getOption("operator") != false) {
          if (count($command) > 0) {
            $nick = array_shift($command);
            $client = $this->client->getClientByNick($nick);
            if ($client != false) {
              $message = "Killed";
              if (count($command) > 0) {
                $message = "Killed: ".implode(" ", $command);
              }
              $client->send("ERROR :Closing Link: ".$client->getHost()." (".
                $message.")");
              ModuleManagement::getModuleByName("QUIT")->notifyQuit(null,
                $client, $message);
              $client->setOption("registered", false);
              $client->disconnect();
            }
            else {
              $connection->send($this->numeric->get("ERR_NOSUCHNICK", array(
                $this->self->getConfigFlag("serverdomain"),
                $connection->getOption("nick"),
                $nick
              )));
            }
          }
          else {
            $connection->send(":".$this->numeric->get("ERR_NEEDMOREPARAMS",
              array(
                $this->self->getConfigFlag("serverdomain"),
                $connection->getOption("nick"),
                $this->name
              )
            ));
          }
        }
        else {
          $connection->send($this->numeric->get("ERR_NOPRIVILEGES", array(
            $this->self->getConfigFlag("serverdomain"),
            ($connection->getOption("nick") ?
            $connection->getOption("nick") : "*")
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

    public function isInstantiated() {
      $this->client = ModuleManagement::getModuleByName("Client");
      $this->numeric = ModuleManagement::getModuleByName("Numeric");
      $this->self = ModuleManagement::getModuleByName("Self");
      EventHandling::registerForEvent("commandEvent", $this, "receiveCommand",
        "kill");
      return true;
    }
  }
?>
