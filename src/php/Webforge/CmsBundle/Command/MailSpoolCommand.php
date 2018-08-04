<?php

namespace Webforge\CmsBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class MailSpoolCommand extends ContainerAwareCommand
{
    protected $spoolPath;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('mail:spool')
            ->setDescription('Returns all mails currently spooled.')
            ->setDefinition(array(
                new InputOption(
                    'clear',
                    null,
                    InputOption::VALUE_NONE,
                    'Clears the whole spool (!).'
                )
            ));
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->spoolPath = $this->getContainer()->getParameter('mailer_spoolpath');


        if (!is_dir($this->spoolPath)) {
            mkdir($this->spoolPath, 0755, true);
        }

        if ($input->getOption('clear')) {
            foreach ($this->getFinder() as $file) {
                unlink($file);
            }

            return 0;
        }

        $getters = array(
            'subject' => 'getSubject',
            'returnPath' => 'getReturnPath',
            'sender' => 'getSender',
            'from' => 'getFrom',
            'replyTo' => 'getReplyTo',
            'to' => 'getTo',
            'cc' => 'getCc',
            'bcc' => 'getBcc',
            'body' => 'getBody'
        );

        $result = array();
        foreach ($this->getFinder() as $mailFile) {
            /** @var $message \Swift_Message */
            $message = unserialize($mailFile->getContents());

            $export = new \stdClass;

            foreach ($getters as $var => $getter) {
                $export->$var = $message->$getter();
            }

            $export->fullFrom = $export->from;
            $export->from = current(array_keys($export->fullFrom));

            $export->headers = array();
            foreach ($message->getHeaders()->getAll() as $header) {
                $export->headers[$header->getFieldName()] = $header->getFieldBody();
            }

            if ($export->headers['X-Swift-To']) {
                $export->to = $export->headers['X-Swift-To'];
            }

            $result[] = $export;
        }

        $output->write(json_encode($result, JSON_PRETTY_PRINT));
    }

    protected function getFinder()
    {
        return Finder::create()->files()->in($this->spoolPath);
    }
}
