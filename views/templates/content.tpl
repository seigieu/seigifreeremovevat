{*
/*
*  @author     SEIGI Grzegorz Zawadzki <kontakt@seigi.eu>
*  @copyright  2021 SEIGI Grzegorz Zawadzki
*  @license    https://creativecommons.org/licenses/by-sa/3.0/pl/ Uznanie autorstwa-Na tych samych warunkach 3.0 Polska (CC BY-SA 3.0 PL)
*/

*}
<div class="panel">
    <form action="" method="post">
        <p>
            {l s='Type Order ID and press button' mod='seigifreeremovevat'}: <input name="removeOrderVat" placeholder="{l s='Order ID' mod='seigifreeremovevat'}">
        </p>
        <p>
            <input type="submit" value="{l s='Remove VAT from order' mod='seigifreeremovevat'}" class="button btn btn-default">
        </p>
        <p>
            {l s='Removing VAT from order applies undoable changes to order and database. Remember to always have a backup ready in case you want to restore data' mod='seigifreeremovevat'}
{*            Usuniecie VATu z zamówienia spowoduje nieodwracalne zmiany w zamówieniu. Usuwasz VAT na własną odpowiedzialność.*}
        </p>
    </form>
</div>
