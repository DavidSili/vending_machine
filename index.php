<?php

require_once('models/VendingMachine.php');

use models\VendingMachine;

$machine = new VendingMachine(
    [
        'sign' => 'лв.',
        'space' => '',
        'position' => VendingMachine::CURRENCY_POSITION_AFTER,
    ],
    [
        'Milk' => 0.5,
        'Espresso' => 0.40,
        'Long Espresso' => 0.60,
    ]
);

$machine
    ->buyDrink( 'espresso' )
    ->buyDrink( 'Espresso' )
    ->viewDrinks()
    ->putCoin( 2 )
    ->putCoin( 1 )
    ->buyDrink( 'Espresso' )
    ->getCoins()
    ->viewAmount()
    ->getCoins();
