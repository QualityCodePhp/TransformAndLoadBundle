<?php

namespace QualityCode\TransformAndLoadBundle\Command;

use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Import command line.
 */
class ImportCommand extends ContainerAwareCommand
{
    use LockableTrait;

    protected function configure()
    {
        $this
            ->setName('qltyc:import')
            ->setDescription('Import de données')
            ->addArgument('schema', InputArgument::REQUIRED, "Schema d'import")
            ->addArgument('file', InputArgument::REQUIRED, 'Fichier à importer')
        ;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (0 < $this->checkParams($input, $output)) {
            return 0;
        }

        $schema = $input->getArgument('schema');
        $fileName = $input->getArgument('file');

        $schemaDefinition = $this->getContainer()->getParameter('qltyc_tl.imports.'.$schema);

        $begin = new \DateTime();
        $output->writeln('<comment>Début de l\'import du fichier '.$fileName.' avec le schema '.$schema.' à '.$begin->format('d-m-Y G:i:s').'</comment>');

        $this->getContainer()->get('qltyc.tl.import')->import($schemaDefinition, $fileName, $output);

        $end = new \DateTime();
        $output->writeln('');
        $output->writeln('<comment>L\'import du fichier '.$fileName.' utilisant le schema '.$schema.' est terminé à '.$end->format('d-m-Y G:i:s').'</comment>');

        $this->release();
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    private function checkParams(InputInterface $input, OutputInterface $output)
    {
        $errors = 0;

        $errors += $this->checkLock($output);
        $errors += $this->checkFile($input, $output);
        $errors += $this->checkSchema($input, $output);

        return $errors;
    }

    /**
     * @param OutputInterface $output
     *
     * @return int
     */
    private function checkLock(OutputInterface $output)
    {
        if (!$this->lock()) {
            $output->writeln('The command is already running in another process.');

            return 1;
        }

        return 0;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    private function checkFile(InputInterface $input, OutputInterface $output)
    {
        $fileName = $input->getArgument('file');

        $fs = new Filesystem();

        if (!$fs->exists($fileName)) {
            $output->writeln('This file doesn\'t exist !');

            return 1;
        }

        return 0;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    private function checkSchema(InputInterface $input, OutputInterface $output)
    {
        $schema = $input->getArgument('schema');
        if (!$this->getContainer()->hasParameter('qltyc_tl.imports.'.$schema)) {
            $output->writeln('This schema doesn\'t exist !');

            return 1;
        }

        return 0;
    }
}
