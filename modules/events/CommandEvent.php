<?php
  class __CLASSNAME__ {
    public $depend = array("Self");
    public $name = "CommandEvent";
    private $self = null;

    public function preprocessEvent($name, $registrations, $connection, $data) {
      $params = trim($data);

      if (stristr($params, " :")) {
        $cex = explode(" :", trim($params));
        $params = $cex[0];
        unset($cex[0]);
        $cex = implode(" :", $cex);
      }
      if (stristr($params, " ")) {
        $ex = explode(" ", trim($params));
        foreach ($ex as $key => $item) {
          if (trim($item) == null) {
            unset($ex[$key]);
          }
        }
        if (isset($cex)) {
          $ex[] = $cex;
        }
        $params = array_values($ex);
      }
      else {
        $params = array($params);
        if (isset($cex)) {
          $params[] = $cex;
        }
      }

      if (substr($params[0], 0, 1) == ":") {
        unset($params[0]);
        $params = array_values($params);
      }

      if (count($params) == 0) {
        return true;
      }
      $cmd = array_shift($params);

      $count = 0;
      foreach ($registrations as $id => $registration) {
        if ($registration[2] == null || strtolower(trim($registration[2]))
            != strtolower(trim($cmd))) {
          continue;
        }
        // Trigger the nsCommandEvent event for each
        // registered module.
        $count++;
        EventHandling::triggerEvent($name, $id, array($connection, $params));
      }
      if ($count == 0) {
        // Command doesn't exist.
        $connection->send(":".$this->self->getConfigFlag(
          "serverdomain")." 421 ".($connection->getOption("nick") ?
          $connection->getOption("nick") : "*")." ".$cmd." :Unknown command");
      }

      return true;
    }

    public function isUnloadable() {
      return false;
    }

    public function isInstantiated() {
      $this->self = ModuleManagement::getModuleByName("Self");
      // Create an event for raw data.
      EventHandling::createEvent("commandEvent", $this, "preprocessEvent");
      return true;
    }
  }
?>
