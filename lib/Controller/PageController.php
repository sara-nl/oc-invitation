<?php

/**
 * 
 */

namespace OCA\RDMesh\Controller;

use OCA\RDMesh\Federation\DomainProvider;
use OCA\RDMesh\Service\MeshRegistryService;
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
   public function wayf(string $token, string $providerDomain): void
   {

      echo '<html title="WAYF"><head></head><h4>Where Are You From</h4>';
      // TODO: retrieve the mesh servers info from the db
      foreach ($this->getWAYFURLs($token, $providerDomain) as $i => $url) {
         $domain = parse_url($url, PHP_URL_HOST);
         echo print_r("<p><a href=\"$url\">$domain</a></p>", true) . '</html>';
      }
      echo '</html>';

      exit(0);
   }

   /**
    * Returns the WAYF URLs.
    */
   private function getWAYFURLs(string $token, string $providerDomain): array
   {
      $domainProviders = $this->meshRegistryService->allDomainProviders();
      $wayfList = [];
      foreach ($domainProviders as $i => $domainProvider ) {
         $host = $domainProvider->getDomain();

         // TODO: check if the server supports the invitation workflow

         $appName = $this->appName;
         $handleInviteEndpoint = trim(MeshRegistryService::ENDPOINT_HANDLE_INVITE, '/');
         $tokenParam = MeshRegistryService::PARAM_NAME_TOKEN;
         $providerDomainParam = MeshRegistryService::PARAM_NAME_PROVIDER_DOMAIN;
         $link = "https://$host/apps/$appName/$handleInviteEndpoint?$tokenParam=$token&$providerDomainParam=$providerDomain";
         $wayfList[$i] = $link;
      }

      return $wayfList;
   }
}
