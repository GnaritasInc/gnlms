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

	function doCSVResponse ($filename, $records, $encoding="windows-1252") {

		// header("Content-type: text/csv");
		// header("Content-Disposition: attachment; filename=\"$filename.csv\"");

		$this->writeCSVHeaders($filename);

		$out = fopen("php://output", 'w');

		$this->writeCSVRecords ($out, $records, $encoding);

		fclose($out);
		exit();
	}


	function writeCSVHeaders ($filename) {

		header("Content-type: text/csv");
		header("Content-Disposition: attachment; filename=\"$filename.csv\"");


	}
}

endif; ?>