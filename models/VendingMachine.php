<?php

namespace models;

class VendingMachine
{
    const CURRENCY_POSITION_BEFORE = 0;
    const CURRENCY_POSITION_AFTER = 1;
    const VALID_CURRENCY_POSITIONS = [
        self::CURRENCY_POSITION_BEFORE,
        self::CURRENCY_POSITION_AFTER,
    ];
    const VALID_COINS = [0.05, 0.1, 0.2, 0.5, 1];

    private string $currencySign;
    private string $currencySpace;
    private int $currencyPosition;
    private array $drinks;
    /**
     * @var float Amount stored in 100x the actual value.
     */
    private float $coinsAmount = 0;

    /**
     * Constructs a Vending Machine object.
     *
     * @param array $currencySettings
     *   An array containing currency settings: sign, space, and position.
     * @param array $drinks
     *   An array containing drinks with their respective prices.
     */
    public function __construct(array $currencySettings, array $drinks)
    {
        $this->setCurrency($currencySettings);
        $this->setDrinks($drinks);
    }

    /**
     * Lists available drinks with their prices.
     *
     * @return $this
     */
    public function viewDrinks(): self
    {
        $responseMessage = '<b>Напитки:</b>';
        foreach ($this->drinks as $drink => $price) {
            $responseMessage .= '<br>' . $drink . ': ' . $this->formatPrice($price);
        }

        $this->notify($responseMessage, 'info');

        return $this;
    }

    /**
     * Adds a coin to the available sum.
     *
     * @param float $amount
     *
     * @return $this,
     */
    public function putCoin(float $amount): self
    {
        if (!in_array($amount, self::VALID_COINS)) {
            $acceptable_coins = [];
            foreach (self::VALID_COINS as $coin) {
                $acceptable_coins[] = $this->formatPrice($coin, true);
            }

            $this->notify('Автомата приема монети от: '.implode(', ', $acceptable_coins), 'error');

            return $this;
        }

        $this->coinsAmount += $amount * 100;
        $this->notify('Успешно поставихте ' . $this->formatPrice($amount, true) . ', текущата Ви сума ' . $this->formatPrice($this->coinsAmount), 'success');

        return $this;
    }

    /**
     * Buys a drink and notifies the customer of the purchase.
     *
     * @param string $name
     *
     * @return $this
     */
    public function buyDrink(string $name): self
    {
        if (empty($name) || !is_string($name)) {
            $this->notify('Името на продукта трябва да е стринг!', 'error');

            return $this;
        }

        if (!isset($this->drinks[trim($name)])) {
            $this->notify('Исканият продукт не е намерен.', 'error');

            return $this;
        }
        $price = $this->drinks[trim($name)];

        if ($price > $this->coinsAmount) {
            $this->notify('Недостатъчна наличност.', 'error');

            return $this;
        }

        $this->coinsAmount -= $price;
        $this->notify('Успешно закупихте \'' . $name . '\' от ' . $this->formatPrice($price) . ', текущата Ви сума е ' . $this->formatPrice($this->coinsAmount), 'success');

        return $this;
    }

    /**
     * Gives back the change and notifies the customer of the transaction.
     *
     * @return $this
     */
    public function getCoins(): self
    {
        if (round($this->coinsAmount, 2) == 0) {
            $this->notify('Няма ресто за връщане.', 'error');

            return $this;
        }

        $moneyBackAmount = $this->coinsAmount;
        $moneyBackCoins = [];

        foreach (array_reverse(self::VALID_COINS) as $coin) {
            $coin_quantity = floor(($this->coinsAmount) / ($coin * 100));
            if ($coin_quantity >= 1) {
                $moneyBackCoins[] = floor($coin_quantity) . 'x' . $this->formatPrice($coin, true);

                $this->coinsAmount -= ($coin * 100) * floor($coin_quantity);
            }
        }

        $this->notify('Получихте ресто ' . $this->formatPrice($moneyBackAmount) . ' в монети от: '.implode(', ', $moneyBackCoins), 'success');

        return $this;
    }

    /**
     * Displays available amount of coins.
     *
     * @return $this
     */
    public function viewAmount(): self
    {
        $coinSum = number_format($this->coinsAmount, 2, '.', '');

        $this->notify('Tекущата Ви сума е ' . $this->formatPrice($coinSum), 'info');

        return $this;
    }

    /**
     * Formats and prints a notification message.
     *
     * @param string $message
     * @param string $notificationType
     */
    private function notify(string $message, string $notificationType, bool $diesOnError = false)
    {
        $textColor = $notificationType === 'error' ? 'red' : 'black';

        echo '<p style="color: ' . $textColor . '">' . $message . '</p>';

        if ($diesOnError) {
            die;
        }
    }

    /**
     * Sets a currency based on the settings provided.
     *
     * @param array $settings
     *   Contains currency settings: sign, space, and position.
     */
    private function setCurrency(array $settings)
    {
        if (empty(trim($settings['sign'])) || !is_string($settings['sign'])) {
            $this->notify('Първият параметър на класа VendingMachine трябва да съдържа параметър "sign" (стринг)!', 'error', true);
        }
        $this->currencySign = trim($settings['sign']);

        if (!isset($settings['space']) || !is_string($settings['space'])) {
            $this->notify('Първият параметър на класа VendingMachine трябва да съдържа параметър "space" (стринг)!', 'error', true);
        }
        $this->currencySpace = $settings['space'];

        if (empty($settings['position']) || !is_int($settings['position']) || !in_array($settings['position'], self::VALID_CURRENCY_POSITIONS)) {
            $this->notify('Първият параметър на класа VendingMachine трябва да съдържа параметър "position" (цяло число: 0 или 1)!', 'error', true);
        }
        $this->currencyPosition = $settings['position'];
    }

    /**
     * Sets an array of drinks.
     *
     * @param array $drinks
     *   Contains an array of key-value pairs: drink name (string) => drink price (float).
     */
    private function setDrinks(array $drinks)
    {
        if (empty($drinks)) {
            $this->notify('Вторият параметър на класа VendingMachine трябва да съдържа поне една напитка!', 'error', true);
        }
        $this->drinks = [];

        foreach ($drinks as $drink => $price) {
            if (empty(trim($drink)) || !is_string($drink) || empty($price) || !(is_float($price) || is_int($price))) {
                $this->notify('Има грешка в списък с напитки! Напитките трябва да следват модел: string => float.', 'error', true);
            }

            if ($price <= 0) {
                $this->notify('Цената на напитката трябва да е положителна!', 'error', true);
            }

            if (round($price * 100) % 5 !== 0) {
                $this->notify('Цената на една напитка трябва да бъде на стъпка от 0.05лв!', 'error', true);
            }

            $this->drinks[trim($drink)] = round($price, 2) * 100;
        }
    }

    /**
     * Formats a price based on the currency settings.
     *
     * @param float $amount
     *
     * @return string
     *   Amount with the currency sign.
     */
    private function formatPrice(float $amount, bool $actualValue = false): string
    {
        if ($this->currencyPosition === self::CURRENCY_POSITION_BEFORE) {
            return $this->currencySign.$this->currencySpace.number_format($actualValue ? $amount : $amount / 100, 2, '.', '');
        }

        return number_format($actualValue ? $amount : $amount / 100, 2, '.', '').$this->currencySpace.$this->currencySign;
    }

}
