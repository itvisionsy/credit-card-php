<?php
/**
 * Created by PhpStorm.
 * User: Muhannad Shelleh <muhannad.shelleh@live.com>
 * Date: 6/10/17
 * Time: 5:36 PM
 */

use ItvisionSy\CreditCard\CreditCard;

class CreditCardTest extends PHPUnit_Framework_TestCase
{

    public function testCardType()
    {
        $card = new CreditCard("4005550000000001", "", "1705", "");
        $this->assertEquals(CreditCard::typeToName(CreditCard::TYPE_VISA), $card->cardType());
    }

    public function testMakeStatic()
    {
    }

    public function testSettersAndGetters()
    {
    }

    public function testChecks()
    {
    }

}
