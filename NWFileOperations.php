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

		Github release
		8. May 2015

		This is a function set for
		file operations like move,
		delete, rename.
	*/

	/*
		Switch debug mode on and off.

		If on, logs will be written using NWLog Functions.
	*/

	define("DEBUG", false);

	/*
		NWPathComplete (v1.0)
			Completes a given path to match the following pattern:
			./path/

		$path
			the path to complete, path must exist.
	*/

	function NWPathComplete($path)
	{
		$path = trim($path);
		if (substr($path, 0, 1) == "/") {
			$path = substr($path, 1);
		}
		if (file_exists($path) AND substr($path, 0, 2) != "./") {
			$path = $path;
			if (!is_file($path) and substr($path, -1) != "/") {
				$path = "$path/";
			}
			return $path;
		} elseif (file_exists($path)) {
			if (substr($path, 0, 2) != "./") {
				$path = "./$path";
			}

			if (!is_file($path) and substr($path, -1) != "/") {
				$path = "$path/";
			}

			return $path;
		}

		if (DEBUG) NWWriteLog("$path does not exist.");
		return false;
	}

	/*
		NWDelete (v1.0)
			Deletes an object.

		$object
			the object to remove
	*/

	function NWDelete($object)
	{
		if (!$object = NWPathComplete($object)) {
			if (DEBUG) NWWriteLog("Error completing path.");
			return false;
		}

		if (!is_dir($object)) {
			if (!unlink($object)) {
				if (DEBUG) NWWriteLog("Error deleting $object");
				return false;
			}
		} else {
			$scanDir = scandir($object);
			if (count($scanDir) >= 1) {
				foreach ($scanDir as $value) {
					if ($value != ".." AND $value != ".") {
						NWDelete($object.$value);
					}
				}
			}

			rmdir($object);
		}

		return true;
	}

	/*
		NWCreate (v1.0)
			Creates an object.

		$filename
			the filename of the object to create

		$path
			the path to the object

		$content
			the content of the object

		$plusone
			true: if $filename exists, _n will be added while
				$filename_n exists
			false: produces error if $filename exists
	*/

	function NWCreate($filename, $path = "./", $content="", $plusone = true)
	{

		if (!$path = NWPathComplete($path)) {
			if (DEBUG) NWWriteLog("Couldn't resolve path.");
			return false;
		}

		if ($plusone) {
			$file = NWPlusOne($filename, $path);
		} else {
			$file = $path.$filename;
		}

		if (file_exists($file)) {
			if (DEBUG) NWWriteLog("File $filename already exists in $path.");
			return false;
		}

		if (!file_put_contents($file, $content)) {
			if (DEBUG) NWWriteLog("Error creating file.");
			return false;
		}

		return $file;
	}

	/*
		NWMKDir (v1.0)
			Creates a directory.

		$dirName
			the name of the directory to create

		$path
			the path to the new directory
	*/

	function NWMKDir($dirName, $path="./")
	{
		if (!$path = NWPathComplete($path)) {
			if (DEBUG) NWWriteLog("Couldn't resolve path.");
			return false;
		}

		if (file_exists($path.$dirName)) {
			$newPath = NWPlusOne($dirName, $path);
		} else {
			$newPath = $path.$dirName;
		}

		if (!mkdir($newPath, 0777, true)) {
			if (DEBUG) NWWriteLog("Couldn't create directory.");
			return false;
		}

		return true;
	}

	/*
		NWEmptyDir (v1.0)
			Deletes all contents of the given directory

		$object
			the name of the directory to clear
	*/

	function NWEmptyDir($object = "./")
	{
		if (!$object = NWPathComplete($object)) {
			if (DEBUG) NWWriteLog("Error completing path.");
			return false;
		}

		$scanDir = scandir($object);
		if (count($scanDir) >= 1) {
			foreach ($scanDir as $value) {
				if ($value != ".." AND $value != ".") {
					NWDelete($object.$value);
				}
			}
		}

		return true;
	}

	/*
		NWListDir (v1.0)
			Lists the contents of a directory

		$dir
			the name of the directory to list

		$showHidden
			true: shows files and directories starting with .
			flase: does not
	*/

	function NWListDir($dir = "./", $showHidden = false)
	{

		if (!file_exists($dir)) {
			if (DEBUG) NWWriteLog("Requested directory does not exist.");
			return false;
		}
		$directoryHandle = opendir($dir);

		while ($datei = readdir($directoryHandle)) {
			if ($datei != ".." AND $datei != ".") {
				if (!$showHidden) {
					if (substr($datei, 0, 1) != ".") {
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

	/*
		Alias for NWListDir()
	*/

	function ls($dir = "./", $showHidden = false)
	{
		return NWListDir($dir, $showHidden);
	}

	/*
		NWFileSize (v1.0)
			Returns an array with the filesize in B, KB and MB

		$object
			the name of the object
	*/

	function NWFileSize($object)
	{
		if (!$object = NWPathComplete($object)) {
			if (DEBUG) NWWriteLog("Couldn't check $object - Does it exist?");
			return false;
		}

		$size = 0;

		if (is_file($object)) {
			$size = $size + filesize($object);
		} else {
			foreach (ls($object) as $file) {
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

	/*
		NWCopy (v1.0)
			Copies an object.

		$source
			the object to copy. Must be a full path

		$destination
			false: duplicates $source
			string: copies $source to $destination
				if $destination does not start with "./",
				function assumes $destination is inside
				$source
				"folder/" will be interpreted as "$source/folder/"
				"./folder/" will be interpreted as "./folder"

		$overwrite
			false: uses NWPlusOne to append a number
			true: overwrites $destination if it already exists
	*/

	function NWCopy($source=false, $destination=false, $overwrite=false)
	{
		if (!$source) {
			if (DEBUG) NWWriteLog("No source given.");
			return false;
		}

		if (!$source = NWPathComplete($source)) {
			if (DEBUG) NWWriteLog("NWPathComplete failed with Source: $source.");
			return false;
		}

		$sourceInfo = pathinfo($source);

		if (!$destination) {
			$destinationFolder = NWPathComplete($sourceInfo["dirname"]);
			$destinationFilename = $sourceInfo["basename"];
		} else {
			if (substr($destination, 0, 2) != "./") {
				$destination =	NWPathComplete($sourceInfo["dirname"]).$destination;
			}
			$destInfo = pathinfo($destination);
			$destinationFolder = NWPathComplete($destInfo["dirname"]);
			$destinationFilename = $destInfo["basename"];
		}

		if (!$overwrite) {
			$destinationPath = NWPlusOne($destinationFilename, NWPathComplete($destinationFolder));
		} else {
			$destinationPath = $destinationFolder.$destinationFilename;
		}

		if (is_file($source)) {
			if (!copy($source, $destinationPath)) {
				if (DEBUG) NWWriteLog("Couldn't copy files.");
				return false;
			}
		} else {
			NWMKDir($sourceInfo["basename"], $destinationFolder);
			$destinationPath = $destinationPath."/";
			foreach (ls($source) as $file) {
				NWCopy($source.$file, $destinationPath.$file, $overwrite);
			}
		}

		return true;
	}

	/*
		NWRename (v1.0)
			Renames an object.

		$oldname
			the old name of the object

		$newname
			the new name of the object

		$path
			the path to the object
	*/

	function NWRename($oldname, $newname, $path = "./")
	{
		if (!$path = NWPathComplete($path)) {
			if (DEBUG) NWWriteLog("Couldn't resolve path.");
			return false;
		}

		$oldInfo = pathinfo($oldname);
		$newInfo = pathinfo($newname);

		$oldpath = $path.$oldInfo["basename"];
		$newpath = NWPlusOne($newInfo["basename"], $path);

		if (!file_exists($oldpath)) {
			if (DEBUG) NWWriteLog("$oldpath does not exist.");
			return false;
		}

		if (!rename($oldpath, $newpath)) {
			if (DEBUG) NWWriteLog("Couldn't rename $oldpath to $newpath");
			return false;
		}

		return true;
	}

	/*
		NWMove (v1.0)
			Moves an object.

		$filename
			the name of the object

		$newpath
			the new path to the object

		$oldpath
			the old path to the object
	*/

	function NWMove($filename, $newpath, $oldpath="./")
	{
		if (!$oldpath = NWPathComplete($oldpath)) {
			if (DEBUG) NWWriteLog("Couldn't resolve old path.");
			return false;
		}
		if (!$newpath = NWPathComplete($newpath)) {
			if (DEBUG) NWWriteLog("Couldn't resolve new path.");
			return false;
		}

		if (!file_exists($oldpath.$filename)) {
			if (DEBUG) NWWriteLog("$oldpath$filename does not exist.");
			return false;
		}

		if (!rename($oldpath.$filename, $newpath.$filename)) {
			if (DEBUG) NWWriteLog("Couldn't move $filename from $oldpath to $newpath");
			return false;
		}

		return true;
	}

	/*
		NWCountLines (v1.0)
			Counts the lines of a file.

		$filename
			the name of the object

		$dir
			the path to the object
	*/

	function NWCountLines($filename, $dir="./")
	{
		$filename = trim($filename);
		if (!$dir = NWPathComplete($dir)) {
			if (DEBUG) NWWriteLog("Couldn't resolve path '$dir'.");
			return false;
		}
		$file = $dir.$filename;
		if ($filename == "" OR !file_exists($file)) {
			if (DEBUG) NWWriteLog("File '$file' does not exist.");
			return false;
		}

		$linesArr = file($file);
		return count($linesArr);
	}

	/*
		NWReadFileToLine (v1.0)
			Reads a portion of a file from and to a specified line

		$file
			the name of the object

		$lineEnd
			indicates the line where the function must stop reading

		$lineStart
			indicates the line where the function must star reading

		$path
			the path to the object
	*/

	function NWReadFileToLine($file, $lineEnd = 0, $lineStart = 0, $path="./")
	{

		$lineEnd = $lineEnd - 1;

		if (!$path = NWPathComplete($path)) {
			if (DEBUG) NWWriteLog("Couldn't resolve path.");
			return false;
		}

		$linesArr = file($path.$file);

		$maxLines = count($linesArr);

		if ($lineEnd <= 0 OR $lineEnd < $lineStart) {
			$lineEnd = $maxLines;
			if (DEBUG) NWWriteLog("Line parameters are not as expected #1. Modifying.");
		}

		if ($lineEnd > $maxLines) {
			$lineEnd = $maxLines;
			if (DEBUG) NWWriteLog("Line parameters are not as expected #2 (End: $linesEnd but Max: $maxLines). Modifying.");
		}

		if ($lineStart > $maxLines) {
			$lineEnd = $lineEnd - 1;
			if (DEBUG) NWWriteLog("Line parameters are not as expected #2. Modifying.");
		}

		for ($i = $lineStart; $i <= $lineEnd; $i++) {
			$returnArray[] = $linesArr[$i];
		}


		return implode("", $returnArray);
	}

	/*
		NWPlusOne (v1.0)
			Appends a number to a file while filename already exists

		$file
			the name of the object

		$directory
			the path to the object

		$sub_opt
			false: underscore is used to seperate the number
				from the filename like "file_1", "file_2"
			true: space is used to seperate the number from
				the filename like "file 1", "file 2"
	*/

	function NWPlusOne($file, $directory="./", $sub_opt = false)
	{
		$appender = 1;
		if ($sub_opt) {
			$sub = "_";
		} else {
			$sub = " ";
		}

		$pathinfo = pathinfo($directory.$file);
		$filename = $pathinfo["filename"];
		if (array_key_exists("extension", $pathinfo)) {
			$extension = $pathinfo["extension"];
		} else {
			$extension = "";
		}
		if ($extension != "") {
			$filename_check = $directory.$filename.$sub.$appender.".".$extension;
		} else {
			$filename_check = $directory.$filename.$sub.$appender;
		}
		if (file_exists($directory.$file)) {
			while (file_exists($filename_check)) {
				$appender++;
				if ($extension != "") {
					$filename_check = $directory.$filename.$sub.$appender.".".$extension;
				} else {
					$filename_check = $directory.$filename.$sub.$appender;
				}
			}
		} else {
			if ($extension != "") {
				$filename_check = $directory.$filename.".".$extension;
			} else {
				$filename_check = $directory.$filename;
			}
		}

		return $filename_check;
	}
?>