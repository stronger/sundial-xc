<?php

class cListing {

	const DESC_OFFERED = "Offer";
	const DESC_WANTED = "Want";

	const CODE_OFFERED = "O";
	const CODE_WANTED = "W";

	var $member; // this will be an object of class cMember
	var $title;
	var $description;
	var $category; // this will be an object of class cCategory
	var $rate;
	var $status;
	var $posting_date; // the date a listing was created or last modified
	var $expire_date;
	var $reactivate_date;
	var $type;

	public function __construct($member = NULL, $values = NULL) {
		if ($member) {
			$this->member = $member;
			$this->title = $values['title'];
			$this->description = $values['description'];
			$this->rate = $values['rate'];
			$this->expire_date = $values['expire_date'];
			$this->type = $values['type'];
			$this->reactivate_date = null;
			$this->status = 'A';
			$this->category = new cCategory();
			$this->category->LoadCategory($values['category']);
		}
	}

	public function TypeCode() {
		return ($this->type == self::DESC_OFFERED or $this->type == self::CODE_OFFERED)
			? self::CODE_OFFERED
			: self::CODE_WANTED;
	}

	public function TypeDesc($code) {
		return ($code == self::CODE_OFFERED or $code == self::DESC_OFFERED)
			? self::DESC_OFFERED
			: self::DESC_WANTED;
	}

	public function SaveNewListing() {
		PDOHelper::insert(DB::LISTINGS, array(
			"title" => $this->title,
			"description" => $this->description,
			"category_code" => $this->category->id,
			"member_id" => $this->member->member_id,
			"rate" => $this->rate,
			"status" => $this->status,
			"expire_date" => $this->expire_date,
			"reactivate_date" => $this->reactivate_date,
			"type" => $this->TypeCode(),
		));
		$listing = new cListing();
		$listing->LoadListing($this->title, $this->member->member_id, $this->TypeCode());
		return $listing->title === $this->title;
	}

	public function SaveListing($updatePostingDate = TRUE) {
		$updatePostingDate and $this->posting_date = date('Y-m-d H:i:s');
		$row = array(
			"description" => $this->description,
			"member_id" => $this->member->member_id,
			"rate" => $this->rate,
			"status" => $this->status,
			"expire_date" => $this->expire_date,
			"reactivate_date" => $this->reactivate_date,
			"type" => $this->TypeCode(),
		);
		$where = array(
			"title" => $this->title,
			"categoryId" => $this->category->id,
			"memberId" => $this->member->getId(),
		);
		return PDOHelper::update(DB::LISTINGS, $row, "title = :title AND category_code = :categoryId AND member_id = :memberId", $where);
	}

	public function DeleteListing($title, $memberId, $typeCode) {
		return PDOHelper::delete(DB::LISTINGS, "title = :title AND member_id = :id AND type = :type", array("title" => $title, "id" => $memberId, "type" => $typeCode));
	}

	public function LoadListing($title, $memberId, $type) {
		$typeCode = strtoupper(substr($type, 0, 1));
		$tableName = DB::LISTINGS;
		$sql = "SELECT * FROM $tableName WHERE title = :title AND member_id = :id AND type = :type";
		$row = PDOHelper::fetchRow($sql, array("title" => $title, "id" => $memberId, "type" => $typeCode));
		if (empty($row)) {
			return;
		}

		$this->title = $row['title'];
		$this->description = $row['description'];
		$this->member_id = $row['member_id'];
		$this->rate = $row['rate'];
		$this->status = $row['status'];
		$this->posting_date = $row['posting_date'];
		$this->expire_date = $row['expire_date'];
		$this->reactivate_date = $row['reactivate_date'];
		$this->type = $this->TypeDesc($typeCode);
		$this->category = new cCategory();
		$this->category->LoadCategory($row['category_code']);

		// load member associated with member_id
		$this->member = new cMember;
		$this->member->LoadMember($memberId);
		$this->DeactivateReactivate();
	}

	public function DeactivateReactivate() {
		if ($this->reactivate_date) {
			$date = new cDateTime($this->reactivate_date);
			$unix = $date->Timestamp();
			if ($this->status == INACTIVE and $unix and $unix <= time()) {
				$this->status = ACTIVE;
				$this->reactivate_date = null;
				$this->SaveListing();
			}
		}
		if ($this->expire_date) {
			$date = new cDateTime($this->expire_date);
			$unix = $date->Timestamp();
			if ($this->status <> EXPIRED and $unix and $unix <= time()) {
				$this->status = EXPIRED;
				$this->SaveListing();
			}
		}
	}

	function ShowListing()
	{
		$output = $this->type . "ed Data:<BR>";
		$output .= $this->title . ", " . $this->description . ", " . $this->category->id . ", " . $this->member->member_id . ", " . $this->rate . ", " . $this->status . ", " . $this->posting_date . ", " . $this->expire_date . ", " . $this->reactivate_date . "<BR><BR>";
		$output .= $this->member->ShowMember();
		return $output;
	}

	function DisplayListing()
	{
		$output = "";
		if($this->description != "")
			$output .= "<STRONG>Descripción:</STRONG> ". $this->description ."<BR>";
		if($this->rate != "")
			$output .= "<STRONG>Rate:</STRONG> ". $this->rate ."<BR>";
		$output .= $this->member->DisplayMember();
		return $output;
	}

}