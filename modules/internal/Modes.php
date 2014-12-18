<?php
  class __CLASSNAME__ {
    public $name = "Modes";
    private $alphabetCount = 0;
    private $modes = array();

    public function createAlphabet() {
      $index = $this->alphabetCount++;
      $this->modes[$index] = array(
        "byname" => array(),
        "bychar" => array(
          "0" => array(),
          "1" => array()
        ),
        "byprefix" => array(),
        "bytarget" => array(
          "0" => array(),
          "1" => array()
        ),
        "bytype" => array(
          "0" => array(),
          "1" => array(),
          "2" => array(),
          "3" => array(),
          "4" => array(),
          "5" => array()
        ),
        "byweight" => array()
      );
      return $index;
    }

    public function getModeByName($name, $alphabet = 0) {
      // Retrieve the requested mode if it exists, otherwise return false.
      if (isset($this->modes[$alphabet]["byname"][$name])) {
        return $this->modes[$alphabet]["byname"][$name];
      }
      return false;
    }

    public function getModeByChar($target, $char, $alphabet = 0) {
      // Retrieve the requested mode if it exists, otherwise return false.
      return $this->getModeByName($this->getModeNameByChar($target, $char,
        $alphabet), $alphabet);
    }

    public function getModeByPrefix($prefix, $alphabet = 0) {
      // Retrieve the requested mode if it exists, otherwise return false.
      return $this->getModeByName($this->getModeNameByPrefix($prefix,
        $alphabet), $alphabet);
    }

    public function getModeNameByChar($target, $char, $alphabet = 0) {
      // Retrieve the requested name if it exists, otherwise return false.
      return (isset($this->modes[$alphabet]["bychar"][$target][$char]) ?
        $this->modes[$alphabet]["bychar"][$target][$char] : false);
    }

    public function getModeNameByPrefix($prefix, $alphabet = 0) {
      // Retrieve the requested name if it exists, otherwise return false.
      return (isset($this->modes[$alphabet]["byprefix"][$prefix]) ?
        $this->modes[$alphabet]["byprefix"][$prefix] : false);
    }

    public function getModeNamesAndWeight($alphabet = 0) {
      // Retrieve the requested names if they exist, otherwise return false.
      return (isset($this->modes[$alphabet]["byweight"]) ?
        $this->modes[$alphabet]["byweight"] : false);
    }

    public function getModeNamesByTarget($target, $alphabet = 0) {
      // Retrieve the requested names if they exist, otherwise return false.
      return (isset($this->modes[$alphabet]["bytarget"][$target]) ?
        $this->modes[$alphabet]["bytarget"][$target] : false);
    }

    public function getModeNamesByType($type, $alphabet = 0) {
      // Retrieve the requested names if they exist, otherwise return false.
      return (isset($this->modes[$alphabet]["bytype"][$type]) ?
        $this->modes[$alphabet]["bytype"][$type] : false);
    }

    public function getModes($alphabet = 0) {
        return array_values($this->modes[$alphabet]["byname"]);
    }

    public function getModesAndWeight($alphabet = 0) {
      // Retrieve the requested modes if they exist, otherwise return false.
      $ret = $this->getModeNamesAndWeight($alphabet);
      foreach ($ret as $weight => &$modenames) {
        foreach ($modenames as &$modename) {
          $modename = $this->getModeByName($modename, $alphabet);
        }
      }
      if (count($ret) > 0) {
        return $ret;
      }
      return false;
    }

    public function getModesByTarget($target, $alphabet = 0) {
      // Retrieve the requested modes if they exist, otherwise return false.
      $modes = array();
      foreach ($this->getModeNamesByTarget($target, $alphabet) as $name) {
        if (isset($this->modes[$alphabet]["byname"][$name])) {
          $modes[] = $this->modes[$alphabet]["byname"][$name];
        }
      }
      if (count($modes) > 0) {
        return $modes;
      }
      return false;
    }

    public function getModesByType($type, $alphabet = 0) {
      // Retrieve the requested modes if they exist, otherwise return false.
      $modes = array();
      foreach ($this->getModeNamesByType($type, $alphabet) as $name) {
        if (isset($this->modes[$alphabet]["byname"][$name])) {
          $modes[] = $this->modes[$alphabet]["byname"][$name];
        }
      }
      if (count($modes) > 0) {
        return $modes;
      }
      return false;
    }

    public function getModeStringComponents($ms, $letters = false,
        $ignore = array(), $alphabet = 0) {
      $modes = array();
      $params = array();
      foreach ($ms as $mode) {
        if (is_array($ignore) && !in_array($mode["name"], $ignore)) {
          $m = $this->getModeByName($mode["name"], $alphabet);
          if ($m != false) {
            if ($m[3] == "0") {
              $modes[] = ($letters == false ? $m[0] : $m[1]);
            }
            if ($m[3] == "1" || $m[3] == "2") {
              $modes[] = ($letters == false ? $m[0] : $m[1]);
              $params[] = $mode["param"];
            }
          }
        }
      }
      return array($modes, $params);
    }

    public function parseModes($type, $modeString, $alphabet = 0) {
      $mex = array($modeString);
      if (stristr($mex[0], " ")) {
        $mex = explode(" ", $mex[0]);
      }

      $operation = "+";
      $modes = array();
      $ms = str_split(array_shift($mex));
      $mex = array_values($mex);
      foreach ($ms as $m) {
        if ($m == "+" || $m == "-") {
          $operation = $m;
        }
        else {
          $mode = $this->getModeByChar($type, $m, $alphabet);
          if ($mode != false) {
            if ($operation == "+" && in_array($mode[3],
            array("1", "2", "3", "4")) && isset($mex[0])) {
              $modes[] = array(
                "operation" => $operation,
                "name" => $mode[0],
                "param" => array_shift($mex)
              );
              $mex = array_values($mex);
            }
            elseif ($operation == "+" && in_array($mode[3],
            array("0", "3"))) {
              $modes[] = array(
                "operation" => $operation,
                "name" => $mode[0]
              );
            }
            elseif ($operation == "-" && in_array($mode[3],
            array("1", "3", "4")) && isset($mex[0])) {
              $modes[] = array(
                "operation" => $operation,
                "name" => $mode[0],
                "param" => array_shift($mex)
              );
              $mex = array_values($mex);
            }
            elseif ($operation == "-" && in_array($mode[3],
            array("0", "2", "3"))) {
              $modes[] = array(
                "operation" => $operation,
                "name" => $mode[0]
              );
            }
          }
        }
      }
      Logger::debug("Modes parsed:");
      Logger::debug(var_export($modes, true));
      return $modes;
    }

    public function setMode($mode, $alphabet = 0) {
      $this->unsetMode($mode);
      for ($i = 0; $i < 4; $i++) {
        $mode[$i] = strval($mode[$i]);
      }
      // [name, char, [0 (channel), 1 (user)], type, {prefix}, {weight}]
      // Types:
      // 0 - Never requires a parameter
      // 1 - Parameter required when set/unset
      // 2 - Parameter required when set
      // 3 - List type mode
      // 4 - Status type mode
      // 5 - Key
      $this->modes[$alphabet]["byname"][$mode[0]] = array($mode[0], $mode[1],
        $mode[2], $mode[3], (isset($mode[4]) ? $mode[4] : null),
        (isset($mode[5]) ? $mode[5] : null));
      $this->modes[$alphabet]["bychar"][$mode[2]][$mode[1]] = $mode[0];
      if (isset($mode[4])) {
        $this->modes[$alphabet]["byprefix"][$mode[4]] = $mode[0];
      }
      $this->modes[$alphabet]["bytarget"][$mode[2]][] = $mode[0];
      $this->modes[$alphabet]["bytype"][$mode[3]][] = $mode[0];
      if (isset($mode[5])) {
        if (!isset($this->modes[$alphabet]["byweight"][$mode[5]])) {
          $this->modes[$alphabet]["byweight"][$mode[5]] = array();
        }
        $this->modes[$alphabet]["byweight"][$mode[5]][] = $mode[0];
        ksort($this->modes[$alphabet]["byweight"]);
      }
      Logger::debug("Current mode state:");
      Logger::debug(var_export($this->modes, true));
    }

    public function unsetMode($mode, $alphabet = 0) {
      $names = array();

      foreach ($this->modes[$alphabet]["byname"] as $m) {
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
        foreach ($this->modes[$alphabet]["byname"] as $key => $mode) {
          if ($key == $name) {
            unset($this->modes[$alphabet]["byname"][$key]);
          }
        }
        foreach ($this->modes[$alphabet]["bychar"] as $key => $mode) {
          if ($mode == $name) {
            unset($this->modes[$alphabet]["bychar"][$key]);
          }
        }
        foreach ($this->modes[$alphabet]["byprefix"] as $key => $mode) {
          if ($mode == $name) {
            unset($this->modes[$alphabet]["byprefix"][$key]);
          }
        }
        foreach ($this->modes[$alphabet]["bytarget"] as $key => $modes) {
          foreach ($modes as $key1 => $mode) {
            if ($mode == $name) {
              unset($this->modes[$alphabet]["bytarget"][$key][$key1]);
            }
          }
        }
        foreach ($this->modes[$alphabet]["bytype"] as $key => $modes) {
          foreach ($modes as $key1 => $mode) {
            if ($mode == $name) {
              unset($this->modes[$alphabet]["bytype"][$key][$key1]);
            }
          }
        }
        foreach ($this->modes[$alphabet]["byweight"] as $key => $modes) {
          foreach ($modes as $key1 => $mode) {
            if ($mode == $name) {
              unset($this->modes[$alphabet]["byweight"][$key][$key1]);
            }
          }
        }
      }
      Logger::debug("Current mode state:");
      Logger::debug(var_export($this->modes, true));
    }

    public function isUnloadable() {
      return false;
    }

    public function isInstantiated() {
      // Create the default alphabet structure
      $this->createAlphabet();
      return true;
    }
  }
?>
