<?php if (!defined('ABSPATH')) exit('Access is Denied');

class LS_QBO_Constant
{
    const SYNC_ALL_PRODUCTS_FROM_QBO = 'Sync all products from QuickBooks';
    const SYNC_ALL_PRODUCTS_TO_QBO = 'Sync all products to QuickBooks';
    const TRIAL_PRODUCT_SYNC_LIMIT = 50;
    const TRIAL_ORDER_SYNC_LIMIT = 50;
    const QBO_SALES_RECEIPT_URL = 'https://qbo.intuit.com/app/salesreceipt?txnId=';
    const QBO_INVOICE_URL = 'https://qbo.intuit.com/app/invoice?txnId=';
}