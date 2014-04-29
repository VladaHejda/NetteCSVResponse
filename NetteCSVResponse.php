<?php

namespace Nette\Application\Responses;

use Nette;

/**
 * CSV download response.
 * Under New BSD license.
 *
 * @property-read string $name
 * @property-read string $contentType
 * @package Nette\Application\Responses
 */
class CsvResponse extends Nette\Object implements Nette\Application\IResponse
{
	/** @var array */
	private $data;

	/** @var string */
	private $name;

	/** @var bool */
	public $addHeading;

	/** @var string */
	public $glue;

	/** @var string */
	private $charset;

	/** @var string */
	private $contentType;


	/**
	 * @param array[]|\Traversable $data
	 * @param string $name
	 * @param bool $addHeading
	 * @param string $glue
	 * @param string $charset
	 * @param string $contentType
	 * @throws \InvalidArgumentException
	 */
	public function __construct($data, $name = NULL, $addHeading = TRUE, $glue = ';', $charset = 'utf-8', $contentType = NULL)
	{
		if (is_array($data)) {
			if (count($data) && !is_array(reset($data))) {
				$invalid = TRUE;
			}
		} elseif (!$data instanceof \Traversable) {
			$invalid = TRUE;
		}
		if (isset($invalid)) {
			throw new \InvalidArgumentException(__CLASS__.": data must be array of arrays or instance of Traversable.");
		}
		if (empty($glue) || preg_match('/^[\n\r]+$/s', $glue) || $glue === '"') {
			throw new \InvalidArgumentException(__CLASS__.": glue cannot be an empty or reserved character.");
		}

		$this->data = $data;
		$this->name = $name;
		$this->addHeading = $addHeading;
		$this->glue = $glue;
		$this->charset = $charset;
		$this->contentType = $contentType ? $contentType : 'text/csv';
	}


	/**
	 * Returns the file name.
	 * @return string
	 */
	final public function getName()
	{
		return $this->name;
	}


	/**
	 * Returns the MIME content type of a downloaded content.
	 * @return string
	 */
	final public function getContentType()
	{
		return $this->contentType;
	}


	/**
	 * Sends response to output.
	 * @param Nette\Http\IRequest $httpRequest
	 * @param Nette\Http\IResponse $httpResponse
	 */
	public function send(Nette\Http\IRequest $httpRequest, Nette\Http\IResponse $httpResponse)
	{
		$httpResponse->setContentType($this->contentType, $this->charset);

		if (empty($this->name)) {
			$httpResponse->setHeader('Content-Disposition', 'attachment');
		} else {
			$httpResponse->setHeader('Content-Disposition', 'attachment; filename="' . $this->name . '"');
		}

		$data = $this->formatCsv();

		$httpResponse->setHeader('Content-Length', strlen($data));
		print $data;
	}


	protected function formatCsv()
	{
		if (empty($this->data)) {
			return '';
		}

		$csv = array();

		if (!is_array($this->data)) {
			$this->data = iterator_to_array($this->data);
		}
		$firstRow = reset($this->data);

		if ($this->addHeading) {
			if (!is_array($firstRow)) {
				$firstRow = iterator_to_array($firstRow);
			}

			$labels = array();
			foreach (array_keys($firstRow) as $key) {
				$labels[] = ucfirst(str_replace(array("_", '"'), array(' ', '""'), $key));
			}
			$csv[] = '"'.join('"'.$this->glue.'"', $labels).'"';
		}

		foreach ($this->data as $row) {
			if (!is_array($row)) {
				$row = iterator_to_array($row);
			}
			foreach ($row as &$value) {
				$value = str_replace(array('"'), array('""'), $value);
			}
			$csv[] = '"'.join('"'.$this->glue.'"', $row).'"';
		}

		return join("\r\n", $csv);
	}
}
