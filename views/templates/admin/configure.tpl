{*
* 2007-2023 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2023 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
{block name='moduleconfig'}
    <div class="panel">
        <div class="row moduleconfig-header">
            <div class="col-xs-5 text-right">
                <img src="{$module_dir|escape:'html':'UTF-8'}views/img/logo.png"/>
            </div>
            <div class="col-xs-7 text-left">
                <h2>{l s='Retro Connexion' mod='retroconnexion'}</h2>
                <h4>{l s='Connect your Node JS Chat !' mod='retroconnexion'}</h4>
            </div>
        </div>

        <hr/>

        <div class="moduleconfig-content">
            <div class="row">
                <div class="col-xs-12">
                    <h4>{l s='Configure your key here' mod='retroconnexion'}</h4>
                    <form method="post"
                          action="{$action}">
                        <div class="form-group">
                            <label for="key">Key</label>
                            <div class="keyInputLinux">
                                <input type="text" id="key" value="{$secretKey|escape:'html':'UTF-8'}" name="key"
                                       class="form-control"
                                       placeholder="Key"/>https://soundcloud.com/leonekmi/ikea-njut-earrape
                                <button class="btn btn-primary" id="generateKey" type="button">Generate a Key</button>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary" name="submit">Save</button>
                    </form>

                    <br/>
                </div>
            </div>
        </div>
    </div>
    {*    Get the script in the assets/js folder*}
    <script src="{$module_dir|escape:'html':'UTF-8'}views/js/configure.js"></script>
{/block }