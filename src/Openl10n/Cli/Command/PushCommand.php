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
        $localesToPush = $input->getOption('locale');
        $localesToPush = array_unique($localesToPush);
        $pushAllLocale = false;

        // Process locales special cases
        if (in_array('all', $localesToPush)) {
            $localesToPush = $projectLocales;
            $pushAllLocale = true;
        } elseif (false !== $key = array_search('default', $localesToPush)) {
            unset($localesToPush[$key]);
            $localesToPush[] = $defaultLocale;
        }

        // Deduplicate values
        $localesToPush = array_unique($localesToPush);

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
            $options = $fileSet->getOptions('push');

            foreach ($files as $file) {
                $resourceIdentifier = $file->getPathname(['locale' => $defaultLocale]);
                $locale = $file->getAttribute('locale');

                // Ignore non specified locales
                if (!in_array($locale, $localesToPush) && !$pushAllLocale) {
                    continue;
                }

                // Skip unwanted files
                if (!empty($fileFilter) && !in_array($file->getRelativePathname(), $fileFilter)) {
                    continue;
                }

                // Create locale if non existing
                if (!in_array($locale, $projectLocales)) {
                    try {
                        $output->writeln(sprintf('<info>Adding</info> locale <comment>%s</comment>', $locale));
                        $projectApi->addLanguage($project->getSlug(), $locale);
                        $projectLocales[] = $locale;
                    } catch (BadResponseException $e) {
                        $output->writeln(sprintf('<error>Unknown</error> locale <comment>%s</comment>', $locale));
                        continue;
                    }
                }

                // Retrieve or create resource entity
                if (isset($resources[$resourceIdentifier])) {
                    $resource = $resources[$resourceIdentifier];
                } else {
                    $output->writeln(sprintf('<info>Creating</info> resource <comment>%s</comment>', $resourceIdentifier));

                    $resource = new Resource($project->getSlug());
                    $resource->setPathname($resourceIdentifier);
                    $resourceApi->create($resource);

                    $resources[$resourceIdentifier] = $resource;
                }

                $output->writeln(sprintf('<info>Uploading</info> file <comment>%s</comment>', $file->getRelativePathname()));
                $resourceApi->import($resource, $file->getAbsolutePathname(), $locale, $options);
            }
        }
    }
}
