<?php

namespace Openl10n\Cli\Project;


class ProjectHandler
{
    protected $projectId;

    /**
     * @param string $projectSlug
     */
    public function __construct($projectSlug)
    {
        $this->projectSlug = $projectSlug;
    }

    public function getProjectSlug()
    {
        return $this->projectSlug;
    }
}
