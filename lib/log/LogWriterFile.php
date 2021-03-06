<?php

/**
 * Writer outputting logged messages to a file
 *
 * @author Michał Rudnicki <michal.rudnicki@epsi.pl>
 */
final class LogWriterFile implements LogWriter {

	/**
	 * Flag indicating complete initialization and readiness to log to a file
	 * @var boolean
	 */
	private $enabled = FALSE;

	/**
	 * File name to log to
	 * @var string
	 */
	private $file;

	/**
	 * Log level threshold
	 * @var int
	 */
	private $level = Debug::DEBUG;

	/**
	 * Log line format
	 * @var string
	 */
	private $format = "{severity}\t@{time}\t+{duration}\t{message}";

	/**
	 * Constructor
	 *
	 * Configures output file, log format, and threshold based on config options.
	 * Enables writing to a file upon successful initialization.
	 *
	 * @param int $startTime
	 * @param ConfigNode $config
	 * @author Michał Rudnicki <michal.rudnicki@epsi.pl>
	 */
	public function __construct($startTime, ConfigNode $config) {
		$this->startTime = $startTime;
		if (!$config->enabled) {
			return;
		}
		if (!$config->file) {
			throw new Exception("Missing mandatory config param 'file'");
		}
		$this->file = ROOT_DIR . "/" . $config->file;
		if (!file_exists($this->file) and !is_writable(dirname($this->file))) {
			throw new Exception("Cannot create file '{$this->$file}' - directory not wriable");
		} elseif (!is_writable($this->file)) {
			throw new Exception("File '{$this->file}' not writable");
		}
		$config->level and $this->level = $config->level;
		$config->format and $this->format = $config->format;
		$this->enabled = TRUE;
	}

	public function log($time, $message, $severity) {
		if (!$this->enabled or $severity < $this->level) {
			return;
		}
		$replacements = array(
			"{time}" => $time,
			"{duration}" => $time - $this->startTime,
			"{message}" => trim($message),
			"{severity}" => $severity
		);
		$message = trim(str_replace(array_keys($replacements), array_values($replacements), $this->format));
		file_put_contents($this->file, "{$message}\n", FILE_APPEND);
	}

	public function done() { }

}