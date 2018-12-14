<?php

namespace MauticPlugin\MauticMotaWordBundle;

use DirectoryIterator;
use Mautic\CoreBundle\Helper\CoreParametersHelper;
use Mautic\EmailBundle\Entity\Email;
use Mautic\EmailBundle\Model\EmailModel;
use Psr\Log\LoggerInterface;

// WHAT THIS DOES
// get email records in default language (en)
// create an html file for each email separately
// prepare zanata configurations for those directories. this is where user decides
//          which target languages they want translated.
// use zanata-cli to push source files
// use zanata-cli to pull translations
// loop through non-default-language directories
// get their content, create or update an email record for that email in that language
class SyncService
{
    private $baseURL;
    private $username;
    private $password;

    private static $baseDirectory;
    private static $cacheSubDirectory = 'l10n';
    private static $languages         = ['fr'];

    private $emailModel;
    private $logger;

    /**
     * MotaWordApi constructor.
     *
     * @param EmailModel           $emailModel
     * @param LoggerInterface      $logger
     * @param CoreParametersHelper $coreParametersHelper
     */
    public function __construct(
        EmailModel $emailModel,
        LoggerInterface $logger,
        CoreParametersHelper $coreParametersHelper
    ) {
        $this->emailModel     = $emailModel;
        $this->logger         = $logger;

        $this->baseURL  = getenv('MOTAWORD_PROJECT_URL');
        $this->username = getenv('MOTAWORD_PROJECT_USERNAME');
        $this->password = getenv('MOTAWORD_PROJECT_PASSWORD');

        static::$baseDirectory = $this->removeTrailingSlash($coreParametersHelper->getParameter('tmp_path')).'/'.static::$cacheSubDirectory;

        // Create temporary directories
        if (!file_exists(static::$baseDirectory)) {
            mkdir(static::$baseDirectory, 0777, true);
        }
    }

    /**
     * @param $dir
     *
     * @return string
     */
    private function removeTrailingSlash($dir)
    {
        if (substr($dir, -1) === '/') {
            $dir = substr($dir, 0, -1);
        }

        return $dir;
    }

    public function getBaseDirectory()
    {
        return static::$baseDirectory;
    }

    public function getLanguages()
    {
        return static::$languages;
    }

    public function getEmailsInDefaultLanguage()
    {
        return $this->emailModel->getEntities([
            'filter' => [
                'force' => [
                    [
                        'column' => 'e.language',
                        'expr'   => 'eq',
                        'value'  => 'en',
                    ],
                ],
            ],
        ]);
    }

    public function createFilesForEmails($emails)
    {
        $subjectFilePath = static::$baseDirectory.'/en/email-subjects.json';
        $subjects        = [];

        $this->logger->info('I am processing '.count($emails).' emails.');

        $done = 0;

        foreach ($emails as $email) {
            /** @var $email Email */
            $language          = $email->getLanguage();
            $languageDirectory = static::$baseDirectory.'/'.$language.'/emails';

            if (!file_exists($languageDirectory)) {
                mkdir($languageDirectory, 0777, true);
            }

            $name     = $email->getName();
            $fileName = $name.'.html';
            $filePath = $languageDirectory.'/'.$fileName;
            $content  = $email->getCustomHtml();

            if (file_exists($filePath)) {
                $this->logger->info('Replacing file '.$fileName.' for language '.$language);
            } else {
                $this->logger->info('Creating file '.$fileName.' for language '.$language);
            }

            if (!file_put_contents($filePath, $content)) {
                $this->logger->error('Error writing file: '.$filePath);

                continue;
            }

            // Only for English emails
            if ($language === 'en') {
                $subjects[$name.'-subject'] = $email->getSubject();
            }

            ++$done;
        }

        $this->logger->info('Created '.$done.' email files under '.static::$baseDirectory);

        file_put_contents($subjectFilePath, json_encode($subjects));

        $this->logger->info('Created a JSON file for email subjects: '.$subjectFilePath);
    }

