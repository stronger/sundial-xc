<?php

class cNews {
	var $news_id;
	var $title;
	var $description;
	var $expire_date;
	var $sequence;

	function cNews ($title=null, $description=null, $expire_date=null, $sequence=null) {
		if($title) {
			$this->title = $title;
			$this->description = $description;
			$this->expire_date = new cDateTime($expire_date);
			$this->sequence = $sequence;
		}
	}
	
	function SaveNewNews () {
		global $cDB;
		
		$insert = $cDB->Query("INSERT INTO ". DB::NEWS ." (title, description, expire_date, sequence) VALUES (".$cDB->EscTxt($this->title) .", ". $cDB->EscTxt($this->description) .", '". $this->expire_date->MySQLDate() ."', ". $this->sequence .");");

		if(mysql_affected_rows() == 1) {
			$this->news_id = mysql_insert_id();		
			return true;
		} else {
			PageView::getInstance()->displayError("Could not save news item.");
			return false;
		}		
	}
	
	function SaveNews () {
		global $cDB;			
		
		$update = $cDB->Query("UPDATE ".DB::NEWS." SET title=". $cDB->EscTxt($this->title) .", description=". $cDB->EscTxt($this->description) .", expire_date='". $this->expire_date->MySQLDate(). "', sequence=". $this->sequence ." WHERE news_id=". $cDB->EscTxt($this->news_id) .";");

		return $update;	
	}
	
	function LoadNews ($news_id) {
		global $cDB;
		
//		$this->ExpireNews();
				
		$query = $cDB->Query("SELECT title, description, expire_date, sequence FROM ".DB::NEWS." WHERE  news_id=". $cDB->EscTxt($news_id) .";");
		
		if($row = mysql_fetch_array($query)) {		
			$this->news_id = $news_id;
			$this->title = $cDB->UnEscTxt($row[0]);
			$this->description = $cDB->UnEscTxt($row[1]);		
			$this->expire_date = new cDateTime($row[2]);
			$this->sequence = $row[3];
			return true;
		} else {
			PageView::getInstance()->displayError("There was an error accessing the news table.  Please try again later.");
			return FALSE;
		}
		
	}

	function DisplayNews () {
		$output = "<STRONG>". $this->title ."</STRONG><P>";
		$output .= $this->description ."<P>";
		return $output;
	}
}

class cNewsGroup {

	public $newslist;
	public $max_seq;

	public function LoadNewsGroup () {
		$this->DeleteOldNews();
		$tableName = DB::NEWS;
		$sql = "SELECT news_id FROM $tableName ORDER BY sequence DESC";
		$rows = PDOHelper::fetchAll($sql, array());
		if (empty($rows)) {
			return FALSE;
		}
		foreach ($rows as $i => $row) {
			$this->newslist[$i] = new cNews;
			$this->newslist[$i]->LoadNews($row["news_id"]);
		}
		$this->max_seq = $this->newslist[0]->sequence;
		return TRUE;
	}

	function DisplayNewsGroup () {
		$output = "";
		if(!isset($this->newslist))
			return $output;
		
		foreach($this->newslist as $news) {
			if($news->expire_date->Timestamp() > strtotime("yesterday"))
				$output .= $news->DisplayNews() . "<BR>";
		}
		return $output;
	}
	
	function MakeNewsArray() {
		if (!isset($this->newslist))
			return false;
			
		foreach($this->newslist as $news) {
			$list[$news->news_id] = $news->title;
		}
		return $list;
	}

	function MakeNewsSeqArray($current_seq=null) { // TODO: OK, this is just ugly...
		$prior_seq = 0;									// Should use 1,2,3,4... and reorder
		$prior_title = "At top of list";				// all each time.
		$lead_txt = "";
		$follow_txt = "";
		
		if (!isset($this->newslist))
			return array("100"=>$prior_title);
		
		foreach($this->newslist as $news) {
			if ($current_seq == $news->sequence) {
				$list[$this->CutZero($current_seq)] = $lead_txt. $prior_title . $follow_txt;
			} elseif ($prior_seq != $current_seq or $current_seq == null) {
				if ($prior_seq == 0)
					$seq = $this->GetNewSeqNum();
				else
					$seq = $this->GetSeqNumAfter($prior_seq);
					
				$list[$seq] = $lead_txt. $prior_title .$follow_txt;
			}
			
			$prior_seq = $news->sequence;
			$saved_title = $prior_title;
			$prior_title = $news->title;
			$lead_txt = "After '";
			$follow_txt = "'";
		}
		
		if ($current_seq != $news->sequence) {
			if ($prior_seq == 0)
				$seq = $this->GetNewSeqNum();
			else
				$seq = $this->GetSeqNumAfter($prior_seq);
		
			$list[$seq] = $lead_txt . $prior_title . $follow_txt;
		}
		
		return $list;	
	}	
	
	function CutZero($value) {
   	return preg_replace("/(\.\d+?)0+$/", "$1", $value)*1;
	}

	public function DeleteOldNews() {
		return PDOHelper::delete(DB::NEWS, "expire_date < current_timestamp - INTERVAL 14 DAY", array());
	}

	function GetSeqNumAfter ($high) {
		$low = 0;
		foreach($this->newslist as $news) {
			if ($news->sequence < $high) {
				$low = $news->sequence;
				break;
			} 
		}
		
		return $low + (($high - $low) / 2);
	}
	
	function GetNewSeqNum () {
		return round($this->max_seq + 100, -2);
	}
}

?>
