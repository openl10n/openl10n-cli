<?php

namespace Openl10n\Cli\Command;

use Symfony\Component\Console\Input\InputArgument;
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
            ->setDescription('Pull the translations from the server to the local files')
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
                'File list you want to pull from the server'
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $api = $this->get('api');
        $projectApi = $api->getEntryPoint('project');
        $resourceApi = $api->getEntryPoint('resource');

        //
        // Get project
        //
        $projectSlug = $this->get('project_handler')->getProjectSlug();
        $project = $projectApi->get($projectSlug);

        //
        // Get project locales
        //
        $languages = $projectApi->getLanguages($project->getSlug());
        $projectLocales = array_map(function ($language) {
            return $language->getLocale();
        }, $languages);

        $defaultLocale = $project->getDefaultLocale();

        // Retrieve locales option
        $localesToPull = $input->getOption('locale');
        $localesToPull = array_unique($localesToPull);

        // Process locales special cases
        if (in_array('all', $localesToPull)) {
            $localesToPull = $projectLocales;
        } elseif (false !== $key = array_search('default', $localesToPull)) {
            unset($localesToPull[$key]);
            $localesToPull[] = $defaultLocale;
        }

        // Deduplicate values
        $localesToPull = array_unique($localesToPull);

        //
        // Retrieve existing project's resources
        //
        $resources = $resourceApi->findByProject($project);
        // Set resources' pathname as array key
        $resources = array_combine(array_map(function ($resource) {
            return $resource->getPathname();
        }, $resources), $resources);

        //
        // Iterate over resources
        //
        $fileSets = $this->get('file_handler')->getFileSets();
        $fileFilter = $input->getArgument('files');

        foreach ($fileSets as $fileSet) {
            $files = $fileSet->getFiles();
            $options = $fileSet->getOptions('pull');

            $resourcesToPull = [];

            foreach ($files as $file) {
                $resourceIdentifier = $file->getPathname(['locale' => $defaultLocale]);
                $locale = $file->getAttribute('locale');

                // Skip unwanted files
                if (!empty($fileFilter) && !in_array($file->getRelativePathname(), $fileFilter)) {
                    continue;
                }

                if (!isset($resources[$resourceIdentifier])) {
                    $output->writeln(sprintf('Skipping file %s', $file->getRelativePathname()));
                    continue;
                }

                $resource = $resources[$resourceIdentifier];

                // Ignore non specified locales
                if (!in_array($locale, $localesToPull)) {
                    continue;
                }

                $output->writeln(sprintf('<info>Downloading</info> file <comment>%s</comment>', $file->getRelativePathname()));
                $content = $resourceApi->export($resource, $locale, $options);

                file_put_contents($file->getAbsolutePathname(), $content);
            }
        }
    }
}