    public function createEmailsFromFiles()
    {
        $this->logger->info('I am processing the translations into Mautic system now.');

        foreach (static::$languages as $language) {
            $path = static::$baseDirectory.'/'.$language;
            $dir  = new DirectoryIterator($path);

            $emailSubjectsPath = $path.'/email-subjects.json';
            $subjects          = [];
            if (is_readable($emailSubjectsPath)) {
                $subjects = json_decode(file_get_contents($emailSubjectsPath), true);
            }

            foreach ($dir as $file) {
                if ($file->isDot() || $file->isDir()
                    // Only email bodies in this run. Email subjects are handled down there.
                    || $file->getExtension() !== 'html') {
                    continue;
                }

                $emailName   = $file->getBasename('.html');
                $subjectName = $emailName.'-subject';

                $translatedEmailList = $this->emailModel->getEntities([
                    'filter' => [
                        'force' => [
                            [
                                'column' => 'e.language',
                                'expr'   => 'eq',
                                'value'  => $language,
                            ],
                            [
                                'column' => 'e.name',
                                'expr'   => 'eq',
                                'value'  => $emailName.'-'.$language,
                            ],
                        ],
                    ],
                ]);

                $sourceEmailList = $this->emailModel->getEntities([
                    'filter' => [
                        'force' => [
                            [
                                'column' => 'e.language',
                                'expr'   => 'eq',
                                'value'  => 'en',
                            ],
                            [
                                'column' => 'e.name',
                                'expr'   => 'eq',
                                'value'  => $emailName,
                            ],
                        ],
                    ],
                ]);
                $sourceEmail = null;

                if ($sourceEmailList->count() > 0) {
                    /** @var Email $sourceEmail */
                    $sourceEmail = $sourceEmailList->getIterator()->current();
                }

                if ($translatedEmailList->count() > 0) {
                    /** @var Email $translatedEmail */
                    $translatedEmail = $translatedEmailList->getIterator()->current();

                    if (isset($subjects[$subjectName]) && mb_strlen($subjects[$subjectName]) > 0) {
                        $this->logger->info('Setting subject of this email to: '.$subjects[$subjectName]);
                        $translatedEmail->setSubject($subjects[$subjectName]);
                    }

                    $translatedEmail->setLanguage($language);
                    if ($sourceEmail) {
                        $translatedEmail->setTranslationParent($sourceEmail);
                    }
                    $translatedEmail->setIsPublished(true);
                    $translatedEmail->setCustomHtml(file_get_contents($file->getPathname()));
                    $this->emailModel->saveEntity($translatedEmail);

                    $this->logger->info('Updated Mautic email record #'.$translatedEmail->getId().' "'.$emailName.'" for language '.$language);
                } else {
                    // If source email was not found or deleted on purpose from Mautic,
                    // do not create target emails in that case
                    if ($sourceEmail) {
                        /** @var Email $targetEmail */
                        $targetEmail = clone $sourceEmail;
                        $targetEmail->setNew();
                        $targetEmail->setName($emailName.'-'.$language);
                        $targetEmail->setLanguage($language);
                        $targetEmail->setTranslationParent($sourceEmail);
                        $targetEmail->setIsPublished(true);
                        $targetEmail->setCustomHtml(file_get_contents($file->getPathname()));

                        if (isset($subjects[$subjectName]) && mb_strlen($subjects[$subjectName]) > 0) {
                            $this->logger->info('Setting subject of this email to: '.$subjects[$subjectName]);
                            $targetEmail->setSubject($subjects[$subjectName]);
                        }

                        $this->emailModel->saveEntity($targetEmail);

                        $this->logger->info('Created Mautic email record #'.$targetEmail->getId().' "'.$emailName.'" for language '.$language);
                    }
                }
            }
        }
    }
}
