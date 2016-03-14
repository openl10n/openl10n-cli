<?php

namespace Openl10n\Cli\Command;

use Openl10n\Sdk\EntryPoint\ProjectEntryPoint;
use Openl10n\Sdk\EntryPoint\TranslationEntryPoint;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RemoveTranslationCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('remove-translation')
            ->setDescription('Removes a translation given its identifier')
            ->addArgument(
                'identifier',
                InputArgument::REQUIRED,
                'Translation\'s identifier you want to remove'
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $api = $this->get('api');
        /* @var ProjectEntryPoint $projectApi */
        $projectApi = $api->getEntryPoint('project');
        /* @var TranslationEntryPoint $translationApi */
        $translationApi = $api->getEntryPoint('translation');

        $projectSlug = $this->get('project_handler')->getProjectSlug();
        $project = $projectApi->get($projectSlug);

        $identifier = $input->getArgument('identifier');

        $translation = $translationApi->findOneByIdentifier($project, $identifier);
        if (!$translation) {
            $output->writeln(sprintf('<error>Translation "%s" does not exist</error>', $identifier));
        } else {
            $output->writeln(sprintf('<info>Removing</info> translation <comment>"%s"</comment>', $identifier));
            $translationApi->delete($translation);
        }
    }
}
