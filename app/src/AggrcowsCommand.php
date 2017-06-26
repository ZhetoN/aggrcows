<?php

namespace Aggrcows;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

use Aggrcows\Aggrcows;

class AggrcowsCommand extends Command {

    const MIN_T = 1;
    const MAX_T = 10;
    const MIN_N = 2;
    const MAX_N = 100 * 1000;
    const POSITIONS = 1000 * 1000 * 1000;

    protected function configure()
    {
        $this
            ->setName("aggrcows")
            ->setDescription("Get minimal distance between array elements.");
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->testcases = [];
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $test_num = $this->getTestCases($input, $output);

        foreach (range(1, $test_num) as $testcase) {

            if ($test_num > 1) {
                $output->writeln(
                    sprintf("\n<comment>Test case %s:</comment>", $testcase)
                );
            }

            list($n, $c) = $this->getTestNC($input, $output);
            $positions = $this->getTestCasePositions($input, $output, $n);

            $this->testcases[] = [$n, $c, $positions];
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->testcases as $i => $testcase) {
            if (count($this->testcases) > 1) {
                $output->writeln(
                    sprintf('<comment>Running Test Case %s:</comment>', $i + 1)
                );
            } else {
                $output->writeln('<comment>Running Test Case:</comment>');
            }

            list($n, $c, $positions) = $testcase;

            $distance = Aggrcows::getMinDistance($positions, $c);
            $output->writeln("Minimal distance: {$distance}");
        }
    }

    /**
     * Get number of test cases.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    public function getTestCases(InputInterface $input, OutputInterface $output)
    {
        $title = "<info>How many test cases do you want to run?</info>\n";
        $error = "Number of test cases shoud be a number between 1 and 10";

        $question = new Question($title, false);
        $question->setValidator(function ($number) use ($error) {

            if (!$this->intRange($number, self::MIN_T, self::MAX_T)) {
                throw new \InvalidArgumentException($error);
            }
            return (int) $number;
        });

        return $this->doAsk($input, $output, $question, $error);
    }

    /**
     * Get two space-separated integers: N and C per each testcase.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return array
     */
    public function getTestNC(InputInterface $input, OutputInterface $output)
    {
        $title = "<info>Enter two space-separated integers: N and C</info>\n";
        $error = sprintf("N should be a number between %d and %s",
                         self::MIN_N,
                         number_format(self::MAX_N, 0, ",", "."));

        $question = new Question($title, false);

        $question->setNormalizer(function ($answer) {
            return preg_split('/\s+/', $answer);
        });

        $question->setValidator(function($answer) use ($error) {
            if (empty($answer)) {
                 throw new \InvalidArgumentException(
                    "Input required"
                );
            }

            switch (count($answer)) {
                case 1:
                    throw new \InvalidArgumentException(
                        "Enter two space-separated integers: N and C"
                    );
                    break;

                case 2:
                    $n = $this->intRange($answer[0], self::MIN_N, self::MAX_N);

                    if (!$n) {
                        throw new \InvalidArgumentException($error);
                    }

                    $c = $this->intRange($answer[1], self::MIN_N, $n);

                    if (!$c) {
                        if (self::MIN_N == $n) {
                            $msg = "C should be equal N = %s";
                        } else {
                            $msg = "C should be greater than %s"
                                 . " and less or equal N = %s";
                        }

                        throw new \InvalidArgumentException(
                           sprintf($msg, self::MIN_N,
                                   number_format($n, 0, ",", "."))
                        );
                    }

                    return [$n, $c];
                    break;

                default:
                    throw new \InvalidArgumentException("Too many arguments");
            }
        });

        return $this->doAsk($input, $output, $question, $error);
    }

    /**
     * Get N integers per each testcase.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param int             $positions_count
     *
     * @return array
     */
    public function getTestCasePositions(
        InputInterface $input,
        OutputInterface $output,
        int $positions_count
    ) {
        $title = "<info>Enter %d numbers between %s and %s"
               . " (hit Enter after each)</info>";

        $error = sprintf(
            "Position sould be a number between %s and %s",
            0, number_format(self::POSITIONS, 0, ",", ".")
        );

        $output->writeLn(
            sprintf(
                $title, $positions_count, 0,
                number_format(self::POSITIONS, 0, ",", ".")
            )
        );

        $positions = [];

        foreach (range(1, $positions_count) as $position) {

            $question = new Question("<comment>{$position}:</comment> ", false);

            $question->setValidator(function($number) use (&$positions, $error) {
                $val = $this->intRange($number, 0, self::POSITIONS);

                if (false === $val) {
                    throw new \InvalidArgumentException($error);
                }

                # check if position not exists
                if (in_array($val, $positions)) {
                    throw new \InvalidArgumentException("Position exists");
                } else {
                    $positions[] = $val;
                }

                return (int) $val;
            });

            $this->doAsk($input, $output, $question, $error);
        }

        return $positions;
    }

    /**
     * Check if number is integer and it is in range (min, max).
     *
     * @param mixed $number
     * @param int $min
     * @param int $min
     *
     * @return bool
     */
    private function intRange($number, $min, $max)
    {
        // FILTER_VALIDATE_INT do not validate
        // numbers with leading zero
        if (!is_numeric($number)) {
            return false;
        }

        $val = filter_var(
            (int) $number,
            FILTER_VALIDATE_INT,
            array(
                'options' => array(
                    'min_range' => $min,
                    'max_range' => $max,
                )
            )
        );
        // validate zero as true
        return $val !== false ? $val : false;
    }

    /**
     * Wrapper around \Symfony\Component\Console\Helper\QuestionHelper::ask.
     * If input is empty string we want to throw InvalidArgumentException
     * but QuestionHelper already throws RuntimeException if inputStream empty.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param Question        $question
     * @param string          $error_message
     *
     * @return bool
     */
    private function doAsk(InputInterface $input, OutputInterface $output,
        Question $question, string $error_message
    ) {
        $helper = $this->getHelper('question');

        try {
            return $helper->ask($input, $output, $question);
        } catch (\RuntimeException $e) {
            throw new \InvalidArgumentException($error_message);
        }
    }
}
