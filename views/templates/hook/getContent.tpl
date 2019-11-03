<fieldset>
    <h2>{l s='Predefined options' mod='trfacturas'}</h2>
    <div class="panel">
        <div class="panel_heading">
        <legend><img src="../img/admin/cog.gif" alt="conf" width="16">{l s='Configuration' mod='trfacturas'}</legend>
        </div>
        <form action="" method="post" class="bg-success" style="border: solid 1px black;">
            <div class="form-group clearfix bg-warning">
                <label class="col-lg-3">{l s='Print all' mod='trfacturas'}</label>
                <div class="col-lg-9">                    
                    <input type="radio" id="export_active" name="export_active" value="1" 
                    {if $export_active == "1"}checked {/if}>
                    <img src="../img/admin/enabled.gif" alt="ok" />                    
                    <input type="radio" id="export_active" name="export_active" value="0"
                    {if $export_active == "0"}checked {/if}>
                    <img src="../img/admin/disabled.gif" alt="ko" />                    
                </div>
            </div>
            <div class="form-group clearfix bg-warning ">
                <label class="col-lg-3">{l s='Stasuses to print (when only active)' mod='trfacturas'}</label>
                <div class="col-lg-9">
                    {foreach key=key from=$statusID item=curr_status}                    
                        <input type="radio" id="status{$curr_status}" name="status{$curr_status}" value="0"
                        {if $statusActive[$key] == '0'}checked {/if}>
                        <img src="../img/admin/disabled.gif" alt="ko" />
                        <input type="radio" id="status{$curr_status}" name="status{$curr_status}" value="1"
                        {if $statusActive[$key] == '1'}checked {/if}>
                        <img src="../img/admin/enabled.gif" alt="ko" />
                        {$curr_status} {$statusName[$key]}<br />
                    {/foreach}
                </div>
            </div>
             <div class="form-group clearfix bg-warning">
                <label class="col-lg-3">{l s='Invoice models' mod='trfacturas'}</label>
                <div class="col-lg-9">
                    {foreach key=key from=$invoicenames item=invoice}                    
                        <input type="radio" id="invoiceS" name="invoiceS" value="{$invoice.value}"
                         {if $invoice_active == $invoice.value}checked {/if}>
                        <img src="../img/admin/enabled.gif" alt="ko" />
                        {$invoice.name}<br />
                    {/foreach}
                </div>
            </div>
            <div class="panel-footer">
                <input class="btn btn-success btn-block" value="Guardar" name="trfacturas_conf_form" type="submit">
            </div>
        </form>
    </div>
</fieldset>