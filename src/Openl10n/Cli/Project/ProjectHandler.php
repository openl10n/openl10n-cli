<?php

namespace Openl10n\Cli\Project;

class ProjectHandler
{
    protected $projectSlug;

    /**
     * @param string $projectSlug
     */
    public function __construct($projectSlug)
    {
        $this->projectSlug = $projectSlug;
    }

    /**
     * @return string
     */
    public function getProjectSlug()
    {
        return $this->projectSlug;
    }
}
