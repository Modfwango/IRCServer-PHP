<?php
  class @@CLASSNAME@@ {
    public $depend = array("Channel", "CommandEvent", "Self");
    public $name = "LIST";
    private $channel = null;
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
        $connection->send(":".$this->self->getConfigFlag(
          "serverdomain")." 323 ".$connection->getOption("nick")." Channel ".
          ":Users Name");
        if (count($command) > 0) {
          $c = $this->channel->getChannelByName($command[0]);
          if ($c != false) {
            $connection->send(":".$this->self->getConfigFlag(
              "serverdomain")." 322 ".$connection->getOption("nick")." ".
              $c["name"]." ".count($c["members"])." :".(
              isset($c["topic"]["text"]) ? $c["topic"]["text"] : null));
          }
          else {
            $connection->send(":".$this->self->getConfigFlag(
              "serverdomain")." 401 ".$connection->getOption("nick")." :No ".
              "such nick/channel");
          }
        }
        else {
          foreach ($this->channel->getChannels() as $c) {
            $connection->send(":".$this->self->getConfigFlag(
              "serverdomain")." 322 ".$connection->getOption("nick")." ".
              $c["name"]." ".count($c["members"])." :".(
              isset($c["topic"]["text"]) ? $c["topic"]["text"] : null));
          }
        }
        $connection->send(":".$this->self->getConfigFlag(
          "serverdomain")." 323 ".$connection->getOption("nick")." :End of ".
          "/LIST");
      }
      else {
        $connection->send(":".$this->self->getConfigFlag(
          "serverdomain")." 451 ".($connection->getOption("nick") ?
          $connection->getOption("nick") : "*")." :You have not registered");
      }
    }

    public function isInstantiated() {
      $this->channel = ModuleManagement::getModuleByName("Channel");
      $this->self = ModuleManagement::getModuleByName("Self");
      EventHandling::registerForEvent("commandEvent", $this, "receiveCommand",
        "list");
      return true;
    }
  }
?>
