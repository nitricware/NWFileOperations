<?php
	/*
		   /\
		  /  \   NitricWare Studios
		 / /\ \  Linz/Danube
		/\/\/\/
		\/
	
		NitricWare presents
		NWFileOperations 1.0
		Version 1.0
		
		Development started
		14. January 2013
		
		This is a function set for
		file operations like move,
		delete, rename.
	*/
	
	function NWFileInfo($file){
		$file = NWPathComplete($file);
		if (!file_exists($file)){
			if (DEBUG) NWWriteLog("File does not exist.");
			return false;
		}
		
		return pathinfo($file);
	}
	
	function NWIsSysFile($file){
		
		NWWriteLog($file);
		$file = NWPathComplete(strtolower($file));
		
		NWWriteLog($file);
		
		foreach (unserialize(SYSFILES) as $sysfiles){
			if (strpos($sysfiles, $file) !== false){
				return true;
			}
		}
		
		if (strpos($file, "./system/") !== false){
			return true;
		}
		
		if (trim($file) == "./"){
			return true;
		}
		
		return false;
	}
	
	function NWFileExists($filename,$extension,$path=CURRENT_DIR){
		if (!$path = NWPathComplete($path)) return false;
		if (file_exists("$path$filename.$extension")) return "$path$filename.$extension";
		return false;
	}
	
	function NWPathComplete($path){
		$path = trim($path);
		if (substr($path,0,1) == "/"){
			$path = substr($path,1);
		}
		if (file_exists(CURRENT_DIR.$path) AND substr($path,0,2) != "./"){
			$path = CURRENT_DIR.$path;
			if(!is_file($path) and substr($path,-1) != "/"){
				$path = "$path/";
			}
			return $path;
		} elseif (file_exists($path)){
			if (substr($path,0,2) != "./"){
				$path = "./$path";
			}
		
			if(!is_file($path) and substr($path,-1) != "/"){
				$path = "$path/";
			}
			
			return $path;
		}
		
		if (DEBUG) NWWriteLog("$path does not exist.");
		return false;
	}
	
	function NWChangeDirectory($newPath){
		
		if ($newPath == "."){
			$newPath = "./";
		}
		
		if ($newPath == ".."){
			$newPath = dirname(CURRENT_DIR)."/";
		}
		
		if (substr($newPath, -1) != "/"){
			$newPath = "$newPath/";
		}
		
		if (!file_exists($newPath) AND !file_exists(CURRENT_DIR.$newPath)){
			if (DEBUG) NWWriteLog("Directory $newPath and ".CURRENT_DIR.$newPath." do not exist.");
			return false;
		}
		
		writeToFile:
		if (strpos($newPath, "./") !== 0){
			$newPath = CURRENT_DIR.$newPath;
		}
		
		if (!file_put_contents("./System/Library/Databases/curdir.set", $newPath)){
			if (DEBUG) NWWriteLog("Error writing to file.");
			return false;
		} else {
			return true;
		}
			
	}
	
	function cd($newPath){
		return NWChangeDirectory($newPath);
	}
	
	function NWDelete($object, $security = true){
		if (!$object = NWPathComplete($object)){
			if (DEBUG) NWWriteLog("Error completing path.");
			return false;
		}
		
		if (NWIsSysFile($object) AND $security){
			if (DEBUG) NWWriteLog("You tried to delete a system file.");
			return false;	
		}
		
		if (!is_dir($object)){
			if (!unlink($object)){
				if (DEBUG) NWWriteLog("Error deleting $object");
				return false;
			}
		} else {
			$scanDir = scandir($object);
			if (count($scanDir) >= 1){
				foreach ($scanDir as $value){
					if ($value != ".." AND $value != "."){
						NWDelete($object.$value);
					}
				}
			}
			
			rmdir($object);
		}
		
		return true;
	}
	
	function NWFileOutput($object){
		if (!$object = NWPathComplete($object)){
			if (DEBUG) NWWriteLog("Error completing path.");
			return false;
		}
		
		if (is_file($object)){
			$pathinfo = pathinfo($object);
			return "<a href=\"$object\" target=\"_blank\">".$pathinfo["basename"]."</a>";
		}
		
		if (DEBUG) NWWriteLog("Requested Object is not a file.");
		return false;
	}
	
	function NWCreate($filename, $path = CURRENT_DIR, $content="", $plusone = true){
		
		if (!$path = NWPathComplete($path)){
			if (DEBUG) NWWriteLog("Couldn't resolve path.");
			return false;
		}
		
		if ($plusone){
			$file = NWPlusOne($filename, $path);
		} else {
			$file = $path.$filename;
		}
		
		if (file_exists($file)){
			if (DEBUG) NWWriteLog("File $filename already exists in $path.");
			return false;
		}
		
		if (!file_put_contents($file,$content)){
			if (DEBUG) NWWriteLog("Error creating file.");
			return false;
		}
			
		return $file;
	}
	
	function NWMKDir($dirName, $path=CURRENT_DIR){
		if (!$path = NWPathComplete($path)){
			if (DEBUG) NWWriteLog("Couldn't resolve path.");
			return false;
		}
		
		if (file_exists($path.$dirName)){
			$newPath = NWPlusOne($dirName, $path);
		} else {
			$newPath = $path.$dirName;
		}
		
		if (!mkdir($newPath, 0777, true)){
			if (DEBUG) NWWriteLog("Couldn't create directory.");
			return false;	
		}
		
		return true;
	}
	
	function NWEmptyDir($object = CURRENT_DIR){
		if (!$object = NWPathComplete($object)){
			if (DEBUG) NWWriteLog("Error completing path.");
			return false;
		}
		
		if (NWIsSysFile($object)){
			if (DEBUG) NWWriteLog("You tried to delete a system file.");
			return false;	
		}
		
		$scanDir = scandir($object);
		if (count($scanDir) >= 1){
			foreach ($scanDir as $value){
				if ($value != ".." AND $value != "."){
					NWDelete($object.$value);
				}
			}
		}
		
		return true;
	}
	
	function NWListDir($dir = CURRENT_DIR, $showHidden = false){
		
		if(!file_exists($dir)){
			if (DEBUG) NWWriteLog("Requested directory does not exist.");
			return false;	
		}
		$directoryHandle = opendir($dir);
		
		while ($datei = readdir($directoryHandle)) {
			if ($datei != ".." AND $datei != "."){
				if (!$showHidden){
					if (substr($datei, 0, 1) != "."){
						$directoryList[] = $datei;
					}
				} else {
					$directoryList[] = $datei;
				}
			}
		}
		
		closedir($directoryHandle);
		return $directoryList;
	}
	
	function ls($dir = CURRENT_DIR, $showHidden = false){
		return NWListDir($dir,$showHidden);
	}
	
	function NWFileSize($object){
		if (!$object = NWPathComplete($object)){
			if (DEBUG) NWWriteLog("Couldn't check $object - Does it exist?");
			return false;
		}
		
		$size = 0;
		
		if (is_file($object)){
			$size = $size + filesize($object);
		} else {
			foreach (ls($object) as $file){
				$newSize = NWFileSize($object.$file);
				$size = $size + $newSize["bytes"];	
			}
		}
		
		$sizeKB = $size/1024;
		$sizeMB = $sizeKB/1024;
		
		$return = array("bytes" => $size,
						"kilobytes" => $sizeKB,
						"megabytes" => $sizeMB);
		
		return $return;
	}
	
	function NWCopy($source=false, $destination=false, $overwrite=false){
		if (!$source){
			if (DEBUG) NWWriteLog("No source given.");
			return false;	
		}
		
		if (!$source = NWPathComplete($source)){
			if (DEBUG) NWWriteLog("NWPathComplete failed with Source: $source.");
			return false;
		}
		
		$sourceInfo = pathinfo($source);
		
		if (!$destination){
			$destinationFolder = NWPathComplete($sourceInfo["dirname"]);
			$destinationFilename = $sourceInfo["basename"];
		} else {
			if (substr($destination,0,2) != "./"){
				$destination =	NWPathComplete($sourceInfo["dirname"]).$destination;
			}
			$destInfo = pathinfo($destination);
			$destinationFolder = NWPathComplete($destInfo["dirname"]);
			$destinationFilename = $destInfo["basename"];
		}
		
		if (!$overwrite){
			$destinationPath = NWPlusOne($destinationFilename, NWPathComplete($destinationFolder));
		} else {
			$destinationPath = $destinationFolder.$destinationFilename;
		}
		
		if (is_file($source)){
			if (!copy($source, $destinationPath)){
				if (DEBUG) NWWriteLog("Couldn't copy files.");
				return false;
			}
		} else {
			NWMKDir($sourceInfo["basename"],$destinationFolder);
			$destinationPath = $destinationPath."/";
			foreach (ls($source) as $file){
				NWCopy($source.$file, $destinationPath.$file, $overwrite);
			}
		}
		
		return true;
	}
	
	function NWRename($oldname,$newname,$path = CURRENT_DIR){
		if (!$path = NWPathComplete($path)){
			if (DEBUG) NWWriteLog("Couldn't resolve path.");
			return false;
		}
		
		$oldInfo = pathinfo($oldname);
		$newInfo = pathinfo($newname);
		
		$oldpath = $path.$oldInfo["basename"];
		$newpath = NWPlusOne($newInfo["basename"],$path);
		
		if (!file_exists($oldpath)){
			if (DEBUG) NWWriteLog("$oldpath does not exist.");
			return false;
		}
		
		if (!rename($oldpath,$newpath)){
			if (DEBUG) NWWriteLog("Couldn't rename $oldpath to $newpath");
			return false;
		}
		
		return true;
	}
	
	function NWMove($filename, $newpath, $oldpath=CURRENT_DIR){
		if (!$oldpath = NWPathComplete($oldpath)){
			if (DEBUG) NWWriteLog("Couldn't resolve old path.");
			return false;
		}
		if (!$newpath = NWPathComplete($newpath)){
			if (DEBUG) NWWriteLog("Couldn't resolve new path.");
			return false;
		}
		
		if (!file_exists($oldpath.$filename)){
			if (DEBUG) NWWriteLog("$oldpath$filename does not exist.");
			return false;
		}
		
		if (!rename($oldpath.$filename,$newpath.$filename)){
			if (DEBUG) NWWriteLog("Couldn't move $filename from $oldpath to $newpath");
			return false;
		}
		
		return true;
	}
	
	function NWCountLines ($filename, $dir=CURRENT_DIR){
		$filename = trim($filename);
		if (!$dir = NWPathComplete($dir)){
			if (DEBUG) NWWriteLog("Couldn't resolve path '$dir'.");
			return false;
		}
		$file = $dir.$filename;
		if ($filename == "" OR !file_exists($file)){
			if (DEBUG) NWWriteLog("File '$file' does not exist.");
			return false;
		}
		
		$linesArr = file($file);
		return count($linesArr);
	}
	
	function NWReadFileToLine($file, $lineEnd = 0, $lineStart = 0, $path=CURRENT_DIR){
	
		$lineEnd = $lineEnd - 1;
	
		if (!$path = NWPathComplete($path)){
			if (DEBUG) NWWriteLog("Couldn't resolve path.");
			return false;
		}
		
		$linesArr = file($path.$file);
		
		$maxLines = count($linesArr);
		
		if ($lineEnd <= 0 OR $lineEnd < $lineStart){
			$lineEnd = $maxLines;
			if (DEBUG) NWWriteLog("Line parameters are not as expected #1. Modifying.");
		}
		
		if ($lineEnd > $maxLines){
			$lineEnd = $maxLines;
			if (DEBUG) NWWriteLog("Line parameters are not as expected #2 (End: $linesEnd but Max: $maxLines). Modifying.");
		}
		
		if ($lineStart > $maxLines){
			$lineEnd = $lineEnd - 1;
			if (DEBUG) NWWriteLog("Line parameters are not as expected #2. Modifying.");
		}
		
		for ($i = $lineStart; $i <= $lineEnd; $i++) {
			$returnArray[] = $linesArr[$i];
		}
		
		
		return implode("", $returnArray);
	}
?>