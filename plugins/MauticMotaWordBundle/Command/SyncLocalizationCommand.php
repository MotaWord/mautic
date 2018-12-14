<?php

/*
 * @copyright   2018 MotaWord. All rights reserved
 * @author      MotaWord
 *
 * @link        https://www.motaword.com
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticMotaWordBundle\Command;

use MauticPlugin\MauticMotaWordBundle\SyncService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SyncLocalizationCommand extends ContainerAwareCommand
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('mautic:motaword:sync')
            ->setDescription('Sync email translations with your MotaWord project')
            ->addOption(
                '--project-name',
                '-i',
                InputOption::VALUE_REQUIRED,
                'MW project name.',
                null
            );
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container    = $this->getContainer();
        $this->logger = $container->get('monolog.logger.mautic');
        /** @var SyncService $service */
        $service = $container->get('mautic.motaword.syncservice');

        // Prepare source files
        $emails = $service->getEmailsInDefaultLanguage();
        $service->createFilesForEmails($emails);

        $baseDirectory = __DIR__.'/..';

        $this->execPush($baseDirectory);
        $this->execPull($baseDirectory);

        $service->createEmailsFromFiles();

        return 0;
    }

    protected function execPush($baseDirectory = null)
    {
        if (!$baseDirectory) {
            $baseDirectory = '.';
        }

        $cmd = $baseDirectory.'/./bin/zanata-cli/bin/zanata-cli -B '.
            'push '.
            '--user-config '.$baseDirectory.'/./Config/zanata.ini '.
            '--project-config '.$baseDirectory.'/./Config/zanata.xml '.
            '--push-type source '.
            '--file-types "HTML,JSON"';

        $this->logger->debug($cmd);

        exec($cmd, $output, $code);
        $this->logger->info(implode(PHP_EOL, $output));

        if ($code > 0) {
            throw new \Error(print_r($output, true));
        }

        return $code < 1 ? true : false;
    }

    protected function execPull($baseDirectory = null)
    {
        if (!$baseDirectory) {
            $baseDirectory = '.';
        }

        $cmd = $baseDirectory.'/./bin/zanata-cli/bin/zanata-cli -B '.
            'pull '.
            '--user-config '.$baseDirectory.'/./Config/zanata.ini '.
            '--project-config '.$baseDirectory.'/./Config/zanata.xml '.
            '--pull-type trans '.
            '--min-doc-percent 1';

        $this->logger->debug($cmd);

        exec($cmd, $output, $code);
        $this->logger->info(implode(PHP_EOL, $output));

        if ($code > 0) {
            throw new \Error(print_r($output, true));
        }

        return $code < 1 ? true : false;
    }
}
