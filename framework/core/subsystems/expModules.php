<?php
/**
 *  This file is part of Exponent
 *  Exponent is free software; you can redistribute
 *  it and/or modify it under the terms of the GNU
 *  General Public License as published by the Free
 *  Software Foundation; either version 2 of the
 *  License, or (at your option) any later version.
 *
 * The file that holds the expModules class
 *
 * @link http://www.gnu.org/licenses/gpl.txt GPL http://www.gnu.org/licenses/gpl.txt
 * @package Exponent-CMS
 * @copyright 2004-2011 OIC Group, Inc.
 * @author Phillip Ball <phillip@oicgroup.net>
 * @version 2.0.0
 */

/**
 * This is the class expModules
 *
 * @subpackage Core-Subsystems
 * @package Framework
 */

class expModules {

	public static function initializeControllers() {
	    $controllers = array();
	//    loadModulesDir(BASE.'themes/'.DISPLAY_THEME_REAL.'/modules', $controllers);
	    self::loadModulesDir(BASE.'themes/'.DISPLAY_THEME.'/modules', $controllers);
	    self::loadModulesDir(BASE.'framework/modules', $controllers);
	    return $controllers;
	}

	// recursive function used for (auto?)loading 2.0 modules controllers & models
	public static function loadModulesDir($dir, &$controllers) {
		global $db;
	    if (is_readable($dir)) {
	        $dh = opendir($dir);
	        while (($file = readdir($dh)) !== false) {
	            if (is_dir($dir.'/'.$file) && ($file != '..' && $file != '.')) {
	                // load controllers
	                $dirpath = $dir.'/'.$file.'/controllers';
	                if (file_exists($dirpath)) {
	                    $controller_dir = opendir($dirpath);
	                    while (($ctl_file = readdir($controller_dir)) !== false) {
	                        if (empty($controllers[substr($ctl_file,0,-4)]) && substr($ctl_file,-4,4) == ".php") {
	                            include_once($dirpath.'/'.$ctl_file);
	                            $controllers[substr($ctl_file,0,-4)] = $dirpath.'/'.$ctl_file;
	//	                        $module->module = substr($ctl_file,0,-4);
	//	                        $module->active = 1;
	//	                        $module->path = $dirpath.'/'.$ctl_file;
	//	                        if (($db->selectObject('modstate','module = "'.substr($ctl_file,0,-4).'"')) == null) $db->insertObject($module,'modstate');
	                        }
	                    }
	                }
	                // load models
	                $dirpath = $dir.'/'.$file.'/models';
	                if (file_exists($dirpath)) {
	                    $controller_dir = opendir($dirpath);
	                    while (($ctl_file = readdir($controller_dir)) !== false) {
	                        if (empty($controllers[substr($ctl_file,0,-4)]) && substr($ctl_file,-4,4) == ".php") {
	                            include_once($dirpath.'/'.$ctl_file);
	                            $controllers[substr($ctl_file,0,-4)] = $dirpath.'/'.$ctl_file;
	//                            $module->module = substr($ctl_file,0,-4);
	//                            $module->active = 1;
	//                            $module->path = $dirpath.'/'.$ctl_file;
	//	                          if (($db->selectObject('modstate','module = "'.substr($ctl_file,0,-4).'"')) == null) $db->insertObject($module,'modstate');
	                        }
	                    }
	                }
	            }
	        }
	    }
	}

    public static function listActiveControllers() {
        global $db;
        
        $controllers = expModules::listUserRunnableControllers();
        
        foreach ($controllers as $module) {
    		if (class_exists($module)) {
    			$mod = new $module();
    			$modstate = $db->selectObject("modstate","module='$module'");

    			$moduleInfo[$module] = null;
    			$moduleInfo[$module]->class = $module;
    			$moduleInfo[$module]->name = $mod->name();
    			$moduleInfo[$module]->author = $mod->author();
    			$moduleInfo[$module]->description = $mod->description();
    			$moduleInfo[$module]->codequality = isset($mod->codequality) ? $mod->codequality : 'alpha';
    			$moduleInfo[$module]->active = ($modstate != null ? $modstate->active : 0);
    		}
    	}
        $moduleInfo = expSorter::sort(array('array'=>$moduleInfo,'sortby'=>'name', 'order'=>'ASC', 'ignore_case'=>true));
    	return $moduleInfo;
    }

