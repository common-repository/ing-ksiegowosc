<?php

namespace Ingk;

/**
 * Class Invoice
 *
 * @package Ingk
 */
class Invoice
{

	/**
	 * @const string
	 */
	const SHOP_TAX_EXEMPT = 'zw';

	/**
	 * @const        string
	 * @noinspection PhpUnused
	 */
	const TAX_23 = 'TAX_23';

	/**
	 * @const        string
	 * @noinspection PhpUnused
	 */
	const TAX_22 = 'TAX_22';

	/**
	 * @const        string
	 * @noinspection PhpUnused
	 */
	const TAX_8 = 'TAX_8';

	/**
	 * @const        string
	 * @noinspection PhpUnused
	 */
	const TAX_7 = 'TAX_7';

	/**
	 * @const        string
	 * @noinspection PhpUnused
	 */
	const TAX_5 = 'TAX_5';

	/**
	 * @const        string
	 * @noinspection PhpUnused
	 */
	const TAX_3 = 'TAX_3';

	const TAX_0 = 'TAX_0';

	/**
	 * @const string
	 */
	const TAX_EXEMPT = 'TAX_EXEMPT';

	/**
	 * @const string
	 */
	const TAX_NOT_LIABLE = 'TAX_NOT_LIABLE';

	/**
	 * @const string
	 */
	const TAX_REVERSE_CHARGE = 'TAX_REVERSE_CHARGE';

	/**
	 * @const string
	 */
	const TAX_NOT_EXLUCDING = 'TAX_NOT_EXCLUDING';

	/**
	 * @const string
	 */
	const BUYER_PERSON = 'TYPE_PERSON';
	/**
	 * @const string
	 */
	const BUYER_COMPANY = 'TYPE_COMPANY';

	/**
	 * @const string
	 */
	const ID_TYPE_VAT = 'VAT_ID';

	/**
	 * @const string
	 */
	const P_METHOD = 'OTHER';

	/**
	 * @const string
	 */
	const S_WOOCOMMERCE = 'WOOCOMMERCE';

	/**
	 * @const string
	 */
	const S_PRESTASHOP = 'PRESTASHOP';

	/**
	 * @var array
	 */
	protected $buyer;

	/**
	 * @var array
	 */
	protected $positions;

	/**
	 * @var array
	 */
	protected $payment;

	/**
	 * @var array
	 */
	protected $currency;

	/**
	 * @var string
	 */
	protected $orderId;

	/**
	 * @var string
	 */
	protected $source;

	/**
	 * @var string[]
	 */
	private static $basisExempt = [
		'DENTAL_TECHNICAN_SERVICES'        => 'DENTAL_TECHNICAN_SERVICES',
		'DOCTOR_DENTIST_SERVICES'          => 'DOCTOR_DENTIST_SERVICES',
		'PHYSIOTHERAPY_SERVICES'           => 'PHYSIOTHERAPY_SERVICES',
		'NURSING_SERVICES'                 => 'NURSING_SERVICES',
		'PSYCHOLOGICAL_SERVICES'           => 'PSYCHOLOGICAL_SERVICES',
		'MEDICAL_TRANSPORT_SERVICES'       => 'MEDICAL_TRANSPORT_SERVICES',
		'CARE_SERVICES'                    => 'CARE_SERVICES',
		'TUTORING'                         => 'TUTORING',
		'TEACHING_FOREIGN_LANGUAGES'       => 'TEACHING_FOREIGN_LANGUAGES',
		'ARTISTS'                          => 'ARTISTS',
		'RENTING_PROPERTY'                 => 'RENTING_PROPERTY',
		'INSURANCE_SERVICES'               => 'INSURANCE_SERVICES',
		'CREDITS_AND_LOANS_SERVICES'       => 'CREDITS_AND_LOANS_SERVICES',
		'GUARANTIEES'                      => 'GUARANTIEES',
		'SPECIAL_CONDITIONS_FOR_EXEMPTION' => 'SPECIAL_CONDITIONS_FOR_EXEMPTION',
		'UE_TRANSACTIONS'                  => 'UE_TRANSACTIONS',
		'SUBJECTIVE_EXEMPTIONS'            => 'SUBJECTIVE_EXEMPTIONS',
		'OTHER'                            => 'OTHER',
		'OTHER_OBJECTIVE_EXEMPTIONS'       => 'OTHER_OBJECTIVE_EXEMPTIONS',
	];

