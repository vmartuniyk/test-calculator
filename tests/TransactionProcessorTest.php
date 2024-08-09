<?php


use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class TransactionProcessorTest extends TestCase
{
    private TransactionProcessor $processor;
    private $clientMock;

    protected function setUp(): void
    {
        $this->clientMock = $this->createMock(Client::class);
        $this->processor = new TransactionProcessor($this->clientMock);
    }

    public function testParseTransaction(): void
    {
        $row = '{"bin":"45717360","amount":"100.00","currency":"EUR"}';
        $result = $this->processor->parseTransaction($row);
        $this->assertEquals([
            'bin' => '45717360',
            'amount' => '100.00',
            'currency' => 'EUR'
        ], $result);
    }



    public function testIsEuCountry(): void
    {
        $this->assertTrue($this->processor->isEuCountry('DE'));
        $this->assertFalse($this->processor->isEuCountry('US'));
    }


    public function testCalculateAmount(): void
    {
        $this->assertEquals(100.00, $this->processor->calculateAmount(100.00, 'EUR', 1));
        $this->assertEquals(50.00, $this->processor->calculateAmount(100.00, 'USD', 2));
    }

    public function testCalculateCommission(): void
    {
        $this->assertEquals(1.00, $this->processor->calculateCommission(100.00, true));
        $this->assertEquals(2.00, $this->processor->calculateCommission(100.00, false));
    }
}

