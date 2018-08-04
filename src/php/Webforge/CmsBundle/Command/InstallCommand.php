<?php

namespace Webforge\CmsBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Question\Question;

use Webforge\Common\System\Dir;
use Webforge\Common\StringUtil as S;
use Symfony\Component\Yaml\Yaml;
use Webmozart\Json\JsonDecoder;
use Webmozart\Json\JsonEncoder;

class InstallCommand extends ContainerAwareCommand
{
    protected $target;
    protected $cms;
    protected $config;

    private $input;
    private $output;

    protected function configure()
    {
        $this
            ->setName('install')
            ->addArgument(
                'target',
                InputArgument::OPTIONAL
            )
            ->addArgument(
                'project-name',
                InputArgument::OPTIONAL,
                'Meine Webseite'
            )
            ->addOption(
                'project-nicename',
                null,
                InputOption::VALUE_REQUIRED,
                'use only lowercase numeric and alpha and -'
            )
            ->addOption(
                'bundle-name',
                null,
                InputOption::VALUE_REQUIRED,
                'in camelCase'
            );
    }

    protected function config()
    {
        $this->target = Dir::factoryTS($this->askStringValue(
            'Provide absolute path to the target directory',
            $this->input->getArgument('target')
        ));
        $this->output->writeln('<info>Install to: '.$this->target.' </info>');
        $this->target->create();

        $this->cms = $GLOBALS['env']['root'];

        $this->config = (object)[
            "composer" => "composer",
            "version" => "1.11.2",

            "project.name" => $this->askStringValue(
                'Name des Projektes',
                $name = $this->input->getArgument('project-name') ?: str_replace('-', ' ', $this->target->getName())
            ),
            "project.nicename" => $nicename = $this->askStringValue(
                'technischer Name des Projektes',
                $this->input->getOption('project-nicename') ?: S::camelCaseToDash($name)
            ),
            'project.bundle_name' => $bundleName = $this->askStringValue(
                'Name des Symfony Bundles (in camelCase)',
                $this->input->getOption('bundle-name') ?: S::dashToCamelCase($nicename).'Bundle'
            ),
            'project.bundle_name_dashed' => S::camelCaseToDash($bundleName),
            'project.bundle_namespace' => $bundleName,

            "target.root" => $this->target->wtsPath(),
            "cms.root" => $this->cms->wtsPath()
        ];
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        $this->completes = array();

        $this->config($input, $output);

        $this->installAndConfigureWithComposer();
        $this->copySymfonyBasicFiles($output);
        $this->configureSymfonyParameters($input, $output);
        $this->configureScss();

        $formatter = $this->getHelper('formatter');
        $output->writeln($formatter->formatBlock(
            $this->completes,
            'comment'
        ));
        $this->target->getFile('post-install.bat')->writeContents(implode("\n".$this->completes)."\n");
        $output->writeln('<info>this commands are written to post-install.bat</info>');
    }

    protected function configureSymfonyParameters($input, $output)
    {
        file_put_contents(
            (string)$this->target->getFile('.gitignore'),
            "\n.env\n/etc/symfony/parameters.yml",
            FILE_APPEND
        );

        $output->writeln('<info>Configure Symfony Parameters</info>');

        $parameters = [
            'secret' => $this->generateNewSecret(),
            'database_host' => $this->askStringValue('value for database_host', '127.0.0.1'),
            'database_port' => null,
            'database_name' => $name = $this->askStringValue(
                'value for database_name',
                $this->config->{'project.nicename'}
            ),
            'database_user' => $this->askStringValue('value for database_user', $name),
            'database_password' => $this->askStringValue(
                'value for database_password',
                mb_substr($this->generateNewSecret(), 0, 7)
            ),
            'mailer_transport' => 'smtp',
            'mailer_host' => '127.0.0.1',
            'mailer_user' => null,
            'mailer_password' => null
        ];

        $formatter = $this->getHelper('formatter');

        $sql = array();
        $sql[] = sprintf("CREATE DATABASE IF NOT EXISTS `%s`;", $parameters['database_name']);
        $sql[] = sprintf(
            "CREATE USER '%s'@'%s' IDENTIFIED BY '%s';",
            $parameters['database_user'],
            $parameters['database_host'],
            $parameters['database_password']
        );
        $sql[] = sprintf(
            "GRANT USAGE ON *.* TO '%s'@'%s' IDENTIFIED BY '%s' WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0;",
            $parameters['database_user'],
            $parameters['database_host'],
            $parameters['database_password']
        );
        $sql[] = sprintf(
            "GRANT ALL PRIVILEGES ON `%s`.* TO '%s'@'%s';",
            $parameters['database_name'],
            $parameters['database_user'],
            $parameters['database_host']
        );

        $output->writeln('<comment>Create the Database in SQL (copy n paste this as root)</comment>');
        $output->writeln($formatter->formatBlock(
            $sql,
            'comment'
        ));

        $this->target->getFile('etc/symfony/parameters.yml')->writeContents(
            Yaml::dump(['parameters' => $parameters])
        );

        $this->completes[] = 'webforge-doctrine-compiler orm:compile --extension=Serializer etc/doctrine/model.json src/php/ && php app/console doctrine:schema:update -v --force';

        $this->output->writeln('<info>Wrote parameters.yml</info>');
    }

