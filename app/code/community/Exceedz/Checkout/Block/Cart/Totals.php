<?php
/**
 * @category    Exceedz
 * @package     Exceedz_Checkout
 */
class Exceedz_Checkout_Block_Cart_Totals extends Mage_Checkout_Block_Cart_Totals
{
    /**
     * Render totals html for specific totals area (footer, body)
     *
     * @param   null|string $area
     * @param   int $colspan
     * @return  string
     */
    public function renderTotals($area = null, $colspan = 1)
    {
        $html = '';
        foreach($this->getTotals() as $total) {
            if ($total->getArea() != $area && $area != -1) {
                continue;
            }
            $html .= $this->renderTotal($total, $area, $colspan);
        }
        return $html;
    }
	
	/**
     * Render totals html for specific totals area (footer, body)
     *
     * @param   null|string $area
     * @param   int $colspan
     * @return  string
     */
    public function renderTotalsByCode($area = null, $colspan = 1, $code = 'subtotal')
    {
        $html = '';
        foreach($this->getTotals() as $total) {
            if ($total->getArea() != $area && $area != -1) {
                continue;
            }
			if($code == $total->getCode())
			    $html .= $this->renderTotal($total, $area, $colspan);
        }
        return $html;
    }   
}