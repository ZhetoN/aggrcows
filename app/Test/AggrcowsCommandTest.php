<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Tester\CommandTester;

use Aggrcows\AggrcowsCommand;

/**
 * @covers AggrcowsCommand
 */
final class AggrcowsCommandTest extends TestCase
{

    public function testNumberOfTestCasesInRange(): void
    {
        $testcases = 1;
        $command = $this->getCommand();
        $commandTester = new CommandTester($command);

        $callback = function(
            InputInterface $input,
            OutputInterface $output
        ) use ($command, $testcases) {
            $this->assertEquals(
                $command->getTestCases($input, $output), $testcases
            );
        };

        $command->expects($this->once())
            ->method('interact')
            ->will($this->returnCallback($callback));

        $commandTester->setInputs([$testcases]);
        $commandTester->execute(['command' => $command->getName()]);
    }

    public function testNumberOfTestCasesOutOfRange(): void
    {
        $command = $this->getCommand();
        $commandTester = new CommandTester($command);

        $callback = function(
            InputInterface $input,
            OutputInterface $output
        ) use ($command, $testcases) {
            $this->assertEquals(
                $command->getTestCases($input, $output), $testcases
            );
        };

        $command->expects($this->exactly(2))
            ->method('interact')
            ->will($this->returnCallback($callback));

        $testcases = 0;
        $commandTester->setInputs([$testcases]);
        $this->assertTrue($this->expectedException($commandTester, $command));

        $testcases = 11;
        $commandTester->setInputs([$testcases]);
        $this->assertTrue($this->expectedException($commandTester, $command));
    }

    public function test_NC_isValid(): void
    {
        $n = 5;
        $c = 3;
        $command = $this->getCommand();
        $commandTester = new CommandTester($command);

        $callback = function(
            InputInterface $input,
            OutputInterface $output
        ) use ($command, $n, $c) {
            list($_n, $_c) = $command->getTestNC($input, $output);
            $valid = $n == $_n and $c == $$c;
            $this->assertTrue($valid);
        };

        $command->expects($this->once())
            ->method('interact')
            ->will($this->returnCallback($callback));

        $nc = implode(' ', [$n, $c]);
        $commandTester->setInputs([$nc]);
        $commandTester->execute(['command' => $command->getName()]);
    }

    public function test_N_isOutOfRange(): void
    {
        $n = 1;
        $c = 3;
        $command = $this->getCommand();
        $commandTester = new CommandTester($command);

        $callback = function(
            InputInterface $input,
            OutputInterface $output
        ) use ($command, $n, $c) {
            list($_n, $_c) = $command->getTestNC($input, $output);
            $valid = $n == $_n and $c == $$c;
            $this->assertTrue($valid);
        };

        $command->expects($this->exactly(2))
            ->method('interact')
            ->will($this->returnCallback($callback));

        $nc = implode(' ', [$n, $c]);
        $commandTester->setInputs([$nc]);
        $this->assertTrue($this->expectedException($commandTester, $command));

        $n = 100001;
        $nc = implode(' ', [$n, $c]);
        $commandTester->setInputs([$nc]);
        $this->assertTrue($this->expectedException($commandTester, $command));
    }

    public function test_C_isGreaterThan_N(): void
    {
        $n = 2;
        $c = 3;
        $command = $this->getCommand();
        $commandTester = new CommandTester($command);

        $callback = function(
            InputInterface $input,
            OutputInterface $output
        ) use ($command, $n, $c) {
            $command->getTestNC($input, $output);
        };

        $command->expects($this->once())
            ->method('interact')
            ->will($this->returnCallback($callback));

        $nc = implode(' ', [$n, $c]);
        $commandTester->setInputs([$nc]);
        $this->assertTrue($this->expectedException($commandTester, $command));
    }

    public function testPositionsInsert(): void
    {
        $positions = [1, 2, 3, 4, 5];

        $command = $this->getCommand();
        $commandTester = new CommandTester($command);

        $callback = function(
            InputInterface $input,
            OutputInterface $output
        ) use ($command, $positions) {
            $count = count($positions);
            $inserted = $command->getTestCasePositions($input, $output, $count);
            $valid = (0 === count(array_diff($positions, $inserted)));
            $this->assertTrue($valid);
        };

        $command->expects($this->once())
            ->method('interact')
            ->will($this->returnCallback($callback));

        $commandTester->setInputs($positions);
        $commandTester->execute(['command' => $command->getName()]);
    }

    public function testDuplicatePositionsInsert(): void
    {
        $positions = [1, 2, 2, 4, 5];
        $positions_count = count($positions);

        $command = $this->getCommand();
        $commandTester = new CommandTester($command);

        $callback = function(
            InputInterface $input,
            OutputInterface $output
        ) use ($command, $positions_count) {
            $command->getTestCasePositions($input, $output, $positions_count);
        };

        $command->expects($this->once())
            ->method('interact')
            ->will($this->returnCallback($callback));

        $commandTester->setInputs($positions);
        $this->assertTrue($this->expectedException($commandTester, $command));
    }

    public function testNonIntegerPositionsInsert(): void
    {
        $positions = [1, 2, 'A', 4, 5];
        $positions_count = count($positions);

        $command = $this->getCommand();
        $commandTester = new CommandTester($command);

        $callback = function(
            InputInterface $input,
            OutputInterface $output
        ) use ($command, $positions_count) {
            $command->getTestCasePositions($input, $output, $positions_count);
        };

        $command->expects($this->once())
            ->method('interact')
            ->will($this->returnCallback($callback));

        $commandTester->setInputs($positions);
        $this->assertTrue($this->expectedException($commandTester, $command));
    }


    public function testSingleTestCase(): void
    {
        $app = new Application();
        $app->add(new  AggrcowsCommand());
        $command = $app->find('aggrcows');
        $commandTester = new CommandTester($command);

        $input = [1, '5 3', 1, 2, 8, 4, 9];
        $commandTester->setInputs($input);

        $commandTester->execute(['command' => $command->getName()]);
        $this->assertRegExp(
            '/Minimal distance:\s3/',
            $commandTester->getDisplay()
        );

    }

    public function testMultipleTestCases(): void
    {
        $app = new Application();
        $app->add(new  AggrcowsCommand());
        $command = $app->find('aggrcows');
        $commandTester = new CommandTester($command);

        $input = [2, '5 3', 1, 2, 8, 4, 9, '2 2', 5, 10];
        $commandTester->setInputs($input);

        $commandTester->execute(['command' => $command->getName()]);
        $output = $commandTester->getDisplay();
        preg_match_all('/Minimal distance:\s([3,5])/im', $output, $matches);
        $valid = (0 === count(array_diff([3,5], $matches[1])));
        $this->assertTrue($valid );
    }

    private function getCommand()
    {
        $commandMock = $this->getMockBuilder('\Aggrcows\AggrcowsCommand')
            ->setMethods(['interact', 'execute'])
            ->getMock();

        $app = new Application();
        $app->add($commandMock);
        $command = $app->find('aggrcows');

        return $command;
    }

    private function expectedException($commandTester, $command)
    {
        try {
            $commandTester->execute(['command' => $command->getName()]);
        } catch (\InvalidArgumentException $e) {
            return true;
        }
        return false;
    }
}