    protected function copySymfonyBasicFiles($output)
    {
        $se = $this->cms->sub('Resources/standard-edition/');

        $vars = (array)$this->config;

        foreach ($se->getFiles() as $file) {
            $relativeFile = clone $file;
            $relativeFile->makeRelativeTo($se);

            $targetFile = $this->target->getFile($relativeFile);
            $targetFile->getDirectory()->create();

            $contents = $file->getContents();
            $contents = S::miniTemplate($contents, $vars);

            $targetFile->writeContents($contents);

            //$output->writeln('copied: '.$relativeFile);
        }

        // specials
        $this->target->getFile('src/php/RenameBundle/RenameBundle.php')->move($this->target->getFile('src/php/RenameBundle/'.$this->config->{'project.bundle_name'}.'.php'));
        $this->target->sub('src/php/RenameBundle')->move($this->target->sub('src/php/'.$this->config->{'project.bundle_name'}));

        $this->completes[] = 'git add bin/cli.sh';
        $this->completes[] = 'git update-index --chmod=+x bin/cli.sh';

        file_put_contents((string)$this->target->getFile('.gitignore'), "\n/files/logs\n/files/cache", FILE_APPEND);

        $this->output->writeln('<info>copied and configured symfony standard edition</info>');
    }

    protected function installAndConfigureWithComposer()
    {
        // write composer updates
        $encoder = new JsonEncoder();
        $encoder->setEscapeSlash(false);
        $encoder->setPrettyPrinting(true);

        $decoder = new JsonDecoder();
        $data = $decoder->decodeFile($composerJsonFile = $this->target->getFile('composer.json'));

        if (!isset($data->autoload)) {
            $data->autoload = new \stdClass;
        }

        if (!isset($data->autoload->{"psr-0"})) {
            $data->autoload->{"psr-0"} = new \stdClass;
        }

        $data->autoload->{"psr-0"}->{$this->config->{'project.bundle_name'}.'\\'} = ["src/php/"];
        $data->autoload->{"psr-0"}->{'Webforge\\CmsBundle'} = [$this->config->{'cms.root'}.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'php'];
        $data->autoload->{"psr-0"}->{'Webforge\\Symfony'} = ['D:\\www\\webforge-symfony\\src\\php'];
        $data->autoload->{"psr-0"}->{'Webforge\\'} = [$this->config->{'cms.root'}.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'php'];

        $data->{"prefer-stable"} = true;
        $data->{"minimum-stability"} = "dev";

        $deps = json_decode('{
      "knplabs/knp-markdown-bundle": "^1.4",
      "nesbot/carbon": "^1.21",
      "twig/extensions": "^1.4",
      "mekras/atom": "^1.0"
    }');

        $devdeps = json_decode('{
      "webforge/testing": "^1.0"
    }');

        if (!isset($data->require)) {
            $data->require = new \stdClass;
        }
        if (!isset($data->{'require-dev'})) {
            $data->{'require-dev'} = new \stdClass;
        }

        foreach ($deps as $dep => $version) {
            $data->require->{$dep} = $version;
            //$this->composer('require --no-update '.$dep.':'.$version);
        }

        foreach ($devdeps as $dep => $version) {
            $data->{'require-dev'}->{$dep} = $version;
            //$this->composer('require --dev --no-update '.$dep.':'.$version);
        }

        $encoder->encodeFile($data, (string)$composerJsonFile);

        $this->composer('require --no-update webforge/cms:'.$this->config->version);

        $this->completes[] = 'composer update --prefer-dist -o';
    }

    protected function configureScss()
    {
        $this->completes[] = 'npm install';

        file_put_contents((string)$this->target->getFile('.gitignore'), "\n/www/assets", FILE_APPEND);
    }

    protected function askStringValue($question, $default)
    {
        $question = new Question(sprintf('%s ( default: %s ): ', $question, $default), $default);
        $question->setNormalizer(function ($value) use ($default) {
            if ($value == "") {
                return $default;
            }

            return $value;
        });

        return $this->getHelper('question')->ask($this->input, $this->output, $question);
    }

    protected function composer($cmd, Dir $workdir = null)
    {
        return $this->exec($this->config->composer.' '.$cmd, $workdir);
    }

    protected function exec($commandline, Dir $workdir = null)
    {
        if (!isset($workdir)) {
            $workdir = $this->target;
        }

        $commandline = 'cd '.$workdir->getQuotedString().' && '.$commandline;

        $retvar = null;
        passthru($commandline, $retvar);
        return $retvar;
    }

    protected function generateNewSecret()
    {
        if (function_exists('openssl_random_pseudo_bytes')) {
            return hash('sha1', openssl_random_pseudo_bytes(23));
        }

        return hash('sha1', uniqid(mt_rand(), true));
    }
}
