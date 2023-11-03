<?php

/**
 * 
 */

namespace OCA\RDMesh\Controller;

use OCA\Federation\TrustedServers;
use OCA\RDMesh\Service\MeshRegistryService;
use OCP\AppFramework\Controller;
use OCP\IRequest;

class PageController extends Controller
{

   private TrustedServers $trustedServers;

   public function __construct($appName, IRequest $request, TrustedServers $trustedServers)
   {
      parent::__construct($appName, $request);
      $this->trustedServers = $trustedServers;
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
         echo print_r("<a href=\"$url\">$url</a>", true) . '</html>';
      }
      echo '</html>';

      exit(0);
   }

   /**
    * Returns the WAYF URLs.
    */
   private function getWAYFURLs(string $token, string $providerDomain): array
   {
      // FIXME: get the domain providers, NOT the trusted servers
      $trustedServers = $this->trustedServers->getServers();
      $wayfList = [];
      foreach ($trustedServers as $i => $server) {

         // TODO: we assume that the trusted server domain is the full owncloud root url, ie. the part before /apps/
         //
         //        +----------------------------+
         //        |                            |
         //        https://owncloud.mydomain.org/apps/rd-mesh/...
         //
         // TODO: We must figure out how to deal with domains, hosts, and designing the request functions for these.
         //       plus if required the configuration of these.
         
         $host = parse_url($server['url'], PHP_URL_HOST);

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
