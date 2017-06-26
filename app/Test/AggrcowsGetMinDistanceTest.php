<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Aggrcows\Aggrcows;

/**
 * @covers Aggrcows
 */
final class AggrcowsGetMinDistanceTest extends TestCase
{
    public function testGetMinDistance(): void
    {
    	$testcases = [
    		[
    			'positions' => [1, 2, 9, 8, 4, 4, 8, 9, 2, 1,],
				'c' => 3,
				'distance' => 3
			],
			[
    			'positions' => [1, 2, 8, 4, 9],
				'c' => 3,
				'distance' => 3
			],
			[
    			'positions' => [
    				9, 8, 7, 10, 6, 5, 4, 3, 2, 1, 19, 18, 17,
    				16, 15, 14, 13, 12, 11, 20,
    			],
				'c' => 3,
				'distance' => 9
			],
			[
    			'positions' => [0, 1000000000, 500000000,],
				'c' => 3,
				'distance' => 500000000
			],
			[
    			'positions' => [
    				9, 8, 7, 10, 6, 5, 4, 3, 2, 1, 19, 18, 17,
    				16, 15, 14, 13, 12, 11, 20,
    			],
				'c' => 4,
				'distance' => 6
			],
			[
    			'positions' => [
    				9, 8, 7, 10, 6, 5, 4, 3, 2, 1, 19, 18, 17,
    				16, 15, 14, 13, 12, 11, 20,
    			],
				'c' => 5,
				'distance' => 4
			],
			[
    			'positions' => [5, 10],
				'c' => 2,
				'distance' => 5
			],
		];

		foreach ($testcases as $testcase) {
			extract($testcase);
			$min = Aggrcows::getMinDistance($positions, $c);
			$this->assertEquals($min, $distance);
		}
    }
}
