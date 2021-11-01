<?php
/*
*  @author SEIGI Grzegorz Zawadzki <kontakt@seigi.eu>
*  @copyright  2021 SEIGI Grzegorz Zawadzki
*  @license    https://creativecommons.org/licenses/by-sa/3.0/pl/ Uznanie autorstwa-Na tych samych warunkach 3.0 Polska (CC BY-SA 3.0 PL)
*/

if (!defined('_PS_VERSION_'))
	exit;

class seigifreeremovevat extends Module
{
	public $_html;
	public function __construct()
	{
		$this->name = 'seigifreeremovevat';
		$this->tab = 'payments_gateways';
		$this->version = '1.0.0';
		$this->author = 'SEIGI Grzegorz Zawadzki';
		$this->is_eu_compatible = 1;

		$this->bootstrap = true;
		parent::__construct();

		$this->displayName = $this->l('Remove VAT from Order');
		$this->description = $this->l('Allows to remove VAT from order manually.');
		$this->ps_versions_compliancy = array('min' => '1.6', 'max' => '1.7.99.99');

	}

    public function removVatFromOrder($id_order) {
        $order = new Order($id_order);
        if(!$order->id)
            return false;
        $db = Db::getInstance();
        $id_tax = (int) $db->getValue('SELECT `id_tax` FROM `'._DB_PREFIX_.'tax` WHERE `rate` = 0 AND `active` = 1 AND `deleted` = 0');
        if(!$id_tax)
            return false;
        try {
            $db->execute('START TRANSACTION');

            $result = 1;

            $result &= $db->execute('UPDATE `'._DB_PREFIX_.'orders` SET
                `total_discounts` =`total_discounts_tax_excl`,
                `total_discounts_tax_incl` =`total_discounts_tax_excl`,
                `total_shipping` =`total_shipping_tax_excl`,
                `total_shipping_tax_incl` =`total_shipping_tax_excl`,
                `total_wrapping` =`total_wrapping_tax_excl`,
                `total_wrapping_tax_incl` =`total_wrapping_tax_excl`,
                `total_products_wt` =`total_products`,
                `total_paid` =`total_paid_tax_excl`,
                `total_paid_tax_incl` =`total_paid_tax_excl`,
                `carrier_tax_rate` = 0
                WHERE id_order = '. $order->id);

            $result &= $db->execute('UPDATE `'._DB_PREFIX_.'order_carrier` SET
                `shipping_cost_tax_incl` =`shipping_cost_tax_excl`
                WHERE id_order = '. $order->id);

            $result &= $db->execute('UPDATE `'._DB_PREFIX_.'order_detail` SET
                `reduction_amount` =`reduction_amount_tax_excl`,
                `reduction_amount_tax_incl` =`reduction_amount_tax_excl`,
                `total_price_tax_incl` =`total_price_tax_excl`,
                `unit_price_tax_incl` =`unit_price_tax_excl`,
                `total_shipping_price_tax_incl` =`total_shipping_price_tax_excl`,
                `tax_rate` = 0
                WHERE id_order = '. $order->id);

            $result &= $db->execute('UPDATE `'._DB_PREFIX_.'order_invoice` SET
                `total_discount_tax_incl` =`total_discount_tax_excl`,
                `total_paid_tax_incl` =`total_paid_tax_excl`,
                `total_shipping_tax_incl` =`total_shipping_tax_excl`,
                `total_wrapping_tax_incl` =`total_wrapping_tax_excl`,
                `total_products_wt` =`total_products`
                WHERE id_order = '. $order->id);

            foreach ($order->getOrderDetailList() as $order_detail) {
                $result &= $db->execute('UPDATE `'._DB_PREFIX_.'order_detail_tax` SET `id_tax` = '.$id_tax.', `unit_amount` = 0, `total_amount` = 0 WHERE `id_order_detail` = '. $order_detail['id_order_detail']);
            }
            foreach($order->getInvoicesCollection() as $invoice) {
                $result &= $db->execute('UPDATE`'._DB_PREFIX_.'order_invoice_tax` SET `id_tax` = '.$id_tax.', `amount` = 0 WHERE id_order_invoice = '. $invoice->id);
            }

            if($result){
                $db->execute('COMMIT');
                return true;
            }
        } catch (\Exception $e) {
            // We catch just in case to execute rollback
        }

        $db->query('ROLLBACK');
        return false;
    }

	public function getContent()
	{

		$this->_html .= '<h1>'.$this->displayName.'</h1>';
		if (Tools::isSubmit('removeOrderVat') && $id_order = (int)Tools::getValue('removeOrderVat'))
		{
            if($this->removVatFromOrder($id_order))
                $this->_html .=$this->displayConfirmation(sprintf($this->l('Result: Tax removed for #%s'), $id_order) );
            else
                $this->_html .=$this->displayError(sprintf($this->l('Result: Could not remove tax for #%s'), $id_order));
		}

        $this->_html .= $this->display(__DIR__, 'views/templates/content.tpl');
		$this->_html .= '<p>'. sprintf($this->l('Free module by %s. See our other modules at'), $this->author) .' <a href="https://seigi.eu/">https://seigi.eu/</a> </p>';
		return $this->_html;
	}
}
