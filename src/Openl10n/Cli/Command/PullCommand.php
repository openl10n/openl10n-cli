<?php

namespace Openl10n\Cli\Command;

use Guzzle\Http\Exception\ClientErrorResponseException;
use Openl10n\Sdk\Api;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\Glob;
use Symfony\Component\Yaml\Yaml;

class PullCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pull')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $data = $this->getConfig();

        $api = new Api(array(
            'hostname' => $data['server']['hostname'],
            'username' => $data['server']['username'],
            'password' => $data['server']['password'],
            'scheme' => $data['server']['use_ssl'] ? 'https' : 'http',
        ));

        $projectSlug = $data['project']['slug'];

        // Get project
        try {
            $project = $api->getProject($projectSlug);
        } catch (ClientErrorResponseException $e) {
            if (404 !== $e->getResponse()->getStatusCode()) {
                throw $e;
            }

            $output->writeln(sprintf(
                '<error>Project "%s" does not exist</error>',
                $data['project']['slug']
            ));

            return 1;
        }

        $languages = $api->getLanguages($projectSlug);
        $locales = array();
        foreach ($languages as $language) {
            $locales[] = $language['locale'];
        }

        // Get files
        foreach ($data['files'] as $file) {
            $pattern = $file['source'];
            $pattern = str_replace('<domain>', '___DOMAIN_PLACEHOLDER___', $pattern);
            $pattern = str_replace('<locale>', '___LOCALE_PLACEHOLDER___', $pattern);
            $pattern = Glob::toRegex($pattern);

            $pattern = str_replace('___DOMAIN_PLACEHOLDER___', '(?P<domain>\w+)', $pattern);
            $pattern = str_replace('___LOCALE_PLACEHOLDER___', '(?P<locale>\w+)', $pattern);

            $export = array();

            $finder = new Finder();
            $finder->in(getcwd())->path($pattern);
            foreach ($finder->files() as $file) {
                if (!preg_match($pattern, $file->getRelativePathname(), $matches)) {
                    $output->writeln(sprintf(
                        'File %s does match pattern %s',
                        $file->getRelativePathname(),
                        $pattern
                    ));
                    continue;
                }

                $domain = $matches['domain'];

                $pattern2 = str_replace('(?P<domain>\w+)', $domain, trim($pattern, '#'));

                $pos = strpos($pattern2, '(?P<locale>\w+)');
                if ($pos > 0) {
                    $prefix = '('.substr($pattern2, 0, $pos).')';
                } else {
                    $prefix = '';
                }
                if ($pos < strlen($pattern2) - strlen('(?P<locale>\w+)') - 1) {
                    $suffix = '('.substr($pattern2, $pos + strlen('(?P<locale>\w+)') + 1, strlen($pattern2) - $pos - strlen('(?P<locale>\w+)')).')';
                } else {
                    $suffix = '';
                }
                $pattern3 = '#'.$prefix.'(\w+)'.$suffix.'#';

                $path = preg_replace($pattern3, '$1___LOCALE_PLACEHOLDER___$3', $file->getRelativePathname());

                $export[$domain] = $path;
            }
        }

        foreach ($export as $domain => $path) {
            $format = pathinfo($path, PATHINFO_EXTENSION);

            foreach ($locales as $locale) {
                $filepath = str_replace('___LOCALE_PLACEHOLDER___', $locale, $path);

                $content = $api->exportFile($projectSlug, $domain, $locale, $format);

                $md5file = '';
                if (file_exists($filepath) && is_readable($filepath)) {
                    $md5file = md5(file_get_contents($filepath));
                }

                $md5content = '';
                if (strlen($content) > 0) {
                    $md5content = md5($content);
                }

                if ($md5file !== $md5content) {
                    $output->writeln(sprintf(
                        '<info>Writing into <comment>%s</comment></info>',
                        $filepath
                    ));

                    file_put_contents($filepath, $content);
                }
            }
        }
    }
}
