<?php

namespace Openl10n\Cli\Resource;

use Openl10n\Cli\File\Matcher;

class ResourcesHandler
{
	protected $filesConfig;

	private $resourceDefinitions;

	public function __construct(array $filesConfig)
	{
		$this->filesConfig = $filesConfig;
	}

	public function getResourceDefinitions()
	{
		if (null === $this->resourceDefinitions) {
			$this->processResourceDefinitions();
		}

		return $this->resourceDefinitions;
	}

	private function processResourceDefinitions()
	{
		$this->resourceDefinitions = array();

		foreach ($this->filesConfig as $configFile) {
			$matcher = new Matcher();

			$options = array();

			// Get every files that match given pattern
            $files = $matcher->match($configFile['pattern'], getcwd());

            // Regroup each files per resource pattern (ie. locale indenpendant)
            $resources = [];
            foreach ($files as $file) {
                $pathname = $file->getPathname(['locale']);

                if (!array_key_exists($pathname, $resources)) {
                    $resources[$pathname] = [];
                }

                $resources[$pathname][$file->getAttribute('locale')] = $file;
            }

            // Build definitions
            $definitions = [];
            foreach ($resources as $pattern => $resource) {
            	$files = [];
            	foreach ($resource as $locale => $fileInfo) {
            		$files[$locale] = $fileInfo->getPathname();
            	}

            	$this->resourceDefinitions[] = new ResourceDefinition($pattern, $files, $options);
            }

            continue;

            $resourceApi = $api->getEntryPoint('resource');
            $resourcesEntities = $resourceApi->findByProject($project);
            $indexedResourcees = [];
            foreach ($resourcesEntities as $entity) {
                $indexedResourcees[$entity->getPathname()] = $entity;
            }

            foreach ($resources as $pathname => $resource) {
                $defaultLocale = $project->getDefaultLocale();
                $source = $resource[$defaultLocale];
                $translations = $resource;
                unset($translations[$defaultLocale]);

                $filepath = $source->getPathname();

                // check if resource exist
                if (!array_key_exists($filepath, $indexedResourcees)) {
                    $resource = new Resource($project->getSlug());
                    $resource->setPathname($filepath);
                    $resourceApi->create($resource);
                    //echo 'Created ' . $filepath.PHP_EOL;
                } else {
                    $resource = $indexedResourcees[$filepath];
                }

                // import source
                $resourceApi->import($resource, $filepath, $defaultLocale);

                // import translation if option --all
                foreach ($translations as $locale => $translation) {
                    // TODO Create locale if no exist
                    $resourceApi->import($resource, $translation->getPathname(), $locale);
                }
            }
		}
	}
}
