<?php
// Copyright (c) 2013-2016 Datenstrom, http://datenstrom.se
// This file may be used and distributed under the terms of the public license.

// Contact plugin
class YellowContact
{
	const VERSION = "0.6.6";
	var $yellow;			//access to API
	
	// Handle initialisation
	function onLoad($yellow)
	{
		$this->yellow = $yellow;
		$this->yellow->config->setDefault("contactLocation", "/contact/");
		$this->yellow->config->setDefault("contactSpamFilter", "href=|url=");
	}
	
	// Handle page content parsing of custom block
	function onParseContentBlock($page, $name, $text, $shortcut)
	{
		$output = null;
		if($name=="contact" && $shortcut)
		{
			list($location) = $this->yellow->toolbox->getTextArgs($text);
			if(empty($location)) $location = $this->yellow->config->get("contactLocation");
			$output = "<div class=\"".htmlspecialchars($name)."\">\n";
			$output .= "<form class=\"contact-form\" action=\"".$this->yellow->page->base.$location."\" method=\"post\">\n";
			$output .= "<p class=\"contact-name\"><label for=\"name\">".$this->yellow->text->getHtml("contactName")."</label><br /><input type=\"text\" class=\"form-control\" name=\"name\" id=\"name\" value=\"\" /></p>\n";
			$output .= "<p class=\"contact-from\"><label for=\"from\">".$this->yellow->text->getHtml("contactEmail")."</label><br /><input type=\"text\" class=\"form-control\" name=\"from\" id=\"from\" value=\"\" /></p>\n";
			$output .= "<p class=\"contact-message\"><label for=\"message\">".$this->yellow->text->getHtml("contactMessage")."</label><br /><textarea class=\"form-control\" name=\"message\" id=\"message\" rows=\"7\" cols=\"70\"></textarea></p>\n";
			$output .= "<input type=\"hidden\" name=\"referer\" value=\"".$page->getUrl()."\" />\n";
			$output .= "<input type=\"hidden\" name=\"status\" value=\"send\" />\n";
			$output .= "<input type=\"submit\" value=\"".$this->yellow->text->getHtml("contactButton")."\" class=\"btn contact-btn\" />\n";
			$output .= "</form>\n";
			$output .= "</div>\n";
		}
		return $output;
	}
	
	// Handle page parsing
	function onParsePage()
	{
		if($this->yellow->page->get("template")=="contact")
		{
			if(PHP_SAPI=="cli") $this->yellow->page->error(500, "Static website not supported!");
			if(empty($_REQUEST["referer"]))
			{
				$_REQUEST["referer"] = $_SERVER["HTTP_REFERER"];
				$this->yellow->page->setHeader("Last-Modified", $this->yellow->toolbox->getHttpDateFormatted(time()));
				$this->yellow->page->setHeader("Cache-Control", "no-cache, must-revalidate");
			}
			if($_REQUEST["status"]=="send")
			{
				$status = $this->sendMail();
				if($status=="error") $this->yellow->page->error(500, $this->yellow->text->get("contactStatusError"));
				$this->yellow->page->setHeader("Last-Modified", $this->yellow->toolbox->getHttpDateFormatted(time()));
				$this->yellow->page->setHeader("Cache-Control", "no-cache, must-revalidate");
				$this->yellow->page->set("status", $status);
			} else {
				$this->yellow->page->set("status", "none");
			}
		}
	}
	
	// Send contact email
	function sendMail()
	{
		$status = "send";
		$name = trim(preg_replace("/[^\pL\d\-\. ]/u", "-", $_REQUEST["name"]));
		$from = trim($_REQUEST["from"]);
		$message = trim($_REQUEST["message"]);
		$referer = trim($_REQUEST["referer"]);
		$spamFilter = $this->yellow->config->get("contactSpamFilter");
		if(empty($name) || empty($from) || empty($message)) $status = "incomplete";
		if(!empty($from) && !filter_var($from, FILTER_VALIDATE_EMAIL)) $status = "invalid";
		if(!empty($message) && preg_match("/$spamFilter/i", $message)) $status = "error";
		if($status=="send")
		{
			$author = $this->yellow->config->get("author");
			$email = $this->yellow->config->get("email");
			if($this->yellow->page->isExisting("author") && !$this->yellow->page->parserSafeMode) $author = $this->yellow->page->get("author");
			if($this->yellow->page->isExisting("email") && !$this->yellow->page->parserSafeMode) $email = $this->yellow->page->get("email");
			$mailTo = mb_encode_mimeheader("$author <$email>");
			$mailSubject = mb_encode_mimeheader($this->yellow->page->get("title"));
			$mailHeaders = mb_encode_mimeheader("From: $name <$from>")."\r\n";
			$mailHeaders .= mb_encode_mimeheader("X-Referer-Url: ".$referer)."\r\n";
			$mailHeaders .= mb_encode_mimeheader("X-Request-Url: ".$this->yellow->page->getUrl())."\r\n";
			$mailHeaders .= mb_encode_mimeheader("X-Remote-Addr: $_SERVER[REMOTE_ADDR]")."\r\n";
			$mailHeaders .= "Mime-Version: 1.0\r\n";
			$mailHeaders .= "Content-Type: text/plain; charset=utf-8\r\n";
			$mailMessage = "$message\r\n-- \r\n$name";
			$status = mail($mailTo, $mailSubject, $mailMessage, $mailHeaders) ? "done" : "error";
		}
		return $status;
	}
}

$yellow->plugins->register("contact", "YellowContact", YellowContact::VERSION);
?>