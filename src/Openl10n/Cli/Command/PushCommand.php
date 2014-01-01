<?php

namespace Openl10n\Cli\Command;

use Guzzle\Http\Exception\ClientErrorResponseException;
use Openl10n\Api\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class PushCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('push')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filepath = getcwd().'/openl10n.yml';

        $data = Yaml::parse($filepath);

        $client = new Client($data['server']);

        // Get project
        try {
            $command = $client->getCommand('GetProject', array(
                'slug' => $data['project']['name'],
            ));
            $client->execute($command);
        } catch (ClientErrorResponseException $e) {
            if (404 !== $e->getResponse()->getStatusCode()) {
                throw $e;
            }

            $output->writeln(sprintf(
                '<info>Creating project <comment>%s</comment></info>',
                $data['project']['name']
            ));

            $command = $client->getCommand('CreateProject', array(
                'slug' => $data['project']['name'],
                'name' => ucfirst($data['project']['name']),
            ));
            $client->execute($command);
        }

        // Ensure locales are present
        $command = $client->getCommand('ListLanguages', array(
            'project' => $data['project']['name'],
        ));
        $response = $client->execute($command);
        $locales = array();
        foreach ($response as $language) {
            $locales[] = $language['locale'];
        }

        $localesToCreate = array_diff($data['project']['locales'], $locales);

        foreach ($localesToCreate as $locale) {
            $output->writeln(sprintf(
                '<info>Adding locale <comment>%s</comment></info>',
                $locale
            ));

            $command = $client->getCommand('CreateLanguage', array(
                'project' => $data['project']['name'],
                'locale' => $locale,
            ));
            $response = $client->execute($command);
        }

        // Import files
        foreach ($data['files']['expr'] as $expr) {
            $pattern = $expr;
            $pattern = str_replace('<domain>', '(?P<domain>\w+)', $pattern);
            $pattern = str_replace('<locale>', '(?P<locale>\w+)', $pattern);
            $pattern = '@'.$pattern.'@';

            $expr = str_replace('<domain>', '*', $expr);
            $expr = str_replace('<locale>', '*', $expr);

            foreach (glob($expr, GLOB_BRACE) as $filename) {
                if (!preg_match($pattern, $filename, $matches)) {
                    continue;
                }

                $output->writeln(sprintf(
                    '<info>Importing file <comment>%s</comment></info>',
                    $filename
                ));

                $command = $client->getCommand('ImportDomain', array(
                    'project' => $data['project']['name'],
                    'slug' => $matches['domain'],
                    'locale' => $matches['locale'],
                    'file' => '@'.realpath($filename)
                ));
                $client->execute($command);
            }
        }
    }
}