	/**
	 * @param float  $paidAmount
	 * @param string $currency
	 */
	public function __construct($paidAmount, $currency, $generatedOrderId, $source)
	{

		$this->source = $source;
		$this->payment = [
			'method'     => self::P_METHOD,
			'paidAmount' => (float) $paidAmount,
		];
		$this->currency = [
			'code' => $currency,
		];
		$this->orderId = $generatedOrderId;
	}

	/**
	 * @param string $basis
	 *
	 * @return string
	 */
	public static function getBasisExempt($basis)
	{

		if(isset(self::$basisExempt[$basis])) {
			return self::$basisExempt[$basis];
		}

		return '';
	}

	/**
	 * @param string|int $id
	 * @param string     $name
	 * @param float      $amountGross
	 * @param int        $quantity
	 * @param string     $taxStake
	 * @param bool       $isUnitPrice
	 * @param number     $discountAmount
	 *
	 * @return void
	 */
	public function addItem($id, $name, $amountGross, $quantity, $taxStake, $isUnitPrice, $discountAmount = 0)
	{

		if($isUnitPrice) {

			$this->prepareItem(
				$name,
				$id,
				(int) $quantity,
				$taxStake,
				$amountGross,
				$discountAmount
			);

			return;
		}

		$amountGross = round($amountGross * 100);

		$amountCalc = floor($amountGross / $quantity);

		if((float) $amountGross !== ($amountCalc * $quantity)) {

			$quantityCalc = $amountGross % $quantity;

			$quantity = $quantity - $quantityCalc;

			$this->prepareItem(
				$name,
				$id,
				$quantityCalc,
				$taxStake,
				($amountCalc + 1) / 100,
				$discountAmount
			);
		}

		$this->prepareItem(
			$name,
			$id,
			(int) $quantity,
			$taxStake,
			$amountCalc / 100,
			$discountAmount
		);
	}

	/**
	 * @param string $name
	 * @param string $sku
	 * @param int    $quantity
	 * @param float  $taxStake
	 * @param float  $amountGross
	 * @param int    $discountAmount
	 *
	 * @return void
	 */
	public function prepareItem($name, $sku, $quantity, $taxStake, $amountGross, $discountAmount = 0)
	{

		$position = [
			'name'     => $name,
			'sku'      => (string) $sku,
			'quantity' => $quantity,
			'unit'     => 'sztuki',
			'gross'    => $amountGross,
			'taxStake' => $taxStake,
		];

		if($discountAmount) {
			$position['discountAmount'] = (float) $discountAmount;
		}

		$this->positions[] = $position;
	}

	/**
	 * @param string $type
	 * @param string $email
	 * @param string $fullName
	 * @param string $street
	 * @param string $city
	 * @param string $postCode
	 * @param string $countryCode
	 * @param string $taxCountryCode
	 * @param string $taxNumber
	 *
	 * @return Invoice
	 */
	public function setBuyer($type, $email, $fullName, $street, $city, $postCode, $countryCode, $taxCountryCode = '', $taxNumber = '')
	{

		$array = [
			'email'         => $email,
			'fullName'      => $fullName,
			'addressStreet' => $street,
			'city'          => $city,
			'postCode'      => $postCode,
			'countryCode'   => $countryCode,
			'type'          => $type,
		];

		if($taxCountryCode) {
			$array['taxCountryCode'] = $taxCountryCode;
		}

		if($taxNumber) {
			$array['taxNumber'] = $taxNumber;
		}

		$this->buyer = $array;

		return $this;
	}

	/**
	 * @return string
	 */
	private function prepare()
	{

		return json_encode([
			'issuedGross'    => true,
			'payment'        => $this->payment,
			'buyer'          => $this->buyer,
			'positions'      => $this->positions,
			'orderId'        => $this->orderId,
			'currency'       => $this->currency,
			'sourcePaymento' => $this->source,
		], JSON_UNESCAPED_UNICODE);
	}

	/**
	 * @return string
	 */
	public function get()
	{

		return $this->prepare();
	}
}
