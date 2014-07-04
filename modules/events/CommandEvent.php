<?php
  class @@CLASSNAME@@ {
    public $name = "CommandEvent";

    public function preprocessEvent($name, $registrations, $connection, $data) {
      $data = trim($data);

      if (stristr($data, " :")) {
        $cex = explode(" :", trim($data));
        $data = $cex[0];
        unset($cex[0]);
        $cex = implode(" :", $cex);
        if (trim($cex) == null) {
          unset($cex);
        }
      }
      if (stristr($data, " ")) {
        $ex = explode(" ", trim($data));
        if (isset($cex)) {
          $ex[] = $cex;
        }
        foreach ($ex as $key => $item) {
          if (trim($item) == null) {
            unset($ex[$key]);
          }
        }
        $data = array_values($ex);
      }
      else {
        $data = array($data);
        if (isset($cex)) {
          $data[] = $cex;
        }
      }

      $found = false;
      // Iterate through each registration.
      foreach ($registrations as $id => $registration) {
        // Trigger the event for a certain registration.
        if (EventHandling::triggerEvent($name, $id, array($connection,
            $data))) {
          $found = true;
        }
      }

      if ($found == false) {
        $connection->send(":".__SERVERDOMAIN__." 421 ".(
          $connection->getOption("nick") ? $connection->getOption("nick") :
          "*")." ".$data[0]." :Unknown command");
      }

      return true;
    }

    public function isInstantiated() {
      // Create an event for raw data.
      EventHandling::createEvent("commandEvent", $this, "preprocessEvent");
      return true;
    }
  }
?>
