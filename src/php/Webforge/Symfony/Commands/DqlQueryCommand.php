<?php

namespace Webforge\Symfony\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Webmozart\Json\JsonDecoder;

class DqlQueryCommand extends Command {

    protected $doctrine;

    public function __construct($doctrine) {
        parent::__construct();
        $this->doctrine = $doctrine;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
        ->setName('testing:dql')
        ->setDescription('Executes arbitrary DQL directly from the command line.')
        ->setDefinition(array(
            new InputArgument('dql', InputArgument::REQUIRED, 'The DQL to execute.'),
            new InputArgument('parameters', InputArgument::OPTIONAL, 'The parameters for the dql encoded as JSON.'),
            new InputOption(
                'base64', null, InputOption::VALUE_NONE,
                'If set the params will be expected in base64 encoded json.'
            ),
            new InputOption(
                'manager', 'em', InputOption::VALUE_REQUIRED,
                'The name of the entityManager to use.',
                'default'
            ),
            new InputOption(
                'first-result', null, InputOption::VALUE_REQUIRED,
                'The first result in the result set.'
            ),
            new InputOption(
                'max-result', null, InputOption::VALUE_REQUIRED,
                'The maximum number of results in the result set.'
            )
        ))
        ->setHelp(<<<EOT
Executes arbitrary DQL directly from the command line.
EOT
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->doctrine->getManager($input->getOption('manager'));

        if (($dql = $input->getArgument('dql')) === null) {
            throw new \RuntimeException("Argument 'DQL' is required in order to execute this command correctly.");
        }

        $query = $em->createQuery($dql);

        if (($firstResult = $input->getOption('first-result')) !== null) {
            if ( ! is_numeric($firstResult)) {
                throw new \LogicException("Option 'first-result' must contains an integer value");
            }

            $query->setFirstResult((int) $firstResult);
        }

        if (($maxResult = $input->getOption('max-result')) !== null) {
            if ( ! is_numeric($maxResult)) {
                throw new \LogicException("Option 'max-result' must contains an integer value");
            }

            $query->setMaxResults((int) $maxResult);
        }

        $jsonParams = $input->getArgument('parameters');

        if (!empty($jsonParams)) {
            if ($input->getOption('base64')) {
                $jsonParams = base64_decode($jsonParams);
            }
        
            $json = new JsonDecoder();
            $params = $json->decode($jsonParams);

            $query->setParameters((array) $params);
        }

        $resultSet = $query->execute(array(), \Doctrine\ORM\Query::HYDRATE_ARRAY);

        print json_encode($resultSet, JSON_PRETTY_PRINT);
    }
}
