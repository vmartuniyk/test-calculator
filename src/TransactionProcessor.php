<?php

require 'vendor/autoload.php';

use GuzzleHttp\Client;

class TransactionProcessor
{
    private const EU_COUNTRIES = [
        'AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 'ES', 'FI', 'FR',
        'GR', 'HR', 'HU', 'IE', 'IT', 'LT', 'LU', 'LV', 'MT', 'NL', 'PO',
        'PT', 'RO', 'SE', 'SI', 'SK'
    ];

    private Client $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    public function processFile(string $filePath): void
    {
        $rows = explode("\n", file_get_contents($filePath));
        foreach ($rows as $row) {
            if (empty($row)) {
                break;
            }
            $transaction = $this->parseTransaction($row);
            $binData = $this->fetchBinData($transaction['bin']);

            $isEu = $this->isEuCountry($binData->country->alpha2);
            $rate = $this->fetchExchangeRate($transaction['currency']);
            $amountFixed = $this->calculateAmount($transaction['amount'], $transaction['currency'], $rate);
            echo $this->calculateCommission($amountFixed, $isEu) . PHP_EOL;
        }
    }

    public function parseTransaction(string $row): array
    {
        $data = json_decode($row, true);
        return [
            'bin' => $data['bin'],
            'amount' => $data['amount'],
            'currency' => $data['currency']
        ];
    }

    public function fetchBinData(string $bin): object
    {

        $response = $this->client->get("https://lookup.binlist.net/$bin");
        sleep(10); // its for problem too many requests
        return json_decode($response->getBody()->getContents());
    }

    public function isEuCountry(string $countryCode): bool
    {
        return in_array($countryCode, self::EU_COUNTRIES);
    }

    public function fetchExchangeRate(string $currency): float
    {
        $response = $this->client->get('https://api.exchangeratesapi.io/latest');
        $rates = json_decode($response->getBody()->getContents(), true)['rates'];
        return $rates[$currency] ?? 0;
    }

    public function calculateAmount(float $amount, string $currency, float $rate): float
    {
        return $currency === 'EUR' || $rate == 0 ? $amount : $amount / $rate;
    }

    public function calculateCommission(float $amount, bool $isEu): float
    {
        $commission = $amount * ($isEu ? 0.01 : 0.02);
        return ceil($commission * 100) / 100; // Apply ceiling to cents
    }
}


