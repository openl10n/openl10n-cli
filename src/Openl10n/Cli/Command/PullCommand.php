<?php

namespace Openl10n\Cli\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PullCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pull')
            ->addOption(
                'locale',
                null,
                InputOption::VALUE_REQUIRED|InputOption::VALUE_IS_ARRAY,
                'The locale id, "default" for the source, "all" for every locales found',
                ['default']
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
        $projectLocales = array_map(function($language) {
            return $language->getLocale();
        }, $languages);

        $defaultLocale = $project->getDefaultLocale();

        // Retrieve locales option
        $locales = $input->getOption('locale');
        $locales = array_unique($locales);

        // Process locales special cases
        if (in_array('all', $locales)) {
            $locales = $projectLocales;
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

        //
        // Iterate over resources
        //
        $resourcesHandler = $this->get('openl10n.resources_handler');
        $resourceDefinitions = $resourcesHandler->getResourceDefinitions();
        $resourceDefinitions = array_combine(array_map(function($resourceDef) use ($defaultLocale) {
            return $resourceDef->getPathnameForLocale($defaultLocale);
        }, $resourceDefinitions), $resourceDefinitions);

        $rootDir = $this->getApplication()->getWorkingDirectory();

        foreach ($resources as $resource) {
            $resourcePathname = $resource->getPathname();

            if (isset($resourceDefinitions[$resourcePathname])) {
                $definition = $resourceDefinitions[$resourcePathname];
            } else {
                $output->writeln(sprintf('Skipping resource %s', $resourcePathname));
                continue;
            }

            //
            // Download files
            //
            foreach ($locales as $locale) {
                $pathname = $definition->getPathnameForLocale($locale);

                $output->writeln(sprintf('<info>Downloading</info> file <comment>%s</comment>', $pathname));
                $content = $resourceApi->export($resource, $locale);

                file_put_contents($rootDir.'/'.$pathname, $content);
            }
        }
    }
}
