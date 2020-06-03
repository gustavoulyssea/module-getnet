<?php

/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to https://www.fcamara.com.br/ for more information.
 *
 * @category  FCamara
 * @package   FCamara_
 * @copyright Copyright (c) 2020 FCamara Formação e Consultoria
 * @Agency    FCamara Formação e Consultoria, Inc. (http://www.fcamara.com.br)
 * @author    Danilo Cavalcanti de Moura <danilo.moura@fcamara.com.br>
 */

namespace FCamara\Getnet\Controller\Adminhtml\Seller;

use FCamara\Getnet\Model\Client;
use Magento\Backend\App\Action\Context;
use FCamara\Getnet\Model\SellerFactory;

class NewAction extends \Magento\Backend\App\Action
{
    /**
     * @var SellerFactory
     */
    protected $seller;

    /**
     * @var Client
     */
    protected $client;

    /**
     * NewAction constructor.
     * @param Context $context
     * @param SellerFactory $seller
     * @param Client $client
     */
    public function __construct(
        Context $context,
        SellerFactory $seller,
        Client $client
    ) {
        $this->seller = $seller;
        $this->client = $client;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->renderLayout();

        $data = $this->getRequest()->getParam('main_fieldset');

        if (is_array($data)) {
            $seller = $this->seller->create();
            $seller->addData(['merchant_id' => $data['seller_information']['legal_document_number']]);
            $seller->addData($data['seller_information']);
            $seller->addData(['business_address' => json_encode($data['seller_address'])]);
            $seller->addData(['mailing_address' => json_encode($data['seller_address'])]);
            $seller->addData(['working_hours' => json_encode($data['seller_working_hours'])]);
            $seller->addData(['bank_accounts' => json_encode($data['seller_bank_account'])]);

            try {
                $seller->save();

                //Integrate Getnet
                $this->client->createSellerPf($data);

                $this->messageManager->addSuccessMessage('Seller Successfully Saved!');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('Error saving the Seller, please try again!'));
            }

            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('*/*/index');
        }
    }
}