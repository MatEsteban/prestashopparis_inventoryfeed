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

<style>
*, a{
  font-size: 12px;
}

.bootstrap h6, .bootstrap .h6{
  font-size: 14px;
}
#intro > div.contact-btn.text-center > a > button{
  padding: 15px;
}
</style>

<div id="config-urbitproductfeed">
  <div class="form-wrapper">
    <ul class="nav nav-tabs">
      <li {if $active == 'intro'}class="active"{/if}><a href="#intro"
                                                        data-toggle="tab">{l s='Presentation' mod='urbitproductfeed'}</a></li>
      <li {if $active == 'account'}class="active"{/if}><a href="#account"
                                                          data-toggle="tab">{l s='Module Configuration' mod='urbitproductfeed'}</a>
      </li>
    </ul>
  <div class="tab-content panel">
      <div id="intro" class="tab-pane {if $active == 'intro'}active{/if}">
            <div id="urbit-theme-texticon_390" class="urbit-theme__widget urbit-theme-texticon ABdmKOduM6 text-center" style="" data-tab-id="intro">
      <h2 class="h2">{l s='Main benefit of being our partner ?' mod='urbitproductfeed'}</h2>
      <br><br>
           <div class="row urbit-theme-texticon__element">
          <div class="col col-sm-2 col-xs-12">
              <div class="image-wrapper">
              <img src="https://urb-it.com/fr/wp-content/uploads/sites/4/2017/11/Play.jpg" id="play" width="100px">
          </div>
      </div>
    <div class="col col-sm-8 col-xs-12">
            <div class="title h h6">{l s='MOVE PRODCUTS FASTER' mod='urbitproductfeed'}</div>
            <div class="text b b3">{l s='Adding Urb-it as a sales channel for your physical store will move your inventory faster as it lower the barrier to purchase. Checking out with urb-it is seamless for customers and handover often happens within an hour of purchase.' mod='urbitproductfeed'}</div>
        </div>
    </div>
    <br>
    <div class="row urbit-theme">
        <div class="col col-sm-2 col-xs-12">
            <div class="image-wrapper">
            <img src="https://urb-it.com/fr/wp-content/uploads/sites/4/2017/11/Satisfaction.jpg" alt="satisfaction" width="100px">
        </div>
    </div>
    <div class="col col-sm-8 col-xs-12">
            <div class="title h h6">{l s='SATISFY THE ON-DEMAND NEED OF YOUR CUSTOMER' mod='urbitproductfeed'}</div>
            <div class="text b b3">{l s='Nowadays customers get inspired, shop and share their experience from all possible places and platforms; social media, the web, stores etc. Urb-it helps you to meet the exceeding expectations for “on demand” shopping this entails, by being accessible when and where they want to be inspired, shop, and receive their purchase.' mod='urbitproductfeed'}</div>
        </div>
    </div>
    <br><br>
    <div class="row urbit-theme-texticon__element">
        <div class="col col-sm-2 col-xs-12">
          <br>
            <div class="image-wrapper">
             <img src="https://urb-it.com/fr/wp-content/uploads/sites/4/2017/11/Rotation.jpg" alt="Rotation" width="90px">
            </div>
        </div>
        <br>
    <div class="col col-sm-8 col-xs-12">
        <div class="title h h6">{l s='OFFER EXTRAORDINARY CUSTOMER EXPERIENCE FROM START TO FINISH' mod='urbitproductfeed'}</div>
        <div class="text b b3">{l s='With Urb-it you can be sure that your customer will be treated right. From a smooth, hassle-free checkout process to a personal handover of their purchase at a time that suits them best, your customer is in good hands.' mod='urbitproductfeed'}</div>
        </div>
    </div>
  </div>
  <br><br>
  <div class="row text-center">
      <div class="col col-sm-12">
      <h2 class="h2">{l s='How Does Urb-it Work?' mod='urbitproductfeed'}</h2>
  </div>
  <div class="col col-sm-12">
      <div class="row urbit-theme-circle__container">
        <div class="col col-sm-4 text-center urbit-theme-circle__element numerated">
        <div class="image image-small">
          <img src="https://urb-it.com/fr/wp-content/uploads/sites/4/2017/10/Onboarding_1_500x500px_2-1.gif" alt="Un client achète un produit de votre boutique sur l’application, sur votre site e-commerce ou directement dans votre magasin." width="100px">
        </div>
        <div class="data">
          <span class="num">1</span>
      <div class="title">{l s='SHOP FOR YOURSELF, OR CHOOSE THE PERFECT GIFT FOR SOMEONE ELSE' mod='urbitproductfeed'}</div>
      <div class="text"></div>
      </div>
    </div>
        <div class="col col-sm-4 text-center urbit-theme-circle__element numerated">
            <div class="image image-small">
              <img src="https://urb-it.com/fr/wp-content/uploads/sites/4/2017/10/Onboarding_2_500x500px-3.gif" alt="Un Urber va chercher vos achats dans votre boutique." width="100px">
          </div>
      <div class="data">
          <span class="num">2</span>
          <div class="title">{l s='CHOOSE A TIME AND PLACE YOU WANT IT. IF IT’S A GIFT, YOU CAN LET THE GIFT RECIPIENT CHOOSE OR EVEN SEND IT AS A SURPRISE.' mod='urbitproductfeed'}</div>
          <div class="text"></div>
        </div>
    </div>

      <div class="col col-sm-4 text-center urbit-theme-circle__element numerated">
          <div class="image image-small">
              <img src="https://urb-it.com/fr/wp-content/uploads/sites/4/2017/10/Onboarding_3_500x500px-2.gif" alt="Votre Urber apporte vos produits à vos clients exactement à l’heure et à l’endroit qu’ils ont choisi. " width="100px">
              </div>
              <div class="data">
              <span class="num">3</span>
              <div class="title">{l s='AN URBER WILL BRING IT TO YOU, OR TO THE GIFT RECIPIENT' mod='urbitproductfeed'}</div>
              <div class="text"></div>
              </div>
          </div>
        </div>
      </div>
      </div>
      <br><br>
         <div class="contact-btn text-center">
            <a href="https://urb-it.com/fr/contact-us/" target="_blank"><button type="button" class="btn btn-primary btn-lg">{l s='CONTACT US' mod='urbitproductfeed'}</button></a>
        </div>
      </div>
        <div id="account" class="tab-pane {if $active == 'account'}active{/if}">
          <div class="alert alert-info">{l s='Please fill in your credentials to use the module' mod='urbitproductfeed'}</div>
          <div class="form-group" data-tab-id="account">
            {$config}
        </div>
    </div>
</div>
