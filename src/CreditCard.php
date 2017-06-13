<?php

namespace ItvisionSy\CreditCard;

/*
 * Acknowledgement: this class is based on the following class:
 * Class: CreditCard Class (http://www.phpclasses.org/package/441-PHP-Validate-credit-cards-and-detect-the-type-of-card.html)
 * Author: Daniel Froz Costa (http://www.phpclasses.org/browse/author/41459.html)
 *
 * Documentation:
 *
 * Card Type                   Prefix           Length     Check digit
 * - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
 * MasterCard                  51-55            16         mod 10
 * Visa                        4                13, 16     mod 10
 * AMEX                        34, 37           15         mod 10
 * Dinners Club/Carte Blanche  300-305, 36, 38  14         mod 10
 * Discover                    6011             16         mod 10
 * enRoute                     2014, 2149       15         any
 * JCB                         3                16         mod 10
 * JCB                         2131, 1800       15         mod 10
 *
 * More references:
 * http://www.beachnet.com/~hstiles/cardtype.hthml
 *
 * $Id: creditcard_class.php,v 1.1 2002/02/16 16:02:12 daniel Exp $
 *
 */
/**
 * Class CreditCard
 * @package ItvisionSy\CreditCard
 * @author Daniel Froz Costa <dfroz@users.sourceforge.net>
 * @author Muhannad Shelleh <muhannad@shelleh.me>
 */
class CreditCard
{

    const TYPE_UNKNOWN = 0;
    const TYPE_MASTERCARD = 1;
    const TYPE_VISA = 2;
    const TYPE_AMEX = 3;
    const TYPE_DINNERS = 4;
    const TYPE_DISCOVER = 5;
    const TYPE_ENROUTE = 6;
    const TYPE_JCB = 7;
    const ERROR_OK = 0;
    const ERROR_ECALL = 1;
    const ERROR_EARG = 2;
    const ERROR_ETYPE = 3;
    const ERROR_ENUMBER = 4;
    const ERROR_EFORMAT = 5;
    const ERROR_ECANTYPE = 6;
    const ERROR_EXPIRY_INVALID = 7;

    protected $cardNumber;
    protected $cardHolderName;
    protected $cardExpiryDate;
    protected $cardCVV2;
    protected $type;
    protected $cardNumberErrorNumber;
    protected $cardExpiryErrorNumber;

    /**
     * @param string|number $cardNumber
     * @param string $cardHolderName
     * @param string $cardExpiryDate in one of the formats: YY/MM, YY-MM, YYMM, YYYYMM, YYYY/MM, YYYY-MM
     * @param string $cardCVV2
     * @return CreditCard|static|$this
     */
    public static function make($cardNumber, $cardHolderName, $cardExpiryDate, $cardCVV2)
    {
        return new static($cardNumber, $cardHolderName, $cardExpiryDate, $cardCVV2);
    }

    /**
     * CreditCard constructor.
     * @param string|number $cardNumber
     * @param string $cardHolderName
     * @param string $cardExpiryDate in one of the formats: YY/MM, YYMM, YYYYMM, YYYY/MM
     * @param string $cardCVV2
     */
    public function __construct($cardNumber, $cardHolderName, $cardExpiryDate, $cardCVV2)
    {
        $this->set($cardNumber, $cardHolderName, $cardExpiryDate, $cardCVV2);
    }

    /**
     * @param string|number $cardNumber
     * @param string $cardHolderName
     * @param string $cardExpiryDate in one of the formats: YY/MM, YYMM, YYYYMM, YYYY/MM
     * @param string $cardCVV2
     * @return $this;
     */
    public function set($cardNumber, $cardHolderName, $cardExpiryDate, $cardCVV2)
    {
        $this->setCardNumber((string)$cardNumber);
        $this->setCardHolderName($cardHolderName);
        $this->setCardCVV2($cardCVV2);
        $this->setCardExpiryDate($cardExpiryDate);
        return $this;
    }

    /**
     * @return bool|string
     */
    public function getCardNumber()
    {
        if (!$this->cardNumber) {
            $this->cardNumberErrorNumber = static::ERROR_ECALL;
            return false;
        }

        return $this->cardNumber;
    }

    /**
     * @param string $cardNumber
     * @return CreditCard
     */
    public function setCardNumber($cardNumber)
    {

        $this->cardNumber = (string)$cardNumber;
        return $this;
    }

    /**
     * @return string YYMM
     */
    public function getCardExpiryDate()
    {
        return $this->cardExpiryDate;
    }

