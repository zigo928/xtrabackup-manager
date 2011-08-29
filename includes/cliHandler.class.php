<?php
/*

Copyright 2011 Marin Software

This file is part of XtraBackup Manager.

XtraBackup Manager is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
(at your option) any later version.

XtraBackup Manager is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with XtraBackup Manager.  If not, see <http://www.gnu.org/licenses/>.

*/


	// Class to handle the "xbm" generic command functionality
	class cliHandler {


		function setLogStream($log) {
			$this->log = $log;
		}

		// Print top level command help-text
		function printBaseHelpText() {

			echo("Usage: xbm <context> <action> <args> ...\n\n");
			echo("Contexts and actions may be one of the following:\n\n");

			echo("volume [add|list|edit|delete] <args>\t\t -- Manage Backup Volumes\n");

			echo("host [add|list|edit|delete] <args>\t\t -- Manage Hosts to Backup\n");

			echo("backup [add|list|edit|delete] <args>\t\t -- Manage Scheduled Backup Tasks\n");

			echo("snapshot [list|delete]\t\t\t\t -- Manage Backup Snapshots\n");

			echo("restore [local|remote] <args>\t\t\t -- Restore Backups\n");

			echo("\n");
			echo("You may specify only a context, or a context and action to get help on its relevant arguments.\n");
			echo("\n");
		
			return;

		}

		// Handles the arguments given on the command-line
		// Accepts the $argv array 
		function handleArguments($args) {

			// If we arent given any parameters
			if(!isSet($args[1])) {

				// Just output help information and exit

				echo("Error: Context missing.\n\n");

				$this->printBaseHelpText();

				return;
				
			}


			// Handle the first arg to determine what context we're in
			switch($args[1]) {

				// Call volume context handler
				case 'volumes':
				case 'volume':
					$this->handleVolumeActions($args);
				break;

				// Call host context handler
				case 'hosts':
				case 'host':
				break;

				// Call backup context handler
				case 'backup':
				case 'backups':
				break;

				// Call snapshot context handler
				case 'snapshot':
				case 'snapshots':
				break;

				// Call restore context handler
				case 'restore':
				case 'restores':
				break;

				// Handle unknown action context
				default:
					echo("Error: Unrecognized context specified: ".$args[1]."\n\n");
					$this->printBaseHelpText();
				break;				
			}

			return;

		}

		// Print out the help text for volumes context
		function printVolumeHelpText($args) {

				echo("Usage: xbm ".$args[1]." <action> <args> ...\n\n");
				echo("Actions may be one of the following:\n\n");

				echo("add <name> <path>\t\t\t -- Add a New Backup Volume\n");
				echo("list\t\t\t\t\t -- List available Backup Volumes\n");
				echo("edit <name> <parameter> <value>\t\t -- Edit a Backup Volume to set <parameter> to <value>\n");
				echo("delete <name>\t\t\t\t -- Delete a Backup Volume\n");

				echo("\n");
				echo("You may specify an action without parameters to get help on its relevant arguments.\n");
				echo("\n");

				return;

		}


		// Handle actions relating to volumes
		// Accepts an argv array from the command line
		function handleVolumeActions($args) {

			// If we arent given any more parameters
			if(!isSet($args[2]) ) {

				// Just output some helpful information and exit
				echo("Error: Action missing.\n\n");
				$this->printVolumeHelpText($args);

				return;
			}


			// Handle actions
			switch($args[2]) { 

				// Handle add
				case 'add':

					// Verify that we have all the params we need and they are OK.

					// Are they set..
					if(!isSet($args[3]) || !isSet($args[4]) ) {
						// Input exception
						throw new InputException("Error: Name and Path of Backup Volume to add must be given.\n\n  Example:\n\n    xbm ".$args[1].' add "Storage Array 1" /backup');
					}

					$volumeName = $args[3];
					$volumePath = rtrim($args[4], '/');

					$volumeGetter = new volumeGetter();
					$volumeGetter->setLogStream($this->log);

					// Get the new Volume
					$volume = $volumeGetter->getNew($volumeName, $volumePath);

					echo("Action: New volume created with name/path: ".$volumeName." -- ".$volumePath."\n\n");
	
				break;

				// Handle listing
				case 'list':

					$volumeGetter = new volumeGetter();
					$volumeGetter->setLogStream($this->log);


					$volumes = $volumeGetter->getAll();


					echo("-- Listing all Backup Volumes --\n\n");

					foreach($volumes as $volume) {
						$volumeInfo = $volume->getInfo();
						echo("Name: ".$volumeInfo['name']."\tPath: ".$volumeInfo['path']."\n");
					}

					echo("\n\n");
					
				break;

				// Handle editing
				case 'edit':

					if( !isSet($args[3]) || !isSet($args[4]) || !isSet($args[5]) ) {
						$errMsg = "Error: Name of Backup Volume to edit must be given along with parameter and value.\n\n";
						$errMsg .= "  Parameters:\n\n";
						$errMsg .= "    name - The name of the Backup Volume - Can be edited at any time.\n";
						$errMsg .= "    path - The path of the Backup Volume - May only be edited if no Scheduled Backups are configured for the volume.\n\n";
						$errMsg .= "  Example:\n\n    xbm ".$args[1].' edit "Storage Array 1" path /storage1';

						throw new InputException($errMsg);

					}

					$volumeName = $args[3];
					$volumeParam = $args[4];
					$volumeValue = $args[5];

					$volumeGetter = new volumeGetter();
					$volumeGetter->setLogStream($this->log);
					if( ! ($volume = $volumeGetter->getByName($volumeName) ) ) {
						throw new ProcessingException("Error: No Backup Volume exists with name: ".$volumeName);
					}

					$volume->setParam($volumeParam, $volumeValue);

					echo("Action: Backup Volume: ".$volumeName." parameter '".$volumeParam."' set to: ".$volumeValue."\n\n");
					
				break;

				// Handle deleting
				case 'delete':

					if( !isSet($args[3]) ) {
						throw new InputException("Error: Name of Backup Volume to delete must be given.");
					}

					$volumeName = $args[3];

					$volumeGetter = new volumeGetter();
					$volumeGetter->setLogStream($this->log);
					if( ! ($volume = $volumeGetter->getByName($volumeName) ) ) {
						throw new ProcessingException("Error: No Backup Volume exists with name: ".$volumeName);
					}

					$volume->delete();

					echo("Action: Backup Volume: ".$volumeName." deleted.\n\n");

				break;

				// Handle unknown action
				default:

					echo("Error: Unrecognized action for ".$args[1]." context: ".$args[2]."\n\n");
					$this->printVolumeHelpText($args);

				break;
			}


			return;

		}

	}

?>