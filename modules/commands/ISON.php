<?php
  class __CLASSNAME__ {
    public $depend = array("Client", "CommandEvent", "Numeric", "Self");
    public $name = "ISON";
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
        if (count($command) > 0) {
          $online = array();
          foreach ($command as $user) {
            $c = $this->client->getClientByNick($user);
            if ($c != false) {
              if (strlen($this->numeric->get("RPL_ISON", array(
                  $this->self->getConfigFlag("serverdomain"),
                  $connection->getOption("nick"),
                  implode(" ", $online)))) < 510) {
                $online[] = $c->getOption("nick");
              }
            }
          }

          while (strlen($this->numeric->get("RPL_ISON", array(
                  $this->self->getConfigFlag("serverdomain"),
                  $connection->getOption("nick"),
                  implode(" ", $online)))) > 510) {
            array_pop($online);
          }

          $connection->send($this->numeric->get("RPL_ISON", array(
            $this->self->getConfigFlag("serverdomain"),
            $connection->getOption("nick"),
            implode(" ", $online)
          )));
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

    public function isInstantiated() {
      $this->client = ModuleManagement::getModuleByName("Client");
      $this->numeric = ModuleManagement::getModuleByName("Numeric");
      $this->self = ModuleManagement::getModuleByName("Self");
      EventHandling::registerForEvent("commandEvent", $this, "receiveCommand",
        "ison");
      return true;
    }
  }
?>
