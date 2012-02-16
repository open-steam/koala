<?php
namespace Widgets;

interface IContentProvider {

	public function getId($contentItem);
	public function getCellData($cell, $contentItem);
	public function getNoContentText();
	public function getOnClickHandler($contentItem);
}
?>