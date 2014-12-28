<?php
  class __CLASSNAME__ {
    public $depend = array("Numeric", "Self", "UnknownCommandEvent");
    public $name = "CommandEvent";

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
        $source = array_shift($params);
      }

      if (count($params) == 0) {
        return true;
      }
      $cmd = array_shift($params);

      $count = 0;
      foreach ($registrations as $id => $registration) {
        // Filter non-compliant registrations
        if (!is_array($registration[2]) || count($registration[2]) < 1) {
          continue;
        }

        // Filter non-matching command preference
        if ($registration[2][0] == null || strtolower(trim($registration[2][0]))
            != strtolower(trim($cmd))) {
          continue;
        }

        // Filter non-matching server preference (if specified)
        if (isset($registration[2][1]) &&
            $connection->getOption("server") != $registration[2][1]) {
          continue;
        }

        // Filter non-matching protocol preference (if specified)
        if (isset($registration[2][2]) &&
            $connection->getOption("protocol") != $registration[2][2]) {
          continue;
        }

        if ($registration[2][1] == true) {
          array_unshift($params, $source);
        }

        $count++;
        EventHandling::triggerEvent($name, $id, array($connection, $params));
      }
      if ($count == 0) {
        // Command doesn't exist
        $event = EventHandling::getEventByName("unknownCommandEvent");
        if ($event != false) {
          foreach ($event[2] as $id => $registration) {
            // Filter non-compliant registrations
            if (!is_array($registration[2]) || count($registration[2]) < 2) {
              continue;
            }

            // Filter non-matching server preference (if specified)
            if ($connection->getOption("server") != $registration[2][0]) {
              continue;
            }

            // Filter non-matching protocol preference (if specified)
            if ($connection->getOption("protocol") != $registration[2][1]) {
              continue;
            }

            // Trigger the unknownCommandEvent event for each
            // applicable module.
            EventHandling::triggerEvent("unknownCommandEvent", $id,
              array($connection, $cmd));
          }
        }
      }

      return true;
    }

    public function isUnloadable() {
      return false;
    }

    public function isInstantiated() {
      // Create an event for raw data
      EventHandling::createEvent("commandEvent", $this, "preprocessEvent");
      return true;
    }
  }
?>
