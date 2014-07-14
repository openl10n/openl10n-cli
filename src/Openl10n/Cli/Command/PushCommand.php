<?php

namespace Openl10n\Cli\Command;

use GuzzleHttp\Exception\BadResponseException;
use Openl10n\Sdk\Model\Resource;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PushCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('push')
            ->addOption(
                'locale',
                null,
                InputOption::VALUE_REQUIRED|InputOption::VALUE_IS_ARRAY,
                'The locale id, "default" for the source, "all" for every locales found',
                ['default']
            )
            ->addArgument(
                'files',
                InputArgument::IS_ARRAY,
                'File list you want to push to the server'
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $projectHandler = $this->get('openl10n.project_handler');

        //
        // Get project
        //
        try {
            $project = $projectHandler->getProject();
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));

            return 1;
        }

        //
        // Get project locales
        //
        $languages = $projectHandler->getProjectLanguages();
        $projectLocales = array_map(function ($language) {
            return $language->getLocale();
        }, $languages);

        $defaultLocale = $project->getDefaultLocale();

        // Retrieve locales option
        $locales = $input->getOption('locale');
        $locales = array_unique($locales);
        $createLocaleIfNeeded = false;

        // Process locales special cases
        if (in_array('all', $locales)) {
            $locales = $projectLocales;
            $createLocaleIfNeeded = true;
        } elseif (false !== $key = array_search('default', $locales)) {
            unset($locales[$key]);
            $locales[] = $defaultLocale;
        }

        // Deduplicate values
        $locales = array_unique($locales);

        //
        // Retrieve existing project's resources
        //
        $resourceApi = $this->get('openl10n.api')->getEntryPoint('resource');
        $resources = $resourceApi->findByProject($project);
        $resources = array_combine(array_map(function ($resource) {
            return $resource->getPathname();
        }, $resources), $resources);

        //
        // Iterate over resources
        //
        $resourcesHandler = $this->get('openl10n.resources_handler');
        $resourceDefinitions = $resourcesHandler->getResourceDefinitions();

        $rootDir = $this->getApplication()->getWorkingDirectory();

        foreach ($resourceDefinitions as $definition) {
            $sourcePathname = $definition->getPathnameForLocale($defaultLocale);

            // Retrieve or create resource entity
            if (isset($resources[$sourcePathname])) {
                $resource = $resources[$sourcePathname];
            } else {
                $output->writeln(sprintf('<info>Creating</info> resource <comment>%s</comment>', $sourcePathname));

                $resource = new Resource($project->getSlug());
                $resource->setPathname($sourcePathname);
                $resourceApi->create($resource);
            }

            //
            // Upload files
            //
            $fileFilter = $input->getArgument('files');
            foreach ($definition->getFiles() as $locale => $pathname) {
                if (!in_array($locale, $locales)) {
                    if (!$createLocaleIfNeeded) {
                        continue;
                    }

                    try {
                        $output->writeln(sprintf('<info>Adding</info> locale <comment>%s</comment>', $locale));
                        $projectHandler->addLocale($locale);
                        $locales[] = $locale;
                    } catch (BadResponseException $e) {
                        $output->writeln(sprintf('<error>Unknown</error> locale <comment>%s</comment>', $locale));
                        continue;
                    }
                }

                // Skip unwanted files
                if (!empty($fileFilter) && !in_array($pathname, $fileFilter)) {
                    continue;
                }

                $output->writeln(sprintf('<info>Uploading</info> file <comment>%s</comment>', $pathname));
                $resourceApi->import($resource, $rootDir.'/'.$pathname, $locale);
            }
        }
    }
}
