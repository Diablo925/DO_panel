<?php

class module_controller extends ctrl_module
{
		
		static $ok;
		static $Addok;
		
    /**
     * The 'worker' methods.
     */
	 
	  static function ExecuteAddApi($key)
	 {
		global $zdbh;
		$sql = "UPDATE x_doapi SET apikey = :key";
        $sql = $zdbh->prepare($sql);
        $sql->bindParam(':key', $key);
        $sql->execute();
        self::$ok = true;
	 }

	
	   static function ExecuteAction($type, $id)
	 {
		 global $zdbh;
		 global $controller;
		 $sql = "SELECT * FROM x_doapi";
        $sql = $zdbh->prepare($sql);
        $sql->execute();
        while ($row = $sql->fetch()) {
		$apikey = $row["apikey"];
		}
		 $data = array("type" => "$type");
		 $data_string = json_encode($data);
$url = "https://api.digitalocean.com/v2/droplets/".$id."/actions";
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Authorization: Bearer '. $apikey .'',
    'Content-Type: application/json',
      'Content-Length: ' . strlen($data_string))
);
curl_exec($ch);	 
		self::$ok = true;
        return true;
	 }
	 static function ExecuteMake($Name, $Size, $Region, $Image, $Backup, $ipv6)
	 {
		 global $zdbh;
		 global $controller;
		 
		 $sql = "SELECT * FROM x_doapi";
        $sql = $zdbh->prepare($sql);
        $sql->execute();
        while ($row = $sql->fetch()) {
		$apikey = $row["apikey"];
		}
		 if(isset($Backup)) { $Backup = "true"; } else { $backup = "false"; }
		 if(isset($ipv6)) { $ipv6 = "true"; } else { $ipv6 = "false"; }
		 $data = array("name" => "$Name", "region" => "$Region", "size" => "$Size", "image" => "$Image", "backups" => "$Backup", "ipv6" => "$ipv6");
		 $data_string = json_encode($data);
$url = "https://api.digitalocean.com/v2/droplets";
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Authorization: Bearer '.$apikey.'',
    'Content-Type: application/json',
      'Content-Length: ' . strlen($data_string))
);
curl_exec($ch);	 
		self::$Addok = true;
        return true;
	 }
	 
	 static function ListDroplet($uname)
	{
	    global $zdbh;
		global $controller;
		
		$sql = "SELECT * FROM x_doapi";
        $sql = $zdbh->prepare($sql);
        $sql->execute();
        while ($row = $sql->fetch()) {
		$apikey = $row["apikey"];
		}
		
		$ch = curl_init('https://api.digitalocean.com/v2/droplets');
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,TRUE);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    	'Authorization: Bearer '.$apikey.'',
    	'Content-Type: application/json'));
		$result =  curl_exec($ch);
		$data = json_decode($result , true);
		foreach ($data['droplets'] as $drops) {
			if ($drops['status'] == 'active') {
				$status = '<button class="button-loader btn btn-danger" type="submit" id="button" name="inAction" id="inShutDown" value="shutdown">Shutdown</button>';
			} else {
				$status = '<button class="button-loader btn btn-primary" type="submit" id="button" name="inAction" id="inPowerOn" value="power_on">Power On</button>';
			}
				
		$res[] = array('Id' => $drops['id'],
					    'Os' => '<img src="modules/' . $controller->GetControllerRequest('URL', 'module') . '/assets/os/'.$drops['image']['distribution'].'.png" alt="'.$drops['image']['distribution'].'"/>',
						'Name' => $drops['name'],
						'Memmory' => $drops['memory'],
						'VCpu' => $drops['vcpus'],
						'Hdd' => $drops['disk'],
						'ip' => $drops['networks']['v4']['0']['ip_address'],
						'status' => $status);
		}
    return $res;
	}
	
	static function ListImage()
	{
	    global $zdbh;
		global $controller;
		
		$sql = "SELECT * FROM x_doapi";
        $sql = $zdbh->prepare($sql);
        $sql->execute();
        while ($row = $sql->fetch()) {
		$apikey = $row["apikey"];
		}
		
		$ch = curl_init('https://api.digitalocean.com/v2/images');
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,TRUE);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    	'Authorization: Bearer '.$apikey.'',
    	'Content-Type: application/json'));
		$result =  curl_exec($ch);
		$data = json_decode($result , true);
		foreach ($data['images'] as $images) {
			if($images['public'] == "true") {
		$res[] = array('slug' => $images['slug']);
			}
		}
    return $res;
	}
	
	static function ListRegion()
	{
	    global $zdbh;
		global $controller;
		$sql = "SELECT * FROM x_doapi";
        $sql = $zdbh->prepare($sql);
        $sql->execute();
        while ($row = $sql->fetch()) {
		$apikey = $row["apikey"];
		}
		$ch = curl_init('https://api.digitalocean.com/v2/regions');
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,TRUE);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    	'Authorization: Bearer '.$apikey.'',
    	'Content-Type: application/json'));
		$result =  curl_exec($ch);
		$data = json_decode($result , true);
		foreach ($data['regions'] as $regions) {
		if($regions['available'] == "true") {
			$res[] = array('slug' => $regions['slug'], 'name' => $regions['name']);
		}
		
		}
    return $res;
	}
	
	static function ListSize()
	{
	    global $zdbh;
		global $controller;
		$sql = "SELECT * FROM x_doapi";
        $sql = $zdbh->prepare($sql);
        $sql->execute();
        while ($row = $sql->fetch()) {
		$apikey = $row["apikey"];
		}
		$ch = curl_init('https://api.digitalocean.com/v2/sizes');
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,TRUE);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    	'Authorization: Bearer '.$apikey.'',
    	'Content-Type: application/json'));
		$result =  curl_exec($ch);
		$data = json_decode($result , true);
		foreach ($data['sizes'] as $sizes) {
			if($sizes['available'] == "true") {
			$res[] = array('slug' => $sizes['slug']);
			}
		}
    return $res;
	}

	static function getApikey()
	{
		global $zdbh;
        $sql = "SELECT * FROM x_doapi";
        $sql = $zdbh->prepare($sql);
        $sql->execute();
        while ($row = $sql->fetch()) {
		if($row['apikey'] == "NULL") { $res = '
		<input type="text" name="inApiKey" id="inApiKey" placeholder="Add Digital oacen api key"/>
		<button class="button-loader btn btn-danger" type="submit" id="button" name="inKey" id="inkey" value="Addkey">Add key</button>'; } else {
		$res = '<input type="hidden" name="inApiKey" id="inApiKey" value="NULL"/>
		<button class="button-loader btn btn-danger" type="submit" id="button" name="inKey" id="inKey" value="RemoveKey">Remove key</button>';
		}
		
		}
		return $res;
	}

	/**
     * End 'worker' methods.
     */


    /**
     * Webinterface sudo methods.
     */ 
	 
	 static function dokey()
	{
		global $controller;
        $currentuser = ctrl_users::GetUserDetail();
        $formvars = $controller->GetAllControllerRequests('FORM');
        if (self::ExecuteAddApi($formvars["inApiKey"]))
        return true;
    } 
	
	  static function doaction()
    {
        global $controller;
        $currentuser = ctrl_users::GetUserDetail();
        $formvars = $controller->GetAllControllerRequests('FORM');
        if (self::ExecuteAction($formvars['inAction'], $formvars['inId']))
        return true;
    }
	
	static function doAdd()
    {
        global $controller;
        $currentuser = ctrl_users::GetUserDetail();
        $formvars = $controller->GetAllControllerRequests('FORM');
        if (self::ExecuteMake($formvars['inName'], $formvars['inSize'], $formvars['inRegion'], 
								$formvars['inImage'], $formvars['Inbackup'], $formvars['Inipv6']))
        return true;
    } 
	
	
	
	static function getDroplet()
    {
        $currentuser = ctrl_users::GetUserDetail();
        return self::ListDroplet($currentuser['username']);
    }
	
	static function getSizeList()
    {
        $currentuser = ctrl_users::GetUserDetail();
        return self::ListSize($currentuser['username']);
    }
	
	static function getImageList()
	{
        return self::ListImage();
	}
	static function getRegionList()
	{
        return self::ListRegion();
	}
	static function getResult()
    {
		 if (self::$ok) {
            return ui_sysmessage::shout(ui_language::translate("Command sent"), "zannounceok");
        }
		if (self::$Addok) {
            return ui_sysmessage::shout(ui_language::translate("Droplet addet"), "zannounceok");
        }
        return;
    }
	
	 /**
     * Webinterface sudo methods.
     */
	
}
?>