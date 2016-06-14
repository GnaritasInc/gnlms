<?php if(!class_exists("gn_CSVWriter")):

class gn_CSVWriter {


	function writeCSVRecords ($out, $records, $encoding="", $writeHeader=true) {

		if($writeHeader && $records) fputcsv ($out, array_keys($records[0]));

		foreach($records as $record) {
			if($encoding) {
				foreach($record as $key=>$value) {
					$record[$key] = iconv("UTF-8", "$encoding//TRANSLIT", $value);
				}
			}
			fputcsv ($out, $record);
		}
	}

	function doCSVResponse ($filename, $records, $includeHeaders=true, $encoding="windows-1252") {

		$this->writeCSVHeaders($filename);

		$out = fopen("php://output", 'w');

		$this->writeCSVRecords ($out, $records, $encoding, $includeHeaders);

		fclose($out);
		exit();
	}


	function writeCSVHeaders ($filename="") {

		header("Content-type: text/csv");
		if($filename) header("Content-Disposition: attachment; filename=\"$filename.csv\"");


	}
}

endif; ?>