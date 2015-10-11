<?php
	use React\EventLoop\Factory;
	use MKraemer\ReactInotify\Inotify;
	use Christiaan\StreamProcess\StreamProcess;
	require 'vendor/autoload.php';
	
	 // Number of seconds to delay reloading for, allows mass file operations
	 // to continue without triggering a large number of reloads.
	const DELAY = 0.5;
	
	 // The PHP interpreter to execute scripts with, defaults to the current
	 // interpreter, could be used to start another, such as HHVM.
	const INTERPRETER = PHP_BINARY;

	// The ReactPHP script you want to invoke.
	const SCRIPT = 'server.php';
	$loop = Factory::create();
	$message = function($data) {
		echo date('[H:i:s] '), $data;
	};
	
	$forward = function($stream) {
		$data = fgets($stream);
		$delimiter = "\n        > ";
		if ($data == null) return;
		echo date('[H:i:s] > '), implode($delimiter, explode("\n", rtrim($data))), "\n";
	};

	$stop = function() use ($loop, $message, &$process) {
		if ($process instanceof StreamProcess) {
			$message("Stopping process... ");
			$loop->removeReadStream($process->getReadStream());
			$loop->removeReadStream($process->getErrorStream());
			//$process->terminate();
			$process->close();
			echo "done.\n";
		}
	};

	$start = function() use ($loop, $message, $forward, &$process, &$time) {
		$message("Starting process... ");
		$command = INTERPRETER . ' ' . escapeshellarg(realpath(SCRIPT));
		$process = new StreamProcess($command);
		echo "done.\n";
		$loop->addReadStream($process->getReadStream(), $forward);
		$loop->addReadStream($process->getErrorStream(), $forward);
		if (isset($time) === false) {
			$time = microtime(true);
		}
	};

	$restart = function() use ($loop, $stop, $start, &$time) {
		$now = microtime(true);
		if ($now <= $time) {
		    return;
		}
		$stop();
		$start();
		$time = microtime(true) + DELAY;
	};

	$exit = function() use ($loop, $stop) {
		$stop();
		$loop->stop();
		exit;
	};

	// Begin monitoring:
	$inotify = new Inotify($loop);
	$inotify->add(__DIR__, IN_CLOSE_WRITE | IN_CREATE | IN_DELETE);
	$inotify->on(IN_CLOSE_WRITE, $restart);
	$inotify->on(IN_CREATE, $restart);
	$inotify->on(IN_DELETE, $restart);

	// Wait for exit signal:
	$pcntl = new MKraemer\ReactPCNTL\PCNTL($loop);
	$pcntl->on(SIGTERM, $exit);
	$pcntl->on(SIGINT, $exit);

	$start();
	$loop->run();