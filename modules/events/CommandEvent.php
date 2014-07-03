<?php
  class @@CLASSNAME@@ {
    public $name = "CommandEvent";

    public function preprocessEvent($name, $registrations, $connection, $data) {
      $preprocessors = $registrations[1];
      $registrations = $registrations[0];
      $data = trim($data);

      if (stristr($data, " :")) {
        $cex = explode(" :", trim($data));
        $data = $cex[0];
        unset($cex[0]);
        $cex = implode(" :", $cex);
      }
      if (stristr($data, " ")) {
        $ex = explode(" ", trim($data));
        $ex[] = $cex;
        $data = $ex;
      }

      // Iterate through each registration.
      foreach ($registrations as $id => $registration) {
        // Trigger the event for a certain registration.
        EventHandling::triggerEvent($name, $id, array($connection, $data));
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
