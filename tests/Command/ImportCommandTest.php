<?php

namespace Senalia\QualityCode\TransformAndLoadBundle\Tests\Command;

use QualityCode\TransformAndLoadBundle\Command\ImportCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use QualityCode\TransformAndLoadBundle\Tests\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class ImportCommandTest extends KernelTestCase
{
    public function testExecute()
    {
        self::bootKernel();
        $application = new Application(self::$kernel);

        $application->add(new ImportCommand());

        $command = $application->find('qltyc:import');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command' => $command->getName(),
            'schema' => 'test',
            'file' => 'test',
        ));

        $output = $commandTester->getDisplay();
        $this->assertContains("This file doesn't exist !", $output);
    }
}
