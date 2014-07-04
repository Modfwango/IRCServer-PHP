<?php
  class @@CLASSNAME@@ {
    public $name = "CommandEvent";

    public function preprocessEvent($name, $registrations, $connection, $data) {
      $data = array(trim($data));

      if (stristr($data[0], " :")) {
        $cex = explode(" :", trim($data[0]));
        $data = array($cex[0]);
        unset($cex[0]);
        $cex = implode(" :", $cex);
      }
      if (stristr($data[0], " ")) {
        $ex = explode(" ", trim($data[0]));
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
