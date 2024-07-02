<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Http;
use ReflectionClass;
use App\Http\Controllers\CalculationController;

class CalculationControllerTest extends TestCase
{
    private int $failCount = 0;

    public function testRoundUp()
    {
        $controller = new \App\Http\Controllers\CalculationController();
        $method = $this->getPrivateMethod($controller, 'roundUp');

        $testCases = [
            [0.001, 0.01],
            [0.099, 0.10],
            [0.999, 1.00],
            [7.001, 7.01],
            [123.456, 123.46]
        ];

        foreach ($testCases as $case) {
            $input = $case[0];
            $expected = $case[1];
            try {
                $result = $method->invokeArgs($controller, [$input]);
                $this->assertEquals($expected, $result);
                echo "\033[32m"."testRoundUp: Passed for input $input\n"."\033[0m";
            } catch (\Exception $e) {
                echo "\033[31m"."testRoundUp: Failed for input $input - Expected $expected, got $result\n"."\033[0m";
                $this->failCount++;
                continue;
            }
        }
    }

    public function testConvertCurrency()
    {
        Http::fake([
            'https://api.freecurrencyapi.com/v1/latest' => Http::response([
                'data' => [
                    'EUR' => 1.0,
                    'USD' => 0.8697921196833957,
                    'JPY' => 0.0077202192542268,
                ]
            ], 200)
        ]);

        $controller = new \App\Http\Controllers\CalculationController();
        $method = $this->getPrivateMethod($controller, 'convertCurrency');

        $testCases = [
            [1.0, 'USD', 'EUR', 0.8697921196833957],
            [1.0, 'JPY', 'EUR', 0.0077202192542268]
        ];

        foreach ($testCases as $case) {
            $amount = $case[0];
            $fromCurrency = $case[1];
            $toCurrency = $case[2];
            $expected = $case[3];
            try {
                $result = $method->invokeArgs($controller, [$amount, $fromCurrency, $toCurrency]);
                $this->assertEquals($expected, $result);
                echo "\033[32m"."testConvertCurrency: Passed for amount $amount from $fromCurrency to $toCurrency\n"."\033[0m";
            } catch (\Exception $e) {
                echo "\033[31m" . "testConvertCurrency: Failed for amount $amount from $fromCurrency to $toCurrency - Expected $expected, got $result\n"."\033[0m";
                $this->failCount++;
                continue;
            }
        }
    }

    public function testCalculateCommission()
    {
        $controller = $this->getMockBuilder(CalculationController::class)
            ->onlyMethods(['convertCurrency', 'roundUp'])
            ->getMock();

        // Mock the convertCurrency method
        $controller->expects($this->any())
            ->method('convertCurrency')
            ->willReturnCallback(function ($amount, $fromCurrency, $toCurrency) {
                if ($fromCurrency === 'USD' && $toCurrency === 'EUR') {
                    return $amount * 0.8697921196833957;
                } elseif ($fromCurrency === 'JPY' && $toCurrency === 'EUR') {
                    return $amount * 0.0077202192542268;
                }
                return $amount;
            });

        // Mock the roundUp method
        $controller->expects($this->any())
                   ->method('roundUp')
                   ->willReturnCallback(function ($value) {
                       $mult = pow(10, 2);
                       return ceil($value * $mult) / $mult;
                   });

                   $testCases = [
                        // Deposits
                        ['private', 'deposit', 200, 'EUR', '2016-01-05', 1, 0.06, 0.06],
                        ['business', 'deposit', 10000, 'EUR', '2016-01-10', 2, 3.00, 3.00],

                        // Private Withdrawals within free limit
                        ['private', 'withdraw', 1200, 'EUR', '2014-12-31', 4, 0.60, 0.60],
                        ['private', 'withdraw', 1000, 'EUR', '2015-01-01', 4, 3.00, 3.00],
                        ['private', 'withdraw', 1000, 'EUR', '2016-01-05', 4, 0.00, 0.00],
                        ['private', 'withdraw', 30000, 'JPY', '2016-01-06', 1, 0.00, 0.00],
                        ['private', 'withdraw', 1000, 'EUR', '2016-01-07', 1, 0.70, 1.39],
                        ['private', 'withdraw', 100, 'USD', '2016-01-07', 1, 0.30, 0.28],
                        ['private', 'withdraw', 100, 'EUR', '2016-01-10', 1, 0.30, 0.30],
                        ['private', 'withdraw', 1000, 'EUR', '2016-01-10', 3, 0.00, 0.00],
                        ['private', 'withdraw', 300, 'EUR', '2016-02-15', 1, 0.00, 0.00],
                        ['private', 'withdraw', 3000000, 'JPY', '2016-02-19', 5, 8612.00, 11403.23],

                        // Business Withdrawals
                        ['business', 'withdraw', 300, 'EUR', '2016-01-06', 2, 1.50, 1.50]
                    ];

        foreach ($testCases as $case) {
            list($clientType, $transactionType, $amount, $currency, $date, $client, $expected) = $case;
            try {
                $result = $controller->calculateCommission($clientType, $transactionType, $amount, $currency, $date, $client);
                $this->assertEquals($expected, $result);
                // echo "testCalculateCommission: Passed for $clientType $transactionType of $amount $currency on $date for client $client\n";
                echo "\033[32mtestCalculateCommission: Passed for $clientType $transactionType of $amount $currency on $date for client $client\033[0m\n";
            } catch (\Exception $e) {
                // echo "testCalculateCommission: Failed for $clientType $transactionType of $amount $currency on $date for client $client - Expected $expected, got $result\n";
                echo "\033[31mtestCalculateCommission: Failed for $clientType $transactionType of $amount $currency on $date for client $client\033[0m\n";
                $this->failCount++;
                continue;
            }
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        if ($this->failCount > 0) {
            echo "Total Failed Tests: " . $this->failCount . "\n\n\n";
        }
    }

    private function getPrivateMethod($object, $methodName)
    {
        $reflection = new ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method;
    }
}
