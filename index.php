<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

require __DIR__ . '/Convert.class.php';

use Prototurk\Convert as Convert;

function get($name){
    if (isset($_GET[$name]))
        return htmlspecialchars(trim($_GET[$name]));
}

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

    <form action="" method="get">
        <input type="text" name="amount" value="<?=get('amount')?>" placeholder="0" />
        <span>TL</span>
        <span>Kaç</span>
        <select name="to">
            <option value="eur" <?=get('to') === 'eur' ? 'selected' : null ?>>Euro</option>
            <option value="usd" <?=get('to') === 'usd' ? 'selected' : null ?>>Dolar</option>
        </select>
        <button type="submit">Göster</button>
    </form>

    <div class="actions">
        <a href="?amount=50&to=usd">Ben hep 50 liralık alırım butonu</a>
        <a href="?amount=2324&to=eur">2020 Asgari ücret butonu</a>
        <a href="?amount=44&to=eur">44 tl hesap butonu</a>
        <a class="usd-btn" href="https://www.youtube.com/watch?v=uq2DxZWO0UI" target="_blank">Doları düşürme butonu</a>
    </div>

    <?php if (get('amount') && get('to')): ?>
    <main>

        <div>
            <?php
            $moneys = Convert::amount(get('amount'))->to('try');
            foreach ($moneys as $money => $total):
            ?>
            <div class="money-group">
                <?=str_repeat('<img class="' . ($money > 2 ? 'paper' : 'coin') . '" src="moneys/try/' . $money . '.png" />', $total)?>
            </div>
            <?php endforeach; ?>
            <table>
                <thead>
                    <tr>
                        <th>Miktar</th>
                        <th>Toplam</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($moneys as $money => $total): ?>
                        <tr>
                            <td>
                                <?=$money?>
                                <?=Convert::getCurrencySymbol('try')?>
                            </td>
                            <td>
                                x<?=$total?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2">
                            <b>Toplam : </b>
                            <?=Convert::getConvertedAmount() ?>
                            <?=Convert::getCurrencySymbol('try')?>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div>
            <?php
            $moneys = Convert::amount(get('amount'))->to(get('to'));
            foreach ($moneys as $money => $total):
            ?>
            <div class="money-group">
                <?=str_repeat('<img class="' . ((($money > 2) || (get('to') === 'usd' && $money >= 1)) ? 'paper' : 'coin') . ' ' . get('to') . '" src="moneys/' . get('to') . '/' . $money . '.png" />', $total)?>
            </div>
            <?php endforeach; ?>
            <table>
                <thead>
                    <tr>
                        <th>Miktar</th>
                        <th>Toplam</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($moneys as $money => $total): ?>
                        <tr>
                            <td>
                                <?=$money?>
                                <?=Convert::getCurrencySymbol(get('to'))?>
                            </td>
                            <td>
                                x<?=$total?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2">
                            <b>Toplam : </b>
                            <?=Convert::getConvertedAmount() ?>
                            <?=Convert::getCurrencySymbol(get('to'))?>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

    </main>
    <?php endif; ?>
    
</body>
</html>