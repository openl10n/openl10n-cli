<?php

namespace Openl10n\Cli\Project;

use Openl10n\Sdk\Api;

class ProjectHandler
{
	protected $api;

	protected $projectId;

	/**
	 * @param Api    $api
	 * @param string $projectId
	 */
	public function __construct(Api $api, $projectId)
	{
		$this->api = $api;
		$this->projectId = $projectId;
	}

	public function getProject()
	{
		$projectApi = $this->api->getEntryPoint('project');
		$project = $projectApi->get($this->projectId);

		return $project;
	}

	public function getProjectLanguages()
	{
		$projectApi = $this->api->getEntryPoint('project');
		$languages = $projectApi->getLanguages($this->projectId);

		return $languages;
	}

	public function addLocale($locale)
	{
		$projectApi = $this->api->getEntryPoint('project');
		$projectApi->addLanguage($this->projectId, $locale);
	}
}
