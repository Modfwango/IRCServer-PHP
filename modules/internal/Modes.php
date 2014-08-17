<?php
  class @@CLASSNAME@@ {
    public $name = "Modes";
    private $modes = array("byname" => array(),
      "bychar" => array("0" => array(), "1" => array()), "byprefix" => array(),
      "bytarget" => array("0" => array(), "1" => array()), "bytype" => array(
      "0" => array(), "1" => array(), "2" => array(), "3" => array(),
      "4" => array(), "5" => array()), "byweight" => array());

    public function getModeByName($name) {
      // Retrieve the requested mode if it exists, otherwise return false.
      if (isset($this->modes["byname"][$name])) {
        return $this->modes["byname"][$name];
      }
      return false;
    }

    public function getModeByChar($target, $char) {
      // Retrieve the requested mode if it exists, otherwise return false.
      return $this->getModeByName($this->getModeNameByChar($target, $char));
    }

    public function getModeByPrefix($prefix) {
      // Retrieve the requested mode if it exists, otherwise return false.
      return $this->getModeByName($this->getModeNameByPrefix($prefix));
    }

    public function getModeNameByChar($target, $char) {
      // Retrieve the requested name if it exists, otherwise return false.
      return (isset($this->modes["bychar"][$target][$char]) ?
        $this->modes["bychar"][$target][$char] : false);
    }

    public function getModeNameByPrefix($prefix) {
      // Retrieve the requested name if it exists, otherwise return false.
      return (isset($this->modes["byprefix"][$prefix]) ?
        $this->modes["byprefix"][$prefix] : false);
    }

    public function getModeNamesAndWeight() {
      // Retrieve the requested names if they exist, otherwise return false.
      return (isset($this->modes["byweight"]) ?
        $this->modes["byweight"] : false);
    }

    public function getModeNamesByTarget($target) {
      // Retrieve the requested names if they exist, otherwise return false.
      return (isset($this->modes["bytarget"][$target]) ?
        $this->modes["bytarget"][$target] : false);
    }

    public function getModeNamesByType($type) {
      // Retrieve the requested names if they exist, otherwise return false.
      return (isset($this->modes["bytype"][$type]) ?
        $this->modes["bytype"][$type] : false);
    }

    public function getModesAndWeight() {
      // Retrieve the requested modes if they exist, otherwise return false.
      $ret = $this->getModeNamesAndWeight();
      foreach ($ret as $weight => &$modenames) {
        foreach ($modenames as &$modename) {
          $modename = $this->getModeByName($modename);
        }
      }
      if (count($ret) > 0) {
        return $ret;
      }
      return false;
    }

    public function getModesByTarget($target) {
      // Retrieve the requested modes if they exist, otherwise return false.
      $modes = array();
      foreach ($this->getModeNamesByTarget($target) as $name) {
        if (isset($this->modes["byname"][$name])) {
          $modes[] = $this->modes["byname"][$name];
        }
      }
      if (count($modes) > 0) {
        return $modes;
      }
      return false;
    }

    public function getModesByType($type) {
      // Retrieve the requested modes if they exist, otherwise return false.
      $modes = array();
      foreach ($this->getModeNamesByType($type) as $name) {
        if (isset($this->modes["byname"][$name])) {
          $modes[] = $this->modes["byname"][$name];
        }
      }
      if (count($modes) > 0) {
        return $modes;
      }
      return false;
    }

    public function setMode($mode) {
      $this->unsetMode($mode);
      for ($i = 0; $i < 4; $i++) {
        $mode[$i] = strval($mode[$i]);
      }
      // [name, char, [0 (channel), 1 (user)], type, {prefix}, {weight}]
      // Types:
      // 0 - Never requires a parameter.
      // 1 - Parameter required when set/unset.
      // 2 - Parameter required when set.
      // 3 - List type mode.
      // 4 - Status type mode.
      // 5 - Key.
      $this->modes["byname"][$mode[0]] = array($mode[0], $mode[1], $mode[2],
        $mode[3], (isset($mode[4]) ? $mode[4] : null),
        (isset($mode[5]) ? $mode[5] : null));
      $this->modes["bychar"][$mode[2]][$mode[1]] = $mode[0];
      if (isset($mode[4])) {
        $this->modes["byprefix"][$mode[4]] = $mode[0];
      }
      $this->modes["bytarget"][$mode[2]][] = $mode[0];
      $this->modes["bytype"][$mode[3]][] = $mode[0];
      if (isset($mode[5])) {
        if (!isset($this->modes["byweight"][$mode[5]])) {
          $this->modes["byweight"][$mode[5]] = array();
        }
        $this->modes["byweight"][$mode[5]][] = $mode[0];
        ksort($this->modes["byweight"]);
      }
    }

    public function unsetMode($mode) {
      $names = array();

      foreach ($this->modes["byname"] as $m) {
        if ($m[0] == $mode[0]) {
          $names[] = $m[0];
        }
        if ($m[1] == $mode[1]) {
          $names[] = $m[0];
        }
        if (isset($m[4]) && isset($mode[4]) && $m[4] == $mode[4]) {
          $names[] = $m[0];
        }
      }

      foreach ($names as $name) {
        foreach ($this->modes["byname"] as $key => $mode) {
          if ($key == $name) {
            unset($this->modes["byname"][$key]);
          }
        }
        foreach ($this->modes["bychar"] as $key => $mode) {
          if ($mode == $name) {
            unset($this->modes["bychar"][$key]);
          }
        }
        foreach ($this->modes["byprefix"] as $key => $mode) {
          if ($mode == $name) {
            unset($this->modes["byprefix"][$key]);
          }
        }
        foreach ($this->modes["bytarget"] as $key => &$modes) {
          $modes = array_unique($modes);
          foreach ($modes as $mode) {
            if ($mode == $name) {
              unset($this->modes["bytarget"][$key]);
            }
          }
        }
        foreach ($this->modes["bytype"] as $key => &$modes) {
          $modes = array_unique($modes);
          foreach ($modes as $mode) {
            if ($mode == $name) {
              unset($this->modes["bytype"][$key]);
            }
          }
        }
        foreach ($this->modes["byweight"] as $key => &$modes) {
          $modes = array_unique($modes);
          foreach ($modes as $mode) {
            if ($mode == $name) {
              unset($this->modes["byweight"][$key]);
            }
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
