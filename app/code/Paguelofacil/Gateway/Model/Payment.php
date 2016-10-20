<?php
/**
 * @copyright   Paguelofacil (http://www.paguelofacil.com)
 */

namespace Paguelofacil\Gateway\Model;
use Magento\Payment\Gateway\Response\HandlerInterface;

class Payment extends \Magento\Payment\Model\Method\Cc
{
    const CODE = 'paguelofacil_gateway';

    protected $_code = self::CODE;

    protected $_isGateway                   = true;
    protected $_canCapture                  = true;
    protected $_canCapturePartial           = false;
    protected $_canRefund                   = false;
    protected $_canRefundInvoicePartial     = false;

    protected $_countryFactory;
	
    protected $_cclw = null;
    protected $_sandbox = null;

    protected $_supportedCurrencyCodes = array('USD');

    protected $_debugReplacePrivateDataKeys = ['number', 'exp_month', 'exp_year', 'cvc'];

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        array $some = array(),
        array $data = array()
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $moduleList,
            $localeDate,
            null,
            null,
            $data
        );
		$this->_cclw = $this->getConfigData('cclw');
		$this->_sandbox = $this->getConfigData('sandbox');
    }



    //right now this function has sample code only, you need put code here as per your api.
    private function callApi($apidata, $type){

        $requestBody = http_build_query($apidata);
        $sandbox = ($this->_sandbox) ? 'qa':'api';
	
		$prefix = ($this->_sandbox==true)?'dev':'secure';
		$curlURL = "https://".$prefix.".paguelofacil.com/rest/ccprocessing/";
		
        $this->debugData(['request' => $curlURL]);

        $ch = curl_init($curlURL);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $requestBody);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded','Accept: */*'));
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSL_CIPHER_LIST, 'TLSv1.2');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        $arresult = json_decode($response,true);
		
        curl_close($ch);

        return $arresult;
    }



    /**
     * Payment capturing
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param float $amount
     * @return $this
     * @throws \Magento\Framework\Validator\Exception
     */
    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $order = $payment->getOrder();
        $billing = $order->getBillingAddress();

		$email = $order->getCustomerEmail();
        $ccv = $payment->getCcCid();
        $ccnumber = $payment->getCcNumber();
		$ccowner = $payment->getCcOwner();
		
		#Credit card Owner
        $ccowner = explode(" ",$ccowner,2);
        $name = isset($ccowner[0])?$ccowner[0]:'';
        $lastname = isset($ccowner[1])?$ccowner[1]:' ';
        
		
		$nombre = $payment->getCcOwner();
		

        $secred = $ccnumber.$ccv.$email;
		
		$apidata = array(
            "CCLW" => $this->_cclw,
            "txType" => 'SALE',
            "CMTN" => '1.00',
            "CDSC" => 'MG-Order-'.$order->getIncrementId(),
            "CCNum" => $ccnumber,
            "ExpMonth" => ($payment->getCcExpMonth()<10)?'0'.$payment->getCcExpMonth():$payment->getCcExpMonth(),
            "ExpYear" => $payment->getCcExpYear(),
            "CVV2" => $ccv,
            "Name" => $name,
            "LastName" => $lastname,
            "Email" => $email,
            "Address" => $billing->getStreetLine(1),
            "Tel" => $billing->getTelephone(),
            "SecretHash" => hash('sha512', $secred),
        );

        try {
            $callapi = $this->callApi($apidata,'sale');
			if(!array_key_exists('Status',$callapi))
			{
				if(array_key_exists('error',$callapi))
				{
				header("HTTP/1.0 404 Not Found");
				header("Content-Type: application/json");
				echo '{"message":"'.$callapi['error'].'","trace":"#"}';
				die();
				}
				else{
				header("HTTP/1.0 404 Not Found");
				header("Content-Type: application/json");
				echo '{"message":"Se ha producido un error en la conexion","trace":"#"}';
				die();
				}
			}
			
            if($callapi["Status"] == "Approved") {

                $payment->setTransactionId($callapi['CODOPER']);
                $payment->setIsTransactionClosed(1);

            } else {
                $this->_logger->error(__($callapi['CODOPER']));
				header("HTTP/1.0 404 Not Found");
				header("Content-Type: application/json");
				echo '{"message":"'.__( $callapi['RespText'] ).'","trace":"#"}';
				die();
            }
        } catch (\Exception $e) {
			header("HTTP/1.0 404 Not Found");
			header("Content-Type: application/json");
			echo '{"message":"'.__( $e->getMessage() ).'","trace":"#"}';
     		die();
        }

        return $this;
    }


    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        if (!$this->getConfigData('cclw')) {
            return false;
        }

        return parent::isAvailable($quote);
    }

    public function canUseForCurrency($currencyCode)
    {
        if (!in_array($currencyCode, $this->_supportedCurrencyCodes)) {
            return false;
        }
        return true;
    }
	
	public function assignData(\Magento\Framework\DataObject $data)
    {
        if (!$data instanceof \Magento\Framework\DataObject) {
            $data = new \Magento\Framework\DataObject($data);
        }
        $info = $this->getInfoInstance();

		
        $info->setCcType(
            $data->getData()['additional_data']['cc_type']
        )->setCcOwner(
            $data->getData()['additional_data']['cc_ownerx']
        )->setCcLast4(
            substr($data->getData()['additional_data']['cc_number'], -4)
        )->setCcNumber(
            $data->getData()['additional_data']['cc_number']
        )->setCcCid(
            $data->getData()['additional_data']['cc_cid']
        )->setCcExpMonth(
            $data->getData()['additional_data']['cc_exp_month']
        )->setCcExpYear(
            $data->getData()['additional_data']['cc_exp_year']
        )->setCcSsIssue(
            $data->getData()['additional_data']['cc_ss_issue']
        )->setCcSsStartMonth(
            $data->getData()['additional_data']['cc_ss_start_month']
        )->setCcSsStartYear(
            $data->getData()['additional_data']['cc_ss_start_year']
        );
        return $this;
    }
}