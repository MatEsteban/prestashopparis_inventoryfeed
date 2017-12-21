{*
*
* 2015-2017 Urb-it
*
* NOTICE OF LICENSE
*
*
*
* Do not edit or add to this file if you wish to upgrade Urb-it to newer
* versions in the future. If you wish to customize Urb-it for your
* needs please refer to https://urb-it.com for more information.
*
* @author    Urb-it SA <parissupport@urb-it.com>
* @copyright 2015-2017 Urb-it SA
* @license  http://www.gnu.org/licenses/
*
*
*}

{extends file="helpers/form/form.tpl"}
{block name="input"}
    {if $input.type == 'urbit_product_id_filter'}
        <input type="hidden" id='controllerUrl' value="{$controllerlink}">
        <div class="row">
            <div class="col-xs-5">
                <select name="from[]" id="search" class="form-control" size="8" multiple="multiple">
                </select>
            </div>

            <div class="col-xs-1">
                <button type="button" id="search_rightAll" class="btn btn-block"><i class="icon-double-angle-right"></i></button>
                <button type="button" id="search_rightSelected" class="btn btn-block"><i class="icon-chevron-sign-right"></i></button>
                <button type="button" id="search_leftSelected" class="btn btn-block"><i class="icon-chevron-sign-left"></i></button>
                <button type="button" id="search_leftAll" class="btn btn-block"><i class="icon-double-angle-left"></i></button>
            </div>

            <div class="col-xs-5">
                <select name="URBITINVENTORYFEED_PRODUCT_ID_FILTER[]" id="search_to" class="form-control" size="8" multiple="multiple"></select>
            </div>
        </div>
        <script>
            (function ($) {

                $('#search').multiselect({
                    search: {
                        left: '<input type="text" name="q" class="form-control" placeholder="Search..." />',
                        right: '<input type="text" name="q" class="form-control" placeholder="Search..." />',
                    },
                    fireSearch: function(value) {
                        return value.length > 3;
                    }
                });

                getConfigOptionsByAjax();

                $('#urbitinventoryfeed-filter-categories, #urbitinventoryfeed-filter-tags, #urbitinventoryfeed-filter-minimal-stock').change(getOptionsByAjax);
                $('#urbitinventoryfeed-filter-minimal-stock').on('input', getOptionsByAjax);
            })($);

            //get products for left multiselect
            function getOptionsByAjax() {
                var url = $("#controllerUrl").val();

                $.ajax({
                    method: "POST",
                    url: url,
                    data: {
                        ajax: true,
                        categoriesFromAjax: $('#urbitinventoryfeed-filter-categories').val(),
                        tagsFromAjax: $('#urbitinventoryfeed-filter-tags').val(),
                        minimalStockFromAjax: $('#urbitinventoryfeed-filter-minimal-stock').val()
                    },
                    success: function (data) {
                        var rightMultiselectValues = $.map($('#search_to option'), function(e) { return e.value; });

                        var options = jQuery.parseJSON(data);
                        var $leftSelectBox = $("#search");

                        $leftSelectBox.empty();

                        $.each(options, function(key,value) {
                            //remove duplicates
                            if (jQuery.inArray(value.id, rightMultiselectValues) !== -1) {
                                return;
                            }

                            $leftSelectBox.append($("<option></option>")
                                .attr("value", value.id).text(value.name));
                        });
                    }
                });
            }

            //get products for right multiselect from config
            function getConfigOptionsByAjax() {
                var url = $("#controllerUrl").val();

                $.ajax({
                    method: "POST",
                    url: url,
                    data: {
                        ajax: true,
                        configValues: true
                    },
                    success: function (data) {
                        var options = jQuery.parseJSON(data);
                        var $rightSelectBox = $("#search_to");

                        $rightSelectBox.empty();

                        $.each(options, function(key,value) {
                            $rightSelectBox.append($("<option></option>")
                                .attr("value", value.id).text(value.name));
                        });

                        getOptionsByAjax();
                    }
                });
            }

        </script>
    {elseif $input.type == 'urbit_token'}
        <div class="form-group">
            <input type="text" name="URBITINVENTORYFEED_FEED_TOKEN" class="fixed-width-xxl" id="urbit-feed-token" value="{$fields_value[$input.name]}">
        </div>
        <div class="form-group">
            <button type="submit" value="1" name="submitInventoryFeedModule" class="btn btn-default">Save Token</button>
        </div>
        <div class="form-group">
            <a href="/index.php?fc=module&module=urbitinventoryfeed&controller=feed&token={$input.token}" id="generate-token-button" class="btn btn-default">Get the feed</a>
        </div>
    {else}
        {$smarty.block.parent}
    {/if}

{/block}