    /**
     * @param string $cardExpiryDate
     * @return CreditCard
     */
    public function setCardExpiryDate($cardExpiryDate)
    {
        $raw = preg_replace('#[^0-9]#', '', $cardExpiryDate);
        $len = strlen($raw);
        switch ($len) {
            case 4:
                $year = substr($raw, 0, 2);
                $month = substr($raw, 2, 2);
                break;
            case 6:
                $year = substr($raw, 2, 2);
                $month = substr($raw, 4, 2);
                break;
            default:
                $year = null;
                $month = null;
        }
        if (!$year || !$month) {
            $this->cardExpiryErrorNumber = static::ERROR_EXPIRY_INVALID;
            $this->cardExpiryDate = null;
        } else {
            $this->cardExpiryErrorNumber = static::ERROR_OK;
            $this->cardExpiryDate = $year . $month;
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getCardCVV2()
    {
        return $this->cardCVV2;
    }

    /**
     * @param string $cardCVV2
     * @return CreditCard
     */
    public function setCardCVV2($cardCVV2)
    {
        $this->cardCVV2 = (string)$cardCVV2;
        return $this;
    }

    /**
     * @return string
     */
    public function getCardHolderName()
    {
        return $this->cardHolderName;
    }

    /**
     * @param string $cardHolderName in one of the formats: YY/MM, YYMM, YYYYMM, YYYY/MM
     * @return CreditCard
     */
    public function setCardHolderName($cardHolderName)
    {
        $this->cardHolderName = (string)$cardHolderName;
        return $this;
    }

    /**
     * @return null|string
     */
    public function cardType()
    {
        if (!$this->cardNumber) {
            if (!$this->type) {
                $this->cardNumberErrorNumber = static::ERROR_EARG;
            }
        } else {
            $this->type = static::detectType($this->cardNumber);
        }

        if (!$this->type) {
            $this->cardNumberErrorNumber = static::ERROR_ETYPE;
        }

        $name = static::typeToName($this->type);
        if ($name === false) {
            $this->cardNumberErrorNumber = static::ERROR_ECANTYPE;
        }
        return $name;
    }

    /**
     * @return int
     */
    public function errno()
    {
        return $this->cardNumberErrorNumber ?: $this->cardExpiryErrorNumber;
    }

    /**
     * @return string
     */
    public function error()
    {
        switch ($this->cardNumberErrorNumber) {
            case static::ERROR_ECALL:
                return "Invalid call for this method";
            case static::ERROR_ETYPE:
                return "Invalid card type";
            case static::ERROR_ENUMBER:
                return "Invalid card number";
            case static::ERROR_EFORMAT:
                return "Invalid format";
            case static::ERROR_ECANTYPE:
                return "Cannot detect the type of your card";
            case static::ERROR_OK:
            default:
        }
        switch ($this->cardExpiryErrorNumber) {
            case static::ERROR_EXPIRY_INVALID:
                return "Invalid expiry date";
        }
        return null;
    }

    /**
     * @return bool
     */
    public function check()
    {
        $this->type = static::TYPE_UNKNOWN;
        $this->cardNumberErrorNumber = static::ERROR_OK;
        if (!$this->detectType($this->cardNumber)) {
            $this->cardNumberErrorNumber = static::ERROR_ETYPE;
            return false;
        }
        if ($this->mod10($this->cardNumber)) {
            $this->cardNumberErrorNumber = static::ERROR_ENUMBER;
            return false;
        }
        return true;
    }

    protected function mod10()
    {
        for ($sum = 0, $i = strlen($this->cardNumber) - 1; $i >= 0; $i--) {
            $sum += $this->cardNumber[$i];
            $doubdigit = "" . (2 * $this->cardNumber[--$i]);
            for ($j = strlen($doubdigit) - 1; $j >= 0; $j--) {
                $sum += $doubdigit[$j];
            }
        }
        return $sum % 10;
    }

    public static function typeToName($type)
    {
        if (!$type) {
            return null;
        }
        switch ($type) {
            case static::TYPE_MASTERCARD:
                return "MASTERCARD";
            case static::TYPE_VISA:
                return "VISA";
            case static::TYPE_AMEX:
                return "AMEX";
            case static::TYPE_DINNERS:
                return "DINNERS";
            case static::TYPE_DISCOVER:
                return "DISCOVER";
            case static::TYPE_ENROUTE:
                return "ENROUTE";
            case static::TYPE_JCB:
                return "JCB";
            default:
                return false;
        }
    }

    /**
     * @param string $cardNumber
     * @param bool $returnName return name or number
     * @return int
     */
    public static function detectType($cardNumber, $returnName = false)
    {
        if ($returnName) {
            return self::typeToName(self::detectType($cardNumber, false));
        }
        if (preg_match("/^5[1-5]\d{14}$/", $cardNumber)) {
            return static::TYPE_MASTERCARD;
        } elseif (preg_match("/^4(\d{12}|\d{15})$/", $cardNumber)) {
            return static::TYPE_VISA;
        } elseif (preg_match("/^3[47]\d{13}$/", $cardNumber)) {
            return static::TYPE_AMEX;
        } else if (preg_match("/^[300-305]\d{11}$/", $cardNumber) || preg_match("/^3[68]\d{12}$/", $cardNumber)) {
            return static::TYPE_DINNERS;
        } elseif (preg_match("/^6011\d{12}$/", $cardNumber)) {
            return static::TYPE_DISCOVER;
        } elseif (preg_match("/^2(014|149)\d{11}$/", $cardNumber)) {
            return static::TYPE_ENROUTE;
        } elseif (preg_match("/^3\d{15}$/", $cardNumber) || preg_match("/^(2131|1800)\d{11}$/", $cardNumber)) {
            return static::TYPE_JCB;
        }
        return static::TYPE_UNKNOWN;
    }

}
