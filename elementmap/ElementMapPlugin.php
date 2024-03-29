<?php

namespace Craft;

class ElementMapPlugin extends BasePlugin {

	public function init() {
		parent::init();
		
		// Include the panel injector and styles on any control panel page.
		if (craft()->request->isCpRequest() && craft()->userSession->isLoggedIn()) {
			craft()->templates->includeCssResource('elementmap/css/elementmap.css');
			craft()->templates->includeJsResource('elementmap/js/elementmap.js');
		}
	}

	public function getVersion() {
		return '1.0.1';
	}
	
	public function getSchemaVersion()
	{
		return '1.0.0';
	}
	
	public function getName() {
		return Craft::t('Element Map');
	}

	public function getDescription() {
		return Craft::t('Adds an additional panel to certain element editors to show a detailed list of related elements.');
	}

	public function getDeveloper() {
		return 'Charlie Development';
	}

	public function getDeveloperUrl() {
		return 'http://charliedev.com/';
	}
	
	public function getDocumentationUrl()
	{
		return 'https://github.com/charliedevelopment/Craft2-Element-Map/blob/master/README.md';
	}

	public function getReleaseFeedUrl()
	{
		return 'https://raw.githubusercontent.com/charliedevelopment/Craft2-Element-Map/master/release.json';
	}	
}
