<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

function get($name){
    if (isset($_GET[$name]))
        return htmlspecialchars($_GET[$name]);
}

class Money {

    public static $amount;
    public $fromCurrency;
    public $toCurrency;
    public $currencies = [];
    public $banknotes = [
        'eur' => [500, 200, 100, 50, 20, 10, 5, 2, 1, '0.5', '0.2', '0.1', '0.05', '0.02', '0.01'],
        'usd' => [100, 50, 20, 10, 5, 1, '0.5', '0.1', '0.01'],
        'try' => [200, 100, 50, 20, 10, 5, 1, '0.5', '0.25', '0.1', '0.01']
    ];

    public static function amount($amount){
        self::$amount = $amount;
        return new self();
    }

    public function from($currency){
        $this->fromCurrency = $currency;
        return $this;
    }

    public function checkCurrencies(){
        $cache = __DIR__ . '/cache/currencies.cache';
        if (file_exists($cache) && time() - 3600 < filemtime($cache)){

            $data = file_get_contents($cache);
            $currencies = unserialize($data);

        } else {

            $ch = curl_init('https://www.tcmb.gov.tr/kurlar/today.xml');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true
            ]);
            $output = curl_exec($ch);
            curl_close($ch);
            $xml = new SimpleXMLElement($output);

            $currencies = [
                'usd' => (float) $xml->Currency[0]->ForexBuying,
                'eur' => (float) $xml->Currency[3]->ForexBuying,
            ];

            file_put_contents($cache, serialize($currencies));

        }
        $this->currencies = $currencies;
    }
    
    public function to($toCurrency){
        $this->checkCurrencies();
        $this->toCurrency = $toCurrency;
        if ($this->fromCurrency === 'try' && $toCurrency === 'eur'){
            self::$amount /= $this->currencies['eur'];
        }
        elseif ($this->fromCurrency === 'try' && $toCurrency === 'usd'){
            self::$amount /= $this->currencies['usd'];
        }
        elseif ($this->fromCurrency === 'eur' && $toCurrency === 'try'){
            self::$amount *= $this->currencies['eur'];
        }
        elseif ($this->fromCurrency === 'usd' && $toCurrency === 'try'){
            self::$amount *= $this->currencies['usd'];
        }
        return $this;
    }

    public function exchange(){
        return array_reduce($this->banknotes[$this->toCurrency], function($acc, $value){
            if (fmod($value, self::$amount) <= self::$amount){
                $total = floor(self::$amount / $value);
                if ($total){
                    self::$amount = self::$amount - ($value * $total);
                    $acc[$value] = $total;
                }
            }
            return $acc;
        });
    }

}

// $eur = Money::amount(44)->from('try')->to('eur')->exchange();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" type="text/css" href="style.css" />
</head>
<body>

<header>
    <form method="get" action="">
        <input type="text" value="<?=get('amount')?>" name="amount" placeholder="0" />
        <span>TL</span>
        <span>Kaç</span>
        <select name="to">
            <option value="eur" <?=get('to') === 'eur' ? 'selected' : null ?>>Euro</option>
            <option value="usd" <?=get('to') === 'usd' ? 'selected' : null ?>>Dolar</option>
        </select>
        <button type="submit">Göster</button>
    </form>
</header>

<main>
    <div>
        <?php
        $moneys = Money::amount(get('amount'))->from('try')->to('try')->exchange();
        foreach ($moneys as $money => $total):
        ?>
        <div class="money-group">
            <?=str_repeat('<img class="' . ($money > 2 ? 'paper' : 'coin') . ' try" src="moneys/try/' . $money . '.png">', $total)?>
        </div>
        <?php endforeach; ?>
        <table>
            <tbody>
                <?php foreach ($moneys as $money => $total): ?>
                    <tr>
                        <td><?=$money?> TRY</td>
                        <td>x<?=$total?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div>
        <?php
        $moneys = Money::amount(get('amount'))->from('try')->to(get('to'))->exchange();
        foreach ($moneys as $money => $total):
        ?>
        <div class="money-group">
            <?=str_repeat('<img class="' . ((($money > 2) || (get('to') === 'usd' && $money >= 1)) ? 'paper' : 'coin') . ' ' . get('to') . '" src="moneys/' . get('to') . '/' . $money . '.png">', $total)?>
        </div>
        <?php endforeach; ?>
        <table>
            <tbody>
                <?php foreach ($moneys as $money => $total): ?>
                    <tr>
                        <td><?=$money?> <?=get('to')?></td>
                        <td>x<?=$total?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

</body>
</html>
