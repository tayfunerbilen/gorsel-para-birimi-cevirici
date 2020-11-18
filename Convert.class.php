<?php

namespace Prototurk;

class Convert {

    public static $amount;
    public static $amount_converted;
    public $toCurrency;
    public $cacheTime = 3600; // 1 saat
    public $banknotes = [
        'eur' => [500, 200, 100, 50, 20, 10, 5, 2, 1, '0.5', '0.2', '0.1', '0.05', '0.02', '0.01'],
        'usd' => [100, 50, 20, 10, 5, 1, '0.5', '0.25', '0.1', '0.05', '0.01'],
        'try' => [200, 100, 50, 20, 10, 5, 1, '0.5', '0.25', '0.1', '0.05', '0.01']
    ];
    public $currencies = [
        'eur' => 9.20185,
        'usd' => 7.74947,
        'try' => 1
    ];

    public static function amount($amount)
    {
        self::$amount = $amount;
        return new self();
    }

    /**
     * Currency cache
     */
    public function checkCurrencies(){
        $cache = __DIR__ . '/cache/currencies.cache';
        if (file_exists($cache) && time() - $this->cacheTime < filemtime($cache)){

            $data = file_get_contents($cache);
            $currencies = unserialize($data);

        } else {

            $ch = curl_init('https://www.tcmb.gov.tr/kurlar/today.xml');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true
            ]);
            $output = curl_exec($ch);
            curl_close($ch);
            $xml = new \SimpleXMLElement($output);

            $currencies = [
                'usd' => (float) $xml->Currency[0]->ForexBuying,
                'eur' => (float) $xml->Currency[3]->ForexBuying,
                'try' => 1
            ];

            file_put_contents($cache, serialize($currencies));

        }
        $this->currencies = $currencies;
    }

    public function to($toCurrency)
    {
        $this->toCurrency = $toCurrency;
        $this->checkCurrencies(); // cache
        self::$amount_converted = self::$amount = self::$amount / $this->currencies[$toCurrency];
        return $this->calculate();
    }

    public function calculate(){
        return array_reduce($this->banknotes[$this->toCurrency], function($acc, $value){
            if (fmod(self::$amount, $value) < self::$amount){
                $total = floor(self::$amount / $value);
                $acc[$value] = $total;
                self::$amount -= $value * $total;
            }
            return $acc;
        });
    }

    public static function getConvertedAmount()
    {
        $amount = explode('.', self::$amount_converted);
        return $amount[0] . (isset($amount[1]) ? '.' . substr($amount[1], 0, 2) : null);
    }

    public static function getCurrencySymbol($currency)
    {
        $symbols = [
            'eur' => '€',
            'usd' => '$',
            'try' => '₺'
        ];
        return $symbols[$currency];
    }
    
}