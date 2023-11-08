<?php

/**
 * 
 */

namespace OCA\RDMesh\Controller;

use OCA\RDMesh\Federation\Service\MeshRegistryService;
use OCP\AppFramework\Controller;
use OCP\IRequest;

class PageController extends Controller
{

   private MeshRegistryService $meshRegistryService;

   public function __construct($appName, IRequest $request, MeshRegistryService $meshRegistryService)
   {
      parent::__construct($appName, $request);
      $this->meshRegistryService = $meshRegistryService;
   }

   /**
    * Displays the WAYF page.
    * 
    * @NoCSRFRequired
    * @PublicPage
    * @param string $token the token
    * @param string $providerDomain the domain of the sender
    * @return void
    */
   public function wayf(string $token, string $providerDomain, string $name): void
   {
      // TODO: use template for this
      echo '<html title="WAYF"><head></head><h4>Where Are You From</h4>';
      foreach ($this->getWAYFURLs($token, $providerDomain, $name) as $i => $url) {
         $domain = parse_url($url, PHP_URL_HOST);
         echo print_r("<p><a href=\"$url\">$domain</a></p>", true) . '</html>';
      }
      echo '</html>';

      exit(0);
   }

   /**
    * Returns the WAYF URLs.
    */
   private function getWAYFURLs(string $token, string $providerDomain, string $name): array
   {
      $domainProviders = $this->meshRegistryService->allDomainProviders();
      $wayfList = [];
      foreach ($domainProviders as $i => $domainProvider ) {
         $host = $domainProvider->getDomain();

         // TODO: optional: check if the server supports the invitation workflow
         //       This should be done via the ocm /ocm-provider endpoint which must return the '/invite-accepted' capability
         //       to inform us it supports handling invitations.
         //       More likely is that we already know it should, 
         //       so this would be more like a sanity check (eg. the service may be down)

         $appName = $this->appName;
         $handleInviteEndpoint = trim(MeshRegistryService::ENDPOINT_HANDLE_INVITE, '/');
         $tokenParam = MeshRegistryService::PARAM_NAME_TOKEN;
         $providerDomainParam = MeshRegistryService::PARAM_NAME_PROVIDER_DOMAIN;
         $nameParam = MeshRegistryService::PARAM_NAME_NAME;
         $link = "https://$host/apps/$appName/$handleInviteEndpoint?$tokenParam=$token&$providerDomainParam=$providerDomain&$nameParam=$name";
         $wayfList[$i] = $link;
      }

      return $wayfList;
   }
}