	public static function listUserRunnableControllers() {
	    global $available_controllers;

	    $controllers = array();
	    foreach($available_controllers as $name=>$path) {
	        $controller = new $name();
	        if (!empty($controller->useractions)) $controllers[] = $name;
	    }

	    return $controllers;
	}

	public static function listInstalledControllers($type=null, $loc=null) {
	    if (empty($type)) return array();
	        global $db;

	        // setup the where clause
	        $where = 'module="'.$type.'"';
	        if (!empty($loc)) $where .= " AND source != '".$loc->src."'";

	        $refs = $db->selectObjects('sectionref', $where);
	        $modules = array();
	        foreach ($refs as $ref) {
	            if ($ref->refcount > 0) {
	                $instance = $db->selectObject('container', 'internal like "%'.$ref->source.'%"');
	                $mod = null;
	                $mod->title = !empty($instance->title) ? $instance->title : "Untitled";
	                $mod->section = $db->selectvalue('section', 'name', 'id='.$ref->section);
                    $mod->src = $ref->source;
	                $modules[$ref->source] = $mod;
	            }
	        }

	        return $modules;
	}

	public static function listControllers() {
	    global $available_controllers;
	    return $available_controllers;
	}

	public static function getController($controllername='') {
	    $fullname = expModules::getControllerClassName($controllername);
	    if (expModules::controllerExists($controllername))  {
	        $controller = new $fullname();
	        return $controller;
	    } else {
	        return null;
	    }
	}

	public static function controllerExists($controllername='') {
	    global $available_controllers;

	    // make sure the name is in the right format
	    $controllername = expModules::getControllerClassName($controllername);

	    // check for module based controllers
	    if (array_key_exists($controllername, $available_controllers)) {
	        if (is_readable($available_controllers[$controllername])) return true;
	    } else {
	        // check for core controllers
	        if (is_readable(BASE.'framework/core/controllers/'.expModules::getControllerClassName($controllername).'.php')) return true;
	    }

	    // if we got here we didn't find any controllers matching the name
	    return false;
	}

	public static function getControllerClassName($controllername) {
	    if (empty($controllername)) return null;
	    return (substr($controllername, -10) == 'Controller') ? $controllername : $controllername.'Controller';
	}

	public static function getControllerName($controllername) {
	    if (empty($controllername)) return null;
	        return (substr($controllername, -10) == 'Controller') ? substr($controllername, 0, -10) : $controllername;
	}

	/** exdoc
	 * Looks through the database returns a list of all module class
	 * names that exist in the system and have been turned on by
	 * the administrator.  Inactive modules will not be included.
	 * Returns the list of active module class names.
	 * @node Subsystems:Modules
	 * @return array
	 */
	public static function getActiveModulesAndControllersList() {
		global $db;

        $modulestates = $db->selectObjects("modstate","active='1'");

        $mods = array();  // 1.0 modules
        foreach ($modulestates as $state) {
            if (class_exists($state->module)) $mods[] = $state->module;
        }

	    $ctls = array();  // 2.0 modules
	    foreach($modulestates as $state) {
	        if (expModules::controllerExists($state->module)) {
	            $controller = new $state->module();
	            if (!empty($controller->useractions)) {
		            $ctls[] = $state->module;
	            }
	        }
	    }

	    return array_merge($ctls, $mods);
	}

    public static function modules_list() {
    	$mods = array();
    	if (is_readable(BASE."framework/modules-1")) {
    		$dh = opendir(BASE."framework/modules-1");
    		while (($file = readdir($dh)) !== false) {
    			if (substr($file,-6,6) == "module") $mods[] = $file;
    		}
    	}
    	return $mods;
    }

	public static function listActiveOSMods() {
		global $db;

		$osmods = self::modules_list();

		foreach ($osmods as $module) {
			if (class_exists($module)) {
				 $mod = new $module();
				 $modstate = $db->selectObject("modstate","module='$module'");

				 if (!method_exists($mod,"dontShowInModManager")) {
				     $moduleInfo[$module] = null;
				     $moduleInfo[$module]->class = $module;
				     $moduleInfo[$module]->name = $mod->name();
				     $moduleInfo[$module]->author = $mod->author();
				     $moduleInfo[$module]->description = $mod->description();
				     $moduleInfo[$module]->active = ($modstate != null ? $modstate->active : 0);
				 }
			}
		}
		return $moduleInfo;
	}

}
?>
