<?php

declare(strict_types=1);

namespace Shapecode\Bundle\CronBundle\Tests\Command;

use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;
use Shapecode\Bundle\CronBundle\Command\CronRunCommand;
use Shapecode\Bundle\CronBundle\Entity\CronJob;
use Shapecode\Bundle\CronBundle\Entity\CronJobResult;
use Shapecode\Bundle\CronBundle\Repository\CronJobRepository;
use Shapecode\Bundle\CronBundle\Repository\CronJobResultRepository;
use Shapecode\Bundle\CronBundle\Service\CommandHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpKernel\Kernel;

final class CronRunCommandTest extends TestCase
{
    public function testRun(): void
    {
        $kernel = $this->createMock(Kernel::class);
        $kernel->method('getProjectDir')->willReturn(__DIR__);

        $commandHelper = $this->createMock(CommandHelper::class);
        $commandHelper->method('getConsoleBin')->willReturn('/bin/console');
        $commandHelper->method('getPhpExecutable')->willReturn('php');
        $commandHelper->method('getTimeout')->willReturn(null);

        $manager = $this->createMock(ObjectManager::class);

        $job = CronJob::create('pwd', '* * * * *');
        $job->setNextRun(new DateTime());

        $cronJobRepo = $this->createMock(CronJobRepository::class);
        $cronJobRepo->method('findAll')->willReturn([
            $job,
        ]);

        $cronJobResultRepo = $this->createMock(CronJobResultRepository::class);

        $registry = $this->createMock(ManagerRegistry::class);
        $registry->method('getManager')->willReturn($manager);
        $registry->method('getRepository')->willReturnCallback(static function (string $param) use ($cronJobRepo, $cronJobResultRepo) {
            if ($param === CronJob::class) {
                return $cronJobRepo;
            }

            if ($param === CronJobResult::class) {
                return $cronJobResultRepo;
            }
        });

        $input  = $this->createMock(InputInterface::class);
        $output = new BufferedOutput();

        $command = new CronRunCommand($commandHelper, $registry);
        $command->run($input, $output);

        self::assertEquals('shapecode:cron:run', $command->getName());
    }

    public function testRunWithTimeout(): void
    {
        $kernel = $this->createMock(Kernel::class);
        $kernel->method('getProjectDir')->willReturn(__DIR__);

        $commandHelper = $this->createMock(CommandHelper::class);
        $commandHelper->method('getConsoleBin')->willReturn('/bin/console');
        $commandHelper->method('getPhpExecutable')->willReturn('php');
        $commandHelper->method('getTimeout')->willReturn(30.0);

        $manager = $this->createMock(ObjectManager::class);

        $job = CronJob::create('pwd', '* * * * *');
        $job->setNextRun(new DateTime());

        $cronJobRepo = $this->createMock(CronJobRepository::class);
        $cronJobRepo->method('findAll')->willReturn([
            $job,
        ]);

        $cronJobResultRepo = $this->createMock(CronJobResultRepository::class);

        $registry = $this->createMock(ManagerRegistry::class);
        $registry->method('getManager')->willReturn($manager);
        $registry->method('getRepository')->willReturnCallback(static function (string $param) use ($cronJobRepo, $cronJobResultRepo) {
            if ($param === CronJob::class) {
                return $cronJobRepo;
            }

            if ($param === CronJobResult::class) {
                return $cronJobResultRepo;
            }
        });

        $input  = $this->createMock(InputInterface::class);
        $output = new BufferedOutput();

        $command = new CronRunCommand($commandHelper, $registry);
        $command->run($input, $output);

        self::assertEquals('shapecode:cron:run', $command->getName());
    }
}
